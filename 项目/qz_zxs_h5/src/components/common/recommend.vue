<template>
  <div class="recommend">
    <h2 class="caption"><span>相关推荐</span></h2>
    <slot></slot>
    <down-btn></down-btn>
  </div>
</template>

<script>
import downBtn from '../common/downBtn'
export default {
  name: 'recommend',
  components: {
    downBtn
  }
}
</script>

<style scoped>
  .recommend{
    background-color: white;
  }
  .recommend > .caption{
    font-weight: bold;
    color: #333;
    font-size: 0.1536rem;
    padding: 0 15px;
    border-bottom: 0.01rem solid #F5F5F5;
    line-height: 2;
  }
  .recommend > .caption > span{
    border-left: 3px solid #FF5353;
    padding-left: 10px;
  }
</style>
