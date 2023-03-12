<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\forms\common\CommonTemplate;
use app\plugins\diy\models\DiyTemplate;
use yii\helpers\Json;


class ModuleEditForm extends Model
{
    public $id;
    public $name;
    public $data;

    /** 'module' */

    public function rules()
    {
        return [
            [['name', 'data'], 'required'],
            [['id'], 'integer'],
            [['name', 'data'], 'string'],
            [['data'], dataValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '模块名称',
            'data' => '模块内容',
            'type' => '类型',
        ];
    }

    public function post()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $common = CommonTemplate::getCommon();
            $template = $common->getTemplate($this->id);
            if (!$template) {
                $template = new DiyTemplate();
                $template->type = DiyTemplate::TYPE_MODULE;
                $template->is_delete = 0;
                $template->mall_id = \Yii::$app->mall->id;
            }
            $template->name = $this->name;
            $data = Json::decode($this->data, true);
            foreach ($data as $key => $datum) {
                $data[$key] = $this->handle($datum);
            }
            $template->data = Json::encode($data, JSON_UNESCAPED_UNICODE);
            if (!$template->save()) {
                throw new \Exception('保存失败');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
                'data' => [
                    'id' => $template->id
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $e->getMessage(),
            ];
        }
    }

    protected function handle($datum)
    {
        if ($datum['id'] === 'rubik') {
            foreach ($datum['data']['list'] as $rIndex => $rItem) {
                try {
                    if ($datum['data']['style'] == 0) {
                        list($width, $height) = getimagesize($rItem['pic_url']);
                        $datum['data']['a_height'] = price_format($height * 750 / $width);
                    }
                } catch (\Exception $exception) {
                }
            }
        }
        return $datum;
    }
}