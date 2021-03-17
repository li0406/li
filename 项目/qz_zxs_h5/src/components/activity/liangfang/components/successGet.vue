<template>
    <div class="success-get-box-mask">
        <div class="success-get-box">
            <span class="close" @click="handleClose">×</span>
            <img src="../../../../assets/img/activity/liangfang/face-happy.png" alt="" class="face-happy">
            <div class="tips">领取成功！</div>
            <p class="other-tips">小齐稍后会与您确认装修需求<br>请留意接听哦~</p>
        </div>
    </div>
</template>

<script>
export default {
  name: 'SuccessGet',
  data () {
    return {
      user: '137*****362',
      timer: null
    }
  },
  created () {
    this.timer = setTimeout(() => {
      this.handleClose()
    }, 2000)
  },
  methods: {
    handleClose () {
      if (this.timer) {
        clearTimeout(this.timer)
        this.timer = null
      }
      this.$emit('listenerHideSuccessGetAction')
    }
  }
}
</script>
<style scope>
.success-get-box-mask{
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: rgba(0, 0, 0, 0.5)
}
.success-get-box{
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
.success-get-box .close{
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
.success-get-box .face-happy{
    margin-top: 0.2rem;
    width: 0.4693rem;
    height: 0.4693rem;
}
.success-get-box .tips{
    color: #FFDC19;
    font-size: 0.2048rem;
    line-height: 2;
    font-weight: bold;
    padding: 0.1rem 0 0.1rem 0.2rem;
    box-sizing: border-box;
}
.success-get-box .other-tips{
    color: #E6B188;
}
</style>
