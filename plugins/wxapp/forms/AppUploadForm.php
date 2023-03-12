<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/26
 * Time: 11:11
 */

namespace app\plugins\wxapp\forms;


use app\core\cloud\CloudNotLoginException;
use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\WxappPlatform;
use app\plugins\wxapp\models\WxappConfig;
use app\plugins\wxapp\models\WxappJumpAppid;

class AppUploadForm extends Model
{
    public $action;
    public $branch;
    public $is_platform;

    public function rules()
    {
        return [
            ['action', 'required'],
            ['branch', 'safe'],
            [['is_platform'], 'default', 'value' => 0],
        ];
    }

    public function getResponse()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }
        try {
            switch ($this->action) {
                case 'login':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->login(),
                    ];
                    break;
                case 'preview':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->preview(),
                    ];
                    break;
                case 'upload':
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => $this->upload(),
                    ];
                    break;
                default:
                    break;
            }
        } catch (CloudNotLoginException $exception) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage(),
                'data' => [
                    'retry' => 1,
                ],
            ];
        } catch (\Exception $exception) {
            $msg = $exception->getMessage();
            if (mb_stripos($msg, 'PORT_NOT_EXIST') !== false) {
                $this->unsetToken();
            }
            $msg = str_replace('[undefined]', '', $msg);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $msg,
            ];
        }
    }

    public function login()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
            'is_platform' => $this->is_platform,
        ];
        $this->setJumpApppid($data);
        $this->setOptions($data);
        return \Yii::$app->cloud->wxapp->login($data);
    }

    public function preview()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
        ];
        $this->setJumpApppid($data);
        $this->setOptions($data);
        return \Yii::$app->cloud->wxapp->preview($data);
    }

    public function upload()
    {
        $data = [
            'api_root' => $this->getApiRoot(),
            'appid' => $this->getAppId(),
            'token' => $this->getToken(),
            'version' => app_version(),
            'protocol' => $this->getProtocol(),
            'branch' => $this->branch,
            'is_platform' => $this->is_platform,
        ];
        $this->setJumpApppid($data);
        $this->setOptions($data);
        $res = \Yii::$app->cloud->wxapp->upload($data);
        $res['version'] = app_version();
        return $res;
    }

    private function getApiRoot()
    {
        if ($this->is_platform) {
            return \Yii::$app->request->scriptUrl . '?_mall_id=';
        }
        return \Yii::$app->request->scriptUrl . '?_mall_id=' . \Yii::$app->mall->id;
    }

    private function getAppId()
    {
        if ($this->is_platform) {
            $platform = WxappPlatform::getPlatform(2);
            if (!$platform) {
                throw new \Exception('第三方平台信息尚未配置');
            }
            if (!$platform->third_appid) {
                throw new \Exception('未绑定第三方平台开发小程序appid');
            }
            return $platform->third_appid;
        }
        $wxappConfig = WxappConfig::findOne(['mall_id' => \Yii::$app->mall->id]);
        if (!$wxappConfig) {
            throw new \Exception('小程序信息尚未配置。');
        }
        if (!$wxappConfig->appid) {
            throw new \Exception('小程序AppId尚未配置。');
        }
        return $wxappConfig->appid;
    }

    private function getToken()
    {
        $key = 'WXAPP_UPLOAD_TOKEN';
        if ($this->is_platform) {
            $key = 'WXAPP_UPLOAD_TOKEN_BY_PLATFORM';
        }
        $token = \Yii::$app->session->get($key);
        if (!$token) {
            $token = \Yii::$app->security->generateRandomString();
            \Yii::$app->session->set($key, $token);
        }
        return $token;
    }

    private function unsetToken()
    {
        $key = 'WXAPP_UPLOAD_TOKEN';
        if ($this->is_platform) {
            $key = 'WXAPP_UPLOAD_TOKEN_BY_PLATFORM';
        }
        \Yii::$app->session->remove($key);
    }

    private function getProtocol()
    {
        return 'https';
    }

    private function setJumpApppid(&$data)
    {
        if (!$this->is_platform) {
            $list = WxappJumpAppid::find()->where(['mall_id' => \Yii::$app->mall->id])->all();
            $newList = [];
            foreach ($list as $index => $item) {
                $newList[] = $item->appid;
                $data['jump_appid_list[' . $index . ']'] = $item->appid;
            }
        }
    }

    private function setOptions(&$data)
    {
        $options = [];
        if ($this->is_platform) {
            $pluginList = [];
        } else {
            $pluginList = (array)CommonOption::get('wxapp_enable_plugins', \Yii::$app->mall->id, 'plugin', []);
        }
        $options['plugins'] = is_array($pluginList) && count($pluginList) ? $pluginList : [];
        $data['options'] = json_encode($options, JSON_UNESCAPED_UNICODE);
    }
}
