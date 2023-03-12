<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 9:53
 */
$_currentPluginBaseUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl(Yii::$app->plugin->currentPlugin->getName());
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<script>
    const _currentPluginBaseUrl = '<?=$_currentPluginBaseUrl?>';
</script>
<?php
$diyPath = \Yii::$app->viewPath . '/components/diy';
$currentDir = opendir($diyPath);
$mallComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $mallComponents[] = substr($file, 4, (stripos($file, '.php') - 4));
    }
}
closedir($currentDir);
foreach ($mallComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", $diyPath);
}
$currentDir = opendir(__DIR__);
$diyComponents = [];
while (($file = readdir($currentDir)) !== false) {
    if (stripos($file, 'diy-') === 0) {
        $temp = substr($file, 4, (stripos($file, '.php') - 4));
        if (!in_array($temp, $mallComponents)) {
            $diyComponents[] = $temp;
        }
    }
}
closedir($currentDir);
foreach ($diyComponents as $component) {
    Yii::$app->loadViewComponent("diy-{$component}", __DIR__);
}
$components = array_merge($diyComponents, $mallComponents);
Yii::$app->loadViewComponent('app-hotspot');
Yii::$app->loadViewComponent('app-rich-text');
Yii::$app->loadViewComponent('app-radio');
Yii::$app->loadViewComponent('app-g', __DIR__);
Yii::$app->loadViewComponent("app-padding", \Yii::$app->viewPath . '/components/diy');
?>
<style>
    .all-components {
        background: #fff;
        padding: 20px;
    }

    .all-components .component-group {
        border: 1px solid #eeeeee;
        width: 300px;
        margin-bottom: 20px;
    }

    .all-components .component-group:last-child {
        margin-bottom: 0;
    }

    .all-components .component-group-name {
        height: 35px;
        line-height: 35px;
        background: #f7f7f7;
        padding: 0 20px;
        border-bottom: 1px solid #eeeeee;
    }

    .all-components .component-list {
        margin-right: -2px;
        margin-top: -2px;
        flex-wrap: wrap;
    }

    .all-components .component-list .component-item {
        width: 100px;
        height: 100px;
        border: 0 solid #eeeeee;
        border-width: 0 1px 1px 0;
        text-align: center;
        padding: 15px 0 0;
        cursor: pointer;
    }

    .all-components .component-list .component-icon {
        width: 40px;
        height: 40px;
        /*border: 1px solid #eee;*/
    }

    .all-components .component-list .component-name {

    }

    .mobile-framework {
        width: 375px;
        height: 100%;
    }

    .mobile-framework-header {
        position: relative;
        height: 60px;
        /*line-height: 60px;*/
        background: #333;
        color: #fff;
        text-align: center;
        font-size: 15px;
        padding-top: 20px;
        cursor: pointer;
        background: url('statics/img/mall/head-diy.png') no-repeat;
    }

    .mobile-framework-header > div {
        margin-left: 13px;
    }

    .mobile-framework-header .search {
        position: relative;
        background: url('statics/img/app/mall/head-nav-bar-ssss.png') no-repeat;
        background-size: 100% 100%;
        width: 150px;
        height: 27px;
        background-repeat: no-repeat;
    }

    .mobile-framework-header .t-omit {
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
    }

    .mobile-framework-header .search div {
        position: absolute;
        top: 7px;
        line-height: 1;
        left: 12px;
        font-size: 11px;
        max-width: 110px;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
    }

    .mobile-framework-body {
        min-height: 645px;
        border: 1px solid #e2e2e2;
        /* background: #f5f7f9; */
    }

    .mobile-framework .diy-component-preview {
        del-cursor: pointer;
        position: relative;
        zoom: 0.5;
        -moz-transform: scale(0.5);
        -moz-transform-origin: top left;
        font-size: 28px;
    }

    @-moz-document url-prefix() {
        .mobile-framework .diy-component-preview {
            cursor: pointer;
            position: relative;
            -moz-transform: scale(0.5);
            -moz-transform-origin: top left;
            font-size: 28px;
            width: 200% !important;
            height: 100%;
            margin-bottom: auto;
        }
        .mobile-framework .active .diy-component-preview {
            border: 2px dashed #409EFF;
            left: -2px;
            right: -2px;
            width: calc(200% + 4px) !important;
        }
    }

    .mobile-framework .diy-component-preview:hover {
        box-shadow: inset 0 0 10000px rgba(0, 0, 0, .03);
    }

    .mobile-framework .diy-component-edit {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 465px;
        right: 0;
        background: #fff;
        padding: 20px;
        display: none;
        overflow: auto;
    }

    .diy-component-options {
        position: relative;
    }

    .diy-component-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .mobile-framework .active .diy-component-preview {
        border: 2px dashed #409EFF;
        left: -2px;
        right: -2px;
        width: calc(100% + 4px);
    }

    .mobile-framework .active .diy-component-edit {
        display: block;
        del-padding-right: 20%;
        min-width: calc(650px - 20%);
    }

    .all-components {
        max-height: 725px;
        overflow-y: auto;
    }

    .bottom-menu {
        text-align: center;
        height: 54px;
        width: 100%;
    }

    .bottom-menu .el-card__body {
        padding-top: 10px;
    }

    .el-dialog {
        min-width: 800px;
    }

    #ggg div, span {
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none; /* Chrome/Safari/Opera */
        -khtml-user-select: none; /* Konqueror */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none; /* Non-prefixed version, currently */
    }
</style>
<style>
    .app-nav-bar {
        cursor: pointer;
    }

    .app-nav-bar .nav-input {
        width: 400px;
    }

    .app-nav-bar .reset {
        position: absolute;
        top: 7px;
        left: 90px;
    }
</style>
<template id="app-edit">
    <div class="app-edit">
        <el-card shadow="never" style="margin-bottom: 10px">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:`${labelText['page_url']}`})">
                        {{labelText['text']}}
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="has_home == 1">装修首页</el-breadcrumb-item>
                <el-breadcrumb-item v-else-if="id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>新增</el-breadcrumb-item>
            </el-breadcrumb>
        </el-card>
        <div v-loading="loading">
            <div flex="box:first" style="margin-bottom: 10px;min-width: 1280px;height: 725px;">
                <div class="all-components">
                    <el-form v-if="labelText['title'] !== '微页面标题'" @submit.native.prevent label-width="95px"
                             :ref="templateName">
                        <el-form-item :rules="[{ required: true, validator: validate, trigger: 'change'}]"
                                      :label="labelText['title']"
                                      prop="templateName">
                            <el-input size="small" show-word-limit v-model="templateName" maxlength="15"></el-input>
                        </el-form-item>
                    </el-form>
                    <el-form label-width="95px" v-if="type !== 'module'">
                        <el-form-item label="页面设置" required>
                            <el-button size="small" @click="openPageSetting">设置</el-button>
                        </el-form-item>
                        <el-form-item label="设置背景">
                            <el-button size="small" @click="openBgSetting">设置</el-button>
                        </el-form-item>
                    </el-form>
                    <div class="component-group" v-for="group in allComponents">
                        <div class="component-group-name">{{group.groupName}}</div>
                        <div class="component-list" flex="">
                            <template v-for="item in group.list">
                                <div class="component-item"
                                     @click="selectComponent(item)">
                                    <img class="component-icon" :src="item.icon">
                                    <div class="component-name">{{item.name}}</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div style="padding-left: 2px;position: relative;overflow-y: auto">
                    <div id="ggg" style="overflow-y: auto;padding: 0 25px;width: 435px;height: 705px;">
                        <div class="mobile-framework" style="height: 705px;">
                            <div class="mobile-framework-header"
                                 flex="dir:left"
                                 @click="openPageSetting"
                                 :style="{
                                    backgroundColor: appNavBar.backgroundColor,
                                    color: appNavBar.color,
                                    justifyContent: (appNavBar.position == 'center' && appNavBar.style == 1) ? 'center': 'flex-start'
                                }">
                                <div v-if="showLeftIcon" flex="cross:center">
                                    <image flex="cross:center" :style="hw_style"
                                           style="max-width: 50px;max-height: 27px" :src="appNavBar.leftIcon" alt="">
                                </div>
                                <div v-if="showPageTitle" flex="cross:center"
                                     :style="{marginLeft: appNavBar.position == 'center' && appNavBar.style == 1 ? '0': '13px'}">
                                    <div class="t-omit" style="font-size: 15px;font-weight: bold" :style="[maxWidth()]">
                                        {{templateName}}
                                    </div>
                                </div>
                                <div v-if="showLink" flex="cross:center">
                                    <div class="search">
                                        <div :style="{color:appNavBar.placeholderColor}">{{appNavBar.placeholder}}</div>
                                    </div>
                                </div>
                            </div>
                            <div id="mobile-framework-body" class="mobile-framework-body"
                                 :style="'background-color:'+ bg.backgroundColor+';background-image:url('+bg.backgroundPicUrl+');background-size:'+bg.backgroundWidth+'% '+bg.backgroundHeight+'%;background-repeat:'+repeat+';background-position:'+position">
                                <!------------------------------------------------------------------>
                                <div :class="{active: hasNavShow}" class="app-nav-bar">
                                    <div class="diy-component-edit">
                                        <el-form label-width="100px">
                                            <el-card shadow="never">
                                                <div slot="header">
                                                    <span>页面设置</span>
                                                </div>
                                                <el-form @submit.native.prevent label-width="95px" :ref="templateName">
                                                    <el-form-item
                                                            :rules="[{ required: true, validator: validate, trigger: 'change'}]"
                                                            :label="labelText['title']"
                                                            prop="templateName">
                                                        <el-input size="small" show-word-limit v-model="templateName"
                                                                  maxlength="15"></el-input>
                                                    </el-form-item>
                                                </el-form>
                                                <el-form-item label="选择风格" prop="style">
                                                    <el-radio-group v-model="appNavBar.style">
                                                        <el-radio label="1">风格1</el-radio>
                                                        <el-radio label="2">风格2</el-radio>
                                                        <el-radio label="3">风格3</el-radio>
                                                        <el-radio label="4">风格4</el-radio>
                                                    </el-radio-group>
                                                </el-form-item>

                                                <el-form-item v-if="appNavBar.style == 1" label="展示位置" prop="position">
                                                    <el-radio-group v-model="appNavBar.position">
                                                        <el-radio label="center">居中</el-radio>
                                                        <el-radio label="left">左对齐</el-radio>
                                                    </el-radio-group>
                                                </el-form-item>

                                                <template v-if="[2,3].indexOf(Number(appNavBar.style)) !== -1">
                                                    <el-form-item label="图片上传" prop="leftIcon">
                                                        <app-attachment style="margin-bottom:10px" :multiple="false"
                                                                        :max="1"
                                                                        @selected="mallLeftIconPic">
                                                            <el-tooltip effect="dark"
                                                                        content="建议尺寸: 最大宽度100px，最大高度54px"
                                                                        placement="top">
                                                                <el-button size="mini">选择图片</el-button>
                                                            </el-tooltip>
                                                        </app-attachment>
                                                        <div style="margin-right: 20px;display:inline-block;position: relative;cursor: move;">
                                                            <app-attachment :multiple="false" :max="1"
                                                                            @selected="mallLeftIconPic">
                                                                <app-image mode="aspectFill"
                                                                           width="80px"
                                                                           height='80px'
                                                                           :src="appNavBar.leftIcon">
                                                                </app-image>
                                                            </app-attachment>
                                                        </div>
                                                        <el-button size="mini" @click="resetImg('left_icon')"
                                                                   class="reset" type="primary">恢复默认
                                                        </el-button>
                                                    </el-form-item>
                                                </template>
                                                <el-form-item v-if="[2,3,4].indexOf(Number(appNavBar.style)) !== -1"
                                                              label="选择链接" prop="link">
                                                    <el-input :disabled="true" size="small"
                                                              class="nav-input"
                                                              v-model="appNavBar.link.url" autocomplete="off">
                                                        <app-pick-link slot="append" @selected="onSelectLink">
                                                            <el-button size="mini">选择链接</el-button>
                                                        </app-pick-link>
                                                    </el-input>
                                                </el-form-item>
                                                <template v-if="[3,4].indexOf(Number(appNavBar.style)) !== -1">
                                                    <el-form-item label="搜索提示文字" prop="placeholder">
                                                        <el-input size="small" v-model="appNavBar.placeholder"
                                                                  class="nav-input" maxlength="8"
                                                                  show-word-limit></el-input>
                                                    </el-form-item>
                                                    <el-form-item label="文字颜色" prop="placeholderColor">
                                                        <el-color-picker size="small"
                                                                         v-model="appNavBar.placeholderColor"></el-color-picker>
                                                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                                                  v-model="appNavBar.placeholderColor"></el-input>
                                                    </el-form-item>
                                                </template>
                                            </el-card>

                                            <el-card shadow="never" style="margin-top: 12px">
                                                <div slot="header">
                                                    <span>顶部标题栏</span>
                                                </div>
                                                <el-form-item label="使用商城配置" prop="hasMallSetting">
                                                    <el-switch v-model="appNavBar.hasMallSetting" :inactive-value="0"
                                                               :active-value="1"></el-switch>
                                                </el-form-item>
                                                <template v-if="!appNavBar.hasMallSetting">
                                                    <el-form-item v-if="[1,2,4].indexOf(Number(appNavBar.style)) !== -1"
                                                                  label="文字颜色" prop="color">
                                                        <el-radio-group v-model="appNavBar.color">
                                                            <el-radio label="white">白色</el-radio>
                                                            <el-radio label="black">黑色</el-radio>
                                                        </el-radio-group>
                                                    </el-form-item>
                                                    <el-form-item label="背景颜色" prop="backgroundColor">
                                                        <el-color-picker size="small"
                                                                         v-model="appNavBar.backgroundColor"></el-color-picker>
                                                        <el-input size="small" style="width: 80px;margin-right: 25px;"
                                                                  v-model="appNavBar.backgroundColor"></el-input>
                                                    </el-form-item>
                                                </template>
                                            </el-card>
                                        </el-form>
                                    </div>
                                </div>
                                <!-- ---------------------------------------------------------------->
                                <draggable v-model="components" :options="{filter:'.active',preventOnFilter:false}"
                                           v-if="components && components.length" id="child">
                                    <div v-for="(component, index) in components" :key="component.key"
                                         :style="{cursor: component.active ? 'pointer': 'move'}">
                                        <div @click="showComponentEdit(component,index)"
                                             :class="(component.active?'active':'')">
                                            <div class="diy-component-options" v-if="component.active">
                                                <el-button type="primary"
                                                           icon="el-icon-delete"
                                                           @click.stop="deleteComponent(index)"
                                                           style="left: -25px;top:0;"></el-button>
                                                <el-button type="primary"
                                                           icon="el-icon-document-copy"
                                                           @click.stop="copyComponent(index)"
                                                           style="left: -25px;top:30px;"></el-button>
                                                <el-button v-if="index > 0 && components.length > 1"
                                                           type="primary"
                                                           icon="el-icon-arrow-up"
                                                           @click.stop="moveUpComponent(index)"
                                                           style="right: -25px;top:0;"></el-button>
                                                <el-button v-if="components.length > 1 && index < components.length-1"
                                                           type="primary"
                                                           icon="el-icon-arrow-down"
                                                           @click.stop="moveDownComponent(index)"
                                                           style="right: -25px;top:30px;"></el-button>
                                            </div>
                                            <?php foreach ($components as $component) : ?>
                                                <diy-<?= $component ?> v-if="component.id === '<?= $component ?>'"
                                                                       :active="component.active"
                                                                       v-model="component.data"
                                                ></diy-<?= $component ?>>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </draggable>
                                <div v-else flex="main:center cross:center"
                                     style="height: 200px;color: #adb1b8;text-align: center;">
                                    <div>
                                        <i class="el-icon-folder-opened"
                                           style="font-size: 32px;margin-bottom: 10px"></i>
                                        <div>空空如也</div>
                                        <div>请从左侧组件库添加组件</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <el-dialog title="背景设置" :visible.sync="bgVisible">
            <el-form @submit.native.prevent label-width="100px">
                <diy-bg v-if="bgVisible" :data="bgSetting" :background="bgVisible" :hr="!bgVisible" @update="updateData"
                        @toggle="toggleData" @change="changeData"></diy-bg>
            </el-form>
            <div slot="footer">
                <el-button size="small" @click="bgVisible = false">取消</el-button>
                <el-button size="small" @click="beSettingBg" type="primary">确定</el-button>
            </div>
        </el-dialog>
        <el-card class="bottom-menu" shadow="never">
            <div>
                <el-button size="small" @click="save(false)" type="primary" :loading="submitLoading">保存</el-button>
                <el-button v-if="has_home != 1" size="small" @click="saveAs" :loading="submitLoading">另存为</el-button>
            </div>
        </el-card>
    </div>
</template>
<script>
    Vue.component('app-edit', {
        template: '#app-edit',
        props: {
            type: {
                type: String,
                default: '',
            },
            requestUrl: {
                type: String,
                default: 'plugin/diy/mall/template/edit'
            },
        },
        data() {
            var validate = (rule, value, callback) => {
                if (this.templateName) {
                    callback();
                } else {
                    callback(new Error(this.labelText['title'] + '不能为空'))
                }
            };
            return {
                hw_style: {},
                appNavBar: {
                    style: '1',
                    leftIcon: "<?php echo \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/" ?>" + 'statics/img/app/mall/head-nav-bar-mall.png',
                    link: {},
                    hasMallSetting: 0,
                    color: 'black',
                    backgroundColor: '#FFFFFF',
                    position: 'center',
                    placeholder: '搜索',
                    placeholderColor: '#666666',
                },
                loading: false,
                validate: validate,
                bgVisible: false,
                allComponents: [],
                id: null,
                templateName: '',
                components: [],
                submitLoading: false,
                model: '',
                bg: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                bgSetting: {
                    showImg: false,
                    backgroundColor: '#f5f7f9',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                    positionText: 'center center',
                    repeatText: 'no-repeat',
                },
                position: 'center center',
                repeat: 'no-repeat',
                overrun: null,
                has_home: 0,
                hasNavShow: false,
            };
        },
        created() {
            this.id = getQuery('id');
            this.model = getQuery('model');
            this.has_home = getQuery('has_home')
            this.loadData();

        },
        watch: {
            'appNavBar.leftIcon'(newValue, oldValue) {
                this.doSomething();
            }
        },
        computed: {
            maxWidth() {
                return () => {
                    let xstyle = parseInt(this.appNavBar.style);
                    let width = 0;

                    switch (xstyle) {
                        case 1:
                            if (this.appNavBar.position === 'center') {
                                width = 380;
                            } else {
                                width = 500;
                            }
                            break;
                        case 2:
                            width = 400;
                            break;
                        case 4:
                            width = 200;
                            break;
                        default:
                            break;
                    }
                    width = width / 2;
                    return Object.assign({}, {
                        'max-width': width + 'px',
                    });
                }
            },
            showLeftIcon() {
                return [2, 3].indexOf(parseInt(this.appNavBar.style)) !== -1
            },
            showPageTitle() {
                return [1, 2, 4].indexOf(parseInt(this.appNavBar.style)) !== -1
            },
            showLink() {
                return [3, 4].indexOf(parseInt(this.appNavBar.style)) !== -1
            },
            labelText() {
                if (this.has_home == 1) {
                    return {
                        'text': 'DIY首页',
                        'page_url': 'plugin/diy/mall/home/index',
                        'title': '首页标题',
                    }
                } else if (this.type === 'module') {
                    return {
                        'text': '自定义模块',
                        'page_url': 'plugin/diy/mall/module/index',
                        'title': '模块名称',
                    }
                } else {
                    return {
                        'text': '微页面',
                        'page_url': 'plugin/diy/mall/template/index',
                        'title': '微页面标题',
                    }
                }
            }
        }
        ,
        methods: {
            doSomething() {
                let url = this.appNavBar.leftIcon;
                const maxHeight = 54;
                const maxWidth = 100;
                let a = 2;
                var img = new Image();

                img.onload = (res) => {
                    let {height, width} = img;
                    let s = {};
                    if (height <= maxHeight && width <= maxWidth) {
                        s = {height: height / a + 'px', width: width / a + 'px'};
                    }
                    if (height <= maxHeight && width >= maxWidth) {
                        s = {height: height / (width / maxWidth) / a + 'px', width: maxWidth / a + 'px'}
                    }
                    if (height >= maxHeight && width <= maxWidth) {
                        s = {height: maxHeight / a + 'px', width: width / (height / maxHeight) / a + 'px'}
                    }

                    if (height > maxHeight && width >= maxWidth) {
                        if (maxWidth / maxHeight > width / height) {
                            s = {height: maxHeight / a + 'px', width: width / (height / maxHeight) / a + 'px'}
                        } else {
                            s = {width: maxWidth / a + 'px', height: height / (width / maxWidth) / a + 'px'}
                        }
                    }
                    this.hw_style = s;
                }
                img.src = url;
            },
            onSelectLink(e) {
                e.map((item, index) => {
                    this.appNavBar.link = {
                        url: item.new_link_url,
                        openType: item.open_type,
                        params: item.params,
                    }
                });
            },
            resetImg(type) {
                const host = "<?php echo \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . "/" ?>";
                if (type === 'left_icon') {
                    this.appNavBar.leftIcon = host + 'statics/img/app/mall/head-nav-bar-mall.png';
                }
            },
            mallLeftIconPic(e) {
                if (e.length) {
                    this.appNavBar.leftIcon = e[0].url;
                }
            },
            beSettingBg() {
                this.bg = JSON.parse(JSON.stringify(this.bgSetting));
                this.position = this.bgSetting.positionText;
                this.repeat = this.bgSetting.repeatText;
                this.bgVisible = false;
            },
            openBgSetting() {
                this.bgSetting = JSON.parse(JSON.stringify(this.bg));
                this.bgSetting.positionText = this.position;
                this.bgSetting.repeatText = this.repeat;
                this.bgVisible = true;
            },
            openPageSetting() {
                if (this.type === 'module') return
                this.hasNavShow = true;
                this.showComponentEdit('', '-1');
            },
            updateData(e) {
                this.bgSetting = e;
            },
            toggleData(e) {
                this.bgSetting.positionText = e;
            },
            changeData(e) {
                this.bgSetting.repeatText = e;
            },
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: this.requestUrl,
                        id: this.id,
                        is_home_page: this.has_home,
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.openPageSetting();
                        let {allComponents, overrun, name, data} = response.data.data
                        this.allComponents = allComponents;
                        this.overrun = overrun;
                        this.templateName = name;
                        const components = JSON.parse(data);

                        for (let i in components) {
                            components[i].active = false;
                            components[i].key = randomString();
                            if (components[i].id == 'background') {
                                this.bg = components[i].data;
                                this.bgSetting = this.bg;
                                this.position = this.bg.positionText;
                                this.repeat = this.bg.repeatText;
                            }
                            if (components[i].id === 'app-nav-bar') {
                                this.appNavBar = components[i].data;
                                this.doSomething();
                            }
                        }
                        this.components = components;
                    } else {
                    }
                }).catch(e => {
                });
            },
            selectComponent(e) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length - 1 >= this.overrun.diy_module_overrun) {
                    this.$message.error('最多添加' + this.overrun.diy_module_overrun + '个组件');
                    return;
                }
                if (e.single) {
                    for (let i in this.components) {
                        if (this.components[i].id === e.id) {
                            this.$message.error('该组件只允许添加一个。');
                            return;
                        }
                    }
                }
                this.hasNavShow = false;
                let currentIndex = this.components.length;
                for (let i in this.components) {
                    if (this.components[i].active) {
                        currentIndex = parseInt(i) + 1;
                        break;
                    }
                }
                const component = {
                    id: e.id,
                    data: null,
                    active: false,
                    key: randomString(),
                    permission_key: e.key ? e.key : ''
                };

                this.components.splice(currentIndex, 0, component);
                this.$nextTick(function () {
                    let document = this.$el.querySelector('#child').childNodes[currentIndex];
                    this.showComponentEdit(component, currentIndex);
                    this.$el.querySelector('#ggg').scrollTop = document.offsetTop - 200;
                });
            },
            showComponentEdit(component, index) {
                for (let i in this.components) {
                    if (i == index) {
                        this.components[i].active = true;
                    } else {
                        this.components[i].active = false;
                    }
                }
            },
            deleteComponent(index) {
                this.components.splice(index, 1);
            },
            copyComponent(index) {
                if (this.overrun && !this.overrun.is_diy_module_overrun
                    && this.components.length >= this.overrun.diy_module_overrun) {
                    this.$message({
                        message: '最多添加' + this.overrun.diy_module_overrun + '个组件',
                        type: 'error',
                        center: true
                    });
                    return;
                }
                for (let i in this.allComponents) {
                    for (let j in this.allComponents[i].list) {

                        if (this.allComponents[i].list[j].id === this.components[index].id) {
                            if (this.allComponents[i].list[j].single) {
                                this.$message({message: '该组件只允许添加一个。', type: 'error', center: true});
                                return;
                            }
                        }
                    }
                }
                let json = JSON.stringify(this.components[index]);
                let copy = JSON.parse(json);
                copy.active = false;
                copy.key = randomString();
                this.components.splice(index + 1, 0, copy);
            },
            moveUpComponent(index) {
                this.swapComponents(index, index - 1);
            },
            moveDownComponent(index) {
                this.swapComponents(index, index + 1);
            },
            swapComponents(index1, index2) {
                this.components[index2] = this.components.splice(index1, 1, this.components[index2])[0];
            },
            save(isSaveAs, saveAsName) {
                if (!this.templateName) {
                    this.$message({message: this.labelText['title'] + '不能为空', type: 'error', center: true});
                    return;
                }
                let hasBackGround = false;
                let hasAppNavBar = false;

                for (let i in this.components) {
                    if (this.components[i].id === 'background') {
                        hasBackGround = true;
                        this.components[i].data = this.bg;
                    }
                    if (this.components[i].id === 'app-nav-bar') {
                        hasAppNavBar = true;
                        this.components[i].data = this.appNavBar;
                    }
                }
                if (!hasBackGround) this.components.push({
                    id: 'background',
                    permission_key: '',
                    data: this.bg
                })
                if (!hasAppNavBar) this.components.push({
                    id: 'app-nav-bar',
                    permission_key: '',
                    data: this.appNavBar
                })
                const postComponents = [];
                for (let i in this.components) {
                    //需求要加的判断
                    if (this.components[i]['id'] === 'module' && this.components[i]['data']['list'].length > 1) {
                        let list = this.components[i]['data']['list'];
                        for (let j in list) {
                            if (!list[j].tabName) {
                                this.$message({message: '自定义模块名称不能为空', type: 'error', center: true});
                                return;
                            }
                        }
                    }
                    if (this.components[i]['id'] === 'nav') {
                        let bgType = this.components[i]['data']['bgType'];
                        let backgroundPicUrl = this.components[i]['data']['backgroundPicUrl'];
                        if (bgType === 'pic' && !backgroundPicUrl) {
                            this.$message({message: '导航图标背景图不能为空', type: 'warning', center: true});
                            return;
                        }
                    }
                    if (this.components[i]['id'] === 'customer') {
                        let {title,wechat,select_style,sub_title} = this.components[i]['data'];
                        if (!title) {
                            this.$message({message: '客服标题不能为空', type: 'warning', center: true});
                            return;
                        }
                        if(!wechat || !wechat.length){
                            this.$message({message: '客服微信不能为空', type: 'warning', center: true});
                            return;
                        }
                        if(select_style === '2' && !sub_title){
                            this.$message({message: '副标题不能为空', type: 'warning', center: true});
                            return;
                        }
                    }
                    if (this.components[i]['id'] === 'banner') {
                        let banner = this.components[i]['data']['banners'];
                        if (banner.length === 0) {
                            this.$message({message: '轮播图不能为空', type: 'warning', center: true});
                            return;
                        }
                        if (banner.length < 2) {
                            this.$message({message: '轮播图图片不能少于2张', type: 'warning', center: true});
                            return;
                        }
                        for (let i = 0; i < banner.length; i++) {
                            if (!banner[i].picUrl) {
                                this.$message({message: '轮播图图片不能为空', type: 'warning', center: true});
                                return;
                            }
                        }
                    }
                    postComponents.push({
                        id: this.components[i].id,
                        permission_key: this.components[i].permission_key,
                        data: this.components[i].data,
                    });
                }
                this.submitLoading = true;
                this.$request({
                    params: {
                        r: this.requestUrl,
                        id: isSaveAs ? null : this.id,
                    },
                    method: 'post',
                    data: {
                        name: isSaveAs ? saveAsName : this.templateName,
                        data: JSON.stringify(postComponents),
                        is_home_page: this.has_home,
                    },
                }).then(response => {
                    this.submitLoading = false;
                    if (response.data.code === 0) {
                        this.$message({message: response.data.msg, type: 'success', center: true});
                        this.id = response.data.data.id;
                        //this.loadData();
                        return;
                        if (this.type === `module`) {
                            this.$navigate({
                                r: 'plugin/diy/mall/module/index',
                            });
                        } else {
                            this.$navigate({
                                r: 'plugin/diy/mall/template/index',
                            });
                        }
                    } else {
                        this.$message({message: response.data.msg, type: 'error', center: true});
                    }
                }).catch(e => {
                });
            },
            saveAs() {
                this.$prompt('请输入' + this.labelText.title + ':', '另存为').then(({value}) => {
                    if (value) {
                        this.save(true, value);
                    }
                }).catch(() => {
                });
            },
        },
    });
</script>