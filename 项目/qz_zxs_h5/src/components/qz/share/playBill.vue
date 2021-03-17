<template>
  <section class="bg">
    <common-top :companyMesage="companyMesage"></common-top>
    <div class="banner">
        <div class="logo">
            <img :src="companyInfo.logo" alt="" v-if="companyInfo.logo != ''">
            <img v-else src="../../../assets/img/qz/share/company-default-logo.jpg" alt="">
        </div>
        <div class="company-name">{{companyInfo.jc}}</div>
        <div class="info">
            <p>案例：{{companyInfo.case_count}}&nbsp; | &nbsp;&nbsp;设计师：{{companyInfo.designer_count}}&nbsp;&nbsp; | &nbsp;&nbsp;在建工地：{{companyInfo.build_count}}</p>
        </div>
    </div>
    <div class="main">
        <div class="intro" v-if="companyInfo.introduction != ''">
            <div class="title"><span class="left"></span>企业简介<span class="right"></span></div>
            <div class="desc" v-if="more">{{companyInfo.introductionSub}}</div>
            <div class="desc" v-else>{{companyInfo.introduction}}</div>
            <div class="next" v-if='introLength' @click="getMore()">
                <img src="../../../assets/img/qz/share/next.png" alt="">
            </div>
        </div>
        <div class="style" v-if="typeList.length != 0">
            <div class="title"><span class="left"></span>服务类型<span class="right"></span></div>
            <ul class="clearfix">
                <li v-for="(val,index) in typeList" :key="index">{{val}}</li>
            </ul>
        </div>
        <div class="area" v-if="companyInfo.fw_area != ''">
            <div class="title"><span class="left"></span>服务区域<span class="right"></span></div>
            <div class="area-desc">{{companyInfo.fw_area}}</div>
        </div>
        <div class="special" v-if="companyInfo.fw_special.length != 0">
            <div class="title"><span class="left"></span>服务特色<span class="right"></span></div>
            <div class="s-spec">
                <span v-for="(val,index) in companyInfo.fw_special" :key="index"><i class="icon-gou"></i>{{val.name}}</span>
            </div>
        </div>
        <div class="honor" v-if="companyInfo.honour.length != 0">
            <div class="title"><span class="left"></span>企业形象&荣誉<span class="right"></span></div>
            <swiper class="ul" :options="swiperOption" ref="mySwiper" >
                <swiper-slide class="swipe-item" v-for="(val,index) in companyInfo.honour" :key="index">
                    <img :src="val.img_url" alt="">
                </swiper-slide>
            </swiper>
        </div>
        <div class="yuyue" @click="toBaojia">
            <img src="../../../assets/img/qz/share/yuyue.png" alt="">
        </div>
    </div>
 </section>
</template>
<script>
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
import { shareCompanyBasicinfo } from '@/api/api'
import commonTop from '@/components/common/commonTop'
export default {
  name: 'shareBrand',
  components: {
    swiper,
    swiperSlide,
    commonTop
  },
  data () {
    return {
      id: '',
      companyMesage: {},
      swiperOption: {
        slidesPerView: 2.5,
        spaceBetween: 10,
        loop: false
      },
      companyInfo: {
        logo: '',
        jc: '',
        case_count: '',
        designer_count: '',
        build_count: '',
        introduction: '',
        introductionSub: '',
        fw_type: '',
        fw_area: '',
        fw_special: [],
        honour: []
      },
      typeList: [],
      more: true,
      introLength: false
    }
  },
  computed: {
    swiper () {
      return this.$refs.mySwiper.swiper
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getBaseinfo()
  },
  mounted () {
    this.getBaseinfo()
  },
  methods: {
    getBaseinfo () {
      shareCompanyBasicinfo({
        company_id: this.id
      }).then(res => {
        if (parseInt(res.data.error_code) === 0) {
          this.companyInfo = res.data.data
          this.companyMesage = this.companyInfo
          let arr = this.companyInfo.fw_type.split('、')
          this.typeList = arr
          if (this.companyInfo.introduction.length > 120) {
            this.introLength = true
            this.companyInfo.introduction = this.companyInfo.introduction.replace(/&quot;/g, '"')
            this.companyInfo.introductionSub = this.companyInfo.introduction.substr(0, 120) + '...'
          } else {
            this.more = false
          }
        }
      }).catch(err => {
        console.log(err)
      })
    },
    getMore: function () {
      this.more = false
      this.introLength = false
    },
    toBack: function () {
      this.$bridge.callNative('UI_closeWebView', {}, function (res) {

      })
    },
    toShare: function () {
      let companyName = this.companyInfo.jc
      this.$bridge.callNative('base_share', {
        mShareUrl: 'https://' + document.domain + '/share-playbill?sharebtnid=qztopshare_qzbottomshare&id=' + this.id,
        mShareTitle: companyName + '-齐装APP',
        mShareSubTitle: '我发现了优质装修公司，推荐你也来看看吧！',
        mShareIconUrl: this.companyInfo.logo
      }, function (res) {

      })
    },
    toBaojia: function () {
      this.$bridge.callNative('reservation_service', {}, function (res) {

      })
    }
  }
}
</script>
<style scoped>
    .open-app {
        margin-bottom: 0!important;
    }
    .bg {
        background: url(../../../assets/img/qz/share/c-bg.png) repeat;
        background-size: 100%;
        background-color: #09173a;
        min-height: 100%;
        width: 100%;
        box-sizing: border-box;
    }
    .top {
        background: #fff;
        height: 44px;
        width: 100%;
        line-height: 44px;
        position: fixed;
        top: 0;
    }
    .top .back {
        width: 10px;
        height: 18px;
        float: left;
        margin-left: 0.2rem;
        margin-top: 13px;
    }
    .top .back img {
        display: block;
        width: 100%;
        height: 100%;
    }
    .top .c-name {
        float: left;
        width: 70%;
        height: 100%;
        text-align: center;
        font-size: 0.162rem;
        color: #333;
        margin: 0 5%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .top .share {
        float: left;
        width: 20px;
        height: 18px;
        margin-top: 13px;
    }
    .top .share img {
        display: block;
        width: 100%;
        height: 100%;
    }
    .banner {
        width: 100%;
        height: 2.25rem;
        background: url(../../../assets/img/qz/share/bill-banner.png) no-repeat;
        background-size: 100%;
        margin-top: 44px;
    }
    .main {
        background-size: 100%;
        margin-top: -0.5rem;
    }
    .banner .logo {
        border-radius: 5px;
        margin: 0 auto;
        box-sizing: border-box;
        padding-top: 0.18rem;
    }
    .banner .logo img {
        display: block;
        width: 0.85rem;
        height: 0.6rem;
        border: 2px solid #E2A982;
        margin: auto;
        border-radius: 5px;
    }
    .banner .company-name {
        margin: 0.09rem 0;
        font-size: 0.18rem;
        color: #F3B48E;
        font-weight: bold;
        text-align: center;
    }
    .banner .info {
        font-size: 0.117rem;
        color: #BCA699;
        text-align: center;
        font-weight: 500;
    }
    .title {
        color: #F3B48E;
        font-size: 0.153rem;
        text-align: center;
    }
    .title .left {
        display: inline-block;
        width: 0.72rem;
        height: 0.135rem;
        background: url(../../../assets/img/qz/share/left-flower.png) no-repeat;
        background-size: 100%;
        margin-right: 0.09rem;
        vertical-align: middle;
    }
    .title .right {
        display: inline-block;
        width: 0.72rem;
        height: 0.135rem;
        background: url(../../../assets/img/qz/share/right-flower.png) no-repeat;
        background-size: 100%;
        margin-left: 0.09rem;
        vertical-align: middle;
    }
    .main .intro .title {
        margin-bottom: 0.18rem;;
    }
    .main .intro .desc {
        line-height: 0.18rem;
        font-weight: normal;
        font-size: 0.117rem;
        color:rgba(188,166,153,1);
        padding: 0 0.18rem;
        margin-bottom: 0.18rem;
    }
    .main .intro .next {
        margin-bottom: 0.3rem;
    }
    .main .intro .next img {
        display: block;
        width: 0.425rem;
        height: 0.25rem;
        margin: 0 auto;
    }
    .main .style ul {
        margin: 0.25rem 0.4rem 0.4rem;
    }
    .main .style ul li {
        width: 1.1rem;
        height: 0.2rem;
        padding-top: 2px;
        text-align: center;
        color: #BCA699;
        font-size: 0.117rem;
        float: left;
        margin-right: 4%;
        margin-bottom: 0.1rem;
        border: 1px solid;
    }
    .main .style ul li:nth-child(2n) {
        margin-right: 0;
        float: right;
    }
    .main .style ul li::before {
        content: '';
        width: 0.1rem;
        height: 0.2rem;
        background: #fff;
    }
    .main .style ul li:last-child {
        margin-right: 0;
    }
    .main .area .area-desc {
        color: #BCA699;
        line-height: 0.3rem;
        padding: 0 0.18rem;
        font-size: 0.117rem;
        text-align: center;
        margin-top: 0.225rem;
        margin-bottom: 0.4rem;
    }
    .main .special .s-spec {
        margin: 0.1rem 0 0.4rem;
        color: #BCA699;
        font-size: 0.117rem;
        padding: 0 0.17rem;
        text-align: center;
        line-height: 0.3rem;
    }
    .main .special .s-spec span {
        margin-right: 0.09rem;
    }
    .main .special .s-spec .icon-gou {
        display: inline-block;
        width: 15px;
        height: 15px;
        background: url(../../../assets/img/qz/share/icon-gou.png) no-repeat;
        background-size: 100%;
        margin-right: 2px;
        position: relative;
        top: 3px;
    }
    .main .honor {
        height: 1.5rem;
    }
    .main .honor .ul {
        margin-top: 0.225rem;
        margin-left: 0.135rem;
    }
    .main .honor .ul .swipe-item {
        background: #fff;
        height: 0.72rem;
        touch-action: none;
    }
    .main .honor .ul .swipe-item img {
        display: block;
        width: 100%;
        height: 100%;
    }
    .main .yuyue {
        text-align: center;
        padding-bottom: 0.5rem;
    }
    .main .yuyue a {
        display: block;
        width: 2.25rem;
        height: 0.45rem;
        margin: 0 auto;
    }
    .main .yuyue img {
        display: block;
        width: 2.25rem;
        height: 0.45rem;
        margin: 0 auto;
    }
</style>
