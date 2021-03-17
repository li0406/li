<template>
  <div class="member-repoort-detail">
    <div class="qian-main">
      <div class="btitle">报备详情 <el-button class="ml30" @click="handleBack">返回</el-button></div>
      <detail />
    </div>
  </div>
</template>

<script>
import detail from './components/detail'
export default {
  name: 'ReportDetail',
  components: {
    detail
  },
  data() {
    return {
      id: '',
      type: ''
    }
  },
  created() {
    this.id = this.$route.query.id
    this.type = this.$route.query.type
  },
  methods: {
    handleBack() {
      this.$router.push({
        path: '/smallReport/list'
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.member-repoort-detail {
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
  .qian-main {
    padding: 20px 50px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
    .btitle {
      margin-bottom: 20px;
      font-weight: bold;
      .ml30{
        margin-left: 30px;
      }
    }
  }
}
</style>
