<template>
  <div class="edit-page">
    <div class="top">
      <h2 class="title">{{ title }}</h2>
      <div class="btns fr">
        <slot name="btn" />
      </div>
    </div>
    <slot />
  </div>
</template>

<script>
export default {
  name: '',
  props: {
    title: {
      type: String,
      default: ''
    },
    total: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      currentPage: 1,
      size: 20
    }
  },
  watch: {},
  created() {
  },
  mounted() {},
  methods: {
    handleSizeChange(val) {
      this.$emit('changeData', val)
    },
    handleCurrentChange(val) {
      this.$emit('changeData', val)
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.edit-page{
  .top{
    padding-bottom: 20px;
    border-bottom: 3px solid #ccc;
    margin-bottom: 20px;
  }
  .title{
    font-size: 20px;
    display: inline-block;
  }
}
</style>
