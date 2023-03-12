<?php
?>
<style>
    .y-left-input .el-select .el-input {
        width: 130px;
    }

    .y-left-input .input-with-select .el-input-group__prepend {
        background-color: #fff;
    }

    .col-li {
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 12px;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span style="margin: auto 0">业绩明细</span>
            <app-new-export-dialog-2 action_url="plugin/teller/mall/push/index"
                                :field_list="exportList"
                                :params="search"
                                @selected="confirmSubmit">
            </app-new-export-dialog-2>
        </div>
        <div class="table-body">
            <div class="toolbar" style="background: #FFFFFF">
                <div class="col-li">
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
                <div class="col-li">
                    <span style="margin-right: 5px">下单时间</span>
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
                    <el-input placeholder="请输入搜索内容" size="small" v-model="search.keyword_value"
                              clearable
                              class="y-left-input"
                              @clear="searchList"
                              @keyup.enter.native="searchList">
                        <el-select v-model="search.keyword_name" slot="prepend">
                            <el-option label="姓名" value="name"></el-option>
                            <el-option label="电话" value="mobile"></el-option>
                            <el-option :label="search.user_type === 'sales' ? '导购员编号': '收银员编号'" value="number"></el-option>
                            <el-option label="订单号" value="order_no"></el-option>
                        </el-select>
                    </el-input>
                </div>

                <div class="col-li">
                    <span style="margin-right: 5px">类型</span>
                    <el-select size="small" v-model="search.order_type" @change="searchList">
                        <el-option label="全部" value=""></el-option>
                        <el-option label="买单" value="order"></el-option>
                        <el-option label="会员充值" value="recharge"></el-option>
                    </el-select>
                </div>

                <div class="col-li">
                    <span style="margin-right: 5px">付款方式</span>
                    <el-select size="small" v-model="search.pay_type" @change="searchList">
                        <el-option label="全部" value=""></el-option>
                        <el-option label="现金" value="cash"></el-option>
                        <el-option label="微信" value="wechat_scan"></el-option>
                        <el-option label="支付宝" value="alipay_scan"></el-option>
                        <el-option label="余额" value="balance"></el-option>
                        <el-option label="POS机" value="pos"></el-option>
                    </el-select>
                </div>
            </div>

            <el-tabs v-model="search.user_type" @tab-click="searchList" style="background: #FFFFFF">
                <el-tab-pane label="收银员" name="cashier"></el-tab-pane>
                <el-tab-pane label="导购员" name="sales"></el-tab-pane>
            </el-tabs>
            <!-- 列表 -->
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="created_at" label="下单时间"></el-table-column>
                <el-table-column prop="order_no" label="订单号">
                    <template slot-scope="scope">
                        <el-link v-if="scope.row.order_type === '买单'"
                                type="primary" :underline="false"
                                 @click="$navigate({r:`mall/order/detail`, order_id: scope.row.order_id},true)"
                        >{{scope.row.order_no}}
                        </el-link>
                        <el-link v-if="scope.row.order_type === '会员充值'"
                                 type="primary" :underline="false"
                                 @click="$navigate({r:`mall/user/balance-log`, keyword: scope.row.order_no},true)"
                        >{{scope.row.order_no}}
                        </el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status === '已完成'" type="success">已完成</el-tag>
                        <el-tag v-else type="warning">未完成</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="name" :label="search.user_type === 'sales' ? '导购员': '收银员'">
                    <template slot-scope="scope">
                        <span>{{scope.row.number}} {{scope.row.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="order_type" label="类型"></el-table-column>
                <el-table-column prop="pay_type" label="付款方式"></el-table-column>
                <el-table-column prop="total_pay_price" label="实收金额(元)"></el-table-column>
                <el-table-column prop="refund_money" label="退款金额(元)"></el-table-column>
                <el-table-column prop="push_money" label="提成(元)"></el-table-column>
            </el-table>
        </div>
        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next"
                    @current-change="pageChange"
                    :current-page="pagination.current_page"
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
                    time: [],
                    start_date: '',
                    end_date: '',
                    keyword_name: getQuery('keyword_name') ? getQuery('keyword_name') : 'name',
                    keyword_value: getQuery('keyword_value'),
                    pay_type: '',
                    order_type: '',
                    user_type: 'cashier'
                },
                exportList: [],
                storeList: [],
                listLoading: false,
                list: [],
                pagination: null,
                page: 1,
            };
        },
        mounted() {
            if (getQuery('user_type')) {
                this.search.user_type = getQuery('user_type')
            }
            this.getList();
            this.getStore();
        },
        watch: {
            'search.time'(newData, oldData) {
                if (newData && newData.length) {
                    this.search.start_date = newData[0];
                    this.search.end_date = newData[1];
                } else {
                    this.search.start_date = '';
                    this.search.end_date = '';
                }
            }
        },
        methods: {
            getStore() {
                request({
                    params: {
                        r: 'mall/store/index',
                        limit: 99999,
                    },
                    method: 'get',
                }).then(e => {
                    this.storeList = e.data.data.list;
                });
            },
            confirmSubmit() {
                console.log('export click');
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            searchList() {
                this.page = 1;
                this.$nextTick(function () {
                    this.getList();
                });
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/teller/mall/push/index',
                    },
                    method: 'POST',
                    data: Object.assign({
                        page: this.page,
                    }, this.search)
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.exportList = e.data.data.export_list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        }
    });
</script>
