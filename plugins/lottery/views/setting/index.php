<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');

?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
    .red {
        color: #ff4544;
        padding: 0 10px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px !important;
    }

    .wechat-end-box {
        height: 32px;
        line-height: 32px;
        width: 200px;
        padding: 0 12px;
        color: #606266;
        border-left: 1px solid #e2e2e2;
        border-right: 1px solid #e2e2e2;
        border-bottom: 1px solid #e2e2e2;
    }

    .wechat-image {
        height: 232px;
        width: 200px;
        cursor: pointer;
        position: relative;
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
             v-loading="listLoading">
        <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础设置" name="first">
                    <app-setting v-model="form" :is_discount="false" :is_share="false"
                                 :is_territorial_limitation="false"></app-setting>
                    <div class="form-body">
                        <el-form-item label="小程序标题" prop="title">
                            <el-input size="small" style="width: 30%" v-model="form.title"
                                      autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="中奖码获取规则" prop="type">
                            <el-radio-group v-model="form.type">
                                <el-radio :label="0">分享点击即送</el-radio>
                                <el-radio :label="1">参加抽奖即送</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item label="规则说明" prop="rule">
                            <div style="width: 458px; min-height: 458px;">
                                <app-rich-text v-model="form.rule"></app-rich-text>
                            </div>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" v-if="false"  class="form-body" style="padding: 0;background:none" name="second">
                    <app-poster :rule_form="form.goods_poster" :goods_component="goodsComponent"></app-poster>
                </el-tab-pane>
                <el-tab-pane label="客服设置" class="form-body" name="three">
                    <el-form-item class="switch" label="是否开启客服提示" prop="cs_status">
                        <el-switch v-model="form.cs_status" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item label="客服提示图片" prop="cs_prompt_pic">
                        <div style="margin-bottom:10px;">
                            <app-attachment style="display:inline-block;margin-right: 10px" :multiple="false" :max="1"
                                            @selected="wechatPrompt">
                                <el-tooltip effect="dark" content="建议尺寸:750 * 150" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <el-button type="primary" @click="wechatPromptDefault" size="mini">恢复默认</el-button>
                        </div>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1" @selected="wechatPrompt">
                                <app-image mode="aspectFill" width="80px" height='80px'
                                           :src="form.cs_prompt_pic"></app-image>
                            </app-attachment>
                            <el-button v-if="form.cs_prompt_pic" class="del-btn" size="mini" type="danger"
                                       icon="el-icon-close" circle @click="wechatPromptClose"></el-button>
                        </div>
                    </el-form-item>

                    <el-form-item label="客服微信" prop="cs_wechat">
                        <el-button size="mini" @click="addWechat">选择</el-button>
                        <div flex="dir:left" style="flex-wrap:wrap">
                            <div v-for="(value,index) in form.cs_wechat" style="margin-right: 24px;margin-top: 12px">
                                <div class="wechat-image" flex="dir:top"
                                     @click="editWechat(value,index)">
                                    <el-image :src="value.qrcode_url" style="height: 200px;width:100%"></el-image>
                                    <el-tooltip class="v" effect="dark" :content="'微信号'+ value.name" placement="top">
                                        <div class="wechat-end-box">微信号：{{value.name}}</div>
                                    </el-tooltip>
                                    <el-button v-if="form.cs_prompt_pic" class="del-btn" size="mini" type="danger"
                                               icon="el-icon-close" circle @click.stop="picClose(index)"></el-button>
                                </div>
                            </div>
                        </div>
                        <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                    </el-form-item>

                    <el-form-item label="微信群二维码" prop="cs_wechat_flock_qrcode_pic">
                        <app-attachment style="margin-bottom:10px" :multiple="true" :max="50" @selected="wechatFlock">
                            <el-tooltip effect="dark" content="建议尺寸:360 * 360" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <app-gallery v-if="form.cs_wechat_flock_qrcode_pic && form.cs_wechat_flock_qrcode_pic.length"
                                     :show-delete="true" @deleted="wechatFlockClose"
                                     :list="form.cs_wechat_flock_qrcode_pic"></app-gallery>
                        <app-image v-else width="80px" height='80px'></app-image>
                        <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                    </el-form-item>
                </el-tab-pane>
                <el-tab-pane label="背景图设置" name="bg">
                    <div style="display: flex;">
                        <div class="mobile-box">
                            <div class="head-bar" flex="main:center cross:center">
                                <div>抽奖</div>
                            </div>
                            <div class="show-box" style="position: relative">
                                <app-image style="background-size: 100% 100%;position: absolute;z-index: 2"
                                           src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/lottery-ht.png' ?>"
                                           width='100%' height='100%'></app-image>
                                <app-image :src="form.bg_pic" width='100%' height='120'></app-image>
                                <div style="height: calc(100% - 120px);position: absolute;top: 120px;width: 100%"
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
                                                    content="建议尺寸:750 * 242"
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
                                               style="position: absolute;top: 7px;left: 90px" type="primary">恢复默认
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
                            <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">
                                提交
                            </el-button>
                            <el-button class="button-item" @click="reDefault">恢复默认</el-button>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <el-button class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">提交</el-button>
        </el-form>

        <!--客服微信-->
        <el-dialog title="客服微信" :visible.sync="wechatVisible" width="30%" :close-on-click-modal="false">
            <el-form :model="wechatForm" label-width="150px" :rules="wechatRules" ref="wechatForm"
                     @submit.native.prevent>
                <el-form-item label="客服微信二维码" prop="qrcode_url">
                    <div style="margin-bottom:10px;">
                        <app-attachment style="display:inline-block;margin-right: 10px" :multiple="false" :max="1"
                                        @selected="wechatSelect">
                            <el-tooltip effect="dark" content="建议尺寸:360 * 360" placement="top">
                                <el-button size="mini">选择文件</el-button>
                            </el-tooltip>
                        </app-attachment>
                    </div>
                    <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                        <app-attachment :multiple="false" :max="1" @selected="wechatSelect">
                            <app-image mode="aspectFill" width="80px" height='80px'
                                       :src="wechatForm.qrcode_url"></app-image>
                        </app-attachment>
                        <el-button v-if="wechatForm.qrcode_url" class="del-btn" size="mini" type="danger"
                                   icon="el-icon-close" circle @click="wechatClose"></el-button>
                    </div>
                </el-form-item>
                <el-form-item label="客服微信号" prop="name">
                    <el-input size="small" v-model="wechatForm.name" auto-complete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="wechatVisible = false">取消</el-button>
                <el-button size="small" type="primary" @click.native="wechatSubmit">提交</el-button>
            </div>
        </el-dialog>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                wechatVisible: false,
                index: -1,
                wechatForm: {
                    qrcode_url: '',
                    name: '',
                },
                wechatRules: {
                    qrcode_url: [
                        {required: true, message: '图片不能为空', trigger: 'blur'},
                    ]
                },

                form: {
                    payment_type: ['online_pay'],
                    send_type: ['express', 'offline'],
                    bg_pic: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/bg-pic.png' ?>",
                    bg_color: '#f12416',
                    bg_color_type: 'pure',
                    bg_gradient_color: '#f12416',
                },
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    type: [
                        {required: true, message: '规则不能为空', trigger: 'blur'},
                    ]
                },
                cs_default: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/prompt.png",
                activeName: 'bg',
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
                        key: 'poster_bg_two',
                        icon_url: 'statics/img/mall/poster/icon-free.png',
                        title: '免费标识',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品原价',
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
                ],
                goodsComponentKey: 'head',
            };
        },
        methods: {
            reDefault() {
                this.resetImg('bg_pic');
                this.form.bg_color = '#ff4544';
                this.form.bg_color_type = 'pure';
                this.form.bg_gradient_color = '#ff4544';
            },
            selectBgPic(e) {
                if (e.length) {
                    this.form.bg_pic = e.shift()['url'];
                }
            },
            resetImg(type) {
                if (type === 'bg_pic') {
                    this.form.bg_pic = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/bg-pic.png' ?>"
                }
            },
            wechatSelect(e) {
                if (e.length) {
                    this.wechatForm.qrcode_url = e[0].url;
                }
            },

            wechatClose() {
                this.wechatForm.qrcode_url = '';
            },
            wechatSubmit() {
                this.$refs.wechatForm.validate((valid) => {
                    if (valid) {
                        if (this.index === -1) {
                            this.form.cs_wechat.push(Object.assign({}, this.wechatForm));
                        } else {
                            this.form.cs_wechat.splice(this.index, 1, this.wechatForm);
                        }
                        this.wechatVisible = false;
                    }
                });
            },

            picClose(index) {
                this.form.cs_wechat.splice(index, 1);
            },

            addWechat() {
                this.index = -1;
                this.wechatForm = {
                    qrcode_url: '',
                    name: '',
                };
                this.wechatVisible = true
            },

            editWechat(item, index) {
                this.index = index;
                this.wechatForm = Object.assign({}, item);
                this.wechatVisible = true;
            },

            wechatPrompt(e) {
                if (e.length) {
                    this.form.cs_prompt_pic = e[0].url;
                }
            },
            wechatPromptDefault() {
                this.form.cs_prompt_pic = this.cs_default;
            },
            wechatPromptClose() {
                this.form.cs_prompt_pic = '';
            },

            wechatFlock(e) {
                if (e.length) {
                    for (let i = 0; i < e.length; i++) {
                        this.form.cs_wechat_flock_qrcode_pic.push(e[i]);
                    }
                }
            },

            wechatFlockClose(e) {
                let pic = this.form.cs_wechat_flock_qrcode_pic;
                let index = pic.indexOf(e);
                this.form.cs_wechat_flock_qrcode_pic.splice(index, 1)
            },
            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'plugin/lottery/mall/setting',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg)
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.$message.error(e.data.msg);
                            this.btnLoading = false;
                        });
                    }
                });
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/lottery/mall/setting',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            this.form = e.data.data;
                            this.form.cs_wechat_qrcode_pic = this.form.cs_wechat_qrcode_pic ? this.form.cs_wechat_qrcode_pic : [];
                            this.form.cs_wechat_flock_qrcode_pic = this.form.cs_wechat_flock_qrcode_pic ? this.form.cs_wechat_flock_qrcode_pic : [];
                        }
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.$message.error(e.data.msg);
                    this.listLoading = false;
                });
            },
        },

        mounted: function () {
            this.getList();
        }
    })
</script>