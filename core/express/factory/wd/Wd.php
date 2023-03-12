<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\factory\wd;

use app\commands\express\ECommon;
use app\core\express\core\Config;
use app\core\express\core\HttpRequest;
use app\core\express\exception\HttpException;
use app\core\express\exception\WdException;
use app\core\express\factory\ExpressExtends;
use app\core\express\factory\ExpressInterface;
use app\core\express\format\WdFormat;
use app\core\express\Interfaces\WdConfigurationConstant;
use app\validators\PhoneNumberValidator;
use GuzzleHttp\Exception\TransferException;
use yii\db\Exception;

class Wd extends ExpressExtends implements ExpressInterface, WdConfigurationConstant
{
    use HttpRequest;
    use ECommon;

    public function track(...$params)
    {
        $model = $this->rInit(...$params);
        return $model->getExpressInfo();
    }

    private function handleParams($params)
    {
        list($express_no, $express_name, $phone) = $params;
        $this->express_no = $express_no;
        $this->express_code = $this->getExpressCode($params[1]);
        if ($express_name === '顺丰速运') {
            $pattern = (new PhoneNumberValidator())->pattern;
            if ($phone && !preg_match($pattern, $phone)) {
                throw new WdException('收件人手机号错误');
            }
            $this->express_no = $this->express_no . ':' . substr($phone, -4);
        }
        //$this->mobile = $phone;
        return $this;
    }

    private function rInit(...$params)
    {
        $return = $this->handleParams($params)->serverData();
        return (new WdFormat())->injection($return);
    }

    private function serverData()
    {
        $configModel = new Config();
        $config = $configModel->setFuncName(WdConfigurationConstant::PROVIDER_NAME)->config($this->config);
        $params = [
            'n' => $this->express_no,
            't' => $this->express_code,
        ];
        $header = [
            'Authorization' => 'APPCODE ' . $config['code'],
        ];
        try {
            $response = $this->get(WdConfigurationConstant::SELECT_URL, $params, $header);
            if (isset($response['returnCode']) && $response['returnCode'] != WdConfigurationConstant::SUCCESS_STATUS) {
                throw new Exception($response['message']);
            }
            return $response;
        } catch (TransferException $e) {
            $httpCode = $e->getResponse()->getStatusCode();
            $headers = $e->getResponse()->getHeaders();
            $header = '';
            foreach ($headers as $key => $item) {
                $header .= $key . ': ' . ucwords(join(';', $item)) . "\r\n";
            }

            if ($httpCode == 400 && strpos($header, "Invalid Param Location") !== false) {
                $msg = '参数错误';
            } elseif ($httpCode == 400 && strpos($header, "Invalid AppCode") !== false) {
                $msg = "AppCode错误";
            } elseif ($httpCode == 400 && strpos($header, "Invalid Url") !== false) {
                $msg = "请求的 Method、Path 或者环境错误";
            } elseif ($httpCode == 403 && strpos($header, "Unauthorized") !== false) {
                $msg = "服务未被授权（或URL和Path不正确）";
            } elseif ($httpCode == 403 && strpos($header, "Quota Exhausted") !== false) {
                $msg = "套餐包次数用完";
            } elseif ($httpCode == 500) {
                $msg = "API网关错误";
            } elseif ($httpCode == 0) {
                $msg = "URL错误";
            } else {
                $msg = "参数名错误 或 其他错误";
                print($httpCode);
                $headers = explode("\r\n", $header);
                $headList = array();
                foreach ($headers as $head) {
                    $value = explode(':', $head);
                    $headList[$value[0]] = $value[1];
                }
                $msg = $headList['x-ca-error-message'];
            }
            throw new HttpException($msg);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function extraExpressCode()
    {
        return WdConfigurationConstant::LOGISTICS_COM_CODE_URL;
    }
}
