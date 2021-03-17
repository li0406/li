//  FIXME: 修改菜单跳转方法和 icon
<template>
<!-- ISSUE: css提出 -->
<!-- ISSUE: menu绑定路由 -->
<el-menu :default-openeds="openeds" class="menu" :default-active="activeIndex">
    <el-submenu :index="item.menu_name" v-for="(item, index) in menu" :key="index">
        <template slot="title">
            <span :class="['iconfont',item.icon]"></span>
            <span style="font-weight:bold;font-size:14px;">{{item.menu_name}}</span>
        </template>
        <router-link :to="it.link" v-for="(it, i) in item.child" :key="i">
            <el-menu-item :index="it.link" style="padding-left: 48px;">{{it.menu_name}}</el-menu-item>
        </router-link>
    </el-submenu>
</el-menu>
</template>

<script>
import ApiPublic from '@/api/public';
import {
    initDynamicRoutes
} from '@/router/index';

export default {
    name: 'Menu',
    data() {
        return {
            activeIndex: '/home',
            menu: [],
            openeds: [],
        };
    },
    created() {
        this.activeIndex = this.$route.path;
        // 获取菜单
        this.getMenu();
    },
    methods: {
        getMenu() {
            const storageMenu = JSON.parse(localStorage.getItem('menuList'));
            if (storageMenu !== null && storageMenu.length > 0) {
                const menu = storageMenu;
                const openeds = menu.map((item) => item.menu_name);
                this.menu = menu;
                this.openeds = openeds;
                // 处理权限
                const tagsArr = menu.map((item) => item.child);
                let child = [];
                tagsArr.forEach((item) => {
                    child = [...child, ...item];
                });
                const objList = child.map((item) => ({
                    link: item.link.substr(1),
                    rights: item.rights,
                }));
                const noList = objList.filter((item) => item.link !== 'home');
                const childList = [...noList, {
                    link: 'NotFound',
                    rights: []
                }];
                initDynamicRoutes(childList);
            } else {
                ApiPublic.getMenu().then((res) => {
                    if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
                        const menu = res.data.data;
                        const openeds = menu.map((item) => item.menu_name);
                        this.menu = menu;
                        this.openeds = openeds;
                        localStorage.setItem('menuList', JSON.stringify(this.menu));
                        // 处理权限
                        const tagsArr = menu.map((item) => item.child);
                        let child = [];
                        tagsArr.forEach((item) => {
                            child = [...child, ...item];
                        });
                        const objList = child.map((item) => ({
                            link: item.link.substr(1),
                            rights: item.rights,
                        }));
                        const noList = objList.filter((item) => item.link !== 'home');
                        const childList = [...noList, {
                            link: 'NotFound',
                            rights: []
                        }];
                        localStorage.setItem('childList', JSON.stringify(childList));
                        initDynamicRoutes(childList);
                    } else {
                        this.$message({
                            message: res.data.error_msg,
                            type: 'error',
                        });
                    }
                });
            }
        },
    },

    watch: {
        $route(to) {
            this.activeIndex = to.path;
        },
    },
};
</script>

<style scoped>
@import '../assets/css/iconfont.css';

::-webkit-scrollbar {
    width: 0;
    height: 0;
    background: none;
}
</style><style lang="less">
.menu {
    height: 100%;
    overflow-x: hidden;

    .menu-item {
        padding: 0 20px;
    }

    .el-menu-item.is-active {
        color: #ff5353;
    }

    .el-submenu__title:hover {
        background-color: #f5f7fa;
    }

    .el-menu-item:focus {
        background-color: #f5f7fa;
    }

    .el-submenu:last-child {
        margin-bottom: 200px;
    }

    .iconfont {
        margin-right: 8px;
    }
}
</style>
