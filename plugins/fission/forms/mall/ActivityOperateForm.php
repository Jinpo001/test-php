<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/19
 * Time: 11:36 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;

class ActivityOperateForm extends Model
{
    public $id;
    public $ids;
    public $operate;

    public function rules()
    {
        return [
            ['id', 'integer'],
            ['operate', 'in', 'range' => ['delete', 'up', 'down', 'batch_delete', 'batch_up', 'batch_down']],
            ['ids', 'safe']
        ];
    }

    public function operate()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            switch ($this->operate) {
                case 'up':
                    if (!$this->id) {
                        throw new \Exception('请选择需要上架的活动');
                    }
                    $attribute = ['status' => 1];
                    $condition = ['id' => $this->id, 'mall_id' => \Yii::$app->mall->id];
                    break;
                case 'batch_up':
                    if (!$this->ids || empty($this->ids)) {
                        throw new \Exception('请选择需要上架的活动');
                    }
                    $attribute = ['status' => 1];
                    $condition = ['id' => $this->ids, 'mall_id' => \Yii::$app->mall->id];
                    break;
                case 'down':
                    if (!$this->id) {
                        throw new \Exception('请选择需要下架的活动');
                    }
                    $attribute = ['status' => 0];
                    $condition = ['id' => $this->id, 'mall_id' => \Yii::$app->mall->id];
                    break;
                case 'batch_down':
                    if (!$this->ids || empty($this->ids)) {
                        throw new \Exception('请选择需要下架的活动');
                    }
                    $attribute = ['status' => 0];
                    $condition = ['id' => $this->ids, 'mall_id' => \Yii::$app->mall->id];
                    break;
                case 'delete':
                    if (!$this->id) {
                        throw new \Exception('请选择需要删除的活动');
                    }
                    $attribute = ['is_delete' => 1];
                    $condition = ['id' => $this->id, 'mall_id' => \Yii::$app->mall->id];
                    break;
                case 'batch_delete':
                    if (!$this->ids || empty($this->ids)) {
                        throw new \Exception('请选择需要删除的活动');
                    }
                    $attribute = ['is_delete' => 1];
                    $condition = ['id' => $this->ids, 'mall_id' => \Yii::$app->mall->id];
                    break;
                default:
                    throw new \Exception('错误的操作');
            }
            $count = FissionActivity::updateAll($attribute, $condition);
            return $this->success([
                'msg' => '操作成功',
                'extra' => $count
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }
}
