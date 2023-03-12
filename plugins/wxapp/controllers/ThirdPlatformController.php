<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/9/24
 * Time: 18:00
 */

namespace app\plugins\wxapp\controllers;

use app\core\response\ApiCode;
use app\forms\open3rd\ExtAppForm;
use app\forms\open3rd\Open3rd;
use app\forms\open3rd\Open3rdException;
use app\helpers\ArrayHelper;
use app\models\Model;
use app\models\WxappPlatform;
use app\plugins\wxapp\forms\FastCreateForm;
use app\plugins\wxapp\forms\FastCreateListForm;
use app\plugins\wxapp\models\WxappWxminiprograms;
use app\plugins\wxapp\models\WxappWxminiprogramAudit;
use app\plugins\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ThirdPlatformController extends Controller
{
    public function actionAuthorizer()
    {
        try {
            $platform = WxappPlatform::getPlatform();
            if (!$platform || empty($platform->component_access_token)) {
                throw new \Exception('未配置微信开放平台或者未收到推送ticket,请等待10分钟后再试');
            }
            $open3rd = new Open3rd([
                'appId' => $platform->appid,
                'appSecret' => $platform->appsecret,
                'componentAccessToken' => $platform->component_access_token,
                'auth_type' => 2
            ]);
            $res = $open3rd->getAuthorizerInfo();
            \Yii::error($res);
            $t = \Yii::$app->db->beginTransaction();
            $third = WxappWxminiprograms::findOne(['authorizer_appid' => $res['authorization_info']['authorizer_appid'], 'is_delete' => 0]);
            if (!$third) {
                $third = new WxappWxminiprograms();
            } elseif ($third->mall_id != \Yii::$app->mall->id) {
                throw new \Exception('该账号已经绑定过其他商城');
            }
            $third->mall_id = \Yii::$app->mall->id;
            $third->authorizer_appid = $res['authorization_info']['authorizer_appid'];
            $third->authorizer_access_token = $res['authorization_info']['authorizer_access_token'];
            $third->authorizer_refresh_token = $res['authorization_info']['authorizer_refresh_token'];
            $third->authorizer_expires = time() + 7000;
            $third->func_info = json_encode($res['authorization_info']['func_info']);
            $third->domain = '';
            $third->is_delete = 0;
            if (!$third->save()) {
                throw new \Exception((new Model())->getErrorMsg($third));
            }
            $ext = ExtAppForm::instance($third);
            $serverDomain = [
                'action' => 'set',
                'requestdomain' => [
                    'https://' . \Yii::$app->request->hostName
                ],
                'wsrequestdomain' => [
                    'wss://' . \Yii::$app->request->hostName
                ],
                'uploaddomain' => [
                    'https://' . \Yii::$app->request->hostName
                ],
                'downloaddomain' => [
                    'https://' . \Yii::$app->request->hostName
                ],
            ];
            $ext->setServerDomain(json_encode($serverDomain));
            $info = $ext->getAuthorizerInfo();
            if (!$info) {
                throw new \Exception('获取授权方的帐号基本信息');
            }
            if ($info) {
                $third->nick_name = $info['authorizer_info']['nick_name'];
                $third->head_img = $info['authorizer_info']['head_img'];
                $third->verify_type_info = $info['authorizer_info']['verify_type_info']['id'];
                $third->user_name = $info['authorizer_info']['user_name'];
                $third->qrcode_url = $info['authorizer_info']['qrcode_url'];
                $third->principal_name = $info['authorizer_info']['principal_name'];
                $third->principal_name = $info['authorizer_info']['principal_name'];
                $third->signature = $info['authorizer_info']['signature'];
            }
            if (!$third->save()) {
                throw new \Exception((new Model())->getErrorMsg($third));
            }
            $t->commit();
            return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['plugin/wxapp/wx-app-config/setting']));
        } catch (\Exception $exception) {
            if (isset($t)) {
                $t->rollBack();
            }
            \Yii::error($exception);
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    public function actionTemplateList()
    {
        try {
            $ext = ExtAppForm::instance();
            $list = $ext->templateList();
            if (!empty($list->template_list)) {
                $temp = array_column($list->template_list, 'create_time');
                array_multisort($temp, SORT_ASC, $list->template_list);
                foreach ($list->template_list as $item) {
                    if (isset($item->create_time)) {
                        $item->create_at = date("Y-m-d H:i:s", $item->create_time);
                    }
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 上传代码
     * @return array
     */
    public function actionUpload()
    {
        try {
            $template_id = \Yii::$app->request->post('template_id');
            $is_plugin = \Yii::$app->request->post('is_plugin', 0);
            $ext = ExtAppForm::instance();
            $res = $ext->uploadCode($template_id, $is_plugin, app_version(), \Yii::$app->mall->name);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取体验版二维码
     * @return array
     */
    public function actionPreview()
    {
        try {
            $ext = ExtAppForm::instance();
            $preview = $ext->getExpVersion();
            $response = \Yii::$app->getResponse();
            $response->headers->set('Content-Type', 'image/jpeg');
            $response->format = Response::FORMAT_RAW;
            $response->data = $preview;
            $response->send();
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 提交审核
     * @return array
     */
    public function actionSubmitReview()
    {
        try {
            $extApp = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            $ext = ExtAppForm::instance($extApp);
            $submit = $ext->submitReview();
            if (!$submit) {
                throw new \Exception('提交审核失败');
            }
            $audit = new WxappWxminiprogramAudit();
            $audit->appid = $extApp->authorizer_appid;
            $audit->template_id = \Yii::$app->request->post('template_id');
            $audit->version = \Yii::$app->request->post('version');
            $audit->auditid = (string)$submit;
            $audit->status = 2;
            if (!$audit->save()) {
                throw new \Exception((new Model())->getErrorMsg($audit));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '提交审核成功',
                'data' => $submit
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取最后一次审核记录
     * @return array
     */
    public function actionGetLastAudit()
    {
        try {
            $extApp = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            $ext = ExtAppForm::instance($extApp);
            $last = $ext->getLastAudit();
            /**@var WxappWxminiprogramAudit $audit**/
            $audit = WxappWxminiprogramAudit::find()
                ->where(['appid' => $extApp->authorizer_appid])
                ->orderBy('id desc')
                ->one();
            $lastAudit = WxappWxminiprogramAudit::find()
                ->where(['appid' => $extApp->authorizer_appid, 'status' => 4])
                ->orderBy('id desc')
                ->one();
            $lasts = ArrayHelper::toArray($last);
            $lasts['audit'] = $audit;
            $lasts['last'] = $lastAudit;
            if (isset($last->auditid)) {
                $has = WxappWxminiprogramAudit::findOne(['auditid' => $last->auditid]);
                if ($has) {
                    if ($audit) {
                        if ($last->status == 1) {
                            $audit->reason = $last->reason;
                        }
                        if ($audit->status != 4) {
                            $audit->status = $last->status;
                            $audit->save();
                        }
                    }

                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '获取数据成功',
                        'data' => $lasts
                    ];
                }
            }

            $lasts['status'] = -1;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取数据成功',
                'data' => $lasts
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 撤回当前的代码审核单
     * @return array
     */
    public function actionUnDoCodeAudit()
    {
        try {
            $extApp = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            $ext = ExtAppForm::instance($extApp);
            $undo = $ext->unDoCodeAudit();
            $audit = WxappWxminiprogramAudit::find()
                ->where(['appid' => $extApp->authorizer_appid])
                ->orderBy('id desc')
                ->one();
            if ($audit) {
                $audit->is_delete = 1;
                $audit->save();
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '撤回成功',
                'data' => $undo
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 发布最后一个审核通过的小程序代码版本
     * @return array
     */
    public function actionRelease()
    {
        try {
            $extApp = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
            $ext = ExtAppForm::instance($extApp);
            $release = $ext->release();
            $audit = WxappWxminiprogramAudit::find()
                ->where(['appid' => $extApp->authorizer_appid, 'status' => 0])
                ->orderBy('id desc')
                ->one();
            if ($audit) {
                $audit->status = 4;
                $audit->release_at = mysql_timestamp();
                $audit->save();
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发布成功',
                'data' => $release
            ];
        } catch (Open3rdException $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    /**
     * 设置业务域名
     * @return array
     */
    public function actionBusinessDomain()
    {
        if (\Yii::$app->request->isAjax) {
            try {
                $domain = \Yii::$app->request->post('domain');
                if (empty($domain) || !is_array($domain)) {
                    throw new \Exception('请填写业务域名');
                }
                $businessDomain = [
                    'action' => 'set',
                    'webviewdomain' => [
                    ]
                ];
                $businessDomain['webviewdomain'] =
                    array_merge($businessDomain['webviewdomain'], $domain);
                $extApp = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                $ext = ExtAppForm::instance($extApp);
                $res = $ext->setBusinessDomain(json_encode($businessDomain));
                $extApp->domain = implode(",", $domain);
                if (!$extApp->save()) {
                    throw new \Exception((new Model())->getErrorMsg($extApp));
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '设置成功',
                    'data' => $res
                ];
            } catch (Open3rdException $exception) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage(),
                ];
            } catch (\Exception $e) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * 快速创建小程序
     * @return array
     */
    public function actionFastCreate()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new FastCreateForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->create();
            } else {
                $form = new FastCreateForm();
                $form->attributes = \Yii::$app->request->get();
                return $form->getInfo();
            }
        } else {
            return $this->render('fast-create');
        }
    }

    /**
     * 小程序创建记录
     * @return array
     */
    public function actionFastCreateList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new FastCreateListForm();
            $form->attributes = \Yii::$app->request->get();
            return $form->search();
        }
        return $this->render('fast-create-list');
    }
}
