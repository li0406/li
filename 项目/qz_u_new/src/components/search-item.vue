<template>
  <div class="qz-search-item">
    <label class="label">{{label}}</label>
    <div class="content">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QzSearchItem',
  props: {
    label: String,
  },
};
</script>

<style lang="less" scoped>
.qz-search-item {
  display: inline-block;
  .label {
    display: inline-block;
    margin-right: 8px;
  }
  .content {
    display: inline-block;
    min-width: 200px;
  }
}
</style>
