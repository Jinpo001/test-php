<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/4
 * Time: 4:19 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$baseUrl = Yii::$app->request->baseUrl;
$iconBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl('mobile') . '/img/';
?>
<style>
    .form_box {
        background-color: #fff;
        padding: 20px 40% 40px 75px;
        min-width: 900px;
    }

    .build-qr {
        position: relative;
        width: 386px;
        height: 186px;
    }

    .build-qr img {
        width: 100%;
        height: 100%;
    }

    .build-qr .build-qr-btn {
        position: absolute;
        left: 45px;
        top: 38px;
    }

    .build-qr-item {
        border-radius: 5px;
        border: 1px solid #e2e2e2;
        width: 386px;
        height: 407px;
        position: relative;
        font-size: 14px;
        padding-bottom: 75px;
        background-color: #fff;
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
        bottom: 66px;
        left: 0;
        width: 100%;
        z-index: 10;
    }
    .build-qr-item .build-update-btn {
        position: absolute;
        bottom: 22px;
        left: 0;
        width: 100%;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>基础配置</span>
        </div>
        <div class="build-qr" v-if="!qrcode">
            <img src="<?= $iconBaseUrl ?>bg.png" alt="">
            <div class="build-qr-btn">
                <div style="margin-bottom: 38px;">配置H5</div>
                <el-button size="small" type="primary" :loading="btnLoading" @click="issue">去配置</el-button>
            </div>
        </div>
        <div class="build-qr-item" flex="dir:top cross:center" v-else>
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
            <div class="build-update-btn" flex="dir:top cross:center">
                <div style="margin-bottom: 5px;">当前版本 {{version}}</div>
                <div style="margin-bottom: 50px;">最新版本 {{last_version}}</div>
                <el-button size="small" type="primary" :loading="btnLoading" @click="issue">发布新版本</el-button>
            </div>
        </div>
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
                cardLoading: false,
                btnLoading: false,
                qrcode: '',
                path: '',
                version: '',
                last_version: ''
            };
        },
        methods: {
            issue() {
                this.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/mobile/mall/config/issue',
                    },
                    method: 'get',
                }).then(e => {
                    this.btnLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                          message: '发布成功',
                          type: 'success'
                        });
                        this.qrcode = e.data.data.qrcode;
                        this.path = e.data.data.path;
                        this.version = e.data.data.version;
                        this.last_version = e.data.data.last_version;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mobile/mall/config/index',
                    },
                    method: 'get',
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.qrcode = e.data.data.qrcode;
                        this.path = e.data.data.path;
                        this.version = e.data.data.version;
                        this.last_version = e.data.data.last_version;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            }
        },
        created() {
            this.getDetail();
        }
    });
</script>