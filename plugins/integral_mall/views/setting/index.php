<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }

    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
        background-color: #fff;
        margin-bottom: 10px;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 25px;
    }
    .red {
        padding: 0px 25px;
        color: #ff4544;
    }

    .setting-item {
        background-color: #f3f3f3;
        padding-bottom: 10px;
    }

    .setting-item:last-of-type {
        padding-bottom: 0;
    }

    .setting-item .el-card {
        background-color: #fff
    }


    .mobile {
        width: 400px;
        height: 740px;
        border: 1px solid #cccccc;
        padding: 25px 10px;
        border-radius: 30px;
        margin: 0 20px;
        position: relative;
        flex-shrink: 0;
        background-color: #ffffff;
    }


    .mobile>div {
        height: 690px;
        position: absolute;
        overflow-x: hidden;
        overflow-y: auto;
        background-size: 100% 100%;
    }

    .mobile+div .form-body {
        padding: 40px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 40%;
        min-width: 900px;
    }
    .mobile .mobile-title {
        position: absolute;
        top: 30px;
        left: 10px;
        text-align: center;
        width: 375px;
        font-size: 16px;
        font-weight: 600;
        color: #303133;
    }

    .reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }

    .color {
        margin-left: 10px;
    }

    .del-btn.el-button--mini.is-circle {
        position: absolute;
        top: -8px;
        right: -8px;
        padding: 4px;
    }

    .mobile .bg-img {
        height: 110px;
        width: 375px;
        position: absolute;
        left: 0;
        top: 65px;
    }
    .mobile .my-point {
        text-align: center;
        position: absolute;
        top: 78px;
        left: 0;
        width: 375px;
        font-size: 12px;
    }
    .mobile .about-icon {
        position: absolute;
        top: 79px;
        right: 38%;
        width: 15px;
        height: 15px;
    }
    .mobile .point-num {
        text-align: center;
        position: absolute;
        top: 100px;
        left: 0;
        width: 375px;
        font-size: 24px;
    }
    .exchange-icon {
        width: 50%;
        height: 17px;
        font-size: 14px;
        position: absolute;
        top: 140px;
    }
    .exchange-icon img {
        height: 17px;
        width: 17px;
        margin-right: 8px;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="cardLoading">
        <div class="text item" style="width:100%">
            <el-form :model="ruleForm" label-width="150px" :rules="rules" ref="ruleForm">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基础设置" class="form-body" name="first">
                        <div class="setting-item">
                            <app-setting v-model="ruleForm" :is_member_price="false" :is_integral="false"></app-setting>
                        </div>

                        <div class="setting-item">
                            <el-card shadow="never">
                                <div slot="header">
                                    <span>说明设置</span>
                                </div>
                                <el-form-item label="积分说明" prop="desc">
                                    <div style="width: 458px; min-height: 458px;">
                                        <app-rich-text v-model="ruleForm.rule"></app-rich-text>
                                    </div>
                                </el-form-item>
                            </el-card>
                        </div>
                    </el-tab-pane>
<!--                    <el-tab-pane v-if="false"  label="自定义海报" class="form-body" name="second">-->
<!--                        <app-poster :rule_form="ruleForm.goods_poster"-->
<!--                                    :goods_component="goodsComponent"-->
<!--                        ></app-poster>-->
<!--                    </el-tab-pane>-->
                    <el-tab-pane label="轮播图" class="form-body" name="third">
                        <app-banner :title="false" url="plugin/integral_mall/mall/banner/index" submit_url="plugin/integral_mall/mall/banner/edit"></app-banner>
                    </el-tab-pane>
                    <el-tab-pane label="自定义背景图" name="second">
                        <div v-if="ruleForm && ruleForm.customize">
                            <div flex="dir:left">
                                <div class="mobile">
                                    <div style="height: 690px;position: absolute;overflow-x: hidden;overflow-y: auto;">
                                        <img src="statics/img/plugins/template.png" alt="">
                                        <div class="mobile-title">积分商城</div>
                                        <img class="bg-img" :src="ruleForm.customize.top_bg" alt="">
                                        <div class="my-point" :style="{'color': ruleForm.customize.integral_text}">我的积分</div>
                                        <img class="about-icon" :src="ruleForm.customize.about_icon" alt="">
                                        <div class="point-num" :style="{'color': ruleForm.customize.integral_num_text}">132400</div>
                                        <div class="exchange-icon" style="left: 0" flex="main:center cross:center">
                                            <img :src="ruleForm.customize.exchange_icon" alt="">
                                            <div class="exchange-text" :style="{'color': ruleForm.customize.exchange_text}">我的兑换</div>
                                        </div>
                                        <div class="exchange-icon" style="right: 0" flex="main:center cross:center">
                                            <img :src="ruleForm.customize.log_icon" alt="">
                                            <div class="exchange-text" :style="{'color': ruleForm.customize.log_text}">积分明细</div>
                                        </div>
                                    </div>
                                </div>
                                <div style="width: 100%;">
                                    <div class="form-body">
                                        <el-form-item label="顶部背景图片" label-width="180px" prop="top_bg">
                                            <div style="position: relative">
                                                <app-attachment v-model="ruleForm.customize.top_bg" :multiple="false" :max="1">
                                                    <el-tooltip class="item" effect="dark" content="建议尺寸:750*220" placement="top">
                                                        <el-button size="mini">选择文件</el-button>
                                                    </el-tooltip>
                                                </app-attachment>
                                                <div style="margin-top: 10px;position: relative;display: inline-block">
                                                    <app-image width="100px"
                                                               height="100px"
                                                               mode="aspectFill"
                                                               :src="ruleForm.customize.top_bg">
                                                    </app-image>
                                                    <el-button v-if="ruleForm.customize.top_bg != ''" class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="ruleForm.customize.top_bg = ''"></el-button>
                                                </div>
                                                <el-button size="mini" @click="ruleForm.customize.top_bg = ruleForm.customize.default_top_bg" class="reset" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="我的积分字体颜色" label-width="180px" prop="integral_text">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker
                                                        size="small"
                                                        v-model="ruleForm.customize.integral_text"></el-color-picker>
                                                <el-input size="small" style="width: 90px;margin-left: 5px;"
                                                          v-model="ruleForm.customize.integral_text"></el-input>
                                                <el-button size="mini" @click="ruleForm.customize.integral_text = ruleForm.customize.default_integral_text" class="color" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="我的积分说明图标" label-width="180px" prop="about_icon">
                                            <div style="position: relative">
                                                <app-attachment v-model="ruleForm.customize.about_icon" :multiple="false" :max="1">
                                                    <el-tooltip class="item" effect="dark" content="建议尺寸:15*15" placement="top">
                                                        <el-button size="mini">选择文件</el-button>
                                                    </el-tooltip>
                                                </app-attachment>
                                                <div style="margin-top: 10px;position: relative;display: inline-block">
                                                    <app-image width="100px"
                                                               height="100px"
                                                               mode="aspectFill"
                                                               :src="ruleForm.customize.about_icon">
                                                    </app-image>
                                                    <el-button v-if="ruleForm.customize.about_icon != ''" class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="ruleForm.customize.about_icon = ''"></el-button>
                                                </div>
                                                <el-button size="mini" @click="ruleForm.customize.about_icon = ruleForm.customize.default_about_icon" class="reset" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="积分字体颜色" label-width="180px" prop="integral_num_text">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker
                                                        size="small"
                                                        v-model="ruleForm.customize.integral_num_text"></el-color-picker>
                                                <el-input size="small" style="width: 90px;margin-left: 5px;"
                                                          v-model="ruleForm.customize.integral_num_text"></el-input>
                                                <el-button size="mini" @click="ruleForm.customize.integral_num_text = ruleForm.customize.default_integral_num_text" class="color" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="我的兑换字体颜色" label-width="180px" prop="exchange_text">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker
                                                        size="small"
                                                        v-model="ruleForm.customize.exchange_text"></el-color-picker>
                                                <el-input size="small" style="width: 90px;margin-left: 5px;"
                                                          v-model="ruleForm.customize.exchange_text"></el-input>
                                                <el-button size="mini" @click="ruleForm.customize.exchange_text = ruleForm.customize.default_exchange_text" class="color" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="我的兑换图标" label-width="180px" prop="exchange_icon">
                                            <div style="position: relative">
                                                <app-attachment v-model="ruleForm.customize.exchange_icon" :multiple="false" :max="1">
                                                    <el-tooltip class="item" effect="dark" content="建议尺寸:17*17" placement="top">
                                                        <el-button size="mini">选择文件</el-button>
                                                    </el-tooltip>
                                                </app-attachment>
                                                <div style="margin-top: 10px;position: relative;display: inline-block">
                                                    <app-image width="100px"
                                                               height="100px"
                                                               mode="aspectFill"
                                                               :src="ruleForm.customize.exchange_icon">
                                                    </app-image>
                                                    <el-button v-if="ruleForm.customize.exchange_icon != ''" class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="ruleForm.customize.exchange_icon = ''"></el-button>
                                                </div>
                                                <el-button size="mini" @click="ruleForm.customize.exchange_icon = ruleForm.customize.default_exchange_icon" class="reset" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="积分明细字体颜色" label-width="180px" prop="log_text">
                                            <div flex="dir:left cross:center">
                                                <el-color-picker
                                                        size="small"
                                                        v-model="ruleForm.customize.log_text"></el-color-picker>
                                                <el-input size="small" style="width: 90px;margin-left: 5px;"
                                                          v-model="ruleForm.customize.log_text"></el-input>
                                                <el-button size="mini" @click="ruleForm.customize.log_text = ruleForm.customize.default_log_text" class="color" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                        <el-form-item label="积分明细图标" label-width="180px" prop="log_icon">
                                            <div style="position: relative">
                                                <app-attachment v-model="ruleForm.customize.log_icon" :multiple="false" :max="1">
                                                    <el-tooltip class="item" effect="dark" content="建议尺寸:17*17" placement="top">
                                                        <el-button size="mini">选择文件</el-button>
                                                    </el-tooltip>
                                                </app-attachment>
                                                <div style="margin-top: 10px;position: relative;display: inline-block">
                                                    <app-image width="100px"
                                                               height="100px"
                                                               mode="aspectFill"
                                                               :src="ruleForm.customize.log_icon">
                                                    </app-image>
                                                    <el-button v-if="ruleForm.customize.log_icon != ''" class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="ruleForm.customize.log_icon = ''"></el-button>
                                                </div>
                                                <el-button size="mini" @click="ruleForm.customize.log_icon = ruleForm.customize.default_log_icon" class="reset" type="primary">恢复默认</el-button>
                                            </div>
                                        </el-form-item>
                                    </div>
                                    <el-button style="margin-top: 20px;" class="button-item" type="primary" :loading="btnLoading" @click="store('ruleForm')">保存</el-button>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                </el-tabs>
                <el-button v-if="activeName == 'first'" class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                           size="small">保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var checkTop = (rule, value, callback) => {
                if (!this.ruleForm.customize.top_bg) {
                    callback(new Error('请选择顶部背景图片'));
                } else {
                    callback();
                }
            };
            return {
                ruleForm: {
                    desc: [],
                    customize: {},
                    is_share: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    is_territorial_limitation: 0,
                    send_type: ['express', 'offline'],
                    payment_type: ['online_pay'],
                    rule: ''
                },
                rules: {
                    top_bg: [
                        {required: true, validator: checkTop, trigger: 'blur'},
                    ]
                },
                btnLoading: false,
                cardLoading: false,
                is_show: false,
                activeName: 'first',
                FormRules: {
                    is_cat: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_share: [
                        {required: true, message: '分销不能为空', trigger: 'blur'},
                    ],
                    is_sms: [
                        {required: true, message: '短信提醒不能为空', trigger: 'blur'},
                    ],
                    is_mail: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_print: [
                        {required: true, message: '显示分类不能为空', trigger: 'blur'},
                    ],
                    is_form: [
                        {required: true, message: '显示表单不能为空', trigger: 'blur'},
                    ]
                },
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
                    }
                ],
            };
        },
        computed: {
            send_type_list() {
                let list = [];
                for (let i in this.ruleForm.send_type) {
                    if (this.ruleForm.send_type[i] == 'express') {
                        list.push('快递配送');
                    }
                    if (this.ruleForm.send_type[i] == 'offline') {
                        list.push('到店自提');
                    }
                    if (this.ruleForm.send_type[i] == 'city') {
                        list.push('同城配送');
                    }
                }
                return list;
            }
        },
        watch: {
            ruleForm: {
                handler(data) {
                    if(this.$refs.ruleForm) {
                        this.$refs.ruleForm.clearValidate();
                    }
                    
                },
                deep: true,
                immediate: true
            }
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/integral_mall/mall/setting/index',
                    },
                }).then(e => {
                    this.cardLoading = false;
                    this.is_show = true;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.setting;
                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        let error = !self.ruleForm.customize.top_bg ? '请选择顶部背景图片' : '';
                        if(!self.ruleForm.customize.integral_text) {
                            error = "请选择'我的积分字体颜色'"
                        }else if(!self.ruleForm.customize.integral_num_text) {
                            error = "请选择'积分字体颜色'"
                        }else if(!self.ruleForm.customize.exchange_text) {
                            error = "请选择'我的兑换字体颜色'"
                        }else if(!self.ruleForm.customize.log_text) {
                            error = "请选择'积分明细字体颜色'"
                        }else if(!self.ruleForm.customize.about_icon) {
                            error = "请选择'我的积分说明图标'"
                        }else if(!self.ruleForm.customize.exchange_icon) {
                            error = "请选择'我的兑换图标'"
                        }else if(!self.ruleForm.customize.log_icon) {
                            error = "请选择'积分明细图标'"
                        }
                        if(self.ruleForm.customize.integral_text.indexOf('#') != 0 || self.ruleForm.customize.integral_text.length != 7 ||self.ruleForm.customize.integral_num_text.indexOf('#') != 0 || self.ruleForm.customize.integral_num_text.length != 7 || self.ruleForm.customize.exchange_text.indexOf('#') != 0 || self.ruleForm.customize.exchange_text.length != 7 || self.ruleForm.customize.log_text.indexOf('#') != 0 || self.ruleForm.customize.log_text.length != 7) {
                            error = "请选择正确的颜色"
                        }
                        if(error) {
                            self.$message.error(error);
                            return false;
                        }
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/integral_mall/mall/setting/index'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 添加权益
            addIntegralDesc() {
                this.ruleForm.desc.push({
                    title: '',
                    content: '',
                })
            },
            // 删除权益
            destroyIntegralDesc(index) {
                this.ruleForm.desc.splice(index, 1);
            }
        },

        mounted: function () {
            this.getDetail();
        }
    })
</script>