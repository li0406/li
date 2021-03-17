
// 装修案例 tab
<template>
  <div class="decoration-case-tab">
    <el-tabs v-model="activeName" class="tabs">
      <el-tab-pane label="装修案例" name="case">
        <decoration-case></decoration-case>
      </el-tab-pane>
      <el-tab-pane label="3D效果图" name="effect">
        <effect-pic></effect-pic>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import DecorationCase from './decoration-case.vue';
import EffectPic from './effect-pic.vue';

export default {
  created() {
    this.activeName = this.$route.params.tab || 'case';
    if(this.$route.query.active_name){
      this.activeName = this.$route.query.active_name
    }
  },
  name: 'CaseTab',
  components: { DecorationCase, EffectPic },
  data() {
    return {
      activeName: 'first',
    };
  },
  methods: {},
};
</script>

<style lang="less">
.decoration-case-tab {
  height: 100%;
  background: #fff;
  .el-tabs__content {
    padding: 0 30px;
    background: #fff;
  }
  .el-tabs__item {
    font-size: 16px;
  }

  .el-tabs__item.is-active {
    color: red;
    font-weight: bold;
  }

  .el-tabs__active-bar {
    background-color: red;
  }

  .el-tabs__item:hover {
    color: red;
  }

  .tabs {
    height: 60px;
    line-height: 60px;
  }

  .el-tabs__nav-wrap {
    padding: 0 30px;
  }
}
</style>
