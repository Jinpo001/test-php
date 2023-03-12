<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/29
 * Time: 5:35 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\template_msg;

use app\core\response\ApiCode;
use app\models\Model;

class IndexTemplateForm extends Model
{
    public $list;

    public function rules()
    {
        return [
            ['list', 'required'],
        ];
    }

    /**
     * @return array
     * 调取平台插件接口保存模板消息
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $list = $this->list;
            foreach ($list as $item) {
                $plugin = \Yii::$app->plugin->getPlugin($item['key']);
                if (!$plugin || !isset($item['list'])) {
                    continue;
                }
                if (method_exists($plugin, 'addTemplateList')) {
                    $res = $plugin->addTemplateList($item['list']);
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
