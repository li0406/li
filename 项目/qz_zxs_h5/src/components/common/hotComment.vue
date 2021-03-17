<template>
  <div class="hot-comment">
    <div v-if="hasComment">
      <h2 class="caption"><span>热门评论({{num}})</span></h2>
      <slot></slot>
    </div>
    <div v-else class="none-comment">
      <img src="../../assets/img/share/nonecomment.png" alt="">
      <p>当前暂无评论，去APP内抢占沙发>></p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'hotComment',
  props: {
    hasComment: {
      type: Boolean,
      default: false
    },
    num: {
      type: Number,
      default: 0
    }
  }
}
</script>

<style scoped>
  .hot-comment{
    background-color: white;
  }
  .hot-comment .caption{
    color: #333;
    font-size: 0.1365rem;
    border-bottom: 0.01rem solid #F5F5F5;
    line-height: 2;
  }
  .hot-comment .caption > span{
    border-left: 2px solid #FF5353;
    padding-left: 10px;
  }
  .hot-comment .none-comment{
    text-align: center;
    padding: 0.8rem 0;
  }
  .hot-comment .none-comment img{
    display: block;
    margin: 0 auto;
    width: 50%;
  }
  .hot-comment .none-comment p{
    line-height: 4;
  }
</style>
