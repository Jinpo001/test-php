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
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                 <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/teller/mall/cashier/index'})">
                    收银员
                 </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item v-if="id">编辑收银员</el-breadcrumb-item>
            <el-breadcrumb-item v-else>添加收银员</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <el-card v-loading="listLoading" shadow="never" body-style="background-color: #ffffff;">
        <el-form :model="editForm" ref="editForm" :rules="editFormRules" label-width="150px" position-label="right">
            <el-form-item prop="number" label="收银员编号">
                <el-input class="out-max"
                          v-model="editForm.number"
                          placeholder="请输入收银员编号"
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
            <el-form-item prop="username" label="账号">
                <el-input class="out-max"
                          v-model="editForm.username"
                          placeholder="请输入账号"
                          size="small"
                ></el-input>
            </el-form-item>
            <el-form-item v-if="!id" prop="password" label="密码">
                <el-input class="out-max"
                          show-password
                          v-model="editForm.password"
                          placeholder="请输入密码"
                          size="small"
                ></el-input>
            </el-form-item>
            <el-form-item v-if="!id" prop="password_verify" label="确认密码">
                <el-input class="out-max"
                          show-password
                          v-model="editForm.password_verify"
                          placeholder="请确认密码"
                          size="small"
                ></el-input>
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
    <el-button size="small" style="margin-top: 20px" :loading="btnLoading" @click="reset">重置</el-button>
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
                    username: '',
                    password: '',
                    password_verify: '',
                    store_id: '',
                    status: 1,
                },
                editFormRules: {
                    number: [
                        {required: true, message: '收银员编号不能为空', trigger: 'blur'},
                    ],
                    name: [
                        {required: true, message: '姓名不能为空', trigger: 'blur'},
                    ],
                    mobile: [
                        {required: true, message: '电话不能为空', trigger: 'blur'},
                    ],
                    username: [
                        {required: true, message: '账户不能为空', trigger: 'blur'},
                    ],
                    password: [
                        {required: true, message: '密码不能为空', trigger: 'blur'},
                    ],
                    password_verify: [
                        {
                            required: true, type: 'array', validator: (rule, value, callback) => {
                                if (value == '') {
                                    callback('确认密码不能为空');
                                } else if (value !== this.editForm.password) {
                                    callback('密码不一致');
                                } else {
                                    callback();
                                }
                            }
                        }
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
            reset() {
                this.editForm = {
                    number: '',
                    name: '',
                    mobile: '',
                    username: '',
                    password: '',
                    password_verify: '',
                    store_id: '',
                    status: 0,
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
                            r = 'plugin/teller/mall/cashier/modify';
                        } else {
                            r = 'plugin/teller/mall/cashier/store';
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
                                        r: 'plugin/teller/mall/cashier/index',
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
                        r: 'plugin/teller/mall/cashier/detail',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.editForm = Object.assign({}, e.data.data.cashier);
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
