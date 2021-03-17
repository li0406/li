<template>
  <section class="share-good">
    <div class="good-header" @click="qzOpenApp">
      <div class="left"><img src="../../../assets/img/share/new-logo.png" class="img-responsive v-middle"></div>
      <div class="middle"><span class="c3" style="line-height: 1.5">下载齐装APP</span></div>
      <div class="right"><span class="openapp">去APP打开</span></div>
    </div>
    <div class="swiper-container">
      <swiper :options="swiperOption">
        <swiper-slide v-for="(val,index) in imgLIst" :key="index">
          <img :src="val" />
        </swiper-slide>
      </swiper>
    </div>
    <div class="good-tit">{{ shareInfo.title }}</div>
    <p class="good-des">
      <span>【包邮】</span>
      <span>{{shareInfo.free_shipment == 1 ? '是' : '否'}}</span>
    </p>
    <p class="good-des">
      <span>【在售价】</span>
      <span>{{ shareInfo.reserve_price }}元</span>
    </p>
    <p class="good-des">
      <span>【券后价】</span>
      <span>{{ shareInfo.zk_final_price }}元</span>
    </p>
    <div class="good-quan">
      <div class="good-jine">{{quanContent.cash}}元购物优惠券</div>
      <div class="good-time">
        <span>有效期：</span>
        <span>{{quanContent.start_time}}-{{quanContent.end_time}}</span>
        <span @click="qzOpenApp" class="quan-btn">兑换领券</span>
      </div>
    </div>
    <div class="good-share">
      <div class="share-left">
        <div>齐装</div>
        <p>扫码下载APP</p>
        <p @click="qzOpenApp">开启经济适用新生活>>></p>
      </div>
      <div class="share-right">
        <img src="../../../assets/img/qz/share/share.png" />
      </div>
    </div>
    <div class="gotoapp" @click="qzOpenApp">去APP内查看</div>
    <div class="wx_tips">
      <div class="wechat-mask" v-if="isWechat">
        <img src="@/assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
      </div>
    </div>
  </section>
</template>
<script>
import {mobileSystem} from '../../../utils/globalFun'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import qzOpenApp from '@/mixins/qzOpenApp'
import { shareGoodInfo } from '@/api/api'
export default {
  name: 'shareGood',
  components: {
    swiper,
    swiperSlide,
  },
  mixins: [qzOpenApp],
  data () {
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
      imgLIst: [],
      shareInfo: {},
      quanContent: {},
      id: '',
      isWechat: false
    }
  },
  mounted () {
    this.id = this.$route.query.id
    this.shareGoodInfo()
    this.isDefaultBrowser()
  },
  created() {

  },
  methods: {
    isDefaultBrowser() {
      const sys = mobileSystem().toLowerCase()
      const ua = navigator.userAgent.toLowerCase()
      // 判断是否是ios的qq内置浏览器和qq浏览器的区别
      if (sys === 'ios' && / QQ/i.test(navigator.userAgent)) {
        this.isWechat = true
      }
      // 判断是否是android的qq内置浏览器和qq浏览器的区别
      if (sys === 'android' && /MQQBrowser/i.test(navigator.userAgent) && /QQ/i.test((navigator.userAgent).split('MQQBrowser'))) {
        this.isWechat = true
      }
      // 判断是否是微信、微博内
      if (ua.indexOf('micromessenger') !== -1 || ua.indexOf('weibo') !== -1) {
        if (this.$parent.$parent.isWechat === undefined) {
          this.isWechat = true
        }
      }
    },
    shareGoodInfo: function() {
      shareGoodInfo({
        id: this.id
      }).then(res => {
        if(res.status === 200 && res.data.error_code === 0) {
          this.shareInfo = res.data.data
          this.imgLIst = res.data.data.img
          this.quanContent = res.data.data.coupon
        }
      })
    }
  }
}
</script>
<style scoped>
  img{
    max-width:100%;
    vertical-align: middle;
  }
  p{
    margin-bottom:0.05rem;
  }
  .share-good{
    padding-bottom: 0.5rem;
  }
  .good-header{
    background-color: #FEE0E0;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    color: #fff;
    font-size: 14px;
    line-height: 1;
    padding: 5px 0.05rem;
    height: 0.4rem;
    position: relative;
    text-align: center;
    white-space: nowrap;
  }
  .good-header>div{
    float: left;
  }
  .good-header > .left{
    width: 0.32rem;
    height: 0.32rem;
  }
  .good-header > .middle{
    width: 1.7rem;
    line-height: 0.32rem;
    text-align: left;
    padding-left: 0.1rem;
  }
  .good-header > .right{
    margin-top: 0.11rem;
    float: right;
  }
  .good-header .openapp{
    background-color: white;
    color: #FF5353;
    border-radius: 0.5rem;
    padding: 0.02rem 0.06rem;
  }
  .good-tit{
    padding:0.1rem;
    font-size:17px;
    color:#333333;
    word-break: break-all;
  }
  .good-des{
    font-size:13px;
    padding:0 0.1rem;
    color:#999999
  }
  .good-quan{
    margin:0.1rem;
    height: 0.77rem;
    padding-top:0.1rem;
    position: relative;
    background:url("../../../assets/img/qz/share/bg.png");
    background-size:100% 100%;
  }
  .good-quan>div{
    color:#fff;
    padding-left: 0.1rem;
    margin-top:0.11rem;
  }
  .good-quan>div.good-jine{
    font-weight: bold;
    font-size: 24px;
  }
  .good-quan>div.good-time{
    font-size: 14px;
  }
  .quan-btn{
    display: block;
    width: 0.75rem;
    height: 0.25rem;
    line-height: 0.25rem;
    border-radius:16px;
    text-align: center;
    background: #FFDAE0;
    color:#F6294A;
    position: absolute;
    top:0;
    bottom: 0;
    right: 0.15rem;
    margin:auto;
  }
  .good-share{
    padding: 0.1rem;
    margin-top: 0.14rem;
    overflow: hidden;
  }
  .good-share .share-left{
    width:70%;
    float: left;
  }
  .good-share .share-left div{
    font-weight: bold;
    color:#333333;
    font-size: 25px;
    margin-bottom: 0.1rem;
  }
  .good-share .share-left p{
    color:#999999;
    font-size: 17px;
  }
  .good-share .share-right{
    width:30%;
    float: left;
  }
  .share-right img{
    width: 0.75rem;
    height: 0.75rem;
  }
  .gotoapp{
    width: 1.03rem;
    height: 0.312rem;
    background:rgba(246,41,74,1);
    border-radius:0.156rem;
    font-size:13px;
    font-weight:500;
    color:rgba(254,254,254,1);
    line-height:0.312rem;
    text-align: center;
    position: fixed;
    bottom: 0.161rem;
    left: 0;
    right: 0;
    margin: 0 auto;
  }
  .share-good .wx_tips .wechat-mask{
    position: fixed;
    left:0px;
    top:0px;
    width:100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
  }
</style>
