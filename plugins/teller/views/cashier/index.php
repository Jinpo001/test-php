<style>
    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>收银员</span>
            <el-button @click="$navigate({r:'plugin/teller/mall/cashier/detail'})" style="margin: -5px 0" type="primary"
                       size="small">添加收银员
            </el-button>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <el-select size="small" @change="searchList" v-model="search.store_id" placeholder="请选择门店">
                    <el-option value="" label="全部门店"></el-option>
                    <el-option
                            v-for="(item, index) in storeList"
                            :key="index"
                            :label="item.name"
                            :value="item.id">
                    </el-option>
                </el-select>
                <div class="input-item" style="display:inline-block;margin-left: 12px">
                    <el-input @keyup.enter.native="searchList"
                              size="small"
                              placeholder="请输入收银员编号/姓名/电话"
                              v-model="search.keyword"
                              clearable
                              @clear="searchList">
                        <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                    </el-input>
                </div>
            </el-col>
            <!-- 列表 -->
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="number" label="收银员编号" width="100"></el-table-column>
                <el-table-column prop="name" label="姓名"></el-table-column>
                <el-table-column prop="mobile" label="电话" width="180"></el-table-column>
                <el-table-column prop="username" label="账号"></el-table-column>
                <el-table-column prop="creator_name" label="创建人"></el-table-column>
                <el-table-column prop="store_name" label="所属门店"></el-table-column>
                <el-table-column prop="id" label="业绩">
                    <template slot="header" slot-scope="scope">
                        <span>业绩</span>
                        <el-tooltip class="item" effect="dark" content="销售额/提成" placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </template>
                    <template slot-scope="scope">
                        <span>{{scope.row.sale_money}} / {{scope.row.push_money}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="添加日期" width="110"></el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                    <template slot-scope="scope">
                        <el-switch
                                @change="changeStatus(scope.row.id,scope.row.status)"
                                v-model="scope.row.status"
                                :active-value="1"
                                :inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="280" fixed="right">
                    <template slot-scope="scope">
                        <el-button size="mini" type="text" @click="navTest(scope.row)" circle>
                            <el-tooltip class="item" effect="dark" content="修改密码" placement="top">
                                <img src="statics/img/mall/change.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="edit(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" type="text"
                                   @click="$navigate({r:'plugin/teller/mall/push/index',user_type:'cashier',keyword_name: 'number', keyword_value: scope.row.number})"
                                   circle>
                            <el-tooltip class="item" effect="dark" content="业绩明细" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button class="set-el-button" size="mini" type="text" circle
                                   @click="destroy(scope.row,scope.$index)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <el-dialog title="修改密码" :visible.sync="pFormVisible" width="30%" :close-on-click-modal="false">
            <el-form :model="pForm" label-width="100px" :rules="pFormRules" ref="pForm">
                <el-form-item label="修改密码" prop="password">
                    <el-input size="small" v-model="pForm.password" auto-complete="off" show-password></el-input>
                </el-form-item>
                <el-form-item label="确认密码" prop="password_verify">
                    <el-input size="small" v-model="pForm.password_verify" auto-complete="off" show-password></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click.native="pFormVisible = false" :loading="btnLoading">取消</el-button>
                <el-button size="small" type="primary" @click.native="pSubmit" :loading="btnLoading">提交</el-button>
            </div>
        </el-dialog>

        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="pagination">
            </el-pagination>
        </el-col>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    store_id: '',
                    keyword: '',
                },
                pFormVisible: false,
                pForm: {
                    id: '',
                    password: '',
                    password_verify: '',
                },
                pFormRules: {
                    password: [
                        {required: true, message: '密码不能为空', trigger: 'blur'},
                    ],
                    password_verify: [
                        {
                            required: true, type: 'String', validator: (rule, value, callback) => {
                                if (value == '') {
                                    callback('确认密码不能为空');
                                } else if (value !== this.pForm.password) {
                                    callback('密码不一致');
                                } else {
                                    callback();
                                }
                            }
                        }
                    ]

                },
                storeList: [],
                btnLoading: false,
                listLoading: false,
                list: [],
                pagination: null,
                page: 1,
            };
        },
        mounted() {
            this.getList();
            this.getStore();
        },
        methods: {
            getStore() {
                request({
                    params: {
                        r: 'mall/store/index',
                        page_size: 999,
                    },
                    method: 'get',
                }).then(e => {
                    this.storeList = e.data.data.list;
                });
            },
            getList() {
                this.listLoading = true;
                let params = Object.assign({}, {
                    r: 'plugin/teller/mall/cashier/index',
                    page: this.page,
                }, this.search);
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination
                    }
                });
            },
            navTest(column) {
                this.pForm = {
                    id: column.id,
                    password: '',
                    password_verify: '',
                };
                this.pFormVisible = true;
            },
            pSubmit() {
                this.$refs.pForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.pForm);
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/teller/mall/cashier/update-password',
                            },
                            data: para,
                            method: 'POST',
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                this.pFormVisible = false;
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        })
                    }
                });
            },
            pageChange(page) {
                this.page = page;
                this.getList()
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            edit(column) {
                navigateTo({
                    r: 'plugin/teller/mall/cashier/detail',
                    id: column.id,
                })
            },
            changeStatus(id, status) {
                let para = Object.assign({}, {id: id, status: status});
                request({
                    params: {
                        r: 'plugin/teller/mall/cashier/update-status',
                    },
                    data: para,
                    method: 'POST',
                }).then(e => {
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            destroy(params, index) {
                this.$confirm('是否删除该收银员', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/teller/mall/cashier/delete',
                        },
                        data: {
                            id: params.id,
                        },
                        method: 'POST',
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            this.list.splice(index, 1);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch(() => {
                    this.$message({type: 'info', message: '已取消删除'});
                });
            },
        }
    });
</script>
