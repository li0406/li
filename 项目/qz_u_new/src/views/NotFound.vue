<template>
  <div class="NotFound-contains">
    <img class="no" src="../assets/404.png" alt />
    <span class="title">当前页面无法打开</span>
  </div>
</template>
<style lang="less" scoped>
.NotFound-contains {
  height: 100%;
  background: #fff;
  .no {
    display: block;
    margin: 0 auto;
    margin-top: 200px;
  }
  .title {
    display: block;
    margin: 0 auto;
    margin-top: 56px;
    color: rgba(144, 147, 153, 1);
    font-weight: 400;
    font-size: 14px;
    text-align: center;
  }
}
</style>
