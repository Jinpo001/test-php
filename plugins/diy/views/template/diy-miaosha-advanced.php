<?php
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<style>
    /*-----------------设置部分--------------*/
    .diy-miaosha-advanced .diy-component-edit .goods-list {
        flex-wrap: wrap;
    }

    .diy-miaosha-advanced .diy-component-edit .goods-item,
    .diy-miaosha-advanced .diy-component-edit .goods-add {
        width: 50px;
        height: 50px;
        position: relative;
        margin-right: 15px;
        margin-bottom: 15px;
    }

    .diy-miaosha-advanced .diy-component-edit .goods-add .el-button {
        width: 100%;
        height: 100%;
        border-radius: 0;
        padding: 0;
    }

    .diy-miaosha-advanced .diy-component-edit .goods-pic {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
    }

    .diy-miaosha-advanced .diy-component-edit .goods-delete {
        position: absolute;
        left: calc(100% - 13px);
        top: -13px;
        width: 25px;
        height: 25px;
        line-height: 25px;
        padding: 0 0;
        visibility: hidden;
        z-index: 1;
    }

    .diy-miaosha-advanced .diy-component-edit .goods-item:hover .goods-delete {
        visibility: visible;
    }

    /*-----------------预览部分--------------*/
    .diy-miaosha-advanced .diy-component-preview .goods-list {
        padding: 11px;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-item {
        padding: 11px;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-pic {
        background-size: cover;
        background-position: center;
        width: 99.8%;
        height: 700px;
        background-color: #f6f6f6;
        background-repeat: no-repeat;
        position: relative;
        border-radius: 10px 10px 0 0;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style--1 .goods-item,
    .diy-miaosha-advanced .diy-component-preview .goods-list-style-1 .goods-item {
        width: 100%;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style-2 .goods-item {
        width: 50%;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style-3 .goods-item {
        width: 33.333333%;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style-0 .goods-item {
        width: 249px;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style--1 .goods-pic {
        width: 200px;
        height: 200px;
        border-radius: 10px 0 0 10px;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style-2 .goods-pic {
        height: 342px;
        border-radius: 10px 10px 0 0;
    }

    .diy-miaosha-advanced .diy-component-preview .goods-list-style-0 .goods-pic,
    .diy-miaosha-advanced .diy-component-preview .goods-list-style-3 .goods-pic {
        height: 200px;
        border-radius: 10px 10px 0 0;
    }


    .diy-miaosha-advanced hr {
        border: none;
        height: 1px;
        background-color: #e2e2e2;
    }
</style>
<template id="diy-miaosha-advanced">
    <div class="diy-miaosha-advanced">
        <div class="diy-component-preview">
            <div>
                秒杀模块
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width='150px' @submit.native.prevent>
                <el-form-item label="商品添加">
                    <app-radio v-model="data.addGoodsType" :label="0">自动添加</app-radio>
                    <app-radio v-model="data.addGoodsType" :label="1">手动添加</app-radio>
                </el-form-item>
                <el-form-item v-show="data.addGoodsType == 0" label="商品数量">
                    <el-input size="small" v-model.number="data.goodsLength" type="number"></el-input>
                </el-form-item>
                <el-form-item v-show="data.addGoodsType == 1" label="商品列表">
                    <draggable v-model="data.list" flex class="goods-list">
                        <div class="goods-item"
                             v-for="(goods,goodsIndex) in data.list">
                            <el-tooltip effect="dark" content="移除商品" placement="top">
                                <el-button @click="deleteGoods(goodsIndex)" circle class="goods-delete"
                                           type="danger"
                                           icon="el-icon-close"></el-button>
                            </el-tooltip>
                            <div class="goods-pic"
                                 :style="'background-image:url('+goods.picUrl+')'"></div>
                        </div>
                    </draggable>
                    <div class="goods-add">
                        <el-button size="small" @click="showGoodsDialog" icon="el-icon-plus"></el-button>
                    </div>
                </el-form-item>
                <hr>
                <!--————————————————————————————————————————————————-->
                <el-form-item label="秒杀标题">
                    <el-input size="small" v-model.number="data.mTitle"></el-input>
                </el-form-item>
                <el-form-item label="标题文字颜色">
                    <div flex="dir:left cross:center">
                        <el-color-picker @change="(row) => {row == null ? data.mColor = '#ffffff' : ''}" size="small"
                                         v-model="data.mColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mColor"
                        ></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="标题背景颜色">
                    <el-radio v-model="data.mBgType" label="pure">纯色</el-radio>
                    <el-radio v-model="data.mBgType" label="gradient">渐变</el-radio>
                    <div flex="dir:left cross:center">
                        <div>
                            <el-color-picker @change="(row) => {row == null ? data.mBgColor = '#FF366F' : ''}"
                                             size="small" v-model="data.mBgColor"></el-color-picker>
                            <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mBgColor"
                            ></el-input>
                        </div>
                        <div style="margin-left: 24px">
                            <el-color-picker @change="(row) => {row == null ? data.mBgGradientColor = '#FF4242' : ''}"
                                             size="small" v-model="data.mBgGradientColor"></el-color-picker>
                            <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mBgGradientColor"
                            ></el-input>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="倒计时文字颜色">
                    <div flex="dir:left cross:center">
                        <el-color-picker @change="(row) => {row == null ? data.mTimeColor = '#353535' : ''}"
                                         size="small"
                                         v-model="data.mTimeColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mTimeColor"
                        ></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="倒计时背景颜色">
                    <div flex="dir:left cross:center">
                        <el-color-picker @change="(row) => {row == null ? data.mTimeBgColor = '#FFFFFF' : ''}"
                                         size="small"
                                         v-model="data.mTimeBgColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mTimeBgColor"
                        ></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="商品背景颜色">
                    <div flex="dir:left cross:center">
                        <el-color-picker @change="(row) => {row == null ? data.mGoodsBgColor = '#FFE7E7' : ''}"
                                         size="small"
                                         v-model="data.mGoodsBgColor"></el-color-picker>
                        <el-input size="small" style="width: 80px;margin-left: 5px;" v-model="data.mGoodsBgColor"
                        ></el-input>
                    </div>
                </el-form-item>
                <!--————————————————————————————————————————————————-->
                <el-form-item label="显示商品名称">
                    <el-switch v-model="data.showGoodsName"></el-switch>
                </el-form-item>
                <el-form-item label="显示商品价格">
                    <el-switch v-model="data.showGoodsPrice"></el-switch>
                </el-form-item>
                <el-form-item label="显示商品角标">
                    <el-switch v-model="data.showGoodsTag"></el-switch>
                </el-form-item>
                <el-form-item label="商品角标" v-if="data.showGoodsTag">
                    <app-radio v-model="data.goodsTagPicUrl" v-for="tag in goodsTags" :label="tag.picUrl"
                               :key="tag.name"
                               @change="goodsTagChange">
                        {{tag.name}}
                    </app-radio>
                    <app-radio v-model="data.customizeGoodsTag" :label="true" @change="customizeGoodsTagChange">自定义
                    </app-radio>
                </el-form-item>
                <el-form-item label="自定义商品角标" v-if="data.showGoodsTag&&data.customizeGoodsTag">
                    <app-image-upload v-model="data.goodsTagPicUrl" width="64" height="64"></app-image-upload>
                </el-form-item>
                <el-form-item label="显示划线价" v-if="[-1,0,1,2,3].indexOf(data.listStyle) != -1">
                    <el-switch v-model="data.isUnderLinePrice"></el-switch>
                </el-form-item>
                <diy-bg :data="data" @update="updateData" @toggle="toggleData" @change="changeData"></diy-bg>
            </el-form>
        </div>
        <el-dialog title="选择商品" :visible.sync="goodsDialog.visible" :close-on-click-modal="false"
                   @open="loadGoodsData">
            <el-input size="mini" v-model="goodsDialog.keyword" placeholder="根据名称搜索" :clearable="true"
                      @clear="goodsDialogPageChange(1)" @keyup.enter.native="goodsDialogPageChange(1)">
                <el-button slot="append" @click="goodsDialogPageChange(1)">搜索</el-button>
            </el-input>
            <el-table :data="goodsDialog.list" v-loading="goodsDialog.loading" @selection-change="goodsSelectionChange">
                <el-table-column label="选择" type="selection"></el-table-column>
                <el-table-column label="ID" prop="id" width="100px"></el-table-column>
                <el-table-column label="名称" prop="name">
                    <template slot-scope="props">
                        <div flex="cross:center dir:left">
                            <img width="50" height="50" style="margin-right: 10px" :src="props.row.cover_pic" alt="">
                            <div>{{props.row.name}}</div>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="goodsDialog.pagination"
                        style="display: inline-block"
                        background
                        @current-change="goodsDialogPageChange"
                        layout="prev, pager, next, jumper"
                        :page-size.sync="goodsDialog.pagination.pageSize"
                        :total="goodsDialog.pagination.totalCount">
                </el-pagination>
            </div>
            <div slot="footer">
                <el-button @click="goodsDialog.visible = false">取 消</el-button>
                <el-button type="primary" @click="addGoods">确 定</el-button>
            </div>

        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-miaosha-advanced', {
        template: '#diy-miaosha-advanced',
        props: {
            value: Object,
        },
        data() {
            return {
                goodsDialog: {
                    selectedList: null,
                    visible: false,
                    page: 1,
                    keyword: '',
                    loading: false,
                    list: [],
                    pagination: null,
                    selected: null,

                },
                goodsTags: [
                    {
                        name: '热销',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-rx.png',
                    },
                    {
                        name: '新品',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-xp.png',
                    },
                    {
                        name: '折扣',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-zk.png',
                    },
                    {
                        name: '推荐',
                        picUrl: _currentPluginBaseUrl + '/images/goods-tag-tj.png',
                    },
                ],
                data: {
                    list: [],
                    addGoodsType: 0,
                    goodsLength: 10,
                    /* */
                    mTitle: '圣诞秒杀整点抢',
                    mColor: '#FFFFFF',
                    mBgType: 'gradient',
                    mBgColor: '#FF366F',
                    mBgGradientColor: '#FF4242',
                    mTimeColor: '#353535',
                    mTimeBgColor: '#FFFFFF',
                    mGoodsBgColor: '#FFE7E7',
                    /* */
                    showGoodsName: true,
                    showGoodsPrice: true,
                    showGoodsTag: false,
                    customizeGoodsTag: false,
                    goodsTagPicUrl: _currentPluginBaseUrl + '/images/goods-tag-rx.png',
                    isUnderLinePrice: true,

                    backgroundColor: '#fff',
                },
                position: 'center center',
                repeat: 'no-repeat',
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cButtonStyle() {
                return 123;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            },
        },
        methods: {
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            cList() {
                if (!this.data.list || !this.data.list.length) {
                    const item = {
                        id: 0,
                        name: '演示商品名称',
                        picUrl: '',
                        price: '100.00',
                        originalPrice: '300.00',
                    };
                    return [item, item];
                } else {
                    return this.data.list;
                }
            },
            goodsTagChange(e) {
                this.data.goodsTagPicUrl = e;
                this.data.customizeGoodsTag = false;
            },
            customizeGoodsTagChange(e) {
                this.data.goodsTagPicUrl = '';
                this.data.customizeGoodsTag = true;
            },
            loadGoodsData() {
                this.goodsDialog.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/diy/mall/template/get-goods',
                        page: this.goodsDialog.page,
                        keyword: this.goodsDialog.keyword,
                        sign: 'miaosha',
                    },
                }).then(response => {
                    this.goodsDialog.loading = false;
                    if (response.data.code === 0) {
                        this.goodsDialog.list = response.data.data.list;
                        this.goodsDialog.pagination = response.data.data.pagination;
                    }
                });
            },
            goodsDialogPageChange(page) {
                this.goodsDialog.page = page;
                this.loadGoodsData();
            },
            showGoodsDialog() {
                this.goodsDialog.visible = true;
            },
            goodsSelectionChange(e) {
                this.goodsDialog.selectedList = e;
            },
            addGoods() {
                this.goodsDialog.visible = false;
                for (let i in this.goodsDialog.selectedList) {
                    const item = {
                        id: this.goodsDialog.selectedList[i].id,
                        name: this.goodsDialog.selectedList[i].name,
                        picUrl: this.goodsDialog.selectedList[i].cover_pic,
                        price: this.goodsDialog.selectedList[i].price,
                        original_price: this.goodsDialog.selectedList[i].original_price,
                    };
                    this.data.list.push(item);
                }
            },
            deleteGoods(goodsIndex) {
                this.data.list.splice(goodsIndex, 1);
            },
        }
    });
</script>
