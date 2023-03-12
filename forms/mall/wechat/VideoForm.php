<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/24
 * Time: 4:44 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\MallSetting;
use app\models\Model;
use yii\helpers\Json;

class VideoForm extends Model
{
    public $is_video_number;
    public $is_video_number_member;
    public $video_number_member_list;
    public $video_number_template_list;
    public $video_number_share_title;
    public $video_number_user_1;
    public $video_number_user_2;

    public function rules()
    {
        return [
            [['is_video_number', 'is_video_number_member'], 'integer'],
            [['video_number_member_list', 'video_number_template_list'], 'safe'],
            [['video_number_share_title', 'video_number_user_1', 'video_number_user_2'], 'trim'],
            [['video_number_share_title', 'video_number_user_1', 'video_number_user_2'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_video_number' => '视频号设置开关',
            'is_video_number_member' => '会员等级可用权限开关',
            'video_number_member_list' => '会员等级列表',
            'video_number_template_list' => '公众号模板',
            'video_number_share_title' => '分享标题',
            'video_number_user_1' => '接受消息用户openId1',
            'video_number_user_2' => '接受消息用户openId2',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->is_video_number) {
                if (!$this->video_number_share_title) {
                    throw new \Exception('请填写视频号分享标题');
                }

                if (mb_strlen($this->video_number_share_title) > 20) {
                    throw new \Exception("视频号分享标题不能大于20个字");
                }

                if (!count($this->video_number_template_list)) {
                    throw new \Exception("请选择视频号模板");
                }

                if ($this->is_video_number_member && !count($this->video_number_member_list)) {
                    throw new \Exception('请选择视频号会员等级');
                }

                if (!$this->video_number_user_1 || !$this->video_number_user_2) {
                    throw new \Exception('请填写视频号公众号用户openId');
                }
            }
            foreach ($this->attributes as $k => $item) {
                if (in_array($k, ['video_number_member_list', 'video_number_template_list'])) {
                    $newItem = Json::encode($item, JSON_UNESCAPED_UNICODE);
                } else {
                    $newItem = $item;
                }

                $mallSetting = MallSetting::findOne(['key' => $k, 'mall_id' => \Yii::$app->mall->id]);
                if ($mallSetting) {
                    $mallSetting->value = (string)$newItem;
                    $res = $mallSetting->save();
                } else {
                    $mallSetting = new MallSetting();
                    $mallSetting->key = $k;
                    $mallSetting->value = (string)$newItem;
                    $mallSetting->mall_id = \Yii::$app->mall->id;
                    $res = $mallSetting->save();
                }

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mallSetting));
                }
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功。',
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => $exception
            ];
        }
    }

    public function getDetail()
    {
        $mall = new Mall();
        $setting = $mall->getMallSetting(array_keys($this->attributeLabels()));
        return [
            'code' => 0,
            'data' => [
                'detail' => $setting
            ]
        ];
    }
}
