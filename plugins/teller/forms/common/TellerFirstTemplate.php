<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 17:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\teller\forms\common;

use app\forms\common\prints\templates\BaseTemplate;


/**
 * Class FirstTemplate
 * @package app\forms\common\prints\templates
 */
class TellerFirstTemplate extends BaseTemplate
{
    public function getContent()
    {

    }

    public function getContentByArray()
    {
        $data = $this->data;

        $show_type = \yii\helpers\BaseJson::decode($this->printer->show_type);
        switch ($this->printer->big) {
            case 1:
                $otherHandle = 'b';
                break;
            case 2:
                $otherHandle = 'dB';
                break;
            default:
                $otherHandle = 'bR';
                break;
        }
        $content = [
            [
                'handle' => 'centerBold',
                'content' => $data['store_name']
            ],
            [
                'handle' => 'bR',
                'content' => '收银员: ' . $data['cashier_name']
            ],
            [
                'handle' => 'bR',
                'content' => '起始时间: ' . $data['start_time']
            ],
            [
                'handle' => 'bR',
                'content' => '结束时间: ' . $data['end_time']
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('金额汇总:', '(元)', 24),
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('收款总额:', $data['proceeds']['total_proceeds'], 23)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('充值总额:', $data['recharge']['total_recharge'], 23)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('退款总额:', $data['refund']['total_refund'], 23)
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('单量详情:', '(单)', 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('收银:', $data['proceeds']['total_order'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('充值:', $data['recharge']['total_order'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('退款:', $data['refund']['total_order'], 26)
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('收款详情:', '(元)', 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('现金:', $data['proceeds']['cash_proceeds'],26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('微信:', $data['proceeds']['wechat_proceeds'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('支付宝:', $data['proceeds']['alipay_proceeds'], 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('余额:', $data['proceeds']['balance_proceeds'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('POS机:', $data['proceeds']['pos_proceeds'], 25)
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('充值详情:', '(元)', 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('现金:', $data['recharge']['cash_recharge'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('微信:', $data['recharge']['wechat_recharge'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('支付宝:', $data['recharge']['alipay_recharge'], 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('POS机:', $data['recharge']['pos_recharge'], 25)
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('退款详情:', '(元)', 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('现金:', $data['refund']['cash_refund'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('微信:', $data['refund']['wechat_refund'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('支付宝:', $data['refund']['alipay_refund'], 24)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('余额:', $data['refund']['balance_refund'], 26)
            ],
            [
                'handle' => 'bR',
                'content' => $this->addSpacing('POS机:', $data['refund']['pos_refund'], 26)
            ]
        ];

        return $content;
    }

    public function addSpacing($label, $value, $size = 20)
    {
        $spacing = '';
        $size = $size - strlen($value);
        for ($i=0; $i < $size; $i++) { 
            $spacing .= ' ';
        }

        return $label . $spacing . $value;
    }
}
