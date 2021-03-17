<template>
    <div class="see-contains">
        <div class="head">
            <img :src="imgUrl+ 'custom/20200824/FjZoNpxBMxsdQK4b6f2DmmaUBqsQ'" alt="">
        </div>
        <div class="step">
            <div class="titles">7步了解流程 <span>仅需1min&nbsp;&nbsp;秒懂装修</span><span class="icon"></span></div>
            <div class="content">
                <div class="tips nav">
                    <mt-button class="item" @click.native.prevent="active = 'tab1'" :class="{active:active == 'tab1'}">
                        01<br>预约
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab2'" :class="{active:active == 'tab2'}">
                        02<br>量房
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab3'" :class="{active:active == 'tab3'}">
                        03<br>对比
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab4'" :class="{active:active == 'tab4'}">
                        04<br>签约
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab5'" :class="{active:active == 'tab5'}">
                        05<br>硬装
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab6'" :class="{active:active == 'tab6'}">
                        06<br>软装
                    </mt-button>
                    <mt-button class="item" @click.native.prevent="active = 'tab7'" :class="{active:active == 'tab7'}">
                        07<br>验收
                    </mt-button>
                </div>
                <div class="tip-pic">
                    <mt-tab-container class="page-tabbar-tab-container" v-model="active" swipeable>
                        <mt-tab-container-item id="tab1">
                            <img :src="imgUrl+ 'custom/20200826/Fmp25tlPQxI_fiGCbSmEqpDChBU0'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab2">
                            <img :src="imgUrl+ 'custom/20200826/FkqoPW5c8XRUHcqCmKO3xI6k8Rla'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab3">
                            <img :src="imgUrl+ 'custom/20200826/Fr5ZZz-n1H4RKi2ZYYc67rm4eB4O'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab4">
                            <img :src="imgUrl+ 'custom/20200826/Fg4SXk0s7uHEhkZG19E2dKVJY1qQ'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab5">
                            <img :src="imgUrl+ 'custom/20200826/FnkfhwuztCSfKSJjiMqHBANUktsO'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab6">
                            <img :src="imgUrl+ 'custom/20200826/FiP62SlQdLiIIM-kOCmoKGwadOFF'" alt="">
                        </mt-tab-container-item>
                        <mt-tab-container-item id="tab7">
                            <img :src="imgUrl+ 'custom/20200826/FjwgzifOe2O2ob6-XR7FUFC2nDKb'" alt="">
                        </mt-tab-container-item>
                    </mt-tab-container>
                </div>
            </div>
        </div>
        <div class="step video">
            <div class="titles">视频划重点 <span>3分钟get知识点</span><span class="icon"></span>
                <div class="more" @click="bindVideo"><span>更多&nbsp;&nbsp; </span><img src="../../assets/img/qzw/right.png" alt=""></div>
            </div>
            <div class="content">
                <div class="video-box">
                    <div class="item" v-for="item in videoList" @click="goVideo(item.vedio_id)">
                        <img :src="item.img_url" class="img" alt="">
                        <img class="icon" src="../../assets/img/share/icon-play.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="step company">
            <div class="titles">本地装企推荐 <span>公司选得好&nbsp;&nbsp;装修没烦恼</span><span class="icon"></span>
                <div class="more" @click="bindCompany"><span>更多&nbsp;&nbsp; </span> <img src="../../assets/img/qzw/right.png" alt=""></div>
            </div>
            <div class="content">
                <ul>
                    <li v-for="item in companyList" @click="goCompany(item.id)">
                        <div class="head">
                            <div class="left">
                                <img :src="item.logo" alt="">
                            </div>
                            <div class="right">
                                <div class="detail">
                                    <div class="name">{{item.name.substring(0, 10)}}</div>
                                    <div class="position">
                                        <img src="../../assets/img/qzw/dingwei.png" alt="">
                                        <div>{{item.area_name}}</div>
                                    </div>
                                </div>
                                <div class="stars">
                                    <div class="star-img">
                                        <img src="../../assets/img/qzw/star.png" alt=""
                                             v-for="star in Math.round(item.starA)">
                                        <img src="../../assets/img/qzw/starone.png" alt=""
                                             v-for="star in Math.round(item.starB)">
                                    </div>
                                    <div class="title" v-if="item.comment_count > 3"><span>{{item.comment_count}}</span>位业主点评</div>
                                </div>
                            </div>
                        </div>
                        <div class="rich">
                            <div class="nums" v-if="item.case_count || item.team_count || item.building_count">
                                <div v-if="item.case_count > 0">案例：<span class="item-nums">{{item.case_count}}</span></div>
                                <div v-if="item.team_count > 0">设计师：<span class="item-nums">{{item.team_count}}</span></div>
                                <div v-if="item.building_count > 0">在建工地：<span class="item-nums">{{item.building_count}}</span></div>
                            </div>
                            <div class="stips" v-if="item.tags.length">
                                <div class="tip-item" v-for="tag in item.tags">{{tag.name}}</div>
                            </div>
                        </div>
                        <div class="mode">
                            <div class="mode-item" v-for="it in item.cases_show">
                                <img :src="it.img_url" alt="">
                                <div class="xq">{{it.title.substring(0, 12)}}</div>
                                <div class="ms">{{it.case_attr}}</div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import qzConfig from '../../utils/config'
    import {xiaobaiCompany, xiaobaiVideo} from '@/api/share'
    import {mapLocation} from '@/api/api'
    
    export default {
        name: 'See',
        data() {
            return {
                imgUrl: qzConfig.oss_qiniu_host,
                active: 'tab1',
                videoList: [],
                companyList: [],
                city_id: '',
                city_name: ''
            }
        },
        created() {
            this.xiaobaiVideo()
            this.xiaobaiCompany()
        },
        methods: {
            xiaobaiVideo() {
                xiaobaiVideo().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.videoList = res.data.data
                    }
                })
            },
            xiaobaiCompany() {
                mapLocation().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        const address = res.data.data
                        const city = address.city_id
                        const city_name = address.city_name
                        this.city_id = city
                        this.city_name = city_name
                        xiaobaiCompany({
                            city: city
                        }).then(res => {
                            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                                const companyList = res.data.data;
                                companyList.forEach((item)=>{
                                    if(parseInt((item.score)/0.5) == 10){
                                        item.starA = 5
                                        item.starB = 0
                                    } else if (parseInt((item.score)/0.5)== 9){
                                        item.starA = 4
                                        item.starB = 1
                                    } else if (parseInt((item.score)/0.5) == 8){
                                        item.starA = 4
                                        item.starB = 0
                                    } else {
                                        item.starA = 3
                                        item.starB = 1
                                    }
                                })
                                this.companyList = companyList
                            }
                        })
                    }
                })
            },
            bindVideo() {
                this.$bridge.callNative('Native_video_more', null, function (res) {})
            },
            bindCompany(){
                this.$bridge.callNative('Native_decorate_more', null, function (res) {})
            },
            goVideo(id) {
                this.$bridge.callNative('Native_video_detail', id, function (res) {})
            },
            goCompany(id) {
                this.$bridge.callNative('Native_decorate_detail', id, function (res) {})
            },
            bindKf() {
                window.location.href = 'https://tb.53kf.com/code/app/10032978/1?device=ios';
            },
            bindSheji() {
                this.$router.push({path:'/sheji'})
            }
        },
    }
</script>

<style lang="scss" scoped>
    ::-webkit-scrollbar {
        display: none; /* Chrome Safari */
    }

    .mint-button--normal {
        padding: 0;
        -webkit-box-shadow: none;
    }

    .see-contains {
        background: #fff;
        .head {
            overflow: hidden;
            img {
                width: 100%;
                display: block;
            }
        }
        .step {
            width: 100%;
            margin-top: 0.17rem;
            padding: 0 0.12rem;
            padding-bottom: 0.08rem;
            box-sizing: border-box;
            overflow: hidden;
            .titles {
                height: 0.18rem;
                font-size: 0.17rem;
                font-weight: bold;
                color: rgba(51, 51, 51, 1);
                line-height: 0.18rem;
                position: relative;
                z-index: 0;
                span {
                    font-size: 0.10rem;
                    font-weight: 500;
                    color: rgba(153, 153, 153, 1);
                }
                .icon {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    z-index: -1;
                    display: block;
                    width: 0.29rem;
                    height: 0.08rem;
                    background: #FFC100;
                    border-top-right-radius: 0.04rem;
                }
                .more {
                    float: right;
                    font-size: 0.10rem;
                    font-weight: 500;
                    color: rgba(153, 153, 153, 1);
                    display: flex;
                    align-items: center;
                    span{
                        display: inline-block;
                        width: 0.40rem;
                        text-align: right;
                    }
                    img {
                        display: inline-block;
                        width: 0.05rem;
                        height: 0.10rem;
                    }
                }
            }
            .content {
                .tips {
                    height: 0.72rem;
                    width: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    .item {
                        display: flex;
                        text-align: center;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        width: 0.33rem;
                        height: 0.38rem;
                        font-size: 0.11rem;
                        font-weight: 500;
                        color: rgba(153, 153, 153, 1);
                        background: rgba(245, 245, 245, 1);
                        border-radius: 2px;
                    }
                    .active {
                        background: #FF5353;
                        color: #FFFFFF;
                    }
                }
                .tip-pic {
                    img {
                        width: 100%;
                    }
                }
            }
        }
        .video {
            padding-right: 0;
            .titles {
                padding-right: 0.12rem;
            }
            .content {
                .video-box {
                    margin-top: 0.17rem;
                    white-space: nowrap;
                    overflow-y: hidden;
                    overflow-x: auto;
                    .item {
                        width: 2.47rem;
                        height: 1.42rem;
                        margin-right: 0.12rem;
                        display: inline-block;
                        position: relative;
                        border-radius: 3px;
                        overflow: hidden;
                        .img {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                        }
                        .icon {
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            margin: auto;
                            width: 0.32rem;
                            height: 0.32rem;
                            z-index: 2;
                        }
                    }
                    .item:first-child {
                        /*margin-left: 0;*/
                    }
                }
            }
        }
        .company {
            margin-bottom: 0.60rem;
            ul {
                margin-top: 0.17rem;
                overflow: hidden;
                li {
                    width: 98%;
                    margin: 4px auto;
                    padding: 0.08rem;
                    padding-bottom: 0;
                    box-sizing: border-box;
                    overflow: hidden;
                    background: rgba(255, 255, 255, 1);
                    box-shadow: 0px 1px 0.08rem 0px rgba(0, 0, 0, 0.07);
                    border-radius: 8px;
                    margin-bottom: 0.13rem;
                    .head {
                        display: flex;
                        align-items: center;
                        .left {
                            width: 0.64rem;
                            height: 0.47rem;
                            background: rgba(255, 255, 255, 1);
                            border: 1px solid rgba(229, 229, 229, 0.6);
                            border-radius: 2px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            img {
                                width: 0.58rem;
                                height: 0.29rem;
                                border-radius: 2px;
                            }
                        }
                        .right {
                            .detail {
                                height: 0.24rem;
                                width: 2.10rem;
                                padding-left: 0.08rem;
                                box-sizing: border-box;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                font-size: 0.13rem;
                                font-weight: 500;
                                color: rgba(51, 51, 51, 1);
                                .position {
                                    display: flex;
                                    align-items: center;
                                    font-size: 0.10rem;
                                    font-weight: 500;
                                    color: rgba(153, 153, 153, 1);
                                    img {
                                        display: block;
                                        width: 0.16rem;
                                        height: 0.16rem;
                                    }
                                }
                            }
                            .stars {
                                height: 0.23rem;
                                padding-left: 0.08rem;
                                box-sizing: border-box;
                                font-size: 0.10rem;
                                font-weight: 500;
                                color: rgba(153, 153, 153, 1);
                                display: flex;
                                align-items: center;
                            }
                            .star-img {
                                display: flex;
                                align-items: center;
                                margin-right: 0.05rem;
                                img {
                                    width: 0.09rem;
                                    margin-left: 2px;
                                }
                            }
                        }
                    }
                    .rich {
                        .nums {
                            width: 100%;
                            height: 0.30rem;
                            display: flex;
                            align-items: center;
                            font-size: 0.10rem;
                            color: #333333;
                            div {
                                height: 0.12rem;
                                border-right: 1px solid #333333;
                                margin-right: 0.04rem;
                                line-height: 0.12rem;
                                .item-nums {
                                    color: #999999;
                                    margin-right: 0.04rem;
                                    line-height: 0.125rem;
                                }
                            }
                            div:last-child {
                                border: none;
                            }

                        }
                        .stips {
                            width: 100%;
                            min-height: 0.30rem;
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            .tip-item {
                                display: flex;
                                padding: 0 0.05rem;
                                margin-bottom: 0.06rem;
                                height: 0.16rem;
                                background: rgba(255, 241, 228, 1);
                                border-radius: 2px;
                                align-items: center;
                                margin-right: 0.06rem;
                                font-size: 0.077rem;
                                font-weight: 500;
                                color: rgba(255, 127, 0, 1);
                            }
                            .tip-item:last-child {
                                margin-right: 0;
                            }
                        }
                    }
                    .mode {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        margin-top: 0.06rem;
                        .mode-item {
                            margin-bottom: 0.10rem;
                            img {
                                display: block;
                                width: 1.34rem;
                                height: 1.00rem;
                                border-radius: 2px;
                            }
                            .xq {
                                font-size: 0.11rem;
                                font-weight: bold;
                                color: rgba(51, 51, 51, 1);
                                line-height: 0.27rem;
                            }
                            .ms {
                                font-size: 0.10rem;
                                font-weight: 500;
                                color: rgba(153, 153, 153, 1);
                            }
                        }
                    }
                }
                li:last-child {
                    margin-bottom: 4px;
                }
            }
        }
        .mint-button::after {
            background: none;
            content: none;
        }
        .footer{
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            z-index: 99;
            background: #fff;
            height: 0.52rem;
            display: flex;
            align-items: center;
            overflow: hidden;
            .advice{
                width: 0.30rem;
                height: 0.34rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                font-size: 0.08rem;
                font-weight: 500;
                color: #8B9195;
                margin-left: 0.40rem;
                img{
                    width:0.20rem;
                    height:0.19rem;
                }
            }
            .appointment{
                width: 1.96rem;
                height: 0.34rem;
                margin-left: 0.40rem;
                background: linear-gradient(90deg, #EB433D 0%, #F56F47 100%);
                border-radius: 0.17rem;
                display: flex;
                align-items:center;
                font-size: 0.13rem;
                font-weight: 500;
                color: #FFFFFF;
                justify-content: center;
            }
        }
    }
</style>
