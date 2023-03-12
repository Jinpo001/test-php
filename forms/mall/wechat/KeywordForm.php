<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/2
 * Time: 3:11 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;


use app\models\Model;
use app\models\WechatKeyword;

class KeywordForm extends Model
{
    public $name;
    public $status;
    public $rule_id;

    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name'], 'string'],
            [['rule_id', 'status'], 'integer'],
            ['status', 'in', 'range' => [0, 1]]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        $model = new WechatKeyword();
        $model->mall_id = \Yii::$app->mall->id;
        $model->is_delete = 0;
        $model->name = $this->name;
        $model->rule_id = $this->rule_id;
        $model->status = $this->status;
        if (!$model->save()) {
            throw new \Exception($this->getErrorMsg($model));
        }
        return $model->id;
    }
}
