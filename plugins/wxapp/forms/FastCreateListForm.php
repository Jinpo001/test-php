<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/10/29
 * Time: 17:15
 */

namespace app\plugins\wxapp\forms;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappFastCreate;
use yii\helpers\ArrayHelper;

class FastCreateListForm extends Model
{
    public $status;
    public $page;
    public $limit;
    public $keyword;
    public $search_type;

    public function rules()
    {
        return [
            [['keyword'], 'string'],
            [['page', 'limit', 'status', 'search_type'], 'integer'],
            ['status', 'in', 'range' => [0, 1, 2, 3]],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20]
        ];
    }

    public function search()
    {
        $query = WxappFastCreate::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($this->keyword) {
            switch ($this->search_type) {
                case 1:
                    $query->andWhere(['like', 'name', $this->keyword]);
                    break;
                case 2:
                    $query->andWhere(['like', 'code', $this->keyword]);
                    break;
                case 3:
                    $query->andWhere(['like', 'legal_persona_name', $this->keyword]);
                    break;
                case 4:
                    $query->andWhere(['like', 'legal_persona_wechat', $this->keyword]);
                    break;
                case 5:
                    $query->andWhere(['like', 'component_phone', $this->keyword]);
                    break;
                case 6:
                    $query->andWhere(['like', 'appid', $this->keyword]);
                    break;
            }
        }
        if (isset($this->status)) {
            $query->andWhere(['status' => $this->status]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)->all();
        $newList = [];
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            $newItem['status_text'] = ($this->errorCode()[$item['status']]) ?? "未知状态,错误代码{$item['status']}";
            $newList[] = $newItem;
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ],
        ];
    }

    private function errorCode()
    {
        return [
            -2 => '已提交审核',
            0 => '已创建',
            89249 => '该主体已有任务执行中，距上次任务 24h 后再试',
            89247 => '内部错误',
            86004 => '无效微信号',
            61070 => '法人姓名与微信号不一致',
            89248 => '企业代码类型无效，请选择正确类型填写',
            89250 => '未找到该任务',
            89251 => '待法人人脸核身校验',
            89252 => '法人&企业信息一致性校验中',
            89253 => '缺少参数',
            89254 => '第三方权限集不全，补全权限集全网发布后生效',
            89255 => 'code参数无效，请检查code长度以及内容是否正确 ；注意code_type的值不同需要传的code长度不一样',
        ];
    }
}
