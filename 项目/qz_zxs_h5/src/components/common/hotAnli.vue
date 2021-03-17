<template>
  <div class="anli">
    <h2 class="caption"><span>热门案例</span></h2>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: 'hotAnli'
}
</script>

<style scoped>
  .anli{
    background-color: white;
  }
  .anli > .caption{
    font-weight: bold;
    color: #333;
    font-size: 0.1536rem;
    padding: 0 15px;
    border-bottom: 0.01rem solid #F5F5F5;
    line-height: 2;
  }
  .anli > .caption > span{
    border-left: 3px solid #FF5353;
    padding-left: 10px;
  }
</style>
