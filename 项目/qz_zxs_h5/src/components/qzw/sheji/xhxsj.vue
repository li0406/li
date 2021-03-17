<template>
  <section class="sheji-body">
    <div class="top-banner">
      <img src="../../../assets/img/qzw/sheji-bannner.png" />
    </div>
    <div class="sheji-bg">
      <div>
        <img src="../../../assets/img/qzw/banner-new.png" />
      </div>
      <design-order></design-order>
      <div class="sheji-fa">
        <p class="sheji-tit">户型美容 空间规划 让家更实用</p>
        <p class="sheji-des">4套设计方案挑选 做最优选择</p>
        <div class="fa-imgs">
          <img src="../../../assets/img/qzw/pingmian.png" />
          <img src="../../../assets/img/qzw/space.png" />
          <img src="../../../assets/img/qzw/3d.png" />
        </div>
      </div>
      <div class="sheji-design">
        <p class="sheji-tit">专业设计师1对1服务</p>
        <p class="sheji-des">量身定制化 适合的才是好的</p>
        <div class="swiper-container">
          <swiper :options="swiperOption">
            <swiper-slide>
              <div class="desgin-box">
                <div class="design-intro">
                  <img src="../../../assets/img/qzw/design1.png" />
                  <div class="mrl mrt">
                    <span class="name">蒋海林</span>
                    <span class="position">10年设计经验</span>
                  </div>
                  <p class="mrl">擅长打造自然、舒适的生活理念</p>
                </div>
                <div style="overflow: hidden">
                  <div style="margin-bottom: 3px;">
                    <img src="../../../assets/img/qzw/work1.png" />
                  </div>
                  <div>
                    <img class="wid50" src="../../../assets/img/qzw/work2.png" />
                    <img class="wid50" style="float:right;" src="../../../assets/img/qzw/work3.png" />
                  </div>
                </div>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="desgin-box">
                <div class="design-intro">
                  <img src="../../../assets/img/qzw/design2.png" />
                  <div class="mrl mrt">
                    <span class="name">徐静怡</span>
                    <span class="position">8年设计经验</span>
                  </div>
                  <p class="mrl">擅长日式和现代简约风格</p>
                </div>
                <div style="overflow: hidden">
                  <div style="margin-bottom: 3px;">
                    <img src="../../../assets/img/qzw/work4.png" />
                  </div>
                  <div>
                    <img class="wid50" src="../../../assets/img/qzw/work5.png" />
                    <img class="wid50" style="float:right;" src="../../../assets/img/qzw/work6.png" />
                  </div>
                </div>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="desgin-box">
                <div class="design-intro">
                  <img src="../../../assets/img/qzw/design3.png" />
                  <div class="mrl mrt">
                    <span class="name">高阳旭</span>
                    <span class="position">9年设计经验</span>
                  </div>
                  <p class="mrl">擅长空间利用和户型改造</p>
                </div>
                <div style="overflow: hidden">
                  <div style="margin-bottom: 3px;">
                    <img src="../../../assets/img/qzw/work7.png" />
                  </div>
                  <div>
                    <img class="wid50" src="../../../assets/img/qzw/work8.png" />
                    <img class="wid50" style="float:right;" src="../../../assets/img/qzw/work9.png" />
                  </div>
                </div>
              </div>
            </swiper-slide>
          </swiper>
        </div>
      </div>
      <div class="sheji-fuwu">
        <p class="sheji-tit fuwu-zeng">现在申请送3项增值服务</p>
        <div class="fuwu-list">
          <img src="../../../assets/img/qzw/fuwu1.png" />
          <div class="fuwu-des">
            <p>齐装保 保赔偿</p>
            <p>业主签约齐装网推荐的优质装修公司，可免费申请齐装保，出现问题，最高赔付100万。</p>
          </div>
        </div>
        <div class="fuwu-list">
          <img src="../../../assets/img/qzw/fuwu2.png" />
          <div class="fuwu-des">
            <p>30分钟出效果图</p>
            <p>根据你的户型图，通过专业软件，快速看到未来家的装修效果，所见即所得。</p>
          </div>
        </div>
        <div class="fuwu-list">
          <img src="../../../assets/img/qzw/fuwu3.png" />
          <div class="fuwu-des">
            <p>在线查看装修进度</p>
            <p>免费提供装修现场功能，验收每个装修节点，严控施工质量，平台服务 专业 透明 有保障。</p>
          </div>
        </div>
      </div>
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import designOrder from '../sheji/designOrder.vue'
import 'swiper/dist/css/swiper.css'
import { swiper, swiperSlide } from 'vue-awesome-swiper'
export default {
  name: 'xhxsj',
  components: {
    swiper,
    swiperSlide,
    designOrder,
    mTips
  },
  data () {
    return {
      addText: '',
      swiperOption: {
        //移动端轮播
        effect: "side",
        slidesPerView: 1.34,
        centeredSlides: true,
        loopAdditionalSlides: 3,
        loop: true,
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true,//修改swiper的父元素时，自动初始化swiper
      }
    }
  },
  created () {
    setInterval(() => {
      this.callNative()
    }, 200)
  },
  methods: {
    getCityVlaue (city) {
      this.addText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
    },
    callNative () {
      this.$bridge.callNative('UI_onBackClick', '000', function (res) {})
      this.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        nativeCallBack(-1)
      })
    }
  }
}
</script>
<style scoped>
  img{
    width: 100%;
    vertical-align: middle;
  }
  .sheji-bg{
    background: #F3F3F3;
  }
  .sheji-fa{
    background: #fff;
  }
  .sheji-tit{
    font-size: 0.158rem;
    color: #333;
    font-weight: bold;
    text-align: center;
    padding-top: 0.24rem;
    margin-bottom: 8px;
  }
  .sheji-des{
    font-size: 0.13rem;
    color: #999;
    text-align: center;
  }
  .fa-imgs{
    display: flex;
    justify-content: space-around;
    padding: 0 10px;
    margin-top: 0.42rem;
    padding-bottom: 0.24rem;
    border-bottom: 0.12rem solid #F3F3F3;
  }
  .fa-imgs img{
    width: 0.94rem;
    height: 1.197rem;
  }
  .fa-imgs img:nth-child(2){
    position: relative;
    top: -0.21rem;
  }
  .sheji-design{
    background: #fff;
    border-bottom: 0.12rem solid #F3F3F3;
  }
  .design-intro{
    position: relative;
    overflow: hidden;
    margin-bottom: 10px;
  }
  .design-intro img{
    display: inline-block;
    float: left;
    width: 0.513rem;
    height: 0.513rem;
  }
  .design-intro .name{
    font-size: 0.143rem;
    color: #333;
  }
  .design-intro .position{
    padding: 4px 9px;
    border-radius: 10px;
    font-size: 9px;
    color: #fff;
    position: relative;
    top: -3px;
    background: linear-gradient(90deg,rgba(231,191,145,1) 0%,rgba(168,129,85,1) 100%);
  }
  .design-intro p{
    font-size: 0.11rem;
    color: #666;
  }
  .mrl{
    margin-left: 0.65rem;
  }
  .mrt{
    position: relative;
    top: 0.05rem;
    margin-bottom: 0.12rem;
  }
  .swiper-slide{
    width:2.2rem!important;
  }
  .wid50{
    width:49%;
    float: left;
  }


  .swiper-container {
    height: 100%;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    overflow: hidden;
    padding: .12rem 0 .12rem
  }

  .swiper-container .swiper-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 1;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-transition-property: -webkit-transform;
    transition-property: -webkit-transform;
    -o-transition-property: transform;
    transition-property: transform,-webkit-transform;
    -webkit-box-sizing: content-box;
    box-sizing: content-box
  }

  .swiper-container .swiper-wrapper {
    position: relative
  }

  .swiper-container .swiper-wrapper .swiper-slide {
    padding: .1rem;
    transform: scale(.9,.8);
    transition-duration: .5s;
    -webkit-flex-shrink: 0;
    -ms-flex-negative: 0;
    flex-shrink: 0;
    -webkit-box-shadow: .01rem .01rem .12rem 0 rgba(82,91,100,.16);
    box-shadow: .01rem .01rem .12rem .01rem rgba(82,91,100,.16);
    border-radius: .06rem
  }

  .swiper-container .swiper-wrapper .swiper-slide.swiper-slide-active {
    transform: scale(1,1);
    transition-duration: .5s
  }
  .sheji-fuwu{
    background: #fff;
  }
  .sheji-fuwu .fuwu-zeng{
    margin-bottom: 0.22rem;
  }
  .sheji-fuwu .fuwu-list{
    width:92%;
    height: 0.94rem;;
    margin:0 auto;
    margin-bottom: 0.13rem;
    position: relative;
    box-shadow:0px 1px 11px 0px rgba(0, 0, 0, 0.1);
  }
  .fuwu-list img{
    display: block;
    width: 0.645rem;
    height: 0.645rem;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0.156rem;
    margin: auto;
  }
  .fuwu-list .fuwu-des{
    width: 60%;
    height: 100%;
    position: relative;
    left: 1.01rem;
  }
  .fuwu-list .fuwu-des p:nth-child(1){
    color: #333;
    font-size: 0.145rem;
    padding-top: 0.1rem;
    margin-bottom: 0.07rem;
    font-weight: 500;
  }
  .fuwu-list .fuwu-des p:nth-child(2){
    color: #666;
    font-size: 0.118rem;
  }

</style>
