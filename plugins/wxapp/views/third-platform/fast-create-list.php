<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-body .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .t-omit-two {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        white-space: normal !important;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="header-box">
            <el-breadcrumb separator="/" style="display: inline-block">
                <el-breadcrumb-item>
                <span style="color: #409EFF;cursor: pointer"
                      @click="$navigate({r:'plugin/wxapp/wx-app-config/setting'})">
                    小程序配置
                </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>提交记录</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="table-body">
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column prop="name" label="企业名称" width=""></el-table-column>
                <el-table-column prop="code_type" :formatter="codeTypeFormat" label="代码类型" min-width=""
                                 width=""></el-table-column>
                <el-table-column prop="code" label="企业代码"></el-table-column>
                <el-table-column prop="legal_persona_wechat" label="法人微信号" width=""></el-table-column>
                <el-table-column prop="legal_persona_name" label="法人姓名" width=""></el-table-column>
                <el-table-column prop="component_phone" label="联系电话" width=""></el-table-column>
                <el-table-column prop="status_text" label="状态" width=""></el-table-column>
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
                listLoading: false,
                list: [],
                page: 1,
                pagination: null,
            };
        },
        mounted() {
            this.getList();
        },
        methods: {
            codeTypeFormat(e) {
                const data = {
                    '': '未知',
                    1: '统一社会信用代码(18 位)',
                    2: '组织机构代码(9 位 xxxxxxxx-x)',
                    3: '营业执照注册号(15 位)',
                }
                return data[e.code_type];
            },
            pageChange(currentPage) {
                this.page = currentPage;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/wxapp/third-platform/fast-create-list',
                        page: this.page,
                    }
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        }
    });
</script>
