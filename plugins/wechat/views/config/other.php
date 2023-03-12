<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/29
 * Time: 4:08 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */
$baseUrl = Yii::$app->request->baseUrl;
Yii::$app->loadViewComponent('app-rich-text');
?>
<style>
    .title {
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        padding: 20px 0 40px;
        background-color: #fff;
        margin-bottom: 10px;
        padding-right: 40%;
        min-width: 900px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" v-loading="cardLoading" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>其他设置</span>
        </div>
        <el-form @submit.native.prevent :model="form" label-width="150px" ref="form">
	        <div class="title">
	            <span>强制关注公众号</span>
	        </div>
	        <div class="form-body">
	            <el-form-item class="switch" label="选择页面" prop="page">
	            	<div style="width: 520px;">
                        <el-checkbox v-for="(item,index) in form.list" :key="index" style="width: 140px;" :checked="item.check == 1" :label="item.key" @change="change(item,index)">{{item.name}}</el-checkbox>
	            	</div>
		        </el-form-item>
	        </div>
	    </el-form>
        <el-button class='button-item' :loading="btnLoading" type="primary" @click="store" size="small">保存</el-button>
    </el-card>
</div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
            	cardLoading: false,
            	btnLoading: false,
                form: {
                    list: []
                },
            };
        },
        created() {
            this.getDetail();
        },
        methods: {
            change(item,index) {
                this.form.list[index].check = this.form.list[index].check == 0 ? 1 : 0
            },
            store(formName) {
                let self = this;
                self.btnLoading = true;
                request({
                    params: {
                        r: 'plugin/wechat/mall/config/other'
                    },
                    method: 'post',
                    data: {
                        list:self.form.list
                    }
                }).then(e => {
                    self.btnLoading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/wechat/mall/config/other',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.form.list = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
    });
</script>