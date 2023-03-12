<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/15
 * Time: 18:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
?>
<?php
Yii::$app->loadViewComponent("app-market", __DIR__);
Yii::$app->loadViewComponent('app-setting-index');
?>
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

    .app-setting-dialog .input-item {
        display: inline-block;
        width: 250px;
        margin-bottom: 40px;
    }

    .app-setting-dialog .input-item .el-input__inner {
        border-right: 0;
    }

    .app-setting-dialog .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-setting-dialog .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-setting-dialog .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-setting-dialog .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-setting-dialog .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-setting-dialog .member-item {
        height: 40px;
    }

    .app-setting-dialog .no-list-tip {
        width: 100%;
        text-align: center;
        font-size: 16px;
        padding: 10px 0;
        color: #999999;
    }

    .input-item {
        display: inline-block;
        width: 250px;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" flex="dir:left" style="justify-content:space-between;">
            <span>微页面</span>
            <app-market list-url="plugin/diy/mall/template/market-search"
                        edit-url="plugin/diy/mall/template/edit" type="page">
                <el-button style="margin: -5px 0" type="primary" size="small">新建微页面
                </el-button>
            </app-market>
        </div>
        <div id="NewsToolBox"></div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入微页面ID或名称搜索" v-model="keyword" clearable @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="listLoading" :data="list" border>
                <el-table-column label="ID" prop="id"></el-table-column>
                <el-table-column prop="name" label="名称">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <div>{{scope.row.name}}</div>
                            <el-tooltip placement="top" v-for="item in scope.row.platform" :key="item.icon">
                                <div slot="content">
                                        <span>{{item.text}}</span>
                                </div>
                                <img style="margin: 0 3px;width: 24px;height: 24px;" :src="item.icon" alt="">
                            </el-tooltip>
                        </div>
                        <!-- <el-tag v-if="scope.row.is_home_page == 1" effect="dark" size="mini">店铺首页</el-tag> -->
                    </template>
                </el-table-column>
                <!--<el-table-column prop="goodsCount" label="商品数"></el-table-column>-->
                <el-table-column :formatter="formatterCount" label="浏览人数/浏览人次"></el-table-column>
                <el-table-column prop="created_at" label="创建时间"></el-table-column>
                <el-table-column fixed="right" width="250" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" @click="edit(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="noticePoster(scope.row)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="推广" placement="top">
                                <img src="statics/img/mall/notice.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="settingIndex(scope.row)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="设为首页" placement="top">
                                <img src="statics/img/mall/list_icon_home.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" @click="openBrowse(scope.row)" size="small" circle>
                            <el-tooltip class="item" effect="dark" content="设置浏览权限" placement="top">
                                <img src="statics/img/mall/list_icon_eye.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button v-if="scope.row.platform.length == 0" type="text"
                                   @click="destroy(scope.row,scope.$index)"
                                   size="small" circle>
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <el-dialog title="设置浏览权限" :visible.sync="browseVisible" width="30%">
            <!-- 选择会员等级 -->
            <el-dialog class="app-setting-dialog" title="选择会员等级" :visible.sync="m_visible" width="30%"
                       @submit.native.prevent append-to-body>
                <el-form @submit.native.prevent size="small">
                    <div class="input-item">
                        <el-input @keyup.enter.native="m_toSearch" size="small" placeholder="请输入会员等级名称"
                                  v-model="m_search.keyword" clearable
                                  @clear="m_toSearch">
                            <el-button slot="append" icon="el-icon-search" @click="m_toSearch"></el-button>
                        </el-input>
                    </div>
                </el-form>
                <div v-loading="m_listLoading">
                    <div class="member-item" v-for="item in m_list" :key="item.id">
                        <el-checkbox v-model="item.checked" :label="item.id">
                            {{item.name}}
                        </el-checkbox>
                    </div>
                </div>
                <div class="no-list-tip" v-if="m_list.length == 0 && !m_listLoading">暂无会员等级</div>
                <el-pagination
                        v-if="m_pagination"
                        style="text-align: right;margin: 20px 0;"
                        @current-change=""
                        background
                        :current-page="m_pagination.current_page"
                        layout="prev, pager, next"
                        :page-count="m_pagination.page_count">
                </el-pagination>
                <span slot="footer" class="dialog-footer">
                    <el-button size="small" @click="m_visible = false">取 消</el-button>
                    <el-button size="small" type="primary" @click="submitMember">确 定</el-button>
                 </span>
            </el-dialog>

            <el-form ref="browseForm" :model="browseForm" label-width="130px" size="small">
                <el-form-item label="会员等级浏览权限">
                    <el-tag @close="clearMember(k)"
                            v-for="(i,k) of browseForm.access_limit.member" closable
                            style="margin:5px">{{i.name}}
                    </el-tag>
                    <el-button v-if="browseForm.access_limit.is_all == 0" size="small" @click="openMemberDialog">
                        选择会员等级
                    </el-button>
                    <el-checkbox style="margin-left: 10px" v-model="browseForm.access_limit.is_all" :true-label="1"
                                 @change="changeAccessLimit"
                                 :false-label="0">
                        所有用户
                    </el-checkbox>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="browseVisible = false">取 消</el-button>
                <el-button size="small" type="primary" @click="browseSubmit" :loading="browseBtnLoading">确 定</el-button>
            </div>
        </el-dialog>

        <!-- 推广 -->
        <el-dialog title="推广" :visible.sync="bgVisible" width="956px">
            <div flex="dir:left" v-loading="bgLoading">
                <div style="height: 480px;width: 270px">
                    <img style="height: 100%;width: 100%;display: block" :src="bgPosterForm.picUrl" alt="">
                </div>
                <el-form label-position="top" label-width="80px" style="margin-left: 50px">
                    <el-form-item label="复制小程序路径">
                        <el-input size="small" style="width: 500px" v-model="bgPosterForm.text" disabled>
                            <template slot="append">
                                <el-button type="primary" @click="copyInput">复制</el-button>
                            </template>
                        </el-input>
                        <div>
                            <a style="color:#409EFF;text-decoration:none"
                               :href="bgPosterForm.picUrl"
                               :download="downloadText">下载海报</a>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
        </el-dialog>

        <!--工具条 批量操作和分页-->
        <el-col  flex="dir:right" style="margin: 20px;">
            <el-pagination
                    background
                    hide-on-single-page
                    layout="prev, pager, next, jumper"
                    @current-change="pageChange"
                    :page-size="pagination.pageSize"
                    :total="pagination.total_count"
                    >
            </el-pagination>
        </el-col>
        <app-setting-index :list="platform" :show="indexDialog" :loading="platformLoading" @cancel="cancel" @click="submitPlatform"></app-setting-index>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                keyword: '',
                platformLoading: false,
                indexDialog: false,
                platform: '',
                changeId: null,

                listLoading: false,
                list: [],
                page: 1,
                pagination: {},

                bgVisible: false,
                bgLoading: false,
                bgPosterForm: {
                    id: '',
                    text: '',
                    picUrl: '',
                    name: '',
                },
                browseVisible: false,
                browseForm: {access_limit: {}},
                browseBtnLoading: false,

                m_page: 1,
                m_list: [],
                m_visible: false,
                m_listLoading: false,
                m_pagination: null,
                m_search: {
                    keyword: ''
                },
            };
        },
        created() {
            this.syncTemplateType();
        },
        mounted() {
            this.getList();
        },
        computed: {
            downloadText() {
                return this.bgPosterForm.name + '-' + this.bgPosterForm.id;
            }
        },

        methods: {
            changeAccessLimit(index) {
                if (index == 1) this.browseForm.access_limit.member = [];
            },
            clearMember(index) {
                this.browseForm.access_limit.member.splice(index, 1);
            },
            submitMember() {
                let data = [];
                this.m_list.forEach(item => {
                    if (item.checked) data.push({
                        id: item.id,
                        level: item.level,
                        name: item.name
                    })
                })
                this.browseForm.access_limit['member'] = data;
                this.m_visible = false;
            },
            openMemberDialog() {
                this.m_visible = true;
                this.m_getList();
            },
            m_toSearch() {
                this.page = 1;
                this.m_getList();
            },
            m_pageChange(page) {
                this.page = page;
                this.m_getList();
            },
            m_getList() {
                let self = this;
                self.m_listLoading = true;
                request({
                    params: Object.assign({
                        r: 'mall/mall-member/index',
                        m_page: self.m_page
                    }, this.m_search),
                }).then(e => {
                    if (e.data.code === 0) {
                        self.m_listLoading = false;
                        self.m_list = e.data.data.list.map(item => {
                            item.checked = self.browseForm.access_limit.member.some(t => t.level == item.level);
                            return item;
                        });
                        self.m_pagination = e.data.data.pagination;
                    }
                });
            },
            browseSubmit() {
                this.browseBtnLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/change-access-limit'
                    },
                    data: Object.assign({}, this.browseForm, {access_limit: JSON.stringify(this.browseForm.access_limit)}),
                    method: 'post',
                }).then(e => {
                    this.browseBtnLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        this.browseVisible = false;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            openBrowse(row) {
                this.browseForm = Object.assign({}, row);
                this.browseVisible = true;
            },
            settingIndex(row) {
                this.changeId = row.id;
                this.platform = row.platform.length > 0 ? JSON.stringify(row.platform) : ''
                this.indexDialog = true;
            },
            cancel() {
                this.platformLoading = false;
                this.indexDialog = false;
            },
            submitPlatform(e) {
                this.platformLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/change-home-status'
                    },
                    data: {
                        id: this.changeId,
                        is_home_page: 1,
                        platform: e.length > 0 ? e : []
                    },
                    method: 'post',
                }).then(e => {
                    if (e.data.code == 0) {
                        this.$message.success(e.data.msg);
                        this.platformLoading = false;
                        this.indexDialog = false;
                        this.changeId = 0;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            formatterCount(column) {
                return column['userCount'] + `/` + column['accessCount'];
            },
            copyText(text) {
                var textarea = document.createElement("textarea"); //创建input对象
                var toolBoxwrap = document.getElementById('NewsToolBox'); //将文本框插入到NewsToolBox这个之后
                toolBoxwrap.appendChild(textarea); //添加元素
                textarea.value = text;
                textarea.focus();
                if (textarea.setSelectionRange) {
                    textarea.setSelectionRange(0, textarea.value.length); //获取光标起始位置到结束位置
                } else {
                    textarea.select();
                }
                try {
                    var flag = document.execCommand("copy"); //执行复制
                } catch (eo) {
                    var flag = false;
                }
                toolBoxwrap.removeChild(textarea); //删除元素
                return flag;
            },

            noticePoster(column) {
                let params = '';
                if (column.is_home_page == 0) {
                    params = '?page_id=' + column.id;
                }
                this.bgPosterForm = {
                    id: column.id,
                    text: '/pages/index/index' + params,
                    picUrl: '',
                    name: column.name,
                }
                this.bgVisible = true;
                this.bgLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/poster',
                        page_id: column.is_home_page == 0 ? this.bgPosterForm.id : 0,
                    },
                    method: 'get'
                }).then(e => {
                    this.bgLoading = false;
                    if (e.data.code === 0) {
                        this.bgPosterForm.picUrl = e.data.data.pic_url;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.bgLoading = false;
                });
            },

            copyInput() {
                if (this.copyText(this.bgPosterForm.text)) {
                    this.$message.success('复制成功');
                } else {
                    this.$message.error('复制失败');
                }
            },

            edit(column) {
                navigateTo({
                    r: 'plugin/diy/mall/template/edit',
                    id: column.id
                });
            },
            // changeHome(column, is_home_page) {
            //     request({
            //         params: {
            //             r: 'plugin/diy/mall/template/change-home-status',
            //             id: column.id
            //         },
            //         method: 'post',
            //         data: {
            //             id: column.id,
            //             is_home_page
            //         }
            //     }).then(e => {
            //         this.listLoading = false;
            //         if (e.data.code === 0) {
            //             this.list.forEach(item => {
            //                 item.is_home_page = 0;
            //             });
            //             column.is_home_page = is_home_page;
            //             this.$message.success(e.data.msg);
            //         } else {
            //             this.$message.error(e.data.msg);
            //         }
            //     }).catch(e => {
            //         this.listLoading = false;
            //     });
            // },
            destroy(column, index) {
                this.$confirm('此操作将删除该微页面, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/diy/mall/template/destroy',
                            id: column.id
                        },
                        method: 'get'
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
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },
            pageChange(page) {
                this.page = page;
                this.getList();
            },
            search() {
                this.page = 1;
                this.getList();
            },
            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/diy/mall/template/index',
                        keyword: this.keyword,
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
            syncTemplateType() {
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/sync-template-type',
                    },
                }).then(e => {
                }).catch(e => {
                });
            }
        }
    });
</script>
