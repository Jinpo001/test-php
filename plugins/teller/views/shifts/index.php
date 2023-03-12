<?php
Yii::$app->loadViewComponent('app-export-dialog');
Yii::$app->loadViewComponent('order/app-select-print');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .col-li {
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 12px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span style="margin: auto 0">交班记录</span>
            <app-new-export-dialog-2
                    action_url="plugin/teller/mall/shifts/index"
                    :field_list="exportList"
                    :params="search"
                    @selected="confirmSubmit">
            </app-new-export-dialog-2>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <div class="col-li">
                    <span style="margin-right: 5px">交班时间</span>
                    <el-date-picker
                            class="item-box date-picker"
                            size="small"
                            @change="searchList"
                            v-model="search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div class="col-li">
                    <span style="margin-right: 5px">收银员</span>
                    <el-select size="small" @change="searchList" v-model="search.cashier_id" placeholder="请选择收银员">
                        <el-option value="" label="全部收银员"></el-option>
                        <el-option
                                v-for="(item, index) in cashierList"
                                :key="index"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </div>
                <div class="col-li">
                    <span style="margin-right: 5px">门店名称</span>
                    <el-select size="small" @change="searchList" v-model="search.store_id" placeholder="请选择门店">
                        <el-option value="" label="全部门店"></el-option>
                        <el-option
                                v-for="(item, index) in storeList"
                                :key="index"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </div>
            </el-col>
            <!-- 列表 -->
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="name" label="收银员">
                    <template slot-scope="scope">
                        <span>{{scope.row.number}}</span>
                        <span> {{scope.row.name}}</span>
                        <el-tooltip class="item" effect="dark" :content="scope.row.store_name"
                                    placement="top">
                            <div style="display: block;white-space: nowrap;overflow: hidden;text-overflow: ellipsis">
                                {{scope.row.store_name}}
                            </div>
                        </el-tooltip>
                    </template>
                </el-table-column>
                <el-table-column prop="start_time" label="上班时间"></el-table-column>
                <el-table-column prop="end_time" label="交班时间"></el-table-column>
                <el-table-column prop="total_pay_money" label="订单收款总额">
                    <template slot-scope="scope">
                        ￥{{scope.row.total_pay_money}}
                    </template>
                </el-table-column>
                <el-table-column prop="total_recharge_money" label="会员充值总额">
                    <template slot-scope="scope">
                        ￥{{scope.row.total_recharge_money}}
                    </template>
                </el-table-column>
                <el-table-column prop="refund_money" label="退款总额">
                    <template slot-scope="scope">
                        ￥{{scope.row.refund_money}}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="180" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" @click="showPrintDialog(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="打印小票" placement="top">
                                <img src="statics/img/mall/order/print.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" type="text"
                                   @click="edit(scope.row)"
                                   circle>
                            <el-tooltip class="item" effect="dark" content="详情" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <app-select-print v-model="hasPrintStatus"
                              obtain-url="plugin/teller/mall/printer/options"
                              request-url="plugin/teller/mall/shifts/print"
                              :extra-params="extraParams"
            ></app-select-print>
        </div>

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
                extraParams: {},
                search: {
                    time: [],
                    start_time: '',
                    end_time: '',
                    cashier_id: '',
                    store_id: '',
                },
                exportList: [],
                pagination: null,
                page: 1,
                listLoading: false,
                cashierList: [],
                storeList: [],
                btnLoading: false,
                list: [],
                hasPrintStatus: false,
            };
        },
        mounted() {
            this.getList();
            this.getCashier();
            this.getStore();
        },
        watch: {
            'search.time'(newData, oldData) {
                if (newData && newData.length) {
                    this.search.start_time = newData[0];
                    this.search.end_time = newData[1];
                } else {
                    this.search.start_time = '';
                    this.search.end_time = '';
                }
            }
        },
        methods: {
            showPrintDialog(column) {
                this.extraParams = {
                    work_log_id: parseInt(column.id),
                }
                this.hasPrintStatus = true;
            },
            confirmSubmit() {
                console.log('export click');
            },
            getCashier() {
                request({
                    params: {
                        r: 'plugin/teller/mall/cashier/index',
                        page_size: 999,
                    },
                    method: 'get',
                }).then(e => {
                    this.cashierList = e.data.data.list;
                });
            },
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
                request({
                    params: {
                        r: 'plugin/teller/mall/shifts/index',
                    },
                    data: Object.assign({}, {
                        page: this.page,
                    }, this.search),
                    method: 'POST',
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.exportList = e.data.data.export_list;
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
                this.$nextTick(function () {
                    this.getList();
                })
            },
            edit(column) {
                navigateTo({
                    r: 'plugin/teller/mall/shifts/show',
                    id: column.id,
                })
            },
        }
    });
</script>
