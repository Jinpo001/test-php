<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .login {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        position: relative;
    }

    .login .box-card {
        position: relative;
        border-radius: 15px;
        z-index: 99;
        border: 0;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .5);
        width: 380px;
        min-height: 400px;
        margin: 0 auto;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
        background-size: 100% 100%;
    }

    .logo {
        display: block; 
        border-radius: 50%;
        margin: 30px auto 10px;
        height: 78px;
        /* width: 120px; */
    }

    .username, password {
        margin-bottom: 20px;
    }

    .radio-box {
        height: 35px;
        line-height: 35px;
    }

    .register_box {
        position: absolute;
        right: 15%;
        bottom: 35px;
        width: 150px;
    }

    .register {
        display: inline-block;
        width: 48%;
        height: 15px;
        line-height: 15px;
        text-align: center;
        cursor: pointer;
        color: #4291ff;
    }

    .el-dialog {
        width: 35%;
    }

    .el-card__body {
        padding: 0;
    }

    .login-form {
        padding: 0 55px 30px;
        width: 380px;
        background-color: #fff;
    }

    .form-title {
        font-size: 20px;
        color: #353535;
        margin-bottom: 40px;
        text-align: center;
    }

    .opacity {
        background-color: rgba(0, 0, 0, 0.15);
        height: 100%;
        width: 100%;
        position: absolute;
        left: 0;
        top: 0;
        z-index: 1;
    }

    .el-input .el-input__inner {
        height: 40px;
        border-radius: 8px;
        background-color: #f7f5fb;
        border-color: #f7f5fb;
    }

    .foot {
        position: absolute;
        left: 0;
        right: 0;
        width: auto;
        color: #fff;
        text-align: center;
        font-size: 16px;
    }

    .foot a,
    .foot a:visited {
        color: #f3f3f3;
    }

    .footer-text {
        margin-bottom: 10px;
    }

    .login .login-form .el-input .el-input__inner {
        border-radius: 20px;
    }

    .pic-captcha {
        width: 100px;
        height: 36px;
        vertical-align: middle;
        cursor: pointer;
    }

    .login-btn.el-button--small, .el-button--small.is-round {
        margin-top: 20px;
        width: 100%;
        border-radius: 20px;
        height: 38px;
        font-size: 16px;
        background: linear-gradient(to right, #2E9FFF, #3E79FF);
        box-shadow: 0 4px 10px rgba(0, 123, 255, .5)
    }

    .foot {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 4%;
        width: auto;
        color: #fff;
        text-align: center;
        font-size: 16px;
    }

    .foot a,
    .foot a:visited {
        color: #f3f3f3;
        text-decoration: none;
    }
</style>

<div id="app" v-cloak>
    <div class="login" v-if="setting" :style="{'background-image':'url('+setting.background_image_url+')'}">
        <div class="opacity" flex="cross:center main:center">
            <el-card class="box-card" shadow="always">
                <img class="logo" :src="setting.logo_url" alt="">
                <el-form :model="ruleForm" class="login-form" :rules="rules2" ref="ruleForm" label-width="0"
                         size="small">
                    <div class="form-title">{{setting.name}}收银系统</div>
                    <el-form-item prop="username">
                        <el-input @keyup.enter.native="login('ruleForm')" placeholder="请输入用户名"
                                  v-model="ruleForm.username"></el-input>
                    </el-form-item>
                    <el-form-item prop="password">
                        <el-input @keyup.enter.native="login('ruleForm')" type="password" placeholder="请输入密码"
                                  v-model="ruleForm.password"></el-input>
                    </el-form-item>
                    <el-form-item prop="pic_captcha">
                        <el-input @keyup.enter.native="login('ruleForm')" placeholder="验证码"
                                  style="width: 165px"
                                  v-model="ruleForm.pic_captcha"></el-input>
                        <img :src="pic_captcha_src" class="pic-captcha" @click="loadPicCaptcha">
                    </el-form-item>
                    <el-form-item>
                        <el-button class="login-btn" :loading="btnLoading" round type="primary"
                                   @click="login('ruleForm')">登录
                        </el-button>
                    </el-form-item>
                </el-form>
                <div class="foot">
                    <a :href="setting.copyright_url" target="_blank">{{setting.copyright}}</a>
                </div>
            </el-card>
        </div>
    </div>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                pic_captcha_src: '',
                rules2: {
                    username: [
                        {required: true, message: '请输入用户名', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '请输入密码', trigger: 'blur'},
                    ],
                    pic_captcha: [
                        {required: true, message: '请输入右侧图片上的文字', trigger: 'blur'},
                    ],
                },
                btnLoading: false,
                setting: null,
                ruleForm: {}
            };
        },
        created() {
            this.getSetting();
            this.loadPicCaptcha();
            document.title = '收银系统';
        },
        methods: {
            loadPicCaptcha() {
                this.$request({
                    noHandleError: true,
                    params: {
                        r: 'site/pic-captcha',
                        refresh: true,
                    },
                }).then(response => {
                }).catch(response => {
                    if (response.data.url) {
                        this.pic_captcha_src = response.data.url;
                    }
                });
            },
            login(formName) {
                let self = this;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/teller/web/passport/login'
                            },
                            method: 'post',
                            data: {
                                username: this.ruleForm.username,
                                password: this.ruleForm.password,
                                pic_captcha: this.ruleForm.pic_captcha,
                                mall_id: getQuery('mall_id')
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                this.$navigate({
                                    r: 'plugin/teller/web/manage/index',
                                });
                            } else {
                                this.loadPicCaptcha();
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getSetting() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/teller/web/passport/setting',
                        mall_id: getQuery('mall_id')
                    },
                    method: 'get',
                }).then(e => {
                    if(e.data.code == 0) {
                        self.setting = e.data.data;
                        document.title = self.setting.name + `收银系统`;
                    }else {
                        if(!getQuery('mall_id')) {
                            setTimeout(()=>{
                                window.location.href="javascript:history.go(-1)";
                            },1000)
                        }else {
                            this.$message.error(e.data.msg);
                        }
                    }
                })
            }
        },
    });
</script>
