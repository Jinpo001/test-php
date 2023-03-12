<?php
Yii::$app->loadViewComponent('app-new-export-dialog-2');
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

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .col-li {
        display: inline-block;
        margin-right: 8px;
        margin-bottom: 12px;
    }

    .y-left-input .el-select .el-input {
        width: 130px;
    }

    .y-left-input .input-with-select .el-input-group__prepend {
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>红包墙记录</span>
            <app-new-export-dialog-2
                    action_url="plugin/fission/mall/log/index"
                    style="float: right;margin-top: -5px"
                    :field_list="export_list"
                    :params="search"
                    @selected="confirmSubmit">
            </app-new-export-dialog-2>
        </div>
        <div class="table-body">
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <div class="col-li">
                    <span style="margin-right: 5px">领取时间：</span>
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
                            <el-option label="活动名称" value="select_name"></el-option>
                            <el-option label="用户昵称" value="nickname"></el-option>
                            <el-option label="用户ID" value="user_id"></el-option>
                        </el-select>
                    </el-input>
                </div>
            </el-col>
            <!-- 列表 -->
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="name" label="活动名称"></el-table-column>
                <el-table-column prop="user" label="用户" width="180">
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
                <el-table-column prop="first_status" label="红包类型" width="100">
                    <template slot-scope="scope">
                        <span v-if="scope.row.first_status === 'cash'">现金</span>
                        <span v-if="scope.row.first_status === 'goods'">商品</span>
                        <span v-if="scope.row.first_status === 'coupon'">优惠券</span>
                        <span v-if="scope.row.first_status === 'card'">卡券</span>
                        <span v-if="scope.row.first_status === 'balance'">余额</span>
                        <span v-if="scope.row.first_status === 'integral'">积分</span>
                    </template>
                </el-table-column>
                <el-table-column prop="first_number" label="领取金额（元）" width="180">
                    <template slot-scope="scope">
                        <span v-if="scope.row.first_status === 'coupon'">-</span>
                        <span v-else>{{scope.row.first_number}}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="share_number" label="已发红包数量" width="110"></el-table-column>
                <el-table-column prop="last_share_number" label="剩余红包数量" width="110"></el-table-column>
                <el-table-column prop="created_at" label="当前关卡" width="110">
                    <template slot-scope="scope">
                        {{['','关卡一','关卡二','关卡三','关卡四','关卡五'][scope.row.current_level]}}
                    </template>
                </el-table-column>
                <el-table-column prop="rewards" label="奖品" min-width="200">
                    <template slot-scope="scope">
                        <div v-if="scope.row.rewards.status === 'goods'">{{scope.row.rewards.goods.name}}</div>
                        <div v-if="scope.row.rewards.status === 'coupon'">{{scope.row.rewards.coupon.name}}</div>
                        <div v-if="scope.row.rewards.status === 'card'">{{scope.row.rewards.card.name}}</div>
                        <div v-if="scope.row.rewards.status === 'cash'">￥{{scope.row.rewards.real_reward}}现金金额</div>
                        <div v-if="scope.row.rewards.status === 'balance'">{{scope.row.rewards.real_reward}}余额</div>
                        <div v-if="scope.row.rewards.status === 'integral'">{{scope.row.rewards.real_reward}}积分</div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="奖品领取时间" width="180"></el-table-column>
                <el-table-column label="操作" width="180" fixed="right">
                    <template slot-scope="scope">
                        <el-button size="mini" type="text"
                                   @click="$navigate({r:'plugin/fission/mall/log/detail',id: scope.row.id})"
                                   circle>
                            <el-tooltip class="item" effect="dark" content="详情" placement="top">
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
                export_list: [],
                search: {
                    activity_id: getQuery('activity_id'),
                    store_id: '',
                    keyword: '',
                    start_time: '',
                    end_time: '',
                    time: null,
                    keyword_name: 'select_name',
                },
                storeList: [],
                btnLoading: false,
                listLoading: false,
                list: [],
                pagination: null,
                page: 1,
            };
        },
        watch: {
            'search.time'(newData) {
                if (newData && newData.length) {
                    this.search.start_time = newData[0];
                    this.search.end_time = newData[1];
                } else {
                    this.search.start_time = '';
                    this.search.end_time = '';
                }
            }
        },
        mounted() {
            this.getList();
        },
        methods: {
            confirmSubmit(e) {

            },
            getList() {
                this.listLoading = true;
                let params = Object.assign({}, {
                    r: 'plugin/fission/mall/log/index',
                    page: this.page,
                }, this.search);
                request({
                    params,
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        this.export_list = e.data.data.export_list
                    }
                });
            },
            pageChange(page) {
                this.page = page;
                this.getList()
            },
            searchList() {
                this.$nextTick(item => {
                    this.page = 1;
                    this.getList();
                })
            },
            edit(column) {
                navigateTo({
                    r: 'plugin/fission/mall/edit/detail',
                    id: column.id,
                })
            },
            destroy(params, index) {
                this.$confirm('是否删除该记录', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/fission/mall/log/delete',
                        },
                        data: {
                            activity_log_id: params.id,
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
