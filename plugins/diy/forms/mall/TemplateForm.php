<?php

/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/24
 * Time: 15:43
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\models\Mall;
use app\models\Model;
use app\plugins\diy\forms\common\CommonPageTwo;
use app\plugins\diy\forms\mall\market\CommonTemplateCenter;
use app\plugins\diy\models\CoreTemplate;
use app\plugins\diy\models\CoreTemplateEdit;
use app\plugins\diy\models\CoreTemplateType;
use app\plugins\diy\models\DiyPage;
use app\plugins\diy\models\DiyTemplate;

/**
 * @property Mall $mall
 */
class TemplateForm extends Model
{
    public $page;
    public $limit;
    public $type;
    public $keyword;
    public $id;
    public $is_home_page;
    public $templateType;
    public $platform;
    public $access_limit;

    /** 'module' */

    public function rules()
    {
        return [
            [['page', 'limit', 'id', 'is_home_page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
            [['type', 'keyword', 'access_limit'], 'string'],
            [['type', 'keyword'], 'default', 'value' => ''],
            ['platform', 'safe'],
        ];
    }

    public function getMarketList()
    {
        $query = CoreTemplate::find()->with('edit')->where([
            'is_delete' => 0,
        ]);
        $templateId = CoreTemplateType::find()->where(['type' => $this->templateType])
            ->select('template_id')->column();

        if (!\Yii::$app->role->isSuperAdmin) {
            $templatePermission = \Yii::$app->role->getTemplate();
            $common = CommonTemplateCenter::getInstance();
            $showList = $common->getShowTemplate($templatePermission);
            $useList = $common->getUseTemplate($templatePermission);
            $templateId = array_intersect($showList, $useList, $templateId);
        }

        $list = $query->andWhere(['template_id' => $templateId])
            ->page($pagination, 23)->asArray()->all();
        foreach ($list as $key => $item) {
            $list[$key]['pics'] = \yii\helpers\BaseJson::decode($item['pics']);
            $list[$key]['is_use'] = 1;
            if ($item['edit']) {
                $list[$key]['name'] = $item['edit']['name'];
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ],
        ];
    }

    public function getHome()
    {
        $diyPage = DiyPage::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'is_home_page' => 1,
        ]);
        $template = $diyPage->template ?? null;
        if ($template) {
            $form = new TemplateEditForm();
            $form->id = $diyPage->id;
            $info = $form->get();

            //格式化api数据 可补充优化
            $data = CommonPageTwo::getCommon(\Yii::$app->mall, '', '');
            $apiData = $data->getPage(0, true);
            //商品
            $goodsTag = [
                //'module' => 'DELETE',
                'goods' => 'sGoods',
                'mch' => 'sMch',
                'pintuan' => 'sPintuan',
                'booking' => 'sBooking',
                'miaosha' => 'sMiaosha',
                'bargain' => 'sBargain',
                'integral-mall' => 'sIntegralMall',
                'lottery' => 'sLottery',
                'quick-nav' => 'sQuickNav',
                'advance' => 'sAdVance',
                'pick' => 'sPick',
                'gift' => 'sGift',
            ];
            foreach ($apiData as $key => $item) {
                if (
                    in_array($item['id'], array_keys($goodsTag))
                    && isset($item['data']['list'])
                    && is_array($item['data']['list'])
                ) {
                    foreach ($item['data']['list'] as $key1 => $item1) {
                        $apiData[$key]['data']['list'][$key1]['picUrl'] = $item1['cover_pic'];
                    }
                }
                if ($item['id'] === 'modal') {
                    //请联系其他开发者 为什么起0这个名字
                    $apiData[$key]['data']['list'] = $apiData[$key]['data']['list'][0];
                }
            }

            $info['data']['data'] = \yii\helpers\BaseJson::encode($apiData);
            return $info;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $template,
        ];
    }

    //FIXME
    private function childFork(array $list)
    {
        //        $key = 'diy-access-page-count:' . $this->mall->id;
        //        return \Yii::$app->cache->set($key, $values, 0);

        //        $key = 'diy-access-page-count:' . $this->mall->id;
        //        return \Yii::$app->cache->get($key) ?: [];


        $forkNums = ceil(count($list) / 2) > 10 ? 10 : ceil(count($list) / 2);
        if (!function_exists('pcntl_fork')) {
            return false;
        }
        for (
            $i = 0; $i < $forkNums;
            $i++
        ) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                \Yii::error('创建子进程失败');
                return false;
            } elseif ($pid) {
                pcntl_wait($status);
                //等待子进程中断，防止子进程成为僵尸进程。
            } else {
                //     $filename = sprintf('diy_template_fork_pid_%s.name', '80');
                //        $filename = '.' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $filename;
                //        file_put_contents($filename, 'data');
            }
        }
    }

    public function search()
    {
        $this->compatible();
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = DiyPage::find()->alias('p')->select('p.*')->where([
            'p.mall_id' => \Yii::$app->mall->id,
            'p.is_delete' => 0,
        ])->innerJoinwith(['templateOne t' => function ($query) {
            $query->where(['t.type' => DiyTemplate::TYPE_PAGE]);
        }]);
        //->leftJoin([
        //    'pn' => DiyPageNav::find()->select('page_id')->where([
        //        'mall_id' => \Yii::$app->mall->id,
        //    ])
        //], 'pn.page_id = p.id')->groupBy('p.id');
        empty($this->keyword) || $query->andWhere(['or',['like', 't.name', $this->keyword],['like', 'p.id', $this->keyword]]);
        $list = $query
            //->having(['num' => 1])
            ->page($pagination, 10)
            ->orderBy(['p.platform' => SORT_DESC, 'p.id' => SORT_DESC])
            ->asArray()
            ->all();
        //$this->childFork($list);

        $newList = [];
        $model = CommonPageTwo::getCommon(\Yii::$app->mall);
        $diyAccessLog = $model->getLog();
        $platformIconList = PlatformConfig::getPlatformIconUrl(true);
        $platformIconList = array_column($platformIconList, null, 'key');
        //todo slow
        foreach ($list as $key => $template) {
            $platform = $template['platform'] ? explode(',', $template['platform']) : [];
            $newPlatform = [];
            foreach ($platform as $value) {
                if (!isset($platformIconList[$value])) {
                    continue;
                }
                $newPlatform[] = [
                    'text' => $platformIconList[$value]['name'],
                    'icon' => $platformIconList[$value]['icon']
                ];
            }

            array_push($newList, [
                'id' => $template['id'],
                'name' => $template['title'],
                'is_home_page' => $template['is_home_page'],
                'created_at' => $template['created_at'],
                'userCount' => count($diyAccessLog[$template['id']]['userIds'] ?? []),
                'accessCount' => $diyAccessLog[$template['id']]['accessCount'] ?? 0,
                'platform' => $newPlatform,
                'access_limit' => \yii\helpers\BaseJson::decode($template['access_limit']) ?? [
                        'is_all' => 1,
                        'member' => []
                    ],
                //'goodsCount' => $goodsCount,
            ]);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }


    public function destroy($id)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $diyPage = DiyPage::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $id,
            ]);
            if (!$diyPage) {
                throw new \Exception('数据已删除');
            }

            $diyPage->is_delete = 1;
            $diyPage->save();
            if ($diyPage->template) {
                foreach ($diyPage->template as $template) {
                    $template->is_delete = 1;
                    $template->save();
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function changeHasHomeStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $diyPage = DiyPage::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
            ]);
            if (empty($diyPage)) {
                throw new \Exception('数据不存在');
            }
            if (!$this->platform || !is_array($this->platform)) {
                $this->platform = [];
            }
            $this->setPlatform($diyPage, $this->platform);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '设置成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function changeAccessLimit()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $diyPage = DiyPage::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0,
            ]);
            if (empty($diyPage)) {
                throw new \Exception('数据不存在');
            }
            $diyPage->access_limit = $this->access_limit;
            $diyPage->save();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * @param DiyPage $diyPage
     * @param $platform
     * @return bool
     * 新版设置diy首页数据
     */
    public function setPlatform($diyPage, $platform)
    {
        $diffPlatform = array_diff($platform, explode(',', $diyPage->platform));
        /** @var DiyPage[] $diyPageDiffAll */
        $diyPageDiffAll = DiyPage::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->findInSetOr($diffPlatform, 'platform')
            ->all();
        foreach ($diyPageDiffAll as $diyPageDiff) {
            $diyPageDiff->platform = array_reduce(
                explode(',', $diyPageDiff->platform),
                function ($return, $var) use ($diffPlatform) {
                    if (!in_array($var, $diffPlatform)) {
                        $return .= ',' . $var;
                    }
                    return ltrim($return, ',');
                },
                ''
            );
            $diyPageDiff->save();
        }
        $diyPage->platform = implode(',', $platform);
        $diyPage->save();
        return true;
    }

    /**
     * @return bool
     * 变更旧版diy首页数据到新版
     */
    public function compatible()
    {
        $model = DiyPage::findOne(['mall_id' => \Yii::$app->mall->id, 'is_home_page' => 1, 'is_delete' => 0]);
        if (!$model) {
            return false;
        }
        $model->is_home_page = 0;
        $platform = PlatformConfig::getInstance()->getPlatformIconUrl(true);
        $platformList = array_column($platform, 'key');
        $this->setPlatform($model, $platformList);
        return true;
    }
}
