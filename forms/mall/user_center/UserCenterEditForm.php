<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\user_center;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\models\UserCenter;

class UserCenterEditForm extends Model
{
    public $data;
    public $id;
    public $name;

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'trim'],
            ['name', 'string'],
            ['id', 'integer'],
            ['data', 'safe']
        ];
    }

    public function save()
    {
        try {
            if (!$this->validate()) {
                throw new \Exception($this->getErrorMsg());
            }
            $this->checkData();
            $model = UserCenter::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'is_delete' => 0]);
            if (!$model) {
                $model = new UserCenter();
                $model->mall_id = \Yii::$app->mall->id;
                $model->is_delete = 0;
                $model->is_recycle = 0;
            }
            $model->name = $this->name;
            $model->config = \Yii::$app->serializer->encode($this->data);

            if (!$model->save()) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function checkData()
    {
        if (!isset($this->data['menus'])) {
            $this->data['menus'] = [];
        }
        if (isset($this->data['account_bar'])) {
            foreach ($this->data['account_bar'] as $index => $item) {
                if (is_array($item) && mb_strlen($item['text']) > 4) {
                    throw new \Exception('我的账户--文字说明不能大于4个字');
                }
            }
        }
    }

    public function reset()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $userCenterDefault = (new UserCenterForm())->getDefault();
        $this->data = $userCenterDefault;
        return $this->save();
    }
}
