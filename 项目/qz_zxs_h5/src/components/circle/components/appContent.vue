<template>
  <div class="text-box">
    <div :class="['txt', { 'over-hidden': !unfold }]" ref="textBox">
      <span ref="spanBox">{{content}}</span>
    </div>
    <span class="zankai" v-if="ifOver" @click="fulltextFn">展开</span>

  </div>

</template>
<script>
export default {
  name: "text-box",
  data() {
    return {
      ifOver: false, // 文本是否超出三行，默认否
      unfold: false // 文本是否是展开状态 默认为收起
    };
  },
  props: {
    content: {
      type: String,
      default: ""
    }
  },
  mounted() {
    // 判断是否显示展开收起按钮
      this.ifOver = this.$refs.spanBox.offsetHeight > this.$refs.textBox.offsetHeight

  },
  methods: {
    fulltextFn() {
      this.$emit("clickfulltextFn")
    }
  }
};
</script>
<style scoped>
.txt {
  max-height: 0.685rem;
  line-height: 0.239rem;
  font-size: 0.119rem;
  font-weight: 500;
  color: #333;
  margin-bottom: 0.030rem;
}
.over-hidden {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}
.zankai {
  font-size: 0.137rem;
  font-weight: 500;
  color: #5B7A9F;
  margin-bottom: 0.030rem;
}
</style>
