<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\events\ShareMemberEvent;
use app\forms\common\config\UserCenterConfig;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\RemoveIdentityInfo;
use app\forms\common\template\TemplateList;
use app\handlers\HandlerRegister;
use app\models\MallMembers;
use app\models\Model;
use app\models\User;

class UserEditForm extends Model
{
    public $id;
    public $member_level;
    public $money;

    public $is_blacklist;
    public $remark;
    public $contact_way;
    public $parent_id;
    public $remark_name;

    public function rules()
    {
        return [
            [['parent_id', 'is_blacklist', 'id', 'member_level'], 'integer'],
            [['money'], 'number', 'max' => 999999],
            [['contact_way', 'remark', 'remark_name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级id',
            'member_level' => '等级',
            'is_blacklist' => '是否黑名单',
            'contact_way' => '联系方式',
            'remark' => '备注',
            'money' => '佣金',
            'remark_name' => '备注名',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        /* @var User $form */
        $form = User::find()->alias('u')
            ->with(['share' => function ($query) {
                $query->where(['is_delete' => 0]);
            }])
            ->with('identity')
            ->with('userInfo')
            ->where(['u.id' => $this->id])
            ->one();

        if (!$form) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据为空'
            ];
        }
        $memberChange = $form->identity->member_level != $this->member_level;
        $beforeParentId = $form->userInfo->parent_id;
        $form->userInfo->is_blacklist = $this->is_blacklist;
        $form->userInfo->parent_id = $this->parent_id;
        $form->userInfo->junior_at = mysql_timestamp();
        $form->userInfo->remark = $this->remark;
        $form->userInfo->contact_way = $this->contact_way;
        $form->userInfo->remark_name = $this->remark_name;
        $form->identity->member_level = $this->member_level;


        $t = \Yii::$app->db->beginTransaction();
        if ($form->share && $form->identity->is_distributor == 1) {
            $diff = $this->money - $form->share->money;
            if (floor($form->share->total_money) + floor($diff) > 999999) {
                throw new \Exception('累计佣金金额不能大于999999');
            }
            \Yii::$app->currency->setUser($form)->brokerage->add($diff, '后台修改分销佣金为：' . $this->money);
        }

        try {
            if (!$form->identity->save()) {
                throw new \Exception($this->getErrorMsg($form->identity));
            }

            if (!$form->userInfo->save()) {
                throw new \Exception($this->getErrorMsg($form->userInfo));
            }

            $t->commit();
            \Yii::$app->trigger(HandlerRegister::CHANGE_SHARE_MEMBER, new ShareMemberEvent([
                'mall' => \Yii::$app->mall,
                'beforeParentId' => $beforeParentId,
                'parentId' => $this->parent_id,
                'userId' => $form->id
            ]));
            if ($memberChange && $this->member_level == 0) {
                try {
                    $this->sendTemplate($form, '会员等级解除:你的会员等级身份已被解除');
                    $this->sendSmsToUser($form, '会员等级');
                } catch (\Exception $exception) {
                    \Yii::error("发送解除会员等级模板消息失败");
                    \Yii::error($exception);
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function updateUserLevel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var User $user */
            $user = User::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ])->with('identity')->one();

            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $mallMember = MallMembers::find()->where(['level' => $this->member_level, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->one();
            if (!$mallMember) {
                $userCenterConfig = UserCenterConfig::getInstance()->getApiUserCenter();
                $mallMember['name'] = $userCenterConfig['general_user_text'];
            }

            $user->identity->member_level = $this->member_level;
            $res = $user->identity->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($user->identity));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateUserRemark()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var User $user */
            $user = User::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ])->with('userInfo')->one();

            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $user->userInfo->remark = $this->remark;
            $res = $user->userInfo->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user->userInfo));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateUserRemarkName()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            /** @var User $user */
            $user = User::find()->where([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ])->with('userInfo')->one();

            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $user->userInfo->remark_name = $this->remark_name;
            $res = $user->userInfo->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user->userInfo));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function sendTemplate($user, $remark)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(RemoveIdentityInfo::TPL_NAME)->send([
                'remark' => $remark,
                'time' => date('Y-m-d H:i:s', time()),
                'user' => $user,
                'page' => 'pages/user-center/user-center'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param User $user
     * @param $remark
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($user, $remark)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$remark]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = RemoveIdentityInfo::TPL_NAME;
            $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
