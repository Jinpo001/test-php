<?php

namespace app\forms\open3rd;

use app\forms\common\wechat\WechatFactory;
use Curl\Curl;
use yii\base\BaseObject;

/**
 * 第三方平台代小程序实现业务
 * Class ExtApp
 * @package app\forms\open3rd
 */
class ExtApp extends BaseObject
{
    public $is_platform = 0;
    /**@var string $thirdAppId 开放平台appid**/
    public $thirdAppId;
    /**@var string $thirdToken 开放平台token**/
    public $thirdToken;
    /**@var string $thirdAccessToken 开放平台access_token**/
    public $thirdAccessToken;
    /**@var string $authorizer_appid 授权给第三方平台的小程序appid**/
    public $authorizer_appid;
    /**@var string $authorizer_appid 授权给第三方平台的小程序access_token**/
    public $authorizer_access_token;

    public $plugin = 'wxapp';

    public function init()
    {
        if (!$this->thirdAppId) {
            throw new Open3rdException('thirdAppId not null');
        }
        if (!$this->thirdToken) {
            throw new Open3rdException('thirdToken not null');
        }
        if (!$this->thirdAccessToken) {
            throw new Open3rdException('thirdAccessToken not null');
        }
        if (!$this->is_platform && !$this->authorizer_appid) {
            throw new Open3rdException('authorizer_appid not null');
        }
        if (!$this->is_platform && !$this->authorizer_access_token) {
            if ($this->plugin == 'wechat') {
                $this->authorizer_access_token = WechatFactory::create()->accessToken;
            } else {
                $plugin = \Yii::$app->plugin->getPlugin($this->plugin);
                $this->authorizer_access_token = $plugin->getAccessToken();
            }
        }
        parent::init();
    }

    /**
     * 获取授权方的帐号基本信息
     * @return bool
     * @throws Open3rdException
     */
    public function getAuthorizerInfo()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=" . $this->thirdAccessToken;
        $data = json_encode([
            'component_appid' => $this->thirdAppId,
            'authorizer_appid' => $this->authorizer_appid
        ]);
        $ret = json_decode($this->getCurl()->post($url, $data)->response, true);
        if (isset($ret['authorizer_info'])) {
            return $ret;
        } else {
            $this->errorLog("获取授权方的帐号基本信息操作失败,appid:" . $this->authorizer_appid . $ret['errmsg'], $ret);
            return false;
        }
    }

    /**
     * 设置小程序服务器地址
     * @param string $domain
     * @return bool
     */
    public function setServerDomain($data)
    {
        $url = "https://api.weixin.qq.com/wxa/modify_domain?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("设置小程序服务器地址失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 设置小程序业务域名
     * @param string $domain
     * @return bool
     */
    public function setBusinessDomain($data)
    {
        $url = "https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("设置小程序业务域名失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 成员管理，绑定小程序体验者
     * @params string $wechatid : 体验者的微信号
     * */
    public function bindMember($wechatid)
    {
        $url = "https://api.weixin.qq.com/wxa/bind_tester?access_token=" . $this->authorizer_access_token;
        $data = '{"wechatid":"' . $wechatid . '"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("绑定小程序体验者操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 成员管理，解绑定小程序体验者
     * @params string $wechatid : 体验者的微信号
     * */
    public function unBindMember($wechatid)
    {
        $url = "https://api.weixin.qq.com/wxa/unbind_tester?access_token=" . $this->authorizer_access_token;
        $data = '{"wechatid":"' . $wechatid . '"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("解绑定小程序体验者操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
    * 成员管理，获取小程序体验者列表
    * */
    public function listMember()
    {
        $url = "https://api.weixin.qq.com/wxa/memberauth?access_token=" . $this->authorizer_access_token;
        $data = '{"action":"get_experiencer"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return $ret->members;
        } else {
            $this->errorLog("获取小程序体验者列表操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 获取代码模板列表
     * @return mixed
     * @throws Open3rdException
     */
    public function templateList()
    {
        $url = "https://api.weixin.qq.com/wxa/gettemplatelist?access_token=" . $this->thirdAccessToken;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("获取代码模板列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 删除指定代码模板
     * @param $template_id
     * @return mixed
     * @throws Open3rdException
     */
    public function deletetemplate($template_id)
    {
        $url = "https://api.weixin.qq.com/wxa/deletetemplate?access_token=" . $this->thirdAccessToken;
        $data = '{"template_id":"' . $template_id . '"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("获取代码模板列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 获取代码草稿列表
     * @return mixed
     * @throws Open3rdException
     */
    public function templatedraftlist()
    {
        $url = "https://api.weixin.qq.com/wxa/gettemplatedraftlist?access_token=" . $this->thirdAccessToken;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("获取代码草稿列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 将草稿添加到代码模板库
     * @param $draft_id
     * @return mixed
     * @throws Open3rdException
     */
    public function addtotemplate($draft_id)
    {
        $url = "https://api.weixin.qq.com/wxa/addtotemplate?access_token=" . $this->thirdAccessToken;
        $data = '{"draft_id":"' . $draft_id . '"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("将草稿添加到代码模板库失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 为授权的小程序帐号上传小程序代码
     * @params int $template_id : 模板ID
     * @params json $ext_json : 小程序配置文件，json格式
     * @params string $user_version : 代码版本号
     * @params string $user_desc : 代码描述
     * */
    public function uploadCode($template_id, $is_plugin = 0, $user_version = 'v1.0.0', $user_desc = "小程序模板库")
    {
        $live = [
            "live-player-plugin" => [
                "version" =>  "1.2.8",
                "provider" => "wx2b03c6e691cd7370"
            ]
        ];
        $plugin = $is_plugin ? $live : (object)[];
        $ext = [
            'extEnable' => true,
            'extAppid' => $this->authorizer_appid,
            'ext' => [
                'mall_id' => \Yii::$app->mall->id,
            ],
            'plugins' => $plugin
        ];
        $ext_json = json_encode($ext);
        $url = "https://api.weixin.qq.com/wxa/commit?access_token=" . $this->authorizer_access_token;
        $data = json_encode([
            'template_id' => $template_id,
            'ext_json' => $ext_json,
            'user_version' => $user_version,
            'user_desc' => $user_desc
        ], JSON_UNESCAPED_UNICODE);
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("为授权的小程序帐号上传小程序代码操作失败,appid:" . $this->authorizer_appid . $ret->errmsg . $ret->errcode, $ret);
            return false;
        }
    }

    /**
     * 获取体验小程序的体验二维码
     * @params string $path :   指定体验版二维码跳转到某个具体页面
     * */
    public function getExpVersion($path = '')
    {
        if ($path) {
            $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token=" . $this->authorizer_access_token . "&path=" . urlencode(
                $path
            );
        } else {
            $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token=" . $this->authorizer_access_token;
        }
        $ret = json_decode($this->getCurl()->get($url)->response);
        if (isset($ret->errcode)) {
            $this->errorLog("获取体验小程序的体验二维码操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        } else {
            return $this->getCurl()->get($url)->response;
        }
    }

    /**
     * 提交审核
     * @params string $tag : 小程序标签，多个标签以空格分开
     * @params strint $title : 小程序页面标题，长度不超过32
     * */
    public function submitReview($tag = "商城", $title = "小程序开发")
    {
        $first_class = '';
        $second_class = '';
        $first_id = 0;
        $second_id = 0;
        $address = "pages/index/index";
        $category = $this->getCategory();
        if (!empty($category)) {
            $first_class = $category[0]->first_class ? $category[0]->first_class : '';
            $second_class = $category[0]->second_class ? $category[0]->second_class : '';
            $first_id = $category[0]->first_id ? $category[0]->first_id : 0;
            $second_id = $category[0]->second_id ? $category[0]->second_id : 0;
        }
        $getpage = $this->getPage();
        if (!empty($getpage) && isset($getpage[0])) {
            $address = $getpage[0];
        }
        $url = "https://api.weixin.qq.com/wxa/submit_audit?access_token=" . $this->authorizer_access_token;
        $data = '{
                "item_list":[{
                    "address":"' . $address . '",
                    "tag":"' . $tag . '",
                    "title":"' . $title . '",
                    "first_class":"' . $first_class . '",
                    "second_class":"' . $second_class . '",
                    "first_id":"' . $first_id . '",
                    "second_id":"' . $second_id . '"
                }]
            }';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return $ret->auditid;
        } else {
            $this->errorLog("小程序提交审核操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 小程序审核撤回
     * 单个帐号每天审核撤回次数最多不超过1次，一个月不超过10次。
     * */
    public function unDoCodeAudit()
    {
        $url = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("小程序审核撤回操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 查询指定版本的审核状态
     * @params string $auditid : 提交审核时获得的审核id
     * */
    public function getAuditStatus($auditid)
    {
        $url = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token=" . $this->authorizer_access_token;
        $data = '{"auditid":"' . $auditid . '"}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询指定版本的审核状态操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 查询最新一次提交的审核状态
     * */
    public function getLastAudit()
    {
        $url = "https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询最新一次提交的审核状态操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 发布已通过审核的小程序
     * */
    public function release()
    {
        $url = "https://api.weixin.qq.com/wxa/release?access_token=" . $this->authorizer_access_token;
        $data = '{}';
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("发布已通过审核的小程序操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return $ret->errcode;
        }
    }

    /**
     * 获取授权小程序帐号的可选类目
     * */
    private function getCategory()
    {
        $url = "https://api.weixin.qq.com/wxa/get_category?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret->category_list;
        } else {
            $this->errorLog("获取授权小程序帐号的可选类目操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     * */
    private function getPage()
    {
        $url = "https://api.weixin.qq.com/wxa/get_page?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret->page_list;
        } else {
            $this->errorLog("获取小程序的第三方提交代码的页面配置失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 创建小程序
     * @param $name
     * @param $code
     * @param $code_type
     * @param $legal_persona_wechat
     * @param $legal_persona_name
     * @param $component_phone
     * @return bool
     * @throws Open3rdException
     */
    public function fastCreate($name, $code, $code_type, $legal_persona_wechat, $legal_persona_name, $component_phone)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp?action=create&component_access_token=" . $this->thirdAccessToken;
        $data = json_encode([
            'name' => $name,
            'code' => $code,
            'code_type' => $code_type,
            'legal_persona_wechat' => $legal_persona_wechat,
            'legal_persona_name' => $legal_persona_name,
            'component_phone' => $component_phone
        ], JSON_UNESCAPED_UNICODE);
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("创建小程序失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 查询创建任务状态
     * @param $name
     * @param $legal_persona_wechat
     * @param $legal_persona_name
     * @return mixed
     * @throws Open3rdException
     */
    public function getFastCreate($name, $legal_persona_wechat, $legal_persona_name)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp?action=search&component_access_token=" . $this->thirdAccessToken;
        $data = json_encode([
            'name' => $name,
            'legal_persona_wechat' => $legal_persona_wechat,
            'legal_persona_name' => $legal_persona_name,
        ], JSON_UNESCAPED_UNICODE);
        $ret = json_decode($this->getCurl()->post($url, $data)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询创建任务状态失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 获取提审限额
     * @return mixed
     * @throws Open3rdException
     */
    public function quota()
    {
        $url = "https://api.weixin.qq.com/wxa/queryquota?access_token=" . $this->authorizer_access_token;
        $ret = json_decode($this->getCurl()->get($url)->response);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("获取提审限额失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * @return Curl
     */
    public function getCurl()
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        return $curl;
    }

    private function errorLog($msg, $ret = '')
    {
        $error = [
            '-1' => '系统繁忙',
            '80082' => '没有权限使用该插件',
            '85009' => '已经有正在审核的版本',
            '85012' => '无效的审核 id',
            '85019' => '无效的自定义配置',
            '85013' => '没有审核版本',
            '87013' => '撤回次数达到上限（每天一次，每个月 10 次）',
            '85052' => '该版本小程序已经发布',
            '85017' => '请联系平台管理员，确认小程序已经添加了域名或该域名是否没有在第三方平台添加',
            '89021' => '请求保存的域名不是第三方平台中已设置的小程序业务域名或子域名',
            '89231' => '个人小程序不支持调用 setwebviewdomain 接口',
            '86004' => '无效微信号',
            '61070' => '法人姓名与微信号不一致'
        ];
        \Yii::error('==========error log=========');
        \Yii::error($msg);
        \Yii::error($ret);
        if (isset($ret->errcode) && isset($error[$ret->errcode])) {
            $msg = $error[$ret->errcode];
        }
        throw new Open3rdException($msg);
    }
}
