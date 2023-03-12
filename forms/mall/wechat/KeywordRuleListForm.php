<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/2
 * Time: 4:27 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\forms\common\wechat\WechatFactory;
use app\models\Model;
use app\models\WechatKeywordRules;
use app\models\WechatSubscribeReply;

class KeywordRuleListForm extends Model
{
    public $page;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            ['page', 'default', 'value' => 1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = WechatKeywordRules::find()->with('keyword')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->page($pagination, 20, $this->page)
            ->all();
        $newList = [];
        $replyId = [];
        /* @var WechatKeywordRules[] $list */
        foreach ($list as $item) {
            $replyId = array_merge($replyId, explode(',', $item->reply_id));
        }
        // 获取所有回复内容
        $replyList = WechatSubscribeReply::findAll([
            'id' => $replyId, 'is_delete' => 0, 'status' => 1
        ]);
        foreach ($list as $item) {
            $keywordList = [];
            foreach ($item->keyword as $keyword) {
                $keywordList[] = $keyword->name . '(' . $keyword->getStatusText() . ')';
            }
            $itemReplyId = explode(',', $item->reply_id);
            // 统计回复内容
            $replyTextList = [];
            foreach ($replyList as $reply) {
                if (in_array($reply->id, $itemReplyId)) {
                    if (isset($replyTextList[$reply->type])) {
                        $replyTextList[$reply->type]++;
                    } else {
                        $replyTextList[$reply->type] = 1;
                    }
                }
            }
            $replyTemp = [];
            $replyText = ['文字', '图片', '语音', '视频', '图文'];
            foreach ($replyTextList as $key => $count) {
                $replyTemp[] = $replyText[$key] . '(' . $count . ')';
            }
            $newList[] = [
                'id' => $item->id,
                'name' => $item->name,
                'keyword' => implode(',', $keywordList),
                'reply' => implode(',', $replyTemp),
            ];
        }
        return $this->success([
            'list' => $newList,
            'pagination' => $pagination
        ]);
    }
}
