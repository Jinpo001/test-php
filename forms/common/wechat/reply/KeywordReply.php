<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/3
 * Time: 11:01 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\reply;

use app\forms\common\wechat\WechatFactory;
use app\models\Mall;
use app\models\Model;
use app\models\WechatKeyword;
use app\models\WechatKeywordRules;
use app\models\WechatSubscribeReply;

class KeywordReply extends Model
{
    /**
     * @var WechatFactory $app
     */
    public $app;

    /**
     * @var Mall $mall
     */
    public $mall;

    public function reply($xmlDataArray)
    {
        if ($xmlDataArray['Content'] == '【收到不支持的消息类型，暂无法显示】') {
            return false;
        }
        \Yii::warning('关键词回复');
        $keyword = $xmlDataArray['Content'];
        // 1、进行关键词匹配
        $query = WechatKeyword::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0
        ]);
        $ruleIdList = [];
        foreach ($query->each() as $each) {
            /* @var WechatKeyword $each */
            if (
                $each->status == 0 && $each->name === $keyword
                || $each->status == 1 && strpos($keyword, $each->name) !== false
            ) {
                $ruleIdList[] = $each->rule_id;
            }
        }
        // 2、查找匹配到的关键词回复规则
        $ruleQuery = WechatKeywordRules::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $ruleIdList
        ]);
        $replyIdList = [];
        foreach ($ruleQuery->each() as $rule) {
            /* @var WechatKeywordRules $rule */
            $replyArr = explode(',', $rule->reply_id);
            if ($rule->status == 0) {
                // 全部回复
                $replyIdList = array_merge($replyIdList, $replyArr);
            } else {
                // 随机回复
                array_push($replyIdList, $replyArr[rand(0, count($replyArr) - 1)]);
            }
        }
        // 3、发送消息
        $replyQuery = WechatSubscribeReply::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $replyIdList, 'status' => 1
        ]);
        foreach ($replyQuery->each() as $reply) {
            /* @var WechatSubscribeReply $reply */
            $message = WechatFactory::createMessage($reply->type);
            $arr = [
                'touser' => $xmlDataArray['FromUserName'],
            ];
            $res = $this->app->customService->send(array_merge($arr, $message->custom($reply)));
            \Yii::warning($res);
        }
        return true;
    }
}
