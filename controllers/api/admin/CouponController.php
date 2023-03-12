<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/29
 * Time: 13:48
 */

namespace app\controllers\api\admin;

use app\forms\mall\coupon\CouponForm;

class CouponController extends AdminController
{
    /**
     * 获取商品卡券列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new CouponForm();

        return $form->getOptionList();
    }
}