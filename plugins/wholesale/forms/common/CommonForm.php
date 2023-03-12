<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2020/7/20
 * Email: <657268722@qq.com>
 */

namespace app\plugins\wholesale\forms\common;

use app\models\Model;

class CommonForm extends Model
{
    public static function getWholesalePrice(&$item)
    {
        $price = [];
        $level_price = [];
        $section = [];
        $sectionCommon = [];
        if ($item['wholesaleGoods']['rules_status'] == 1) {
            $item['wholesaleGoods']['wholesale_rules'] = $rules_arr = json_decode(
                $item['wholesaleGoods']['wholesale_rules'],
                true
            );
            foreach ($rules_arr as $rule) {
                //起批数超过最低优惠规则要求数，并且最低优惠规则要求数大于1，则不执行加入商品原价
                if ($item['wholesaleGoods']['rise_num'] > $rule['num'] && $rule['num'] > 1) {
                    continue;
                }
                $tempPrice = $item['price'];
                if (isset($item['price_min'])) {
                    $tempPrice = $item['price_min'];
                }
                switch ($item['wholesaleGoods']['type']) {
                    case 0:
                        array_push($price, price_format($tempPrice * $rule['discount'] / 10));
                        $sectionCommon[] = [
                            'num' => $rule['num'],
                            'price' => round($tempPrice * $rule['discount'] / 10, 2)
                        ];
                        if (isset($item['level_show']) && $item['level_show'] == 1) {
                            array_push($level_price, price_format($item['level_price'] * $rule['discount'] / 10));
                            $section[] = [
                                'num' => $rule['num'],
                                'price' => round($item['level_price'] * $rule['discount'] / 10, 2)
                            ];
                        }
                        break;
                    case 1:
                        $minPrice = min($tempPrice, $rule['discount']);
                        array_push($price, price_format($tempPrice - $minPrice));
                        $sectionCommon[] = [
                            'num' => $rule['num'],
                            'price' => round($tempPrice - $minPrice, 2)
                        ];
                        if (isset($item['level_show']) && $item['level_show'] == 1) {
                            $minPrice = min($item['level_price'], $rule['discount']);
                            array_push($level_price, price_format($item['level_price'] - $minPrice));
                            $section[] = [
                                'num' => $rule['num'],
                                'price' => round($item['level_price'] - $minPrice, 2)
                            ];
                        }
                        break;
                }
            }
        }
        if ($level_price && isset($item['level_price']) && $item['level_price'] && $item['is_level']) {
            $item['section'] = $section;
        } else {
            $item['section'] = $sectionCommon;
        }

        if (count($item['section']) == 2) {
            if ($item['section'][0]['num'] == ($item['section'][1]['num'] - 1)) {
                $item['section'][0]['display_num'] = $item['section'][0]['num'];
            } else {
                $item['section'][0]['display_num'] = $item['section'][0]['num'] . '-' . ($item['section'][1]['num'] - 1);
            }
            $item['section'][1]['display_num'] = "≥" . $item['section'][1]['num'];
        }

        if (count($item['section']) == 3) {
            if ($item['section'][0]['num'] == ($item['section'][1]['num'] - 1)) {
                $item['section'][0]['display_num'] = $item['section'][0]['num'];
            } else {
                $item['section'][0]['display_num'] = $item['section'][0]['num'] . '-' . ($item['section'][1]['num'] - 1);
            }
            if ($item['section'][1]['num'] == ($item['section'][2]['num'] - 1)) {
                $item['section'][1]['display_num'] = $item['section'][1]['num'];
            } else {
                $item['section'][1]['display_num'] = $item['section'][1]['num'] . '-' . ($item['section'][2]['num'] - 1);
            }
            $item['section'][2]['display_num'] = "≥" . $item['section'][2]['num'];
        }

        if ($price) {
            $item['price_section'] = [
                'min_price' => min($price),
                'max_price' => max($price),
            ];
        } else {
            $item['price_section'] = [];
        }

        if ($level_price) {
            $item['level_price_section'] = [
                'min_level_price' => min($level_price),
                'max_level_price' => max($level_price),
            ];
        } else {
            $item['level_price_section'] = [];
        }

        $item['price'] = $item['price_section']['min_price'] ?? $item['price_min'] ?? $item['price'];
        if (isset($item['level_show']) && $item['level_show'] == 1) {
            $item['level_price'] = $item['level_price_section']['min_level_price'] ?? $item['level_price'];
        }
        $item['price_content'] = self::getPriceContent($item['is_negotiable'] ?? 0, $item['price']);
    }

    //todo 暂时先这么处理
    private static function getPriceContent($isNegotiable, $minPrice)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($minPrice > 0) {
            $priceContent = '￥' . $minPrice;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }
}
