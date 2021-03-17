// 客户跟进
<template>
<div class="customer">
    <el-row>
        <el-col :span="2">
            <div class="item remark" @click="handleDivItemClick({is_read:2})">
                <div>
                    <el-badge :value="num" v-if="num!==0">
                        <div class="img-remark"></div>
                    </el-badge>
                    <div class="img-remark" v-else></div>
                </div>
                <span>待标记</span>
            </div>
        </el-col>
        <el-col :span="2">
            <div class="item measure" @click="handleDivItemClick({state:1})">
                <div>
                    <div class="img-measure"></div>
                </div>
                <span>已量房</span>
            </div>
        </el-col>
        <el-col :span="2">
            <div class="item arrive" @click="handleDivItemClick({state:2})">
                <div>
                    <div class="img-arrive"></div>
                </div>
                <span>已到店/见面</span>
            </div>
        </el-col>
        <el-col :span="2">
            <div class="item not" @click="handleDivItemClick({state:3})">
                <div>
                    <div class="img-not"></div>
                </div>
                <span>未量房</span>
            </div>
        </el-col>
        <el-col :span="2">
            <div class="item contract" @click="handleDivItemClick(({state:4}))">
                <div>
                    <div class="img-contract"></div>
                </div>
                <span>已签约</span>
            </div>
        </el-col>
    </el-row>
</div>
</template>

<script>
import ApiHome from '@/api/home';

export default {
    name: 'Customer',
    async created() {
        const res = await ApiHome.home();
        if (res.data.error_code === 0) {
            this.num = res.data.data.client.no_mark_num;
        }
    },
    beforeDestoryed() {
        window.clearInterval(this.timer);
    },
    methods: {
        handleDivItemClick(query) {
            const menuList = this.$locGet('menuList')
            menuList.forEach((item) => {
                item.child.forEach((child) => {
                    if (child.menu_name === '全部订单') {
                        this.$router.push({
                            path: child.link,
                            query
                        });
                    }
                })
            })
        },
    },
    data() {
        return {
            num: 0,
        };
    },
};
</script>

<style lang="less" scoped>
.customer {
    .item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        height: 62px;

        img {
            display: block;
            width: 28px;
            height: 28px;
        }

        .img-remark {
            display: block;
            width: 28px;
            height: 28px;
            background: url('../../assets/customer/remark.png') no-repeat center;
            background-size: 100% 100%;
        }

        .img-measure {
            display: block;
            width: 28px;
            height: 28px;
            background: url('../../assets/customer/measure.png') no-repeat center;
            background-size: 100% 100%;
        }

        .img-arrive {
            display: block;
            width: 28px;
            height: 28px;
            background: url('../../assets/customer/arrive.png') no-repeat center;
            background-size: 100% 100%;
        }

        .img-not {
            display: block;
            width: 28px;
            height: 28px;
            background: url('../../assets/customer/not-measure.png') no-repeat center;
            background-size: 100% 100%;
        }

        .img-contract {
            display: block;
            width: 28px;
            height: 28px;
            background: url('../../assets/customer/contract.png') no-repeat center;
            background-size: 100% 100%;
        }
    }

    .item:hover {
        cursor: pointer;
    }

    .remark:hover .img-remark {
        background: url('../../assets/customer/remark-hover.png') no-repeat center;
    }

    .remark:hover span {
        color: #ff5353;
    }

    .measure:hover .img-measure {
        background: url('../../assets/customer/measure-hover.png') no-repeat center;
    }

    .measure:hover span {
        color: #ff5353;
    }

    .arrive:hover .img-arrive {
        background: url('../../assets/customer/arrive-hover.png') no-repeat center;
    }

    .arrive:hover span {
        color: #ff5353;
    }

    .not:hover .img-not {
        background: url('../../assets/customer/not-measure-hover.png') no-repeat center;
    }

    .not:hover span {
        color: #ff5353;
    }

    .contract:hover .img-contract {
        background: url('../../assets/customer/contract-hover.png') no-repeat center;
    }

    .contract:hover span {
        color: #ff5353;
    }
}
</style>
