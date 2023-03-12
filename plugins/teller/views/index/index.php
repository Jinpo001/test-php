<?php
Yii::$app->loadViewComponent('goods/app-add-cat');
?>
<style>
    .rig {
        margin-left: 24px;
    }

    .rig.color {
        color: #c9c9c9;
    }

    .el-alert {
        padding: 0;
        padding-left: 5px;
        padding-bottom: 5px;
    }

    .el-alert--info .el-alert__description {
        color: #606266;
    }

    .el-alert .el-button {
        margin-left: 20px;
    }

    .el-alert__content {
        display: flex;
        align-items: center;
    }

    .table-body .el-alert__title {
        margin-top: 5px;
        font-weight: 400;
    }

    .el-tooltip__popper {
        max-width: 400px
    }

    .y-card {
        border: 0;
        margin-bottom: 12px;
    }

    .y-card .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .y-card .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

    .y-card .y-input {
        width: 30vw;
    }
    .el-alert__content .el-alert__title.is-bold{
        padding-top: 5px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div id="NewsToolBox"></div>
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <el-form v-loading="listLoading" label-width="172px" size="small" :model="ruleForm" :rules="ruleFormRules"
                 ref="ruleForm">
            <!-- 基础设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>基础设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <div style="margin:0 20px 20px">
                        <el-alert type="info" title="入口：" :closable="false">
                            <template>
                                <span>{{ruleForm.login_url}}</span>
                                <el-button @click="copyInput(ruleForm.login_url)" size="mini">复制链接</el-button>
                            </template>
                        </el-alert>
                    </div>
                    <el-form-item label="是否开启交班" prop="is_shifts">
                        <el-switch v-model="ruleForm.is_shifts" :active-value="1" :inactive-value="0"></el-switch>
                        <el-select v-if="ruleForm.is_shifts == 1"
                                   class="rig"
                                   size="small"
                                   v-model="ruleForm.shifts_print"
                                   placeholder="请选择小票打印机">
                            <el-option v-for="(item,index) in printList"
                                       :key="index"
                                       :label="item.printer_name"
                                       :value="parseInt(item.id)"
                            ></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="是否开启会员充值" prop="is_member_topup">
                        <el-switch v-model="ruleForm.is_member_topup" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item label="加钱开关" prop="is_add_money">
                        <el-switch v-model="ruleForm.is_add_money" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 购买设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>购买设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item v-if="ruleForm.svip_status >= 0" label="是否开启分销" prop="is_share">
                        <el-switch v-model="ruleForm.is_share" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig" style="font-size: 14px">
                            <span style="color:#ff4544">注：必须在“</span>
                            <el-button style="font-size: inherit"
                                       @click="$navigate({r:'mall/share/basic'},true)"
                                       type="text">分销中心=>基础设置</el-button>
                            <span style="color:#ff4544">”中开启，才能使用</span>
                        </span>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 支付设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>支付设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="微信支付" prop="is_wechat_pay">
                        <el-switch v-model="ruleForm.is_wechat_pay" :active-value="1" :inactive-value="0"
                                   :disabled="!!ruleForm.is_wechat_pay && ['is_ali_pay', 'is_cash', 'is_balance', 'is_pos'].every(i => {return ruleForm[i] == 0})"
                        ></el-switch>
                        <el-select v-if="ruleForm.is_wechat_pay == 1" class="rig" size="small" filterable
                                   v-model.number="ruleForm.wechat_pay_id"
                                   placeholder="请选择">
                            <el-option
                                    v-for="item in wxOption"
                                    :key="item.label"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="支付宝支付" prop="is_ali_pay">
                        <el-switch v-model="ruleForm.is_ali_pay" :active-value="1" :inactive-value="0"
                                   :disabled="!!ruleForm.is_ali_pay && ['is_wechat_pay', 'is_cash', 'is_balance', 'is_pos'].every(i => {return ruleForm[i] == 0})"
                        ></el-switch>
                        <el-select v-if="ruleForm.is_ali_pay == 1" class="rig" size="small" filterable
                                   v-model.number="ruleForm.ali_pay_id"
                                   placeholder="请选择">
                            <el-option
                                    v-for="item in aliOption"
                                    :key="item.label"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="现金支付" prop="is_cash">
                    <el-switch v-model="ruleForm.is_cash" :active-value="1" :inactive-value="0"
                               :disabled="!!ruleForm.is_cash && ['is_wechat_pay','is_ali_pay', 'is_balance', 'is_pos'].every(i => {return ruleForm[i] == 0})"
                    ></el-switch>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_balance != -1" label="余额支付" prop="is_balance">
                        <el-switch v-model="ruleForm.is_balance" :active-value="1" :inactive-value="0"
                                   :disabled="!!ruleForm.is_balance && ['is_wechat_pay','is_ali_pay', 'is_cash', 'is_pos'].every(i => {return ruleForm[i] == 0})"
                        ></el-switch>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_balance_pay_password != -1 && ruleForm.is_balance == 1" label="余额支付密码开关" prop="is_balance_pay_password">
                        <el-switch v-model="ruleForm.is_balance_pay_password" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig" style="font-size: 14px">
                            <span style="color:#ff4544">注：必须在“</span>
                            <el-button style="font-size: inherit"
                                       @click="$navigate({r:'mall/index/setting'},true)"
                                       type="text">设置=>基础设置</el-button>
                            <span style="color:#ff4544">”设置支付密码，才能使用</span>
                        </span>
                    </el-form-item>
                    <el-form-item label="POS支付" prop="is_pos">
                        <el-switch v-model="ruleForm.is_pos" :active-value="1" :inactive-value="0"
                                   :disabled="!!ruleForm.is_pos && ['is_wechat_pay','is_ali_pay', 'is_cash', 'is_balance'].every(i => {return ruleForm[i] == 0})"
                        ></el-switch>
                    </el-form-item>
                </div>
            </el-card>

            <!-- Tab页签设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>Tab页签设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="开关" prop="is_tab">
                        <el-switch v-model="ruleForm.is_tab" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig color">设置收银台右边tab页签，最多添加10个常用分类</span>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_tab == 1" label="分类" prop="tab_list">
                        <draggable v-model="ruleForm.new_tab_list"
                                   :options="{filter:'.item-drag',preventOnFilter:false}">
                            <el-tag style="margin-right: 5px;margin-bottom:5px;cursor:move"
                                    v-for="(item,index) in ruleForm.new_tab_list"
                                    :key="index" type="warning" closable disable-transitions
                                    @close="destroyCat(item,index)"
                            >{{item.label}}
                            </el-tag>
                            <el-button class="item-drag" @click="$refs.tab_list.openDialog()">选择分类</el-button>
                        </draggable>
                        <app-add-cat ref="tab_list" :select-max="10" :new-cats="ruleForm.tab_list" @select="selectCat"></app-add-cat>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 优惠叠加设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>优惠叠加设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="优惠券" prop="is_coupon">
                        <el-switch v-model="ruleForm.is_coupon" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.svip_status >= 0" label="超级会员卡" prop="svip_status">
                        <el-switch v-model="ruleForm.svip_status" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig" style="font-size: 14px">
                            <span style="color:#ff4544">注：必须在“</span>
                            <el-button style="font-size: inherit"
                                       @click="$navigate({r:'plugin/vip_card/mall/setting/index'},true)"
                                       type="text">插件中心=>超级会员卡</el-button>
                            <span style="color:#ff4544">”中开启，才能使用</span>
                        </span>
                    </el-form-item>
                    <el-form-item label="会员价" prop="is_member_price">
                        <el-switch v-model="ruleForm.is_member_price" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item label="积分抵扣" prop="is_integral">
                        <el-switch v-model="ruleForm.is_integral" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_full_reduce != -1" label="满减优惠" prop="is_full_reduce">
                        <el-switch v-model="ruleForm.is_full_reduce" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 抹零设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>抹零设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="抹零设置开关" prop="is_price">
                        <el-switch v-model="ruleForm.is_price" :active-value="1" :inactive-value="0"></el-switch>
                        <div v-if="ruleForm.is_price == 1" style="color: #606266">
                            <div style="margin-top: 10px">
                                <el-radio style="width: 3vw" v-model="ruleForm.price_type" :label="1">抹分</el-radio>
                                <span class="rig color">向下抹分，如9.99元，则收9.9元</span>
                            </div>
                            <div style="margin-top: 10px">
                                <el-radio style="width: 3vw" v-model="ruleForm.price_type" :label="2">抹角</el-radio>
                                <span class="rig color">向下抹角，如9.99元，则收9元</span>
                            </div>
                            <div style="margin-top: 10px">
                                <el-radio style="width: 3vw" v-model="ruleForm.price_type" :label="3">四舍分</el-radio>
                                <span class="rig color">如9.94元，则收9.9元</span>
                            </div>
                            <div style="margin-top: 10px">
                                <el-radio style="width: 3vw" v-model="ruleForm.price_type" :label="4">五入到角</el-radio>
                                <span class="rig color">如9.99元，则收10.00元</span>
                            </div>
                        </div>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 提成设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>提成设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="收银员提成" prop="is_cashier_push">
                        <el-switch v-model="ruleForm.is_cashier_push" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig" style="font-size: 14px">
                            <span style="color:#ff4544">注：请到“</span>
                            <el-button style="font-size: inherit"
                                       @click="$navigate({r:'plugin/teller/mall/cashier/index'},true)"
                                       type="text">收银台=>收银员</el-button>
                            <span style="color:#ff4544">”设置收银员</span>
                        </span>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_cashier_push == 1" label="提成类型" prop="cashier_push_type">
                        <el-radio v-model="ruleForm.cashier_push_type" :label="1">按订单</el-radio>
                        <el-radio v-model="ruleForm.cashier_push_type" :label="2">按金额</el-radio>
                        <br>
                        <el-input v-if="ruleForm.cashier_push_type == 1"
                                  style="margin-top: 12px" size="small"
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  :min="0"
                                  class="y-input" v-model="ruleForm.cashier_push">
                            <template slot="append">元</template>
                        </el-input>

                        <el-input v-if="ruleForm.cashier_push_type == 2"
                                  style="margin-top: 12px" size="small"
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  :min="0"
                                  class="y-input" v-model="ruleForm.cashier_push_percent">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>

                    <el-form-item label="导购员提成" prop="is_sales_push">
                        <el-switch v-model="ruleForm.is_sales_push" :active-value="1" :inactive-value="0"></el-switch>
                        <span class="rig" style="font-size: 14px">
                            <span style="color:#ff4544">注：请到“</span>
                            <el-button style="font-size: inherit"
                                       @click="$navigate({r:'plugin/teller/mall/sales/index'},true)"
                                       type="text">收银台=>导购员</el-button>
                            <span style="color:#ff4544">”设置导购员</span>
                        </span>
                    </el-form-item>
                    <el-form-item v-if="ruleForm.is_sales_push == 1" label="提成类型" prop="sales_push_type">
                        <el-radio v-model="ruleForm.sales_push_type" :label="1">按订单</el-radio>
                        <el-radio v-model="ruleForm.sales_push_type" :label="2">按金额</el-radio>
                        <br>
                        <el-input v-if="ruleForm.sales_push_type == 1" style="margin-top: 12px"
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  size="small"
                                  class="y-input" v-model="ruleForm.sales_push">
                            <template slot="append">元</template>
                        </el-input>

                        <el-input v-if="ruleForm.sales_push_type == 2" v-if="ruleForm.cashier_push_type == 1"
                                  style="margin-top: 12px"
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  size="small"
                                  class="y-input" v-model="ruleForm.sales_push_percent">
                            <template slot="append">%</template>
                        </el-input>
                    </el-form-item>
                </div>
            </el-card>

            <!-- 改价设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>改价设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="商品改价开关" prop="is_goods_change_price">
                        <el-switch v-model="ruleForm.is_goods_change_price" :active-value="1"
                                   :inactive-value="0"></el-switch>
                    </el-form-item>
                    <template v-if="ruleForm.is_goods_change_price == 1">
                        <el-form-item label="改价类型" prop="is_goods_change_price_type">
                            <el-radio v-model="ruleForm.is_goods_change_price_type" :label="1">固定金额</el-radio>
                            <el-radio v-model="ruleForm.is_goods_change_price_type" :label="2">百分比</el-radio>
                        </el-form-item>

                        <template v-if="ruleForm.is_goods_change_price_type == 1">
                            <el-form-item label="最多可加" prop="most_plus">
                                <el-input type="number" min="0"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                          class="y-input" size="small" v-model="ruleForm.most_plus">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="最多可减" prop="most_subtract">
                                <el-input type="number" min="0"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                          class="y-input" size="small" v-model="ruleForm.most_subtract">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                        </template>
                        <template v-if="ruleForm.is_goods_change_price_type == 2">
                            <el-form-item label="最多可加" prop="most_plus_percent">
                                <el-input type="number" min="0"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                          class="y-input" size="small" v-model="ruleForm.most_plus_percent">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="最多可减" prop="most_subtract_percent">
                                <el-input type="number" min="0"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                          class="y-input" size="small" v-model="ruleForm.most_subtract_percent">
                                    <template slot="append">%</template>
                                </el-input>
                            </el-form-item>
                        </template>
                    </template>
                </div>
            </el-card>

            <!-- 版权设置 -->
            <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
                <div slot="header">
                    <div style="he">
                        <span>版权设置</span>
                    </div>
                </div>
                <div class="form-body">
                    <el-form-item label="收银台登录页logo" prop="logo_url">
                        <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                        @selected="tellerLogoPic">
                            <el-tooltip effect="dark"
                                        content="建议尺寸:120 * 120"
                                        placement="top">
                                <el-button size="mini">选择图标</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1"
                                            @selected="tellerLogoPic">
                                <app-image mode="aspectFill"
                                           width="80px"
                                           height='80px'
                                           :src="ruleForm.logo_url">
                                </app-image>
                            </app-attachment>
                            <el-button v-if="ruleForm.logo_url" class="del-btn"
                                       size="mini" type="danger" icon="el-icon-close"
                                       circle
                                       @click="removeTellerLogoPic"></el-button>
                        </div>
                        <el-button size="mini" @click="resetImg('logo_url')" class="reset" type="primary">恢复默认
                        </el-button>
                    </el-form-item>
                    <el-form-item label="收银台登录页背景图" prop="background_image_url">
                        <app-attachment style="margin-bottom:10px" :multiple="false" :max="1"
                                        @selected="tellerBgPic">
                            <el-tooltip effect="dark"
                                        content="建议尺寸:1980 * 1080"
                                        placement="top">
                                <el-button size="mini">选择图片</el-button>
                            </el-tooltip>
                        </app-attachment>
                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                            <app-attachment :multiple="false" :max="1"
                                            @selected="tellerBgPic">
                                <app-image mode="aspectFill"
                                           width="80px"
                                           height='80px'
                                           :src="ruleForm.background_image_url">
                                </app-image>
                            </app-attachment>
                            <el-button v-if="ruleForm.background_image_url" class="del-btn"
                                       size="mini" type="danger" icon="el-icon-close"
                                       circle
                                       @click="removeTellerBgPic"></el-button>
                        </div>
                        <el-button size="mini" @click="resetImg('background_image_url')" class="reset" type="primary">
                            恢复默认
                        </el-button>
                    </el-form-item>
                    <el-form-item label="底部版权信息" prop="copyright">
                        <el-input class="y-input" size="small" v-model="ruleForm.copyright"></el-input>
                    </el-form-item>
                    <el-form-item label="底部版权链接" prop="copyright_url">
                        <el-input class="y-input" size="small" placeholder="例如 https://www.baidu.com"
                                  v-model="ruleForm.copyright_url"></el-input>
                    </el-form-item>
                </div>
            </el-card>
        </el-form>
        <el-button size="small" type="primary" :loading="btnLoading" @click="submit">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                components: null,
                wxOption: [],
                aliOption: [],
                printList: [],
                ruleForm: {
                    is_share: '',
                    is_wechat_pay: '',
                    wechat_pay_id: '',
                    is_ali_pay: '',
                    ali_pay_id: '',
                    is_cash: '',
                    is_balance: '',
                    is_balance_pay_password: '',
                    is_pos: '',
                    new_tab_list: [],
                    is_shifts: 0,// 是否开启交班
                    shifts_print: '',// 交班打印机
                    is_member_topup: 0,// 是否开启会员充值
                    is_add_money: 0,// 是否开启加钱开关
                    is_tab: 0,// 是否开启tab标签
                    tab_list: [], // tab标签列表

                    is_coupon: 1, // 是否使用优惠券
                    svip_status: 1, // -1.未安装超级会员卡 1.开启 0.关闭
                    is_member_price: 1, // 是否使用会员价
                    is_integral: 1, // 是否使用积分
                    is_full_reduce: 1, // 是否优惠满减

                    is_price: 0, // 是否开启抹零设置
                    price_type: 1,// 1.抹分|2.抹角|3.四舍分|4.五入到角

                    is_cashier_push: 0, // 是否开启收银员提成
                    cashier_push_type: 1,// 1.按订单|2.按金额百分比
                    cashier_push: 0, // 收银员提成按订单
                    cashier_push_percent: 0, // 收银员提成按金额百分比

                    is_sales_push: 0, // 是否开启导购员提成
                    sales_push_type: 1, // 1.按订单|2.按金额百分比
                    sales_push: 0, // 导购员提成按订单
                    sales_push_percent: 0, // 导购员提成按金额百分比

                    is_goods_change_price: 0, // 是否开启商品改价
                    is_goods_change_price_type: 1, // 1.固定金额|2.百分比
                    most_plus: 0, //最多可加金额
                    most_subtract: 0, //最多可减金额
                    most_plus_percent: 0, //最多可加金额
                    most_subtract_percent: 0, //最多可减金额

                    login_url: '', // 登录入口
                    logo_url: '',
                    background_image_url: '',
                    copyright: '',
                    copyright_url: '',
                },
                ruleFormRules: {
                    // is_shifts: [
                    //     {required: true, message: '商品不能为空', trigger: 'blur'},
                    // ],
                },
                listLoading: false,
                btnLoading: false,
            };
        },
        mounted: function () {
            this.getSetting();
            this.getPrint();
            this.getOption();
        },
        watch: {
            'ruleForm.new_tab_list'(newData) {
                let arr = [];
                newData.map(v => {
                    arr.push(v.value);
                });
                this.ruleForm.tab_list = arr;
                this.$refs.ruleForm.validateField('tab_list');
            }
        },
        methods: {
            getOption() {
                request({
                    params: {
                        r: 'mall/pay-type/index',
                        limit: 99999,
                    }
                }).then(e => {
                    if (e.data.code === 0) {
                        let wxOption = [{label: '不启用', value: 0}];
                        let aliOption = [{label: '不启用', value: 0}];
                        e.data.data.list.forEach(item => {
                            if (item.type == 2) {
                                aliOption.push({label: item.name, value: item.id});
                            }
                            if (item.type == 1) {
                                wxOption.push({label: item.name, value: item.id});
                            }
                        })
                        this.wxOption = wxOption;
                        this.aliOption = aliOption;
                    }
                })
            },
            submit() {
                this.$refs.ruleForm.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.ruleForm);
                        request({
                            params: {
                                r: 'plugin/teller/mall/index/index',
                            },
                            data: {
                                form: JSON.stringify(para)
                            },
                            method: 'POST'
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },
            getSetting() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/teller/mall/index/index',
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.ruleForm = e.data.data.setting;
                });
            },
            getPrint() {
                request({
                    params: {
                        r: 'plugin/teller/mall/printer/options',
                    },
                    method: 'get',
                }).then(e => {
                    this.printList = e.data.data.list;
                });
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
            copyInput(text) {
                if (this.copyText(text)) {
                    this.$message.success('复制成功');
                } else {
                    this.$message.error('复制失败');
                }
            },
            selectCat(tab_list) {
                this.ruleForm.new_tab_list = tab_list.concat().splice(0, 10);
            },
            destroyCat(value, index) {
                this.ruleForm.new_tab_list.splice(index, 1)
            },

            resetImg(type) {
                const path = "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>";
                switch (type) {
                    case 'logo_url':
                        this.ruleForm.logo_url = path + '/img/logo.png'
                        return;
                    case 'background_image_url':
                        this.ruleForm.background_image_url = path + '/img/bg.png'
                        return;
                    default:
                        throw Error('ERROR');
                }
            },
            tellerLogoPic(e) {
                if (e.length) {
                    this.ruleForm.logo_url = e[0].url;
                }
            },
            removeTellerLogoPic() {
                this.ruleForm.logo_url = '';
            },
            tellerBgPic(e) {
                if (e.length) {
                    this.ruleForm.background_image_url = e[0].url;
                }
            },
            removeTellerBgPic() {
                this.ruleForm.background_image_url = '';
            },
        },
    });
</script>
