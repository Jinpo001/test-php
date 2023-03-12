<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\admin\file;

use app\core\response\ApiCode;
use app\forms\mall\file\DeleteFileJob;
use app\models\CoreFile;
use app\models\Model;

class FileForm extends Model
{
    public $id;
    public $keyword;
    public $time;
    public $status;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword', 'status'], 'string'],
            [['time'], 'safe'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        
        try {
            $query = CoreFile::find()->andWhere([
                'mall_id' => 0,
                'mch_id' => 0,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id
            ]);

            if ($this->keyword) {
                $query->andWhere(['like', 'file_name', $this->keyword]);
            }

            if (is_array($this->time) && count($this->time) == 2) {
                $query->andWhere(['>=', 'created_at', $this->time[0]]);
                $query->andWhere(['<=', 'created_at', $this->time[1]]);
            }

            if ($this->status != '' && $this->status != null) {
                $query->andWhere(['status' => $this->status]);
            }

            $list = $query->page($pagination)->orderBy('created_at DESC')->all();

            $newList = [];
            foreach ($list as $item) {
                $id = 'admin_' . \Yii::$app->user->id;
                $downloadUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/csv/' . $id . '/' . $item->file_name;
                $newList[] = [
                    'id' => $item->id,
                    'created_at' => $item->created_at,
                    'percent' => ($item->percent * 100) . '%',
                    'status' => $item->status,
                    'status_text' => $item->getStatusText($item),
                    'download_url' => $downloadUrl,
                    'file_name' => $item->file_name,
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination
                ]
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function destroyAll()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $res = CoreFile::updateAll([
                'is_delete' => 1
            ], [
                'mall_id' => 0,
                'mch_id' => 0,
                'user_id' => \Yii::$app->user->id
            ]);

            $id = 'admin_' . \Yii::$app->user->id;
            $filePath = sprintf('%s%s%s%s', \Yii::$app->basePath, '/web/csv/', $id, '/');
            if (file_exists($filePath)) {
                $class = new DeleteFileJob(['file_path' => $filePath]);
                $queueId = \Yii::$app->queue4->delay(0)->push($class);
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch(\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $coreFile = CoreFile::find()->andWhere([
                'mall_id' => 0,
                'mch_id' => 0,
                'id' => $this->id,
                'is_delete' => 0,
                'user_id' => \Yii::$app->user->id
            ])->one();

            if (!$coreFile) {
                throw new \Exception('数据不存在');
            }

            $coreFile->is_delete = 1;
            $res = $coreFile->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($coreFile));
            }

            $id = 'admin_' . \Yii::$app->user->id;
            $filePath = sprintf('%s%s%s%s%s', \Yii::$app->basePath, '/web/csv/', $id, '/', $coreFile->file_name);
            if (file_exists($filePath)) {
                $class = new DeleteFileJob(['file_path' => $filePath]);
                $queueId = \Yii::$app->queue4->delay(0)->push($class);
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch(\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
