<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>打印设置</span>
            <el-button @click="handleAdd"
                       style="margin: -5px 0"
                       type="primary"
                       size="small">
                添加
            </el-button>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <div class="toolbar" style="background: #FFFFFF">
                <div class="col-li" style="margin-bottom: 24px">
                    <span style="margin-right: 5px">所属门店</span>
                    <el-select @change="searchList" size="small" v-model="search.store_id" placeholder="请选择门店">
                        <el-option label="全部门店" value=""></el-option>
                        <el-option
                                v-for="(item, index) in storeList"
                                :key="index"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </div>
            </div>
            <!-- 列表 -->
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="id" label="ID" width="120"></el-table-column>
                <el-table-column prop="printer_name" label="打印机名称"></el-table-column>
                <el-table-column prop="store_name" label="所属门店"></el-table-column>
                <el-table-column prop="status" label="是否启用">
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
                        <el-button type="text" @click="handleEdit(scope.$index,scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
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
        <!--编辑界面-->
        <el-dialog title="编辑" :visible.sync="editFormVisible" :close-on-click-modal="false" width="35%">
            <el-form :model="editForm" label-width="120px" :rules="editFormRules" ref="editForm">
                <el-form-item label="选择打印机" prop="printer_id">
                    <el-select filterable v-model="editForm.printer_id" size="small">
                        <el-option :label="item.name"
                                   :value="Number(item.id)"
                                   :key="item.id"
                                   v-for="item in printerList"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="打印参数" prop="show_type">
                    <el-checkbox-group v-model="show_type">
                        <el-checkbox label="attr">规格显示</el-checkbox>
                        <el-checkbox label="goods_no">货号显示</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="选择门店" prop="store_id">
                    <!--<template slot="label">-->
                    <!--    <span>选择门店</span>-->
                    <!--    <el-tooltip effect="dark" placement="top"-->
                    <!--                content="注意：选择了门店表示该打印设置只打印该门店的到店自提订单，若要打印快递跟同城订单，请选择全门店通用">-->
                    <!--        <i class="el-icon-info"></i>-->
                    <!--    </el-tooltip>-->
                    <!--</template>-->
                    <el-select filterable v-model="editForm.store_id" size="small">
                        <el-option :label="item.name"
                                   :value="Number(item.id)"
                                   :key="item.id"
                                   v-for="item in storeList"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="显示大小" prop="big">
                    <!--<template slot="label">-->
                    <!--    <span>显示大小</span>-->
                    <!--    <el-tooltip effect="dark" placement="top" content="收货信息或门店">-->
                    <!--        <i class="el-icon-info"></i>-->
                    <!--    </el-tooltip>-->
                    <!--</template>-->
                    <el-radio-group v-model="editForm.big">
                        <el-radio :label="0">一倍</el-radio>
                        <el-radio :label="1">两倍</el-radio>
                        <el-radio :label="2">三倍</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="是否启用" prop="status">
                    <el-switch
                            v-model="editForm.status"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="editFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="editSubmit" :loading="editBtnLoading">提交</el-button>
            </div>
        </el-dialog>
        <!--新增界面-->
        <el-dialog title="新增" :visible.sync="addFormVisible" :close-on-click-modal="false" width="35%">
            <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
                <el-form-item label="选择打印机" prop="printer_id">
                    <el-select filterable v-model="addForm.printer_id" size="small">
                        <el-option :label="item.name" :value="item.id" :key="item.id"
                                   v-for="item in printerList"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="打印参数" prop="show_type">
                    <el-checkbox-group v-model="show_type">
                        <el-checkbox label="attr">规格</el-checkbox>
                        <el-checkbox label="goods_no">货号</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item label="选择门店" prop="store_id">
                    <!--<template slot="label">-->
                    <!--    <span>选择门店</span>-->
                    <!--    <el-tooltip effect="dark" placement="top"-->
                    <!--                content="注意：选择了门店表示该打印设置只打印该门店的到店自提订单，若要打印快递跟同城订单，请选择全门店通用">-->
                    <!--        <i class="el-icon-info"></i>-->
                    <!--    </el-tooltip>-->
                    <!--</template>-->
                    <el-select filterable v-model="addForm.store_id" size="small">
                        <el-option :label="item.name"
                                   :value="Number(item.id)"
                                   :key="item.id"
                                   v-for="item in storeList"
                        ></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="显示大小" prop="big">
                    <!--<template slot="label">-->
                    <!--    <span>显示大小</span>-->
                    <!--    <el-tooltip effect="dark" placement="top" content="收货信息或门店">-->
                    <!--        <i class="el-icon-info"></i>-->
                    <!--    </el-tooltip>-->
                    <!--</template>-->
                    <el-radio-group v-model="addForm.big">
                        <el-radio :label="0">一倍</el-radio>
                        <el-radio :label="1">两倍</el-radio>
                        <el-radio :label="2">三倍</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="是否启用" prop="status">
                    <el-switch
                            v-model="addForm.status"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="addFormVisible = false">取消</el-button>
                <el-button type="primary" @click.native="addSubmit" :loading="addBtnLoading">提交</el-button>
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
                },
                printerList: [],
                storeList: [],
                list: [],
                listLoading: false,
                pagination: null,
                page: 1,

                show_type: [],
                editBtnLoading: false,
                editFormVisible: false,//编辑界面是否显示
                editLoading: false,
                editFormRules: {
                    printer_id: [
                        {required: true, message: '打印机不能为空', trigger: 'change'}
                    ],
                    store_id: [
                        {required: true, message: '门店不能为空', trigger: 'change'}
                    ],
                },
                //编辑界面数据
                editForm: {
                    printer_id: '',
                    show_type: null,
                    store_id: '',
                    big: 0,
                    status: 0,
                },

                addBtnLoading: false,
                addFormVisible: false,//新增界面是否显示
                addLoading: false,
                addFormRules: {
                    printer_id: [
                        {required: true, message: '打印机不能为空', trigger: 'change'}
                    ],
                    store_id: [
                        {required: true, message: '门店不能为空', trigger: 'change'}
                    ],
                },
                //新增界面数据
                addForm: {
                    printer_id: '',
                    show_type: null,
                    store_id: '',
                    big: 0,
                    status: 0,
                },
            };
        },
        mounted() {
            this.getList();
            this.getStore();
            this.getPrinter();
        },
        methods: {
            //编辑界面
            handleEdit: function (index, row) {
                this.editForm = Object.assign({}, row);
                this.show_type = [];
                for (let key in this.editForm.show_type) {
                    if (this.editForm.show_type[key] == 1) this.show_type.push(key)
                }
                this.editFormVisible = true;
            },
            //显示新增界面
            handleAdd: function () {
                this.addFormVisible = true;
                this.show_type = [];
                this.addForm = {
                    printer_id: '',
                    show_type: null,
                    store_id: '',
                    big: 0,
                    status: 0,
                };
            },
            editSubmit: function () {
                this.$refs.editForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.editForm, {
                                show_type: JSON.stringify({
                                    attr: this.show_type.indexOf('attr') !== -1 ? 1 : 0,
                                    goods_no: this.show_type.indexOf('goods_no') !== -1 ? 1 : 0,
                                    form_data: this.show_type.indexOf('form_data') !== -1 ? 1 : 0,
                                })
                            }
                        );
                        this.editBtnLoading = true;
                        request({
                            params: {
                                r: 'plugin/teller/mall/printer/modify',
                            },
                            data: para,
                            method: 'POST'
                        }).then(e => {
                            this.editBtnLoading = false;
                            let {success, error} = this.$message;
                            if (e.data.code === 0) {
                                success(e.data.msg);
                                this.editFormVisible = false;
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                error(e.data.msg);
                            }
                        }).catch(e => {
                            this.editBtnLoading = false;
                        });
                    }
                });
            },
            // 增加
            addSubmit: function () {
                this.$refs.addForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.addForm, {
                                show_type: JSON.stringify({
                                    attr: this.show_type.indexOf('attr') !== -1 ? 1 : 0,
                                    goods_no: this.show_type.indexOf('goods_no') !== -1 ? 1 : 0,
                                    form_data: this.show_type.indexOf('form_data') !== -1 ? 1 : 0,
                                })
                            }
                        );
                        this.addBtnLoading = true;
                        request({
                            params: {
                                r: 'plugin/teller/mall/printer/store',
                            },
                            data: para,
                            method: 'POST'
                        }).then(e => {
                            this.addBtnLoading = false;
                            let {success, error} = this.$message;
                            if (e.data.code === 0) {
                                success(e.data.msg);
                                this.addFormVisible = false;
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                error(e.data.msg);
                            }
                        }).catch(e => {
                            this.addBtnLoading = false;
                        });
                    }
                });
            },

            getStore() {
                request({
                    params: {
                        r: 'mall/store/index',
                        page_size: 999,
                    },
                }).then(e => {
                    this.storeList = e.data.data.list;
                });
            },
            getPrinter() {
                request({
                    params: {
                        r: 'mall/printer/index',
                        page_size: 999,
                    },
                }).then(e => {
                    this.printerList = e.data.data.list;
                });
            },
            getList() {
                this.listLoading = true;
                let params = Object.assign({}, {
                    r: 'plugin/teller/mall/printer/index',
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
            pageChange(page) {
                this.page = page;
                this.getList()
            },
            searchList() {
                this.page = 1;
                this.getList();
            },
            changeStatus(id, status) {
                let para = Object.assign({}, {id: id, status: status});
                request({
                    params: {
                        r: 'plugin/teller/mall/printer/update-status',
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
                this.$confirm('是否删除该打印设置', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/teller/mall/printer/delete',
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
