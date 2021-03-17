<template>
  <div class="dashboard-editor-container">
    <img :src="emptyGif" class="emptyGif">
  </div>
</template>

<script>
export default {
  name: 'DashboardAdmin',
  data() {
    return {
      emptyGif: 'http://staticqn.qizuang.com//custom/20190806/FmO2CGYPOjbHzt0mx_R-0xCUNZnA'
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  text-align: center;
  background-color: #e3e3e3;
}
</style>
