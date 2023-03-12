
<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 285px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }
    .el-form-item__content .el-input-group {
        vertical-align: middle;
    }
    .rules {
        padding: 20px;
        background-color: #F4F4F5;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>链接生成工具(微信小程序)</span>
                <el-button style="float: right; margin: -5px 0" type="primary" @click="toggleDialog" size="small">
                    立即生成
                </el-button>
            </div>
        </div>
        <div class="table-body">
            <div class="rules" style="background-color: #ECF5FE">
                <div>生成的URL Scheme适用于从短信、邮件、微信外网页等场景打开微信小程序，不支持从微信app直接打开。</div>
            </div>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入链接名称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table class="table-info" :data="list" border style="width: 100%" v-loading="listLoading">
                <el-table-column label="链接名称" prop="name" width="300"></el-table-column>
                <el-table-column label="创建时间" prop="created_at" width="200"></el-table-column>
                <el-table-column label="失效时间" prop="expire" width="200"></el-table-column>
                <el-table-column label="iOS专用链接" prop="url_scheme" width="320">
                    <template slot-scope="scope">
                        <span :id="'ios'+scope.$index">{{scope.row.url_scheme}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="通用链接" prop="url" width="390">
                    <template slot-scope="scope">
                        <span :id="'anzhuo'+scope.$index">{{scope.row.url}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作">
                    <template slot-scope="scope">
                        <el-button class="copy-btn" circle size="mini" type="text" data-clipboard-action="copy" :data-clipboard-target="'#ios'+scope.$index">
                            <el-tooltip effect="dark" content="iOS专用链接" placement="top">
                                <img src="statics/img/mall/copy-other.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button class="copy-btn" circle size="mini" type="text" data-clipboard-action="copy" :data-clipboard-target="'#anzhuo'+scope.$index">
                            <el-tooltip effect="dark" content="通用链接" placement="top">
                                <img src="statics/img/mall/copy.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        :page-size="pagination.pageSize" hide-on-single-page background @current-change="pageChange" layout="prev, pager, next, jumper" :total="pagination.total_count">
                </el-pagination>
            </div>
        </div>
    </el-card>
    <el-dialog title="生成推广链接" :visible.sync="dialogAdd" width="30%">
        <el-form @submit.native.prevent :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
            <el-form-item label="链接名称" prop="name">
                <el-input size="small" placeholder="限制14个字以内" maxlength="14" v-model="addForm.name"></el-input>
            </el-form-item>
            <el-form-item label="小程序路径" prop="link">
                <el-input :disabled="true" size="small" v-model="addForm.link.new_link_url" autocomplete="off">
                    <app-pick-link slot="append" @selected="selectAdvertUrl">
                        <el-button size="mini">选择链接</el-button>
                    </app-pick-link>
                </el-input>
            </el-form-item>
            <el-form-item label="分销商选择" prop="nickname">
                <el-autocomplete size="small" style="width: 70%;" v-model="addForm.nickname" value-key="nickname" :fetch-suggestions="querySearchAsync" placeholder="请选择分销商" @select="shareClick"></el-autocomplete>
                <div style="font-size: 12px;color: #909399;height: 28px;">注：请选择绑定指定分销商，若不需要，则不选择</div>
            </el-form-item>
            <el-form-item label="失效时间" prop="is_expire">
                <el-radio v-model="addForm.is_expire" :label="0">永久有效</el-radio>
                <el-radio v-model="addForm.is_expire" label="1">
                    <span style="position: relative;">
                        <el-input size="small" type="number" style="width: 160px;margin-right: 10px;" v-model="addForm.expire_time">
                            <template slot="append">天</template>
                        </el-input>
                        <div v-if="addForm.is_expire == 1" style="font-size: 12px;color: #909399;position: absolute;bottom: -25px;left: 0;">0<失效时间≤365</div>
                    </span>
                    <span>后失效</span>
                </el-radio>
            </el-form-item>
            <el-form-item>
                <el-button size="small" style="float: right;padding: 0;width: 70px;height: 32px;margin-left: 20px" type="primary" @click="addSubmit" :loading="btnLoading">确定</el-button>
                <el-button size="small" style="float: right;padding: 0;width: 70px;height: 32px;" @click="toggleDialog">取消</el-button>
            </el-form-item>
        </el-form>
    </el-dialog>
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
                addFormRules: {
                    name: [
                        { required: true, message: '链接名称不得为空', trigger: 'blur' }
                    ],
                    link: [
                        { required: true, message: '小程序链接不得为空', trigger: 'blur' }
                    ],
                    is_expire: [
                        { required: true, message: '请选择失效时间', trigger: 'blur' }
                    ],
                },
                addForm: {
                    name: '',
                    link: {
                        new_link_url: ''
                    },
                    is_expire: 0,
                    expire_time: ''
                },
                keyword: '',
                userkeyword: '',
                dialogAdd: false,
                btnLoading: false,
                listLoading: false,
                page: 1,
                pagination: {},
                list: [],
            };
        },
        methods: {
            selectAdvertUrl(e) {
                let self = this;
                e.forEach(function (item, index) {
                    self.addForm.link = item;
                })
            },
            //搜索
            querySearchAsync(queryString, cb) {
                this.userkeyword = queryString;
                this.shareUser(cb);
            },

            shareClick(row) {
                this.addForm.user_id = row.id;
                console.log(this.addList)
            },
            search() {
                this.page = 1;
                this.getList();
            },
            shareUser(cb) {
                request({
                    params: {
                        r: 'mall/share/index-data',
                        keyword: this.userkeyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        cb(e.data.data.list);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {});
            },
            addSubmit() {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        if(this.addForm.is_expire == 1 && !(this.addForm.expire_time > 0 && this.addForm.expire_time < 366)) {
                            this.$message.error('请填写正确的失效时间');
                            return false;
                        }
                        this.btnLoading = true;
                        request({
                            params: {
                                r: '/plugin/url_scheme/mall/index/index',
                            },
                            data: this.addForm,
                            method: 'post',
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.toggleDialog();
                                this.getList();
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    }
                });
            },
            toggleDialog() {
                this.dialogAdd = !this.dialogAdd;
                this.addForm.expire_time = '';
                if(this.$refs.addForm) {
                    this.$refs.addForm.resetFields();
                }
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: '/plugin/url_scheme/mall/index/index',
                        keyword: this.keyword,
                        page: this.page
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
        },
        created: function () {
            this.getList();
        }
    });
</script>