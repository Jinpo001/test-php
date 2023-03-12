<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/20
 * Time: 3:55 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\platform;

use app\models\Model;
use app\models\User;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\plugins\Plugin;

class PlatformConfig extends Model
{
    public static function getInstance()
    {
        return new self();
    }

    private static $platformIconList;

    /**
     * @param bool $checkPermission
     * @return array
     * 获取商城可使用的平台图标
     */
    public static function getPlatformIconUrl($checkPermission = false)
    {
        if (self::$platformIconList && !$checkPermission) {
            return self::$platformIconList;
        }
        /** @var Plugin[] $platformList */
        $platformList = \Yii::$app->plugin->getAllPlatformPlugins();
        $permission = \Yii::$app->mall->role->getPermission();
        $list = [];
        foreach ($platformList as $plugin) {
            if (!in_array($plugin->getName(), $permission) && $checkPermission) {
                continue;
            }
            $list = array_merge($list, $plugin->getPlatformIconUrl());
        }
        self::$platformIconList = $list;
        return $list;
    }

    /**
     * @param User $user
     * @param bool $only
     * @return string
     * 获取用户的平台标示
     */
    public function getPlatform($user, $only = false)
    {
        if (!$user) {
            return '';
        }
        $userPlatform = $user->userPlatform;
        $list = array_column($userPlatform, null, 'platform');
        $count = count($list);
        switch ($count) {
            case 1:
                $platform = $userPlatform[0]->platform;
                break;
            case 2:
                $platform = 'wxapp_wechat';
                if ($only) {
                    $platform = 'wxapp';
                }
                break;
            default:
                $platform = $user->userInfo->platform;
        }
        return $platform;
    }

    /**
     * @param User $user
     * @return string
     * 获取用户平台标示名称
     */
    public function getPlatformText($user)
    {
        $detail = $this->getPlatformDetail($user);
        return $detail['text'];
    }

    /**
     * @param $user
     * @return mixed
     * 获取用户平台标示图标
     */
    public function getPlatformIcon($user)
    {
        $detail = $this->getPlatformDetail($user);
        return $detail['platform_icon'];
    }

    /**
     * @param $user
     * @return array
     * 获取用户平台信息
     */
    public function getPlatformDetail($user)
    {
        $webUri = '';
        if (\Yii::$app instanceof \yii\web\Application) {
            $webUri = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
        }
        $platformIconList = self::getPlatformIconUrl();
        $platformIconList = array_column($platformIconList, null, 'key');
        $platform = $this->getPlatform($user);
        switch ($platform) {
            case UserInfo::PLATFORM_WXAPP:
                $detail = [
                    'text' => '微信小程序',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case UserInfo::PLATFORM_ALIAPP:
                $detail = [
                    'text' => '支付宝',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case UserInfo::PLATFORM_BDAPP:
                $detail = [
                    'text' => '百度',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case UserInfo::PLATFORM_TTAPP:
                $detail = [
                    'text' => '抖音/头条',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case UserInfo::PLATFORM_WECHAT:
                $detail = [
                    'text' => '公众号',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case UserInfo::PLATFORM_H5:
                $detail = [
                    'text' => 'h5商城',
                    'icon' => [
                        $platform => $platformIconList[$platform]['icon']
                    ],
                    'platform_icon' => $platformIconList[$platform]['icon'],
                ];
                break;
            case 'wxapp_wechat':
                $detail = [
                    'text' => '微信',
                    'icon' => [
                        UserInfo::PLATFORM_WXAPP => $platformIconList[UserInfo::PLATFORM_WXAPP]['icon'],
                        UserInfo::PLATFORM_WECHAT => $platformIconList[UserInfo::PLATFORM_WECHAT]['icon']
                    ],
                    'platform_icon' => $webUri . '/statics/img/mall/wx.png'
                ];
                break;
            default:
                $detail = [
                    'text' => '未知',
                    'icon' => [
                        $this->webappIcon,
                    ],
                    'openid' => [],
                    'platform_icon' => $webUri . '/' . $this->webappIcon,
                ];
                break;
        }
        $detail['openid'] = $this->getPlatformOpenid($user);
        $detail['platform'] = $platform;
        return $detail;
    }

    public $webappIcon = 'statics/img/mall/site.png';

    /**
     * @param User $user
     * @param bool $only 默认取wxapp
     * @return mixed
     * 获取用户平台对应的openid
     */
    public function getPlatformOpenid($user, $only = false)
    {
        if (!$user) {
            return '';
        }
        $userPlatform = $user->userPlatform;
        $platformId = array_column($userPlatform, 'platform_id', 'platform');
        $count = count($platformId);
        switch ($count) {
            case 1:
                return [
                    $userPlatform[0]->platform => $userPlatform[0]->platform_id
                ];
                break;
            case 2:
                if ($only) {
                    return [
                        'wxapp' => $platformId['wxapp']
                    ];
                }
                return $platformId;
                break;
            default:
                return [
                    $user->userInfo->platform => $user->userInfo->platform_user_id
                ];
        }
    }

    /**
     * @param $user
     * @param $platform
     * @return bool
     * 校验用户是否属于某个平台
     */
    public function check($user, $platform)
    {
        $userPlatform = $this->getPlatform($user);
        if ($platform === $userPlatform || in_array($platform, explode('_', $userPlatform))) {
            return true;
        } else {
            return false;
        }
    }

    public function searchPlatform($query, $platform, $alias)
    {
        $userPlatformQuery = UserPlatform::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'platform' => $platform])
            ->select('user_id');
        $query->leftJoin(['ui' => UserInfo::tableName()], 'ui.user_id = ' . $alias . '.user_id')->keyword(
            $platform,
            [
                'or',
                ['ui.platform' => $platform],
                [$alias . '.user_id' => $userPlatformQuery]
            ]
        );
        return $query;
    }
}
