// 基础信息管理
<template>
  <div class="base-contains">
    <div class="all">
      <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick">
        <!-- 第一个tab -->
        <el-tab-pane label="基本资料" name="first">
          <basemain :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第二个tab -->
        <el-tab-pane label="详细资料" name="second">
          <basedetail :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第三个tab -->
        <el-tab-pane label="公司标签" name="third">
          <basecompany :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第四个tab -->
        <el-tab-pane label="企业图片" name="fourth">
          <basepic :start-tag="activeName" />
        </el-tab-pane>
        <!-- 第五个tab -->
        <el-tab-pane label="营业执照审核" name="five">
          <basecheck :start-tag="activeName" />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
import Basemain from './components/base-main.vue';
import Basedetail from './components/base-detail.vue';
import Basecompany from './components/base-company.vue';
import Basepic from './components/base-pic.vue';
import Basecheck from './components/base-check.vue';

export default {
  name: 'Baseinfo',
  components: {
    Basemain,
    Basedetail,
    Basecompany,
    Basepic,
    Basecheck,
  },
  data() {
    return {};
  },
  created() {
    this.activeName = 'first';
  },
  methods: {
    handleClick() {
      this.currentTag = this.activeName;
    },
  },
};
</script>
<style lang="less">
.base-contains {
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
