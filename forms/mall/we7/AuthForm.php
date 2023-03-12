<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\we7;

use app\core\response\ApiCode;
use app\forms\common\CommonAuth;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class AuthForm extends Model
{
    public $page;
    public $search;
    public $status;
    public $mall_permissions;
    public $plugin_permissions;
    public $secondary_permissions;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['search', 'mall_permissions', 'plugin_permissions', 'secondary_permissions'], 'safe'],
            [['status'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);
        $res = CommonAuth::getChildrenUsers($search);
        $plugins = [];
        foreach (\Yii::$app->plugin->list as $item) {
            $plugins[] = $item['name'];
        }
        foreach ($res['list'] as $key => $item) {
            $permissions = $item['adminInfo']['permissions'] ? \Yii::$app->serializer->decode($item['adminInfo']['permissions']) : [];

            $newPermissions = [
                'mall' => [],
                'plugins' => [],
            ];
            foreach ($permissions as $pItem) {
                if (!in_array($pItem, $plugins)) {
                    $newPermissions['mall'][] = $pItem;
                } else {
                    $newPermissions['plugins'][] = $pItem;
                }
            }

            $res['list'][$key]['adminInfo']['permissions'] = $newPermissions;
            $res['list'][$key]['adminInfo']['permissions_num'] = count($permissions);
        }

        $status = CommonOption::get(
            Option::NAME_PERMISSIONS_STATUS,
            0,
            Option::GROUP_ADMIN
        );

        $customPermissions = CommonOption::get(
            Option::NAME_PERMISSIONS_LIST,
            0,
            Option::GROUP_ADMIN,
            [
                'mall_permissions' => [],
                'plugin_permissions' => [],
                'secondary_permissions' => [
                    'attachment' => [],
                    'template' => [
                        'is_all' => 0,
                        'use_all' => 0,
                        'list' => [],
                        'use_list' => []
                    ]
                ],
            ]
        );

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination'],
                'status' => $status ? $status : '0',
                'custom_permissions' => $customPermissions
            ]
        ];
    }

    public function updateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $option = CommonOption::set(
            Option::NAME_PERMISSIONS_STATUS,
            $this->status,
            0,
            Option::GROUP_ADMIN
        );

        if ($this->status == 2) {
            $mallPermissions = json_decode($this->mall_permissions, true);
            $pluginPermissions = json_decode($this->plugin_permissions, true);
            $secondaryPermissions = json_decode($this->secondary_permissions, true);

            if (!$mallPermissions && !$pluginPermissions) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请选择权限'
                ];
            }

            CommonOption::set(
                Option::NAME_PERMISSIONS_LIST,
                [
                    'mall_permissions' => $mallPermissions,
                    'plugin_permissions' => $pluginPermissions,
                    'secondary_permissions' => $secondaryPermissions 
                ],
                0,
                Option::GROUP_ADMIN
            );
        }


        if (!$option) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }
}
