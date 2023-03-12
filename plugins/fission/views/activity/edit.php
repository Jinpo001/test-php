<?php
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .input-item {
        display: inline-block;
        width: 250px;
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

    .input-item .el-input-group__prepend {
        background-color: #fff;
    }

    .dialog-choose .el-table {
        max-height: 500px;
        overflow: auto;
    }

    .header-box {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 10px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }

    .form-body {
        padding: 20px 20px 0;
        background-color: #fff;
    }

    .out-max {
        width: 500px;
    }

    .app-guarantee-bg {
        position: relative;
        width: 524px;
        height: 627px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .y-card {
        margin: 24px;
    }

    .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .remake-title {
        line-height: 1;
        color: #999999;
    }

    .dialog-choose-radio .input-item {
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <div slot="header" class="header-box">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                 <span style="color: #409EFF;cursor: pointer"
                       @click="$navigate({r:'plugin/fission/mall/activity/index'})">
                    红包墙活动
                 </span>
            </el-breadcrumb-item>
            <el-breadcrumb-item>新建活动</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <!-- 参与者详情 -->
    <el-form :model="ruleForm" v-loading="listLoading" style="background: #FFFFFF;padding-top: 0.1px" ref="ruleForm"
             :rules="ruleFormRules" label-width="130px" size="small">
        <!-- 活动设置 -->
        <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
            <div slot="header">
                <span>活动设置</span>
            </div>
            <div class="form-body">
                <el-form-item prop="name" label="活动名称">
                    <el-input class="out-max"
                              v-model="ruleForm.name"
                              maxlength="10"
                              show-word-limit
                              size="small"
                    ></el-input>
                </el-form-item>
                <el-form-item prop="start_time" label="开始时间">
                    <el-date-picker
                            type="datetime"
                            v-model="ruleForm.start_time"
                            :disabled="editDisabled"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            :picker-options="expireTimeOption"
                            placeholder="选择日期"
                            size="small">
                    </el-date-picker>
                </el-form-item>
                <el-form-item prop="end_time" label="结束时间">
                    <el-date-picker
                            type="datetime"
                            v-model="ruleForm.end_time"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            :picker-options="expireTimeOption"
                            placeholder="选择日期"
                            size="small">
                    </el-date-picker>
                </el-form-item>
                <el-form-item prop="style" label="红包墙样式">
                    <el-radio v-model="ruleForm.style" :label="1">样式一</el-radio>
                    <el-radio v-model="ruleForm.style" :label="2">样式二</el-radio>
                    <div class="remake-title">
                        <el-button @click="dialogVisible = true" style="padding:0" type="text">查看图例</el-button>
                    </div>
                </el-form-item>
                <el-form-item prop="expire_time" label="未兑换过期时间">
                    <el-input class="out-max" v-model="ruleForm.expire_time" size="small"
                              oninput="this.value = this.value.match(/[1-9]\d*/)"
                              v-if="ruleForm.expire_time !== 0"
                    >
                        <template slot="append">天</template>
                    </el-input>
                    <el-checkbox v-model="ruleForm.expire_time" :true-label="0" :false-label="1">无限制</el-checkbox>
                </el-form-item>
            </div>
        </el-card>
        <!--  红包墙设置 -->
        <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
            <div slot="header">
                <span>红包墙设置</span>
            </div>
            <div class="form-body">
                <el-form-item prop="number">
                    <label slot="label">
                        <span>红包个数</span>
                        <el-tooltip class="item" effect="dark" placement="top" content="红包个数包含用户自己">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>

                    <el-input class="out-max"
                              :disabled="editDisabled"
                              v-model="ruleForm.number"
                              oninput="this.value = this.value.match(/100|[1-9]{1}[0-9]{0,1}/)"
                              show-word-limit
                              size="small"
                    ></el-input>
                    <div style="line-height: 1;color: #999999;margin-top: 5px">
                        1 < 红包个数 ≤ 100
                    </div>
                </el-form-item>
                <el-form-item prop="reward_status" label="红包种类">
                    <el-radio v-model="ruleForm.reward_status" label="cash">现金红包</el-radio>
                    <el-radio v-model="ruleForm.reward_status" label="balance">商城余额</el-radio>
                    <el-radio v-model="ruleForm.reward_status" label="coupon">优惠券</el-radio>
                    <div v-if="ruleForm.reward_status === 'cash'" class="remake-title">
                        注：现金红包通过用户添加客服，客服微信发放
                    </div>
                </el-form-item>
                <el-form-item v-if="ruleForm.reward_status === 'coupon'" prop="reward_coupon_id" label="选择优惠券">
                    <el-tag v-if="ruleForm.reward_coupon_id && ruleForm.coupon" closable
                            @close="ruleForm.reward_coupon_id = 0;ruleForm.coupon = null">1张 |
                        {{ruleForm.coupon.name}}
                    </el-tag>
                    <el-button v-else size="small" @click="open('coupon', '-1')">选择优惠券</el-button>
                    <div class="remake-title" style="line-height: 32px">
                        若选择的优惠券库存为0，则会提示后续参与用户活动已结束
                    </div>
                </el-form-item>
                <el-form-item v-else prop="reward_send_type" label="红包金额">
                    <el-radio v-model="ruleForm.reward_send_type" label="random">金额随机</el-radio>
                    <el-radio v-model="ruleForm.reward_send_type" label="average">固定金额</el-radio>
                    <div v-if="ruleForm.reward_send_type === 'random'">
                        <el-input style="width: 200px;"
                                  v-model="ruleForm.reward_min_number"
                                  show-word-limit
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  :min="0"
                                  size="small"
                        >
                            <template slot="append">元</template>
                        </el-input>
                        <span style="margin: 0 5px"> ~ </span>
                        <el-input style="width: 200px;"
                                  v-model="ruleForm.reward_max_number"
                                  show-word-limit
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  :min="0"
                                  size="small"
                        >
                            <template slot="append">元</template>
                        </el-input>
                    </div>
                    <div v-if="ruleForm.reward_send_type === 'average'">
                        <el-input class="out-max"
                                  v-model="ruleForm.reward_min_number"
                                  show-word-limit
                                  oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                  :min="0"
                                  size="small"
                        >
                            <template slot="append">元</template>
                        </el-input>
                    </div>
                </el-form-item>
            </div>
        </el-card>
        <!-- 关卡解锁设置 -->
        <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
            <div slot="header">
                <span>关卡解锁设置</span>
            </div>
            <div class="form-body">
                <div style="position: relative" v-for="(item,index) of ruleForm.level_list">
                    <el-card shadow="never" style="margin-bottom: 20px;width: 700px">
                        <div slot="header">
                            <span style="position:absolute;top: 10px">{{['关卡一', '关卡二', '关卡三', '关卡四', '关卡五'][index]}}</span>
                        </div>
                        <div>
                            <el-form-item prop="people_number">
                                <label slot="label">
                                    <span>邀请</span>
                                    <span :hidden="true">{{allPeople}}</span>
                                    <el-tooltip class="item" effect="dark" placement="top">
                                        <div slot="content">邀请人数不包含用户自己
                                            <br>例，第一关卡设置10人，第二关卡设置5人，
                                            <br>需先邀请10人解锁第一关卡，再邀请5人解锁第二关卡，不能跳关
                                        </div>
                                        <i class="el-icon-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input :disabled="index + 1 === ruleForm.level_list.length" style="width: 180px"
                                          v-model="item.people_number" size="small">
                                    <template slot="append">人</template>
                                </el-input>
                                <span style="margin-left: 12px">解锁</span>
                            </el-form-item>

                            <el-form-item label="奖励">
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="cash">现金红包
                                </el-radio>
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="balance">余额
                                </el-radio>
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="coupon">优惠券
                                </el-radio>
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="card">卡券</el-radio>
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="integral">积分
                                </el-radio>
                                <el-radio @change="changeStatus(item)" v-model="item.status" label="goods">奖品</el-radio>
                            </el-form-item>
                            <el-form-item v-if="item.status === 'cash'" label="红包金额">
                                <el-input style="width: 310px" v-model="item.min_number" size="small"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)">
                                    <template slot="append">元</template>
                                </el-input>
                                <div class="remake-title" style="padding-top: 5px">注：现金红包通过用户添加客服，客服微信发放</div>
                            </el-form-item>
                            <el-form-item v-if="item.status === 'balance'" label="余额金额">
                                <el-input style="width: 310px" v-model="item.min_number" size="small"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)">
                                    <template slot="append">元</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item v-if="item.status === 'coupon'" label="优惠券">
                                <el-tag v-if="item.model_id && item.coupon" closable
                                        @close="item.model_id = 0;item.coupon = null">1张 |
                                    {{item.coupon.name}}
                                </el-tag>
                                <el-button v-else @click="open('coupon', index)" size="small">选择优惠券</el-button>
                            </el-form-item>
                            <el-form-item v-if="item.status === 'integral'" label="积分">
                                <el-input style="width: 310px"
                                          oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                          v-model.number="item.min_number"
                                          size="small"
                                >
                                    <template slot="append">分</template>
                                </el-input>
                            </el-form-item>
                            <template v-if="item.status === 'goods'">
                                <el-form-item label="兑奖方式">
                                    <el-radio v-model="item.exchange_type" label="online">线上兑换</el-radio>
                                    <el-radio v-model="item.exchange_type" label="offline">线下兑换</el-radio>
                                </el-form-item>
                                <el-form-item label="奖品">
                                    <div v-if="item.model_id && item.goods" flex="dir:left"
                                         style="border: 1px solid #eeeeee;padding: 12px;width: 450px">
                                        <app-image :src="item.goods.cover_pic" style="margin-right:8px"></app-image>
                                        <div flex="dir:top main:justify">
                                            <div class="remake-title"
                                                 style="color:#353535;max-width: 300px; word-break: break-all;overflow: hidden; white-space: nowrap;text-overflow: ellipsis;">
                                                {{item.goods.name}}
                                            </div>
                                            <div class="remake-title"
                                                 style="color:#c9c9c9;max-width: 300px; word-break: break-all;overflow: hidden; white-space: nowrap;text-overflow: ellipsis;">
                                                {{item.goods.attr_info}}
                                            </div>
                                            <div class="remake-title" style="color: #ff4544">￥{{item.goods.price}}</div>
                                        </div>
                                        <el-tooltip effect="dark" content="删除" placement="top">
                                            <img style="height: 32px;position: absolute;cursor:pointer;right: 91px;top: 20px"
                                                 @click="item.model_id = 0;item.goods = null"
                                                 src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </div>
                                    <el-button v-else @click="open('goods',index)" size="small">选择商品</el-button>
                                </el-form-item>
                            </template>
                            <el-form-item v-if="item.status === 'card'" label="卡券">
                                <el-tag v-if="item.model_id && item.card" closable
                                        @close="item.model_id = 0;item.card = null">1张 |
                                    {{item.card.name}}
                                </el-tag>
                                <el-button v-else @click="open('card', index)" size="small">选择卡券</el-button>
                            </el-form-item>

                            <!-- 次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要次要 -->
                            <template v-if="['goods','card', 'coupon'].indexOf(item.status)!== -1">
                                <div style="margin-left: 90px;color: #999999;padding: 10px 0">若{{item.status !== 'goods'
                                    ? item.status !== 'coupon' ? '卡券': '优惠券':'商品'}}没有库存，选择下列奖励赠送：
                                </div>
                                <el-form-item label="奖励">
                                    <el-radio @change="changeStatus(item.second)" v-model="item.second.status"
                                              label="cash">现金红包
                                    </el-radio>
                                    <el-radio @change="changeStatus(item.second)" v-model="item.second.status"
                                              label="balance">余额
                                    </el-radio>
                                    <el-radio @change="changeStatus(item.second)" v-model="item.second.status"
                                              label="integral">积分
                                    </el-radio>
                                </el-form-item>
                                <el-form-item v-if="item.second.status === 'cash'" label="红包金额">
                                    <el-input style="width: 310px"
                                              oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                              v-model="item.second.min_number"
                                              size="small"
                                    >
                                        <template slot="append">元</template>
                                    </el-input>
                                    <div class="remake-title" style="padding-top: 5px">注：现金红包通过用户添加客服，客服微信发放</div>
                                </el-form-item>

                                <el-form-item v-if="item.second.status === 'balance'" label="余额金额">
                                    <el-input style="width: 310px"
                                              oninput="this.value = this.value.match(/^\d+(\.)?\d{0,2}/g)"
                                              v-model="item.second.min_number"
                                              size="small"
                                    >
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>
                                <el-form-item v-if="item.second.status === 'integral'" label="积分">
                                    <el-input style="width: 310px"
                                              oninput="this.value = this.value.match(/^\d+/g)"
                                              v-model.number="item.second.min_number"
                                              size="small"
                                    >
                                        <template slot="append">分</template>
                                    </el-input>
                                </el-form-item>
                            </template>
                        </div>
                        <div style="position: absolute;top: 3px;left: 700px;background-color: #409EFF;color:#FFFFFF;cursor: pointer"
                             @click="levelListDel(index)">
                            <i class="el-icon-delete" style="padding: 9px"></i>
                        </div>
                    </el-card>
                </div>
                <div>
                    <div style="color:#c9c9c9;padding-bottom:10px">最多添加5个关卡</div>
                    <el-button v-if="ruleForm.level_list.length < 5" type="primary" size="small" plain
                               style="margin-bottom: 20px"
                               @click="levelListAdd">+ 添加关卡
                    </el-button>
                </div>
            </div>
        </el-card>
        <!-- 分享设置 -->
        <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
            <div slot="header">
                <span>分享设置</span>
            </div>
            <div class="form-body">
                <el-form-item prop="app_share_title">
                    <label slot="label">
                        <span>自定义分享标题</span>
                        <el-tooltip class="item" effect="dark" content="分享给好友时，作为分享标题"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input placeholder="请输入分享标题"
                              class="out-max"
                              v-model="ruleForm.app_share_title"
                              size="small"
                    ></el-input>
                    <div>
                        <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'"
                                   type="text">查看图例
                        </el-button>
                    </div>
                </el-form-item>
                <el-form-item prop="app_share_pic">
                    <label slot="label">
                        <span>自定义分享图片</span>
                        <el-tooltip class="item" effect="dark" content="分享给好友时，作为分享图片"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <app-attachment v-model="ruleForm.app_share_pic" :multiple="false" :max="1">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <div class="customize-share-title">
                        <app-image mode="aspectFill" width='80px' height='80px'
                                   :src="ruleForm.app_share_pic ? ruleForm.app_share_pic : ''"></app-image>
                        <el-button v-if="ruleForm.app_share_pic" class="del-btn" size="mini"
                                   type="danger" icon="el-icon-close" circle
                                   @click="ruleForm.app_share_pic = ''"></el-button>
                    </div>
                    <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                               type="text">查看图例
                    </el-button>
                </el-form-item>
            </div>
        </el-card>
        <!-- 活动设置 -->
        <el-card shadow="never" class="y-card" body-style="background-color: #ffffff;padding: 10px 0 0;">
            <div slot="header">
                <span>活动规则</span>
            </div>
            <div class="form-body">
                <el-form-item prop="rule_title" label="规则标题">
                    <el-input placeholder="请输入分享标题"
                              v-model="ruleForm.rule_title"
                              class="out-max"
                              size="small"
                    ></el-input>
                </el-form-item>
                <el-form-item prop="rule_content" label="活动规则">
                    <app-rich-text class="out-max" v-model="ruleForm.rule_content"></app-rich-text>
                </el-form-item>
            </div>
        </el-card>
    </el-form>
    <el-button type="primary" size="small" @click="onSubmit" :loading="btnLoading">保存</el-button>
    <!-- 多弹框 -->
    <el-dialog title="查看红包墙样式图例" top="5vh"
               :visible.sync="dialogVisible" width="30%">
        <div flex="dir:top cross:center" class="app-share">
            <div flex="dir:left" style="padding: 0 20px 15px;width: 100%;font-weight: bold;font-size: 15px">
                <div style="flex-shrink: 1;width:50%" flex="main:center">样式一</div>
                <div style="flex-shrink: 1;width:50%" flex="main:center">样式二</div>
            </div>
            <div class="app-guarantee-bg"
                 style="<?= 'backgroundImage: url(' . \app\helpers\PluginHelper::getPluginBaseAssetsUrl() . '/img/share.png' . ')' ?>"
            ></div>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false" type="primary">我知道了</el-button>
        </div>
    </el-dialog>
    <el-dialog :title="app_share['type'] == 'pic_bg' ? `查看自定义分享图片图例`:`查看自定义分享标题图例`"
               :visible.sync="app_share.dialog" width="30%">
        <div flex="dir:left main:center" class="app-share">
            <div class="app-share-bg"
                 :style="{backgroundImage: 'url('+app_share[app_share.type]+')'}"
            ></div>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
        </div>
    </el-dialog>
    <el-dialog class="dialog-choose" :title="r_title" :visible.sync="r_listDialog" width="50%" @before-close="selectRe">
        <!-- 商品规格 -->
        <div v-if="r_type === 'goods'" class="dialog-goods-list">
            <div style="margin-bottom: 27px;">
                <el-input size="small" v-model="r_search.search.keyword"
                          @clear="search"
                          placeholder="根据名称或ID搜索"
                          @keyup.enter.native="search"
                          clearable autocomplete="off">
                    <template slot="append">
                        <el-button @click="search">搜索</el-button>
                    </template>
                </el-input>
            </div>
            <el-table v-loading="r_listLoading" :data="r_list" border>
                <el-table-column align="center" width="120px" label="ID" props="id">
                    <template slot-scope="props">
                        <el-radio-group @change="selectChange(props.row)" v-model="r_tempId">
                            <el-radio :disabled="props.row.select" :label="props.row.id"></el-radio>
                        </el-radio-group>
                    </template>
                </el-table-column>
                <el-table-column label="名称">
                    <template slot-scope="props">
                        <div flex="dir:left cross:center">
                            <el-image v-if="props.row.goodsWarehouse"
                                      style="height: 50px;width: 50px;margin-right: 10px;flex-shrink: 0"
                                      :src="props.row.goodsWarehouse.cover_pic"></el-image>
                            <app-ellipsis :line="2">{{props.row.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div v-if="r_type === 'attr'" class="dialog-choose-radio">
            <el-table :data="[r_selectForm.goods]" border style="width: 100%">
                <el-table-column label="商品名称">
                    <template slot-scope="props">
                        <div flex="dir:left cross:center">
                            <el-image style="height: 50px;width: 50px;margin-right: 10px;"
                                      :src="props.row.goodsWarehouse.cover_pic"></el-image>
                            <app-ellipsis :line="2">{{props.row.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <el-table :data="r_selectForm.goods.attr" border style="width: 100%;margin-top: 30px">
                <el-table-column align="center" width="100">
                    <template slot-scope="props">
                        <el-radio @change="selectChange(props.row)" v-model="a_tempId" :label="props.row.id"></el-radio>
                    </template>
                </el-table-column>
                <el-table-column
                        v-for="(item, index) in r_selectForm.goods.attr_groups"
                        :key="item.id"
                        :prop="'attr_list['+index+'].attr_name'"
                        :label="item.attr_group_name">
                </el-table-column>
                <el-table-column label="原价">
                    <template slot-scope="scope">
                        ￥{{scope.row.price}}
                    </template>
                </el-table-column>
                <el-table-column label="库存" prop="stock"></el-table-column>
                <el-table-column label="数量" prop="number">
                    <template slot-scope="scope">1</template>
                </el-table-column>
            </el-table>
        </div>
        <div v-if="r_type === 'coupon'" class="dialog-choose-radio" border>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入优惠券名称" v-model="r_search.keyword"
                          clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="r_listLoading" :data="r_list" border>
                <el-table-column width="100">
                    <template slot-scope="props">
                        <el-radio @change="selectChange(props.row)"
                                  v-model="r_tempId"
                                  :label="props.row.id"
                        ></el-radio>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="优惠券名称"></el-table-column>
                <el-table-column width='100' prop="min_price" label="最低消费金额（元）"></el-table-column>
                <el-table-column prop="type" label="优惠方式">
                    <template slot-scope="scope">
                        <div v-if="scope.row.type == 2">优惠:{{scope.row.sub_price}}元</div>
                        <div v-if="scope.row.type == 1">{{scope.row.discount}}折</div>
                        <div v-if="scope.row.discount_limit && scope.row.type == 1">
                            优惠上限:{{scope.row.discount_limit}}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="appoint_type" label="使用范围">
                    <template slot-scope="scope">
                        <span v-if="scope.row.appoint_type == 1">指定商品类目</span>
                        <span v-if="scope.row.appoint_type == 2">指定商品</span>
                        <span v-if="scope.row.appoint_type == 3">全场通用</span>
                        <span v-if="scope.row.appoint_type == 4">当面付</span>
                        <span v-if="scope.row.appoint_type == 5">礼品卡</span>
                    </template>
                </el-table-column>
                <el-table-column width='170' prop="expire_type" label="有效时间">
                    <template slot-scope="scope">
                        <span v-if="scope.row.expire_type == 1">
                        领取{{scope.row.expire_day}}天后过期
                    </span>
                        <span v-else-if="scope.row.expire_type == 2">
                        {{scope.row.begin_time}} - {{scope.row.end_time}}
                    </span>
                    </template>
                </el-table-column>
                <el-table-column width='150' prop="total_count" label="数量">
                    <template slot-scope="scope">
                        <div v-if="scope.row.total_count == -1">
                            <div>总数量：无限制</div>
                            <div>剩余发放数：无限制</div>
                        </div>
                        <div v-else>
                            <div>总数量：{{scope.row.count}}</div>
                            <div>剩余发放数：{{scope.row.total_count}}</div>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div border>
        <div v-if="r_type === 'card'" class="dialog-choose-radio" border>
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入卡券名称搜索" v-model="r_search.keyword"
                          clearable
                          @clear="search">
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="r_listLoading" :data="r_list" border>
                <el-table-column width="100">
                    <template slot-scope="props">
                        <el-radio @change="selectChange(props.row)"
                                  v-model="r_tempId"
                                  :label="props.row.id"
                        ></el-radio>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="卡券名称"></el-table-column>
                <el-table-column label="核销总次数" width="100" prop="number">
                </el-table-column>
                <el-table-column label="卡券图标" width="100">
                    <template slot-scope="scope">
                        <app-image mode="aspectFill" :src="scope.row.pic_url"></app-image>
                    </template>
                </el-table-column>
                <el-table-column label="有效期" width="320">
                    <template slot-scope="scope">
                        <div v-if="scope.row.expire_type == 1">发放之日起<span
                                    class="text-color">{{scope.row.expire_day}}</span>天内
                        </div>
                        <div v-else>
                            <span class="text-color">{{scope.row.begin_time}}</span>
                            - <span class="text-color">{{scope.row.end_time}}</span>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div border>
        <el-pagination
                v-if="r_pagination && r_type !== 'attr'"
                flex="main:center"
                @current-change="changePage"
                background
                :current-page="r_pagination.current_page"
                layout="prev, pager, next"
                :page-count="r_pagination.page_count">
        </el-pagination>
        <span slot="footer" class="dialog-footer">
            <el-button size="small" @click="selectRe">取 消</el-button>
            <el-button size="small" type="primary" @click="selectPush">确 定</el-button>
        </span>
    </el-dialog>
</div>
<script>
    let imageUrl = "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/";
    const app = new Vue({
        el: '#app',
        data() {
            return {
                //////////////////////////////////////////////////////////////////////////////////////////////////////
                //select
                r_tempId: '',
                a_tempId: '',
                r_selectForm: {
                    goods: null,
                    coupon: null,
                    card: null,
                    attr: null,
                },
                r_type: 'goods',
                r_title: '',
                r_list: [],
                r_pagination: null,
                r_search: {
                    keyword: '',
                    search: {
                        keyword: '',
                    },
                    is_time: 1,
                    is_status: 1,
                    is_expired: 1,
                    is_show_attr: 1,
                },
                r_page: 1,
                r_listLoading: true,
                r_listDialog: false,
                r_url: '',
                r_posIndex: '-1',
                //////////////////////////////////////////////////////////////////////////////////////////////////////


                dialogVisible: false,
                editDisabled: false,
                // app_share_title: '', //自定义分享标题,
                // app_share_pic: '', //自定义分享图片
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-pic.png",
                },

                ruleForm: {
                    expire_time: 0,
                    coupon: null,
                    name: '',
                    start_time: '',
                    end_time: '',
                    style: 1,
                    number: '',
                    app_share_title: '',
                    app_share_pic: "<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() . '/img/app-share.png' ?>",
                    rule_title: '红包墙活动规则',
                    rule_content: "<p> 1.领取红包<br/>登录后，领取红包。领取红包后，点击提现，会弹出商家配置的带有微信名片二维码的弹窗，需添加弹窗中的微信二维码为好友，与客服对接兑现。<br/><br/>2.红包墙<br/>如果墙上红包个数为100，则在领取红包后，可获得100个墙上的红包，且可将剩下的99个红包分享给好友。<br/>领取红包的链接是谁生成的，则算作是谁的红包墙中的红包。<br/><br/>3.解锁红包<br/>邀请领取人数达到解锁人数时，便可解锁领取奖励。 </p>",
                    reward_status: 'cash',
                    reward_coupon_id: 0,
                    reward_send_type: 'average',
                    reward_min_number: 0,
                    reward_max_number: 0,
                    level_list: [],
                },
                ruleFormRules: {
                    name: [
                        {required: true, message: '活动名称不能为空', trigger: 'blur'},
                    ],
                    start_time: [
                        {required: true, message: '开始时间不能为空', trigger: 'blur'},
                    ],
                    end_time: [
                        {required: true, message: '结束时间不能为空', trigger: 'blur'},
                    ],
                    number: [
                        {required: true, message: '红包个数不能为空', trigger: 'blur'},
                    ],
                    reward_coupon_id: [
                        {required: true, message: '优惠券不能为空', trigger: 'blur'},
                    ],
                },
                expireTimeOption: {
                    disabledDate(date) {
                        return date.getTime() < Date.now() - 8.64e7;
                    }
                },
                btnLoading: false,
                listLoading: false,
            }
        },
        computed: {
            allPeople() {
                let sum = 0;
                let temp = null;
                this.ruleForm.level_list.forEach((item, index) => {
                    if (index + 1 === this.ruleForm.level_list.length) {
                        temp = item;
                    } else {
                        sum += parseInt(item.people_number);
                    }
                });
                let all_sum = this.ruleForm.number || 0;
                temp.people_number = all_sum - 1 > sum ? all_sum - 1 - sum : 0;
            },
        },
        methods: {
            changeStatus(item) {
                if (item['status'] === 'integral') {
                    item['max_number'] = 0;
                    item['min_number'] = parseInt(item['min_number']);
                }
            },
            //////////////////////////////////////////////////////////////////////////////////////////////////////
            open(r_type, r_posIndex) {
                this.r_posIndex = r_posIndex;
                this.r_type = r_type;

                if (this.r_type === 'goods') {
                    this.r_url = 'mall/goods/index';
                    this.r_title = '选择商品';
                } else if (this.r_type === 'coupon') {
                    this.r_url = 'mall/coupon/index';
                    this.r_title = '选择优惠券'
                } else if (this.r_type === 'card') {
                    this.r_url = 'mall/card/index';
                    this.r_title = '选择卡券'
                }
                this.r_page = 1;
                this.r_tempId = '';
                this.a_tempId = '';
                this.r_list = [];
                this.r_listDialog = true;
                this.search();
            },
            changePage(currentPage) {
                this.r_page = currentPage;
                this.search();
            },
            search() {
                this.r_listLoading = true;
                request({
                    params: Object.assign({
                        r: this.r_url,
                        page: this.r_page,
                    }, this.r_search),
                }).then(e => {
                    if (e.data.code === 0) {
                        this.r_list = e.data.data.list;
                        this.r_pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.r_listLoading = false;
                }).catch(e => {
                    this.r_listLoading = false
                });
            },
            selectChange(row) {
                this.r_selectForm[this.r_type] = row;
            },
            selectRe() {
                if (this.r_type === 'attr') {
                    this.r_type = 'goods';
                    this.r_listDialog = true;
                } else {
                    console.log(1);
                    this.r_listDialog = false;
                }
            },
            selectPush() {
                let param = {};
                let {goods, coupon, card, attr} = this.r_selectForm;
                try {
                    switch (this.r_type) {
                        case 'attr':
                            if (!attr) {
                                throw new Error('请选择规格');
                            }
                            if (attr.stock <= 0) {
                                throw new Error('库存不足请填加商品数量');
                            }
                            let attr_info = '';
                            attr.attr_list.forEach(item => {
                                attr_info += item['attr_group_name'] + ':' + item['attr_name'] + ';';
                            })
                            attr_info = attr_info.substring(0, attr_info.length - 1);
                            Object.assign(param, {
                                goods: {
                                    attr_info,
                                    cover_pic: goods.goodsWarehouse.cover_pic,
                                    id: goods.id,
                                    name: goods.name,
                                    price: goods.price,
                                },
                                attr_id: attr.id,
                                model_id: goods.id,
                            })
                            break;
                        case 'goods':
                            if (!goods) {
                                throw new Error('请选择商品')
                            }
                            return this.r_type = 'attr';
                        case 'coupon':
                            if (!coupon) {
                                throw new Error('请选择优惠券')
                            }
                            Object.assign(param, {
                                coupon: {
                                    id: coupon.id,
                                    name: coupon.name,
                                },
                                model_id: coupon.id,
                            })
                            break;
                        case 'card':
                            if (!card) {
                                throw new Error('请选择卡券')
                            }
                            Object.assign(param, {
                                card: {
                                    id: card.id,
                                    name: card.name,
                                },
                                model_id: card.id,
                            })
                            break;
                        default:
                            throw new Error('程序错误')
                    }
                    if (this.r_posIndex === '-1') {
                        Object.assign(this.ruleForm, {
                            reward_coupon_id: param.model_id,
                            coupon: param.coupon
                        });
                    } else {
                        this.ruleForm.level_list.splice(this.r_posIndex, 1, Object.assign(this.ruleForm.level_list[this.r_posIndex], param))
                    }
                    this.r_listDialog = false;
                } catch (e) {
                    this.$message.error(e.message);
                }
            },
            /////////////////////////////////////////
            levelListDel(index) {
                this.ruleForm.level_list.splice(index, 1);
            },
            levelListAdd() {
                this.ruleForm.level_list.push({
                    status: 'cash',
                    people_number: 0,
                    model_id: 0,
                    exchange_type: "online",
                    min_number: 0,
                    max_number: 0,
                    send_type: "average",
                    second_status: 0,
                    coupon: null,
                    card: null,
                    second: {
                        status: "cash",
                        min_number: 0,
                        max_number: 0,
                        send_type: "average",
                    }
                })
            },
            onSubmit() {
                this.$refs.ruleForm.validate((valid) => {
                    if (valid) {
                        let para = Object.assign({}, this.ruleForm, {
                            level_list: JSON.stringify(this.ruleForm.level_list)
                        });
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/fission/mall/activity/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                                setTimeout(() => {
                                    this.$navigate({
                                        r: 'plugin/fission/mall/activity/index'
                                    });
                                }, 500)
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false
                        });
                    }
                });
            },
            getData() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'plugin/fission/mall/activity/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.$message.success(e.data.msg);
                        this.ruleForm = e.data.data.detail;
                        let date = "<?= date('Y-m-d H:i:s') ?>";
                        let {start_time, end_time} = this.ruleForm;
                        this.editDisabled = end_time > date && start_time < date;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(() => {
                    this.listLoading = false;
                });
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getData();
            }
        }
    });
</script>
