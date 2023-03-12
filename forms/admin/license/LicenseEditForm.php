<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\license;

use app\core\response\ApiCode;
use app\forms\common\CommonAuth;
use app\forms\common\attachment\CommonAttachment;
use app\models\AdminInfo;
use app\models\License;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use yii\db\ActiveQuery;

class LicenseEditForm extends Model
{
    public $id;
    public $domain;
    public $icp_number;
    public $icp_link;
    public $security_address;
    public $security_number;
    public $electronic_domain;

    public function rules()
    {
        return [
            [['domain', 'icp_number', 'icp_link'], 'required'],
            [['domain', 'icp_number', 'security_address', 'security_number', 'electronic_domain'], 'string', 'max' => 255],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'domain' => '域名',
            'icp_number' => 'ICP备案号',
            'icp_link' => 'ICP备案号跳转链接',
            'security_address' => '联网备案地',
            'security_number' => '联网备案号',
            'electronic_domain' => '电子执照',
        ];
    }

    public function add()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            $license = License::find()->andWhere(['domain' => $this->domain, 'is_delete' => 0])->one();
            if ($license) {
                throw new \Exception('域名已存在');
            }

            $license = new License();
            $license->domain = $this->domain;
            $license->icp_number = $this->icp_number;
            $license->icp_link = $this->icp_link;
            $license->security_address = $this->security_address;
            $license->security_number = $this->security_number;
            $license->electronic_domain = $this->electronic_domain;
            $res = $license->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($license));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function update()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $license = License::find()->where(['id' => $this->id])->one();

            if (!$license) {
                throw new \Exception('备案信息不存在');
            }

            $isExist = License::find()->andWhere(['domain' => $this->domain, 'is_delete' => 0])->andWhere(['!=', 'id' , $this->id])->one();
            if ($isExist) {
                throw new \Exception('域名已存在');
            }

            $license->domain = $this->domain;
            $license->icp_number = $this->icp_number;
            $license->icp_link = $this->icp_link;
            $license->security_address = $this->security_address;
            $license->security_number = $this->security_number;
            $license->electronic_domain = $this->electronic_domain;
            $res = $license->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($license));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
