<template>
  <div class="qz-card" :class="bordered?'has-bordered':null">
    <div class="header" :class="headerBackgroundColor?'has-background-color':null">
      <span class="title">{{title}}</span>
      <span class="extra">{{extra}}</span>
      <span class="more">
        <slot name="more"></slot>
      </span>
      <p class="info">{{info}}</p>
    </div>
    <div class="divider" v-if="divider"></div>
    <div class="content">
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QzCard',
  props: {
    title: String,
    extra: String,
    info: String,
    divider: Boolean,
    bordered: Boolean,
    headerBackgroundColor: Boolean,
  },
};
</script>

<style lang="less" scoped>
@import '../styles/index.less';

.qz-card {
  display: block;
  width: 100%;
  min-width: 150px;
  margin-bottom: 10px;
  background-color: white;
  border-radius: 2px;
  .has-bordered {
    border: 1px solid #e3e3e3;
  }
  .has-background-color {
    background-color: #fafafa;
  }
  .header {
    padding: 30px 30px 0;
    .more {
      float: right;
      margin-top: -16px;
    }
    .title {
      color: @qz-card-title-font-color;
      font-size: @qz-card-title-font-size;
      line-height: 1.5715;
    }
    .extra {
      margin-left: 8px;
      color: #ffa81e;
      font-size: 14px;
      line-height: 1.5715;
    }
    .info {
      margin-bottom: 0;
      color: #ffa81e;
      font-size: 14px;
      line-height: 1.5715;
    }
  }
  .divider {
    display: block;
    clear: both;
    box-sizing: border-box;
    width: 100%;
    height: 1px;
    margin: 10px 0;
    padding: 0;
    background-color: @qz-card-divider-color;
  }
  .content {
    min-height: 64px;
    padding: 30px;
    overflow: hidden;
    font-size: 14px;
    line-height: 1.5715;
  }
}
</style>
