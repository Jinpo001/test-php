<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\ExchangeToken;
use app\plugins\exchange\forms\exchange\validate\FacadeAdmin;
use app\plugins\exchange\models\ExchangeCode;

class GetCode extends Model
{
    public $token;
    public $created_at;
    public $library_id;
    public const KEY = 'exchange-auto-poster-code:';

    public function rules()
    {
        return [
            [['token', 'created_at', 'library_id'], 'required'],
            [['token', 'created_at'], 'string'],
            [['library_id'], 'number'],
        ];
    }

    public static function destroyCache($mall_id, $user_id)
    {
        $key = GetCode::KEY . $mall_id;
        $value = \Yii::$app->cache->get($key);
        if (
            !empty($value)
            && isset($value[$user_id])
            && $value[$user_id]
        ) {
            unset($value[$user_id]);
            \Yii::$app->cache->set($key, $value, 0);
        }
    }

    private function getCache()
    {
        $key = GetCode::KEY . \Yii::$app->mall->id;
        return \Yii::$app->cache->get($key) ?: [];
    }

    private function setCache($value)
    {
        $key = GetCode::KEY . \Yii::$app->mall->id;
        \Yii::$app->cache->set($key, $value, 0);
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $r = ExchangeToken::valid($this->library_id, $this->created_at, $this->token);
            if (!$r) {
                throw new \Exception('二维码无效');
            }
            $f = new FacadeAdmin();
            $f->user(\Yii::$app->user->id);
            $f->library(\Yii::$app->mall->id, $this->library_id);
            $libraryModel = $f->validate->libraryModel;

            $f->validate->hasExchangeSetting([
                'is_anti_brush' => 0,
                'is_limit' => $libraryModel->is_limit,
                'limit_user_num' => $libraryModel->limit_user_num,
                'limit_user_success_num' => $libraryModel->limit_user_success_num,
                'limit_user_type' => $libraryModel->limit_user_type,
            ]);

            $query = ExchangeCode::find()->where([
                'mall_id' => $libraryModel->mall_id,
                'library_id' => $libraryModel->id,
                'type' => ExchangeCode::TYPE_ADMIN,
                'status' => 1,
            ]);
            if ($libraryModel->expire_type !== 'all') {
                $time = date('Y-m-d H:i:s');
                $query->andWhere([
                    'AND',
                    ['<', 'valid_start_time', $time],
                    ['>=', 'valid_end_time', $time],
                ]);
            }
            $value = $this->getCache();
            $user_id = $f->validate->user->id;
            $code_id = $value[$user_id] ?? '';

            if ($code_id) {
                $query2 = clone $query;
                $userCode = $query2->andWhere(['id' => $code_id])->one();
                if ($userCode) {
                    $code = $userCode->code;
                } else {
                    unset($value[$user_id]);
                    $code = $this->selectCode($f, $query, $value);
                }
            } else {
                $code = $this->selectCode($f, $query, $value);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'code' => $code,
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    private function selectCode(FacadeAdmin $f, $query, $value)
    {
        $ids = array_values($value);
        $sql = sprintf(
            "%s ORDER BY %s`created_at` ASC, `id` ASC",
            $query->select('id,code')->createCommand()->getRawSql(),
            empty($ids) ? '' : sprintf("field(id, %s), ", implode(',', $ids))
        );
        $code = \Yii::$app->db->createCommand($sql)->queryOne();

        $f->validate->hasNullLibrary($code);

        $value[\Yii::$app->user->id] = $code['id'];
        $this->setCache($value);
        return $code['code'];
    }
}
