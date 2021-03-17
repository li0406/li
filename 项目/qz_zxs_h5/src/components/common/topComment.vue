<template>
  <div class="top-comment">
    <h2 class="caption"><span>热门评论（{{commentNum}}）</span></h2>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: 'topComment',
  props: ['commentNum']
}
</script>

<style scoped>
  .top-comment{
    background-color: white;
  }
  .top-comment > .caption{
    font-weight: bold;
    color: #333;
    font-size: 0.1536rem;
    padding: 0 15px;
    line-height: 2;
  }
  .top-comment > .caption > span{
    border-left: 3px solid #FF5353;
    padding-left: 10px;
  }
</style>
