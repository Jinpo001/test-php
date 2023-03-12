<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 16:11
 */

namespace app\controllers\system;

use app\controllers\Controller;
use app\forms\common\order\send\city_service\CityServiceForm;
use app\forms\common\order\send\city_service\mk\Mk;
use app\forms\common\wechat\WechatFactory;
use app\models\CityService;
use app\models\Mall;
use app\models\Model;
use app\models\OrderDetailExpress;
use app\models\UserPlatform;
use app\models\VideoNumber;
use luweiss\Wechat\WechatHelper;
use yii\helpers\Json;
use yii\web\Response;

class MsgNotifyController extends Controller
{
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    public function actionCityService()
    {
        \Yii::error('微信事件推送接口回调');
        if (isset($_GET['mall_id'])) {
            $mall = Mall::findOne($_GET['mall_id']);
            \Yii::$app->setMall($mall);
        }

        // 验签
        if (!$this->checkSignature()) {
            return 'success';
        }

        // 微信第一次配置时需校验
        if (isset($_GET["echostr"]) && $_GET["echostr"]) {
            return $_GET['echostr'];
        }

        // yii 框架方式处理方式 | php获取json数据方式 file_get_contents('php://input')
        $rawBody = \Yii::$app->request->rawBody;

        $xmlDataArray = $this->getXmlData($rawBody);
        $app = WechatFactory::create();

        if (isset($xmlDataArray['Event'])) {
            \Yii::error($xmlDataArray);
            switch ($xmlDataArray['Event']) {
                // 群发回调事件
                case 'MASSSENDJOBFINISH':
                    $this->updateSph($xmlDataArray);
                    break;
                // 用户关注事件
                case 'subscribe':
                    $res = UserPlatform::updateAll(['subscribe' => 1], ['platform_id' => $xmlDataArray['FromUserName']]);
                    \Yii::warning($res);
                    if ($messageArr = $app->subscribeReply($xmlDataArray)) {
                        \Yii::$app->response->format = Response::FORMAT_XML;
                        \Yii::warning(Json::encode($messageArr, JSON_UNESCAPED_UNICODE));
                        echo WechatHelper::arrayToXml($messageArr);
                        return 'success';
                    }
                    break;
                // 用户取消关注事件
                case 'unsubscribe':
                    $res = UserPlatform::updateAll(['subscribe' => 0], ['platform_id' => $xmlDataArray['FromUserName']]);
                    \Yii::warning($res);
                    break;
                // 自定义菜单点击事件
                case 'CLICK':
                    $res = $app->menuReply($xmlDataArray);
                    \Yii::warning($res);
                    break;
            }
        }

        $jsonDataArray = json_decode($rawBody, true);
        if (isset($jsonDataArray['Event'])) {
            \Yii::error($jsonDataArray);
            switch ($jsonDataArray['Event']) {
                // 同城配送推送事件
                case 'update_waybill_status':
                    $this->updateExpress($jsonDataArray);
                    break;
            }
        }
        if (isset($xmlDataArray['MsgType'])) {
            switch ($xmlDataArray['MsgType']) {
                case 'text':
                    $res = $app->keywordReply($xmlDataArray);
                    \Yii::warning($res);
                    break;
                default:
            }
        }

        return "success";
    }

    private function getXmlData($xml)
    {
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($obj), true);

        return $data;
    }

    private function checkSignature()
    {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : '';
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';

        if (!\Yii::$app->mall) {
            return false;
        }
        $config = WechatFactory::create()->getServer();
        if (!$config) {
            return false;
        }
        $token = $config['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            \Yii::error('验签通过');
            return true;
        } else {
            \Yii::error('验签不通过');
            return false;
        }
    }

    // 更新视频号数据
    private function updateSph($data)
    {
        try {
            $videoNumber = VideoNumber::find()
                ->andWhere(['msg_id' => $data['MsgID']])
                ->andWhere(['>=', 'created_at', date('Y-m-d H:i:s', $data['CreateTime'] - 600)])
                ->andWhere(['<=', 'created_at', date('Y-m-d H:i:s', $data['CreateTime'] + 600)])
                ->one();

            if ($videoNumber) {
                $extraAttributes = json_decode($videoNumber->extra_attributes, true);
                $extraAttributes['event_result'] = $data;
                $videoNumber->extra_attributes = json_encode($extraAttributes);
                $videoNumber->status = $data['Status'];
                $videoNumber->save();
            }
        } catch (\Exception $exception) {
            \Yii::error('群发消息回调出错');
            \Yii::error($exception);
        }
    }

    // 更新快递小哥信息
    private function updateExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['shop_order_id']])->one();
            if ($express) {
                // 骑手接单
                if ($data['order_status'] == 102) {
                    $express->city_name = $data['agent']['name'];
                    $express->city_mobile = $data['agent']['phone'];
                }
                $cityInfo = json_decode($express->city_info, true);
                $cityInfo[$data['order_status']] = $data;
                $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
                $express->status = $data['order_status'];
                $res = $express->save();
                if (!$res) {
                    throw new \Exception((new Model)->getErrorMsg($express));
                }
            }
        } catch (\Exception $exception) {
            \Yii::error('同城配送回调出错');
            \Yii::error($exception);
        }
    }

    public function actionSf()
    {
        \Yii::error('顺丰接口回调');
        // php获取json数据方式 file_get_contents('php://input')
        // yii 框架方式
        $data = \Yii::$app->request->post();
        \Yii::error($data);
        $this->updateSfExpress($data);
        $responseData = [
            'error_code' => 0,
            'error_msg' => 'success',
        ];
        \Yii::$app->response->data = $responseData;
    }

    // 更新快递小哥信息
    private function updateSfExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['shop_order_id']])->one();
            if (!$express) {
                throw new \Exception('顺风同城未找到记录');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 10) {
                $express->city_name = $data['operator_name'];
                $express->city_mobile = $data['operator_phone'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeBySf($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeBySf($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('同城配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeBySf($code)
    {
        switch ($code) {
            case '10':
                return 102;
            case '12':
                return 202;
            case '15':
                return 301;
            case '17':
                return 302;
            default:
                return $code;
        }
    }

    public function actionSs()
    {
        \Yii::error('闪送接口回调');
        $data = file_get_contents('php://input');
        \Yii::error($data);
        $data = json_decode($data, true);
        \Yii::error($data);
        $this->updateSsExpress($data);
    }

    // 更新快递小哥信息
    private function updateSsExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['orderNo']])->one();
            if (!$express) {
                throw new \Exception('闪送同城未找到记录');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('闪送配送配置不存在');
            }

            // 骑手接单
            if ($data['status'] == 30) {
                $express->city_name = $data['courier']['name'];
                $express->city_mobile = $data['courier']['mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeBySs($data['status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeBySs($data['status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }
        } catch (\Exception $exception) {
            \Yii::error('闪送同城配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeBySs($code)
    {
        switch ($code) {
            case '30':
                return 102;
            case '40':
                return 202;
            case '50':
                return 302;
            default:
                return $code;
        }
    }

    // 达达接口回调
    public function actionDadaCityService()
    {
        \Yii::error('达达接口回调');
        $json = \Yii::$app->request->rawBody;
        $data = json_decode($json, true);
        \Yii::error($data);

        $this->updateDadaExpress($data);
        return "success";
    }

    private function updateDadaExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['order_id']])->one();
            if (!$express) {
                throw new \Exception('达达订单物流不存在');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 2) {
                $express->city_name = $data['dm_name'];
                $express->city_mobile = $data['dm_mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeByDada($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeByDada($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('达达配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeByDada($code)
    {
        switch ($code) {
            case '2':
                return 102;
            case '3':
                return 202;
            case '4':
                return 302;
            default:
                return $code;
        }
    }

    // 美团接口回调
    public function actionMtCityService()
    {
        \Yii::error('美团接口回调');
        $data = \Yii::$app->request->post();
        \Yii::error($data);

        $this->updateMtExpress($data);
        return "success";
    }

    private function updateMtExpress($data)
    {
        try {
            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['order_id']])->one();
            if (!$express) {
                throw new \Exception('美团订单物流不存在');
            }
            $mall = Mall::findOne($express->mall_id);
            if (!$mall) {
                throw new \Exception('未查询到id=' . $express->mall_id . '的商城。 ');
            }
            \Yii::$app->setMall($mall);

            $server = CityService::findOne($express->city_service_id);
            if (!$server) {
                throw new \Exception('配送配置不存在');
            }

            // 骑手接单
            if ($data['order_status'] == 2) {
                $express->city_name = $data['dm_name'];
                $express->city_mobile = $data['dm_mobile'];
            }
            $cityInfo = json_decode($express->city_info, true);
            $cityInfo[$this->transCodeByMt($data['order_status'])] = $data;
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $express->status = $this->transCodeByMt($data['order_status']);
            $res = $express->save();
            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

        } catch (\Exception $exception) {
            \Yii::error('美团配送回调出错');
            \Yii::error($exception);
            exit;
        }
    }

    private function transCodeByMt($code)
    {
        switch ($code) {
            case '20':
                return 102;
            case '30':
                return 202;
            case '50':
                return 302;
            default:
                return $code;
        }
    }

    public function actionMk()
    {
        try {
            \Yii::error('同城速送接口回调');
            $data = json_decode(\Yii::$app->request->rawBody, true);
            \Yii::error($data);

            $express = OrderDetailExpress::find()->andWhere(['shop_order_id' => $data['order_no']])->one();
            if (!$express) {
                throw new \Exception('同城速送同城配送未找到记录');
            }

            $cityService = CityService::findOne($express->city_service_id);
            if (!$cityService) {
                throw new \Exception('同城速送同城配送配置不存在');
            }

            $cityServiceForm =  new CityServiceForm($cityService);

            $model = $cityServiceForm->getModel();
            $cityServiceData = json_decode($cityService->data, true);
            $url = $cityServiceData['domain'];
            $appsecret = $cityServiceData['appsecret'];
            $appkey = $cityServiceData['appkey'];

            $result = $cityServiceForm->getInstance()->getOrder([
                'token' => $model::getToken($cityService->mall_id, $url, $appsecret, $appkey),
                'order_num' => $data['order_no']
            ]);

            if (!$result->isSuccessful()) {
                throw new \Exception($result->getMessage());
            }

            $res = $result->getOriginalData();
            \Yii::error($res);
            if ($res['error_code'] != 0) {
                throw new \Exception($res['msg']);
            }

            $express->city_name = $data['rider_name'];
            $express->city_mobile = $data['rider_mobile'];

            $cityInfo = json_decode($express->city_info, true);
            $status = $res['data']['status'];

            switch ($status) {
                case 'accepted':
                    $express->status = 102;
                    $cityInfo[102] = $res;
                    break;
                case 'geted':
                    $express->status = 202;
                    $cityInfo[302] = $res;
                    break;
                case 'gotoed':
                    $express->status = 302;
                    $cityInfo[302] = $res;
                    break;
                default:
                    $cityInfo[$status] = $res;
                    break;
            }
            $express->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
            $res = $express->save();

            if (!$res) {
                throw new \Exception((new Model)->getErrorMsg($express));
            }

            return 'success';
        }catch(\Exception $exception) {
            \Yii::error($exception);
        }
    }
}
