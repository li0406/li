<template>
  <div class="qdjl">
    <div class="top">
          <h3>组队来领取富贵缠身，一起 <br>嗨起来吧~</h3>
    </div>
    <div class="main">
      <h3 class="title">再签<span class="main_num">7</span>天有惊喜，最高可得<span class="main_num">88</span>现金券哦</h3>
      <div class="list">
        <ul>
          <li class="">
            <div class="icon icon1"></div>
            <span class="red">{{week[0]}}</span>
          </li>
          <li class="line"></li>
          <li class="">
            <div class="icon icon2"></div>
            <span class="red">{{week[1]}}</span>
          </li>
          <li class="line"></li>
          <li class="">
            <div class="icon icon2"></div>
            <span class="red">{{week[2]}}</span>
          </li>
          <li class="line"></li>

          <li class="">
            <div class="icon icon2"></div>
            <span class="red">{{week[3]}}</span>
          </li>
          <li class="line"></li>

          <li class="">
            <div class="icon icon2"></div>
            <span class="red">{{week[4]}}</span>
          </li>
          <li class="line"></li>

          <li class="">
            <div class="icon icon2"></div>
            <span class="red">{{week[5]}}</span>
          </li>
          <li class="line"></li>
          <li class="">
            <div class="icon3 icon"></div>
            <span class="red">{{week[6]}}</span>
          </li>
        </ul>
      </div>
      <div class="btn_title testlibopenappfn" v-clipboard:copy="text" v-clipboard:success="onCopy">
        <span>去助Ta一臂之力！</span>
      </div>
    </div>
    <div class="wx_tips">
      <div class="wechat-mask" v-if="isWechat">
        <img src="@/assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
      </div>
    </div>
  </div>
</template>

<script>

  import {mobileSystem} from '../../utils/globalFun'
  import callappLib from '../../mixins/callappLib'
  import mTips from '../common/mTips.vue'

  import Vue from 'vue'
  import VueClipboard from 'vue-clipboard2'
  Vue.use(VueClipboard);

  export default {
    name: "qdjl",
    mixins: [callappLib],
    components: {
      mTips
    },
    data() {
      return {
        week: [],
        isWechat: false,
        thirdParty: false,
        text:'',
        message:'123123123123123123123123123123123'
      }
    },
    created() {
      // 默认显示当天前一周的数据
      let data = []
      for (let i = 0; i < 7; i++) {
        data.push(this.getDay(i))
      }
      this.week = data
      this.thirdParty = (this.iosVer >= 12) && (this.sys === 'ios')
      let UUID = this.$route.query.UUID
      let imgUrl = this.$route.query.imgUrl
      let isRemind = this.$route.query.isRemind
      this.text = '{"UUID":"'+UUID+'","imgUrl":"'+imgUrl+'","isRemind":"'+isRemind+'","scheme":"qizuang-qz"}'
    },
    mounted() {
      this.isDefaultBrowser();
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
        // 判断是否是微信、微博、手机百度内
        if (ua.indexOf('micromessenger') !== -1 || ua.indexOf('weibo') !== -1|| ua.indexOf('baiduboxapp') !== -1) {
          if (this.$parent.$parent.isWechat === undefined) {
            this.isWechat = true
          }
        }
      },
      getDay(day) {
        let today = new Date();
        let targetday_milliseconds = today.getTime() + 1000 * 60 * 60 * 24 * day;
        today.setTime(targetday_milliseconds); //注意，这行是关键代码
        let tMonth = today.getMonth();
        let tDate = today.getDate();
        tMonth = this.doHandleMonth(tMonth + 1);
        tDate = this.doHandleMonth(tDate);
        return tMonth + "." + tDate;
      },
      doHandleMonth(month) {
        let m = month;
        if (month.toString().length == 1) {
          m = "0" + month;
        }
        return m;
      },
      onCopy(){
        const sys = mobileSystem().toLowerCase()
        if (sys === 'ios'){
          this.qzOpenTips()
        }else{
          this.qzOpenApp()
        }

      }
    }
  }
</script>

<style scoped>
  .qdjl .top {
    height: 1rem;
    background: url("../../assets/img/tjyl/qdjl_top.png") no-repeat center center;
    background-size: cover;
    color: #fff;
    font-size: .1rem;
    line-height: 1.5;
    overflow: hidden;
    padding-top: .8rem;
    padding-left: .1rem;
  }

  .qdjl .main {
    margin: -.2rem .1rem 0;
    background: #fff;
    border-radius: .11rem;
    box-shadow: 0 0 5px #999;
    padding-bottom: .7rem;
  }

  .qdjl .title {
    margin-left: .12rem;
    font-size: .13rem;
    line-height: .55rem;
    margin-bottom: .04rem;
  }

  .qdjl .main {

  }

  .qdjl .main_num {
    color: #ff5353;
  }

  .qdjl .list {
    margin: 0 .06rem .512rem;
    text-align: center;
  }

  .qdjl .main ul li {
    display: inline-block;
    width: 8.1%;
    /*width: .235rem;*/
    height: .2rem;
    text-align: center;
  }

  .qdjl .main ul li.line {
    width: .12rem;
    position: relative;
  }

  .qdjl .main ul li.line:before {
    content: " ";
    position: absolute;
    display: inline-block;
    width: 100%;
    height: 1px;
    background: #BFBFBF;
    left: 0;
    top: -.09rem;
  }

  .qdjl .main ul li .icon {
    height: .19rem;
    background: url("../../assets/img/tjyl/qdjl_icon1.png") no-repeat center center;
    background-size: contain;
    margin-bottom: .05rem;
  }

  .qdjl .main ul li .icon1 {
    background-image: url("../../assets/img/tjyl/qdjl_icon1.png");
  }

  .qdjl .main ul li .icon2 {
    background-image: url("../../assets/img/tjyl/qdjl_icon2.png");
  }

  .qdjl .main ul li .icon3 {
    background-image: url("../../assets/img/tjyl/qdjl_icon3.png");
  }


  .qdjl .main ul li span {
    font-size: .1rem;
  }


  .qdjl .btn_title {
    text-align: center;
  }

  .qdjl .btn_title span {
    display: inline-block;
    background-color: #FF5353;
    text-align: center;
    padding: 0 0.55rem;
    line-height: 0.3755rem;
    color: #ffffff;
    font-size: 0.14rem;
    letter-spacing: 0.02rem;
    -webkit-border-radius: 0.2rem;
    -moz-border-radius: 0.2rem;
    border-radius: 0.2rem;
    font-weight: bold;
  }
  .qdjl .wx_tips .wechat-mask{
    position: fixed;
    left:0px;
    top:0px;
    width:100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
  }

</style>
