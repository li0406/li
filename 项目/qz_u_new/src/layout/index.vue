<template>
<!-- TODO: 添加tagsview -->
<div class="major">
    <div class="header">
        <div class="logo">
            <img src="@/assets/logo.png" />
            <span class="extra">商家版</span>
        </div>
        <div class="right-content">
            <div class="flex">
                <div class="qc">{{header.qc}}</div>
                <div class="viewstore point" @click="tostore()">查看店铺</div>
            </div>
            <div class="message">
                <div @click="handleGotoPage">
                    <el-badge :value="count" v-if="count !==0">
                        <img src="@/assets/message.png" />
                    </el-badge>
                    <img src="@/assets/message.png" v-else />
                </div>
                <el-avatar size="small" :src="header.logo" class="avatar"></el-avatar>
                <el-dropdown @command="handleDropdownCommand">
                    <span class="el-dropdown-link">
                        <span class="user">{{header.user}}</span>
                        <i class="el-icon-arrow-down el-icon--right"></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item command="修改密码" v-if="tag == 'company'">修改密码</el-dropdown-item>
                        <el-dropdown-item command="退出登录">退出登录</el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
        </div>
    </div>
    <div class="main">
        <div class="sidebar" :class="{ 'is-hide': hide }">
            <div class="menu">
                <Menu />
                <div class="sidebar-hide" :class="{ 'is-hide': hide }" @click="handleSidebarOnClick"></div>
            </div>
        </div>
        <div :style="{width:hide?'100%':'calc(100% - 179px)'}">
            <tags-view></tags-view>
            <div class="content">
                <transition name="slide-fade">
                    <router-view />
                </transition>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import ApiHome from '@/api/home';
import ApiPublic from '@/api/public';
import dayjs from 'dayjs';
import Menu from './menu.vue';
import TagsView from './tags-view.vue';

export default {
    components: {
        TagsView,
        Menu,
    },
    async created() {
        // 获取头部导航信息
        const res3 = await ApiPublic.getBasicinfo();
        this.bm = res3.data.data.bm
        this.user_id = res3.data.data.user_id
        this.header.qc = res3.data.data.qc;
        this.header.logo = res3.data.data.logo;
        this.header.user = res3.data.data.tag === 'company' ? res3.data.data.jc : res3.data.data.nick_name;
        localStorage.setItem('companyName', JSON.stringify(res3.data.data.qc));
        localStorage.setItem('tagName', JSON.stringify(res3.data.data.tag));
        // 角色字段判断
        this.tag = res3.data.data.tag;
        await this.handleFetchCompanyNoticeCount();
        if (res3.data.data.tag === 'company') {
            this.timer = window.setInterval(async () => {
                const res = await ApiHome.newnums(); // 请求消息
                if (res.data.data.number !== 0) {
                    const timestamp = dayjs().valueOf();
                    this.notify[timestamp] = this.$notify({
                        title: '新订单',
                        message: `您有${res.data.data.number}条新订单`,
                        duration: 0,
                        onClick: () => {
                            // this.$router.push('/order-all');
                            const menuList = this.$locGet('menuList')
                            menuList.forEach((item) => {
                                item.child.forEach((child) => {
                                    if (child.menu_name === '全部订单') {
                                        this.$router.push({
                                            path: child.link,
                                        });
                                    }
                                })
                            })
                            this.notify[timestamp].close();
                        },
                    });
                }
            }, 30 * 1000);
        }
    },
    destroyed() {
        window.clearInterval(this.timer);
    },
    name: 'Layout',
    data() {
        return {
            bm: '',
            user_id: '',
            count: 0,
            hide: false,
            drawer: false,
            item: 'el-icon-star-off',
            notify: {},
            header: {
                qc: '', // 企业全称
                user: '', // 登录用户名
                logo: '', // 图标
            },
            tag: '',
        };
    },
    methods: {
        tostore() {
            window.open(`http://${this.bm}.qizuang.com/company_home/${this.user_id}/`)
        },
        handleSidebarOnClick() {
            this.hide = !this.hide;
        },

        async handleFetchCompanyNoticeCount() {
            const res = await ApiPublic.companyNoticeCount();
            this.count = res.data.data.count;
        },

        handleGotoPage() {
            this.count = 0;
            this.$router.push('/message-center');
        },

        handleDropdownCommand(command) {
            if (command === '修改密码') {
                this.$router.push('Alter');
            }
            if (command === '退出登录') {
                this.logout();
            }
        },
        logout(){
            const params={}
            ApiHome.logout(params).then((res)=>{
                if (res.data.error_code === 0) {
                    this.$router.push({
                        path:'/login'
                    })
                    window.setTimeout(()=>{
                        window.location.reload()
                    },1000)
                } else {
                    this.$message.error('退出失败');
                }
            }).catch((err)=>{
                this.$message.error(err);
            })
        }
    },
};
</script>

<style lang="less">
.el-notification.right {
    top: 54px !important;
}
</style><style lang="less" scoped>
.slide-fade-enter-active {
    transition: all 0.5s ease;
}

.slide-fade-enter {
    transform: translateY(30px);
    opacity: 0;
}

.major {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: calc(100%);

    .header {
        position: relative;
        z-index: 1000;
        display: flex;
        height: 60px;
        background-color: #e94747;
    }

    .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 180px;
        height: 100%;
        border-right: 1px solid #e3e3e3;

        .extra {
            margin-left: 8px;
            color: #fff;
            font-size: 8px;
            background-color: #d44242;
        }
    }

    .right-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 88%;

        .qc {
            margin-left: 30px;
            color: rgba(255, 255, 255, 1);
            font-weight: bold;
            font-size: 16px;
        }

        .message {
            display: flex;
            align-items: center;
            height: 60px;

            .avatar {
                margin-right: 10px;
                margin-left: 50px;
            }

            .user {
                color: rgba(255, 232, 232, 1);
                font-size: 14px;
            }

            i {
                color: white;
            }
        }
    }

    .main {
        position: relative;
        display: flex;
        flex-direction: row;
        box-sizing: border-box;
        height: 100%;
        background-color: #f2f2f2;
    }

    .sidebar {
        position: relative;
        box-sizing: border-box;
        width: 180px;
        height: 100%;
        overflow: visible;
        background-color: #fff;
        border-right: 1px solid #e3e3e3;
        // transition: all 0.12s ease;
        user-select: none;
    }

    .sidebar.is-hide {
        width: 0;
        transition: all 0.12s ease;
    }

    .menu {
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        background-color: #fff;
    }

    .sidebar-hide {
        position: absolute;
        top: 50%;
        right: 0;
        z-index: 1001;
        width: 17px;
        height: 61px;
        background-image: url('../assets/holder.jpg');
        transform: translate3d(100%, -50%, 0);
        cursor: pointer;
    }

    .sidebar-hide.is-hide {
        background-position: 0 -61px;
    }

    .content {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 1020px;
        height: 100%;
        padding: 10px;
        overflow: scroll;
    }

    .viewstore {
        width: 60px;
        height: 20px;
        margin: 0 0 0 10px;
        color: #e94747;
        font-size: 12px;
        line-height: 20px;
        text-align: center;
        background-color: #fff;
        border-radius: 6px;
    }

    .point {
        cursor: pointer;
    }

    .flex {
        display: flex;
        align-items: center;
    }
}
</style>
