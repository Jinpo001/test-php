<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
Yii::$app->loadViewComponent('app-goods');
?>
<div id="app" v-cloak>
    <div class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                      @click="$navigate({r:'plugin/wholesale/mall/goods/index'})">商品管理</span></el-breadcrumb-item>
            <el-breadcrumb-item v-if="form.id>0">编辑商品</el-breadcrumb-item>
            <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <app-goods
            ref="appGoods"
            :is_attr="1"
            :is_show="0"
            :is_detail="0"
            :form="form"
            :is_cats="0"
            sign="wholesale"
            :is_display_setting="0"
            url="plugin/wholesale/mall/goods/edit"
            get_goods_url="plugin/wholesale/mall/goods/edit"
            referrer="plugin/wholesale/mall/goods/index"
            :is_min_number="0"
            @set-attr="setAttr"
            @change-tabs="changeTabs"
            @goods-success="childrenGoods">
        <template slot="before_basic_tab_pane">
            <el-tab-pane label="批发价设置" name="four">
                <el-card shadow="never" style="margin-bottom: 10px">
                    <div slot="header">起批设置</div>
                    <el-form-item label="起批数量" prop="num">
                        <template slot='label'>
                            <span class="required-icon">起批数量</span>
                        </template>
                        <el-input @input="change" style="width: 40%;" type="number" :min="0" v-model="form.rise_num">
                            <template slot="append">件</template>
                        </el-input>
                        <div style="color: #ff4544;margin-top: -5rpx">起批数量必须为大于0的整数</div>
                    </el-form-item>
                </el-card>
                <el-card shadow="never" style="margin-bottom: 10px">
                    <div slot="header">阶梯优惠设置</div>
                    <el-form-item label="开启阶梯优惠">
                        <el-switch @change="statusChange" v-model="form.rules_status" :active-value="1"
                                   :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="form.rules_status == 1" label="优惠方式">
                        <el-radio @change="typeChange(0)" v-model="form.wholesale_type" :label="0">打折</el-radio>
                        <el-radio @change="typeChange(1)" v-model="form.wholesale_type" :label="1">减钱(元)</el-radio>
                    </el-form-item>
                    <div v-if="form.rules_status == 1">
                        <div class="step" flex="dir:left cross:center">
                            <div class="step-label required-icon">一阶梯</div>
                            <div class="step-item" flex="main:justify">
                                <div flex="dir:left cross:center">
                                    <div>
                                        <el-input oninput="this.value = this.value.replace(/^(0+)|[^\d]+/g, '');" @input="change" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[0].num">
                                            <template slot="append">件</template>
                                        </el-input>
                                    </div>
                                    <div style="margin-left: 10px">及以上</div>
                                </div>
                                <div>
                                    <div v-if="form.wholesale_type == 0" flex="dir:left cross:center">
                                        <div style="margin-right: 10px">打</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');" @blur="changeDiscount(0)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[0].discount">
                                                <template slot="append">折</template>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-else flex="dir:left cross:center">
                                        <div style="margin-right: 10px">减</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" @blur="changeDiscount(0)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[0].discount">
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-if="form.wholesale_type == 1" class="wholesale-tip">减钱金额不大于最低价{{showPrice}}元</div>
                                </div>
                            </div>
                        </div>
                        <div class="step" flex="dir:left cross:center">
                            <div class="step-label">二阶梯</div>
                            <div class="step-item" flex="main:justify">
                                <div flex="dir:left cross:center">
                                    <div>
                                        <el-input oninput="this.value = this.value.replace(/^(0+)|[^\d]+/g, '');" @input="change" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[1].num">
                                            <template slot="append">件</template>
                                        </el-input>
                                    </div>
                                    <div style="margin-left: 10px">及以上</div>
                                </div>
                                <div>
                                    <div v-if="form.wholesale_type == 0" flex="dir:left cross:center">
                                        <div style="margin-right: 10px">打</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');" @blur="changeDiscount(1)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[1].discount">
                                                <template slot="append">折</template>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-else flex="dir:left cross:center">
                                        <div style="margin-right: 10px">减</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" @blur="changeDiscount(1)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[1].discount">
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-if="form.wholesale_type == 1" class="wholesale-tip">减钱金额不大于最低价{{showPrice}}元</div>
                                </div>
                            </div>
                        </div>
                        <div class="step" flex="dir:left cross:center">
                            <div class="step-label">三阶梯</div>
                            <div class="step-item" flex="main:justify">
                                <div flex="dir:left cross:center">
                                    <div>
                                        <el-input oninput="this.value = this.value.replace(/[^[1-9]\d*$]/g, '');" @input="change" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[2].num">
                                            <template slot="append">件</template>
                                        </el-input>
                                    </div>
                                    <div style="margin-left: 10px">及以上</div>
                                </div>
                                <div>
                                    <div v-if="form.wholesale_type == 0" flex="dir:left cross:center">
                                        <div style="margin-right: 10px">打</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');" @blur="changeDiscount(2)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[2].discount">
                                                <template slot="append">折</template>
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-else flex="dir:left cross:center">
                                        <div style="margin-right: 10px">减</div>
                                        <div>
                                            <el-input oninput="this.value = this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');" @blur="changeDiscount(2)" size="small" style="width: 220px;height: 30px;" type="number" :min="0" v-model="form.wholesale_rules[2].discount">
                                            </el-input>
                                        </div>
                                    </div>
                                    <div v-if="form.wholesale_type == 1" class="wholesale-tip">减钱金额不大于最低价{{showPrice}}元</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>
            </el-tab-pane>
        </template>
    </app-goods>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: {
                    attr: [],
                    price: '0',
                    attr_groups: [],
                    wholesale_rules: [
                        {num: '',discount:''},
                        {num: '',discount:''},
                        {num: '',discount:''}
                    ],
                    use_attr: 0,
                    rules_status: 0,
                    rise_num: '',
                    wholesale_type: 0,
                },
                attrBatch: false,
                attr: [],
                newAttr: [],
                use_attr: 0,
                showPrice: '0',
                attr_groups: [],
                selectList: [],
                choose: {
                    name: '',
                    num: '',
                    price: '',
                }
            };
        },
        created() {
            let id = getQuery('id');
            if (id) {
                this.form.id = getQuery('id');
            }
        },
        watch: {
            attr: {
                handler: function(data) {
                    if(JSON.stringify(data).length > 2) {
                        this.newAttr = JSON.parse(JSON.stringify(data));
                    }
                },
                deep: true,
                immediate: true
            },
            attr_groups: {
                handler: function(data) {
                    this.new_attr_groups = JSON.parse(JSON.stringify(data));
                    if (this.new_attr_groups.length === 0 ) {
                        this.new_attr_groups = [
                            {
                                attr_group_id: 1,
                                attr_group_name: "规格",
                                attr_list: {
                                    attr_id: 0,
                                    attr_name: '默认',
                                    pic_url: '',
                                }
                            }
                        ]
                    }
                },
                deep: true,
                immediate: true
            }
        },
        methods: {
            statusChange(e) {
                this.form.rules_status = e;
                if(this.form.rules_status == 1 && !(this.form.wholesale_rules[0].num > 0)) {
                    this.form.wholesale_rules[0].num = this.form.rise_num
                }
                this.$forceUpdate();
            },
            typeChange(e) {
                this.form.wholesale_type = +e;
                if(e == 0) {
                    for(let item of this.form.wholesale_rules) {
                        if(item.discount > 0) {
                            item.discount = item.discount.replace(/^(\-)*(\d+)\.(\d).*$/,'$1$2.$3');
                            if(this.form.wholesale_type == 0) {
                                item.discount = item.discount > 10 ? 10 : item.discount < 0.1 ? 0.1 : item.discount
                            }else {
                                item.discount = item.discount > this.showPrice ? this.showPrice : item.discount
                            }
                        }
                    }
                }
                this.$forceUpdate();
            },
            changeTabs(e,form) {
                this.form.price = form.price
                if(e == 'four') {
                    if(this.form.use_attr == 0) {
                        this.showPrice = this.form.price;
                    }else {
                        this.showPrice = this.form.attr[0].price;
                        for(let item of this.form.attr) {
                            if(+item.price < +this.showPrice) {
                                this.showPrice = item.price
                            }
                        }
                    }
                }
            },
            handleSelectionChange(data) {
                this.selectList = data;
            },
            setAttr(attr, attrGroups,price) {
                this.form.group_list = [];
                this.attr = attr;
                this.form.attr = attr;
                this.form.attr_groups = attrGroups;
                this.form.price = price;
                this.attr_groups = attrGroups;
                if(attrGroups.length == 0) {
                    this.use_attr = 0;
                    this.form.use_attr = 0;
                }else {
                    this.use_attr = 1;
                    this.form.use_attr = 1;
                }
            },
            selectClick() {
                this.$refs.multipleTable.toggleAllSelection();
            },
            // 监听子组件事件
            childrenGoods(e) {
                console.log(e)
                this.form.wholesale_rules = [
                    {num: '',discount:''},
                    {num: '',discount:''},
                    {num: '',discount:''}
                ];
                if(this.form.id) {
                    let rules = e.wholesale_rules.concat(this.form.wholesale_rules);
                    this.form.wholesale_rules = rules.slice(0,3);
                    this.form.rules_status = e.rules_status;
                    this.form.rise_num = e.rise_num;
                    this.form.wholesale_type = e.wholesale_type;
                }else {
                    this.form.rules_status = 0;
                    this.form.rise_num = '';
                    this.form.wholesale_type = 0; 
                }
                this.form.attr = e.attr;
                this.form.price = e.price;
                this.use_attr = e.use_attr;
                this.form.use_attr = e.use_attr;
            },
            changeDiscount(index) {
                if(this.form.wholesale_type == 0) {
                    this.form.wholesale_rules[index].discount = this.form.wholesale_rules[index].discount > 10 ? 10 : this.form.wholesale_rules[index].discount && this.form.wholesale_rules[index].discount < 0.1 ? 0.1 : this.form.wholesale_rules[index].discount
                }else {
                    this.form.wholesale_rules[index].discount = +this.form.wholesale_rules[index].discount > +this.showPrice ? this.showPrice : this.form.wholesale_rules[index].discount
                }
                this.$forceUpdate();
            },
            change() {
                this.$forceUpdate();
            }
        }
    });
</script>
<style>
    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    .el-table__body .el-table__row td:last-of-type {
        padding: 0;
    }

    .el-table__body .el-table__row td:last-of-type>div {
        padding: 0;
    }

    .required-icon:before {
        content: '*';
        color: #F56C6C;
    }

    .step {
        border: 1px solid #e2e2e2;
        margin-left: 60px;
        height: 98px;
        margin-top: -1px;
        width: 700px;
    }
    .step-label {
        height: 98px;
        line-height: 98px;
        width: 100px;
        padding-left: 20px;
        border-right: 1px solid #e2e2e2;
    }
    .step-item {
        padding: 0 20px;
        width: 100%;
    }
    .wholesale-tip {
        position: absolute;
        color: #999;
    }
</style>