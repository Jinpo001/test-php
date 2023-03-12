<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Store;
use app\plugins\teller\models\TellerSales;

class SalesStoreForm extends Model
{
    public $number;
    public $name;
    public $mobile;
    public $store_id;
    public $status;
    public $head_url;

    public function rules()
    {
        return [
            [['number', 'name', 'mobile', 'store_id', 'status'], 'required'],
            [['number', 'name', 'mobile', 'head_url'], 'string'],
            [['status', 'store_id'], 'integer'],
            [['number', 'name', 'mobile'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [
            'number' => '编号',
            'name' => '姓名',
            'mobile' => '电话',
            'store_id' => '门店',
            'status' => '启用状态',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();

            $sales = new TellerSales();
            $sales->mall_id = \Yii::$app->mall->id;
            $sales->mch_id = \Yii::$app->user->identity->mch_id;
            $sales->creator_id = \Yii::$app->user->id;
            $sales->number = $this->number;
            $sales->name = $this->name;
            $sales->mobile = $this->mobile;
            $sales->store_id = $this->store_id;
            $sales->status = $this->status;
            $sales->head_url = $this->head_url;
            $sales->save();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function checkData()
    {
        if (mb_strlen($this->name) < 1 || mb_strlen($this->name) > 30) {
            throw new \Exception('名称长度范围1至30个字符');
        }

        if (mb_strlen($this->number) < 1 || mb_strlen($this->number) > 30) {
            throw new \Exception('编号长度范围1至30个字符');
        }

        if (mb_strlen($this->mobile) > 11) {
            throw new \Exception('请输入正确的手机号');
        }

        if (!in_array($this->status, [0,1])) {
            throw new \Exception('status参数合法值[0,1]');
        }

        $sales = TellerSales::find()->andWhere([
            'number' => $this->number,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->one();

        if ($sales) {
            if ($sales->number == $this->number) {
                throw new \Exception('编号已存在');
            }
        }

        $store = Store::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->store_id,
            'is_delete' => 0
        ])->one();

        if (!$store) {
            throw new \Exception('门店不存在');
        }
    }
}
