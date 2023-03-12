<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/15
 * Time: 5:19 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\forms\common\CommonOption;
use app\forms\common\CommonOptionP;
use app\plugins\fission\forms\Model;

class SettingForm extends Model
{
    public $bg_pic;
    public $custom;
    public $custom_color;
    public $contact_list;
    public $style;
    public $activity_bg_pic;
    public $activity_bg_style;
    public $activity_bg_color;
    public $activity_bg_gradient_color;
    public $poster;
    public function rules()
    {
        return [
            [['bg_pic', 'custom', 'activity_bg_pic', 'activity_bg_color', 'activity_bg_style',
                'activity_bg_gradient_color', 'poster', 'custom_color'], 'trim'],
            [['bg_pic', 'custom', 'activity_bg_pic', 'activity_bg_color', 'activity_bg_style',
                'activity_bg_gradient_color', 'custom_color'], 'string'],
            [['style'], 'integer'],
            [['contact_list'], 'safe'],
            ['style', 'in', 'range' => [1, 2, 3, 4]],
            ['activity_bg_style', 'in', 'range' => ['pure', 'gradient']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'bg_pic' => '背景图',
            'custom' => '自定义内容',
            'custom_color' => '文本颜色',
            'contact_list' => '客服微信列表',
            'style' => '红包样式',
            'activity_bg_pic' => '红包墙背景图',
            'activity_bg_style' => '红包墙下半部分样式',
            'activity_bg_color' => '红包墙下半部分颜色',
            'activity_bg_gradient_color' => '红包墙下半部分渐变色',
            'poster' => '海报',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $contactList = [];
        if (!$this->contact_list) {
            $this->contact_list = [];
        }
        foreach ($this->contact_list as $item) {
            if (!(isset($item['qrcode']) && $item['name'])) {
                continue;
            }
            $contactList[] = [
                'qrcode' => trim($item['qrcode']),
                'name' => trim($item['name'])
            ];
        }
        $this->contact_list = $contactList;
        $this->poster = (new CommonOptionP())->saveEnd($this->poster);
        CommonOption::set('fission_setting', $this->attributes, \Yii::$app->mall->id, 'plugin', 0);
        return $this->success(['msg' => '保存成功']);
    }
}
