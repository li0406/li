<template>
<div class="tags-view" ref="closeBtn">
    <div class="left-button">
        <i class="el-icon-caret-left icon"></i>
    </div>
    <div class="right-button" v-if="tags.length!==1" @click="close(currentTag)">
        <i class="el-icon-circle-close icon"></i>
    </div>
    <div class="right-button">
        <i class="el-icon-caret-right icon"></i>
    </div>
    <div class="tag" v-for="item in tags" :key="item.path">
        <span style="margin-right: 10px;" @click="jump(item.path)" :class="{ active: item.name === currentTag }">{{item.title}}</span>
        <i class="el-icon-circle-close icon" @click="close(item.name)" v-if="tags.length!==1"></i>
    </div>
    <context-menu class="right-menu" :target="contextMenuTarget" :show="contextMenuVisible" @update:show="(show) => contextMenuVisible = show" v-if="tags.length!==1">
        <a href="javascript:;" @click="handleBtnAllClose">关闭其他</a>
    </context-menu>
</div>
</template>

<script>
import qs from 'qs';
import {
    component as ContextMenu
} from '@xunlei/vue-context-menu';

export default {
    name: 'TagsView',
    components: {
        ContextMenu,
    },
    created() {
        this.currentTag = this.$route.name;
        const tags = localStorage.getItem('tags');
        if (qs.parse(tags).tags) {
            this.tags = qs.parse(tags).tags;
        } else {
            this.tags = [];
            const tagArr = this.defaultTags.filter((value) => value.name === this.$route.name);
            if (tagArr.length === 1) {
                this.tags.push(tagArr[0]);
            }
        }
    },
    mounted() {
        this.contextMenuTarget = this.$refs.closeBtn;
    },
    methods: {
        jump(path) {
            this.$router.push(path);
        },
        close(name) {
            // FIXME: 删除tag
            // FIXME: 删除全部tag,删除当前tag跳转到首页
            const tags = this.tags;
            this.tags = tags.filter((value) => value.name !== name);
            if (name === this.currentTag) {
                this.$router.push(this.tags[0].path);
            }
            localStorage.setItem('tags', qs.stringify({
                tags: this.tags
            }));
        },
        handleBtnAllClose() {
            const tags = this.tags;
            this.tags = tags.filter((value) => value.name === this.currentTag);
            localStorage.setItem('tags', qs.stringify({
                tags: this.tags
            }));
            this.contextMenuVisible = false;
        },
    },
    watch: {
        $route(to) {
            const tagArr = this.defaultTags.filter((value) => value.name === to.name);
            if (tagArr.length === 1) {
                const currentTagArr = this.tags.filter((value) => value.name === to.name);
                if (currentTagArr.length !== 1) {
                    this.tags.push(tagArr[0]);
                }
            }
            localStorage.setItem('tags', qs.stringify({
                tags: this.tags
            }));

            this.currentTag = to.name;
        },
    },
    data() {
        return {
            contextMenuTarget: document.body,
            contextMenuVisible: false,
            // FIXME: 暂时设定tag显示默认列表
            defaultTags: [{
                    title: '首页',
                    name: 'Home',
                    path: '/home'
                },
                {
                    title: '全部订单',
                    name: 'OrderAll',
                    path: '/order-all'
                },
                {
                    title: '全部订单',
                    name: 'orderListv1.0',
                    path: '/order-listv1.0'
                },
                {
                    title: '申请补轮',
                    name: 'OrderRound',
                    path: '/order-round'
                },
                {
                    title: '微信接收订单',
                    name: 'OrderWechat',
                    path: '/order-wechat'
                },
                {
                    title: '装修现场',
                    name: 'OrderSite',
                    path: '/order-site'
                },
                {
                    title: '抢单池',
                    name: 'OrderPond',
                    path: '/order-pond'
                },
                {
                    title: '装修案例',
                    name: 'DecorationCase',
                    path: '/decoration-case/case'
                },
                {
                    title: '基本信息管理',
                    name: 'BaseInfo',
                    path: '/base-info'
                },
                {
                    title: '员工管理',
                    name: 'WorkersList',
                    path: '/workers-list'
                },
                {
                    title: '资金明细',
                    name: 'Finance',
                    path: '/finance'
                },
                {
                    title: '消息中心',
                    name: 'MessageCenter',
                    path: '/message-center'
                },
                {
                    title: '账户中心',
                    name: 'Alter',
                    path: '/alter'
                },
                {
                    title: '个人中心',
                    name: 'Personal',
                    path: '/personal'
                },
                {
                    title: '优惠活动',
                    name: 'Promotions',
                    path: '/promotions'
                },
                {
                    title: '评价管理',
                    name: 'Notes',
                    path: '/notes'
                },
                {
                    title: '店铺轮播图',
                    name: 'StoreBanner',
                    path: '/store-banner'
                },
                {
                    title: '微信绑定',
                    name: 'wechatBinding',
                    path: '/wechat-binding'
                },
                {
                    title: '订单设置',
                    name: 'setMenu',
                    path: '/set-menu'
                },
                {
                  title: '选品商城',
                    name: 'selectionMall',
                    path: '/selection-mall'
                }
            ],
            currentTag: 'Home',
            tags: [],
        };
    },
};
</script>

<style lang="less">
.tags-view {
    .active {
        color: #ff5353;
    }

    height: 42px;
    background-color: #f9f9f9;

    .left-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 42px;
        margin-right: 2px;
        background-color: white;
    }

    .left-button:hover {
        cursor: pointer;
    }

    .right-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        float: right;
        width: 20px;
        height: 42px;
        background-color: white;
    }

    .right-button:hover {
        cursor: pointer;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        height: 40px;
        margin-right: 2px;
        padding: 0 8px;
        font-size: 14px;
        background-color: white;
    }

    .tag:hover {
        cursor: pointer;
    }

    .icon:hover {
        color: #ff5353;
    }

    .right-menu {
        position: fixed;
        z-index: 999;
        display: none;
        background: #fff;
        border: solid 1px rgba(0, 0, 0, 0.2);
        border: 1px solid #eee;
        border-radius: 3px;
        border-radius: 1px;
        box-shadow: 0 0.5em 1em 0 rgba(0, 0, 0, 0.1);
    }

    .right-menu a {
        display: block;
        width: 75px;
        height: 28px;
        padding: 2px;
        color: #1a1a1a;
        line-height: 28px;
        text-align: center;
    }

    .right-menu a:hover {
        color: #fff;
        background: #ff5353;
    }
}
</style>
