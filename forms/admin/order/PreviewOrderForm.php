<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\order;

use Alipay\AlipayRequestFactory;
use app\core\response\ApiCode;
use app\forms\admin\PaySettingForm;
use app\forms\admin\order\AdminWechatPay;
use app\forms\admin\order\AppPayment;
use app\forms\common\CertSN;
use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\AppManage;
use app\models\AppOrder;
use app\models\Model;
use app\models\Option;
use app\models\Order;

class PreviewOrderForm extends Model
{
    public $name;
    public $pay_type;

    public function rules()
    {
        return [
            [['name', 'pay_type'], 'required'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '应用标识',
            'pay_type' => '支付方式',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $name = $this->name;
            $Class = '\\app\\plugins\\' . $name . '\\Plugin';
            if (!class_exists($Class)) {
                throw new \Exception('插件不存在。');
            }
            $plugin = new $Class();

            $appManage = AppManage::find()->andWhere(['name' => $name, 'is_delete' => 0])->one();
            if (!$appManage) {
                throw new \Exception('插件不存在。');
            }

            if ($appManage->is_show != 1) {
                throw new \Exception('插件暂不支持购买');
            }

            $setting = (new PaySettingForm())->getOption();
            if (!in_array($this->pay_type, $setting['pay_list'])) {
                throw new \Exception('支付方式不支持');
            }

            $order = new AppOrder();
            $order->user_id = \Yii::$app->user->id;
            $order->nickname = \Yii::$app->user->identity->nickname;
            $order->name = $name;
            $order->pay_type = $this->pay_type;
            $order->app_name = $plugin->getDisplayName();
            $order->order_no = (new Order)->getOrderNo('AD');
            $order->pay_price = $appManage->price;
            $order->extra_attributes = json_encode([
                'app_manage' => $appManage,
            ]);

            $outTradeNo = (new Order)->getOrderNo('HM');
            $order->out_trade_no = $outTradeNo;

            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            $codeUrl = '';
            if ($appManage->price == 0) {
                $isSuccess = true;
                // 插件金额为为直接给予权限
                $order->is_pay = 1;
                $order->pay_time = date('Y-m-d H:i:s', time());
                $res = $order->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($order));
                }

                $adminInfo = AdminInfo::findOne(['user_id' => $order->user_id]);
                $this->setPermission($adminInfo, $order->name, $order->app_name);

            } else {
                $isSuccess = false;
                switch ($this->pay_type) {
                    case '微信':
                        $instance = AppPayment::getInstance('wechat');
                        $paymentData= [
                            'nonce_str' => md5(uniqid()),
                            'body' => $appManage->display_name,
                            'out_trade_no' => $outTradeNo,
                            'total_fee' => $appManage->price * 100,
                            'trade_type' => 'NATIVE',
                            'product_id' => $order->id,
                            'notify_url' => $instance->getNotifyUrl()
                        ];

                        $res = $instance->getService()->unifiedOrder($paymentData);
                        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
                            $extraAttributes = json_decode($order->extra_attributes, true);
                            $extraAttributes['payment_data'] = $paymentData;
                            $extraAttributes['result_data'] = $res;
                            $order->extra_attributes = json_encode($extraAttributes);
                            $orderResult = $order->save();
                            if (!$orderResult) {
                                throw new \Exception($this->getErrorMsg($order));
                            }

                            $codeUrl = $this->getGeneralQrcode(['token' => $res['code_url']]);
                        } else {
                            throw new \Exception($res['return_msg']);
                        }
                        break;

                    case '支付宝':
                        $instance = AppPayment::getInstance('alipay');
                        $aop = $instance->getService();

                        $bizContent = [
                            'out_trade_no' => $outTradeNo,
                            'total_amount' => $appManage->price,
                            'subject' => $appManage->display_name,
                        ];

                        $setting = (new PaySettingForm())->getOption();

                        $request = AlipayRequestFactory::create('alipay.trade.precreate', [
                            'notify_url' => $instance->getNotifyUrl(),
                            'biz_content' => $bizContent,
                            'app_cert_sn' => CertSN::getSn($setting['alipay_appcert']),
                            'alipay_root_cert_sn' => CertSN::getSn($setting['alipay_rootcert'], true)
                        ]);

                        $res = $aop->execute($request)->getData();
                        if ($res['code'] == '10000') {
                            $extraAttributes = json_decode($order->extra_attributes, true);
                            $extraAttributes['payment_data'] = $bizContent;
                            $extraAttributes['result_data'] = $res;
                            $order->extra_attributes = json_encode($extraAttributes);
                            $orderResult = $order->save();
                            if (!$orderResult) {
                                throw new \Exception($this->getErrorMsg($order));
                            }

                            $codeUrl = $this->getGeneralQrcode(['token' => $res['qr_code']]);
                        } else {
                            throw new \Exception($res['msg']);
                        }
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '下单成功',
                'data' => [
                    'is_success' => $isSuccess,
                    'code_url' => $codeUrl,
                    'pay_type' => $order->pay_type,
                    'order_no' => $order->order_no,
                ]
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function getGeneralQrcode($args = [])
    {
        $token = $args['token'];

        $imgName = md5(strtotime('now')) . '.jpg';
        // 获取图片存储的路径
        $res = file_uri('/web/temp/');
        $localUri = $res['local_uri'];
        $webUri = $res['web_uri'];
        $save_path = $localUri . $imgName;
        $args['width'] = $args['width'] ?? 430;
        $size = floor($args['width'] / 37 * 100) / 100 + 0.01;
        \QRcode::png($token, $save_path, QR_ECLEVEL_L, $size, 2);

        return $webUri . $imgName;
    }

    private function setPermission(AdminInfo $adminInfo, $name, $displayName)
    {
        $subjoinPermissions = json_decode($adminInfo->subjoin_permissions, true);
        if (!$subjoinPermissions) {
            $subjoinPermissions = [
                'mall' => [],
                'plugin' => [],
                'secondary' => [
                    'attachment' => [],
                    'template' => [
                        'is_all' => '0',
                        'use_all' => '0',
                        'list' => [],
                        'use_list' => []
                    ]
                ],
                'list' => []
            ];
        }

        $subjoinPermissions['plugin'][] = $name;
        $subjoinPermissions['list'][] = $displayName;

        $subjoinPermissions['plugin'] = array_unique($subjoinPermissions['plugin']);
        $subjoinPermissions['list'] = array_unique($subjoinPermissions['list']);

        $adminInfo->subjoin_permissions = json_encode($subjoinPermissions);
        $adminInfo->save();
    }
}
