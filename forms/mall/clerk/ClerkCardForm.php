<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\clerk;

use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\forms\mall\clerk\BaseClerk;
use app\forms\mall\export\ClerkCardExport;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\ClerkUser;
use app\models\GoodsCardClerkLog;
use app\models\Store;
use app\models\UserCard;

class ClerkCardForm extends BaseClerk
{
    public $time;
    public $keyword;
    public $keyword_name;
    public $clerk_id;

    public $flag;
    public $fields;
    public $page;

    public function rules()
    {
        return [
            [['keyword', 'keyword_name', 'flag'], 'trim'],
            [['time', 'fields'], 'safe'],
            [['page', 'clerk_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $userCardQuery = UserCard::find()->andWhere(['mall_id' => \Yii::$app->mall->id]);

        switch ($this->keyword_name) {
            case 'card_id':
                $userCardQuery->andWhere(['like', 'card_id', $this->keyword]);
                break;
            case 'card_name':
                $userCardQuery->andWhere(['like', 'name', $this->keyword]);
                break;
            default:
                break;
        }

        $userCardIds = $userCardQuery->select('id');

        $query = GoodsCardClerkLog::find()->where(['user_card_id' => $userCardIds]);

        if ($this->time) {
            $query->andWhere(['>=', 'clerked_at', $this->time[0]])
                ->andWhere(['<=', 'clerked_at', $this->time[1]]);
        }

        if ($this->clerk_id) {
            $query->andWhere(['clerk_id' => $this->clerk_id]);
        }

        if ($this->flag == "EXPORT") {
            $queueId = CommonExport::handle([
                'export_class' => 'app\\forms\\mall\\export\\ClerkCardExport',
                'params' => [
                    'query' => $query,
                    'fieldsKeyList' => $this->fields,
                ]
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId
                ]
            ];
        }

        $list = $query->with('user','store','userCard.card')
            ->orderBy(['clerked_at' => SORT_DESC])
            ->page($pagination)
            ->all();

        $newList = [];
        /** @var GoodsCardClerkLog[] $list */
        foreach ($list as $key => $item) {
            $newItem = [];
            $newItem['card_id'] = $item->userCard->card_id;
            $newItem['card_name'] = $item->userCard->name;
            $newItem['clerk_user_id'] = $item->user->id;
            $newItem['clerk_user_name'] = $item->user->nickname;
            $newItem['clerk_user_avatar'] = $item->user->userInfo->avatar;
            $newItem['clerk_user_platform'] = $item->user->userInfo->platform;
            $newItem['clerk_store_name'] = $item->store->name;
            $newItem['clerk_number'] = $item->use_number;
            $newItem['clerk_time'] = $item->clerked_at;
            $newItem['platform_icon'] = PlatformConfig::getInstance()->getPlatformIcon($item->user);

            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'export_list' => (new ClerkCardExport())->fieldsList(),
                'clerk_user_list' => $this->getClerkUserList(),
                'pagination' => $pagination,
            ],
        ];
    }

    private function getClerkUserList()
    {
        $clerkUserlist = ClerkUser::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->with('user.userInfo')->all();

        $newClerkUserList = [];
        /* @var ClerkUser[] $clerkUserlist */
        foreach ($clerkUserlist as $key => $clerkUser) {
            $newClerkUserItem = [];
            $newClerkUserItem['id'] = $clerkUser->user_id;
            $platform = PlatformConfig::getInstance()->getPlatformText($clerkUser->user);
            $newClerkUserItem['name'] = sprintf('(%s)%s', $platform, $clerkUser->user->nickname);
            $newClerkUserList[] = $newClerkUserItem;
        }

        return $newClerkUserList;
    }
}
