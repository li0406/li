<template>
  <section>
    <div class="banner-box">
      <img src="../../assets/img/share/share_banner_1.jpg" class="hide-img">
      <img src="../../assets/img/share/share_banner_1.jpg" :class="imgChange[0] ? 'show' : 'hide'">
      <img src="../../assets/img/share/share_banner_2.jpg" :class="imgChange[1] ? 'show' : 'hide'">
      <img src="../../assets/img/share/share_banner_3.jpg" :class="imgChange[2] ? 'show' : 'hide'">
      <div class="banner-text-box">
        <div class="img-tr">
          <img src="../../assets/img/share/logo.png" alt="">
        </div>
      </div>
      <div class="bottom-text">
        <span>分享</span>
        <span>灵感</span>
        <span>锦囊</span>
      </div>
    </div>
    <div class="body-item">
      <div class="item-title">美家分享</div>
      <div class="item-sm-title">
        <span>数千经验</span>
        <span>真实分享</span>
      </div>
      <div class="item-img">
        <img src="../../assets/img/share/meijia.png" alt="">
        <span class="small-radius left anli"><p>案例</p></span>
        <span class="small-radius right huati"><p>互动</p></span>
        <span class="small-radius left xinde"><p>心得</p></span>
      </div>
    </div>

    <div class="body-item">
      <p class="body-item-line"></p>
      <div class="item-title">避坑指南</div>
      <div class="item-sm-title">
        <span>专业指南</span>
        <span>避坑秘籍</span>
      </div>
      <div class="item-img">
        <img src="../../assets/img/share/bikeng.png" alt="">
        <span class="small-radius right bikeng"><p>避坑</p></span>
        <span class="small-radius left jingyan"><p>经验</p></span>
        <span class="small-radius right jiqiao"><p>技巧</p></span>
      </div>
    </div>

    <div class="body-item">
      <p class="body-item-line"></p>
      <div class="item-title">预算规划</div>
      <div class="item-sm-title">
        <span>预算把控</span>
        <span>快速省钱</span>
      </div>
      <div class="item-img">
        <img src="../../assets/img/share/yusuan.png" alt="">
        <span class="small-radius left yusuan"><p>预算</p></span>
        <span class="small-radius right shengqian"><p>省钱</p></span>
        <span class="small-radius left kexue"><p>科学</p></span>
      </div>
    </div>

    <div class="body-item">
      <p class="body-item-line"></p>
      <div class="item-title">选材榜单</div>
      <div class="item-sm-title">
        <span>口碑排名</span>
        <span>专业解读</span>
      </div>
      <div class="item-img">
        <img src="../../assets/img/share/xuancai.png" alt="">
        <span class="small-radius left paihang"><p>排行</p></span>
        <span class="small-radius right pinpai"><p>品牌</p></span>
        <span class="small-radius right koubei"><p>口碑</p></span>
      </div>
    </div>
    <p class="body-item-line"></p>
    <div class="join-us">
      <p>想要拥有一个美丽温馨的“家”，就来</p>
      <p>参与我们吧！</p>
    </div>
    <br>
    <br>
  </section>
</template>
<script>
export default {
  name: 'shareBanner',
  data () {
    return {
      name: '',
      imgChange: [1, 0, 0]
    }
  },
  mounted () {
    this.changeImg(0)
    let UA = window.navigator.userAgent
    let isAndroid = /android/i.test(UA)
    let isIPhone = /iphone/i.test(UA)
    if (isAndroid) {
      this.phoneModel = '0'
    }
    if (isIPhone) {
      this.phoneModel = '1'
    }
  },
  methods: {
    changeImg (index) {
      let that = this
      index = index % 3
      for (let i = 0; i < this.imgChange.length; i++) {
        if (i === index) {
          this.imgChange.splice(i, 1, 1)
        } else {
          this.imgChange.splice(i, 1, 0)
        }
      }
      index++
      setTimeout(function () {
        that.changeImg(index)
      }, 3000)
    }
  }
}
</script>
<style scoped>
  a{
    display: block;
    width:100%;
    height: 100%;
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
  }
  .banner-box{
    position:relative;
    width:100%;
  }
  .banner-box .hide {
    position: absolute;
    top:0px;
    left:0px;
    width:100%;
    transition: all 1s;
    -moz-transition: all 1s;
    -webkit-transition: all 1s;
    -o-transition: all 1s;
    opacity: 0;
  }
  .show{
    opacity: 1 !important;
    position: absolute;
    left:0px;
    top:0px;
    width:100%;
     transition: all 1s;
    -moz-transition: all 1s;
    -webkit-transition: all 1s;
    -o-transition: all 1s;
    opacity: 0;
  }
  .hide-img{
    opacity: 0;
    width:100%;
    position:relative !important;
  }
  .banner-text-box{
    position:absolute;
    z-index: 999;
    top:0px;
    width:100%;
    left:0px;
    text-align: center;
    height: 3.6rem;
    display: table;
  }
  .img-tr{
    display: table-cell;
    vertical-align: middle;
  }
  .img-tr img{
    width:48%;
  }
  .bottom-text{
    position:absolute;
    z-index: 999;
    top:3.8rem;
    color:#fff;
    font-size: 0.25rem;
    width:82%;
    text-align: center;
    display: flex;
    margin: auto;
    left:0px; right:0px;
  }
  .bottom-text span{
    flex-grow: 1
  }
  .radius-white {
    border-radius: 0.2rem;
    border:1px solid #fff;
    color:#fff;
    margin:auto;
    left:0px; right:0px;
    position:absolute;
    bottom:0.28rem;
    width:38%;
    padding:0.1rem 0px;
    text-align: center;
    display: table;
  }
  .black-radius{
    border-radius: 0.2rem;
    border:1px solid #333;
    color:#333;
    margin:0.2rem auto;
    width:38%;
    padding:0.1rem 0px;
    text-align: center;
    display: table;
  }
  .body-item-line {
    width:80%;
    margin:0px auto;
    border-top:1px solid #EEEEEE;

  }
  .radius-white p,.black-radius p{
    display: table-cell;
    vertical-align: middle;
  }
  .body-item{
    text-align:center;
    color:#333;
    padding-bottom: 0.2rem;
  }
  .item-title{
    padding-top: 0.26rem;
    padding-bottom: 0.05rem;
    font-size: 0.21rem;
  }
  .item-sm-title{
    font-size: 0.16rem;
    padding-bottom: 0.04rem;
  }
  .item-sm-title span{
    padding:0px 0.05rem;
  }
  .item-img img{
    width:100%;
  }
  .item-img {
    position:relative;
  }
  .small-radius{
    position:absolute;
    width:0.5rem;
    padding:0.13rem 0px;
    border-radius: 50%;
    box-shadow: 0px 0px 20px #ccc;
    font-size: 0.16rem;
    background:#fff;
    display:table;
  }
  .small-radius.left {
    left:0.17rem;
  }
  .small-radius.right {
    right:0.17rem;
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

  .anli{ top:0.5rem; }
  .xinde{bottom:0.4rem;}
  .huati{top:0.8rem;}
  .jingyan{bottom:1.2rem;}
  .bikeng{top:0.24rem;}
  .jiqiao{bottom:0.4rem;}
  .yusuan{top:0.7rem;}
  .kexue{bottom:0.5rem;}
  .shengqian{bottom:0.75rem;}
  .paihang{top:1.38rem;}
  .pinpai{top:0.5rem;}
  .koubei{bottom:0.48rem;}
  .join-us{
    text-align: center;
    line-height: 0.2rem;
    padding-top:0.2rem;
    font-size: 0.14rem;
  }
  .bottom-fixed{
    position: fixed;
    bottom:0px;
    left: 0px;
    display: table;
  }
</style>
