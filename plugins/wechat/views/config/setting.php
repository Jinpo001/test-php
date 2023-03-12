<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
Yii::$app->loadViewComponent('app-wechat-info');
$baseUrl = Yii::$app->request->baseUrl;
$iconBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('wechat') . '/img/';
?>
<style>
    .form_box {
        background-color: #fff;
        padding: 30px 20px;
        padding-right: 40%;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .choose {
        margin-left: 10px;
        width: 364px;
        height: 93px;
        border-radius: 6px;
        background-color: #e1f0ff;
        color: #3a3a3a;
        position: relative;
    }

    .choose .left {
        width: 57px;
        height: 57px;
        background-color: #fff;
        border-radius: 8px;
        margin: 0 18px;
    }

    .choose .left img {
        height: 38px;
        width: 38px;
    }

    .choose .button-item {
        margin-top: 0;
        position: absolute;
        right: 23px;
    }

    .choose .button-item.issue {
        padding: 9px 18px;
    }

    .program-info {
        margin-left: 10px;
        width: 414px;
        border: 1px solid #e2e2e2;
        border-top: 0;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        padding: 25px 0;
    }

    .program-info img {
        height: 148px;
        width: 148px;
        margin: 0 auto 25px;
        display: block;
    }

    .program-info div .program-text {
        padding: 0 20px;
        color: #606266;
        margin-top: 10px;
    }

    .build-qr {
        position: relative;
        width: 296px;
        height: 102px;
        margin-left: 24px;
        margin-top: 32px;
    }

    .build-qr img {
        width: 100%;
        height: 100%;
    }

    .build-qr .build-qr-btn {
        position: absolute;
        right: 30px;
        top: 35px;
    }

    .build-qr-item {
        border-radius: 5px;
        border: 1px solid #e2e2e2;
        width: 386px;
        height: 347px;
        position: relative;
        margin-left: 24px;
        margin-top: 32px;
        font-size: 14px;
        padding-bottom: 25px;
    }

    .build-qr-item .build-qr-item-bg {
        width: 100%;
        height: 100%;
    }

    .build-qr-item .build-qr-item-qr {
        position: absolute;
        width: 165px;
        height: 165px;
        left: 50%;
        margin-left: -82.5px;
        top: 33px;
    }

    .build-qr-item .build-qr-item-info {
        position: absolute;
        bottom: 18px;
        left: 0;
        width: 100%;
    }
    .required-icon .el-form-item__label:before {
        content: '*';
        color: #F56C6C;
        margin-right: 4px;
    }
    .build-qr-item  .build-update-version {
        position: absolute;
        bottom: 56px;
        left: 0;
        width: 100%;
        text-align: center;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>基础设置</span>
        </div>
        <app-wechat-info :setting="ruleForm.is_setting"></app-wechat-info>
        <div class="form_box">
            <el-row>
                <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                    <el-col :span="12">
                        <el-form-item v-if="has_third_permission" label="是否使用第三方授权" prop="is_third">
                            <el-radio-group @change="qrLoading=false" :disabled="third && third.id > 0" v-model="is_third">
                                <el-radio :label="0">否</el-radio>
                                <el-radio :label="1">是</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item v-if="is_third == 0" label="公众号名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item v-if="is_third == 0" label="公众号头像" prop="logo" class="required-icon">
                            <app-attachment :multiple="false" :max="1" @selected="logoUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:100*100" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div style="margin: 10px 0;position: relative;width: 80px;">
                                <app-image width="80px"
                                           height="80px"
                                           mode="aspectFill"
                                           :src="ruleForm.logo">
                                </app-image>
                            </div>
                        </el-form-item>
                        <el-form-item v-if="is_third == 0" label="关注公众号二维码" prop="qrcode" class="required-icon">
                            <app-attachment :multiple="false" :max="1" @selected="qrcodeUrl">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:280*280" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                            <div style="margin: 10px 0;position: relative;width: 80px;">
                                <app-image width="80px"
                                           height="80px"
                                           mode="aspectFill"
                                           :src="ruleForm.qrcode">
                                </app-image>
                            </div>
                        </el-form-item>
                        <div v-if="is_third == 0">
                            <div class="build-qr-item" v-if="qrcode">
                                <img class="build-qr-item-bg" src="<?= $iconBaseUrl ?>qr-bg.png" alt="">
                                <img class="build-qr-item-qr" :src="qrcode" alt="">
                                <div flex="cross:center main:center" class="build-qr-item-info">
                                    <div>链接</div>
                                    <div id="path" style="max-width: 40%;margin: 0 20px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap">{{path}}</div>
                                    <el-tooltip style="cursor:pointer" effect="dark" content="复制链接" placement="top">
                                        <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                             data-clipboard-action="copy" data-clipboard-target="#path">
                                    </el-tooltip>
                                </div>
                                <div class="build-update-version">
                                    <div style="margin-bottom: 5px;">当前版本 {{version}}</div>
                                    <div>最新版本 {{last_version}}</div>
                                </div>
                            </div>
                        </div>
                        <div v-if="is_third == 1 && !third" class="choose" flex="dir:left cross:center">
                            <div class="left" flex="main:center cross:center">
                                <img src="<?= $iconBaseUrl ?>wait.png" alt="">
                            </div>
                            <div>选择已有公众号</div>
                            <el-button class='button-item' type="primary" @click="auth" size="small">去绑定</el-button>
                        </div>
                        <div v-if="is_third == 1 && third">
                            <div v-if="!qrcode" class="choose" flex="dir:left cross:center">
                                <div class="left" flex="main:center cross:center">
                                    <img src="<?= $iconBaseUrl ?>pass.png" alt="">
                                </div>
                                <div>公众号绑定成功</div>
                                <el-button class='button-item issue' :loading="qrLoading" type="primary" @click="issue" size="small">生成公众号版本</el-button>
                            </div>
                            <!-- 公众号信息 -->
                            <div class="build-qr-item" v-if="qrcode">
                                <img class="build-qr-item-bg" src="<?= $iconBaseUrl ?>qr-bg.png" alt="">
                                <img class="build-qr-item-qr" :src="qrcode" alt="">
                                <div flex="cross:center main:center" class="build-qr-item-info">
                                    <div>链接</div>
                                    <div id="path" style="max-width: 40%;margin: 0 20px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap">{{path}}</div>
                                    <el-tooltip style="cursor:pointer" effect="dark" content="复制链接" placement="top">
                                        <img class="copy-btn" src="statics/img/mall/copy.png" alt=""
                                             data-clipboard-action="copy" data-clipboard-target="#path">
                                    </el-tooltip>
                                </div>
                                <div class="build-update-version">
                                    <div style="margin-bottom: 5px;">当前版本 {{version}}</div>
                                    <div>最新版本 {{last_version}}</div>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </el-form>
            </el-row>
        </div>
        <el-button v-if="activeName != 'first' || is_third == 0" class='button-item' :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">发布新版本</el-button>
        <el-button v-else class='button-item' :loading="qrLoading" type="primary" @click="getbuildQr" size="small">发布新版本</el-button>
    </el-card>
</div>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/js/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.copy-btn');

    var self = this;
    clipboard.on('success', function (e) {
        self.ELEMENT.Message.success('复制成功');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        self.ELEMENT.Message.success('复制失败，请手动复制');
    });
    const app = new Vue({
        el: '#app',
        data() {
            return {
                third: null, // 版本信息
                is_third: 0, //是否开启第三方授权
                has_third_permission: false,
                activeName: 'first',
                qrLoading: false,
                qrcode: '',
                path: '',
                version: '',
                last_version: '',
                hidden: {
                    appid: true,
                    appsecret: true,
                },
                ruleForm: {
                    is_setting: false,
                    name: '',
                    logo: '',
                    qrcode: ''
                },
                rules: {
                    name: [
                        {required: true, message: '请输入公众号名称', trigger: 'change'},
                    ]
                },
                btnLoading: false,
                cardLoading: false,
                app_qrcode_loading: false,
                app_qrcode: null
            };
        },
        methods: {
            logoUrl(e) {
                this.ruleForm.logo = e[0].url;
                this.$forceUpdate();
            },
            qrcodeUrl(e) {
                this.ruleForm.qrcode = e[0].url;
                this.$forceUpdate();
            },
            // 授权弹窗
            auth() {
                window.open('<?=$baseUrl?>/index.php?r=plugin/wechat/mall/third-platform/authorizer');
                this.$confirm('请在新窗口中完成微信公众号授权', '提示', {
                    confirmButtonText: '已成功授权',
                    cancelButtonText: '授权失败，重试',
                    type: 'warning'
                }).then(() => {
                    this.$message({
                        type: 'success',
                        message: '已完成授权!'
                    });
                    this.getDetail();
                }).catch(() => {
                });
            },
            issue() {
                this.qrLoading = true;
                request({
                    params: {
                        r: 'plugin/wechat/mall/config/issue',
                    },
                    method: 'get',
                }).then(e => {
                    this.qrLoading = false;
                    if (e.data.code == 0) {
                        this.qrcode = e.data.data.qrcode;
                        this.version = e.data.data.version;
                        this.path = e.data.data.path;
                        this.last_version = e.data.data.last_version;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            getbuildQr() {
                this.qrLoading = true;
                request({
                    params: {
                        r: 'plugin/wechat/mall/config/index',
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.qrcode = e.data.data.qrcode;
                        this.version = e.data.data.version;
                        this.path = e.data.data.path;
                        this.last_version = e.data.data.last_version;
                        this.issue();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            store(formName) {
                let self = this;
                if(!self.ruleForm.logo) {
                    this.$message.error('请选择公众号头像');
                    return false;
                }
                if(!self.ruleForm.qrcode) {
                    this.$message.error('请选择关注公众号二维码');
                    return false;
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/wechat/mall/config/setting'
                            },
                            method: 'post',
                            data: {
                                name: self.ruleForm.name,
                                logo: self.ruleForm.logo,
                                qrcode: self.ruleForm.qrcode,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.getbuildQr();
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
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/wechat/mall/config/setting'
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    self.has_third_permission = e.data.data.has_third_permission;
                    self.third = e.data.data.third;
                    self.path = e.data.data.path;
                    self.version = e.data.data.version;
                    self.last_version = e.data.data.last_version;
                    self.qrcode = e.data.data.qrcode;
                    if(!self.has_third_permission) {
                        self.is_third = 0;
                    }
                    if(self.third && self.has_third_permission) {
                        self.is_third = 1;
                    }
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                        self.ruleForm.is_setting = e.data.data.is_setting;
                    } else {
                        self.$message.error(e.data.msg);
                        self.rules.service_key[0].required = true;
                    }
                }).catch(e => {
                    console.log(e);
                });
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
