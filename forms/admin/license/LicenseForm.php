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

class LicenseForm extends Model
{
    public $id;
    public $keyword;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [

        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $query = License::find()->andWhere(['is_delete' => 0]);

        if ($this->keyword) {
            $query->andWhere(['like', 'domain', $this->keyword]);
        }

        $list = $query->page($pagination)->orderBy('created_at DESC')->all();
        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'id' => $item->id,
                'domain' => $item->domain,
                'icp_number' => $item->icp_number,
                'icp_link' => $item->icp_link,
                'security_address' => $item->security_address,
                'security_number' => $item->security_number,
                'electronic_domain' => $item->electronic_domain,
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $license = License::find()->where(['id' => $this->id])->one();

            if (!$license) {
                throw new \Exception('备案信息不存在');
            }

            $license->is_delete = 1;
            $res = $license->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($license));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
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
