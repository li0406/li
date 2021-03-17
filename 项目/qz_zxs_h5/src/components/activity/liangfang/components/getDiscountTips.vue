<template>
    <div class="get-discount-box-mask">
        <div class="get-discount-box">
            <span class="close" @click="handleClose('only')">×</span>
            <p class="title">是否以<span class="username">{{userPhone}}</span>领取此优惠</p>
            <div class="tips">领取后，我们会把优惠<br>以短信形式发送至此手机号</div>
            <button type="button" class="confirm-get" @click='handleConfirmGetDiscount'>确认领取</button>
            <div class="disclaimer">
                <span class="smiudiscla" @click='handleAceptDisclaimer'><i class="hook" v-if='acceptDisclaimer'></i></span>
                接受齐装网推荐装修公司服务
            </div>
        </div>
        <m-tips ref="tips"/>
    </div>
</template>
<script>
import { getDiscountAction, submitLiangFangPic } from '@/api/activityApi.js'
import mTips from '../../../common/mTips.vue'
export default {
  name: 'GetDiscount',
  props: {
    userPhone: {
      type: String,
      default: ''
    }
  },
  components: {
    mTips
  },
  data () {
    return {
      acceptDisclaimer: true
    }
  },
  methods: {
    handleClose (flag) {
      this.$emit('listenerHideAction', flag)
    },
    handleConfirmGetDiscount () {
      if (this.acceptDisclaimer) {
        getDiscountAction({
          source: 19092351
        }).then(res => {
          if (parseInt(res.data.error_code) === 0) {
            submitLiangFangPic({
              img_path: ''
            }).then(res => {
              if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.handleClose()
              }
            })
          } else {
            alert('领取失败 ' + res.data.error_msg)
          }
        }).catch(res => {
          alert('网络异常，请稍后再试')
        })
      } else {
        this.$refs.tips.tipsFadeIn({
          text: '请先接受齐装网推荐装修公司服务'
        })
      }
    },
    handleAceptDisclaimer () {
      this.acceptDisclaimer = !this.acceptDisclaimer
    }
  }
}
</script>
<style scope>
.get-discount-box-mask{
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: rgba(0, 0, 0, 0.5)
}
.get-discount-box{
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    background: url('http://staticqn.qizuang.com/custom/20190923/FkQ3NQmPaR_rwL874EC4EIMiWP1T') no-repeat;
    background-size: 100% auto;
    width: 2.807rem;
    height: 2.2954rem;
    text-align: center;
    box-sizing: border-box;
    padding:0.15rem;
}
.get-discount-box .close{
    width: 0.3rem;
    height: 0.3rem;
    line-height: 0.25rem;
    border-radius: 50%;
    position: absolute;
    top: -0.4rem;
    right: 0;
    text-align: center;
    background-color: rgba(0, 0, 0, 0.3);
    color: #FFEADA;
    font-size: 0.25rem;
}
.get-discount-box .title{
    color: #D9AB69;
    line-height: 3;
    font-size: 0.1536rem;
}
.get-discount-box .title .username{
    color: #FEF629;
}
.get-discount-box .tips{
    color: #E6B188;
    font-size: 0.128rem;
    line-height: 2;
    padding: 0.1rem 0;
}
.get-discount-box .confirm-get{
    background: url('http://staticqn.qizuang.com/custom/20190923/Fh2EFowcER1_V0_F-YkvQ_tLpuu8') no-repeat;
    background-size: 100% auto;
    width: 2.048rem;
    height: 0.5034rem;
    line-height: 0.4rem;
    font-size: 0.1536rem;
    color: white;
}
.get-discount-box .disclaimer{
    color: #E6B188;
}
.get-discount-box .disclaimer .smiudiscla{
    display: inline-block;
    width: 0.1rem;
    height: 0.1rem;
    border:1px solid #E6B188;
    position: relative;
}
.get-discount-box .disclaimer .smiudiscla .hook::after{
    content: '√';
    position: absolute;
    left: 0;
    top: -0.03rem;
    color: #FEF629;
}
</style>
