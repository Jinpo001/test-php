<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\user;


use app\core\response\ApiCode;
use app\models\AccountPermissionsGroup;
use app\models\AccountUserGroup;
use app\models\AdminInfo;
use app\models\Model;
use app\models\User;

class UserGroupForm extends Model
{
    public $id;
    public $keyword_value;
    public $keyword_name;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword_name', 'keyword_value'], 'string'],
            [['keyword_name', 'keyword_value'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = AccountUserGroup::find()->andWhere(['is_delete' => 0]);

            switch ($this->keyword_name) {
                case 'name':
                    $query->andWhere(['like', 'name', $this->keyword_value]);
                    break;
                case 'username':
                    $userIds = User::find()->andWhere(['like', 'username', $this->keyword_value])
                        ->andWhere(['is_delete' => 0])
                        ->select('id');
                    $groupIds = AdminInfo::find()->andWhere(['user_id' => $userIds])->andWhere(['!=', 'user_group_id', 0])->select('user_group_id');
                    $query->andWhere(['id' => $groupIds]);
                    break;
                case 'mobile':
                    $userIds = User::find()->andWhere(['like', 'mobile', $this->keyword_value])
                        ->andWhere(['is_delete' => 0])
                        ->select('id');
                    $groupIds = AdminInfo::find()->andWhere(['user_id' => $userIds])->andWhere(['!=', 'user_group_id', 0])->select('user_group_id');
                    $query->andWhere(['id' => $groupIds]);
                    break;
                default:
                    throw new \Exception('keyword_name参数异常');
                    break;
            }


            $list = $query->with('permissionsGroup', 'adminInfo')->page($pagination, 10)->orderBy(['created_at' => SORT_DESC])->all();
            $newList = [];
            foreach ($list as $item) {
                $newItem = [];
                $newItem['id'] = $item->id;
                $newItem['name'] = $item->name;
                $newItem['user_count'] = count($item->adminInfo);
                $newItem['group_name'] = $item->permissionsGroup->name;
                $newItem['created_at'] = $item->created_at;
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $group = AccountUserGroup::find()->andWhere(['is_delete' => 0, 'id' => $this->id])->with('permissionsGroup')->one();

            if (!$group) {
                throw new \Exception('用户组不存在');
            }

            $newGroup['id'] = $group->id; 
            $newGroup['name'] = $group->name; 
            $newGroup['group_id'] = $group->permissionsGroup->id; 
            $newGroup['group_name'] = $group->permissionsGroup->name; 
            

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'group' => $newGroup,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $group = AccountUserGroup::find()->andWhere(['is_delete' => 0, 'id' => $this->id])->one();

            if (!$group) {
                throw new \Exception('用户组不存在');
            }

            $count = AdminInfo::find()->andWhere(['user_group_id' => $group->id, 'is_delete' => 0])->count();
            if ($count > 0) {
                throw new \Exception('该用户组有账号使用，无法删除');
            }

            $group->is_delete = 1;
            $res = $group->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($group));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
                'data' => []
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
