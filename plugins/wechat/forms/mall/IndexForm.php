<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/5
 * Time: 9:28 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\mall;

use Alchemy\Zippy\Zippy;
use app\forms\common\qrcode\QrcodeServe;
use app\plugins\wechat\Plugin;
use app\plugins\wechat\forms\Model;

class IndexForm extends Model
{
    /**
     * @var Plugin $plugin
     */
    public $plugin;

    public function init()
    {
        parent::init();
        $this->plugin = new Plugin();
    }

    public function getDetail()
    {
        return $this->success($this->getPath());
    }

    public function getPath()
    {
        try {
            $plugin = new Plugin();
            $baseUrl = \Yii::$app->basePath;
            $file = $plugin->filePath() . '/mall/' . \Yii::$app->mall->id;
            $qrcode = '';
            $path = '';
            $version = '';
            if (is_dir($baseUrl . $file)) {
                $path = $plugin->getWebUri();
                $qrcodeServe = new QrcodeServe();
                $qrcode = $qrcodeServe->getQrcode($plugin->getName());
                $content = file_get_contents($baseUrl . $file . '/version.js');
                $version = substr($content, strlen('let version = '));
            }
            return [
                'qrcode' => $qrcode,
                'path' => $path,
                'version' => trim($version, '\''),
                'last_version' => app_version(),
            ];
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }

    public function issue()
    {
        $commonPath = $this->commonPath() . '/h5/';
        $res = file_uri($this->getMallPath() . '/');
        $copyList = ['index.html', 'pay.html', 'ap.js'];
        foreach ($copyList as $name) {
            if (($r = $this->copy($commonPath . $name, $res['local_uri'] . $name)) !== true) {
                return $r;
            }
        }
        $mallId = \Yii::$app->mall->id;
        $apiRoot = \Yii::$app->request->hostInfo
            . rtrim(\Yii::$app->request->baseUrl, '/')
            . '/index.php?_mall_id='
            . \Yii::$app->mall->id;
        $apiRoot = str_replace('http://', 'https://', $apiRoot);
        $version = app_version();
        $siteInfoContent = <<<EOF
let siteInfo = {
    'acid': -1,
    'version': '{$version}',
    'apiroot': '{$apiRoot}',
    'id': '{$mallId}',
    'platform': 'wechat'
};
EOF;
        $siteinfoPath = $res['local_uri'] . 'siteinfo.js';
        if (file_put_contents($siteinfoPath, $siteInfoContent) === false) {
            return $this->fail(['msg' => '发布出错，请检查文件权限X02']);
        }
        $versionContent = <<<EOF
let version = '{$version}'
EOF;
        $versionPath = $res['local_uri'] . 'version.js';
        if (file_put_contents($versionPath, $versionContent) === false) {
            return $this->fail(['msg' => '发布出错，请检查文件权限X02']);
        }
        return $this->getDetail();
    }

    /**
     * @param $file
     * @param $toFile
     * @return array|bool
     * 拷贝部分特殊文件到商城目录
     */
    protected function copy($file, $toFile)
    {
        if (!file_exists($file)) {
            return $this->fail(['msg' => '文件不存在']);
        }
        if (!copy($file, $toFile)) {
            return $this->fail(['msg' => '发布出错，请检查文件权限X01']);
        }
        return true;
    }

    /**
     * @return string
     * 手机端代码商城目录--访问路径
     */
    protected function getMallPath()
    {
        return $this->plugin->filePath() . '/mall/' . \Yii::$app->mall->id;
    }

    /**
     * @return string
     * 手机端代码公共目录--代码存放路径
     */
    protected function commonPath()
    {
        return \Yii::$app->branch->syncPublicPath();
    }

    public function zip()
    {
        $file = \Yii::$app->basePath . '/plugins/wechat/h5.zip';
        if (!file_exists($file)) {
            throw new \Exception('公众号商城包不存在');
        }
        $zippy = Zippy::load();
        $archive = $zippy->open($file);
        $archive->extract($this->commonPath());
        $this->clearOpcache();
        unset($archive);
        return true;
    }

    private function clearOpcache()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}
