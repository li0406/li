<template>
  <section>
    <div class="top-banner">
      <img src="../../../assets/img/qz/share/top-banner.png" class="top-banner-bg"/>
      <img src="../../../assets/img/qz/share/top-title.png" class="top-banner-title"/>
      <div class="top-bottom-buttons">
        <img src="../../../assets/img/qz/share/new-logo.png" style="width:45%" class="pull-left">
        <button class="btn btn-red-line pull-right" style=" margin-top:0.06rem;width:110px" @click="toDown">立即下载</button>
      </div>
    </div>
    <div class="center-container">
      <ul class="item-box">
        <li class="item-right">
          <div class="item-title">
            <img src="../../../assets/img/qz/share/title-icon1.png">
            寻找灵感
          </div>
          <p>美家案例、流行美图</p>
          <p>激发家的装修灵感</p>
          <img src="../../../assets/img/qz/share/banner-icon1.png" class="banner-icons">
        </li>
        <li class="item-left">
          <div class="item-title">
            <img src="../../../assets/img/qz/share/title-icon2.png">
            学习视频
          </div>
          <p>装修视频、真人讲解</p>
          <p>轻松扫除装修盲区</p>
          <img src="../../../assets/img/qz/share/banner-icon2.png" class="banner-icons">
        </li>
        <li class="item-right">
          <div class="item-title">
            <img src="../../../assets/img/qz/share/title-icon3.png">
            查找攻略
          </div>
          <p>避坑指南、装修秘籍</p>
          <p>让你装修不再踩坑</p>
          <img src="../../../assets/img/qz/share/banner-icon3.png" class="banner-icons">
        </li>
        <li class="item-left" style="border:none;">
          <div class="item-title">
            <img src="../../../assets/img/qz/share/title-icon4.png">
            预约服务
          </div>
          <p>0元设计、快速报价</p>
          <p>智能匹配装修公司</p>
          <img src="../../../assets/img/qz/share/banner-icon4.png" class="banner-icons">
        </li>
      </ul>
    </div>
    <div class="bottom-fixed">
      <img src="../../../assets/img/qz/share/newbottom-log.png" class="pull-left">
      <span class="pull-left" style="line-height:55px;font-size:16px;margin-left:10px;">齐装</span>
      <a href='javascript:void(0);'  id='downloadButton' class="btn btn-red pull-right" style="margin-top:15px;margin-right:28px;">打开APP</a>
    </div>
    <br><br><br>
    <div class="wechat-mask" v-if="isWechat"  @click="hideMask">
      <img src="../../../assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
    </div>
  </section>
</template>
<script>
export default {
  name: 'qzshare',
  data () {
    return {
      isWechat: false,
      envi: ''
    }
  },
  created () {
    this.envi = this.$globalFun.mobileSystem()
  },
  mounted () {
    this.wakeUpApp()
    document.addEventListener('click', (e) => {
      if (e.target.nodeName === 'IMG') {
        e.preventDefault()
      }
    })
  },
  methods: {
    hideMask () {
      this.isWechat = false
    },
    toOpen () {
      let that = this
      let ua = navigator.userAgent.toLowerCase()
      if (ua.indexOf('micromessenger') !== -1) {
        that.isWechat = true
        return
      }
      if (that.envi === 'Android') {
        window.location.href = 'qizuang-qz://com.qizuang.qz'
      } else {
        window.location.href = 'qizuang-qz://'
      }
    },
    toDown () {
      if (this.envi === 'iOS') {
        window.location.href = 'https://itunes.apple.com/cn/app/id1466041458'
        return false
      }
      if (this.envi === 'Android') {
        window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.qz'
        return false
      }
    },
    wakeUpApp () {
      // const data = ShareInstall.parseUrlParams()
      // const data = {
      //   key: 'K6BKK6BE66E6HB'
      // }
      // new ShareInstall({
      //   appKey : 'K6BKK6BE66E6HB',
      //   onready : function () {
      //     var m = this, button = document.getElementById("downloadButton")
      //     button.style.visibility = "visible"
      //
      //     button.onclick = function () {
      //       m.wakeupOrInstall()
      //     }
      //   }
      // }, data)
    }
  }
}
</script>
<style scoped>
  .top-banner{
    position: relative;
  }
  .top-banner .banner-img{
    width:100%
  }
  .center-container{
    border-top:0.15rem solid #F5F5F5;
    padding:0.2rem;
  }
  .bottom-fixed{
    box-shadow: 0px 0px 5px #999;
    position: fixed;
    bottom: -1px;
    left:0px;
    width:100%;
    padding:0.05rem 0.11rem;
    background: #fff;
  }
  .item-right{
    text-align: right
  }
  .item-left .item-title{
    margin-left:-6px;
  }
  .item-left p{
    text-indent: 40px;
  }
  .item-title{
    font-size: 0.21rem;
    line-height: 40px;
  }
  .item-title img{
    width: 40px;
    vertical-align: top;
  }
  .item-box p{
    padding: 2px 0px;
  }
  .item-box li {
    border-bottom:1px solid #E6E4E1;
    margin-bottom:0.2rem;
  }
  .top-bottom-buttons{
    position: absolute;
    width:calc(100% - 0.3rem);
    left:0px;
    bottom: 0px;
    padding:0.15rem;
  }
  .btn{
    width:85px;
    padding:0.04rem;
    text-align: center;
    border-radius: 0.4rem;
    box-sizing: border-box;
    background: none;
    outline: none;
    margin:0px 0.05rem;
  }
  .btn-black{
    border:1px solid #333;
  }
  .btn-red{
    border:1px solid #ff5353;
    background: #ff5353;
    color:#fff;
  }
  .btn-red-line{
    border:1px solid #ff5353;
    color:#ff5353;
    padding:0.06rem 0px;
  }
  .banner-icons{
    width: 100%;
    display: block;
    margin:0.1rem 0px
  }
  .top-banner-bg{
    width:100%
  }
  .top-banner-title{
    position: absolute;
    width:74%;
    top:10%;
    margin:auto;
    left:0px;
    right:0px;
  }
  .bottom-fixed img{
    width:52px;
  }
  .wechat-mask{
    position: fixed;
    left:0px;
    top:0px;
    width:100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
  }
</style>
