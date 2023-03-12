<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/10/29
 * Time: 16:56
 */

namespace app\plugins\wxapp\forms;

use app\core\response\ApiCode;
use app\forms\open3rd\ExtAppForm;
use app\forms\open3rd\Open3rdException;
use app\models\Model;
use app\plugins\wxapp\models\WxappFastCreate;

class FastCreateForm extends Model
{
    public $name;
    public $code;
    public $code_type;
    public $legal_persona_wechat;
    public $legal_persona_name;
    public $component_phone;

    public function rules()
    {
        return [
            [['name', 'code', 'code_type', 'legal_persona_wechat', 'legal_persona_name', 'component_phone'], 'required'],
            [['name', 'code', 'code_type', 'legal_persona_wechat', 'legal_persona_name', 'component_phone'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '企业名',
            'code' => '企业代码',
            'code_type' => '企业代码类型',
            'legal_persona_wechat' => '法人微信',
            'legal_persona_name' => '法人姓名',
            'component_phone' => '第三方联系电话'
        ];
    }


    public function create()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            if (!in_array('fast-create-wxapp', $permission)) {
                throw new \Exception('无快速创建小程序的权限');
            }
            $isNew = false;
            $md5 = md5(json_encode([
                'name' => $this->name,
                'code' => $this->code,
                'legal_persona_wechat' => $this->legal_persona_wechat,
                'legal_persona_name' => $this->legal_persona_name,
            ]));
            $wxapp = WxappFastCreate::findOne(['md5' => $md5, 'is_delete' => 0]);
            if (!$wxapp) {
                $wxapp = new WxappFastCreate();
            }
            $wxapp->mall_id = \Yii::$app->mall->id;
            $wxapp->name = $this->name;
            $wxapp->code = $this->code;
            $wxapp->code_type = $this->code_type;
            $wxapp->legal_persona_name = $this->legal_persona_name;
            $wxapp->legal_persona_wechat = $this->legal_persona_wechat;
            $wxapp->component_phone = $this->component_phone;
            $wxapp->md5 = $md5;
            if (!$wxapp->isNewRecord) {
                $wxapp->status = -2;
            }
            if (!$wxapp->save()) {
                throw new \Exception((new Model())->getErrorMsg($wxapp));
            }

            $ext = ExtAppForm::instance(null, 1);
            $res = $ext->fastCreate(
                $this->name,
                $this->code,
                $this->code_type,
                $this->legal_persona_wechat,
                $this->legal_persona_name,
                $this->component_phone
            );
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '创建成功',
            ];
        } catch (Open3rdException $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getInfo()
    {
        $app = WxappFastCreate::find()->where([['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]])
            ->orderBy('id desc')->one();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $app
        ];
    }
}
