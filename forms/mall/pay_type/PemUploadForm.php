<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/11/5
 * Time: 18:00
 */

namespace app\forms\mall\pay_type;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\PayType;
use yii\web\UploadedFile;

class PemUploadForm extends Model
{
    public $id;
    /** @var UploadedFile */
    public $file;
    public $type;

    public function save()
    {
        try {
            /**@var PayType $wxAppConfig**/
            $payType = PayType::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id])
                ->one();

            if (!$payType) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请先保存服务商配置',
                ];
            }

            if ($this->file->extension !== 'pem') {
                throw new \Exception('文件格式不正确, 请上传 .pem 格式文件');
            }

            if ($this->type == 'cert') {
                $payType->service_cert_pem = file_get_contents($this->file->tempName);
            } elseif ($this->type = 'key') {
                $payType->service_key_pem = file_get_contents($this->file->tempName);
            } else {
                throw new \Exception('未知的类型');
            }
            $res = $payType->save();

            if (!$res) {
                throw new \Exception('保存失败');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '上传成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
