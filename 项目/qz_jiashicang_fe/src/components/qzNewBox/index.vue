<template>
  <div class="qz-new-box" :class="{'qz-box-border' : border}">
    <div class="flex jus-bet">
      <div class="title"> <slot name="tab">{{ title }}</slot></div>
      <div class="subtitle">{{ subtitle }}</div>
    </div>
    <div>
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
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.qz-new-box{
  position: relative;
  width: 100%;
  // height: 300px;
  overflow: hidden;
  box-sizing: border-box;
  .title{
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
