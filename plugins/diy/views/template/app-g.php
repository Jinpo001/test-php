<style>
    .app-g .goods-pic {
        width: 100%;
        height: 706px;
        background-color: #e2e2e2;
        background-position: center;
        background-size: cover;
        flex-shrink: 0;
        background-repeat: no-repeat;
    }

    .app-g .goods-tag {
        position: absolute;
        top: 0;
        left: 0;
        width: 64px;
        height: 64px;
        background-position: center;
        background-size: cover;
    }

    .app-g .goods-btn {
        border-color: #ff4544;
        color: #ff4544;
        padding: 0 20px;
        height: 48px;
        line-height: 50px;
        font-size: 24px;
    }

    .app-g .goods-btn.is-round {
        border-radius: 24px;
    }

    .app-g .goods-btn.el-button--primary {
        background-color: #ff4544;
        color: #fff;
    }

    .app-g .goods-name {
        color: #353535;
        width: 100%;
        margin-bottom: auto;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .app-g .goods-name[content]:before {
        content: attr(content);
        background: #ff45441a;
        border-radius: 28px;
        font-size: 22px;
        padding: 0 10px;
        display: inline-block;
        color: #ff4544;
        margin-top: 3px;
        margin-right: 10px;
    }

    .app-g .goods-price {
        color: #ff4544;
        line-height: 1;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .app-g .goods-underline-price {
        margin-left: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 22px;
        color: #999;
        text-decoration: line-through;
    }
</style>
<template id="app-g">
    <div :style="sPadding" class="app-g">
        <slot name="integral_mall"></slot>
        <div :style="goodsList" flex="dir:left">
            <div v-for="goods in list" :style="[goodsItem, cGoodsStyle]"
                 :flex="data.listStyle === -1 ? 'dir:left' : 'dir:top'">
                <div :style="goodsPic(goods.picUrl)"
                     class="goods-pic">
                    <slot name="picEnd"></slot>
                </div>
                <template v-if="data.showGoodsTag">
                    <div class="goods-tag"
                         :style="{backgroundImage: `url('${data.goodsTagPicUrl}')`}"
                    ></div>
                </template>
                <div :style="goodsContent"
                     :flex="data.textStyle === 2 && data.listStyle !== -1 ? 'dir:top cross:center': 'dir:top'">
                    <div v-if="data.showGoodsName" class="goods-name" :content="pluginTag(goods)">
                        {{goods.name}}
                    </div>
                    <div v-else-if="data.showGoodsName" class="goods-name">{{goods.name}}</div>
                    <div style="margin-top: auto;"
                         :style="{width: data.listStyle == 2 || data.listStyle == 1 ? '' : '100%'}">
                        <slot name="nameEnd" :goods="goods"></slot>
                        <div flex="box:last cross:bottom">
                            <div :flex="priceFlex" :style="{width: data.listStyle == 2 || data.listStyle == -1 || data.listStyle == 1 ? '' : '100%'}">
                                <div class="goods-price" v-text="priceText(goods)"></div>
                                <span v-if="data.isUnderLinePrice"
                                      class="goods-underline-price" v-text="originalText(goods)"></span>
                            </div>
                            <template v-if="data.showBuyBtn && data.textStyle !== 2 && data.listStyle !== 0 && data.listStyle !== 3">
                                <el-button :style="cButtonStyle" class="goods-btn" size="small">{{data.buyBtnText}}
                                </el-button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
<script>
    Vue.component('app-g', {
        template: '#app-g',
        props: {
            sign: String,
            data: Object,
            list: Array,
        },
        data() {
            return {};
        },
        computed: {
            priceFlex() {
                if (this.data.listStyle !== -1 ||
                    ['pick', 'integral_mall', 'step', 'flash-sale',
                    'bargain',
                    ].indexOf(this.sign) !== -1
                ) {
                    return 'dir:top main:center';
                } else {
                    return 'dir:left cross:center';
                }
            },
            calcStyle() {
                let {c_padding_lr, goodsStyle, listStyle} = this.data;

                let limit = 24;
                limit = (listStyle - 1) * limit;
                let padding = c_padding_lr !== undefined ? Number(c_padding_lr) * 2 : 0;
                let border = goodsStyle === 2 ? listStyle * 1 : 0;
                let width = 750 - padding - limit - border - 4;

                return width / listStyle + 'px';
            },

            /////////////////END///////////////
            goodsContent() {
                let {listStyle,textStyle} = this.data;
                let style = {
                    minHeight: '160px',
                    width: '100%',
                    color: '#ffffff',
                    textAlign: textStyle === 2 && listStyle !== -1 ? 'center' : 'left',
                    justifyContent: 'space-between',
                };
                switch (listStyle) {
                    case -1:
                        Object.assign(style, {
                            height: '200px',
                            padding: '15px 20px',
                            width: "calc(100% - 200px)",
                        })
                        break;
                    case 0:
                        Object.assign(style, {
                            padding: '12px',
                        })
                        break;
                    case 1:
                        Object.assign(style, {
                            padding: '24px',
                        })
                        break;
                    case 2:
                        Object.assign(style, {
                            padding: '24px',
                        })
                        break;
                    case 3:
                        Object.assign(style, {
                            padding: '12px',
                        })
                        break;
                }
                return style;
            },
            goodsPic() {
                return (picUrl) => {
                    let {fill, listStyle, c_border_top, c_border_bottom, goodsCoverProportion} = this.data;
                    let style = {};
                    switch (listStyle) {
                        case -1:
                            Object.assign(style, {
                                width: '200px',
                                height: '200px',
                                paddingRight: '20px',
                                borderRadius: `${c_border_top}px 0 0 ${c_border_bottom}px`
                            })
                            break;
                        case 0:
                            Object.assign(style, {
                                width: '100%',
                                height: '200px',
                                borderRadius: `${c_border_top}px ${c_border_top}px 0 0`
                            });
                            break;
                        case 1:
                            Object.assign(style, {
                                width: '100%',
                                height: goodsCoverProportion === '3-2' ? '471px' : '700px',
                                borderRadius: `${c_border_top}px ${c_border_top}px 0 0`
                            });
                            break;
                        case 2:
                            Object.assign(style, {
                                width: '100%',
                                height: '342px',
                                borderRadius: `${c_border_top}px ${c_border_top}px 0 0`
                            });
                            break;
                        case 3:
                            Object.assign(style, {
                                width: '100%',
                                height: '200px',
                                borderRadius: `${c_border_top}px ${c_border_top}px 0 0`
                            });
                            break;
                    }
                    return Object.assign(style, {
                        backgroundImage: `url(${picUrl})`,
                        backgroundSize: `${(fill === 1 ? 'cover' : 'contain')}`
                    })
                }
            },
            goodsItem() {
                let {listStyle, bg, c_border_top, c_border_bottom} = this.data;
                let style = {};
                switch (listStyle) {
                    case -1:
                        Object.assign(style, {
                            width: '100%',
                            height: '200px',
                            marginBottom: '24px',
                        })
                        break;
                    case 0:
                        Object.assign(style, {
                            width: '200px',
                            height: '100%',
                            marginLeft: '24px',
                        })
                        break;
                    case 1:
                        Object.assign(style, {
                            width: '100%',
                            height: '100%',
                            marginBottom: '24px',
                        })
                        break;
                    case 2:
                        Object.assign(style, {
                            width: this.calcStyle,
                            height: '100%',
                            marginBottom: '24px',
                            marginLeft: '24px',
                        })
                        break;
                    case 3:
                        Object.assign(style, {
                            width: this.calcStyle,
                            height: '100%',
                            marginBottom: '24px',
                            marginLeft: '24px',
                        })
                        break;
                }
                return Object.assign(style, {
                    position: 'relative',
                    background: `${bg}`,
                    borderTopLeftRadius: `${c_border_top}px`,
                    borderTopRightRadius: `${c_border_top}px`,
                    borderBottomLeftRadius: `${c_border_bottom}px`,
                    borderBottomRightRadius: `${c_border_bottom}px`,
                });
            },
            goodsList() {
                let {listStyle} = this.data;
                let style = {};
                switch (listStyle) {
                    case -1:
                        Object.assign(style, {
                            marginBottom: '-24px',
                            width: '100%',
                            flexWrap: 'wrap',
                        })
                        break;
                    case 0:
                        Object.assign(style, {
                            marginLeft: '-24px',
                            overflowX: 'auto',
                            flexWrap: 'nowrap',
                        })
                        break;
                    case 1:
                        Object.assign(style, {
                            marginBottom: '-24px',
                            width: '100%',
                            flexWrap: 'wrap',
                        })
                        break;
                    case 2:
                        Object.assign(style, {
                            marginLeft: '-24px',
                            marginBottom: '-24px',
                            flexWrap: 'wrap',
                        })
                        break;
                    case 3:
                        Object.assign(style, {
                            marginLeft: '-24px',
                            marginBottom: '-24px',
                            flexWrap: 'wrap',
                        })
                        break;
                }
                return Object.assign(style, {})
            },
            sPadding() {
                let {c_padding_bottom, c_padding_lr, c_padding_top, background, listStyle} = this.data;
                return {
                    overflowX: listStyle === 0 ? 'hidden' : 'visible',
                    padding: `${c_padding_top}px ${c_padding_lr}px ${c_padding_bottom ? c_padding_bottom : 0.1}px`,
                    background: `${background}`
                }
            },
            cButtonStyle() {
                let {buyBtnStyle, buttonColor} = this.data;
                let style = {
                    background: `${buttonColor}`,
                    borderColor: `${buttonColor}`,
                    height: '48px',
                    color: '#FFFFFF',
                    lineHeight: '50px',
                    padding: '0 20px',
                };
                if (buyBtnStyle === 3 || buyBtnStyle === 4) {
                    Object.assign(style, {borderRadius: '24px'});
                }
                if (buyBtnStyle === 2 || buyBtnStyle === 4) {
                    Object.assign(style, {background: 'white', color: `${buttonColor}`});
                }
                return style;
            },
            cGoodsStyle() {
                let {goodsStyle} = this.data;
                if (goodsStyle === 1) {
                    return {
                        background: 'white',
                    }
                } else if (goodsStyle === 2) {
                    return {
                        background: 'white',
                        borderStyle: 'solid',
                        borderWidth: '1px',
                        borderColor: '#e2e2e2',
                    }
                } else {
                    return {}
                }
            },
        },
        methods: {
            originalText(goods) {
                // if([-1,1,2].indexOf(this.data.listStyle) === -1){
                //     return '';
                // }
                if ([
                    'integral_mall', 'lottery', 'advance',
                    'miaosha', 'flash-sale', 'wholesale',
                    'step','bargain', 'pick'
                ].indexOf(this.sign) !== -1) {
                    return '￥' + goods.originalPrice
                } else {
                    return '￥' + goods.original_price
                }
            },
            pluginTag(goods) {
                switch (this.sign) {
                    case 'pintuan':
                        return goods.peopleNum + '人团';
                    case 'miaosha':
                        return '秒杀';
                    case 'pick':
                        return 'N元任选';
                    case 'advance':
                        return '预售';
                    case 'bargain':
                        return '砍价';
                    case 'gift':
                        return '社交送礼';
                    case 'flash-sale':
                        return '限时抢购';
                    case 'exchange':
                        return '礼品卡';
                    case 'wholesale':
                        return '商品批发';
                    case 'step':
                        return '步数宝';
                }
            },
            priceText(goods) {
                switch (this.sign) {
                    case 'integral_mall':
                        return goods.integral + '积分+￥' + goods.price;
                    case 'step':
                        return goods.currency + '活力币+￥' + goods.price;
                    case 'pintuan':
                        return '￥' + goods.pintuanPrice;
                    case 'lottery':
                        return '免费';
                    default:
                        return goods.price > 0 ? '￥' + goods.price : '免费';
                }
            },
        },
    });
</script>
