<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/29
 * Time: 4:08 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$baseUrl = Yii::$app->request->baseUrl;
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .form-body {
        padding: 20px 0 40px;
        background-color: #fff;
        margin-bottom: 10px;
        padding-right: 40%;
        min-width: 900px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .title {
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px !important;
    }

    .wechat-image {
        height: 232px;
        width: 200px;
        cursor: pointer;
        position: relative;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>其他设置</span>
        </div>
        <el-form @submit.native.prevent :model="form" :rules="rules" label-width="150px" ref="form">
            <div class="title">
                <span>注册协议设置</span>
            </div>
	        <div class="form-body">
                <el-form-item class="switch" label="协议标题" prop="agreement_name">
                    <el-input size="small" style="width: 590px;" placeholder="标题（最多输入30个字符）" v-model="form.agreement_name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item class="switch" label="协议内容" prop="agreement">
                    <app-rich-text style="width: 460px;" v-model="form.agreement"></app-rich-text>
                </el-form-item>
                <el-form-item class="switch" label="隐私标题" prop="declare_name">
                    <el-input size="small" style="width: 590px;" placeholder="标题（最多输入30个字符）" v-model="form.declare_name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item class="switch" label="隐私内容" prop="declare">
                    <app-rich-text style="width: 460px;" v-model="form.declare"></app-rich-text>
                </el-form-item>
	        </div>
            <div class="title">
                <span>客服设置</span>
            </div>
            <div class="form-body">
                <el-form-item label="客服微信" prop="list">
                    <el-button size="mini" @click="addWechat">选择</el-button>
                    <div flex="dir:left" style="flex-wrap:wrap">
                        <div v-for="(value,index) in form.list" style="margin-right: 24px;margin-top: 12px">
                            <div class="wechat-image" flex="dir:top"
                                 @click="editWechat(value,index)">
                                <el-image :src="value.qrcode_url" style="height: 200px;width:100%"></el-image>
                                <el-tooltip class="v" effect="dark" :content="'微信号'+ value.name" placement="top">
                                    <div class="wechat-end-box">微信号：{{value.name}}</div>
                                </el-tooltip>
                                <el-button class="del-btn" size="mini" type="danger"
                                           icon="el-icon-close" circle @click.stop="picClose(index)"></el-button>
                            </div>
                        </div>
                    </div>
                    <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                </el-form-item>
            </div>
	    </el-form>
        <el-button class='button-item' :loading="btnLoading" type="primary" @click="store('form')" size="small">保存</el-button>
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
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                wechatVisible: false,
                wechatForm: {
                    qrcode_url: '',
                    name: '',
                },
            	cardLoading: false,
            	btnLoading: false,
                index: -1,
                form: {
                    agreement_name: '',
                    agreement: '',
                    declare_name: '',
                    declare: '',
                    list: []
                },
                rules: {
                    agreement_name: [{required: true, message: '请填写注册协议标题'}],
                    agreement: [{required: true, message: '请填写注册协议内容'}],
                    declare_name: [{required: true, message: '请填写隐私协议标题'}],
                    declare: [{required: true, message: '请填写隐私协议内容'}],
                },
                wechatRules: {
                    qrcode_url: [
                        {required: true, message: '图片不能为空', trigger: 'blur'},
                    ]
                },
            };
        },
        created() {
            this.getDetail();
        },
        methods: {
            editWechat(item, index) {
                this.index = index;
                this.wechatForm = Object.assign({}, item);
                this.wechatVisible = true;
            },
            picClose(index) {
                this.form.list.splice(index, 1);
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
                            this.form.list.push(Object.assign({}, this.wechatForm));
                        } else {
                            this.form.list.splice(this.index, 1, this.wechatForm);
                        }
                        this.wechatVisible = false;
                    }
                });
            },

            addWechat() {
                this.index = -1;
                this.wechatForm = {
                    qrcode_url: '',
                    name: '',
                };
                this.wechatVisible = true
            },
            store(formName) {
                let self = this;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mobile/mall/config/register'
                            },
                            method: 'post',
                            data: {
                                agreement: self.form.agreement,
                                agreement_name: self.form.agreement_name,
                                declare: self.form.declare,
                                declare_name: self.form.declare_name,
                                list: self.form.list
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
                    }
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mobile/mall/config/register-data',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.form.agreement_name = e.data.data.agreement_name
                        self.form.agreement = e.data.data.agreement
                        self.form.declare_name = e.data.data.declare_name
                        self.form.declare = e.data.data.declare
                        self.form.list = e.data.data.list ? e.data.data.list : []
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
    });
</script>