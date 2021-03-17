<template>
    <div class="my-gift-package">
        <div class="discount-box">
            <img src="http://staticqn.qizuang.com/custom/20190923/FoND5Ne6PaVXDaLFe8XU9Y89Dzk-" alt="" class="title">
            <ul class="alldiscount">
                <li class="one">
                    <div class="right-content top01">
                        <div class="discount-desc-box">
                            <p class="discount-title">免费提供空气治理方案 7折优惠券  </p>
                            <p class="discount-desc lh3">治理券500元（满5000使用）</p>
                        </div>
                    </div>
                </li>
                <li class="two">
                    <div class="right-content top01">
                        <div class="discount-desc-box">
                            <p class="discount-title">100元礼品 <span class="font-fix">(9月21日到店领取)</span></p>
                            <p class="discount-title">500元提货卡<span class="font-fix">(满3000使用)</span></p>
                        </div>
                    </div>
                </li>
                <li class="three">
                    <div class="right-content top01">
                        <div class="discount-desc-box">
                            <p class="discount-title">追觅无限吸尘器1台</p>
                            <p class="discount-desc">价值1399元</p>
                            <p class="discount-title">400元优惠券<span class="font-fix">(9.9-12.31)</span></p>
                        </div>
                    </div>
                </li>
                <li class="four">
                    <div class="right-content">
                        <div class="discount-desc-box" style="top: 0.2rem;">
                            <p class="discount-title">空气治理券</p>
                            <p class="discount-desc">抵用券500元 (满3500元使用）<br>抵用券1000元 (满6000元使用）</p>
                        </div>
                    </div>
                </li>
                <li class="five">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-title">机票抵用券</p>
                            <p class="discount-desc">10元无门槛国内机票抵扣券</p>
                            <p class="discount-desc">20元无门槛国际机票抵用券</p>
                        </div>
                    </div>
                </li>
                <li class="six">
                    <div class="right-content" >
                        <div class="discount-desc-box">
                            <p class="discount-title">首刷赠送艺伙APP会员</p>
                            <p class="discount-desc lh3">一年会员价值365元</p>
                        </div>
                    </div>
                </li>
                <li class="seven">
                    <div class="right-content" >
                        <div class="discount-desc-box">
                            <p class="discount-title">1000元优惠券</p>
                            <p class="discount-desc lh3">下单立享9.6折</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="dercoation-line">
            <div class="top"></div>
            <div class="bottom"></div>
        </div>
        <div class="use-intro">
            <p class="title">使用说明：</p>
            <ul class="clause">
                <li>1. 该券使用前，请先添加齐装客服微信：<span style="color: #FEE7D3;">{{wechat}}</span>
                <img src="../../../assets/img/activity/liangfang/fuzhi.png" alt="" @click='copyWechat' class="icon-copy">
                </li>
                <li>2. 使用时请向商家出示此页面或短信凭证</li>
                <li>3. 有效期：即日起至2019-12-31 <br> <span style="color: #D4B290;">（居然之家：即日起至2019-10-08）</span></li>
            </ul>
        </div>
        <m-tips ref="tips"/>
    </div>
</template>
<script>
import mTips from '../../common/mTips.vue'
export default {
  name: 'MyGiftPackage',
  data () {
    return {
      wechat: '155-0137-0380',
      endCall: false
    }
  },
  components: {
    mTips
  },
  beforeRouteUpdate (to, from, next) {
    window.scrollTo(0, 0)
    next()
  },
  beforeRouteEnter (to, from, next) {
    window.scrollTo(0, 0)
    next()
  },
  created () {
    this.changeHeader()
    window.scrollTo(0, 0)
  },
  methods: {
    changeHeader () {
      if (!this.endCall) {
        setTimeout(() => {
          this.changeHeader()
        }, 200)
      }
    },
    copyWechat () {
      this.$copyText(this.wechat).then((e) => {
        this.$refs.tips.tipsFadeIn({
          text: '复制成功'
        })
      }, (e) => {
        this.$refs.tips.tipsFadeIn({
          text: '您的系统不支持该功能'
        })
      })
    }
  }
}
</script>
<style scoped>
.my-gift-package{
    background-color: #890B15;
}
.my-gift-package .lh3{
    line-height: 3;
}
.my-gift-package .discount-box{
    padding-top: 0.15rem;
}
.my-gift-package .discount-box .title{
    display: block;
    width: 2.005rem;
    height: 0.4736rem;
    margin: 0 auto;
}
.my-gift-package .discount-box .alldiscount{
    margin-top: 0.05rem;
}
.my-gift-package .discount-box .alldiscount > li{
    position: relative;
    height: 1.2288rem;
    background: url('http://staticqn.qizuang.com/custom/20190923/FtSivT2xwrkuNOLbx25jKshdMHmd');
    background-size: 100% auto;
    margin-top: 0.06rem;
}
.my-gift-package .discount-box .alldiscount > li.two{
    background: url('http://staticqn.qizuang.com/custom/20190923/FpQwtphyVVh_reqP5S7aVhWo3Uv7');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount > li.three{
    background: url('http://staticqn.qizuang.com/custom/20190923/FsqmL7k7vi9CCdRHZJMjoDLQAgjh');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount > li.four{
    background: url('http://staticqn.qizuang.com/custom/20190923/Ft5FkcnKwVlG1b039405fhx9BuvX');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount > li.five{
    background: url('http://staticqn.qizuang.com/custom/20190923/FpPbHt4pt4vA2D4zyPaa_z1TmnTU');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount > li.six{
    background: url('http://staticqn.qizuang.com/custom/20190923/Fq75dtSlXbY_q_vyT-eXd4l_OEvc');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount > li.seven{
    background: url('http://staticqn.qizuang.com/custom/20190923/Fjn1CJPCcGyY0JDssjWOnPPvpnD-');
    background-size: 100% auto;
}
.my-gift-package .discount-box .alldiscount .right-content{
    position: absolute;
    width: 1.8rem;
    top: 0.2346rem;
    left: 1.28rem;
    height: 0.8rem;
    display: table;
    vertical-align: middle;
}
.my-gift-package .discount-box .alldiscount .discount-desc-box{
    font-size: 0.1109rem;
    color: #F84D10;
    /* height: 0.512rem; */
    height: 100%;
    display: table-cell;
    vertical-align: middle;
}
.my-gift-package .discount-box .alldiscount .discount-desc-box .discount-title{
    color: #F84D10;
    /* font-size: 0.1536rem; */
    font-size: 0.13rem;
    font-weight: bold;
}
.my-gift-package .discount-box .alldiscount .discount-desc-box .font-fix{
    color: #333333;
    /* font-size: 0.128rem; */
    font-size: 0.12rem;
    font-weight: normal;
}
.my-gift-package .discount-box .alldiscount .discount-desc-box .discount-desc{
    color: #333333;
    /* font-size: 0.128rem; */
    font-size: 0.12rem;
}

.dercoation-line{
    width: 2.973rem;
    margin: 0.2rem auto;
}
.dercoation-line .top{
    height: 2px;
    background-color: #4D0A10;
}
.dercoation-line .bottom{
    height: 2px;
    background-color: #C63945;
}

.my-gift-package .use-intro{
    width: 3.0037rem;
    margin: 0 auto;
    color: #FFE9D4;
    font-size: 0.128rem;
    padding-bottom: 0.3rem;
}
.my-gift-package .use-intro .title{
    font-size: 0.128rem;
    font-weight: bold;
}
.my-gift-package .use-intro .clause{
    line-height: 2;
    margin-top: 0.05rem;
    font-size: 0.1024rem;
    color: #D4B290;
}
.my-gift-package .use-intro  .icon-copy{
    width: 0.128rem;
    height: 0.128rem;
    vertical-align: middle;
}
@media screen and (min-width: 320px) {
    .top01 {
        /* top: 0.2546rem !important; */
    }
}
</style>
