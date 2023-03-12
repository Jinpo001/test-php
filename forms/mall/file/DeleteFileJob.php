<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/10
 * Time: 15:39
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\file;


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
class DeleteFileJob extends BaseJob implements JobInterface
{
    public $file_path;

    public function execute($queue)
    {
        \Yii::warning('删除文件开始');
        $this->setRequest();

        try {
        	if (is_dir($this->file_path)) {
                $this->deldir($this->file_path);
            } else {
                unlink($this->file_path);
            }
        }catch(\Exception $exception) {
        	\Yii::error('删除文件异常');
        	\Yii::error($exception);
        }
    }

    private function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            //如果 $p 中有两个以上的元素则说明当前 $path 不为空
            if(count($p)>2){
                foreach($p as $val){
                    //排除目录中的.和..
                    if($val !="." && $val !=".."){
                        //如果是目录则递归子目录，继续操作
                        if(is_dir($path.$val)){
                            //子目录中操作删除文件夹和文件
                            $this->deldir($path.$val.'/');
                        }else{
                            //如果是文件直接删除
                            unlink($path.$val);
                        }
                    }
                }
            }
        }
        //删除目录
        return rmdir($path);
    }
}
