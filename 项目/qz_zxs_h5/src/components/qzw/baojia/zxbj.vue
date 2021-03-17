<template>
  <section>
    <div class="jisuanqi-banner">
      <img src="//staticqn.qizuang.com/custom/20200715/FhSGxK9ZTeZ-v6GcwdelpaGGRoBD" />
    </div>
    <quote-order :formInfo="formInfo"></quote-order>
    <div class="jisuanqi-worry" style="padding-bottom: 0.27rem;">
      <div class="worry-tit" style="width:90%;margin-bottom: 0.213rem">
        <img src="../../../assets/img/qzw/baojia-tit.png" />
      </div>
      <div class="worry">
        <img src="//staticqn.qizuang.com/custom/20200718/FkxxpL6JHwZQRYIvx-gV6_SDCmMt" />
      </div>
    </div>
    <div class="zx-resolve">
      <div class="resolve">
        <img src="../../../assets/img/qzw/tit2.png" />
      </div>
      <p class="worry-result">解决您所有的装修痛点</p>
      <div class="resolve-imgs">
        <ul>
          <li>
            <img src="../../../assets/img/qzw/fan1.png" />
          </li>
          <li>
            <img src="../../../assets/img/qzw/fan2.png" />
          </li>
          <li>
            <img src="../../../assets/img/qzw/fan3.png" />
          </li>
          <li>
            <img src="../../../assets/img/qzw/fan4.png" />
          </li>
          <li>
            <img src="../../../assets/img/qzw/fan5.png" />
          </li>
          <li>
            <img src="../../../assets/img/qzw/fan6.png" />
          </li>
        </ul>
      </div>
    </div>
    <div class="jisuanqi-track">
      <div class="track">
        <img src="../../../assets/img/qzw/tit3.png" />
      </div>
      <p class="track-tit">省心+靠谱+一站式，解决您的装修痛点</p>
      <div class="fanji">
        <img src="../../../assets/img/qzw/fanjz.png" />
      </div>
    </div>
    <div class="fuwu-liucheng">
      <div class="liucheng-img">
        <img src="../../../assets/img/qzw/tit4.png" />
      </div>
      <ul>
        <li>
          <div>
            <img src="../../../assets/img/qzw/icon9.png" />
          </div>
          <p>极速预约</p>
        </li>
        <img class="jiantou" src="../../../assets/img/qzw/jiantou-new.png" />
        <li>
          <div>
            <img src="../../../assets/img/qzw/icon10.png" />
          </div>
          <p>电话沟通</p>
        </li>
        <img class="jiantou" src="../../../assets/img/qzw/jiantou-new.png" />
        <li>
          <div>
            <img src="../../../assets/img/qzw/icon11.png" />
          </div>
          <p>细节确认</p>
        </li>
        <img class="jiantou" src="../../../assets/img/qzw/jiantou-new.png" />
        <li>
          <div>
            <img src="../../../assets/img/qzw/icon12.png" />
          </div>
          <p>上门量房</p>
        </li>
      </ul>
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import quoteOrder from '../baojia/quoteOrder.vue'
export default {
  name: 'baojia',
  components: {
    quoteOrder,
    mTips
  },
  data () {
    return {
      nextStep: false,
      orderId: ''
    }
  },
  created () {
    this.formInfo.source = this.$globalFun.setSource(this.$route.query.source, 'baojia')
  },
  methods: {
    // 回到顶部

  }
}
</script>
<style scoped>
  img{
    width:100%;
    vertical-align: middle;
  }
  .worry-tit{
    margin:0 auto;
    width:75%;
  }
  .worry-result{
    font-size:0.128rem;
    color:#000;
    text-align: center;
    margin:0.08rem 0 0.16rem 0;
  }
  .resolve-imgs{
    margin-bottom: 0.1rem;
  }
  .resolve-imgs ul{
    white-space:nowrap;
    overflow-x: auto;
  }
  ::-webkit-scrollbar {display:none}
  .resolve-imgs ul li{
    display: inline-block;
    width:116px;
    height: 166px;
    margin-right: 10px;
  }
  .resolve-imgs ul li:first-child{
    margin-left: 4%;
  }
  .worry{
    width:92%;
    margin:0 auto;
  }
  .zx-resolve{
    width:100%;
    background: url("../../../assets/img/qzw/resolve_bg.png") no-repeat;
    background-size: 100% 100%;
    padding-bottom: 0.14rem;
  }
  .zx-resolve .resolve{
    width:33%;
    margin:0 auto;
    padding-top:0.18rem;
  }
  .jisuanqi-track{
    background: #F8F8F8;
    padding-bottom: 0.08rem;
  }
  .jisuanqi-track .track{
    width:33%;
    margin:0 auto;
    padding-top:0.21rem;
  }
  .track-tit{
    font-size:0.128rem;
    color:#000;
    text-align: center;
    margin-top:0.08rem;
  }
  .fuwu-liucheng{
    background: #fff;
    padding-bottom: 0.12rem;
  }
  .liucheng-img{
    width:26%;
    margin:0.16rem auto 0.24rem;
  }
  .fuwu-liucheng ul{
    overflow: hidden;
    position: relative;
  }
  .fuwu-liucheng ul li{
    width:25%;
    float: left;
  }
  .fuwu-liucheng ul li div{
    text-align: center;
    margin-bottom: 0.1rem;
  }
  .fuwu-liucheng ul li img{
    width: 0.513rem;
    height: 0.513rem;
  }
  .fuwu-liucheng ul li p{
    font-size:13px;
    font-weight:500;
    color:#000;
    text-align: center;
  }
  .fuwu-liucheng ul img.jiantou{
    width:7px;
    height: 10px;
    position: absolute;
    top: 0.22rem;
  }
</style>
