<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/20
 * Time: 3:31 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\url_scheme\forms;

use app\plugins\url_scheme\models\UrlScheme;

class ListForm extends Model
{
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            ['keyword', 'trim'],
            ['keyword', 'string'],
            ['page', 'integer']
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $list = UrlScheme::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($this->keyword !== '', ['like', 'name', $this->keyword])
                ->page($pagination, 20, $this->page)
                ->orderBy(['id' => SORT_DESC])
                ->all();
            $newList = [];
            /* @var UrlScheme[] $list */
            foreach ($list as $item) {
                if ($item->is_expire == 0) {
                    $expire = '永久有效';
                    $canUse = true;
                } else {
                    $time = strtotime($item->created_at);
                    $expire = mysql_timestamp(strtotime('+' . $item->expire_time . ' day', $time));
                    $canUse = $time + $item->expire_time * 86400 > time();
                }
                $newList[] = [
                    'name' => $item->name,
                    'created_at' => $item->created_at,
                    'expire' => $expire,
                    'can_use' => $canUse,
                    'url_scheme' => $item->url_scheme,
                    'url' => \Yii::$app->urlManager->createAbsoluteUrl(
                        ['site/scheme', 'id' => $item->id]
                    )
                ];
            }
            return $this->success([
                'list' => $newList,
                'pagination' => $pagination
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }
}
