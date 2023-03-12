<?php
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$mallUrl = Yii::$app->request->hostInfo
    . Yii::$app->request->baseUrl
    . '/statics/img/app';
?>
<style>
    .diy-component-edit .c-input {

    }

    .diy-component-edit .c-input-big {
        width: 90px;
        margin-right: 25px;
    }


    .diy-component-edit .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .wechat-end-box {
        overflow: hidden;
        word-break: break-all;
        text-align: justify;
        height: 32px;
        line-height: 32px;
        width: 200px;
        padding: 0 12px;
        color: #606266;
        border-left: 1px solid #e2e2e2;
        border-right: 1px solid #e2e2e2;
        border-bottom: 1px solid #e2e2e2;
    }

    .diy-component-edit .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .diy-component-edit .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

</style>
<template id="diy-customer">
    <div>
        <div class="diy-component-preview" :style="styleA">
            <div :style="styleB" v-if="data.select_style == 1"
                 class="diy-customer" flex="dir:top cross:center">
                <div :style="{color: data.title_color}" style="font-size:34px;margin: 32px 0">{{data.title}}</div>
                <template v-if="data.wechat && data.wechat.length">
                    <app-image :src="data.wechat[0].qrcode"
                               style="height: 360px;width: 360px;display: block"></app-image>
                    <div :style="{color:data.wechat_color}" style="margin-top: 32px;font-size: 28px">
                        微信号：{{data.wechat[0].name}}
                    </div>
                </template>
                <app-image v-else style="height: 360px;width: 360px;display: block" :src="defaultImage"></app-image>
                <div flex="dir:left" style="margin-top: 24px;margin-bottom: 32px">
                    <div :style="{borderRadius: `${data.save_radius}px`, background: `${data.save_bg}`,borderColor: `${data.save_border ? data.save_border: data.save_bg}`,color: `${data.save_color}`}"
                         style="border-width: 1px;border-style: solid;padding: 20px 22px;font-size: 24px">
                        {{data.save_title}}
                    </div>
                    <div style="width: 40px"></div>
                    <div :style="{borderRadius: `${data.copy_radius}px`, background: `${data.copy_bg}`,borderColor: `${data.copy_border ? data.copy_border : data.copy_bg}`,color: `${data.copy_color}`}"
                         style="border-width: 1px;border-style: solid;padding: 20px 22px;font-size: 24px">
                        {{data.copy_title}}
                    </div>
                </div>
            </div>
            <div :style="styleB" v-else-if="data.select_style == 2" flex="dir:left cross:center"
                 style="padding: 20px"
            >
                <app-image style="height: 100px;width: 100px;flex-shrink: 0" :src="data.two_icon"></app-image>
                <div flex="dir:top" style="margin-left: 20px;flex-shrink: 1">
                    <div :style="{color: data.title_color}" style="font-size:28px;">{{data.title}}</div>
                    <div :style="{color: data.sub_title_color}" style="font-size:24px">{{data.sub_title}}</div>
                </div>
                <div style="margin-left:auto;flex-shrink: 0">
                    <div :style="{borderRadius: `${data.two_radius}px`, background: `${data.two_bg}`,borderColor: `${data.two_border ? data.two_border: data.two_bg}`,color: `${data.two_color}`}"
                         style="border-width: 1px;border-style: solid;padding: 14px 28px 16px;font-size: 24px">
                        {{data.two_title}}
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="120px" size="small" @submit.native.prevent>
                <el-form-item label="选择样式">
                    <app-radio v-model="data.select_style" label="1">样式一</app-radio>
                    <app-radio v-model="data.select_style" label="2">样式二</app-radio>
                </el-form-item>

                <el-form-item label="标题"
                              :rules="[{ required: true, validator: validate, trigger: 'change'}]">
                    <el-input size="small" maxlength="15" v-model="data.title" class="c-input"
                              show-word-limit></el-input>
                </el-form-item>
                <el-form-item label="标题文字颜色">
                    <el-color-picker @change="(row) => {row == null ? data.title_color = '#353535' : ''}"
                                     size="small"
                                     v-model="data.title_color"></el-color-picker>
                    <el-input size="small" class="c-input-big"
                              v-model="data.title_color"></el-input>
                </el-form-item>

                <template v-if="data.select_style == 2">
                    <el-form-item label="副标题"
                                  :rules="[{ required: true, validator: validate, trigger: 'change'}]">
                        <el-input size="small" maxlength="15" v-model="data.sub_title" class="c-input"
                                  show-word-limit></el-input>
                    </el-form-item>
                    <el-form-item label="副标题文字颜色">
                        <el-color-picker @change="(row) => {row == null ? data.sub_title_color = '#999999' : ''}"
                                         size="small"
                                         v-model="data.sub_title_color"></el-color-picker>
                        <el-input size="small" class="c-input-big"
                                  v-model="data.sub_title_color"></el-input>
                    </el-form-item>

                    <el-form-item label="图标">
                        <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                        @selected="twoIconSelect">
                            <el-tooltip effect="dark"
                                        content="建议尺寸:100 * 100"
                                        placement="top">
                                <el-button size="mini">选择图标</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1"
                                            @selected="twoIconSelect">
                                <app-image mode="aspectFill"
                                           width="80px"
                                           height='80px'
                                           :src="data.two_icon">
                                </app-image>
                            </app-attachment>
                            <el-button v-if="data.two_icon" class="del-btn"
                                       size="mini" type="danger" icon="el-icon-close"
                                       circle
                                       @click="twoIconDel"></el-button>
                        </div>
                        <el-button size="mini" @click="resetImg('two_icon')" class="reset" type="primary">恢复默认
                        </el-button>
                    </el-form-item>
                </template>

                <el-form-item label="客服微信" prop="wechat"
                              :rules="[{ required: true, validator: validate, trigger: 'change'}]">
                    <el-tooltip effect="dark"
                                content="建议尺寸:200 * 200"
                                placement="top">
                        <el-button size="mini" @click="wechatAdd"
                                   :disabled="data.wechat.length >= 10">选择
                        </el-button>
                    </el-tooltip>

                    <div flex="dir:left cross:center" style="flex-wrap: wrap;margin-top: 12px">
                        <div v-for="(item,index) of data.wechat" @click="wechatEdit(item,index)"
                             style="margin:0 5px 5px;cursor: pointer;flex-shrink: 0;position: relative">
                            <div>
                                <app-image mode="aspectFill"
                                           width="200px"
                                           height='200px'
                                           :src="item['qrcode']"
                                ></app-image>
                                <div class="wechat-end-box" style="padding-left: 12px" v-text="`微信号： ${item.name}`">
                                </div>
                            </div>

                            <el-button class="del-btn"
                                       size="mini" type="danger" icon="el-icon-close"
                                       circle
                                       @click.stop="wechatDel(index)"
                            ></el-button>
                        </div>
                    </div>
                    <div style="color:#909399">注意：最多允许上传10张，前端随机展示一张</div>
                </el-form-item>
                <el-form-item v-if="data.select_style == 1" label="微信号文字颜色">
                    <el-color-picker @change="(row) => {row == null ? data.wechat_color = '#999999' : ''}"
                                     size="small"
                                     v-model="data.wechat_color"></el-color-picker>
                    <el-input size="small" class="c-input-big"
                              v-model="data.wechat_color"></el-input>
                </el-form-item>

                <!-------->
                <template v-if="data.select_style == 2">
                    <el-form-item label="按钮样式设置"></el-form-item>
                    <el-form-item label="按钮圆角" prop="two_radius">
                        <div flex="dir:left">
                            <el-slider style="width: 50%;margin-right: 20px" input-size="mini"
                                       v-model="data.two_radius"
                                       :max="40" :min="0"
                                       :show-tooltip="false"></el-slider>
                            <el-input-number v-model="data.two_radius" :min="0"
                                             :max="40" label="按钮圆角"></el-input-number>
                            <div style="margin-left: 10px">px</div>
                        </div>
                    </el-form-item>
                    <el-form-item label="按钮文本">
                        <el-input size="small" v-model="data.two_title" class="c-input"
                                  :maxlength="5" show-word-limit
                        ></el-input>
                    </el-form-item>
                    <el-form-item label="填充颜色">
                        <div flex="dir:left main:between">
                            <div>
                                <el-color-picker @change="(row) => {row == null ? data.two_bg = '#FFFFFF' : ''}"
                                                 size="small"
                                                 v-model="data.two_bg"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.two_bg"></el-input>
                            </div>
                            <el-form-item label="边框颜色">
                                <el-color-picker @change="(row) => {row == null ? data.two_border = '#ff4544' : ''}"
                                                 size="small"
                                                 v-model="data.two_border"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.two_border"></el-input>
                                <div style="position:absolute;color:#a9a297">不填默认无边框</div>
                            </el-form-item>
                        </div>
                    </el-form-item>
                    <el-form-item label="文本颜色">
                        <el-color-picker @change="(row) => {row == null ? data.two_bg = '#ff4544' : ''}"
                                         size="small"
                                         v-model="data.two_color"></el-color-picker>
                        <el-input size="small" class="c-input-big"
                                  v-model="data.two_color"></el-input>
                    </el-form-item>
                </template>

                <template v-if="data.select_style == 1">
                    <!-------->
                    <el-form-item label="按钮1样式设置"></el-form-item>
                    <el-form-item label="按钮圆角" prop="save_radius">
                        <div flex="dir:left">
                            <el-slider style="width: 50%;margin-right: 20px" input-size="mini"
                                       v-model="data.save_radius"
                                       :max="40" :min="0"
                                       :show-tooltip="false"></el-slider>
                            <el-input-number v-model="data.save_radius" :min="0"
                                             :max="40" label="按钮圆角"></el-input-number>
                            <div style="margin-left: 10px">px</div>
                        </div>
                    </el-form-item>
                    <el-form-item label="按钮文本">
                        <el-input size="small" v-model="data.save_title" class="c-input"
                        ></el-input>
                    </el-form-item>
                    <el-form-item label="填充颜色">
                        <div flex="dir:left main:between">
                            <div>
                                <el-color-picker @change="(row) => {row == null ? data.save_bg = '#FFFFFF' : ''}"
                                                 size="small"
                                                 v-model="data.save_bg"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.save_bg"></el-input>
                            </div>
                            <el-form-item label="边框颜色">
                                <el-color-picker
                                        @change="(row) => {row == null ? data.save_border = '#ff4544' : ''}"
                                        size="small"
                                        v-model="data.save_border"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.save_border"></el-input>
                                <div style="position:absolute;color:#a9a297">不填默认无边框</div>
                            </el-form-item>
                        </div>
                    </el-form-item>
                    <el-form-item label="文本颜色">
                        <el-color-picker @change="(row) => {row == null ? data.save_bg = '#ff4544' : ''}"
                                         size="small"
                                         v-model="data.save_color"></el-color-picker>
                        <el-input size="small" class="c-input-big"
                                  v-model="data.save_color"></el-input>
                    </el-form-item>
                    <!-------->
                    <el-form-item label="按钮2样式设置"></el-form-item>
                    <el-form-item label="按钮圆角" prop="copy_radius">
                        <div flex="dir:left">
                            <el-slider style="width: 50%;margin-right: 20px" input-size="mini"
                                       v-model="data.copy_radius"
                                       :max="40" :min="0"
                                       :show-tooltip="false"></el-slider>
                            <el-input-number v-model="data.copy_radius" :min="0"
                                             :max="40" label="按钮圆角"></el-input-number>
                            <div style="margin-left: 10px">px</div>
                        </div>
                    </el-form-item>
                    <el-form-item label="按钮文本">
                        <el-input size="small" v-model="data.copy_title" class="c-input"
                        ></el-input>
                    </el-form-item>
                    <el-form-item label="填充颜色">
                        <div flex="dir:left main:between">
                            <div>
                                <el-color-picker @change="(row) => {row == null ? data.copy_bg = '#FFFFFF' : ''}"
                                                 size="small"
                                                 v-model="data.copy_bg"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.copy_bg"></el-input>
                            </div>
                            <el-form-item label="边框颜色">
                                <el-color-picker
                                        @change="(row) => {row == null ? data.copy_border = '#ff4544' : ''}"
                                        size="small"
                                        v-model="data.copy_border"></el-color-picker>
                                <el-input size="small" class="c-input-big"
                                          v-model="data.copy_border"></el-input>
                                <div style="position:absolute;color:#a9a297">不填默认无边框</div>
                            </el-form-item>
                        </div>
                    </el-form-item>
                    <el-form-item label="文本颜色">
                        <el-color-picker @change="(row) => {row == null ? data.copy_bg = '#ff4544' : ''}"
                                         size="small"
                                         v-model="data.copy_color"></el-color-picker>
                        <el-input size="small" class="c-input-big"
                                  v-model="data.copy_color"></el-input>
                    </el-form-item>
                </template>
                <app-padding @ss="ss" v-model="data">
                    <template slot="c-bg">
                        <el-form-item label="组件背景颜色">
                            <el-color-picker @change="(row) => {row == null ? value.bg = '#FFFFFF' : ''}" size="small"
                                             v-model="value.bg"></el-color-picker>
                            <el-input size="small" class="c-input-big"
                                      v-model="value.bg"></el-input>
                        </el-form-item>
                    </template>
                </app-padding>
            </el-form>
            <!--客服微信-->
            <el-dialog title="客服微信" :visible.sync="wechatVisible" width="30%" :close-on-click-modal="false">
                <el-form :model="wechatForm" label-width="150px" :rules="wechatRules" ref="wechatForm"
                         @submit.native.prevent>
                    <el-form-item label="客服微信二维码" prop="qrcode">
                        <div style="margin-bottom:10px;">
                            <app-attachment style="display:inline-block;margin-right: 10px" :multiple="false"
                                            :max="1"
                                            @selected="wechatSelect">
                                <el-tooltip effect="dark" content="建议尺寸:360 * 360" placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </app-attachment>
                        </div>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1" @selected="wechatSelect">
                                <app-image mode="aspectFill" width="80px" height='80px'
                                           :src="wechatForm.qrcode"></app-image>
                            </app-attachment>
                            <el-button v-if="wechatForm.qrcode" class="del-btn" size="mini" type="danger"
                                       icon="el-icon-close" circle @click="wechatForm.qrcode = ''"></el-button>
                        </div>
                    </el-form-item>
                    <el-form-item label="客服微信号" prop="name">
                        <el-input size="small" v-model="wechatForm.name" maxlength="20"
                                  auto-complete="off"></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button size="small" @click="wechatVisible = false">取消</el-button>
                    <el-button size="small" type="primary" @click.native="wechatSubmit">提交</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-customer', {
        template: '#diy-customer',
        props: {
            value: Object,
        },
        data() {
            var validate = (rule, value, callback) => {
                if (this.templateName) {
                    callback();
                } else {
                    callback(new Error(this.labelText['title'] + '不能为空'))
                }
            };
            return {
                defaultImage: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/images/customer-up.png",
                wechatVisible: false,
                wechatForm: {
                    qrcode: '',
                    name: '',
                },
                wechatRules: {
                    qrcode: [
                        {required: true, message: '二维码不能为空', trigger: 'blur'},
                    ],
                    name: [
                        {required: true, message: '微信微信号不能为空', trigger: 'blur'},
                    ],
                },
                validate,
                data: {
                    select_style: '1',
                    sub_title: '有专属福利优惠活动哦~',
                    sub_title_color: '#999999',
                    two_icon: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/images/icon-customer-two.png",
                    two_radius: 40,
                    two_title: '复制微信号',
                    two_bg: '#ff4544',
                    two_border: '#FFFFFF',
                    two_color: '#FFFFFF',

                    title: '客服标题',
                    title_color: '#353535',
                    wechat: [],
                    wechat_color: '#999999',
                    save_radius: 40,
                    save_title: '保存客服二维码图片',
                    save_bg: '#FFFFFF',
                    save_border: '#ff4544',
                    save_color: '#ff4544',
                    copy_radius: 40,
                    copy_title: '复制客服微信号',
                    copy_bg: '#ffffff',
                    copy_border: '#ff4544',
                    copy_color: '#ff4544',
                    c_padding_top: 24,
                    c_padding_bottom: 24,
                    c_padding_lr: 24,
                    c_border_top: 16,
                    c_border_bottom: 16,
                    bg: '#FFFFFF',
                    bg_padding: '#F7F7F7'
                },
                styleA: {},
                styleB: {},
            };
        },
        created() {
            let data = JSON.parse(JSON.stringify(this.data));
            if (!this.value) {
                this.$emit('input', data)
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            resetImg(type) {
                const host = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() . '/images/' ?>";
                if (type === 'two_icon') {
                    this.data.two_icon = host + 'icon-customer-two.png';
                }
            },
            ss(styleA, styleB) {
                this.styleA = styleA;
                this.styleB = styleB;
            },
            twoIconSelect(e) {
                if (e.length) this.data.two_icon = e[0]['url'];
            },
            twoIconDel() {
                this.data.two_icon = '';
            },
            wechatDel(index) {
                this.data.wechat.splice(index, 1);
            },
            wechatSelect(e) {
                if (e.length) this.wechatForm.qrcode = e[0]['url'];
            },
            wechatAdd() {
                this.wechatForm = {qrcode: '', name: '', index: this.data.wechat.length};
                this.wechatVisible = true;
            },
            wechatEdit(column, index) {
                this.wechatForm = Object.assign({index}, column);
                this.wechatVisible = true;
            },
            wechatSubmit() {
                this.$refs.wechatForm.validate((valid) => {
                    if (valid) {
                        let {index, qrcode, name} = this.wechatForm;
                        this.data.wechat.splice(index, index === this.data.wechat.length ? 0 : 1, {
                            qrcode,
                            name
                        });
                        this.wechatVisible = false;
                    }
                });
            },

        }
    });
</script>
