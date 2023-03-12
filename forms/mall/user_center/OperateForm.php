<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/20
 * Time: 2:21 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\user_center;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\UserCenter;

class OperateForm extends Model
{
    public $id;
    public $type;
    public $platform;

    public function rules()
    {
        return [
            [['id', 'type'], 'required'],
            ['id', 'integer'],
            ['type', 'string'],
            ['type', 'in', 'range' => ['recycle', 'resume', 'delete', 'choose']],
            ['platform', 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '操作选项'
        ];
    }

    public function operate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $type = $this->type;
        $model = UserCenter::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id, 'is_delete' => 0]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该用户中心配置不存在或已被删除'
            ];
        }
        return $this->$type($model);
    }

    /**
     * @param UserCenter $model
     * @return array
     * 加入回收站
     */
    private function recycle($model)
    {
        if ($model->platform) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该用户中心配置有设置平台，不能加入回收站'
            ];
        }
        $model->is_recycle = 1;
        if (!$model->save()) {
            $this->getErrorResponse($model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '加入回收站成功'
        ];
    }

    /**
     * @param UserCenter $model
     * @return array
     * 移出回收站
     */
    public function resume($model)
    {
        $model->is_recycle = 0;
        if (!$model->save()) {
            $this->getErrorResponse($model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '恢复成功'
        ];
    }

    /**
     * @param UserCenter $model
     * @return array
     * 删除
     */
    public function delete($model)
    {
        if ($model->is_recycle != 1) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '请先将用户中心配置加入回收站后，再进行删除'
            ];
        }
        $model->is_delete = 1;
        if (!$model->save()) {
            $this->getErrorResponse($model);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    /**
     * @param UserCenter $model
     * @return array
     * 设置平台
     */
    public function choose($model)
    {
        if (!$this->platform || !is_array($this->platform)) {
            $this->platform = [];
        }
        $this->setPlatform($model, $this->platform);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '设置成功'
        ];
    }

    /**
     * @param UserCenter $model
     * @param $platform
     * 新版设置用户中心首页数据
     */
    public function setPlatform($model, $platform)
    {
        $diffPlatform = array_diff($platform, explode(',', $model->platform));
        /** @var UserCenter[] $diffAll */
        $diffAll = UserCenter::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->findInSetOr($diffPlatform, 'platform')
            ->all();
        foreach ($diffAll as $diff) {
            $diff->platform = array_reduce(
                explode(',', $diff->platform),
                function ($return, $var) use ($diffPlatform) {
                    if (!in_array($var, $diffPlatform)) {
                        $return .= ',' . $var;
                    }
                    return ltrim($return, ',');
                },
                ''
            );
            $diff->save();
        }
        $model->platform = implode(',', $platform);
        $model->save();
        return true;
    }
}
