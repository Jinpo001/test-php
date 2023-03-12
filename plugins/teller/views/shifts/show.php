<?php
Yii::$app->loadViewComponent('order/app-select-print');
?>
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

    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .y-card {
        border: 0;
        margin-bottom: 12px;
    }

    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .d-text {
        color: #606266;
    }

    .d-text > div {
        margin: 24px 0;
    }

    .d-text > div:nth-of-type(1) {
        margin-top: 0px;
    }
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                 <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/teller/mall/shifts/index'})">
                    交班记录
                 </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item>交班详情</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <app-select-print v-model="hasPrintStatus"
                      obtain-url="plugin/teller/mall/printer/options"
                      request-url="plugin/teller/mall/shifts/print"
                      :extra-params="extraParams"
    ></app-select-print>

    <!-- 交班详情 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>交班详情</span>
                <el-button type="primary" size="small" style="float: right;margin-top: -5px" @click="showPrintDialog">
                    打印交班小票
                </el-button>
            </div>
        </div>
        <div v-loading="detail_listLoading" class="form-body">
            <div class="d-text">
                <div>班次：{{detail_list.start_time}} ~ {{detail_list.end_time}}</div>
                <div>收银员编号：{{detail_list.number}}</div>
                <div>姓名：{{detail_list.name}}</div>
            </div>
            <el-table :data="[detail_list.proceeds]" style="width: 80%;margin-top: 24px" border>
                <el-table-column prop="total_proceeds" label="收款总额(元)"></el-table-column>
                <el-table-column prop="total_order" label="订单数"></el-table-column>
                <el-table-column prop="cash_proceeds" label="现金"></el-table-column>
                <el-table-column prop="wechat_proceeds" label="微信"></el-table-column>
                <el-table-column prop="alipay_proceeds" label="支付宝"></el-table-column>
                <el-table-column prop="balance_proceeds" label="余额"></el-table-column>
                <el-table-column prop="pos_proceeds" label="POS机"></el-table-column>
            </el-table>
            <el-table :data="[detail_list.recharge]" style="width: 70%;margin-top: 24px" border>
                <el-table-column prop="total_recharge" label="充值总额(元)"></el-table-column>
                <el-table-column prop="total_order" label="订单数"></el-table-column>
                <el-table-column prop="cash_recharge" label="现金"></el-table-column>
                <el-table-column prop="wechat_recharge" label="微信"></el-table-column>
                <el-table-column prop="alipay_recharge" label="支付宝"></el-table-column>
                <el-table-column prop="pos_recharge" label="POS机"></el-table-column>
            </el-table>
            <el-table :data="[detail_list.refund]" style="width: 70%;margin-top: 24px" border>
                <el-table-column prop="total_refund" label="退款总额(元)"></el-table-column>
                <el-table-column prop="total_order" label="订单数"></el-table-column>
                <el-table-column prop="cash_refund" label="现金"></el-table-column>
                <el-table-column prop="wechat_refund" label="微信"></el-table-column>
                <el-table-column prop="alipay_refund" label="支付宝"></el-table-column>
                <el-table-column prop="balance_refund" label="余额"></el-table-column>
                <el-table-column prop="pos_refund" label="POS机"></el-table-column>
            </el-table>
        </div>
    </el-card>

    <!-- 商品汇总 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div style="he">
                <span>商品汇总</span>
                <app-new-export-dialog-2
                        action_url="plugin/teller/mall/shifts/goods"
                        style="float: right;margin-top: -5px"
                        :field_list="goods_export_list"
                        :params="goods_search"
                        @selected="goods_confirmSubmit">
                </app-new-export-dialog-2>
            </div>
        </div>
        <div class="form-body">
            <el-col :span="24" class="toolbar" style="padding-bottom: 10px">
                <div class="input-item" style="display:inline-block;margin-left: 12px">
                    <el-input @keyup.enter.native="goods_searchList"
                              size="small"
                              placeholder="请输入商品ID或者名称搜索"
                              v-model="goods_search.keyword"
                              clearable
                              @clear="goods_searchList">
                        <el-button slot="append" icon="el-icon-search" @click="goods_searchList"></el-button>
                    </el-input>
                </div>
            </el-col>

            <el-table ref="goods" @selection-change="goodsSelectionChange" v-loading="goods_listLoading" :data="goods_list" style="width: 100%" border>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="goods_id" sortable label="商品ID"></el-table-column>
                <el-table-column prop="id" label="商品名称" width="700">
                    <template slot-scope="scope">
                        <div flex="dir:left">
                            <div style="flex-grow: 0">
                                <app-image :src="scope.row.cover_pic"></app-image>
                            </div>
                            <div style="margin-left: 12px" flex="dir:top">
                                <span>{{scope.row.name}}</span>
                                <div>
                                    <span>规格：</span>
                                    <el-tag size="small" style="margin-right: 3px;margin-bottom: 3px"
                                            v-for="i of scope.row.attr">
                                        {{i}}
                                    </el-tag>
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="goods_price" :formatter="goodsPriceFormatter" label="售价"></el-table-column>
                <el-table-column prop="num" label="数量"></el-table-column>
                <el-table-column prop="total_price" :formatter="totalPriceFormatter" label="商品小计"></el-table-column>
            </el-table>
        </div>

        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    @current-change="goods_pageChange"
                    :page-size="goods_pagination.pageSize"
                    :total="goods_pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="goods_pagination">
            </el-pagination>
        </el-col>
    </el-card>
    <!-- 操作明细 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>操作明细</span>
                <app-new-export-dialog-2
                        action_url="plugin/teller/mall/shifts/orders"
                        style="float: right;margin-top: -5px"
                        :field_list="info_export_list"
                        :params="info_search"
                        @selected="info_confirmSubmit">
                </app-new-export-dialog-2>
            </div>
        </div>
        <div class="form-body">
            <el-col :span="24" class="toolbar" style="padding-bottom: 20px">
                <div class="col-li">
                    <span style="margin-right: 5px">类型</span>
                    <el-select size="small" @change="info_searchList" v-model="info_search.order_type"
                               placeholder="请选择类型">
                        <el-option value="" label="全部"></el-option>
                        <el-option value="order" label="买单"></el-option>
                        <el-option value="recharge" label="会员充值"></el-option>
                    </el-select>
                </div>
            </el-col>
            <el-table ref="info" @selection-change="infoSelectionChange" v-loading="info_listLoading" :data="info_list" style="width: 100%" border>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="created_at" label="操作时间"></el-table-column>
                <el-table-column prop="order_id" label="订单号">
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
                <el-table-column prop="order_type" label="类型"></el-table-column>
                <el-table-column prop="pay_type" label="付款方式"></el-table-column>
                <el-table-column prop="total_pay_price" label="金额（元）"></el-table-column>
            </el-table>
        </div>

        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    @current-change="info_pageChange"
                    :page-size="info_pagination.pageSize"
                    :total="info_pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="info_pagination">
            </el-pagination>
        </el-col>
    </el-card>
    <!-- 退款明细 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>退款明细</span>
                <app-new-export-dialog-2
                        action_url="plugin/teller/mall/shifts/refund-orders"
                        style="float: right;margin-top: -5px"
                        :field_list="re_export_list"
                        :params="re_search"
                        @selected="re_confirmSubmit">
                </app-new-export-dialog-2>
            </div>
        </div>
        <div class="form-body">
            <el-table ref="re" @selection-change="reSelectionChange" v-loading="re_listLoading" :data="re_list"
                      style="width: 100%" border>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="created_at" label="退款时间"></el-table-column>
                <el-table-column prop="order_id" label="订单号">
                    <template slot-scope="scope">
                        <el-link type="primary" :underline="false"
                                 @click="$navigate({r:`mall/order/refund-detail`, refund_order_id: scope.row.refund_order_id},true)"
                        >{{scope.row.order_no}}
                        </el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="refund_type" label="退款方式"></el-table-column>
                <el-table-column prop="refund_price" label="金额（元）"></el-table-column>
            </el-table>
        </div>

        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    @current-change="re_pageChange"
                    :page-size="re_pagination.pageSize"
                    :total="re_pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="re_pagination">
            </el-pagination>
        </el-col>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                detail_list: {},
                hasPrintStatus: false,
                extraParams: {},

                id: getQuery('id'),
                detail_listLoading: false,

                goods_export_list: [],
                goods_listLoading: false,
                goods_list: [],
                goods_pagination: null,
                goods_page: 1,
                goods_search: {
                    id: getQuery('id'),
                    keyword: '',
                    ids: [],
                },

                info_export_list: [],
                info_listLoading: false,
                info_list: [],
                info_pagination: null,
                info_page: 1,
                info_search: {
                    id: getQuery('id'),
                    order_type: '',
                    ids: [],
                },
                re_export_list: [],
                re_listLoading: false,
                re_list: [],
                re_pagination: null,
                re_page: 1,
                re_search: {
                    id: getQuery('id'),
                    ids: [],
                }
            }
        },

        methods: {
            goodsSelectionChange(e) {
                this.goods_search.ids = e.map(item => {
                    return item.order_detail_id;
                });
            },
            infoSelectionChange(e) {
                this.info_search.ids = e.map(item => {
                    return item.teller_order_id;
                });
            },
            reSelectionChange(e) {
                this.re_search.ids = e.map(item => {
                    return item.refund_order_id;
                });
            },
            goodsPriceFormatter(row) {
                return '￥' + row.goods_price;
            },
            totalPriceFormatter(row) {
                return '￥' + row.total_price;
            },

            showPrintDialog() {
                this.extraParams = {
                    work_log_id: getQuery('id'),
                };
                this.hasPrintStatus = true;
            },

            info_confirmSubmit() {
                console.log('export click');
            },
            info_pageChange(page) {
                this.info_page = page;
                this.info_getData();
            },
            info_searchList() {
                this.info_page = 1;
                this.info_getData();
            },
            info_getData() {
                let params = Object.assign({}, {
                    r: 'plugin/teller/mall/shifts/orders',
                    id: getQuery('id'),
                    page: this.info_page,
                }, this.info_search);
                this.info_listLoading = true;
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.info_list = e.data.data.list;
                        this.info_pagination = e.data.data.pagination;
                        this.info_export_list = e.data.data.export_list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.info_listLoading = false;
                }).catch(e => {
                    this.info_listLoading = false;
                })
            },

            goods_confirmSubmit() {
                console.log('export click');
            },
            goods_pageChange(page) {
                this.goods_page = page;
                this.goods_getData();
            },
            goods_searchList() {
                this.goods_page = 1;
                this.goods_getData();
            },
            goods_getData() {
                let params = Object.assign({}, {
                    r: 'plugin/teller/mall/shifts/goods',
                    id: getQuery('id'),
                    page: this.goods_page,
                }, this.goods_search);
                this.goods_listLoading = true;
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.goods_list = e.data.data.list;
                        this.goods_export_list = e.data.data.export_list;
                        this.goods_pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.goods_listLoading = false;
                }).catch(e => {
                    this.goods_listLoading = false;
                })
            },
            re_confirmSubmit() {
                console.log('export click');
            },
            re_pageChange(page) {
                this.re_page = page;
                this.re_getData();
            },
            re_searchList() {
                this.re_page = 1;
                this.re_getData();
            },
            re_getData() {
                let params = Object.assign({}, {
                    r: 'plugin/teller/mall/shifts/refund-orders',
                    id: getQuery('id'),
                    page: this.re_page,
                }, this.re_search);
                this.re_listLoading = true;
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.re_list = e.data.data.list;
                        this.re_export_list = e.data.data.export_list;
                        this.re_pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.re_listLoading = false;
                }).catch(e => {
                    this.re_listLoading = false;
                })
            },

            getData() {
                this.detail_listLoading = true;
                request({
                    params: {
                        r: 'plugin/teller/mall/shifts/show',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.detail_listLoading = false;
                    if (e.data.code === 0) {
                        this.detail_list = e.data.data.detail;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.detail_listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getData();
            this.info_getData();
            this.goods_getData();
            this.re_getData();
        }
    });
</script>
