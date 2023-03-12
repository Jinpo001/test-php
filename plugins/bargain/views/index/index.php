<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-banner');
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }
    .red {
        display:inline-block;
        padding: 0 25px;
        color: #ff4544;
    }
    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }
    .button-item {
        margin-top: 20px;
        padding: 9px 25px;
    }
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
    }

    .red {
        color: #ff4544;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<style>
    .mobile-box {
        width: 400px;
        height: calc(800px - 20px);
        padding: 35px 11px;
        background-color: #fff;
        border-radius: 30px;
        background-size: cover;
        position: relative;
        font-size: .85rem;
        float: left;
        margin-right: 1rem;
    }

    .mobile-box .show-box {
        height: calc(667px - 20px);;
        width: 375px;
        overflow: auto;
        font-size: 12px;
        overflow-x: hidden;
    }

    .show-box::-webkit-scrollbar { /*滚动条整体样式*/
        width: 1px; /*高宽分别对应横竖滚动条的尺寸*/
    }

    .menus-box .menu-item {
        cursor: move;
        background-color: #fff;
        margin: 5px 0;
    }

    .head-bar {
        width: 378px;
        height: 64px;
        position: relative;
        background: url('statics/img/mall/home_block/head.png') center no-repeat;
    }

    .head-bar div {
        position: absolute;
        text-align: center;
        width: 378px;
        font-size: 20px;
        font-weight: 600;
        height: 64px;
        line-height: 88px;
    }

    .head-bar img {
        width: 378px;
        height: 64px;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" label-width="150px" :rules="rule" ref="form">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础设置" class="form-body" name="first">
                        <app-setting v-model="form" :is_member_price="false" :is_territorial_limitation="true"
                                     :is_coupon="true"></app-setting>

                        <el-card style="margin-bottom: 10px">
                            <div slot="header">砍价规则设置</div>
                            <el-form-item label="活动规则" prop="rule">
                                <div style="width: 458px; min-height: 458px;">
                                    <app-rich-text v-model="form.rule"></app-rich-text>
                                </div>
                            </el-form-item>
                            <el-form-item label="活动标题" prop="title">
                                <label slot="label">活动标题
                                    <el-tooltip class="item" effect="dark"
                                                content="多个标题请换行，多个标题随机选一个标题显示"
                                                placement="top">
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input class="ml-24" style="width: 600px" type="textarea" :rows="3"
                                          placeholder="请输入活动标题"
                                          v-model="form.title"></el-input>
                            </el-form-item>
                        </el-card>

                    </el-tab-pane>
                    <el-tab-pane v-if="false" label="自定义海报" class="form-body" name="second">
                        <app-poster :rule_form="form.goods_poster"
                                    :goods_component="goodsComponent"
                                    goods_component_key_tmp="head"
                        ></app-poster>
                    </el-tab-pane>
                    <el-tab-pane label="轮播图" class="form-body" name="third">
                        <app-banner url="plugin/bargain/mall/index/banner-store"
                                    submit_url="plugin/bargain/mall/index/banner-store" :title="false"></app-banner>
                    </el-tab-pane>
                    <el-tab-pane label="背景图设置" name="bg">
                        <div style="display: flex;">
                            <div class="mobile-box">
                                <div class="head-bar" flex="main:center cross:center">
                                    <div>砍价</div>
                                </div>
                                <div class="show-box" style="position: relative">
                                    <app-image style="background-size: 100% 100%;position: absolute;z-index: 2"
                                               src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('bargain') . '/img/prize-detail.png' ?>"
                                               width='100%' height='412'></app-image>
                                    <app-image :src="form.bg_pic" width='100%' height='412'></app-image>

                                    <app-image style="background-size: 100% 100%;position: absolute;z-index: 2"
                                               src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('bargain') . '/img/prize-head.png' ?>"
                                               width='100%' height='315'></app-image>

                                    <div style="height: 315px;position: absolute;top: 412px;width: 100%"
                                         :style="{background: 'linear-gradient(' + form.bg_color +', '+ (form.bg_color_type === 'gradient' ? form.bg_gradient_color: form.bg_color) + ')'}">
                                    </div>
                                </div>
                            </div>

                            <div style="width: 100%">
                                <div style="background: #FFFFFF;padding: 30px 0px">
                                    <el-form-item label="背景图" prop="bg_pic">
                                        <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                                        @selected="selectBgPic">
                                            <el-tooltip effect="dark"
                                                        content="建议尺寸:750 * 870"
                                                        placement="top">
                                                <el-button size="mini">选择图标</el-button>
                                            </el-tooltip>
                                        </app-attachment>
                                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                            <app-attachment :multiple="false" :max="1"
                                                            @selected="selectBgPic">
                                                <app-image mode="aspectFill"
                                                           width="80px"
                                                           height='80px'
                                                           :src="form.bg_pic">
                                                </app-image>
                                            </app-attachment>
                                        </div>
                                        <el-button size="mini" @click="resetImg('bg_pic')"
                                                   style="position: absolute;top: 7px;left: 90px" type="primary">
                                            恢复默认
                                        </el-button>
                                    </el-form-item>
                                    <el-form-item label="下半部颜色配置" prop="bg_color_type">
                                        <el-radio v-model="form.bg_color_type" label="pure">纯色</el-radio>
                                        <el-radio v-model="form.bg_color_type" label="gradient">渐变</el-radio>
                                    </el-form-item>
                                    <el-form-item label="下半部背景颜色" prop="bg_color">
                                        <div flex="dir:left cross:center">
                                            <el-color-picker
                                                    @change="(row) => {row == null ? form.bg_color = '#f12416' : ''}"
                                                    size="small"
                                                    v-model="form.bg_color"></el-color-picker>
                                            <el-input size="small" style="width: 80px;margin-left: 5px;"
                                                      v-model="form.bg_color"></el-input>
                                        </div>
                                    </el-form-item>
                                    <el-form-item v-if="form.bg_color_type === 'gradient'" label="下半部渐变颜色配置"
                                                  prop="bg_gradient_color">
                                        <div flex="dir:left cross:center">
                                            <el-color-picker
                                                    @change="(row) => {row == null ? form.bg_gradient_color = '#f12416' : ''}"
                                                    size="small"
                                                    v-model="form.bg_gradient_color"></el-color-picker>
                                            <el-input size="small" style="width: 80px;margin-left: 5px;"
                                                      v-model="form.bg_gradient_color"></el-input>
                                        </div>
                                    </el-form-item>


                                </div>
                                <el-button class="button-item" :loading="btnLoading" type="primary"
                                           @click="store('form')" size="small" v-if="activeName != 'third'">保存
                                </el-button>
                                <el-button class="button-item" @click="reDefault">恢复默认</el-button>
                            </div>

                        </div>
                    </el-tab-pane>
                </el-tabs>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small"
                           v-if="activeName != 'third' && activeName != 'bg'">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {},
                rule: {},
                is_show: false,
                activeName: 'first',
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'poster_bg',
                        icon_url: 'statics/img/mall/poster/icon-mark.png',
                        title: '标识',
                        is_active: true
                    },
                    {
                        key: 'time_str',
                        icon_url: 'statics/img/mall/poster/icon_time.png',
                        title: '时间',
                        is_active: true
                    },
                ],
            };
        },
        methods: {
            reDefault(){
                this.resetImg('bg_pic');
                this.form.bg_color = '#f46655';
                this.form.bg_color_type = 'gradient';
                this.form.bg_gradient_color = '#fdac42';
            },
            selectBgPic(e) {
                if (e.length) {
                    this.form.bg_pic = e.shift()['url'];
                }
            },
            resetImg(type) {
                if (type === 'bg_pic') {
                    this.form.bg_pic = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('bargain') . '/img/bg-pic.png' ?>";
                }
            },
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/bargain/mall/index/index-data'
                            },
                            method: 'post',
                            data: this.form
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        return false;
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/bargain/mall/index/index-data'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.form = e.data.data.list;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        },

        created() {
            this.loadData();
        },
    })
</script>