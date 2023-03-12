<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 11:42
 */


namespace app\controllers\api;


use app\forms\AttachmentUploadForm;
use yii\web\UploadedFile;

class AttachmentController extends ApiController
{
    public function actionUpload($name = 'file')
    {
        if (mb_stripos(\Yii::$app->request->referrer, 'toutiao') !== false) {
            if (!empty($_FILES[$name])) {
                $fName = $_FILES[$name]['name'];
                $qPosition = mb_stripos($fName, '?');
                if ($qPosition !== false) {
                    $fName = mb_substr($fName, 0, $qPosition);
                    $_FILES[$name]['name'] = $fName;
                }
            }
        }

        $form = new AttachmentUploadForm();
        if ($name === 'base64') {
            if ($filePath = $this->base64(\Yii::$app->request->post('database'))) {
                $form->file = AttachmentUploadForm::getInstanceFromFile($filePath);
            } else {
                return $this->asJson([
                    'code' => 1,
                    'msg' => '上传的图片有问题'
                ]);
            }
        } else {
            $form->file = UploadedFile::getInstanceByName($name);
            if (\Yii::$app->request->post('file_name') && \Yii::$app->request->post('file_name') !== 'null') {
                $form->file->name = \Yii::$app->request->post('file_name');
            }
        }

        $mchId = \Yii::$app->request->post('mch_id');
        if ($mchId && is_numeric($mchId)) {
            \Yii::$app->setMchId($mchId);
        }

        return $this->asJson($form->save());
    }

    public function base64($base64)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            //后缀
            $type = $result[2];
            //创建文件夹，以年月日
            $res = file_uri('/web/temp/' . date('Ymd', time()) . "/");
            $newFile = $res['local_uri'];
            $newFile = $newFile . time() . ".{$type}";    //图片名以时间命名
            //保存为文件
            if (file_put_contents($newFile, base64_decode(str_replace($result[1], '', $base64)))) {
                //返回这个图片的路径
                return $newFile;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
