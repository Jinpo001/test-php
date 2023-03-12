<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/1
 * Time: 2:36 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\forms\common\wechat\WechatFactory;
use app\models\Model;
use app\models\WechatSubscribeReply;

class WechatMenuForm extends Model
{
    public $list;

    public function rules()
    {
        return [
            [['list'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!isset($this->list)) {
                throw new \Exception('参数错误');
            }
            if (count($this->list) < 1) {
                throw new \Exception('一级菜单数量最少一个');
            }
            if (count($this->list) > 3) {
                throw new \Exception('一级菜单数量最多三个');
            }
            $button = [];
            foreach ($this->list as $item) {
                if (isset($item['sub_button']) && !empty($item['sub_button'])) {
                    // 二级菜单
                    $subButton = [];
                    foreach ($item['sub_button'] as $value) {
                        $res = $this->check($value);
                        if (!$res) {
                            continue;
                        }
                        $subButton[] = $res;
                    }
                    $button[] = [
                        'name' => $item['name'],
                        'sub_button' => $subButton
                    ];
                } else {
                    // 一级菜单
                    $res = $this->check($item);
                    if (!$res) {
                        continue;
                    }
                    $button[] = $res;
                }
            }
            $res = WechatFactory::create()->menuService->create([
                'button' => $button
            ]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param array $item
     * @return array|boolean
     * @throws \Exception
     */
    protected function check($item)
    {
//        [
//            'type' => '菜单类型 click, view, miniprogram',
//            'name' => '菜单名称',
//            'reply_type' => 'click菜单时，消息类型0--文字 1--图片',
//            'key' => 'click菜单时，消息id',
//            'content' => 'click菜单，text消息类型时，消息内容',
//            'picurl' => 'click菜单、image消息类型时，图片链接',
//            'url' => '跳转网页链接|小程序无法跳转时链接',
//            'appid' => '小程序的appid',
//            'page' => '小程序路径'
//        ]
        switch ($item['type']) {
            case 'click':
                $model = null;
                if (isset($item['key'])) {
                    $model = WechatSubscribeReply::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'id' => $item['key'],
                        'status' => 2
                    ]);
                }
                if (!$model) {
                    $model = new WechatSubscribeReply();
                    $model->mall_id = \Yii::$app->mall->id;
                    $model->status = 2;
                    $model->is_delete = 0;
                }
                $model->type = $item['reply_type'];
                $message = WechatFactory::createMessage($model->type);
                $arr = [
                    'content' => $item['content'] ?? '',
                    'url' => $item['picurl'] ?? ''
                ];
                $message->attributes = $arr;
                if (!$message->validate()) {
                    throw new \Exception($this->getErrorMsg($message));
                }
                $model->media_id = $message->getMedia($model);
                $model->attributes = $arr;
                if (!$model->save()) {
                    throw new \Exception($this->getErrorMsg($model));
                }
                return [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'key' => $model->id
                ];
            case 'view':
                return [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'url' => $item['url']
                ];
            case 'miniprogram':
                return [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'appid' => $item['appid'],
                    'pagepath' => $item['page']
                ];
            default:
                return false;
        }
    }

    public function getDetail()
    {
        try {
            $service = WechatFactory::create()->menuService;
            $list = $service->getMenuInfo();
            if ($list['is_menu_open'] != 1) {
                return $this->fail([
                    'msg' => '公众号平台自定义菜单未开启'
                ]);
            }
            \Yii::warning($list);
            $newList = [];
            foreach ($list['selfmenu_info']['button'] as $item) {
                if (isset($item['sub_button']) && !empty($item['sub_button'])) {
                    $subButton = [];
                    foreach ($item['sub_button']['list'] as $value) {
                        $subButton[] = $this->getButton($value);
                    }
                    $newList[] = [
                        'name' => $item['name'],
                        'sub_button' => $subButton
                    ];
                } else {
                    $newList[] = $this->getButton($item);
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $newList
            ];
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }

    protected function getButton($item)
    {
        if (!isset($item['type'])) {
            return $item;
        }
        switch ($item['type']) {
            case 'click':
                $model = WechatSubscribeReply::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $item['key'],
                    'status' => 2
                ]);
                $res = [];
                if ($model) {
                    $res = [
                        'key' => $model->id,
                        'reply_type' => $model->type,
                        'content' => $model->content,
                        'picurl' => $model->url,
                    ];
                }
                return array_merge([
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'reply_type' => 0
                ], $res);
            case 'view':
                return [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'reply_type' => 0,
                ];
            case 'miniprogram':
                return [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'appid' => $item['appid'],
                    'page' => $item['pagepath'],
                    'reply_type' => 0,
                ];
            default:
                return $item;
        }
    }
}
