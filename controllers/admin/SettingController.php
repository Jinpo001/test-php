<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin;


use app\controllers\behaviors\SuperAdminFilter;
use app\core\response\ApiCode;
use app\forms\admin\MessageRemindSettingEditForm;
use app\forms\admin\MessageRemindSettingForm;
use app\forms\admin\PaySettingEditForm;
use app\forms\admin\PaySettingForm;
use app\forms\admin\mall\FileForm;
use app\forms\admin\mall\MallOverrunForm;
use app\forms\admin\platform\PlatformSettingEditForm;
use app\forms\admin\platform\PlatformSettingForm;
use app\forms\common\CommonOption;
use app\forms\common\UploadForm;
use app\forms\common\attachment\CommonAttachment;
use app\forms\open3rd\ExtAppForm;
use app\forms\open3rd\Open3rdException;
use app\helpers\ArrayHelper;
use app\jobs\RunQueueShJob;
use app\jobs\TestQueueServiceJob;
use app\models\AccountUserGroup;
use app\models\AttachmentStorage;
use app\models\Mall;
use app\models\Option;
use app\models\WxappPlatform;
use app\plugins\wxapp\forms\AppUploadForm;
use app\plugins\wxapp\models\WxappWxminiprograms;
use yii\web\UploadedFile;

class SettingController extends AdminController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'superAdminFilter' => [
                'class' => SuperAdminFilter::class,
                'safeRoutes' => [
                    'admin/setting/small-routine',
                    'admin/setting/upload-file',
                    'admin/setting/attachment',
                    'admin/setting/attachment-create-storage',
                    'admin/setting/attachment-enable-storage',
                ]
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $setting = \Yii::$app->request->post('setting');
                $setting = json_decode($setting, true);
                if (CommonOption::set(Option::NAME_IND_SETTING, $setting)) {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'msg' => '保存成功。',
                    ];
                } else {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '保存失败。',
                    ];
                }
            } else {
                $setting = CommonOption::get(Option::NAME_IND_SETTING);
                if (isset($setting['user_group_id'])) {
                    $group = AccountUserGroup::findOne(['id' => $setting['user_group_id'], 'is_delete' => 0]);
                    $setting['user_group_id'] = $group ? $group->id : null;
                    $setting['user_group_name'] = $group ? $group->name : '';
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'setting' => $setting,
                    ],
                ];
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionAttachment()
    {
        if (\Yii::$app->request->isAjax) {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $list = $common->getAttachmentList();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'storageTypes' => $common->getStorageType()
                ]
            ]);
        } else {
            return $this->render('attachment');
        }
    }

    public function actionAttachmentCreateStorage()
    {
        try {
            $user = \Yii::$app->user->identity;
            $common = CommonAttachment::getCommon($user);
            $data = \Yii::$app->request->post();
            $res = $common->attachmentCreateStorage($data);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function actionAttachmentEnableStorage($id)
    {
        $common = CommonAttachment::getCommon(\Yii::$app->user->identity);
        $common->attachmentEnableStorage($id);
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功。',
        ]);
    }

    public function actionOverrun()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->post()) {
                $form = new MallOverrunForm();
                $form->form = \Yii::$app->request->post('form');

                return $this->asJson($form->save());
            } else {
                $form = new MallOverrunForm();
                return $this->asJson($form->setting());
            }
        } else {
            return $this->render('overrun');
        }
    }

    public function actionQueueService($action = null, $id = null, $time = null)
    {
        if (\Yii::$app->request->isAjax) {
            if ($action == 'create') {
                try {
                    $time = time();
                    $job = new TestQueueServiceJob();
                    $job->time = $time;
                    $id = \Yii::$app->queue->delay(0)->push($job);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                            'time' => $time,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test') {
                $done = \Yii::$app->queue->isDone($id);
                if ($done) {
                    $job = new TestQueueServiceJob();
                    $job->time = intval($time);
                    if (!$job->valid()) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '队列服务测试失败：任务似乎已经运行，但没有得到预期结果，请检查redis是否连接正常并且数据正常。',
                        ];
                    } else {
                        return [
                            'code' => ApiCode::CODE_SUCCESS,
                            'data' => [
                                'done' => true,
                            ],
                        ];
                    }
                } else {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'done' => false,
                        ],
                    ];
                }
            }
            if ($action == 'env') {
                $fs = [
                    'proc_open', 'proc_get_status', 'proc_close', 'proc_terminate', 'proc_nice',
                    'pcntl_fork', 'pcntl_waitpid', 'pcntl_wait', 'pcntl_signal', 'pcntl_signal_dispatch',
                    'pcntl_wifexited', 'pcntl_wifstopped', 'pcntl_wifsignaled', 'pcntl_wexitstatus',
                    'pcntl_wifcontinued', 'pcntl_wtermsig', 'pcntl_wstopsig', 'pcntl_exec', 'pcntl_alarm',
                    'pcntl_get_last_error', 'pcntl_errno', 'pcntl_strerror', 'pcntl_getpriority', 'pcntl_setpriority',
                    'pcntl_sigprocmask', 'pcntl_async_signals', 'pcntl_signal_get_handler',
                    // 'pcntl_sigwaitinfo', 'pcntl_sigtimedwait',
                ];
                $notExistsFs = [];
                foreach ($fs as $f) {
                    if (!function_exists($f)) $notExistsFs[] = $f;
                }
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'not_exists_fs' => $notExistsFs,
                    ],
                ];
            }
            if ($action == 'create-queue') {
                try {
                    $time = time();
                    $job = new RunQueueShJob();
                    $job->time = $time;
                    $id = \Yii::$app->queue->delay(0)->push($job);
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'id' => $id,
                            'time' => $time,
                        ],
                    ];
                } catch (\Exception $exception) {
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => '队列服务测试失败：' . $exception->getMessage(),
                    ];
                }
            }
            if ($action == 'test3') {
                $done = \Yii::$app->queue->isDone($id);
                if ($done) {
                    $job = new RunQueueShJob();
                    $job->time = intval($time);
                    if (!$job->valid()) {
                        return [
                            'code' => ApiCode::CODE_ERROR,
                            'msg' => '队列服务测试失败：任务似乎已经运行，但没有得到预期结果，请检查redis是否连接正常并且数据正常。',
                        ];
                    } else {
                        return [
                            'code' => ApiCode::CODE_SUCCESS,
                            'data' => [
                                'done' => true,
                            ],
                        ];
                    }
                } else {
                    return [
                        'code' => ApiCode::CODE_SUCCESS,
                        'data' => [
                            'done' => false,
                        ],
                    ];
                }
            }
        } else {
            return $this->render('queue-service');
        }
    }

    public function actionSmallRoutine()
    {
        return $this->render('small-routine');
    }

    // 上传业务域名文件
    public function actionUploadFile($name = 'file')
    {
        $form = new FileForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }

    public function actionUploadLogo($name = 'file')
    {
        $form = new UploadForm();
        $form->file = UploadedFile::getInstanceByName($name);
        return $this->asJson($form->save());
    }

    public function actionMessageRemind()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new MessageRemindSettingEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $form->save();
            } else {
                $form = new MessageRemindSettingForm();
                return $form->search();
            }
        } else {
            return $this->render('message-remind');
        }
    }

    public function actionMessageRemindReset()
    {
        $form = new MessageRemindSettingForm();
        return $form->reset();
    }

    /**
     * 微信开放平台
     * @return array|string
     * @throws \Exception
     */
    public function actionWxapp()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PlatformSettingEditForm();
                $form->attributes = \Yii::$app->request->post('platform');
                return $form->save();
            } else {
                $form = new PlatformSettingForm();
                return $form->search();
            }
        } else {
            return $this->render('wxapp');
        }
    }

    /**
     * 上传代码包至开放平台并自动设置到小程序模板库
     * @return \yii\web\Response
     */
    public function actionUpload()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AppUploadForm();
            $form->attributes = \Yii::$app->request->get();
            $form->is_platform = 1;
            $res = $form->getResponse();
            try {
                $platform = WxappPlatform::getPlatform();
                if (!$platform || empty($platform->component_access_token)) {
                    throw new \Exception('未配置微信开放平台或者未收到推送ticket,请等待10分钟后再试');
                }
                if ($res['code'] == 0 && $form->action == 'upload') {
                    $ext = ExtAppForm::instance(null, 1);
                    $list = $ext->templatedraftlist();
                    $list = ArrayHelper::toArray($list);
                    $arr = $list['draft_list'];
                    if (!isset($arr) || empty($arr)) {
                        throw new \Exception('获取草稿列表失败');
                    }
                    $temp = array_column($arr, 'create_time');
                    array_multisort($temp, SORT_DESC, $arr);
                    $result = $ext->addtotemplate($arr[0]['draft_id']);
                    if (!$result) {
                        throw new \Exception('将草稿添加到代码模板库失败');
                    }
                }
            } catch (Open3rdException $open3rdException) {
                return $this->asJson([
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $open3rdException->getMessage(),
                ]);
            } catch (\Exception $exception) {
                return $this->asJson([
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $exception->getMessage()
                ]);
            }
            return $this->asJson($res);
        }
    }

    /**
     * 获取代码模板列表
     * @return array
     */
    public function actionTemplateList()
    {
        try {
            $ext = ExtAppForm::instance(null, 1);
            $list = $ext->templateList();
            $list = ArrayHelper::toArray($list);
            $arr = $list['template_list'];
            if (isset($arr) && !empty($arr)) {
                $temp = array_column($arr, 'create_time');
                array_multisort($temp, SORT_DESC, $list['template_list']);
                foreach ($list['template_list'] as &$item) {
                    if (isset($item['create_time'])) {
                        $item['create_at'] = date("Y-m-d H:i:s", $item['create_time']);
                    }
                }
                unset($item);
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
     * 删除模板
     * @return array
     */
    public function actionDelTemplate()
    {
        try {
            $templateId = \Yii::$app->request->post('template_id');
            if (empty($templateId)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '请选择模板',
                ];
            }
            $ext = ExtAppForm::instance(null, 1);
            $res = $ext->deletetemplate($templateId);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
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
     * 查询quota
     * @return array
     */
    public function actionQuota()
    {
        try {
            $mallId = \Yii::$app->request->get('mall_id');
            if (!$mallId) {
                throw new \Exception('请填写已授权三方的小程序商城id');
            }
            $mall = Mall::findOne($mallId);
            if (!$mall) {
                throw new \Exception('商城不存在');
            }
            \Yii::$app->setMall($mall);
            $extApp = WxappWxminiprograms::findOne(['mall_id' => $mallId, 'is_delete' => 0]);
            if (!$extApp) {
                throw new \Exception('该小程序不存在或未授权');
            }
            $ext = ExtAppForm::instance($extApp);
            $quota = $ext->quota();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
                'data' => [
                    'rest' => $quota->rest,
                    'limit' => $quota->limit
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

    public function actionPaySetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PaySettingEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $form->save();
            } else {
                $form = new PaySettingForm();
                return $form->getSetting();
            }
        } else {
            return $this->render('pay-setting');
        }
    }
}
