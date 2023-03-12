<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\app_manage;

use app\core\response\ApiCode;
use app\forms\admin\PaySettingForm;
use app\models\AppManage;
use app\models\AppOrder;
use app\models\Model;

class PluginForm extends Model
{
    public $name;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '应用标识'
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $name = $this->name;
            $versionFile = \Yii::$app->basePath . '/plugins/' . $name . '/version';
            if (file_exists($versionFile)) {
                $version = trim(file_get_contents($versionFile));
            } else {
                $version = null;
            }
            $cloudData = \Yii::$app->cloud->plugin->getDetail([
                'name' => $name,
                'version' => $version,
            ]);
            $data = [
                'id' => $cloudData['id'],
                'name' => $cloudData['name'],
                'display_name' => $cloudData['display_name'],
                'pic_url' => $cloudData['new_icon'] ? $cloudData['new_icon'] : $cloudData['pic_url'],
                'content' => $cloudData['content'],
                'type' => 'cloud',
                'order' => $cloudData['order'],
                'version' => isset($cloudData['package']) ? $cloudData['package']['version'] : null,
                'new_version' => $cloudData['new_version'],
                'desc' => $cloudData['desc'],
                'price' => $cloudData['price']
            ];
        } catch (\Exception $exception) {
            $Class = '\\app\\plugins\\' . $name . '\\Plugin';
            if (!class_exists($Class)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '插件不存在。',
                ];
            }
            /** @var Plugin $plugin */
            $plugin = new $Class();
            $data = [
                'id' => null,
                'name' => $plugin->getName(),
                'display_name' => $plugin->getDisplayName(),
                'pic_url' => $plugin->getIconUrl(),
                'content' => $plugin->getContent(),
                'type' => 'local',
                'version' => $plugin->getVersionFileContent(),
                'new_version' => false,
            ];
        }
        $data['installed_plugin'] = \Yii::$app->plugin->getInstalledPlugin($data['name']);

        $appManage = AppManage::find()->andWhere(['name' => $name, 'is_delete' => 0])->one();
        if ($appManage && !\Yii::$app->role->isSuperAdmin) {
            $data['display_name'] = $appManage->display_name;
            $data['desc'] = $appManage->content;
            $data['content'] = $appManage->detail;
            $data['pic_url'] = $appManage->pic_url ?: $data['pic_url'];
        }

        $PluginClass = "app\\plugins\\{$name}\\Plugin";
        if (class_exists($PluginClass)) {
            /** @var Plugin $pluginObject */
            $pluginObject = new $PluginClass();
            $data['is_buy'] = true;
            if (!\Yii::$app->role->checkPlugin($pluginObject)) {
                $data['is_buy'] = false;
            }

            $appOrder = AppOrder::find()->andWhere([
                'name' => $pluginObject->getName(),
                'user_id' => \Yii::$app->user->id, 
                'is_delete' => 0,
                'is_pay' => 1
            ])->one();
            $data['app_order'] = $appOrder;
        }
        $data['is_super_admin'] = \Yii::$app->role->isSuperAdmin;
        $data['app_manage'] = $appManage;

        $setting = (new PaySettingForm())->getOption();
        $data['setting'] = $setting;
        $data['random_number'] = rand(0, count($setting['customer_service_list']) - 1);
        
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data,
        ];
    }
}
