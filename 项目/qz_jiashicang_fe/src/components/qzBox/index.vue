<template>
  <div class="qz-box" :class="{'qz-box-border' : border}">
    <div class="flex jus-bet">
      <div class="title"> <slot name="tab">{{ title }}</slot></div>
      <div class="subtitle">{{ subtitle }}</div>
    </div>
    <div class="center">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: {
    title: {
      type: String,
      default: ''
    },
    border: { // 1为有边框 0 为无边框
      type: Number,
      default: 1
    },
    subtitle: {
      type: String,
      default: ''
    },
    tabNum: {
      type: Number,
      default: 0
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.qz-box{
  width: 100%;
  // height: 300px;
  overflow: hidden;
  box-sizing: border-box;
  .title{
    background: url("~@/assets/imgs/arrow.png") no-repeat;
    background-size: 47px 21px;
    background-position: 0 center;
    padding-left: 54px;
    color: #fff;
    font-size: 20px;
    line-height: 46px;
  }
  .center{
    text-align: center;
  }
  .subtitle{
    color: #04E3FF;
    font-size: 14px;
    margin-top: 14px;
  }
}
.qz-box-border{
  box-shadow: inset 0 0 11px #04E3FF;
  padding: 20px;
}
</style>
