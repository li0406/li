<template>
  <div class="table-seatch">
    <h2 class="title">{{ title }}</h2>
    <div class="from">
      <slot name="from" />
    </div>
    <div class="table">
      <slot name="table" />
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
<style rel=" stylesheet/scss" lang="scss" scoped>
.table-seatch{
  .title{
    font-size: 20px;
    padding-bottom: 20px;
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
