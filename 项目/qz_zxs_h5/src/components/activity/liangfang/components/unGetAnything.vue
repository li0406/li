<template>
    <div class="unget-box-mask">
        <div class="unget-box">
            <span class="close" @click="handleClose">×</span>
            <img src="../../../../assets/img/activity/liangfang/face-sad.png" alt="" class="face-sad">
            <div class="tips">抱歉！</div>
            <p class="other-tips">您暂未领取任何优惠券，请先<br>领取后查看！</p>
        </div>
    </div>
</template>

<script>
export default {
  name: 'HasGet',
  data () {
    return {
      user: '137*****362'
    }
  },
  methods: {
    handleClose () {
      this.$emit('listenerHideUnGetAction')
    }
  }
}
</script>
<style scope>
.unget-box-mask{
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: rgba(0, 0, 0, 0.5)
}
.unget-box{
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
.unget-box .close{
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
.unget-box .face-sad{
    margin-top: 0.2rem;
    width: 0.4693rem;
    height: 0.4693rem;
}
.unget-box .tips{
    color: #D9AB69;
    font-size: 0.2048rem;
    line-height: 2;
    font-weight: bold;
    padding: 0.1rem 0;
    box-sizing: border-box;
    padding-left: 0.2rem;
}
.unget-box .other-tips{
    color: #E6B188;
}
</style>
