<template>
  <div class="pond-contains">
    <div class="all">
      <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick">
        <!-- 第一个tab -->
        <el-tab-pane label="抢分池" name="first">
          <Rounddetail :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第二个tab -->
        <el-tab-pane label="抢赠池" name="second">
          <Depositdetail :start-tag="activeName" />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
import Rounddetail from './components/round-details.vue';
import Depositdetail from './components/deposit-details.vue';

export default {
  name: 'Finance',
  components: {
    Rounddetail,
    Depositdetail,
  },
  data() {
    return {};
  },
  created() {
    this.activeName = 'first';
    if(this.$route.params.id === 1){
      this.activeName = 'second';
    }
  },
  methods: {
    handleClick() {
      this.currentTag = this.activeName;
    },
  },
};
</script>

<style  lang="less">
.pond-contains {
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
