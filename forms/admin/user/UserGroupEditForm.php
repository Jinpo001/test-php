<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\user;


use app\core\response\ApiCode;
use app\forms\common\CommonAuth;
use app\models\AccountPermissionsGroup;
use app\models\AccountUserGroup;
use app\models\Model;

class UserGroupEditForm extends Model
{
    public $id;
    public $name;
    public $permissions_group_id;

    public function rules()
    {
        return [
            [['name', 'permissions_group_id'], 'required'],
            [['name'], 'trim'],
            [['id', 'permissions_group_id'], 'integer'],
            [['name'], 'string', 'max' => 65],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '用户组名称',
            'permissions_group_id' => '套餐权限组',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {

            $group = AccountPermissionsGroup::findOne(['id' => $this->permissions_group_id, 'is_delete' => 0]);
            if (!$group) {
                throw new \Exception('权限套餐不存在');
            }

            if ($this->id) {
                $group = AccountUserGroup::find()->andWhere([
                    'id' => $this->id,
                    'is_delete' => 0
                ])->one();

                if (!$group) {
                    throw new \Exception('用户组不存在');
                }

                $isExist = AccountUserGroup::find()->andWhere([
                    'name' => $this->name,
                    'is_delete' => 0
                ])
                    ->andWhere(['!=', 'id', $this->id])
                    ->one();

            } else {
                $group = new AccountUserGroup();

                $isExist = AccountUserGroup::find()->andWhere([
                    'name' => $this->name,
                    'is_delete' => 0
                ])->one();
            }

            if ($isExist) {
                throw new \Exception('用户组名称已存在');
            }
            
            $group->name = $this->name;
            $group->permissions_group_id = $this->permissions_group_id;
            $res = $group->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($group));
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
}
