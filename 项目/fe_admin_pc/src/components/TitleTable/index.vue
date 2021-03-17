<template>
  <div class="title-table">
    <h3 class="title">
      <i :class="iconName" class="mr5" />
      <span>{{ title }}</span>
    </h3>
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
    iconName: {
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
.title-table{
  .title{
    font-size: 18px;
    padding-bottom: 20px;
    font-weight: normal;
  }
  .from{
    padding: 20px 20px 0;
    border-top: 3px solid #999;
    background: #fff;
  }
  .table{
    padding: 20px;
    border-top: 3px solid #999;
    background: #fff;
  }
}
</style>
