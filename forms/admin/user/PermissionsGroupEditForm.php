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
use app\models\Model;

class PermissionsGroupEditForm extends Model
{
    public $id;
    public $name;
    public $permissions;

    public function rules()
    {
        return [
            [['name', 'permissions'], 'required'],
            [['name'], 'trim'],
            [['id'], 'integer'],
            [['permissions',], 'default', 'value' => []],
            [['name'], 'string', 'max' => 65],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '权限套餐名称',
            'permissions' => '插件权限',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();

            if ($this->id) {
                $group = AccountPermissionsGroup::find()->andWhere([
                    'id' => $this->id,
                    'is_delete' => 0
                ])->one();

                if (!$group) {
                    throw new \Exception('权限套餐不存在');
                }

                $isExist = AccountPermissionsGroup::find()->andWhere([
                    'name' => $this->name,
                    'is_delete' => 0
                ])
                    ->andWhere(['!=', 'id', $this->id])
                    ->one();

            } else {
                $group = new AccountPermissionsGroup();

                $isExist = AccountPermissionsGroup::find()->andWhere([
                    'name' => $this->name,
                    'is_delete' => 0
                ])->one();
            }

            if ($isExist) {
                throw new \Exception('权限套餐名称已存在');
            }
            
            $group->name = $this->name;
            $group->permissions = $this->checkPermissions($this->permissions);
            $group->permissions_text = $this->getPermissionsText();
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

    private function checkPermissions($permissions)
    {
        $permissions = json_decode($permissions, true);

        $data = CommonAuth::getPermissionsList();

        $list = [];
        foreach ($data['mall'] as $item) {
            $list[$item['name']] = $item['display_name'];
        }
        foreach ($data['plugins'] as $item) {
            $list[$item['name']] = $item['display_name'];
        }

        foreach ($permissions['mall_permissions'] as $key => $item) {
            if (!isset($list[$item])) {
                unset($permissions['mall_permissions'][$key]);
            }
        }
        $permissions['mall_permissions'] =  array_values($permissions['mall_permissions']);

        foreach ($permissions['plugin_permissions'] as $key => $item) {
            if (!isset($list[$item])) {
                unset($permissions['plugin_permissions'][$key]);
            }
        }
        $permissions['plugin_permissions'] = array_values($permissions['plugin_permissions']);

        return json_encode($permissions, JSON_UNESCAPED_UNICODE);
    }

    private function getPermissionsText()
    {
        $permissions = json_decode($this->permissions, true);

        $data = CommonAuth::getPermissionsList();

        $list = [];
        foreach ($data['mall'] as $item) {
            $list[$item['name']] = $item['display_name'];
        }
        foreach ($data['plugins'] as $item) {
            $list[$item['name']] = $item['display_name'];
        }

        $permissionsText = '';
        foreach ($permissions['mall_permissions'] as $item) {
            $newName = isset($list[$item]) ? $list[$item] : '';
            if ($newName) {
                $permissionsText .= $permissionsText ? '、' . $newName : $newName;
            }
        }
        foreach ($permissions['plugin_permissions'] as $item) {
            $newName = isset($list[$item]) ? $list[$item] : '';
            if ($newName) {
                $permissionsText .= $permissionsText ? '、' . $newName : $newName;
            }
        }

        return $permissionsText;
    }

    private function checkData()
    {
        $permissions = json_decode($this->permissions, true);
        if (!isset($permissions['mall_permissions']) || !isset($permissions['plugin_permissions'])) {
            throw new \Exception('插件权限参数异常');
        }

        if (count($permissions['mall_permissions']) == 0 && count($permissions['plugin_permissions']) == 0) {
            throw new \Exception('请添加插件权限');
        }
    }
}
