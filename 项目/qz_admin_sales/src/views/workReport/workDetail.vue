<template>
  <div v-loading="loading" class="work-detail">
    <div class="main">
      <p>汇报日期：{{ reportInfo.detail.date }}</p>
      <p>销售人员：{{ reportInfo.adminuser.name }}</p>
      <p>职&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;位：{{ reportInfo.adminuser.role_name }}</p>
      <p>负责城市：{{ reportInfo.adminuser.citys }}</p>
    </div>
    <div class="main">
      <p>工作内容：</p>
      <div>{{ reportInfo.detail.content }}</div>
    </div>
    <div class="main">
      <p>遇到问题：</p>
      <div>{{ reportInfo.detail.question }}</div>
    </div>
    <div class="main">
      <p>解决方案：</p>
      <div>{{ reportInfo.detail.solution }}</div>
    </div>
    <div class="main">
      <p>明日计划：</p>
      <div>{{ reportInfo.detail.plan }}</div>
    </div>
    <div class="main">
      <p>需要协助：</p>
      <div>{{ reportInfo.detail.assist }}</div>
    </div>
    <div class="main">
      <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px" class="demo-ruleForm">
        <el-form-item label="回复：" prop="reply">
          <el-input
            v-model="ruleForm.reply"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
      </el-form>
      <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
    </div>
  </div>
</template>

<script>
import { fetchReportInfo, fetchAddReply } from '@/api/workReport'
export default {
  name: 'WorkDetail',
  data() {
    return {
      id: '',
      reportInfo: {
        detail: {},
        adminuser: {}
      },
      loading: false,
      ruleForm: {
        reply: ''
      },
      rules: {
        reply: [
          { required: true, message: '请输入内容', trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.id = this.$route.params.id
    this.fetchReportInfo()
  },
  methods: {
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.fetchAdd()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    fetchReportInfo() {
      this.loading = true
      fetchReportInfo({
        id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const data = res.data.data
          this.loading = false
          this.reportInfo.detail = data.detail
          this.reportInfo.adminuser = data.adminuser
        }
      })
    },
    fetchAdd() {
      this.loading = true
      fetchAddReply({
        id: this.id,
        reply_text: this.ruleForm.reply
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '添加成功!'
          })
          history.go(-1)
        } else {
          this.$message.error(res.data.error_msg)
          this.loading = false
        }
      }).catch(res => {
        this.$message({
          message: '保存失败，请重新保存',
          type: 'error',
          duration: 5 * 1000
        })
        this.loading = false
      })
    }
  }
}
</script>

<style scoped>
  .work-detail {
    padding: 20px;
    box-sizing: border-box;
  }
  .main {
    padding: 20px;
    margin-bottom: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
  }
  .demo-ruleForm{
    width: 800px;
  }
  .mb20{
    margin-bottom: 20px;
  }
</style>
