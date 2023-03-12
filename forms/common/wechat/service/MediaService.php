<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 2:13 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\service;

use app\helpers\CurlHelper;

class MediaService extends BaseService
{
    /**
     * @param array $args ['type' => '媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）','media' => '本地路径']
     * @return mixed
     * @throws \Exception
     * 新增临时素材
     * https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html
     */
    public function upload($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::MULTIPART)->httpPost($api, [
            'type' => $args['type']
        ], [
            [
                'name' => 'media',
                'contents' => fopen($args['media'], 'r')
            ]
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args ['media_id' => '临时素材id']
     * @return mixed
     * @throws \Exception
     * 获取临时素材
     * https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Get_temporary_materials.html
     */
    public function download($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$this->accessToken}";
        return $this->getClient()->httpGet($api, [
            'media_id' => $args['media_id']
        ]);
    }
}
