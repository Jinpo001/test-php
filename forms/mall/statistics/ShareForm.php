<?php


namespace app\forms\mall\statistics;


use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\ShareStatisticsExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\Model;
use app\models\Share;
use app\models\ShareCash;
use app\models\User;
use app\models\UserInfo;

class ShareForm extends Model
{
    public $name;
    public $order;

    public $page;
    public $limit;

    public $flag;
    public $fields;

    public $platform;

    public function rules()
    {
        return [
            [['flag'], 'string'],
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['name', 'order', 'platform'], 'string'],
            [['fields'], 'trim']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        $query->select("s.`user_id` as `id`,s.`user_id`,s.`name`,s.`total_money`,s.`first_children`,s.`all_children`,
        s.`all_money`,  s.`all_order`,  COALESCE(SUM(sc.`price`),0) AS `price`,`i`.`platform`")
            ->groupBy('s.`user_id`,s.`mall_id`');

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            return $this->export($new_query);
        }

        $list = $query->with('user.userInfo')
            ->page($pagination)
            ->asArray()
            ->all();

        foreach ($list as $key => $value) {
            $list[$key]['nickname'] = $value['user']['nickname'];
            $list[$key]['avatar'] = $value['user']['userInfo']['avatar'];
            unset($list[$key]['user']);
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ]
        ];
    }

    protected function where()
    {
        $query = Share::find()->alias('s')->where(['s.is_delete' => 0, 's.status' => 1, 's.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['sc' => ShareCash::tableName()], 'sc.`user_id` = s.`user_id` AND sc.`mall_id`=s.`mall_id` AND sc.`status` = 2 ')
            ->leftJoin(['i' => UserInfo::tableName()], 'i.user_id = s.user_id')
            ->leftJoin(['uu' => User::tableName()], 'uu.id = s.user_id');
        if ($this->name) {
            $query->andWhere(['or',
                ['s.user_id' => $this->name],
                ['like', 's.name', $this->name],
                ['like', 'uu.nickname', $this->name],
            ]);
        }
        //平台标识查询
        if ($this->platform) {
            $query->andWhere(['i.platform' => $this->platform]);
        }
        $query->orderBy(!empty($this->order) ? $this->order : 's.id');

        return $query;
    }


    protected function export($query)
    {
        $queueId = CommonExport::handle([
            'export_class' => 'app\\forms\\mall\\export\\ShareStatisticsExport',
            'params' => [
                'query' => $query
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
}