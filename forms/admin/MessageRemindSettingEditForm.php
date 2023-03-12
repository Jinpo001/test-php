<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Option;
use yii\helpers\ArrayHelper;


class MessageRemindSettingEditForm extends Model
{
    public $day;
    public $message_text;
    public $status;


    public function rules()
    {
        return [
            [['day', 'message_text'], 'required'],
            [['day', 'status'], 'integer'],
            [['message_text'], 'string'],
        ];
    }

    public function save()
    {

        try {

            if ($this->day < 1) {
                throw new \Exception('过期天数提醒不能小于1天');
            }

            if ($this->day > 90) {
                throw new \Exception('过期天数提醒不能大于90天');
            }

            $data = [
                'day' => $this->day,
                'message_text' => $this->message_text,
                'status' => $this->status
            ];

            $setting = \app\forms\common\CommonOption::set('message_remind_setting', $data, 0, Option::GROUP_ADMIN);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
