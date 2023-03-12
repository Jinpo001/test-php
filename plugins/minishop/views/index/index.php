<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/9
 * Time: 5:17 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('goods/app-select-goods');
Yii::$app->loadViewComponent('goods/app-attr');
?>
<style>
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

    .operate .el-button--mini.is-circle {
        padding: 0;
    }

    .operate img {
        width: 36px;
        height: 36px;
    }

    .app-add-cat .app-goods-cat-list {
        border: 1px solid #E8EAEE;
        border-radius: 5px;
        margin-top: -5px;
        padding: 10px 0;
        overflow: scroll;
        height: 400px;
    }

    .app-add-cat .el-dialog__body h3 {
        font-weight: bold;
        color: #999999;
    }

    .app-add-cat .app-goods-cat-list .cat-item {
        cursor: pointer;
        padding: 5px 10px;
        line-height: 40px;
    }

    .app-add-cat .app-goods-cat-list .active {
        background: #FAFAFA;
    }

    .app-add-cat .el-radio {
        margin-right: 0;
        margin-top: 13px;
    }

    .refreshModal {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: .5;
        background: #000;
        z-index: 2000;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never"
             body-style="background-color: #f3f3f3;padding: 10px 0 0 0;">
        <div slot="header">
            <span>交易组件</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" v-if="form.can_use"
                       @click="edit.dialogVisible = true">
                添加商品
            </el-button>
        </div>
        <template v-if="!loading">
            <div v-if="!form.can_use">
                <el-card shadow="never" body-style="background-color: #e1f0ff; color: #409EFF; padding: 14px 20px">
                    <span>{{form.content}}</span>
                    <a href="https://mp.weixin.qq.com/" target="_blank">
                        <el-button type="primary" size="mini" @click="">去开通</el-button>
                    </a>
                </el-card>
            </div>
            <div v-else>
                <el-card shadow="never">
                    <div class="input-item">
                        <el-input @keyup.enter.native="search" size="small" placeholder="请输入商品id或名称搜索"
                                  v-model="search.keyword" clearable @clear="search">
                            <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                        </el-input>
                    </div>
                    <div style="padding: 10px; background-color: #f7f7f7">
                        <el-button size="mini" @click="clickRefresh">同步刷新</el-button>
                    </div>
                    <el-table
                            ref="multipleTable"
                            :data="form.list"
                            border
                            style="width: 100%;margin-bottom: 15px"
                            @sort-change="sortChange" v-loading="listLoading">
                        <el-table-column width='110' prop="goods_id" label="ID" :sortable="true"></el-table-column>
                        <el-table-column prop="cat" label="分类" width="160">
                            <template slot-scope="scope">
                                <el-tag size="mini">{{scope.row.cat}}</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="title" label="商品名称" width="380">
                            <template slot-scope="scope">
                                <div flex="dir:left box:first">
                                    <div style="padding-right: 10px">
                                        <app-image mode="aspectFill" :src="scope.row.cover_pic"></app-image>
                                    </div>
                                    <div v-line-clamp="2">{{scope.row.title}}</div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="price" label="售价" :sortable="true" width="120"></el-table-column>
                        <el-table-column prop="stock" label="库存" :sortable="true" width="120"></el-table-column>
                        <el-table-column prop="created_at" label="提交时间" width="250"></el-table-column>
                        <el-table-column prop="apply_status" label="审核状态" width="120">
                            <template slot-scope="scope">
                                <el-tag v-if="scope.row.apply_status == 0" type="info" size="mini">未审核</el-tag>
                                <el-tag v-else-if="scope.row.apply_status == 1" size="mini">审核中</el-tag>
                                <el-tag v-else-if="scope.row.apply_status == 2" type="success" size="mini">审核通过</el-tag>
                                <el-tag v-else type="danger" size="mini">审核驳回</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状态" width="120">
                            <template slot-scope="scope">
                                <el-tag v-if="scope.row.status == 0" type="warning" size="mini">下架中</el-tag>
                                <el-tag v-else type="success" size="mini">销售中</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" fixed="right" width="220">
                            <template slot-scope="scope">
                                <div class="operate">
                                    <el-button @click="apply(scope.row)" type="text" circle size="mini">
                                        <el-tooltip class="item" effect="dark" content="提交审核" placement="top">
                                            <img src="statics/img/plugins/minishop/apply.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button @click="up(scope.row.id)" type="text" circle size="mini"
                                               v-if="scope.row.status === 0 && scope.row.apply_status === 2">
                                        <el-tooltip class="item" effect="dark" content="上架" placement="top">
                                            <img src="statics/img/plugins/minishop/up.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button @click="down(scope.row.id)" type="text" circle size="mini"
                                               v-if="scope.row.status === 1 && scope.row.apply_status === 2">
                                        <el-tooltip class="item" effect="dark" content="下架" placement="top">
                                            <img src="statics/img/plugins/minishop/down.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button @click="updateClick(scope.row)" type="text" circle size="mini"
                                               v-if="scope.row.apply_status === 2">
                                        <el-tooltip class="item" effect="dark" content="免审核更新" placement="top">
                                            <img src="statics/img/plugins/minishop/refresh.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                    <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-card>
                <div flex="dir:right" style="margin-top: 20px;">
                    <el-pagination
                            @current-change="pagination"
                            background
                            hide-on-single-page
                            :page-size="pageSize"
                            :current-page="current_page"
                            layout="total, prev, pager, next, jumper"
                            :total="pageCount">
                    </el-pagination>
                </div>
                <el-dialog title="添加商品" :visible.sync="edit.dialogVisible" width="30%">
                    <span>
                        <el-form size="small">
                            <el-form-item label="商品分类" required>
                                <div>
                                    <el-tag v-if="edit.cat_info" type="warning" closable
                                            @close="removeCat">{{edit.cat_info.label}}</el-tag>
                                    <el-button type="primary" size="mini" @click="openCat">选择分类</el-button>
                                </div>
                            </el-form-item>
                            <el-form-item label="商品信息" required>
                                <div>
                                    <el-button type="primary" @click="select_goods_dialog" size="mini" v-if="edit.is_update == 0">选择商品</el-button>
                                    <div flex="dir:left" v-if="edit.goods_info"
                                         style="padding: 5px;border: 1px #eeeeee solid;margin-top: 10px">
                                        <div style="padding-right: 10px;flex-grow: 0">
                                            <app-image mode="aspectFill" :src="edit.goods_info.cover_pic"></app-image>
                                        </div>
                                        <div style="flex-grow: 1">
                                            <div v-line-clamp="1" style="line-height: 25px">{{edit.goods_info.name}}</div>
                                            <div style="color: #ff4544;line-height: 25px">{{edit.goods_info.price}}</div>
                                        </div>
                                        <div style="flex-grow: 0" v-if="edit.is_update == 0">
                                            <el-button @click="destroy_goods" type="text" circle size="mini">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>
                        </el-form>
                    </span>
                    <span slot="footer" class="dialog-footer">
                        <el-button @click="edit.dialogVisible = false" size="mini">取 消</el-button>
                        <el-button type="primary" @click="addGoods" size="mini" :loading="edit.editLoading">确 定</el-button>
                    </span>
                </el-dialog>
                <app-select-goods :multiple="false" sign="mall" :is_cancel="true" ref="edit_goods" @selected="selectGoods">
                </app-select-goods>
                <el-dialog title="选择分类" width="1100px" :visible.sync="edit.catDialogVisible">
                    <div class="app-add-cat">
                        <el-row v-loading="edit.catLoading" :gutter="20" style="margin-top: -30px;">
                            <template v-if="catList.length > 0">
                                <el-col :span="8">
                                    <h3>一级分类</h3>
                                    <div class="app-goods-cat-list active">
                                        <div :class="{'active': current1 == option.value ? true : false}" class="cat-item"
                                             flex="dir:left box:first"
                                             v-for="(option,index) in catList"
                                             :key="option.value">
                                            <span style="display: none;">{{option.label}}</span>
                                            <div @click="selectCats(option,1)" flex="box:last cross:center">
                                                <span>{{option.label}}</span>
                                                <i v-if="option.children" class="el-icon-arrow-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8" v-if="children.length > 0">
                                    <h3>二级分类</h3>
                                    <div class="app-goods-cat-list">
                                        <div :class="{'active': current2 == option.value ? true : false}" class="cat-item"
                                             flex="dir:left box:first"
                                             v-for="(option,index) in children"
                                             :key="option.value">
                                            <span style="display: none;">{{option.label}}</span>
                                            <div @click="selectCats(option,2)" flex="box:last cross:center">
                                                <span>{{option.label}}</span>
                                                <i v-if="option.children" class="el-icon-arrow-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8" v-if="third.length > 0">
                                    <h3>三级分类</h3>
                                    <div class="app-goods-cat-list">
                                        <div :class="{'active': current3 == option.value ? true : false}" class="cat-item"
                                             flex="dir:left box:first"
                                             v-for="(option,index) in third"
                                             :key="option.value">
                                            <el-radio @change="optionClick(option)" v-model="select_cat.value"
                                                         :label="option.value" size="mini">
                                                <span style="display: none;">{{option.label}}</span>
                                            </el-radio>
                                            <div @click="optionClick(option)" flex="box:last cross:center">
                                                <span>{{option.label}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                            </template>
                        </el-row>
                    </div>
                    <span slot="footer" class="dialog-footer">
                <el-button size='small' @click="edit.catDialogVisible = false">取 消</el-button>
                <el-button size='small' type="primary" @click="dialogSubmit">确 定</el-button>
            </span>
                </el-dialog>
                <el-dialog title="免审核更新" width="1100px" :visible.sync="update.dialogVisible">
                    <div v-loading="update.loading">
                        <el-form label-width="180px" size="small">
                            <app-attr v-model="update.attr" :attr-groups="update.attrGroups"></app-attr>
                        </el-form>
                    </div>
                    <div slot="footer" class="dialog-footer">
                        <el-button size='small' @click="update.dialogVisible = false">取 消</el-button>
                        <el-button size='small' type="primary"
                                   :loading="update.submitLoading" @click="updateSubmit">确 定</el-button>
                    </div>
                </el-dialog>
                <div class="refreshModal" v-if="refreshDialogVisible">
                    <div style="margin: 0 auto; width: 145px; margin-top: calc(50vh - 73px); position: relative">
                        <img src="statics/img/plugins/minishop/loading.gif" style="width: 145px;height: 145px">
                        <div style="position: absolute;top: 115px;left: 38px;color: #fff">数据加载中...</div>
                    </div>
                </div>
            </div>
        </template>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                listLoading: false,
                form: {
                    can_use: false,
                    content: '',
                    list: [],
                },
                catList: [],
                search: {
                    keyword: '',
                    sort_prop: '',
                    sort_type: 1
                },
                page: 1,
                pageSize: 20,
                pageCount: 0,
                current_page: 1,
                edit: {
                    dialogVisible: false,
                    catDialogVisible: false,
                    catLoading: false,
                    editLoading: false,
                    goods_info: null,
                    cat_info: null,
                    form: {
                        third_cat_id: 0,
                        goods_id: 0,
                    },
                    is_update: 0
                },
                children: [],
                third: [],
                current1: '',
                current2: '',
                current3: '',
                select_cat: {
                    label: 0,
                    value: 0
                },
                update: {
                    dialogVisible: false,
                    loading: false,
                    attr: [],
                    attrGroups: [],
                    submitLoading: false,
                    id: 0,
                },
                refreshDialogVisible: false
            };
        },
        created() {
            this.loading = true;
            this.getList();
        },
        watch: {
            'edit.dialogVisible': {
                handler: function (to) {
                    if (this.edit.dialogVisible === false) {
                        this.edit.goods_info = null
                        this.edit.cat_info = null
                        this.edit.form = {
                            third_cat_id: 0,
                            goods_id: 0,
                        }
                        this.edit.is_update = 0
                    }
                }
            }
        },
        methods: {
            getCat() {
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/cat'
                    },
                    method: 'get'
                }).then(e => {
                    this.edit.catLoading = false;
                    if (e.data.code === 0) {
                        this.catList = e.data.data.list;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            getList() {
                this.listLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/index',
                        page: this.page,
                        search: this.search,
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false
                    this.listLoading = false
                    if (e.data.code === 0) {
                        this.form = e.data.data
                        self.pageCount = e.data.data.pagination.total_count;
                        self.pageSize = e.data.data.pagination.pageSize;
                        self.current_page = e.data.data.pagination.current_page;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false
                    this.listLoading = false
                });
            },
            // 排序排列
            sortChange(row) {
                if (row.prop && row.order) {
                    this.search.sort_prop = row.prop;
                    this.search.sort_type = row.order === "descending" ? 0 : 1;
                } else {
                    this.search.sort_prop = '';
                    this.search.sort_type = '';
                }
                this.getList();
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            clickRefresh() {
                this.refresh(1);
            },
            refresh(page = 1) {
                this.refreshDialogVisible = true;
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/refresh',
                        page: page
                    },
                    method: 'get'
                }).then(e => {
                    if (e.data.code === 0) {
                        if (e.data.data.retry === 1) {
                            this.refresh(e.data.data.page)
                        } else {
                            this.refreshDialogVisible = false;
                            this.getList();
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {

                });
            },
            destroy(row, index) {
                let self = this;
                let text = '删除该条数据, 是否继续?';
                self.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.operate(row.id, 'delete');
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            up(id) {
                let self = this;
                let text = '是否上架该商品';
                self.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.operate(id, 'up');
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            down(id) {
                let self = this;
                let text = '是否下架该商品';
                self.$confirm(text, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.operate(id, 'down');
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            operate(id, type) {
                let self = this;
                self.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/operate',
                    },
                    method: 'post',
                    data: {
                        id: id,
                        _csrf: _csrf,
                        operate: type
                    }
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.getList();
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                });
            },
            select_goods_dialog() {
                this.$refs['edit_goods'].showModel();
            },
            selectGoods(goods_warehouse) {
                this.edit.goods_info = {
                    name: goods_warehouse.name,
                    price: goods_warehouse.price,
                    cover_pic: goods_warehouse.goodsWarehouse.cover_pic,
                }
                this.edit.form.goods_id = goods_warehouse.id
            },
            destroy_goods() {
                this.edit.goods_info = null;
                this.edit.form.goods_id = 0
            },
            openCat() {
                this.edit.catDialogVisible = true;
                this.edit.catLoading = true;
                if (this.edit.cat_info) {
                    this.select_cat = this.edit.cat_info
                }
                this.getCat();
            },
            // 选择分类
            selectCats(option, type) {
                let self = this;
                if (type === 1) {
                    self.current1 = option.value;
                    self.children = [];
                    self.third = [];
                    if (option.children) {
                        option.children.forEach(function (item) {
                            self.children.push(item);
                        })
                    }
                }

                if (type === 2) {
                    self.current2 = option.value;
                    self.third = [];
                    if (option.children) {
                        option.children.forEach(function (item) {
                            self.third.push(item);
                        })
                    }
                }

                if (type === 3) {
                    self.current3 = option.value;
                }
            },
            optionClick(option) {
                let self = this;
                let sign = true;
                this.select_cat = {
                    label: option.label,
                    value: option.value,
                }
            },
            dialogSubmit() {
                this.edit.cat_info = this.select_cat
                this.edit.form.third_cat_id = this.select_cat.value;
                this.select_cat = {
                    label: 0,
                    value: 0
                }
                this.edit.catDialogVisible = false;
            },
            removeCat() {
                this.edit.cat_info = null;
                this.edit.form.third_cat_id = 0;
            },
            addGoods() {
                if (!this.edit.cat_info) {
                    this.$message.error('请选择分类');
                    return '';
                }
                if (!this.edit.goods_info) {
                    this.$message.error('请选择商品');
                    return '';
                }
                this.edit.editLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/goods',
                    },
                    method: 'post',
                    data: {
                        goods_id: this.edit.form.goods_id,
                        _csrf: _csrf,
                        third_cat_id: this.edit.form.third_cat_id
                    }
                }).then(e => {
                    this.edit.editLoading = false;
                    if (e.data.code === 0) {
                        this.edit.dialogVisible = false;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            apply(row) {
                this.edit.is_update = 1;
                this.edit.form.third_cat_id = row.third_cat_id
                this.edit.form.goods_id = row.goods_id;
                this.edit.goods_info = {
                    name: row.title,
                    price: row.price,
                    cover_pic: row.cover_pic,
                }
                this.edit.cat_info = {
                    label: row.cat,
                    value: row.third_cat_id
                }
                this.edit.dialogVisible = true;
            },
            updateClick(row) {
                this.update.dialogVisible = true;
                this.update.loading = true;
                this.update.id = row.id
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/operate',
                    },
                    method: 'post',
                    data: {
                        id: row.id,
                        _csrf: _csrf,
                        operate: 'attr'
                    }
                }).then(e => {
                    this.update.loading = false;
                    if (e.data.code === 0) {
                        this.update.attr = e.data.data.attr;
                        this.update.attrGroups = e.data.data.attr_groups;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            updateSubmit() {
                this.update.submitLoading = true;
                this.$request({
                    params: {
                        r: 'plugin/minishop/mall/index/update',
                    },
                    method: 'post',
                    data: {
                        id: this.update.id,
                        attr: this.update.attr,
                        _csrf: _csrf,
                    }
                }).then(e => {
                    this.update.submitLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success('更新成功');
                        this.update.attr = [];
                        this.update.id = 0;
                        this.update.dialogVisible = false;
                        this.getList();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            }
        }
    });
</script>
