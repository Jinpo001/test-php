<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/11/4
 * Time: 15:09
 */

namespace app\forms\mall\pay_type;

use app\core\response\ApiCode;
use app\helpers\ArrayHelper;
use app\models\Model;
use app\models\PayType;
use app\plugins\wxapp\forms\wx_app_config\WxAppConfigForm;
use app\plugins\wxapp\models\WxappConfig;
use app\plugins\wxapp\models\WxappWxminiprograms;

class PayTypeForm extends Model
{
    public $id;
    public $limit;
    public $page;
    public $keyword;
    public $type;

    public function rules()
    {
        return [
            [['id', 'page', 'limit', 'type'], 'integer'],
            [['keyword',], 'string'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $detail = PayType::find()->andWhere([
            'id' => $this->id,
        ])->one();

        if (!$detail) {
            throw new \Exception('支付方式记录不存在');
        }
        unset($detail->service_cert_pem);
        unset($detail->service_key_pem);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $detail,
            ]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $this->autoAdd();
        $list = PayType::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->page($pagination, $this->limit, $this->page)
            ->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->keyword($this->type, ['type' => $this->type])
            ->select(['id', 'name', 'type'])
            ->all();
        $newList = [];
        foreach ($list as $item) {
            /**@var PayType $item**/
            $newItem = ArrayHelper::toArray($item);
            $newItem['type_text'] = $this->typeTotext()[$item->type];
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ],
        ];
    }

    private function typeTotext()
    {
        return [
            '1' => '微信',
            '2' => '支付宝',
        ];
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $type = PayType::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'is_delete' => 0]);
            if (!$type) {
                throw new \Exception('该支付方式不存在');
            }
            $type->is_delete = 1;
            if (!$type->save()) {
                throw new \Exception($this->getErrorMsg($type));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'data' => '删除失败',
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * 自动添加微信小程序插件配置过的记录
     */
    private function autoAdd()
    {
        $exists = PayType::findOne(['mall_id' => \Yii::$app->mall->id, 'is_auto_add' => 1]);
        if ($exists) {
            return true;
        }
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (in_array('wxapp', $permission)) {
            try {
                $plugin = \Yii::$app->plugin->getInstalledPlugin('wxapp');
                if (!$plugin) {
                    throw new \Exception('插件未安装');
                }
                /**@var WxappConfig $config**/
                $config = WxappConfig::find()
                    ->where(['mall_id' => \Yii::$app->mall->id])
                    ->with(['service'])
                    ->one();
                $third = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                if (!$config || !$config->mchid || (!$third && !$config->appid)) {
                    throw new \Exception('小程序信息尚未配置。');
                }
                $payType = new PayType();
                $payType->mall_id = \Yii::$app->mall->id;
                $payType->name = '微信小程序微信支付';
                $payType->type = 1;
                $payType->appid = $third->authorizer_appid ?? $config->appid;
                $payType->mchid = $config->mchid;
                $payType->key = $config->key;
                $payType->cert_pem = $config->cert_pem;
                $payType->key_pem = $config->key_pem;
                if (isset($config->service->is_choise)) {
                    $payType->is_service = $config->service->is_choise;
                    $payType->service_appid = $config->service->appid;
                    $payType->service_key = $config->service->key;
                    $payType->service_mchid = $config->service->mchid;
                    $payType->service_cert_pem = $config->service->cert_pem;
                    $payType->service_key_pem = $config->service->key_pem;
                }
                $payType->is_auto_add = 1;
                if (!$payType->save()) {
                    throw new \Exception($this->getErrorMsg($payType));
                }
                return true;
            } catch (\Exception $e) {
                \Yii::error('=====自动添加支付记录配置失败=====');
                \Yii::error($e);
            } catch (\Error $error) {
                \Yii::error($error);
            }
        } else {
            \Yii::error('=====自动添加支付记录配置失败,没有微信小程序插件权限=====');
        }
    }
}
