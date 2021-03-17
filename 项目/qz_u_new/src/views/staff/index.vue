
// 基础信息管理
<template>
  <div class="staff-contains">
    <div class="all">
      <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick">
        <!-- 第一个tab -->
        <el-tab-pane label="员工基本信息" name="first">
          <message :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第二个tab -->
        <el-tab-pane label="账号设置" name="second">
          <setting :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第三个tab -->
        <el-tab-pane label="组织架构" name="third">
          <chart :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第四个tab -->
        <el-tab-pane label="角色权限" name="fourth">
          <limits :start-tag="activeName" />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
import Message from './components/message.vue';
import Setting from './components/setting.vue';
import Chart from './components/chart.vue';
import Limits from './components/limits.vue';

export default {
  name: 'Baseinfo',
  components: {
    Message,
    Setting,
    Chart,
    Limits,
  },
  data() {
    return {};
  },
  created() {
    this.activeName = this.$route.query.grade ? this.$route.query.grade :'first';
  },
  methods: {
    handleClick() {
      this.currentTag = this.activeName;
    },
  },
};
</script>
<style lang="less">
.staff-contains {
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
