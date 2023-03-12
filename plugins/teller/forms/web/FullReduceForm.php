<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\forms\common\full_reduce\CommonActivity;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\teller\forms\common\CommonTellerSetting;

class FullReduceForm extends Model
{
    public $sort;
    public $sort_type;
    public $keyword;
    public $page;
    public $cat_id;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['cat_id', 'keyword', 'sort', 'sort_type'], 'integer'],
        ];
    }

    //GET
    public function search()
    {
        $info = CommonActivity::getActivityMarket();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'data' => $info
            ]
        ];
    }

    public function getGoodsList()
    {
        try {
            $setting = (new CommonTellerSetting())->search();
            $form = new CommonGoodsList();
            $form->sign = [''];
            $form->sort = $this->sort;
            $form->status = 1;
            $form->sort_type = $this->sort_type;
            $form->keyword = $this->keyword;
            $form->page = $this->page;
            $form->cat_id = $this->cat_id;
            $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
            $form->relations = ['goodsWarehouse', 'mallGoods', 'attr'];
            $form->is_full_reduce = true;
            $form->is_del_ecard = true;
            $form->user = User::findOne($setting['user_id']);
            $list = $form->getList();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
