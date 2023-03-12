<?php
Yii::$app->loadViewComponent('app-new-export-dialog-2');
Yii::$app->loadViewComponent('order/app-send');
Yii::$app->loadViewComponent('order/app-add-order');
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
    .col-li {
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 12px;
    }
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                 <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/fission/mall/log/index'})">
                    红包墙记录
                 </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item>详情</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <!-- 活动详情 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>活动详情</span>
            </div>
        </div>
        <div v-loading="listLoading" class="form-body">
            <div class="d-text" flex="dir:left cross:center">
                <span>活动名称：{{activityData.name}}</span>
            </div>
            <el-table :data="[activityData]" style="width: 80%;margin-top: 24px" border>
                <el-table-column label="用户">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <app-image :src="scope.row.avatar" style="flex-shrink: 0"></app-image>
                            <div flex="dir:top" style="margin-left: 12px">
                                <div>{{scope.row.nickname}}</div>
                                <el-tooltip class="item" effect="dark" :content="scope.row.platform_text"
                                            placement="top">
                                    <app-image style="height: 24px;width: 24px;cursor: pointer"
                                               :src="scope.row.platform_icon"></app-image>
                                </el-tooltip>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="share_number" label="已发红包数量"></el-table-column>
                <el-table-column prop="last_share_number" label="剩余红包数量"></el-table-column>
                <el-table-column prop="created_at" label="参与时间"></el-table-column>
            </el-table>
            <div class="d-text" style="margin-top: 24px">
                <div>关卡详情</div>
            </div>
            <el-table :data="activityData.rewards" style="width: 70%;margin-top: 24px" border>
                <el-table-column prop="type" label="关卡详情">
                    <template slot-scope="scope">
                        {{['参与红包','第一关卡','第二关卡','第三关卡','第四关卡','第五关卡'][scope.row.type]}}
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="奖品">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status === 'cash'">{{scope.row.real_reward}}元现金红包</div>
                        <div v-if="scope.row.status === 'integral'">{{scope.row.real_reward}}积分</div>
                        <div v-if="scope.row.status === 'balance'">{{scope.row.real_reward}}余额</div>
                        <div v-if="scope.row.status === 'coupon'">{{scope.row.coupon.name}}</div>
                        <div v-if="scope.row.status === 'card'">{{scope.row.card.name}}</div>
                        <div v-if="scope.row.status === 'goods'">
                            <div flex="dir:left">
                                <app-image :src="scope.row.goods.cover_pic" style="flex-shrink: 0;margin-right: 12px"></app-image>
                                <div style="display: block;white-space: nowrap; width: 100%;overflow: hidden; text-overflow: ellipsis;">
                                    {{scope.row.goods.name}}
                                </div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="cash_recharge" label="状态">
                    <template slot-scope="scope">
                        {{['未兑换','已发放'][scope.row.is_exchange]}}
                    </template>
                </el-table-column>
                <el-table-column prop="order_no" label="订单号">
                    <template slot-scope="scope">
                        <el-link type="primary" :underline="false"
                                 @click="$navigate({r:`mall/order/detail`, order_id: scope.row.order_id},true)"
                        >{{scope.row.order_no}}
                        </el-link>
                    </template>
                </el-table-column>
                <el-table-column prop="alipay_recharge" label="操作">
                    <template slot-scope="scope">
                        <el-button size="mini" type="text" @click="sendCash(scope.row)" circle
                                   v-if="scope.row.status === 'cash' && scope.row.is_exchange == 0">
                            <el-tooltip class="item" effect="dark" content="确认发放" placement="top">
                                <img src="statics/img/mall/pass.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="openOrder(scope.row,scope.$index)" size="mini" type="text" circle
                                   v-if="scope.row.status === 'goods'
                                   && scope.row.is_exchange == 0
                                   && scope.row.exchange_type == 'offline'"
                                   >
                            <el-tooltip class="item" effect="dark" content="生成订单" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>

                        <el-button @click="sendOrderModel(scope.row)" size="mini" type="text" circle
                                   v-if="scope.row.status === 'goods'
                                   && scope.row.is_exchange == 1
                                   && scope.row.is_send == 0
                                   && scope.row.exchange_type == 'offline'"
                        >
                            <el-tooltip class="item" effect="dark" content="发货" placement="top">
                                <img src="statics/img/mall/order/send.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </el-card>
    <app-add-order ref="order" @success="addOrderSuccess"></app-add-order>
    <app-send
            @close="sendClose"
            @submit="sendSubmit"
            :is-show="sendVisible"
            send-type="0"
            express-id="0"
            :order="sendOrder">
    </app-send>
    <!-- 参与者详情 -->
    <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
        <div slot="header">
            <div style="he">
                <span>参与者详情</span>
                <app-new-export-dialog-2
                        action_url="plugin/fission/mall/log/invite"
                        style="float: right;margin-top: -5px"
                        :field_list="invite_export_list"
                        :params="invite_search"
                        @selected="invite_confirmSubmit">
                </app-new-export-dialog-2>
            </div>
        </div>
        <div class="form-body">
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <div class="col-li">
                    <span style="margin-right: 5px">参与时间：</span>
                    <el-date-picker
                            class="item-box date-picker"
                            size="small"
                            @change="invite_searchList"
                            v-model="invite_search.time"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </div>
                <div class="col-li">
                    <div class="input-item" style="display:inline-block;margin-left: 12px">
                        <el-input @keyup.enter.native="invite_searchList"
                                  size="small"
                                  placeholder="请输入用户名或用户ID搜索"
                                  v-model="invite_search.keyword"
                                  clearable
                                  @clear="invite_searchList">
                            <el-button slot="append" icon="el-icon-invite_search"
                                       @click="invite_searchList"></el-button>
                        </el-input>
                    </div>
                </div>
            </el-col>
            <el-table ref="goods" @selection-change="invite_select_change" v-loading="invite_listLoading"
                      :data="invite_list"
                      style="width: 100%" border>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="用户">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <app-image :src="scope.row.avatar" style="flex-shrink: 0"></app-image>
                            <div flex="dir:top" style="margin-left: 12px">
                                <div>{{scope.row.nickname}}</div>
                                <el-tooltip class="item" effect="dark" :content="scope.row.platform_text"
                                            placement="top">
                                    <app-image style="height: 24px;width: 24px;cursor: pointer"
                                               :src="scope.row.platform_icon"></app-image>
                                </el-tooltip>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="real_reward" label="红包金额">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status === 'coupon'">-</span>
                        <span v-else>{{scope.row.real_reward}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="参与时间"></el-table-column>
            </el-table>
        </div>
        <!--工具条 批量操作和分页-->
        <el-col :span="24" class="toolbar">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    @current-change="invite_pageChange"
                    :page-size="invite_pagination.pageSize"
                    :total="invite_pagination.total_count"
                    style="float:right;margin-bottom:15px"
                    v-if="invite_pagination">
            </el-pagination>
        </el-col>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                sendVisible: false,
                sendOrder: null,
                activityData: {
                    name: '',
                },
                listLoading: false,
                invite_export_list: [],
                invite_listLoading: false,
                invite_list: [],
                invite_pagination: null,
                invite_page: 1,
                invite_search: {
                    start_time: null,
                    end_time: null,
                    time: null,
                    activity_log_id: getQuery('id'),
                    keyword: '',
                    choose_list: [],
                },
                tempIndex: 0,
                tempSend: null,
            }
        },
        watch: {
            'invite_search.time'(newData) {
                if (newData && newData.length) {
                    this.invite_search.start_time = newData[0];
                    this.invite_search.end_time = newData[1];
                } else {
                    this.invite_search.start_time = '';
                    this.invite_search.end_time = '';
                }
            }
        },
        methods: {
            sendOrderModel(column) {
                this.tempSend = column;
                this.getDetail(column.order_id);
            },
            /*************************************************/
            addOrderSuccess(e) {
                this.getData();
            },
            getDetail(order_id) {
                request({
                    params: {
                        r: 'mall/order/detail',
                        order_id: order_id,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.sendVisible = true;
                        this.sendOrder = e.data.data.order;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            sendClose() {
                this.sendVisible = false;
            },
            sendSubmit(e) {
                this.$message.success('发货成功');
                Object.assign(this.tempSend, {is_send: 1});
                this.sendVisible = false;
            },

            openOrder(column, index) {
                this.tempIndex = index;
                let user = {
                    id: this.activityData.user_id,
                    nickname: this.activityData.nickname,
                }
                let preview_data = {
                    user_id: this.activityData.user_id,
                    list: [{
                        reward_log_id: column.reward_log_id,
                        mch_id: 0,
                        goods_list: [{
                            id: column.model_id,
                            attrs: [column.goods.attr_list],
                            num: 1,
                            cat_id: 0,
                            goods_attr_id: column.attr_id,
                            cart_id: 0,
                            form_data: []
                        }],
                        distance: 0,
                        remark: "",
                        order_form: [],
                        use_integral: 0,
                        user_coupon_id: 0,
                        store_id:0,
                        store_name: '',
                        store: [],
                        send_type: "express"
                    }],
                    address_id: 0,
                    send_type: "",
                    // express: {
                    //     is_express: '1',
                    //     order_id: '',
                    //     express_no: '',
                    //     code: '',
                    //     mch_id: 0,
                    //     express_content: '',
                    //     express: '',
                    //     merchant_remark: '',
                    //     order_detail_id: [],
                    //     customer_name: '',
                    // },
                    address: {
                        name: '',
                        longitude: '',
                        location: '',
                        mobile: '',
                        province_id: 0,
                        province: '',
                        city_id: 0,
                        city: '',
                        district_id: 0,
                        district: '',
                        latitude: '',
                        detail: '',
                    }
                };
                this.$refs.order.openDialog(user, preview_data);
            },
            invite_select_change(e) {
                this.invite_search.choose_list = e.map(item => {
                    return item.id;
                });
            },
            invite_confirmSubmit() {
                console.log('export click');
            },
            invite_pageChange(invite_page) {
                this.invite_page = invite_page;
                this.invite_getData();
            },
            invite_searchList() {
                this.$nextTick(() => {
                    this.invite_page = 1;
                    this.invite_getData();
                })
            },
            invite_getData() {
                let params = Object.assign({}, {
                    r: 'plugin/fission/mall/log/invite',
                    page: this.invite_page,
                }, this.invite_search);
                this.invite_listLoading = true;
                request({
                    params,
                }).then(e => {
                    this.invite_listLoading = false;
                    if (e.data.code === 0) {
                        this.invite_list = e.data.data.list;
                        this.invite_export_list = e.data.data.export_list;
                        this.invite_pagination = e.data.data.pagination;
                    }
                    this.invite_listLoading = false;
                }).catch(e => {
                    this.invite_listLoading = false;
                })
            },
            sendCash(column) {
                this.$confirm('是否已发放红包?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/fission/mall/log/cash',

                        },
                        data: {
                            reward_log_id: column.reward_log_id,
                            user_id: this.activityData.user_id
                        },
                        method: 'POST',
                    }).then(e => {
                        if (e.data.code === 0) {
                            column.is_exchange = 1;
                            this.$message.success(e.data.msg);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                        this.listLoading = false;
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消'
                    });
                });
            },
            getData() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/fission/mall/log/detail',
                        activity_log_id: getQuery('id'),
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.activityData = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            this.getData();
            this.invite_getData();
        }
    });
</script>
