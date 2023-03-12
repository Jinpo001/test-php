<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/10
 * Time: 15:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\admin\export;


use app\jobs\BaseJob;
use app\models\Mall;
use app\models\Share;
use app\models\ShareSetting;
use app\models\User;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property User $user
 * @property Mall $mall
 */
class AdminExportJob extends BaseJob implements JobInterface
{
	public $export_class;
    public $user;
	public $params = [];

    public $model_class;
    public $model_params = [];
    public $function_name;
    public $function_params = [];
    public $get_data;
    public $post_data;

    public function execute($queue)
    {
        \Yii::warning('导出队列开始');
        $this->setRequest();

        try {
        	if (!class_exists($this->export_class)) {
	            throw new \Exception('未找到导出类');
	        }

            \Yii::$app->user->setIdentity($this->user);

        	$class = new $this->export_class();

            // 当query数据过大 无法直接传入 可使用以下方法
            if ($this->model_class) {
                if (!class_exists($this->model_class)) {
                    throw new \Exception('未找到model类');
                }

                $model = new $this->model_class();
                $model->attributes = $this->get_data;
                $model->attributes = $this->post_data;

                // 额外参数
                foreach ($this->model_params as $key => $value) {
                    $model->$key = $value;
                }

                $query = call_user_func([$model, $this->function_name], $this->function_params);
                $class->query = $query;
            }

        	foreach ($this->params as $key => $value) {
        		$class->$key = $value;
        	}

        	$class->export();
        }catch(\Exception $exception) {
        	\Yii::error('导出队列异常');
        	\Yii::error($exception);
        }
    }
}
