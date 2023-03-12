<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\community\forms\api\poster_share;


use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\plugins\community\forms\Model;

class PosterNewForm extends Model implements BasePoster
{
    public $style;
    public $type;
    public $goods_id;
    public $color;

    public $middleman_id;
    public $activity_id;

    public function rules()
    {
        return [
            [['style', 'type'], 'required'],
            [['style', 'type', 'middleman_id', 'activity_id'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            dd($e);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }


    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $class = $this->getClass($this->style);

        $detail = new \app\plugins\community\forms\api\activity\PosterConfigForm();
        $detail->attributes = $this->attributes;
        $return = $detail->getDetail();

        if ($return['code'] !== 0) {
            throw new \Exception($return['msg']);
        }
        $class->type = $this->type;
        $class->color = $this->color;
        $class->activity_id = $this->activity_id;
        $class->middleman_id = $this->middleman_id;
        $class->extraModel = \app\plugins\community\forms\api\poster_share\PosterCustomize::className();
        $class->other = $return['data'];
        return $class->build();
    }


    /**
     * @param int $key
     * @return StyleGrafika
     * @throws \Exception
     */
    private function getClass(int $key): StyleGrafika
    {
        $map = [
            1 => 'app\plugins\community\forms\api\poster_share\style\StyleOne',
            2 => 'app\plugins\community\forms\api\poster_share\style\StyleTwo',
            3 => 'app\plugins\community\forms\api\poster_share\style\StyleThree',
            4 => 'app\plugins\community\forms\api\poster_share\style\StyleFour',
        ];
        if (isset($map[$key]) && class_exists($map[$key])) {
            return new $map[$key];
        }
        throw new \Exception('调用错误');
    }
}
