<template>
    <div class="p-container">
        <div class="top">
            <div class="top-item one">
                1.选择户型
            </div>
            <div class="top-item active">
                2.选择风格
            </div>
            <div class="top-item">
                3.免费领取方案
            </div>
        </div>
        <div class="select-container">
            <div class="select-title">
                中意的<span>风格</span><span class="title-bit">（点击图片查看图集）</span>
            </div>
            <div class="select-table p-owf">
                <div class="select-table-td" v-for="(item, index) in fgData" :key="item.name">
                    <div class="select-box">
                        <div class="select-image-border">
                            <img v-if="resData[item.name]" :src="resData[item.name] | imgFilter" alt="" @click="getLargeImage(item.name)">
                            <img v-else class="default" src="../../../assets/img/qz/style/default.jpg" alt="">
                        </div>
                    </div>
                    <div @click='changeSelect(item.name,item.id)'>
                        <div v-if="!item.selected"  class="radius">
                            <input type="radio" class="radio-select" size='16' name="radios" ></input>
                            <label>{{item.text}}</label>
                        </div>
                        <div v-else  class="radius">
                            <input type="radio" class="radio-select" size='16' name="radios"></input>
                            <label class="colorSelect">{{item.text}}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div @click="toStyle" class="bottom-btn">
            <img src="../../../assets/img/qz/style/bottom_btn.png" class="center-center">
            <span>选好了</span>
        </div>
        <div @click="toHuxing" class="bottom-btn bottomtwo">
            <img src="../../../assets/img/qz/style/bottomtwo.png" class="center-center">
            <span>返回重选</span>
        </div>
        <m-tips ref="tips"/>
        <div class="swiper-mask" v-if="largeView" @click='getLargeImage()'>
            <div class="banner-box swiper-container">
                <swiper :options="swiperOption">
                    <swiper-slide class="swiper-item" v-for="(item,index) in swiperData" :key="index">
                        <img :src="item.img_url">
                    </swiper-slide>
                    <div class="swiper-pagination" slot="pagination"></div>
                </swiper>
            </div>
        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import { getMeitu } from '@/api/api'
export default {
    name: "fengge",
    components: {
        mTips,
        swiper,
        swiperSlide
    },
    filters: {
        imgFilter(obj){
            if(obj && obj[0].img_url){
                return obj[0].img_url
            }
        }
    },
    data() {
        return {
            swiperOption: {
                notNextTick: true,
                pagination: {
                    el: '.swiper-pagination',
                    type: 'fraction'
                },
                slidesPerView: 'auto',
                centeredSlides: true,
                paginationClickable: true,
                observeParents: true
            },
            fgData: [
                {
                    name:'os',
                    text:'欧式风格',
                    selected:false,
                    id:2
                },
                {
                    name:'zs',
                    text:'中式风格',
                    selected:false,
                    id:3
                },
                {
                    name:'rsjy',
                    text:'日式简约风格',
                    selected:false,
                    id:1
                },
                {
                    name:'dzh',
                    text:'地中海风格',
                    selected:false,
                    id:6
                },
                {
                    name:'ty',
                    text:'田园风格',
                    selected:false,
                    id:5
                },
                {
                    name:'qt',
                    text:'其他',
                    selected:false,
                    id:9
                }
            ],
            hxselectId: '',
            cid: '',
            resData: [],
            largeView: false,
            swiperData: [],
            swiperLen: '',
            img_url:'',
            fengge:'',
            huxing:'',
            fengid:''
        }
    },
    created () {
        this.getStyleImage()
        let url = location.search
        var theRequest = new Object();
        if(url.indexOf("?") != -1 ){
            var str = url.substr(1);
            var strs = str.split("&");
            for(var i= 0;i<strs.length;i++){
                theRequest[strs[i].split("=")[0]] = (strs[i].split("=")[1]);
            }
        }
        let hx = theRequest.hx
        let fg = theRequest.fg
        this.huxing = hx
        this.setCurrent(fg)
        this.changeHeader()
    },
    mounted () {

    },
    methods: {
        changeSelect(e,f) {
            this.setCurrent(e,f)
        },
        toStyle(parent) {
            let that = this
            if (that.fengge === '') {
                that.$refs.tips.tipsFadeIn({
                    text: '请选择风格'
                })
                return
            }
            window.location.href = "third?hx="+that.huxing+'&fg='+that.fengge+'&fd='+that.fengid
        },
        toHuxing(){
            let that = this
            this.fengge = ''
            window.location.href = "huxing?hx="+that.huxing
        },
        setCurrent:function(id,f){
            let me = this
            for (let i in me.fgData) {
                if (me.fgData[i].name == id) {
                    me.fgData[i].selected = true
                    me.fengge = id
                    me.fengid = f
                } else {
                    me.fgData[i].selected = false
                }
            }
        },
        getStyleImage() {
            getMeitu().then((res) => {
              if(res.data.error_code ===0){
                  this.resData = res.data.data.list
              }
            })
        },
        getLargeImage(e) {
            let me = this
            if(e != undefined){
                let index = e
                me.swiperData = me.resData[index]
                me.swiperLen = me.resData[index].length
                me.currentIndex = 1
            }
            this.largeView = !this.largeView
        },
        changeHeader (){
            let that = this
            // 回退
            that.$bridge.callNative('UI_onBackClick', '000', function (res) {
                if (res) {
                    that.endCall = true
                }
            })
            that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
                nativeCallBack(1)
            })
            if (!that.endCall) {
                setTimeout(function () {
                    that.changeHeader()
                }, 200)
            }
        }
    }
}
</script>

<style scoped>
    .p-container{
        overflow: hidden;
        padding:0 0.12rem;
        background: -webkit-linear-gradient(#5D2FAC 10%, #38349B);
        background: -o-linear-gradient(#5D2FAC 10%, #38349B);
        background: -moz-linear-gradient(#5D2FAC 10%, #38349B);
        background: linear-gradient(#5D2FAC 10%, #38349B);
    }
    .bottom-btn{
        width:84%;
        height:0.45rem;
        margin: 0.35rem auto;
        margin-bottom: 0.12rem;
        background:rgba(255,15,88,0.1);
        border-radius:0.22rem;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bottom-btn img{
        width: 100%;
        height: 100%;
        position: absolute;
        top:0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .bottom-btn span{
        color:rgba(255,35,113,1);
        font-size: 0.18rem;
    }
    .select-title{
        height: 0.60rem;
        line-height: 0.60rem;
        font-size:0.16rem;
        font-weight:500;
        color:rgba(255,255,255,1);
    }
    .select-title span{
        color: #02EAFF;
    }
    .select-title .title-bit{
        color: #ffffff;
        font-size: 0.11rem;
    }
    .select-table{
        width: 100%;
        overflow: hidden;
        box-sizing: border-box;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-content: space-between;
    }
    .select-table-td{
        width: 48%;
        height:auto;
    }
    .select-box{
        overflow: hidden;
        border-radius: 0.06rem;
    }
    .select-image-border{
        width:100%;
        height:1.08rem;
        border:0.024rem solid  ;
        border-radius:0.06rem;
        box-sizing: border-box;
        overflow: hidden;
        border-image:linear-gradient(0deg, rgba(46,41,255,1), rgba(47,189,255,1)) 10 10;
        position: relative;
    }
    .select-image-border img{
        width: 100%;
        height: 100%;
        position: absolute;
        top:0;
        left: 0;
        z-index: 0;
    }
    .radius{
        height: 0.34rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 0.05rem 0 0.1rem 0;
    }
    .radius label{
        color: #ffffff;
        font-size: 0.14rem;
        margin-left: 0.06rem;
    }
    .radius .colorSelect{
        color: #0BC0DC;
    }
    .colorSelect::before{
        border: 1px solid #0BC0DC;
    }
    div>input{
        display: none;
    }
    div>label{
        position: relative;
        margin-right: 34px;
    }
    div>label::before{
        display: inline-block;
        content: "";
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 1px solid rgb(219, 219, 219);
        margin-right: 6px;
        vertical-align: bottom;
        margin-bottom: 2px;
    }
    div>input:checked+label::before{
        background-color: rgba(239, 66, 56, 0);
    }
    div>input:checked+label::after{
        display: inline-block;
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        position: absolute;
        left: 6px;
        bottom: 8px;
        background-color: #0BC0DC;
    }
    .colorSelect::after{
        display: inline-block;
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        position: absolute;
        left: 6px;
        bottom: 8px;
        background-color: #0BC0DC;
    }
    .radius input {
        display: block;
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 3;
    }
    .bottomtwo{
        margin-top: 0;
        margin-bottom: 0.40rem;
    }
    .bottomtwo span{
        color:rgba(255,255,255,1);
        opacity:0.6;
    }
    .default{
        opacity: 0.9;
    }
    .swiper-mask{
        position: fixed;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0,0.7);
        left:0px;
        top:0px;
        z-index: 4;
    }
    .swiper-container{
        height: 100%;
    }
    .swiper-item{
        height: 100%;
        position: relative;
    }
    .banner-box img{
        width:100%;
        position:absolute;
        left:0px;
        right:0px;
        top:0px;
        bottom:0px;
        margin:auto;
    }
    .swiper-pagination{
        color:#ffffff;
    }
    .top{
        width: 100%;
        height: 0.42rem;
        margin-top:0.10rem;
        display: flex;
        align-items: center;
    }
    .top .one{
        margin-left: 0;
    }
    .top-item{
        width: 36%;
        height: 100%;
        margin-left: -0.08rem;
        background: url("../../../assets/img/qz/style/topitem.png") center no-repeat;
        background-size: 100% 100%;
        line-height: 0.42rem;
        text-align: center;
        font-size:0.12rem;
        font-weight:500;
        color:rgba(255,255,255,1);
    }
    .top .active{
        background: url("../../../assets/img/qz/style/topact.png") center no-repeat;
        background-size: 100% 100%;
        color:#02F0FF;
    }
</style>
