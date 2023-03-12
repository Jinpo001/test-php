<?php
Yii::$app->loadViewComponent('app-select-store');
?>
<style>
    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .out-max {
        width: 500px;
    }

    .out-max > .el-card__header {
        padding: 0 15px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }

</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                 <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/teller/mall/sales/index'})">
                    导购员
                 </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item v-if="id">编辑导购员</el-breadcrumb-item>
            <el-breadcrumb-item v-else>添加导购员</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <el-card v-loading="listLoading" shadow="never" body-style="background-color: #ffffff;">
        <el-form :model="editForm" ref="editForm" :rules="editFormRules" label-width="150px" position-label="right">
            <el-form-item prop="number" label="导购员编号">
                <el-input class="out-max"
                          v-model="editForm.number"
                          placeholder="请输入导购员编号"
                          size="small"
                ></el-input>
            </el-form-item>
            <el-form-item prop="name" label="姓名">
                <el-input class="out-max"
                          v-model="editForm.name"
                          placeholder="请输入姓名"
                          size="small"
                ></el-input>
            </el-form-item>
            <el-form-item prop="mobile" label="电话">
                <el-input class="out-max"
                          v-model="editForm.mobile"
                          placeholder="请输入电话"
                          size="small"
                ></el-input>
            </el-form-item>
            <el-form-item label="头像" prop="head_url">
                <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                @selected="selectedHeadUrl">
                    <el-tooltip effect="dark"
                                content="建议尺寸:40 * 40"
                                placement="top">
                        <el-button size="mini">选择图标</el-button>
                    </el-tooltip>
                </app-attachment>
                <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                    <app-attachment :multiple="false" :max="1"
                                    @selected="selectedHeadUrl">
                        <app-image mode="aspectFill"
                                   width="80px"
                                   height='80px'
                                   :src="editForm.head_url">
                        </app-image>
                    </app-attachment>
                    <el-button v-if="editForm.head_url" class="del-btn"
                               size="mini" type="danger" icon="el-icon-close"
                               circle
                               @click="delHeadUrl"></el-button>
                </div>
                <el-button size="mini" @click="resetImg('head')" class="reset" type="primary">恢复默认</el-button>
            </el-form-item>
            <el-form-item prop="store_id" label="门店">
                <el-tag v-if="editForm.store_id" @close="handleStoreClose" closable disable-transitions>
                    {{editForm.store_name}}
                </el-tag>
                <app-select-store v-else @change="changeStore">
                    <el-button size="small">选择门店</el-button>
                </app-select-store>
            </el-form-item>
            <el-form-item prop="status" label="是否启用">
                <el-switch v-model="editForm.status" :active-value="1" :inactive-value="0"></el-switch>
            </el-form-item>
        </el-form>
    </el-card>
    <el-button size="small" style="margin-top: 20px" :loading="btnLoading" type="primary" @click="submit">保存</el-button>
    <el-button v-if="false" size="small" style="margin-top: 20px" :loading="btnLoading" @click="reset">重置</el-button>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                id: getQuery('id'),

                store_name: '',
                btnLoading: false,
                listLoading: false,
                editForm: {
                    number: '',
                    name: '',
                    mobile: '',
                    store_id: '',
                    status: 1,
                    head_url: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl(); ?>" + '/img/default-avatar.png',
                },
                editFormRules: {
                    number: [
                        {required: true, message: '导购员编号不能为空', trigger: 'blur'},
                    ],
                    name: [
                        {required: true, message: '姓名不能为空', trigger: 'blur'},
                    ],
                    mobile: [
                        {required: true, message: '电话不能为空', trigger: 'blur'},
                    ],
                    store_id: [
                        {required: true, message: '门店不能为空', trigger: ['blur', 'change']},
                    ],
                    status: [
                        {required: true, message: '是否启用不能为空', trigger: 'blur'},
                    ],
                },
            }
        },

        methods: {
            resetImg() {
                const host = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl(); ?>";
                this.editForm.head_url = host + '/img/default-avatar.png';
            },
            delHeadUrl() {
                this.editForm.head_url = '';
            },
            selectedHeadUrl(e) {
                if (e.length) {
                    this.editForm.head_url = e[0].url;
                }
            },
            reset() {
                this.editForm = {
                    number: '',
                    name: '',
                    mobile: '',
                    store_id: '',
                    status: 0,
                    head_url: '',
                };
            },
            handleStoreClose() {
                this.editForm.store_id = '';
                this.editForm.store_name = '';
            },
            changeStore(e) {
                Object.assign(this.editForm, {
                    store_id: e.id,
                    store_name: e.name,
                })
                this.$refs.editForm.validateField('store_id');
                this.store_name = e.name;
                this.title = e.id;
            },
            submit() {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.editForm);

                        let r;
                        if (this.id) {
                            r = 'plugin/teller/mall/sales/modify';
                        } else {
                            r = 'plugin/teller/mall/sales/store';
                        }
                        request({
                            params: {
                                r,
                            },
                            data: para,
                            method: 'POST'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                setTimeout(function () {
                                    navigateTo({
                                        r: 'plugin/teller/mall/sales/index',
                                    })
                                }, 2000);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            getData() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/teller/mall/sales/detail',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.editForm = Object.assign({}, e.data.data.sales);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getData();
            }
        }
    });
</script>
