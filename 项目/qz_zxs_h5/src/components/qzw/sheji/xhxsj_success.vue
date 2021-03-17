<template>
  <section class="success-box">
    <div class="sheji-bg">
      <div class="sheji-box">
        <p class="success-tit">
          <img src="../../../assets/img/qzw/success-icon.png" />
          <span class="get-success">获取成功</span>
        </p>
        <p class="success-des">
          【<span class="heavy">齐装网</span>】给小主请安！小齐已经接到您的装修圣旨，正马不停蹄赶来您的身边，并会在第一时间与您联系，请您注意接听哦，如有疑问请致电：<span class="tele heavy">400-6969-336</span>
        </p>
      </div>
      <div class="sheji-box">
        <div class="erweima-box clearfix">
          <div class="erweima-left">
            <img src="../../../assets/img/qzw/erweima.jpg" alt="" />
          </div>
          <div class="erweima-right">
            <p class="m-hxsj-erweima-tit">齐装公众号</p>
            <ul>
              <li>全面的活动咨询</li>
              <li>经典的装修案例</li>
              <li>专业的客服咨询</li>
            </ul>
          </div>
        </div>
        <p class="hxsj-guanzhu">
          关注方法：长按二维码保存，微信扫一扫点击右上角相册或从相册选取二维码，选择该二维码后关注。
        </p>
      </div>
    </div>
  </section>
</template>
<script>
  import globalData from '../../../utils/globalData.js'
  export default {
    name: 'xhxsj_success',
    data () {
      return {
        tel: ''
      }
    },
    created () {
      this.tel = globalData.tel400
      setInterval(() => {
        this.callNative()
      }, 200)
    },
    methods: {
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
    vertical-align: bottom;
  }
  .success-box{
    width: 100%;
    height: 100%;
  }
  .sheji-bg{
    height: 100%;
    background: #F3F3F3;
    padding: 10px 0.14rem;
  }
  .sheji-box{
    background: #fff;
    border-radius: 6px;
    margin-bottom: 0.14rem;
    overflow: hidden;
  }
  .sheji-box .success-tit{
    text-align: center;
    padding-top: 0.16rem;
    margin-bottom: 0.24rem;
  }
  .sheji-box .success-tit img{
    width: 0.303rem;
    height: 0.303rem;
    margin-right: 5px;
  }
  .sheji-box .success-tit .get-success{
    font-size: 0.2rem;
    font-weight: bold;
    display: inline-block;
    height: 0.303rem;
    line-height: 0.303rem;
  }
  .sheji-bg .heavy{
    font-weight: bold;
  }
  .sheji-box .success-des{
    font-size: 0.116rem;
    text-align: left;
    padding: 0 0.24rem;
    padding-bottom: 0.35rem;
  }
  .success-des .tele{
    color: #FF7D00;
  }
  .m-hxsj-erweima-tit{
    font-size: 0.15rem;
    font-weight: bold;
    color: rgba(51,51,51,1);
    text-align: left;
    margin: 0.1rem 0;
  }
  .erweima-box{
    background: #fff;
    padding-top:0.13rem;
  }
  .erweima-left{
    width:1.111rem;
    float: left;
    margin-left: 0.2rem;
  }
  .erweima-left img{
    width:100%;
  }
  .erweima-right{
    width:50%;
    float: right;
  }
  .erweima-right ul{
    height: 0.62rem;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
  }
  .erweima-right ul li{
    color: #FF7D00;
    font-size: 0.125rem;
  }
  .hxsj-guanzhu{
    padding: 0 0.28rem;
    font-size: 0.8em;
    color: #666;
    margin-top: 0.05rem;
    padding-bottom: 0.28rem;
  }

</style>
