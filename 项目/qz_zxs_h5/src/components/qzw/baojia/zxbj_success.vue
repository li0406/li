<template>
  <section>
    <div class="jsqsuccess">
      <p class="jsqsuccess-tit">
        <span>装修预算报价为：</span>
        <span class="red">{{result.detail.total}}</span>
        <span>元</span>
      </p>
      <div class="jsqsuccess-result">
        <img src="../../../assets/img/qzw/jsq_success.png" />
        <div class="wsj">
          <p>卫生间：</p>
          <p>
            <span class="red">{{result.detail.child.wsj}}</span>
            <span>元</span>
          </p>
        </div>
        <div class="cf">
          <p>厨房：</p>
          <p>
            <span class="red">{{result.detail.child.cf}}</span>
            <span>元</span>
          </p>
        </div>
        <div class="kt">
          <p>客厅：</p>
          <p>
            <span class="red">{{result.detail.child.kt}}</span>
            <span>元</span>
          </p>
        </div>
        <div class="sd">
          <p>水电：</p>
          <p>
            <span class="red">{{result.detail.child.sd}}</span>
            <span>元</span>
          </p>
        </div>
        <div class="ws">
          <p>卧室：</p>
          <p>
            <span class="red">{{result.detail.child.zw}}</span>
            <span>元</span>
          </p>
        </div>
        <div class="qt">
          <p>其他：</p>
          <p>
            <span class="red">{{result.detail.child.other}}</span>
            <span>元</span>
          </p>
        </div>
      </div>
      <p class="jsqsuccess-tips">*该报价为半包估价，实际以上门量房实测为准</p>
    </div>
    <div class="jsqsuccess-thanks">
      <p class="thanks-tit">恭喜您已成功获得<span class="red">4份装修礼包</span></p>
      <p class="stit">稍后齐装管家将给您回电，请注意接听</p>
      <div class="jsqsuccess-guanzhu">
        <div class="guanzhu-gzh">
          <img src="../../../assets/img/qzw/erweima.jpg" />
          <p>
            <span>关注</span>
            <span class="red">齐装网</span>
            <span>微信公众号</span>
          </p>
          <p>获取更多免费惊喜</p>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
import {baojiadone} from '@/api/api'
export default {
  name: 'baojiaDone',
  data () {
    return {
      result: {
        detail: {
          child: '',
          total: ''
        }
      }
    }
  },
  mounted () {
    let that = this
    console.log(this.$route.query.orderId)
    if (this.$route.query.orderId) {
      baojiadone(this.$route.query.orderId).then((res) => {
        if (res.data.error_code === 0) {
          that.result = res.data.data
          console.log(that.result)
        }
      })
    }
  }
}
</script>
<style scoped>
  .jsqsuccess{
    border-bottom: 0.13rem solid #F8F8F8;
    padding-bottom: 0.2rem;
  }
  .jsqsuccess-tit{
    margin-top:0.2rem;
    text-align: center;
    font-size: 0.16rem;
  }
  .jsqsuccess-tit span{
    font-weight: bold;
  }
  .red{
    color:red;
  }
  .jsqsuccess-result{
    text-align: center;
    position: relative;
  }
  .jsqsuccess-result img{
    width:84%;
    margin-top:0.625rem;
  }
  .jsqsuccess-result>div{
    position:absolute;
    font-size: 0.1rem;
    font-weight: bold;
  }
  .jsqsuccess-result .wsj{
    top: 0.28rem;
    left: 0.66rem
  }
  .jsqsuccess-result .cf{
    top: 0.35rem;
    right: 0.82rem;
  }
  .jsqsuccess-result .kt{
    top: 0.8rem;
    right: 0.3rem;
  }
  .jsqsuccess-result .sd{
    top: 1.43rem;
    right: 0.45rem;
  }
  .jsqsuccess-result .ws{
    top: 1.5rem;
    left: 0.6rem;
  }
  .jsqsuccess-result .qt{
    top: 0.9rem;
    left: 0.3rem;
  }
  .jsqsuccess-tips{
    font-size: 13px;
    color:#999;
    margin-top:0.19rem;
    padding-left: 0.18rem;
  }
  .jsqsuccess-thanks{
    padding:0.2rem 0;
  }
  .thanks-tit{
    font-weight: bold;
    color:#333;
    text-align: center;
    font-size:0.16rem;
    margin-bottom: 0.1rem;
  }
  .stit{
    font-size: 0.12rem;;
    text-align: center;
  }
  .jsqsuccess-guanzhu{
    overflow: hidden;
  }
  .jsqsuccess-guanzhu>div{
    width: 100%;
    float: left;
    text-align: center;
  }
  .jsqsuccess-guanzhu>div img{
    width:1.56rem;
    height: 1.56rem;
    margin-bottom: 0.14rem;
  }
  .jsqsuccess-guanzhu>div p{
    font-size: 0.14rem;
  }
</style>
