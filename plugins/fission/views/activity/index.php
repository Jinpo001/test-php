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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>红包墙活动</span>
            <el-button @click="$navigate({r:'plugin/fission/mall/activity/edit'})" style="margin: -5px 0" type="primary"
                       size="small">新建活动
            </el-button>
        </div>
        <div class="table-body">
            <!-- 状态 -->
            <el-tabs v-model="search.status" @tab-click="searchList">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="未开始" name="0"></el-tab-pane>
                <el-tab-pane label="进行中" name="1"></el-tab-pane>
                <el-tab-pane label="已结束" name="2"></el-tab-pane>
                <el-tab-pane label="下架中" name="3"></el-tab-pane>
            </el-tabs>
            <!--工具条 过滤表单和新增按钮-->
            <el-col :span="24" class="toolbar" style="padding-bottom: 0px">
                <div class="col-li">
                    <span style="margin-right: 5px">活动时间</span>
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
                <div class="input-item" style="display:inline-block;margin-left: 12px">
                    <el-input @keyup.enter.native="searchList"
                              size="small"
                              placeholder="请输入活动名称搜索"
                              v-model="search.keyword"
                              clearable
                              @clear="searchList">
                        <el-button slot="append" icon="el-icon-search" @click="searchList"></el-button>
                    </el-input>
                </div>
            </el-col>
            <!-- 列表 -->
            <el-col style="background: #f9f9f9;padding: 11px 22px">
                <el-button @click="upBatch('batch_up')" size="mini">上架</el-button>
                <el-button @click="upBatch('batch_down')" size="mini">下架</el-button>
                <el-button @click="upBatch('batch_delete')" size="mini">删除</el-button>
            </el-col>
            <el-table v-loading="listLoading" :data="list" @selection-change="handleSelectionChange" border>
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column prop="name" label="活动名称"></el-table-column>
                <el-table-column prop="total_count" label="领取红包总人数" width="120"></el-table-column>
                <el-table-column prop="total_money" label="领取红包总金额（元）" width="200"></el-table-column>
                <el-table-column prop="start_time" label="活动时间" width="180">
                    <template slot-scope="scope">
                        {{scope.row.start_time}} 至 {{scope.row.end_time}}
                    </template>
                </el-table-column>
                <el-table-column prop="status" label="活动状态" width="120">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.status == 0" type="info">未开始</el-tag>
                        <el-tag v-if="scope.row.status == 1">进行中</el-tag>
                        <el-tag v-if="scope.row.status == 2" type="info">已结束</el-tag>
                        <el-tag v-if="scope.row.status == 3" type="warning">下架中</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="280" fixed="right">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row)"
                                   size="small" circle v-if="scope.row.status != 2">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button size="mini" type="text"
                                   @click="$navigate({r:'plugin/fission/mall/log/index',activity_id:scope.row.id})"
                                   circle>
                            <el-tooltip class="item" effect="dark" content="红包墙记录" placement="top">
                                <img src="statics/img/mall/detail.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button class="set-el-button" size="mini" type="text" circle
                                   @click="operate(scope.row,'delete',scope.$index)">
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
                search: {
                    start_time: '',
                    end_time: '',
                    store_id: '',
                    time: null,
                    keyword: '',
                    status: '-1',
                },
                btnLoading: false,
                listLoading: false,
                list: [],
                pagination: null,
                page: 1,
                selectIds: [],
            };
        },
        mounted() {
            this.getList();
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
        methods: {
            upBatch(operate){
                if(!this.selectIds || !this.selectIds.length){
                    this.$message.warning('请勾选活动')
                    return;
                }
                let ids = this.selectIds.map(item => {
                    return item.id;
                })

                let title = operate !== 'batch_delete' ? operate === 'batch_up' ? '上架' : '下架' : '删除';
                this.$confirm(`批量${title}该条数据，是否继续？`, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            ids,
                            operate,
                            r: 'plugin/fission/mall/activity/operate',
                        },
                        method: 'get',
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            setTimeout(() => location.reload(), 500);
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch((e) => {
                    this.$message({type: 'info', message: `已取消${title}`});
                });

                console.log(ids);
            },
            handleSelectionChange(e) {
                this.selectIds = e;
            },
            getList() {
                this.listLoading = true;
                let params = Object.assign({}, {
                    r: 'plugin/fission/mall/activity/index',
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
                this.$nextTick(item => {
                    this.page = 1;
                    this.getList();
                });
            },
            edit(column) {
                navigateTo({
                    r: 'plugin/fission/mall/activity/edit',
                    id: column.id,
                })
            },
            operate(column, operate, index) {
                let title = operate !== 'delete' ? operate === 'up' ? '上架' : '下架' : '删除';
                this.$confirm(`${title}该条数据，是否继续？`, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            id: column.id,
                            operate,
                            r: 'plugin/fission/mall/activity/operate',
                        },
                        method: 'get',
                    }).then(e => {
                        this.listLoading = false;
                        if (e.data.code === 0) {
                            this.$message.success(e.data.msg);
                            if (operate === 'delete') {
                                this.list.splice(index, 1);
                            } else {
                                setTimeout(() => location.reload(), 500);
                            }
                        } else {
                            this.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                }).catch((e) => {
                    this.$message({type: 'info', message: `已取消${title}`});
                });
            },
        }
    });
</script>
