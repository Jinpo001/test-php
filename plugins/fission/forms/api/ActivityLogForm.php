<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/25
 * Time: 11:17 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

use app\plugins\fission\forms\common\CommonActivity;
use app\plugins\fission\forms\common\CommonSetting;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionRewardLog;

class ActivityLogForm extends Model
{
    public $page;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    public function getReward()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $logs = FissionRewardLog::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id])
            ->apiPage(20, $this->page)->orderBy(['created_at' => SORT_DESC])->all();
        $list = CommonActivity::getInstance()->getRewards($logs);
        $setting = CommonSetting::getInstance()->getSetting();
        $count = count($setting['contact_list']);
        $contact = !empty($setting['contact_list']) ? $setting['contact_list'][rand(0, $count - 1)] : null;
        if ($contact) {
            $contact['custom'] = $setting['custom'];
        }
        return $this->success([
            'list' => array_values($list),
            'contact' => $contact
        ]);
    }
}
