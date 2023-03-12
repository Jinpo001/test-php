<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/19
 * Time: 10:36
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\permission\branch;


use app\forms\common\CommonAuth;
use app\models\AccountUserGroup;
use app\models\AdminInfo;
use app\models\Model;
use app\models\User;

abstract class BaseBranch extends Model
{
    public $ignore;

    /**
     * @param $menu
     * @return mixed
     * @throws \Exception
     * 删除非本分支菜单
     */
    abstract public function deleteMenu($menu);

    /**
     * @return mixed
     * 获取商城退出跳转链接
     */
    abstract public function logoutUrl();

    /**
     * @param AdminInfo $adminInfo
     * @return array
     * 获取子账户权限
     */
    public function childPermission($adminInfo)
    {
        $all = CommonAuth::getAllPermission();
        if ($adminInfo->identity->is_super_admin == 1) {
            return $all;
        }
        $permission = [];
        if ($adminInfo->permissions) {
            $permission = json_decode($adminInfo->permissions, true);
        }

        // 查询用户组权限
        try {
            $userGroup = AccountUserGroup::find()->andWhere([
                'id' => $adminInfo->user_group_id,
                'is_delete' => 0
            ])->with('permissionsGroup')->one();
            if ($userGroup && $userGroup->permissionsGroup) {
                $groupPermissions = json_decode($userGroup->permissionsGroup->permissions, true);
                $permission = array_merge($groupPermissions['mall_permissions'], $groupPermissions['plugin_permissions']);
            }
        }catch(\Exception $exception) {
            \Yii::error($exception);
        }

        // 查询附加权限
        if ($adminInfo->subjoin_permissions) {
            $subjoinPermissions = json_decode($adminInfo->subjoin_permissions, true);
            $newSubjoinPermissions = array_merge($subjoinPermissions['mall'], $subjoinPermissions['plugin']);
            $permission = array_merge($permission, $newSubjoinPermissions);
        }

        $permission = array_unique($permission);
        $permission = array_intersect($permission, $all);
        return $permission;
    }

    protected function getKey($list)
    {
        $newList = [];
        foreach ($list as $item) {
            if (isset($item['name'])) {
                $newList[] = $item['name'];
            } elseif (is_array($item)) {
                $newList = array_merge($newList, $this->getKey($item));
            } else {
                continue;
            }
        }
        return $newList;
    }

    /**
     * @param AdminInfo $adminInfo
     * @return array|mixed
     */
    public function getSecondaryPermission($adminInfo)
    {
        if ($adminInfo->identity->is_super_admin == 1) {
            return CommonAuth::getSecondaryPermissionAll();
        }
        $permission = [];
        if ($adminInfo->secondary_permissions) {
            $permission = json_decode($adminInfo->secondary_permissions, true);
        }

        // 查询用户组权限
        try {
            $userGroup = AccountUserGroup::find()->andWhere([
                'id' => $adminInfo->user_group_id,
                'is_delete' => 0
            ])->with('permissionsGroup')->one();
            if ($userGroup && $userGroup->permissionsGroup) {
                $groupPermissions = json_decode($userGroup->permissionsGroup->permissions, true);
                $permission = $groupPermissions['secondary_permissions'];
            }
        }catch(\Exception $exception) {
            \Yii::error($exception);
        }

        // 查询附加权限
        if ($adminInfo->subjoin_permissions) {
            try {
                $subjoinPermissions = json_decode($adminInfo->subjoin_permissions, true);
                $secondary = $subjoinPermissions['secondary'];

                $permission['attachment'] = array_merge($permission['attachment'], $secondary['attachment']);
                $permission['attachment'] = array_unique($permission['attachment']);

                $permission['template']['list'] = array_merge($permission['template']['list'], $secondary['template']['list']);
                $permission['template']['list'] = array_unique($permission['template']['list']);

                $permission['template']['use_list'] = array_merge($permission['template']['use_list'], $secondary['template']['use_list']);
                $permission['template']['use_list'] = array_unique($permission['template']['use_list']);

                if ($secondary['template']['is_all']) {
                    $permission['template']['is_all'] = $secondary['template']['is_all'];
                }
                if ($secondary['template']['use_all']) {
                    $permission['template']['use_all'] = $secondary['template']['use_all'];
                }
            }catch(\Exception $exception) {
                \Yii::error($exception);
            }
        }

        return $permission;
    }

    /**
     * @param User $user
     * @return bool
     * 校验用户是否具备登录后台的权限
     */
    public function checkMallUser($user)
    {
        return $user->mch_id == 0 && $user->identity->is_operator == 0;
    }

    /**
     * 同步微擎版和独立版的目录(h5目录)
     */
    public function syncPublicPath()
    {
        return \Yii::$app->basePath;
    }
}
