<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/20
 * Time: 4:55 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\url_scheme\forms;

use app\plugins\url_scheme\models\UrlScheme;

class OperateForm extends Model
{
    public $operate;
    public $ids;
    public $id;

    public function rules()
    {
        return [
            [['operate'], 'required'],
            [['operate'], 'in', 'range' => ['delete', 'batch_delete']],
            [['id'], 'integer'],
            [['ids'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'operate' => '操作方式'
        ];
    }

    public function execute()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            switch ($this->operate) {
                case 'delete':
                    if (!$this->id) {
                        throw new \Exception('请选择需要删除的链接');
                    }
                    $condition = ['mall_id' => \Yii::$app->mall->id, 'id' => $this->id];
                    break;
                case 'batch_delete':
                    if (!$this->ids) {
                        throw new \Exception('请选择需要删除的链接');
                    }
                    $condition = ['mall_id' => \Yii::$app->mall->id, 'id' => $this->ids];
                    break;
                default:
                    throw new \Exception('错误的操作方式');
            }
            $res = UrlScheme::updateAll(['is_delete' => 1], $condition);
            return $this->success([
                'msg' => '删除成功',
                'content' => $res
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }
}
