<template>
  <section>
    <div class="top-banner">
      <img src="../../../assets/img/ygj/guid-banner1.png" class="top-banner-bg"/>
      <div  class="guid-logo">
        <img src="../../../assets/img/ygj/ygj-logo.png" style="width:40px;">
        <p>让装企快速接单轻松赚钱</p>
      </div>
    </div>
    <div class="down-load-title">
      <button class="load-button" type="button" @click="toloadApp">
        下载齐装云管家
      </button>
    </div>
    <div class="mk-title-box">
      <h2>在线接单</h2>
      <div>移动便捷&nbsp;&nbsp;超多订单</div>
      <span class="under-line"></span>
      <img src="../../../assets/img/ygj/zxjd.png">
      <div class="right-gb-circle"></div>
    </div>
    <div class="mk-title-box align-right">
      <h2>订单管理</h2>
      <div>在线跟单&nbsp;&nbsp;超高转化</div>
      <span class="under-line"></span>
      <img src="../../../assets/img/ygj/ddgl.png"/>
    </div>
    <div class="mk-title-box">
      <h2>业主沟通</h2>
      <div>联系业主&nbsp;&nbsp;快捷方便</div>
      <span class="under-line"></span>
      <img src="../../../assets/img/ygj/yzgt.png">
      <div class="yzgt-bg-circle"></div>
    </div>
    <div class="mk-title-box align-right">
      <h2>信息推送</h2>
      <div>业务信息&nbsp;&nbsp;即时送达</div>
      <span class="under-line"></span>
      <img src="../../../assets/img/ygj/xxts.png" alt="">
    </div>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <div class="bottom-button" v-if="scrollHeight - windowHeight > 0" @click="toloadApp">
      <img src="../../../assets/img/ygj/button-button.png">
    </div>
  </section>
</template>
<script>
export default {
  name: 'guiddown',
  data () {
    return {
      scrollHeight: 0,
      windowHeight: 0
    }
  },
  created: function () {
    this.windowHeight = window.outerHeight
  },
  mounted: function () {
    let that = this
    window.addEventListener('scroll', event => {
      that.scrollHeight = window.pageYOffset
    })
    console.log(this.$globalFun)
  },
  methods: {
    toloadApp () {
      let envi = this.$globalFun.mobileSystem()
      if (envi === 'iOS') {
        window.location.href = 'https://itunes.apple.com/cn/app/id1457641013'
        return false
      }
      if (envi === 'Android') {
        window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qizuang.sjd'
        return false
      }
    }
  }
}
</script>
<style scoped>
  section{
    width:100%;
    overflow-x:hidden;
  }
  .top-banner {
    position: relative;
  }
  .top-banner .top-banner-bg{
    width:100%;
  }
  .guid-logo{
    position: absolute;
    top:0.31rem;
    left:0.12rem;
    color: #fff;
  }
  .guid-logo p{
    margin-top:0.01rem;
    font-size: 0.15rem
  }
  .down-load-title{
    height: 1rem;
    background: url('../../../assets/img/ygj/down-load-title.png') no-repeat bottom center;
    background-size: 100%
  }
  .load-button{
    background: none;
    border:2px solid #ff5353;
    padding:0.1rem 0.5rem;
    font-size:16px;
    border-radius:0.2rem;
    color:#ff5353;
    margin:0.2rem auto;
    display: block;
    outline: none;
  }
  .mk-title-box {
    margin:0px 0.2rem;
    padding-top:0.5rem;
    position: relative;
  }
  .mk-title-box h2 {
    font-weight: normal;
    font-size: 0.2rem;
    margin-bottom:5px;
  }
  .mk-title-box .under-line{
    width:0.38rem;
    height: 4px;
    display: inline-block;
    background: #ff5353;
    position: relative;
    top:5px;
    margin-bottom:0.35rem;
  }
   .mk-title-box div{
     color:#666666;
     font-size: 0.15rem;
   }
   .mk-title-box img{
     width:100%;
     display: block
   }
   .align-right{
     text-align: right;
   }
  .bottom-button{
    width:100%;
    text-align: center;
    position: fixed;
    bottom:10px;
    left:0px;
  }
  .bottom-button img{
    width:90%;
  }
  .right-gb-circle{
    position: absolute;
    width:2.8rem;
    height:2.8rem;
    background: #FAFAFA;
    border-radius: 50%;
    right:-0.8rem;
    z-index: -1;
    top:0.9rem;
  }
  .yzgt-bg-circle{
    position: absolute;
    width:2rem;
    height:2rem;
    background: #FAFAFA;
    border-radius: 50%;
    left:-0.8rem;
    z-index: -1;
    top:-0.3rem;
  }
</style>
