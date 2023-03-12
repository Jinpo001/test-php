<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
$baseUrl = Yii::$app->request->baseUrl;
?>
<style>
    .form_box {
        margin-top: 10px;
        background-color: #fff;
    }

    .form_box_box {
        padding: 30px 20px;
    }

    .form_box_qx {
        padding: 24px 20px 30px;
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
        margin-left: 50px;
    }

    .version {
        width: 414px;
        height: 93px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        background-image: linear-gradient(to right, #409eff, #40b9ff);
        color: #fff;
        margin-left: 10px;
        position: relative;
    }

    .version .left {
        width: 57px;
        height: 57px;
        background-color: #fff;
        border-radius: 8px;
        margin: 0 18px;
    }

    .version .setting-url {
        position: absolute;
        right: 24px;
        top: 30px;
        height: 24px;
        width: 104px;
        line-height: 24px;
        text-align: center;
        border-radius: 12px;
        color: #fff;
        border:  1px solid #fff;
        cursor: pointer;
    }

    .version-big {
        font-size: 16px;
        margin-bottom: 5px;
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

    .program-info .program-tip div {
        color: #606266;
        height: 50px;
        line-height: 30px;
    }

    .program-info .program-tip .number {
        width: 30px;
        height: 30px;
        text-align: center;
        border-radius: 50%;
        margin-right: 22px;
        color: #409eff;
        background-color: #e1f0ff;
        margin-left: 22px;
    }

    .program-info .choose-version {
        margin: 10px 20px;
    }

    .program-info .choose-version>div:first-of-type {
        margin-right: 20px;
    }

    .program-info .button {
        width: 372px;
        text-align: center;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #409eff;
        margin-left: 20px;
        margin-top: 15px;
        cursor: pointer;
        background-color: #409eff;
        color: #fff;
    }

    .program-info .to-test {
        margin-top: 30px;
        color: #409eff;
        background-color: #fff;
    }

    .el-dialog {
        min-width: 360px;
    }

    .add-btn.el-button.is-plain {
        border-color: #409EFF;
        color: #409EFF;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <el-tabs v-model="activeName">
            <el-tab-pane label="小程序配置" name="first">
                <div class="form_box" :class="{'form_box_box': !has_fast_create_wxapp_permission}">
                    <div v-if="has_fast_create_wxapp_permission" flex="dir:left cross:center"
                         style="background-color: #e1f0ff;padding: 10px 24px">
                        <div style="color: #409EFF;padding-right: 24px">尚未注册小程序？ 点此快速注册小程序</div>
                        <el-button type="primary" size="small"
                                   @click="$navigate({r:'plugin/wxapp/third-platform/fast-create'})">立即注册
                        </el-button>
                    </div>
                    <el-row :class="{'form_box_qx' : has_fast_create_wxapp_permission}">
                        <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm1" label-width="150px">
                            <el-col :span="12">
                                <el-form-item v-if="has_third_permission" label="是否使用第三方授权" prop="is_third">
                                    <el-radio-group :disabled="third && third.id > 0" v-model="is_third">
                                        <el-radio :label="0">否</el-radio>
                                        <el-radio :label="1">是</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item v-if="is_third == 0" label="小程序AppId" prop="appid">
                                    <el-input v-model.trim="ruleForm.appid"></el-input>
                                </el-form-item>
                                <el-form-item v-if="is_third == 0" label="小程序appSecret" prop="appsecret">
                                    <el-input @focus="hidden.appsecret = false"
                                              v-if="hidden.appsecret"
                                              readonly
                                              placeholder="已隐藏内容，点击查看或编辑">
                                    </el-input>
                                    <el-input v-else v-model.trim="ruleForm.appsecret"></el-input>
                                </el-form-item>
                                <div v-if="is_third == 1 && !third" class="choose" flex="dir:left cross:center">
                                    <div class="left" flex="main:center cross:center">
                                        <img src="statics/img/mall/mini-program.png" alt="">
                                    </div>
                                    <div>选择已有小程序</div>
                                    <el-button class='button-item' type="primary" @click="auth" size="small">立即授权</el-button>
                                </div>
                                <div v-if="is_third == 1 && third && !have_version" class="choose" flex="dir:left cross:center">
                                    <div class="left" flex="main:center cross:center">
                                        <img src="statics/img/mall/mini-program.png" alt="">
                                    </div>
                                    <div>等待平台发布版本</div>
                                </div>
                                <div v-if="is_third == 1 && third && have_version">
                                    <div class="version" flex="dir:left cross:center">
                                        <div class="left" flex="main:center cross:center">
                                            <img src="statics/img/mall/mini-program.png" alt="">
                                        </div>
                                        <div>
                                            <div class="version-big">线上版本</div>
                                            <div>版本号：{{releaseVersion ? releaseVersion.version : '-'}}</div>
                                        </div>
                                        <div @click="urlVisible=true;" class="setting-url">配置业务域名</div>
                                    </div>
                                    <div class="program-info" v-if="!is_pass">
                                        <div class="program-tip">
                                            <div flex="dir:left">
                                                <div class="number">1</div>
                                                <div>提交审核版本一般需要1-7天，请耐心等待</div>
                                            </div>
                                            <div flex="dir:left">
                                                <div class="number">2</div>
                                                <div>审核通过后自动发布小程序</div>
                                            </div>
                                        </div>
                                        <div class="choose-version" flex="dir:left">
                                            <div>选择版本</div>
                                            <div>
                                                <div style="margin-bottom: 15px">
                                                    <el-radio v-model="is_plugin" :label="0">基础({{template.user_version}})</el-radio>
                                                </div>
                                                <div>
                                                    <el-radio v-model="is_plugin" :label="1">基础含直播({{template.user_version}})</el-radio>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="line-height: 38px;" class="to-test button" @click="toUpVersion">生成体验版本</div>
                                        <el-button @click="toSubmitReview" :loading="submitLoading" v-if="check_status == -1 || check_status == 3" class="button">提交审核</el-button>
                                        <el-button @click="unDoCodeAudit" :loading="submitLoading" v-if="check_status == 2" class="button">审核中，撤回审核</el-button>
                                        <el-button @click="toSubmitReview" :loading="submitLoading" v-if="check_status == 1" class="button">审核失败，再次提交审核</el-button>
                                        <el-button @click="release" :loading="submitLoading" v-if="check_status == 0" class="button">审核通过，立即发布</el-button>
                                        <div v-if="check_status == 1 && errorMsg" style="color: #ff4544;margin: 8px 20px">失败原因：
                                            <span v-html="errorMsg"></span>
                                        </div>
                                    </div>
                                    <!-- 小程序信息 -->
                                    <div v-loading="app_qrcode_loading" class="program-info" v-if="is_pass">
                                        <img :src="app_qrcode">
                                        <div>
                                            <div class="program-text">小程序名称：{{third.nick_name}}</div>
                                            <!-- <div class="program-text">模版名称：双十一预售</div> -->
                                            <div class="program-text">版本生成时间：{{releaseVersion.created_at}}</div>
                                            <div class="program-text">发布时间 ：{{releaseVersion.release_at}}</div>
                                            <el-button @click="is_pass = false;check_status = -1"
                                                       :loading="submitLoading" v-if="is_new" class="button">发现新版本，去更新
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </el-col>
                        </el-form>
                    </el-row>
                </div>
            </el-tab-pane>
            <el-tab-pane v-if="false" label="支付配置" name="second">
                <div class="form_box form_box_box">
                    <el-row>
                        <el-form :model="ruleForm" :rules="other_rules" size="small" ref="ruleForm2"
                                 label-width="150px">
                            <el-col :span="12">
                                <el-form-item label="支付类型选择" prop="is_choise">
                                    <el-radio-group v-model="ruleForm.is_choise">
                                        <el-radio :label="0">普通商户</el-radio>
                                        <el-radio :label="1">服务商</el-radio>
                                    </el-radio-group>
                                </el-form-item>

                                <el-form-item label="微信支付商户号" prop="mchid" v-if="ruleForm.is_choise == 0">
                                    <el-input v-model.trim="ruleForm.mchid"></el-input>
                                </el-form-item>

                                <el-form-item label="特约商户商户号" prop="mchid" v-if="ruleForm.is_choise == 1">
                                    <el-input v-model.trim="ruleForm.mchid"></el-input>
                                </el-form-item>

                                <template v-if="ruleForm.is_choise != 1">
                                    <el-form-item label="微信支付Api密钥" prop="key">
                                        <el-input @focus="hidden.key = false"
                                                  v-if="hidden.key"
                                                  readonly
                                                  placeholder="已隐藏内容，点击查看或编辑">
                                        </el-input>
                                        <el-input v-else v-model.trim="ruleForm.key"></el-input>
                                    </el-form-item>
                                    <el-form-item label="微信支付apiclient_cert.pem" prop="cert_pem">
                                        <el-input @focus="hidden.cert_pem = false"
                                                  v-if="hidden.cert_pem"
                                                  readonly
                                                  type="textarea"
                                                  :rows="5"
                                                  placeholder="已隐藏内容，点击查看或编辑">
                                        </el-input>
                                        <el-input v-else type="textarea" :rows="5" v-model="ruleForm.cert_pem"></el-input>
                                    </el-form-item>
                                    <el-form-item label="微信支付apiclient_key.pem" prop="key_pem">
                                        <el-input @focus="hidden.key_pem = false"
                                                  v-if="hidden.key_pem"
                                                  readonly
                                                  type="textarea"
                                                  :rows="5"
                                                  placeholder="已隐藏内容，点击查看或编辑">
                                        </el-input>
                                        <el-input v-else type="textarea" :rows="5" v-model="ruleForm.key_pem"></el-input>
                                    </el-form-item>
                                </template>
                                <template v-else>
                                    <el-form-item label="服务商AppId" prop="service_appid">
                                        <el-input v-model.trim="ruleForm.service_appid"></el-input>
                                    </el-form-item>
                                    <el-form-item label="服务商商户号" prop="service_mchid">
                                        <el-input v-model.trim="ruleForm.service_mchid"></el-input>
                                    </el-form-item>
                                    <el-form-item label="微信支付服务商Api密钥" prop="service_key">
                                        <el-input @focus="hidden.key = false"
                                                  v-if="hidden.key"
                                                  readonly
                                                  placeholder="已隐藏内容，点击编辑">
                                        </el-input>
                                        <el-input v-else v-model.trim="ruleForm.service_key"></el-input>
                                    </el-form-item>
                                    <el-form-item label="微信支付服务商apiclient_cert.pem">
                                        <app-upload @complete="updateSuccess" accept="" :params="params_cert"
                                                    :simple="true" style="display: inline-block">
                                            <el-button size="small">上传文件</el-button>
                                        </app-upload>
                                    </el-form-item>
                                    <el-form-item label="微信支付服务商apiclient_key.pem">
                                        <app-upload @complete="updateSuccess" accept="" :params="params_key"
                                                    :simple="true" style="display: inline-block">
                                            <el-button size="small">上传文件</el-button>
                                        </app-upload>
                                    </el-form-item>
                                </template>
                            </el-col>
                        </el-form>
                    </el-row>
                </div>
            </el-tab-pane>
        </el-tabs>
        <el-dialog title="体验版本小程序码" :visible.sync="dialogVisible" :close-on-click-modal="false" width="360px">
            <div v-loading="previewLoading">
                <img id="code" style="display: block;margin: 15px auto 50px" width="180" height="180" alt="">
            </div>
        </el-dialog>
        <el-dialog title="配置业务域名" :visible.sync="urlVisible" width="785px">
            <div style="padding-left: 30px;">
                <div style="max-height: 400px;overflow: auto;">
                    <el-form size="small" label-width="120px" label-position="left">
                        <el-form-item v-for="(item,index) in domain" :key="index">
                            <label slot="label">配置业务域名
                                <el-tooltip class="item" effect="dark"
                                            :content="'以https://开头，例:https://' + host"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <div flex="dir:left cross:center">
                                <el-input style="width: 520px;margin-right: 20px;" v-model="domain[index]"></el-input>
                                <el-tooltip effect="dark" content="删除" placement="top">
                                    <img style="cursor: pointer;" @click="delDomain(index)" src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </div>
                        </el-form-item>
                    </el-form>
                </div>
                <div style="color: #999999;margin: 20px 0 10px;">最多添加20条域名</div>
                <el-button v-if="domain.length < 20" class="add-btn" @click="addUrl" size="small" plain>+添加域名</el-button>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="cancel">取 消</el-button>
                <el-button size="small" type="primary" :loading="urlLoading" @click="submitUrl">确 定</el-button>
            </span>
        </el-dialog>
        <el-button v-if="activeName != 'first' || is_third == 0" class='button-item' :loading="btnLoading" type="primary" @click="store('ruleForm1','ruleForm2')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                has_fast_create_wxapp_permission: false,
                host: '',
                is_pass: false, // 小程序是否通过审核
                is_plugin: 0, // 小程序版本是否包含直播 0 基础 1 含直播
                check_status: -1, // 小程序审核状态 -1 未审核 0 审核中 1 审核通过 2 审核失败
                errorMsg: '', // 审核失败原因
                is_new: null, // 是否有新版本
                have_version: false, //后台是否上传版本
                third: null, // 版本信息
                is_third: 0, //是否开启第三方授权
                releaseVersion: null, // 发布的版本信息
                template: null, // 后台最新上传版本
                template_list: [], //后台上传版本列表
                has_third_permission: false,
                activeName: 'first',
                dialogVisible: false,
                urlVisible: false,
                previewLoading: false,
                submitLoading: false,
                urlLoading: false,
                params_key: {
                    r: 'plugin/wxapp/wx-app-config/upload-pem',
                    type: 'key',
                },
                params_cert: {
                    r: 'plugin/wxapp/wx-app-config/upload-pem',
                    type: 'cert',
                },
                domain: [''],
                hidden: {
                    appid: true,
                    appsecret: true,
                    mchid: false,
                    key: true,
                    cert_pem: true,
                    key_pem: true,
                    service_key: true
                },
                ruleForm: {
                    appid: '',
                    appsecret: '',
                    cert_pem: '',
                    key: '',
                    key_pem: '',
                    mchid: '',
                    is_choise: '',
                    service_appid: '',
                    service_mchid: '',
                    service_key: ''
                },
                rules: {
                    appid: [
                        {required: true, message: '请输入appid', trigger: 'change'},
                    ],
                    appsecret: [
                        {required: true, message: '请输入appsecret', trigger: 'change'},
                    ]
                },
                other_rules: {
                    key: [
                        {required: true, message: '请输入key', trigger: 'change'},
                        {max: 32, message: '微信支付Api密钥最多为32个字符', trigger: 'change'},
                    ],
                    is_choise: [
                        {required: true, message: '请选择类型', trigger: 'change'},
                    ],
                    mchid: [
                        {required: true, message: '请输入mchid', trigger: 'change'},
                    ],
                    service_appid: [
                        {required: true, message: '请输入服务商appid', trigger: 'change'},
                    ],
                    service_mchid: [
                        {required: true, message: '请输入服务商mchid', trigger: 'change'},
                    ],
                    service_key: [
                        {required: false, message: '请输入key', trigger: 'change'},
                        {max: 32, message: '微信支付服务商Api密钥最多为32个字符', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
                app_qrcode_loading: false,
                app_qrcode: null
            };
        },
        methods: {
            delDomain(index) {
                this.domain.splice(index,1)
            },
            addUrl() {
                this.domain.push('')
            },
            cancel() {
                this.domain = JSON.parse(JSON.stringify(this.third.domain));
                this.urlVisible = false;
            },
            submitUrl() {
                for(let item of this.domain) {
                    const reg = /(https):\/\/([\w.]+\/?)\S*/
                    if (!reg.test(item)) {
                        this.$message.error('请输入正确的网址');
                        return false
                    }
                }
                this.urlLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/third-platform/business-domain',
                    },
                    data: {
                        domain:this.domain
                    },
                    method: 'post'
                }).then(e => {
                    this.urlLoading = false;
                    if (e.data.code === 0) {
                        this.$message({
                          message: e.data.msg,
                          type: 'success'
                        });
                        this.urlVisible = false;
                    } else {
                        this.$alert(e.data.msg, '提示');
                    }
                }).catch(e => {
                    this.submitLoading = false;
                })
            },
            getQr() {
                this.app_qrcode_loading = true;
                this.$request({
                    params: {
                        r: 'plugin/wxapp/app-upload/app-qrcode',
                    },
                }).then(e => {
                    this.app_qrcode_loading = false;
                    if (e.data.code === 0) {
                        this.app_qrcode = e.data.data.qrcode;
                    } else {
                        this.$alert(e.data.msg, '提示');
                    }
                }).catch(e => {
                    this.app_qrcode_loading = false;
                })
            },
            // 授权弹窗
            auth() {
                window.open('<?=$baseUrl?>/index.php?r=plugin/wxapp/third-platform/authorizer');
                this.$confirm('请在新窗口中完成微信小程序授权', '提示', {
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
            // 提交审核
            toSubmitReview() {
                let self = this;
                self.submitLoading = true;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/upload',
                    },
                    data: {
                        template_id: this.template.template_id,
                        is_plugin: this.is_plugin
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code == 0) {
                        request({
                            params: {
                                r: 'plugin/wxapp/third-platform/submit-review',
                            },
                            data: {
                                template_id: this.template.template_id,
                                version: this.template.user_version
                            },
                            method: 'post',
                        }).then(e => {
                            self.submitLoading = false;
                            if (e.data.code == 0) {
                                this.$message({
                                  message: e.data.msg,
                                  type: 'success'
                                });
                                this.checkSubmit();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        })
                    }else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            // 审核结果查询
            checkSubmit() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/get-last-audit',
                    },
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.check_status = e.data.data.status;
                        self.releaseVersion = e.data.data.last;
                        if(e.data.data.status == 1) {
                            self.errorMsg = e.data.data.reason;
                        }
                        if(e.data.data.last) {
                            self.have_version = true;
                            self.getQr();
                            if(e.data.data.last.version == e.data.data.audit.version) {
                                self.is_pass = true;
                            }
                            let nowVersion = e.data.data.last.version.split(".");
                            let newVersion = self.template.user_version.split(".");
                            for(let i = 0;i < newVersion.length;i++) {
                                if(+newVersion[i] > +nowVersion[i]) {
                                    self.is_new = true;
                                    break;
                                }
                            }
                        }
                    }
                })
            },
            // 撤回审核
            unDoCodeAudit() {
                let self = this;
                self.$confirm('单个帐号每天审核撤回次数最多不超过 1 次(每天的额度从0点开始生效)，一个月不超过 10 次, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.submitLoading = true;
                    request({
                        params: {
                            r: 'plugin/wxapp/third-platform/un-do-code-audit',
                        },
                    }).then(e => {
                        self.submitLoading = false;
                        if (e.data.code == 0) {
                            self.$message({
                              message: e.data.msg,
                              type: 'success'
                            });
                            self.check_status = -1;
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    })

                })
            },
            // 提交代码
            release() {
                let self = this;
                self.submitLoading = true;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/release',
                    },
                }).then(e => {
                    self.submitLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                          message: e.data.msg,
                          type: 'success'
                        });
                        this.getDetail();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            // 预览
            preview() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/preview',
                    },
                    responseType: 'blob',
                    method: 'get',
                }).then(e => {
                    self.previewLoading = false;
                    var blob = e.data;
                    var url = window.URL.createObjectURL(blob);
                    document.getElementById("code").src = url
                }).catch(e => {
                    self.previewLoading = false;
                    var blob = e.data;
                    var url = window.URL.createObjectURL(blob);
                    document.getElementById("code").src = url
                });
            },
            // 上传
            toUpVersion() {
                let self = this;
                self.previewLoading = true;
                self.dialogVisible = true;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/upload',
                    },
                    data: {
                        template_id: this.template.template_id,
                        is_plugin: this.is_plugin
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.preview();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取版本
            getVersion() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/template-list',
                    },
                    method: 'get',
                }).then(e => {
                    this.checkSubmit();
                    if (e.data.code == 0) {
                        if(e.data.data.list.template_list.length > 0) {
                            self.template_list = e.data.data.list.template_list;
                            self.template = e.data.data.list.template_list.pop();
                            self.have_version = true;
                        }else {
                            self.cardLoading = false;
                        }
                    } else {
                        self.cardLoading = false;
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            store(formName1, formName2) {
                console.log(formName1, formName2);
                let self = this;
                this.$refs[formName1].validate((valid) => {
                    if (valid) {
                        //this.$refs[formName2].validate((valid) => {
                        //    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/wxapp/wx-app-config/setting'
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
                        //     } else {
                        //         this.activeName = 'second';
                        //         console.log('error submit!!');
                        //         return false;
                        //     }
                        // });
                    } else {
                        this.activeName = 'first';
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
                        r: 'plugin/wxapp/wx-app-config/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    self.has_third_permission = e.data.data.has_third_permission;
                    self.has_fast_create_wxapp_permission = e.data.data.has_fast_create_wxapp_permission;
                    self.third = e.data.data.third;
                    if(!self.has_third_permission) {
                        self.is_third = 0;
                    }
                    if(self.third && self.has_third_permission) {
                        self.is_third = 1;
                        this.getVersion();
                    }
                    if (e.data.code == 0) {
                        if(e.data.data.third) {
                            self.domain = JSON.parse(JSON.stringify(e.data.data.third.domain));
                        }
                        self.ruleForm = e.data.data.detail;
                        self.params_key.id = e.data.data.detail.id;
                        self.params_cert.id = e.data.data.detail.id;
                    } else {
                        self.$message.error(e.data.msg);
                        self.rules.service_key[0].required = true;
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            updateSuccess(e) {
                if (e[0].response.data.code == 0) {
                    this.$message.success('上传成功')
                }
            }
        },
        mounted: function () {
            this.getDetail();
            this.host = window.location.host;
        }
    });
</script>
