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

class AppManageEditForm extends Model
{
    public $name;
    public $display_name;
    public $pic_url_type;
    public $pic_url;
    public $content;
    public $is_show;
    public $pay_type;
    public $price;
    public $detail;

    public function rules()
    {
        return [
            [['name', 'display_name', 'pay_type', 'price', 'pic_url_type'], 'required'],
            [['name', 'display_name', 'pic_url', 'pay_type', 'content', 'detail', 'price'], 'string'],
            [['is_show', 'pic_url_type'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '应用标识',
            'display_name' => '应用名称',
            'pic_url' => '应用图标',
            'pay_type' => '支付方式',
            'price' => '售价'
        ];
    }

    public function edit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            $appManage = AppManage::find()->andWhere(['name' => $this->name, 'is_delete' => 0])->one();
            if (!$appManage) {
                $appManage = new AppManage();
                $appManage->name = $this->name;
            }

            $appManage->display_name = $this->display_name;
            $appManage->pic_url_type = $this->pic_url_type;
            $appManage->pic_url = $this->pic_url;
            $appManage->content = $this->content;
            $appManage->is_show = $this->is_show;
            $appManage->pay_type = $this->pay_type;
            $appManage->price = $this->price;
            $appManage->detail = $this->detail;
            $res = $appManage->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($appManage));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
