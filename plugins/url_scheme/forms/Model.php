<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 17:06
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\url_scheme\forms;

use app\core\response\ApiCode;

class Model extends \app\models\Model
{
    public function success($data)
    {
        $res = $this->handle($data);
        $res['code'] = ApiCode::CODE_SUCCESS;
        return $res;
    }

    public function fail($data)
    {
        $res = $this->handle($data);
        $res['code'] = ApiCode::CODE_ERROR;
        return $res;
    }

    private function handle($data)
    {
        $msg = $data['msg'] ?? '';
        unset($data['msg']);
        return [
            'msg' => $msg,
            'data' => $data
        ];
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    public function failByException($exception)
    {
        return $this->fail([
            'msg' => $exception->getMessage(),
            'errors' => $exception
        ]);
    }
}
