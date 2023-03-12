<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/18
 * Time: 15:26
 */

namespace app\forms\mall\share;


use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\share\CommonShareLevel;
use app\forms\common\share\CommonShareTeam;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\ShareUserExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\Model;
use app\models\Share;
use app\models\ShareSetting;
use app\models\User;
use app\models\UserInfo;
use app\models\UserPlatform;
use yii\helpers\ArrayHelper;

class IndexForm extends Model
{
    public $keyword;
    public $status;
    public $platform;

    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level;

    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'status', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['status', 'limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['status'], 'default', 'value' => -1],
            [['sort'], 'default', 'value' => ['s.status' => SORT_ASC, 's.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = Share::find()->alias('s')->with(['userInfo', 'user.userPlatform', 'order'])
            ->where(['s.is_delete' => 0, 's.mall_id' => $mall->id])
            ->groupBy('user_id')
            ->leftJoin(['u' => User::tableName()], 'u.id = s.user_id')
            ->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = s.user_id');

        if ($this->platform) {
            $userPlatformQuery = UserPlatform::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'platform' => $this->platform])
                ->select('user_id');
            $query->keyword(
                $this->platform,
                [
                    'or',
                    ['ui.platform' => $this->platform],
                    ['s.user_id' => $userPlatformQuery]
                ]
            );
        }

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 's.name', $this->keyword],
                ['like', 'BINARY(u.nickname)', $this->keyword],
                ['like', 'u.id', $this->keyword],
                ['like', 's.mobile', $this->keyword],
            ]);
        }

        switch ($this->status) {
            case 0:
                $query->andWhere(['s.status' => 0]);
                break;
            case 1:
                $query->andWhere(['s.status' => 1]);
                break;
            case 2:
                $query->andWhere(['s.status' => 2]);
                break;
            default:
                break;
        }


        if ($this->level !== '' && $this->level !== null) {
            $query->andWhere(['s.level' => $this->level]);
        }


        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $queueId = CommonExport::handle([
                'export_class' => 'app\\forms\\mall\\export\\ShareUserExport',
                'params' => [
                    'query' => $new_query,
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

        $list = $query->page($pagination, $this->limit, $this->page)
            ->with(['userInfo', 'shareLevel'])
            ->orderBy($this->sort)->all();
        $level = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::LEVEL, 0);
        $default_level_name = ShareSetting::get(\Yii::$app->mall->id, ShareSetting::DEFAULT_LEVEL_NAME, ShareSetting::INFO[ShareSetting::DEFAULT_LEVEL_NAME]);
        $newList = [];

        $customize = new ShareCustomForm();
        $return = $customize->getData();

        /* @var Share[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;
            /* @var UserInfo $userInfo */
            $userInfo = $item->userInfo;

            $form = new CommonShareTeam();
            $form->mall = \Yii::$app->mall;
            $userPlatformDetail = PlatformConfig::getInstance()->getPlatformDetail($user);

            $newItem = array_merge($newItem, [
                'nickname' => $user ? $user->nickname : '',
                'avatar' => $userInfo->avatar,
                'parent_name' => !$userInfo->parent ? $return['data']['words']['head_office']['name'] ?: '总店' : $userInfo->parent->nickname,
                'platform' => $userPlatformDetail['platform'],
                'platform_icon' => $userPlatformDetail['platform_icon']
            ]);
            if ($level > 0) {
                $newItem['first'] = count($form->info($item->user_id, 1));
                if ($level > 1) {
                    $newItem['second'] = count($form->info($item->user_id, 2));
                    if ($level > 2) {
                        $newItem['third'] = count($form->info($item->user_id, 3));
                    }
                }
            }
//            $newItem['userInfo'] = ArrayHelper::toArray($item->userInfo);
            $newItem['level_name'] = $item->shareLevel ? $item->shareLevel->name : $default_level_name;
            $newItem['form'] = \yii\helpers\BaseJson::decode($item['form']);
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new ShareUserExport())->fieldsList(),
                'shareLevelList' => CommonShareLevel::getInstance()->getList(),
            ]
        ];
    }
}
