<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api;


use app\core\response\ApiCode;
use app\forms\api\home_page\HomeBannerForm;
use app\forms\api\home_page\HomeBlockForm;
use app\forms\api\home_page\HomeCatForm;
use app\forms\api\home_page\HomeCouponForm;
use app\forms\api\home_page\HomeNavForm;
use app\forms\api\home_page\HomeTopicForm;
use app\forms\common\CommonAppConfig;
use app\forms\common\video\Video;
use app\models\Mall;
use app\models\Model;
use app\models\Store;
use app\plugins\diy\Plugin;

class IndexForm extends Model
{
    private $type;

    public $page_id;
    public $mch_id_list;

    public function rules()
    {
        return [
            [['page_id', 'mch_id_list'], 'integer']
        ];
    }

    public function getIndex()
    {
        try {
            /* @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            $this->type = 'diy';
            $page = $plugin->getPage($this->page_id);
        } catch (\Exception $exception) {
            \Yii::warning('diy页面报错');
            \Yii::warning($exception);
            $page = $this->getDefault();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'home_pages' => $page,
                'type' => $this->type
            ]
        ];
    }

    public function getDefault()
    {
        $homePages = CommonAppConfig::getHomePageConfig();
        $newList = [];
        // 商品分类
        $isOpenCat = 0;
        $isAllCat = 0;
        $catIds = [];

        $isOpenCoupon = 0;
        // 魔方
        $isOpenBlock = 0;
        $blockIds = [];
        // 轮播图
        $isOpenBanner = 0;
        $isOpenHomeNav = 0;
        $isOpenTopic = 0;
        // 统一查询数据
        foreach ($homePages as $homePageKey => $homePage) {
            if ($homePage['key'] === 'cat') {
                $isOpenCat = 1;
                if ($homePage['relation_id'] == 0) {
                    $isAllCat = 1;
                }
                $catIds = array_merge($catIds, [$homePage['relation_id']]);
            } elseif ($homePage['key'] === 'coupon') {
                $isOpenCoupon = 1;
            } elseif ($homePage['key'] === 'block') {
                $isOpenBlock = 1;
                $blockIds[] = $homePage['relation_id'];
            } elseif ($homePage['key'] === 'banner') {
                $isOpenBanner = 1;
            } elseif ($homePage['key'] === 'home_nav') {
                $isOpenHomeNav = 1;
            } elseif ($homePage['key'] === 'topic') {
                $isOpenTopic = 1;
            } else {
            }
        }

        if ($isOpenCat) {
            $homeCatForm = new HomeCatForm();
            $catGoods = $homeCatForm->getCatGoods($catIds, $isAllCat);
        }
        if ($isOpenCoupon) {
            $homeCouponForm = new HomeCouponForm();
            $coupons = $homeCouponForm->getCouponList();
        }
        if ($isOpenBanner) {
            $homeBannerForm = new HomeBannerForm();
            $banners = $homeBannerForm->getBanners();
        }
        if ($isOpenBlock) {
            $homeBlockForm = new HomeBlockForm();
            $blocks = $homeBlockForm->getBlock($blockIds);
        }
        if ($isOpenHomeNav) {
            $homeNavForm = new HomeNavForm();
            $homeNavs = $homeNavForm->getHomeNav();
        }
        if ($isOpenTopic) {
            $homeTopicForm = new HomeTopicForm();
            $topics = $homeTopicForm->getTopics();
        }
        $homePages[] = [
            'key' => 'fxhb',
            'name' => '裂变红包'
        ];

        // 统一处理数据
        foreach ($homePages as $homePageKey => $homePage) {
            if ($homePage['key'] === 'cat') {
                $list = $homeCatForm->getNewCatGoods($homePage, $catGoods);
                foreach ($list as $item) {
                    $newList[] = $item;
                }
            } elseif ($homePage['key'] === 'coupon') {
                $homePage['coupons'] = $coupons;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'block') {
                $homeBlockForm = new HomeBlockForm();
                $newList[] = $homeBlockForm->getNewBlocks($homePage, $blocks);
            } elseif ($homePage['key'] === 'banner') {
                $homePage['banners'] = $banners;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'home_nav') {
                $homePage['home_navs'] = $homeNavs;
                // TODO 兼容 2019-6-27
                if (!isset($homePage['row_num'])) {
                    $homePage['row_num'] = 4;
                }
                foreach ($homePage['home_navs'] as $i => $v) {
                    if ($v['open_type'] == 'contact' && \Yii::$app->appPlatform === APP_PLATFORM_TTAPP) {
                        array_splice($homePage['home_navs'], $i, 1);
                    }
                }
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'topic') {
                $homePage['topics'] = $topics;
                $newList[] = $homePage;
            } elseif ($homePage['key'] === 'video') {
                $homePage['video_url'] = Video::getUrl($homePage['video_url']);
                $newList[] = $homePage;
            } else {
                try {
                    $plugin = \Yii::$app->plugin->getPlugin($homePage['key']);
                    $homePage[$homePage['key']] = $plugin->getHomePage('api');
                } catch (\Exception $exception) {
                }
                $newList[] = $homePage;
            }
        }
        $this->type = 'mall';
        return $newList;
    }

    public function shopStatus()
    {
        try {

            $mall = new Mall();
            $mallSetting = $mall->getMallSetting([
                'is_open',
                'open_type',
                'week_list',
                'time_list',
                'is_auto_open',
                'auto_open_time'
            ]);

            $data[] = array_merge($this->handleData($mallSetting), ['name' => '商城', 'mch_id' => 0]);

            if ($this->mch_id_list) {
                $storeList = Store::find()->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => json_decode($this->mch_id_list, true),
                    'is_delete' => 0
                ])->andWhere(['>', 'mch_id', 0])->all();

                foreach ($storeList as $key => $store) {
                    $extraAttributes = json_decode($store->extra_attributes, true);

                    $mchSetting = [
                        'is_open' => $extraAttributes['is_open'] ?: 1,
                        'open_type' => $extraAttributes['open_type'] ?: 1,
                        'week_list' => $extraAttributes['week_list'] ?: [],
                        'time_list' => $extraAttributes['time_list'] ?: [],
                        'is_auto_open' => $extraAttributes['is_auto_open'] ?: 0,
                        'auto_open_time' => $extraAttributes['auto_open_time'] ?: 0
                    ];

                    $data[] = array_merge($this->handleData($mchSetting), ['name' => $store->name, 'mch_id' => $store->mch_id]);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data
            ];
        } catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function handleData($setting)
    {
        $data['is_open'] = $setting['is_open'];
        $data['auto_open_text'] = '';

        // 营业
        if ($setting['is_open'] == 1) {
            if ($setting['open_type'] == 2) {

                // 不在指定时间
                $sign = false;
                foreach ($setting['time_list'] as $key => $listItem) {
                    if ($this->checkTime(time(), $listItem['value'], $setting['week_list'])) {
                        $sign = true;
                    }
                }

                $sign = empty($setting['time_list']) ? true : $sign;

                if ($data['is_open'] == 1) {
                    $data['is_open'] = $sign ? 1 : 2;
                }

            }
        } else {
            // 设置自动开业时间
            if ($setting['is_auto_open'] == 2) {
                if (date('Y-m-d H:i:s') > $setting['auto_open_time']) {
                    $data['is_open'] = 1;
                } else {
                    $data['auto_open_text'] = sprintf('将于%s开始营业', $setting['auto_open_time']);
                }
            }
        }

        return $data;
    }

    private function checkTime($time, $timeBetween, $weekList)
    {
        $dayStr = date('Y-m-d ',time()); // 当天日期
        $beforeDayStr = date('Y-m-d ',time() - 86000);// 前一天日期
        $lastDayStr = date('Y-m-d ',time() + 86000);// 后一天日期

        $week = date('w'); // 当天星期
        $beforeWeek = date('w', strtotime($beforeDayStr)); // 前一天星期

        $timeBegin = strtotime($dayStr.$timeBetween[0]);
        $timeEnd = strtotime($dayStr.$timeBetween[1]);

        $sign = false;
        // 如果是ture 则表示这两个时间范围是跨天时间
        if ($timeBegin > $timeEnd) {
            $list = [];
            if (in_array($beforeWeek, $weekList)) {
                $list[] = [
                    'time_begin' => strtotime($beforeDayStr.$timeBetween[0]),
                    'time_end' => strtotime($dayStr.$timeBetween[1])
                ];
            }

            if (in_array($week, $weekList)) {
                $list[] = [
                    'time_begin' => strtotime($dayStr.$timeBetween[0]),
                    'time_end' => strtotime($lastDayStr.$timeBetween[1])
                ];
            }

            foreach ($list as $item) {
                if ($time > $item['time_begin'] && $time < $item['time_end']) {
                    $sign = true;
                }
            }
        } else {
            // 判断当天时间
            if (in_array(date('w'), $weekList) && $time > $timeBegin && $time < $timeEnd) {
                $sign = true;
            }
        }

        return $sign;
    }
}
