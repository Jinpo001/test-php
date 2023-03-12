<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/31
 * Time: 4:53 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\mall;

use app\forms\common\template\TemplateList;
use app\plugins\wechat\forms\Model;
use app\plugins\wechat\models\WechatTemplate;
use app\plugins\wechat\Plugin;

/**
 * Class WechatTemplateForm
 * @package app\plugins\wechat\forms\mall
 * @property Plugin $plugin
 */
class WechatTemplateForm extends Model
{
    protected $plugin;
    public $mall;

    public function init()
    {
        parent::init();
        $this->plugin = new Plugin();
    }
    public function addTemplate($templateList)
    {
        $templateApi = $this->plugin->getWechatTemplate();
        $list = $templateApi->getTemplateList();
        $newList = [];
        $templateIdList = [];
        foreach ($list['template_list'] as $value) {
            $key = $this->getTemplateKey($value['title'], $value['primary_industry'], $value['deputy_industry']);
            $newList[$key] = $value['template_id'];
        }
        foreach ($templateList as $index => $item) {
            $key = $this->getTemplateKey($item['title']);
            if (isset($newList[$key])) {
                $tplId = $newList[$key];
            } else {
                try {
                    $res = $templateApi->addTemplate($item['keyword_id_list']);
                    $tplId = $res['template_id'];
                    $newList[$key] = $tplId;
                } catch (\Exception $exception) {
                    \Yii::warning('一键添加公众号模板消息出错：');
                    \Yii::warning($exception);
                    continue;
                }
            }
            $templateIdList[] = [
                'tpl_name' => $index,
                'tpl_id' => $tplId
            ];
        }
        return $templateIdList;
    }

    protected function getTemplateKey($title, $primary_industry = 'IT科技', $deputy_industry = '互联网|电子商务')
    {
        return md5($title . $primary_industry . $deputy_industry);
    }

    /**
     * @param $attributes
     * @return bool
     * @throws \Exception
     * 保存订阅消息到数据库
     */
    public function addTemplateList($attributes)
    {
        foreach ($attributes as $item) {
            if (!isset($item['tpl_name'])) {
                throw new \Exception('缺少必要的参数tpl_name');
            }
            if (!isset($item[$item['tpl_name']])) {
                throw new \Exception("缺少必要的参数{$item['tpl_name']}");
            }
            $tpl = WechatTemplate::findOne(['mall_id' => \Yii::$app->mall->id, 'tpl_name' => $item['tpl_name']]);
            $tplId = $item[$item['tpl_name']];
            if (!$tpl) {
                $tpl = new WechatTemplate();
                $tpl->mall_id = \Yii::$app->mall->id;
                $tpl->tpl_name = $item['tpl_name'];
            }
            if ($tpl->tpl_id != $tplId) {
                $tpl->tpl_id = $tplId;
                if (!$tpl->save()) {
                    throw new \Exception((new Model())->getErrorMsg($tpl));
                } else {
                    continue;
                }
            } else {
                continue;
            }
        }
        return true;
    }

    public function getTemplateList($param)
    {
        return WechatTemplate::find()->where(['mall_id' => \Yii::$app->mall->id])->select($param)->all();
    }

    /**
     * @param $templateTpl
     * @return string
     * @throws \Exception
     * 获取单个订阅消息template_id
     */
    public function addTemplateOne($templateTpl)
    {
        $params = TemplateList::getInstance()->getTemplateClass($templateTpl);
        $templateList = $this->addTemplate([$params->config('wechat')]);
        if (empty($templateList)) {
            throw new \Exception('获取模板出错');
        }
        return $templateList[0]['tpl_id'];
    }
}
