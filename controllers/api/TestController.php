<?php


namespace app\controllers\api;


use app\controllers\Controller;
use app\models\Model;

class TestController extends Controller
{

    //FIXED 资源下载
    public function XactionIndex()
    {
        $url = \Yii::$app->request->get('url');
        $url = base64_decode($url);
        $filename = pathinfo($url)['filename'] . '.png';
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename='. $filename);
        readfile('temp/' . $filename);
    }
}