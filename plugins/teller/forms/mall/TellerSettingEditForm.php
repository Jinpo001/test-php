<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\plugins\teller\forms\common\CommonTellerSetting;

class TellerSettingEditForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'string'],
        ];
    }


    public function save()
    {
        $defaultSetting = (new CommonTellerSetting())->getDefault();

        try {
            $this->data = json_decode($this->data, true);

            if (!is_array($this->data)) {
                throw new \Exception('form参数类型需为数组');
            }

            $newData = [];
            foreach ($defaultSetting as $key => $value) {
                if (is_numeric($value)) {
                    // 0 == null 需用===
                    if ($this->data[$key] === '' || $this->data[$key] === null) {
                        $this->data[$key] = $value;
                    }
                    if (!is_numeric($this->data[$key])) {
                        throw new \Exception('参数' . $key . '类型需为数字整数');
                    }
                }

                if (is_string($value)) {
                    if (!is_string($this->data[$key])) {
                        throw new \Exception('参数' . $key . '类型需为字符串');
                    }
                }

                if (!isset($this->data[$key])) {
                    throw new \Exception('参数' . $key . '未设置');
                }

                if (is_array($value)) {
                    if (!is_array($this->data[$key])) {
                        throw new \Exception('参数' . $key . '类型需为数组');
                    }
                }

                $newData[$key] = $this->data[$key];
            }

            $this->checkData($newData);

            if ($newData['is_goods_change_price_type'] == 1) {
                $newData['most_plus_percent'] = 0;
                $newData['most_subtract_percent'] = 0;
            } else {
                $newData['most_plus'] = 0;
                $newData['most_subtract'] = 0;
            }

            $setting = CommonOption::set('teller_setting', $newData, \Yii::$app->mall->id, Option::GROUP_ADMIN);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function checkData($data) 
    {
        if (count($data['tab_list']) > 10) {
            throw new \Exception('分类标签最多设置10个');
        }

        if (!in_array($data['price_type'], [1,2,3,4])) {
            throw new \Exception('抹零类型不合法');
        }

        if (!in_array($data['cashier_push_type'], [1,2])) {
            throw new \Exception('收银员提成类型不合法');
        }

        if (!in_array($data['sales_push_type'], [1,2])) {
            throw new \Exception('导购员提成类型不合法');
        }

        if (!in_array($data['is_goods_change_price_type'], [1,2])) {
            throw new \Exception('商品改价类型不合法');
        }

        if ($data['cashier_push_type'] == 2) {
            if ($data['cashier_push_percent'] < 0) {
                throw new \Exception('收银员提成不能小于0');
            }

            if ($data['cashier_push_percent'] > 100) {
                throw new \Exception('收银员提成不能大于100%');
            }
        } else {
            if ($data['cashier_push'] < 0) {
                throw new \Exception('收银员提成金额不能小于0');
            }
        }

        if ($data['sales_push_type'] == 2) {
            if ($data['sales_push_percent'] < 0) {
                throw new \Exception('导购员提成不能小于0');
            }

            if ($data['sales_push_percent'] > 100) {
                throw new \Exception('导购员提成不能大于100%');
            }
        } else {
            if ($data['sales_push'] < 0) {
                throw new \Exception('导购员提成金额不能小于0');
            }
        }

        if ($data['is_goods_change_price_type'] == 2) {
            if ($data['most_plus_percent'] < 0) {
                throw new \Exception('商品改价最多可加不能小于0');
            }

            if ($data['most_plus_percent'] > 100) {
                throw new \Exception('商品改价最多可加不能大于100%');
            }

            if ($data['most_subtract_percent'] < 0) {
                throw new \Exception('商品改价最多可减不能小于0');
            }

            if ($data['most_subtract_percent'] > 100) {
                throw new \Exception('商品改价最多可减不能大于100%');
            }
        } else {
            if ($data['most_plus'] < 0) {
                throw new \Exception('商品改价最多可加不能小于0');
            }

            $max = 9999999999;
            if ($data['most_plus'] > $max) {
                throw new \Exception('商品改价最多可加不能大于' . $max);
            }

            if ($data['most_subtract'] < 0) {
                throw new \Exception('商品改价最多可减不能小于0');
            }

            if ($data['most_subtract'] > $max) {
                throw new \Exception('商品改价最多可减不能大于' . $max);
            }
        }

        if ($data['is_wechat_pay'] == 0 && $data['is_ali_pay'] == 0 && $data['is_balance'] == 0 && $data['is_cash'] == 0 && $data['is_pos'] == 0) {
            throw new \Exception('至少添加一种支付方式');
        }
    }
}
