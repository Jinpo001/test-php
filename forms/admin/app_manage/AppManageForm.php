<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\app_manage;

use app\core\response\ApiCode;
use app\models\AppManage;
use app\models\Model;

class AppManageForm extends Model
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

            $appManage = AppManage::find()->andWhere(['name' => $this->name, 'is_delete' => 0])->one();
            if ($appManage) {
                $data = [
                    'name' => $appManage->name,
                    'display_name' => $appManage->display_name,
                    'pic_url_type' => $appManage->pic_url_type,
                    'pic_url' => $appManage->pic_url,
                    'content' => $appManage->content,
                    'is_show' => $appManage->is_show,
                    'pay_type' => $appManage->pay_type,
                    'price' => $appManage->price,
                    'detail' => $appManage->detail,
                ];
            } else {

                $data = $this->getPlugin($this->name);

                $data = [
                    'name' => $this->name,
                    'display_name' => $data['display_name'],
                    'pic_url_type' => 1,
                    'pic_url' => $data['pic_url'],
                    'content' => $data['desc'],
                    'is_show' => 1,
                    'pay_type' => 'service',
                    'price' => '',
                    'detail' => $data['content'],
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $data
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    private function getPlugin($name)
    {
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
            ];
        } catch (\Exception $exception) {
            $Class = '\\app\\plugins\\' . $name . '\\Plugin';
            if (!class_exists($Class)) {
                throw new \Exception("插件不存在。");
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
                'desc' => ''
            ];
        }

        return $data;
    }
}
