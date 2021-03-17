<template>
    <div class="has-get-box-mask">
        <div class="has-get-box">
            <span class="close" @click="handleClose">×</span>
            <img src="../../../../assets/img/activity/liangfang/face-naughty.png" alt="" class="face-naughty">
            <div class="tips">温馨提示：</div>
            <p class="other-tips">您已领取过优惠券啦~</p>
            <button type="button" class="confirm-get" @click='checkNow'>立即查看</button>
        </div>
    </div>
</template>

<script>
export default {
  name: 'HasGet',
  data () {
    return {}
  },
  methods: {
    handleClose () {
      this.$emit('listenerHideHasGetAction')
    },
    checkNow () {
      this.handleClose()
      this.$router.push({
        path: 'mygiftpackage'
      })
    }
  }
}
</script>
<style scope>
.has-get-box-mask{
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: rgba(0, 0, 0, 0.5)
}
.has-get-box{
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto;
    background: url('http://staticqn.qizuang.com/custom/20190923/FkQ3NQmPaR_rwL874EC4EIMiWP1T') no-repeat;
    background-size: 100% auto;
    width: 2.679rem;
    height: 2.167rem;
    text-align: center;
    box-sizing: border-box;
    padding:0.15rem;
}
.has-get-box .close{
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
.has-get-box .tips{
    color: #FFDC19;
    font-size: 0.2048rem;
    line-height: 1;
    font-weight: bold;
    padding: 0.1rem 0;
    box-sizing: border-box;
    padding-left: 0.2rem;
}
.has-get-box .face-naughty{
    margin-top: 0.2rem;
    width: 0.4693rem;
    height: 0.4693rem;
}
.has-get-box .confirm-get{
    background: url('http://staticqn.qizuang.com/custom/20190923/Fh2EFowcER1_V0_F-YkvQ_tLpuu8') no-repeat;
    background-size: 100% auto;
    width: 2.048rem;
    height: 0.5034rem;
    line-height: 0.4rem;
    font-size: 0.1536rem;
    color: white;
    margin-top: 0.05rem;
}
.has-get-box .other-tips{
    color: #E6B188;
}
</style>
