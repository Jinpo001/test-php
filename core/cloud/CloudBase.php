<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:21:00
 */


namespace app\core\cloud;


use app\forms\common\CommonOption;
use app\models\AdminInfo;
use app\models\CorePlugin;
use app\models\User;
use GuzzleHttp\Client;
use \Exception;
use yii\base\Component;

class CloudBase extends Component
{
    public $classVersion = '4.2.10';
    public $urlEncodeQueryString = true;
    // todo 开发完成此处请切换
//    private $xBaseUrl = 'aHR0cHM6Ly9iZGF1dGguempoZWppYW5nLmNvbQ=='; // 正式
    private $xBaseUrl = 'aHR0cDovL2xvY2FsaG9zdC9iZGF1dGgvd2Vi'; // 开发
    private $xLocalAuthInfo;
    //优化多次DB查询使用
    private $cps;
    private $accountCount;

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     * @throws Exception
     */
    public function httpGet($url, $params = [])
    {
        //插件数据
        if($url=='/mall/plugin/plugin-data'){
            $data='{"code":0,"data":{"plugins":[{"id":1,"name":"wxapp","display_name":"微信小程序","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/197c46f9c936ae0e6039f93e91553d4c.png","desc":"在微信小程序中经营你的店铺","content":"<p class=\"ql-align-center\"><br></p>","sort":1,"price":"0.00","route":"plugin/wxapp","is_open":1,"version":"4.1.0","is_delete":0,"add_time":"2019-06-05 14:49:19","update_time":"2020-06-15 18:10:56","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/d52a90d4eb06059d9fdb0641592400b3.png"},{"id":2,"name":"bargain","display_name":"砍价","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/44388dfc16126bd6802bb060c14273d3.png","desc":"邀请好友砍价后低价购买","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/3b17deed6898ed4018fc5d47a55c046a.png\"></p>","sort":1000,"price":"1000.00","route":"plugin/bargain","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 14:54:13","update_time":"2020-06-15 18:00:03","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/10da8fa3ab90d190fb6c8dcb1fa0ac24.png"},{"id":3,"name":"pond","display_name":"九宫格抽奖","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/63fac340167f2bc0bb135d55322f8820.png","desc":"抽积分、优惠券、实物等","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/58faf00b23dab32aba6bd8981780aa15.png\"></p>","sort":1000,"price":"300.00","route":"plugin/pond","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 14:54:54","update_time":"2020-06-15 18:06:19","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4b9e58ea9aa6f60e08b5aaef20426ac9.png"},{"id":4,"name":"mch","display_name":"多商户","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c42d7a4c2cfbb9e29f657fc2301fd990.png","desc":"获取入驻商流量，自营+商户入驻","content":"<h3 class=\"ql-align-center\"><strong class=\"ql-font-serif\">商户入驻插件满足：B2B2C模式</strong></h3><h3><strong class=\"ql-font-serif\" style=\"color: rgb(230, 0, 0);\">强调：多商户插件暂不支持其他插件功能（如：拼团、秒杀、砍价等...）</strong></h3><h3><strong class=\"ql-font-serif\">除了实现基础的商城购买流程外，入驻商户还支持采集插件功能；支持自定义分销功能；支持门店自提到店核销功能；支持客服外链功能；支持区域限购功能；支持短信/邮件通知；支持电子面单/发货单/小票打印功能等...</strong></h3><p><br></p><p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/d2ad2de81dc8485bba971229db7e9be4.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/1886b40f9078ad7a2724af598a406dac.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e90688e92f1aa1ab839946aab2d83cc0.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5ce30dc0a7ac08a98e5008dc02e0006b.png\"></p>","sort":1000,"price":"2000.00","route":"plugin/mch","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 14:56:23","update_time":"2020-07-20 17:55:14","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e518720e0d614439e51b49735fe1f842.png"},{"id":5,"name":"fxhb","display_name":"裂变拆“红包”","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e451a34a3e5a31839ca2eeea5d1784cb.png","desc":"裂变式邀请好友拆“红包”","content":"<p class=\"ql-align-center\">拆“红包”的红包是优惠券的意思，不是真实的红包！特别提示：此功能有可能会被腾讯认为恶意营销，请谨慎使用！</p><p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8dc88b30c77b4f38bca80ef57198b432.png\"></p>","sort":1000,"price":"199.00","route":"plugin/fxhb","is_open":1,"version":"4.0.10","is_delete":0,"add_time":"2019-06-05 14:56:59","update_time":"2020-08-08 14:48:24","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9a7e9bb462ece248a6da22d7a2a39b84.png"},{"id":6,"name":"booking","display_name":"预约","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/1d0dfb1146ae84c6eaaad6a12bea75a0.png","desc":"提前线下消费或服务","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ea084cc95c5b72cba7241d4a561a4935.png\"></p>","sort":1000,"price":"500.00","route":"plugin/book","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 14:58:11","update_time":"2020-09-09 15:11:14","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/07c662e46ade264338df7544c5f5057f.png"},{"id":7,"name":"pintuan","display_name":"拼团","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c9d228e5b443da3ed17cf4ca756c203f.png","desc":"引导客户邀请朋友一起拼团购买","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8b9353009a7160f6330cc19ff7adac86.png\"></p>","sort":1000,"price":"1320.00","route":"plugin/pintuan","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 14:58:46","update_time":"2020-06-15 18:06:06","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/1ad43702df97bdea25452f00b3b49f5e.png"},{"id":8,"name":"miaosha","display_name":"整点秒杀","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a2e30d15b485c80bae1bcb004d122c35.png","desc":"引导客户快速抢购","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0df4b70563ee384f6c83ab879b29abc1.png\"></p>","sort":1000,"price":"999.00","route":"plugin/miaosha","is_open":1,"version":"4.0.17","is_delete":0,"add_time":"2019-06-05 14:59:16","update_time":"2020-06-15 18:04:08","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/7ec0386824388b8ce18040857058827d.png"},{"id":9,"name":"scratch","display_name":"刮刮卡","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0ccf61113434e0321f7f12e3befb77f1.png","desc":"刮开卡片参与抽奖","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c8497d9ad3579ee3cec48bd3b6684d09.png\"></p>","sort":1000,"price":"290.00","route":"plugin/scratch","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 15:01:14","update_time":"2020-06-15 18:08:49","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/28fd30646970fe742350d47c3494aee4.png"},{"id":10,"name":"shopping","display_name":"好物圈","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/92c21f4eaf4f60a73cb970700bfe5464.png","desc":"向微信好友推荐好商品","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/d478f393edce46c0005cf687208a117f.png\"></p>","sort":1000,"price":"100.00","route":"plugin/gwd","is_open":1,"version":"4.0.10","is_delete":0,"add_time":"2019-06-05 15:01:48","update_time":"2020-12-10 15:19:53","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/994df3a6c8dc456d4a3a3614b4deaec4.png"},{"id":11,"name":"diy","display_name":"DIY装修","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/80594fae95b9653cd74e9a6d8c868fc4.png","desc":"DIY店铺风格和元素，千人千面","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/13f1f9997eced33d0640bc77c51bea22.png\"></p>","sort":1000,"price":"1400.00","route":"plugin/diy","is_open":1,"version":"4.0.11","is_delete":0,"add_time":"2019-06-05 15:02:32","update_time":"2020-08-08 14:47:54","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4903220b146520fd91533ed140d2542f.png"},{"id":12,"name":"step","display_name":"步数宝","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9463fa5143ead9e14056b72384f1ac2f.png","desc":"步数兑换商品","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a829b1e56e49d22d6e96f887bdc2bbf5.jpg\"></p>","sort":1000,"price":"800.00","route":"plugin/step","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 15:03:03","update_time":"2020-06-15 18:09:33","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/7e2e64e891d444d5c31824fee9f8fb88.png"},{"id":13,"name":"lottery","display_name":"幸运抽奖","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/33f626df7fa5afe2ff9e4a6c871aba1a.png","desc":"裂变玩法，抽取幸运客户赠送奖品","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/78f39c98dccbe8299fc765362c11c975.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ffae29a43424718969f6442acb30421c.jpg\"></p>","sort":1000,"price":"350.00","route":"plugin/lottery","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 15:05:00","update_time":"2020-06-15 18:03:39","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0bde7f03396f64b1a6a602ffdc384fe8.png"},{"id":14,"name":"integral_mall","display_name":"积分商城","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/45cb70e3266f59a6c83807284d722b0a.png","desc":"使用积分或积分+现金兑换商品","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e3af8f54c533e72bacb935cc4e43bb3a.png\"></p>","sort":1000,"price":"1200.00","route":"plugin/integralmall","is_open":1,"version":"4.0.17","is_delete":0,"add_time":"2019-06-05 15:24:06","update_time":"2020-06-15 18:03:25","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4677340a313d1d6a417492dd3b615540.png"},{"id":15,"name":"check_in","display_name":"签到插件","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/607ca79f65b8724c4f114c32fe375432.png","desc":"促进店铺访问量和用户活跃度","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5e26b9e51824605f7fd27d7655db9c5e.png\"></p>","sort":1000,"price":"0.00","route":"plugin/check_in","is_open":1,"version":"4.0.36","is_delete":0,"add_time":"2019-06-05 15:29:22","update_time":"2020-06-15 18:00:58","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9f8feafc5d1619c3d897e56ba2fd4147.png"},{"id":17,"name":"app_admin","display_name":"手机端管理","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ff93a31bd66415f86ba1dd08d08a6d02.png","desc":"手机端操作管理店铺","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/06e3f309fa952624a91498b283d9aed4.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/dfe57ddea2f7a293b40af54b321cd792.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/893c3b81d86a3c508086e3136f2eb611.jpg\"></p>","sort":1000,"price":"500.00","route":"","is_open":1,"version":"4.0.10","is_delete":0,"add_time":"2019-06-28 21:05:18","update_time":"2020-06-15 17:59:38","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e3e79ca9b97dba95ae79b9ac1bed16f9.png"},{"id":18,"name":"dianqilai","display_name":"客服系统","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e8702f07d724591f4ec54378b51c2e44.png","desc":"促进商家和买家之间的高效交流","content":"<p class=\"ql-align-center\"><br></p>","sort":1000,"price":"3000.00","route":"","is_open":1,"version":"4.0.13","is_delete":0,"add_time":"2019-07-05 20:10:15","update_time":"2020-06-15 18:02:01","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9a1ae4508528799d9ca5543a857b3237.png"},{"id":19,"name":"bonus","display_name":"团队分红","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/aed19d0cdf590e639e854866e0230ef8.png","desc":"队长获得队员订单分红","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/915afc5be2a3c6b21bbf434e4d93798f.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/d2d48e921504823605a69a2e4789cac8.jpg\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.0.20","is_delete":0,"add_time":"2019-07-25 15:07:41","update_time":"2020-06-15 18:00:32","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/6831954dc342f2ba066621409fe1e60d.png"},{"id":20,"name":"clerk","display_name":"手机端核销员","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/47988026dae98632067d78c7f9037c34.png","desc":"手机端扫码核销，查询订单","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/7cecb50ed81f44385a4b1ab179c57991.png\"></p>","sort":1000,"price":"0.00","route":"","is_open":1,"version":"4.0.25","is_delete":0,"add_time":"2019-08-05 17:47:42","update_time":"2020-07-20 17:02:29","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/2743ec86b035db21eaa5e3019e33c714.png"},{"id":21,"name":"scan_code_pay","display_name":"当面付","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5ef35e4fd1ab854e0e379d788d9b9c5a.png","desc":"线下场景扫码当面支付","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/f8907f025e34a1aac841517625f8ebee.png\"></p>","sort":1000,"price":"500.00","route":"","is_open":1,"version":"4.0.30","is_delete":0,"add_time":"2019-08-14 17:34:05","update_time":"2020-08-08 14:56:41","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0680c8f2ef2632d373750bd8addcfe8b.png"},{"id":22,"name":"aliapp","display_name":"支付宝小程序","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c47ae5a4c103bdf4340861da18f5700b.png","desc":"在支付宝小程序中经营你的店铺","content":"<p class=\"ql-align-center\"><br></p>","sort":1000,"price":"3000.00","route":"","is_open":1,"version":"4.1.0","is_delete":0,"add_time":"2019-09-24 19:08:32","update_time":"2020-06-15 17:59:22","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e1f631677b48470eeb0c50f811165472.png"},{"id":23,"name":"bdapp","display_name":"百度小程序","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/71fbba5456cfb11841124348ac27b296.jpg","desc":"在百度小程序中经营你的店铺","content":"<p class=\"ql-align-center\"><br></p>","sort":1000,"price":"2000.00","route":"","is_open":1,"version":"4.1.0","is_delete":0,"add_time":"2019-09-24 19:14:07","update_time":"2020-06-15 18:00:17","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c606e51d6c842f68b768c6780f6dbc87.png"},{"id":24,"name":"ttapp","display_name":"抖音/头条小程序","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/6f45d3fa1af3666b483c2754bc62dd66.jpg","desc":"在抖音/头条小程序中经营你的店铺","content":"<p class=\"ql-align-center\"><br></p>","sort":1000,"price":"3000.00","route":"","is_open":1,"version":"4.1.0","is_delete":0,"add_time":"2019-09-24 19:15:26","update_time":"2020-06-15 18:10:01","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0192ac346351c5fbdc2ea1c953d097f2.png"},{"id":25,"name":"advance","display_name":"商品预售","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c5c6c915312ea1fc87f2be7290f4a095.png","desc":"提前交付定金，尾款享受优惠","content":"<h3 class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/47071f88269c19fe960d85acb8d94924.png\"></h3>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.1.8","is_delete":0,"add_time":"2019-10-25 14:30:56","update_time":"2020-06-15 17:58:12","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/888a3c5dbc7881c74e2ae4299c4e6e2c.png"},{"id":26,"name":"vip_card","display_name":"超级会员卡","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/95e97b374f00ebfff39c492ee0d68537.png","desc":"享受超级会员折扣和福利","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/c2238d0284ec6c9ac28a5d34a5e65138.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/6b88a5298a3bdc548b1b7bee3fe5258d.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/734781c38b1eb6e687f5ab3331aca35f.jpg\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/6f3dac56c18e19982a8490995a3e0777.jpg\"></p>","sort":1000,"price":"500.00","route":"","is_open":1,"version":"4.1.11","is_delete":0,"add_time":"2019-11-04 15:02:11","update_time":"2020-06-15 18:10:16","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9c719748ee725092f09fdc5ee18538f2.png"},{"id":27,"name":"quick_share","display_name":"一键发圈","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/2ab4fc0b947169b3adee6de14cc91052.png","desc":"一键保存文案和图片，高效发朋友圈","content":"<p class=\"ql-align-center\"><br></p>","sort":1000,"price":"0.00","route":"","is_open":1,"version":"4.2.5","is_delete":0,"add_time":"2019-11-26 15:38:36","update_time":"2020-06-15 18:06:39","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/7e2836a9861b31de1eb548f067bb2fde.png"},{"id":28,"name":"gift","display_name":"社交送礼","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/b1cdb3b801d828fd9d5e85428aa8af5f.png","desc":"购买礼品送给朋友","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0231773b2d644b9dc27264f3701fed13.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9fa8f7c514c087a1bd1506df64d48661.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.2.10","is_delete":0,"add_time":"2019-12-05 11:18:35","update_time":"2020-07-20 10:16:19","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e88d4dd935389049bdeda86856b59ed3.png"},{"id":29,"name":"stock","display_name":"股东分红","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/587fcc45b2813896aa108fa4fb300efe.png","desc":"股东分红","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/26ef64a6f23ff61df085860688b35ee3.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.2.38","is_delete":0,"add_time":"2020-02-12 11:30:23","update_time":"2020-06-15 18:09:46","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8174c99e859fd446abd47b877fd41a45.png"},{"id":30,"name":"pick","display_name":"N元任选","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/381f4834eeb53a9bd3a8627b59d55736.png","desc":"N元任选","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/3cf45d611470ad86341092d5f6ff8539.png\"></p>","sort":1000,"price":"800.00","route":"","is_open":1,"version":"4.2.50","is_delete":0,"add_time":"2020-03-11 15:55:15","update_time":"2020-06-15 18:05:41","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a4f3d48897eb696fd936816f33a2da70.png"},{"id":31,"name":"composition","display_name":"套餐组合","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5ee5677eda9b168c532ecd9e10e9da04.png","desc":"套餐组合","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/58b4448eee115f41d955e71d52aae243.png\"></p>","sort":1000,"price":"800.00","route":"","is_open":1,"version":"4.2.58","is_delete":0,"add_time":"2020-03-25 14:22:06","update_time":"2020-06-15 18:01:31","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/753525bea4854dae63ce83f575fa3a10.png"},{"id":32,"name":"assistant","display_name":"采集助手","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/027dd0471bd9d6d7c6ca17eba460545f.png","desc":"采集助手","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a20107cb6778241527b08d3f39bb1d69.png\"></p>","sort":1000,"price":"50.00","route":"","is_open":1,"version":"4.2.77","is_delete":0,"add_time":"2020-05-23 10:47:20","update_time":"2020-06-15 17:59:53","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8064507d4b463ae2be31dd86ccc4bd8d.png"},{"id":33,"name":"ecard","display_name":"电子卡密","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/62ae3400387a31463ae2535c3a267626.png","desc":"电子卡密","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ae3967d838263bc3d4f34ca89fc83782.png\"></p>","sort":1000,"price":"900.00","route":"","is_open":1,"version":"4.2.83","is_delete":0,"add_time":"2020-06-05 12:01:38","update_time":"2020-06-15 18:02:31","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ea1a03f8a27561ea18ec1312b102c3e3.png"},{"id":34,"name":"region","display_name":"区域代理","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/209e4f84d0f724a7dafabd0abd298522.png","desc":"区域代理","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/2f83b1e184163b99628f7c3e8890f049.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.2.98","is_delete":0,"add_time":"2020-06-29 15:31:58","update_time":"2020-06-29 16:51:19","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/1a698f7c1b6c64b57f878d735734858b.png"},{"id":35,"name":"flash_sale","display_name":"限时抢购","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0c4882580c64ac1c5a239b653b2e429f.png","desc":"一段时间内以指定的优惠出售","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ccfa6de1c8d23b70b48ae640827c4359.png\"></p>","sort":1000,"price":"688.00","route":"","is_open":1,"version":"4.3.8","is_delete":0,"add_time":"2020-07-15 10:01:41","update_time":"2020-07-15 13:54:53","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0c4882580c64ac1c5a239b653b2e429f.png"},{"id":36,"name":"community","display_name":"社区团购","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8fec059e37dbf85adb01bd1d7f904d1e.png","desc":"团长群内推广，本地社区自提","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4fb706a2d4526ca10ed224ec95e7e7d0.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/acf7a8fff14d955bca05d112eb126df3.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5f1aebfc746e730d0abe477ed3c9f2bd.png\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4355aaf57b26ee8842a5b22924434495.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.3.14","is_delete":0,"add_time":"2020-07-31 16:08:23","update_time":"2020-07-31 16:59:31","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/8fec059e37dbf85adb01bd1d7f904d1e.png"},{"id":37,"name":"exchange","display_name":"兑换中心（礼品卡）","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e7116ea1eee1ae77747cca91cc9f7fed.png","desc":"提货卡、礼品卡、送礼神器","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/0428d029959fe7411360aa73cb0a643b.png\"></p>","sort":1000,"price":"1000.00","route":"","is_open":1,"version":"4.3.45","is_delete":0,"add_time":"2020-09-08 17:22:16","update_time":"2021-01-15 10:11:20","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e7116ea1eee1ae77747cca91cc9f7fed.png"},{"id":38,"name":"wholesale","display_name":"商品批发","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a646bebae632d967be78ee275e9921a5.png","desc":"商品批发","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/346eb4f661ee7afaec71064a99f788ca.png\"></p>","sort":1000,"price":"1000.00","route":"","is_open":1,"version":"4.3.52","is_delete":0,"add_time":"2020-10-14 11:39:06","update_time":"2021-03-04 13:56:27","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a646bebae632d967be78ee275e9921a5.png"},{"id":39,"name":"mobile","display_name":"h5商城","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/68ac2d12965cb0bed7c2c5d357418826.png","desc":"h5商城","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a99f2f1b0226f67b8d90c014ea2eeae7.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.3.76","is_delete":0,"add_time":"2020-12-08 17:07:06","update_time":"2020-12-09 10:12:46","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/68ac2d12965cb0bed7c2c5d357418826.png"},{"id":40,"name":"wechat","display_name":"公众号商城","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/75f92915101acd808800a72d022b0940.png","desc":"公众号商城","content":"<p><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a99f2f1b0226f67b8d90c014ea2eeae7.png\"></p>","sort":1000,"price":"1688.00","route":"","is_open":1,"version":"4.3.76","is_delete":0,"add_time":"2020-12-08 17:23:02","update_time":"2020-12-09 10:12:34","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/75f92915101acd808800a72d022b0940.png"},{"id":41,"name":"teller","display_name":"收银台","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/775debae7be373dbb37d2c9d3ca269ad.png","desc":"门店收银与线上商城完美结合","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/4407a61560d1b73095e122745cfc8662.png\"></p>","sort":1000,"price":"1998.00","route":"","is_open":1,"version":"4.4.1","is_delete":0,"add_time":"2021-01-13 14:43:54","update_time":"2021-01-13 16:22:37","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/775debae7be373dbb37d2c9d3ca269ad.png"},{"id":42,"name":"fission","display_name":"红包墙","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9a7e9bb462ece248a6da22d7a2a39b84.png","desc":"红包墙","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/ec04848907a7a520aa5a3c55d2a68e5d.png\"></p>","sort":1000,"price":"688.00","route":"","is_open":1,"version":"4.4.14","is_delete":0,"add_time":"2021-02-22 10:58:51","update_time":"2021-02-23 14:03:34","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/9a7e9bb462ece248a6da22d7a2a39b84.png"},{"id":43,"name":"url_scheme","display_name":"微信链接生成工具","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/2ee655a1f7f705776d59354bdae7e461.png","desc":"微信链接生成工具","content":"<p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/debae26b049cfad319902d36dc8b931b.png\"></p>","sort":1000,"price":"0.00","route":"","is_open":1,"version":"4.4.15","is_delete":0,"add_time":"2021-03-01 11:22:51","update_time":"2021-03-01 17:53:06","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/2ee655a1f7f705776d59354bdae7e461.png"},{"id":44,"name":"ma_ke","display_name":"同城速送","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a6c0264443a2bb769e63394fc8cddbea.png","desc":"智慧派单系统，打通同城配送各个环节","content":"<p class=\"ql-align-center\">说明：<span style=\"color: rgb(230, 0, 0);\">此插件非官方系统功能</span>，对接的是第三方软件（码科跑腿），须配合码科跑腿才能使用</p><p class=\"ql-align-center\"><img src=\"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/5f762d67fdc5230d4115d964ea4efc6d.png\"></p>","sort":1000,"price":"0.00","route":"","is_open":1,"version":"4.4.18","is_delete":0,"add_time":"2021-03-10 16:25:06","update_time":"2021-03-11 09:36:56","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/a6c0264443a2bb769e63394fc8cddbea.png"},{"id":45,"name":"minishop","display_name":"自定义交易组件","pic_url":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e6ac30ca874a0ac27668c201a43b5ac5.png","desc":"对接微信视频号，实现从微信视频号主页、视频号直播，直接跳转商家制作的小程序商城。","content":"<h2><strong>小程序与视频号如何关联？</strong></h2><p><span style=\"color: rgb(51, 51, 51);\">首先，商家需要在微信小程序后台申请</span><strong style=\"color: rgb(0, 102, 204);\">\"自定义版交易组件\"</strong><span style=\"color: rgb(51, 51, 51);\">权限，申请成功后，可以将小程序关联至对应视频号，并在视频号</span><span style=\"color: rgb(0, 102, 204);\">直播带货</span><span style=\"color: rgb(51, 51, 51);\">中使用。</span></p><p class=\"ql-align-justify\"><strong>但是满足条件的才能申请：</strong></p><ul><li class=\"ql-align-justify\">要求小程序主体为非个人；</li><li class=\"ql-align-justify\">小程序已发布上线；</li><li class=\"ql-align-justify\">有商家自营类目，非电商平台；</li><li class=\"ql-align-justify\">具备客服及售后能力入口；</li><li class=\"ql-align-justify\">无严重违规等。</li></ul><p class=\"ql-align-justify\">其次，接入小程序的视频号要求是企业认证的视频号，且视频号认证主体和微信小程序认证主体一致，或者视频号的管理员和小程序的管理员一致。</p>","sort":1000,"price":"0.00","route":"","is_open":1,"version":"4.4.29","is_delete":0,"add_time":"2021-03-29 15:59:11","update_time":"2021-04-07 17:39:25","new_icon":"http://auth-zjhejiang-com.oss-cn-hangzhou.aliyuncs.com/uploads/versions/e6ac30ca874a0ac27668c201a43b5ac5.png"}],"cats":[{"id":1,"name":"hj-5wwewehdi39siabr","display_name":"销售渠道","color":"#3399FF","sort":1,"icon":null,"is_delete":0,"add_time":"2020-06-04 14:30:14","update_time":"2020-06-15 14:44:28"},{"id":2,"name":"hj-pgrxgba8aow62okf","display_name":"促销玩法","color":"#FAA322","sort":2,"icon":null,"is_delete":0,"add_time":"2020-06-04 14:30:21","update_time":"2020-06-15 14:45:51"},{"id":3,"name":"hj-nj85lj-so7tc6azq","display_name":"获客工具","color":"#0DBCD7","sort":3,"icon":null,"is_delete":0,"add_time":"2020-06-04 14:30:28","update_time":"2020-06-15 14:46:04"},{"id":4,"name":"hj-pfrdhuk5bt2z5npy","display_name":"客户维护","color":"#E84C52","sort":4,"icon":null,"is_delete":0,"add_time":"2020-06-04 14:32:41","update_time":"2020-06-15 14:46:16"},{"id":5,"name":"hj-llgarrhpltiwhrlp","display_name":"常用工具","color":"#7181D9","sort":5,"icon":null,"is_delete":0,"add_time":"2020-06-04 14:32:48","update_time":"2020-07-01 16:39:01"}],"relations":[{"id":35,"plugin_name":"advance","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":36,"plugin_name":"aliapp","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":37,"plugin_name":"app_admin","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":38,"plugin_name":"assistant","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":39,"plugin_name":"bargain","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":40,"plugin_name":"bdapp","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":41,"plugin_name":"bonus","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":43,"plugin_name":"check_in","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":45,"plugin_name":"composition","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":46,"plugin_name":"dianqilai","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":48,"plugin_name":"ecard","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":51,"plugin_name":"integral_mall","plugin_cat_name":"hj-pfrdhuk5bt2z5npy"},{"id":52,"plugin_name":"lottery","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":54,"plugin_name":"miaosha","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":55,"plugin_name":"pick","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":56,"plugin_name":"pintuan","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":57,"plugin_name":"pond","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":58,"plugin_name":"quick_share","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":60,"plugin_name":"scratch","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":62,"plugin_name":"step","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":63,"plugin_name":"stock","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":64,"plugin_name":"ttapp","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":65,"plugin_name":"vip_card","plugin_cat_name":"hj-pfrdhuk5bt2z5npy"},{"id":66,"plugin_name":"wxapp","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":71,"plugin_name":"region","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":79,"plugin_name":"flash_sale","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":80,"plugin_name":"gift","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":81,"plugin_name":"clerk","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":85,"plugin_name":"mch","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":90,"plugin_name":"community","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":91,"plugin_name":"diy","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":92,"plugin_name":"fxhb","plugin_cat_name":"hj-nj85lj-so7tc6azq"},{"id":93,"plugin_name":"scan_code_pay","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":97,"plugin_name":"booking","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":107,"plugin_name":"公众号商城","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":109,"plugin_name":"wechat","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":110,"plugin_name":"mobile","plugin_cat_name":"hj-5wwewehdi39siabr"},{"id":111,"plugin_name":"shopping","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":114,"plugin_name":"teller","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":115,"plugin_name":"exchange","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":119,"plugin_name":"fission","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":124,"plugin_name":"url_scheme","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":125,"plugin_name":"wholesale","plugin_cat_name":"hj-pgrxgba8aow62okf"},{"id":130,"plugin_name":"ma_ke","plugin_cat_name":"hj-llgarrhpltiwhrlp"},{"id":135,"plugin_name":"minishop","plugin_cat_name":"hj-llgarrhpltiwhrlp"}]}}';
            $res=json_decode($data,true);
            return $res['data'];
        }
        $url = $this->getUrl($url);
        $url = $this->appendParams($url, $params);
        $body = $this->curlRequest('get', $url);
        $res = json_decode($body, true);
        if (!$res) {
            throw new \Exception('Cloud response body `' . $body . '` could not be decode.');
        }
        if ($res['code'] !== 0) {
            if ($res['code'] === -1) {
                throw new CloudNotLoginException($res['msg']);
            } else {
                throw new CloudException($res['msg'], $res['code'], null, $res);
            }
        }
        return $res['data'];
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws CloudException
     * @throws Exception
     */
    public function httpPost($url, $params = [], $data = [])
    {
        $url = $this->getUrl($url);
        $url = $this->appendParams($url, $params);
        $body = $this->curlRequest('post', $url, $data);
        $res = json_decode($body, true);
        if (!$res) {
            throw new \Exception('Cloud response body `' . $body . '` could not be decode.');
        }
        if ($res['code'] !== 0) {
            throw new CloudException($res['msg'], $res['code'], null, $res);
        }
        return $res['data'];
    }

    private function getUrl($url)
    {
        if (mb_stripos($url, 'http') === 0) {
            return $url;
        }
        $url = mb_stripos($url, '/') === 0 ? mb_substr($url, 1) : $url;
        $baseUrl = base64_decode($this->xBaseUrl);
        $baseUrl = mb_stripos($baseUrl, '/') === (mb_strlen($baseUrl) - 1) ? $baseUrl : $baseUrl . '/';
        return $baseUrl . $url;
    }

    private function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = $this->paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    private function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            if ($this->urlEncodeQueryString) {
                $v = urlencode($v);
            }
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }

    public function getLocalAuthInfo()
    {
        if ($this->xLocalAuthInfo) {
            return $this->xLocalAuthInfo;
        }
        $this->xLocalAuthInfo = CommonOption::get('local_auth_info');
        if (!$this->xLocalAuthInfo) {
            $this->xLocalAuthInfo = [];
        }
        return $this->xLocalAuthInfo;
    }

    public function setLocalAuthInfo($data)
    {
        return CommonOption::set('local_auth_info', $data);
    }

    public function getLocalAuthDomain()
    {
        $localAuthInfo = $this->getLocalAuthInfo();
        if ($localAuthInfo && !empty($localAuthInfo['domain'])) {
            return $localAuthInfo['domain'];
        }
        return \Yii::$app->request->hostName;
    }

    public function download($url, $file)
    {
        if (!is_dir(dirname($file))) {
            if (!make_dir(dirname($file))) {
                throw new CloudException('无法创建目录，请检查文件写入权限。');
            }
        }
        $fp = fopen($file, 'w+');
        if ($fp === false) {
            throw new CloudException('无法保存文件，请检查文件写入权限。');
        }

        $client = new Client([
            'verify' => false,
            'stream' => true,
        ]);
        $response = $client->get($url);
        $body = $response->getBody();
        while (!$body->eof()) {
            fwrite($fp, $body->read(1024));
        }
        fclose($fp);
        return $file;
    }

    private function getPluginsJson()
    {
        $list = [];
        try {
            if (!$this->cps) {
                $cps = CorePlugin::find()->select('name')->where(['is_delete' => 0,])->all();
                $this->cps = $cps;
            } else {
                $cps = $this->cps;
            }
            foreach ($cps as $cp) {
                $list[] = $cp->name;
            }
        } catch (\Exception $exception) {
        }
        return json_encode($list);
    }

    private function getAccountNum()
    {
        if ($this->accountCount === null) {
            try {
                $countWithSu = AdminInfo::find()
                    ->alias('ai')
                    ->innerJoin(['u' => User::tableName()], 'u.id=ai.user_id')
                    ->where([
                        'AND',
                        ['ai.is_delete' => 0,],
                        ['ai.we7_user_id' => 0,],
                        ['u.is_delete' => 0,],
                        ['u.mall_id' => 0,],
                    ])->count();
                if ($countWithSu && $countWithSu > 0) {
                    $count = $countWithSu - 1;
                } else {
                    $count = $countWithSu;
                }
                $this->accountCount = $count ? intval($count) : 0;
            } catch (Exception $exception) {
                $this->accountCount = 0;
            }
        }
        return $this->accountCount;
    }

    /**
     * @param string $method get or post
     * @param string $url request url
     * @param array | null $data the post data
     * @return bool|string
     * @throws \Exception
     */
    private function curlRequest($method, $url, $data = null)
    {
        try {
            $version = app_version();
        } catch (\Exception $e) {
            $version = '0.0.0';
        }
        $requestHeader = [
            'X-Domain: ' . \Yii::$app->request->hostName,
            'X-Version: ' . $version,
            'X-Plugins: ' . $this->getPluginsJson(),
            'X-Account-Num: ' . $this->getAccountNum(),
            'X-Request-Info: ' . base64_encode(json_encode([
                'current_dir' => dirname(__DIR__),
            ])),
            'X-Type: 1',
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeader);
        if (strtolower($method) === 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($data) {
                $data = is_string($data) ? $data : http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        $content = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if($info['http_code']=='404' || $info['http_code']=='500'){
            $content='{"code":1,"msg":"OK","data":[]}';
            return $content;
        }
        if ($errno) {
            throw new Exception('Cloud: ' . $error);
        }
        return $content;
    }
}
