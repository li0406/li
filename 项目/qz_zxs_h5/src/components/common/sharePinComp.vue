<template>
  <div class="open-app">
    <header class="qz-header clearfix" @click="onCopy">
      <div class="left"><img src="../../assets/img/share/new-logo.png" class="img-responsive v-middle"></div>
      <div class="middle"><span class="c3" style="line-height: 1.5">有房要装，就上齐装！</span></div>
      <div class="right"><span class="openapp">去APP打开</span></div>
    </header>
  </div>
</template>

<script>
import callappLib from '../../mixins/callappLib'
import { getIosVer, mobileSystem } from '../../utils/globalFun'
export default {
  name: 'openApp',
  mixins: [callappLib],
  data () {
    return {
      iosVer: '',
      sys: '',
      urlValue: 'qz/go?action=QZ_DECORATION_GUARANTEE',
      thirdParty: false,
      isWechat: false
    }
  },
  mounted() {
    this.isDefaultBrowser();
  },
  created () {
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
.open-app{
  margin-bottom:0.078rem;
}
.qz-header{
  background-color: #FEE0E0;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  color: #fff;
  font-size: 14px;
  line-height: 1;
  padding: 5px 0.05rem;
  position: relative;
  text-align: center;
  white-space: nowrap;
}
.qz-header>div{
  float: left;
}
.qz-header > .left{
  width: 0.32rem;
  height: 0.32rem;
}
.qz-header > .middle{
  width: 1.7rem;
  line-height: 0.32rem;
  text-align: left;
  padding-left: 0.1rem;
}
.qz-header > .right{
  margin-top: 0.11rem;
  float: right;
}
.qz-header .openapp{
  background-color: white;
  color: #FF5353;
  border-radius: 0.5rem;
  padding: 0.02rem 0.06rem;
}
</style>
