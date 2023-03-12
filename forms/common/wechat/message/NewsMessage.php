<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 5:51 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\message;

class NewsMessage extends BaseMessage
{
    public $name = '回复图文消息';
    public $type = 'news';
    public $url;
    public $title;
    public $content;
    public $picurl;

    public function rules()
    {
        return [
            [['url', 'title', 'content', 'picurl'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'url' => '点击图文消息跳转链接',
            'title' => '图文消息标题',
            'content' => '图文消息描述',
            'picurl' => '图片链接',
        ];
    }

    public function reply($model)
    {
        return [
            'ArticleCount' => 1,
            'Articles' => [
                'item' => [
                    'Title' => $model->title,
                    'Description' => $model->content,
                    'PicUrl' => $model->picurl,
                    'Url' => $model->url
                ]
            ]
        ];
    }

    public function checkMedia()
    {
        return false;
    }

    public function custom($model)
    {
        return [
            'msgtype' => $this->type,
            'news' => [
                'articles' => [
                    [
                        'title' => $model->title,
                        'description' => $model->content,
                        'url' => $model->url,
                        'picurl' => $model->picurl
                    ]
                ]
            ]
        ];
    }
}