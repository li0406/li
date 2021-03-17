<!--
 * @Author: your name
 * @Date: 2020-06-28 09:58:07
 * @LastEditTime: 2020-07-03 15:44:03
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_u_new\src\views\workers-list\staffIndex.vue
-->
<template>
  <div class="process-page">
    <div class="process">
      <div>
        <!--
        <div class="process-all">
          <div class="shape">
            <div :class="['circular',{'sel-process':flag>=1}]">1</div>
            <div :class="['line',{'sel-process':flag>=1}]"></div>
          </div>
          <div :class="[{'sel-title':flag>=1}]">账号设置</div>
        </div>
        <div class="process-all">
          <div class="shape">
            <div :class="['circular',{'sel-process':flag>=2}]">2</div>
            <div :class="['line',{'sel-process':flag>=3}]"></div>
          </div>
          <div :class="[{'sel-title':flag>=2}]">组织架构</div>
        </div>
        <div class="process-all">
          <div class="shape">
            <div :class="['circular',{'sel-process':flag>=3}]">3</div>
            <div :class="['line',{'sel-process':flag>=4}]"></div>
          </div>
          <div :class="[{'sel-title':flag>=3}]">角色权限</div>
        </div>
        <div class="process-all">
          <div class="shape">
            <div :class="['circular',{'sel-process':flag>=4}]">4</div>
          </div>
          <div :class="[{'sel-title':flag>=4}]">个性化信息</div>
        </div>
        -->
        <el-steps :active="flag" :align-center="true" finish-status="success" space="400px">
          <el-step title="账号设置"></el-step>
          <el-step title="组织架构"></el-step>
          <el-step title="角色权限"></el-step>
          <el-step title="个性化信息"></el-step>
        </el-steps>
      </div>
    </div>
    <div class="process-content">
      <router-view></router-view>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      //  步骤样式标识标识
      flag: 1, //  默认是第一阶段
    };
  },
  watch: {
    $route(to) {
      this.flag = to.meta.flag - 1; //  变换样式标识
    },
  },
  mounted() {
    this.flag = this.$route.meta.flag - 1;
  },
};
</script>
<style scoped>
.process-page {
  background-color: #fff;
}
/* 进度条样式 */
.process {
  border-bottom: 1px solid rgba(219, 219, 219, 1);
}
/*
.process>div{
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1000px;
  height: 110px;
  margin: 0 auto;
  font-size: 15px;
}
*/
.process > div {
  padding: 50px 0;
}
.process-all {
  width: 250px;
}
.shape {
  display: flex;
}
.circular {
  width: 50px;
  height: 50px;
  margin: 0 0 15px 0;
  line-height: 50px;
  text-align: center;
  border: 1px solid #cecece;
  border-radius: 50%;
}
.line {
  width: 200px;
  height: 0;
  margin: 26px 0 0 0;
  border-bottom: 1px solid #cecece;
}
.sel-process {
  color: #fff;
  background-color: #fd6262;
  border-color: #fd6262;
}
.sel-title {
  color: #fd6262;
}
/* 内容 */
.process-content {
  padding: 30px;
}
</style>
<style lang="less">
.process-page {
  /* 步骤条样式修改 */
  .el-step__title.is-success {
    color: #fd6262;
    border-color: #fd6262;
  }
  .el-step__head.is-success {
    color: #fd6262;
    border-color: #fd6262;
  }
}
</style>
