<template>
  <section class="lottery">
    <div class="box" id="downloadButton">
      <img src="//staticqn.qizuang.com/custom/20200724/FmwkzbggNGpMmB75XKMVKxz2U3Hm" />
      <p class="lottery-btn">去APP抽奖</p>
    </div>
    <wx-tips :isWechat="isWechat"></wx-tips>
  </section>
</template>

<script>
  import {mobileSystem} from "@/utils/globalFun"
  import wxTips from '@/components/common/wxTips'
  export default {
    name: 'lottery',
    components: {
      wxTips
    },
    data () {
      return {
        isWechat: false
      }
    },
    mounted() {
      this.wakeUpApp()
    },
    created() {
      this.isDefaultBrowser()
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
      }
    }
  }
</script>

<style scoped lang="scss">
  .lottery{
    width: 100%;
    img{
      width: 100%;
      vertical-align: middle;
    }
    .box{
      padding-bottom: 0.75rem;
      background: #292929;
      .lottery-btn{
        width: 2.133rem;
        height: 0.384rem;
        position: fixed;
        bottom: 0.363rem;
        left: 0;
        right: 0;
        margin: auto;
        background: #DE2C31;
        border-radius: 0.192rem;
        font-size: 0.154rem;
        font-weight: bold;
        text-align: center;
        line-height: 0.384rem;
        color: #fff;
        z-index: 999;
      }
    }
  }
</style>
