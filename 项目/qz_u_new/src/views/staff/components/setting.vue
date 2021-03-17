<template>
  <div class="setting-contains">
    <el-row>
      <p>
        <span>账户名:</span>{{account}}
      </p>
    </el-row>
    <el-row class="backBtn">
      <el-button type="danger" plain @click="goList">返回列表</el-button>
    </el-row>
  </div>
</template>

<script>
export default {
  name: 'Setting',
  data() {
    return {
      account: ''
    };
  },
  created() {
    const account = this.$route.query.account ? this.$route.query.account : '';
    this.account = account;
  },
  methods: {
    // 跳转
    goList() {
      this.$router.push({
        path: '/workers-list',
      });
    },
  },
};
</script>

<style lang="less" scoped>
.setting-contains {
  color: #606266;
  font-weight: 400;
  font-size: 14px;
  line-height: 42px;
  span {
    display: inline-block;
    width: 60px;
    color: #303133;
    font-weight: 500;
    text-align: right;
  }
  .backBtn {
    margin-top: 20px;
  }
}
</style>
