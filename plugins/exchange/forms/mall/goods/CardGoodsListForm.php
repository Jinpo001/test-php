<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\goods;


use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\exchange\forms\mall\export\GoodsExport;
use app\plugins\exchange\models\ExchangeGoods;
use app\plugins\exchange\models\Goods;

class CardGoodsListForm extends BaseGoodsList
{
    public $choose_list;
    public $flag;

    public function __construct($config = [])
    {
        $this->plugin = 'exchange';
        $this->goodsModel = Goods::className();
        parent::__construct($config);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['flag'], 'string'],
            [['choose_list'], 'safe'],
        ]);
    }

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    //todo
    protected function setQuery($query)
    {
        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }

        if (isset($search['library_name']) && !empty($search['library_name'])) {
            $goods_ids = ExchangeGoods::find()->alias('g')->select('g.goods_id')->where([
                'g.mall_id' => \Yii::$app->mall->id,
                'g.is_delete' => 0,
            ])->innerJoinWith(['library l' => function ($query) use ($search) {
                $query->where(['like', 'l.name', $search['library_name']])
                    ->orWhere(['like', 'l.id', $search['library_name']]);
            }])->column();
            $query->andWhere(['in', 'id', $goods_ids]);
        }

        $query->andWhere([
            'g.sign' => 'exchange',
            'g.mch_id' => 0,
        ])->with(['mallGoods', 'library']);
        if ($this->flag == "EXPORT") {
            if ($this->choose_list && count($this->choose_list) > 0) {
                $query->andWhere(['g.id' => $this->choose_list]);
            }
            $new_query = clone $query;
            $queueId = CommonExport::handle([
                'export_class' => 'app\\plugins\\exchange\\forms\\mall\\export\\GoodsExport',
                'params' => [
                    'query' => $new_query,
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

        return $query;
    }

    public function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['cardGoods'] = [];
        $newItem['cardGoods']['library_id'] = $goods->library->id;
        $newItem['cardGoods']['library_name'] = $goods->library->name;
        return $newItem;
    }
}
