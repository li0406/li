<!--
 * @Author: your name
 * @Date: 2020-06-22 14:29:22
 * @LastEditTime: 2020-06-29 10:22:20
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_u_new\src\views\workers-list\index.vue
-->
<template>
<div class="workers-list-tab">
  <el-tabs class="tabs" v-model="activeName" @tab-click="handleClick">
    <el-tab-pane label="在职人员管理" name="onthejob">
    <!-- 在职人员管理列表 -->
      <onthejob :activeName=activeName></onthejob>
    <!-- 在职人员管理列表 -->
    </el-tab-pane>
    <el-tab-pane label="已离职人员列表" name="quit">
    <!-- 已离职人员列表 -->
      <quit :activeName=activeName></quit>
    <!-- 已离职人员列表 -->
    </el-tab-pane>
  </el-tabs>
</div>
</template>
<script>
  export default {
    data() {
      return {
        activeName: 'onthejob'
      };
    },
    components:{
      onthejob:()=>import('./components/onthejob-list'),
      quit:()=>import('./components/quit-list')
    },
    methods: {
      //  切换table
      handleClick() {
      }
    }
  };
</script>
<style lang="less">
  .workers-list-tab {
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
