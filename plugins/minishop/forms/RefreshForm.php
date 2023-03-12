<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/12
 * Time: 1:56 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\minishop\models\MinishopGoods;
use app\plugins\wxapp\Plugin;
use yii\helpers\Json;

class RefreshForm extends Model
{
    public $page;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function refresh()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var $plugin Plugin */
            $plugin = \Yii::$app->plugin->getPlugin('wxapp');
            $shopService = $plugin->getShopService();
            $res = $shopService->goods->getList([
                'page' => $this->page,
                'page_size' => 20,
                'need_edit_spu' => 1,
            ]);
            if (empty($res['spus'])) {
                return $this->success([
                    'retry' => 0
                ]);
            }
            foreach ($res['spus'] as $item) {
                $minishopGoods = MinishopGoods::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'product_id' => $item['product_id'],
                    'goods_id' => $item['out_product_id'],
                ]);
                if (!$minishopGoods) {
                    continue;
                }
                $minishopGoods->audit_info = Json::encode($item['audit_info'], JSON_UNESCAPED_UNICODE);
                switch ($item['edit_status']) {
                    case 3:
                        $minishopGoods->apply_status = 3;
                        break;
                    case 4:
                        $minishopGoods->apply_status = 2;
                        break;
                    default:
                        $minishopGoods->apply_status = 1;
                }
                switch ($item['status']) {
                    case 5:
                        $minishopGoods->status = 1;
                        break;
                    case 9:
                        $minishopGoods->is_delete = 1;
                        break;
                    default:
                        $minishopGoods->status = 0;
                }
                if (!$minishopGoods->save()) {
                    throw new \Exception($this->getErrorMsg($minishopGoods));
                }
            }
            $this->page++;
            return $this->success([
                'retry' => 1,
                'page' => $this->page
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }
}
