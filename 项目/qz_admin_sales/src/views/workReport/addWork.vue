<template>
  <div class="add-work">
    <div v-loading="loading" class="main">
      <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px" class="demo-ruleForm">
        <el-form-item label="日期：" prop="dailyDateVal">
          <el-date-picker
            v-model="ruleForm.dailyDateVal"
            class="mb20"
            type="date"
            placeholder="选择日期"
            value-format="yyyy-MM-dd"
          />
        </el-form-item>
        <el-form-item label="工作内容：" prop="dailyContent">
          <el-input
            v-model="ruleForm.dailyContent"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
        <el-form-item label="遇到问题：" prop="dailyTrobule">
          <el-input
            v-model="ruleForm.dailyTrobule"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
        <el-form-item label="解决方案：" prop="dailyPlan">
          <el-input
            v-model="ruleForm.dailyPlan"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
        <el-form-item label="明日计划：" prop="futurePlan">
          <el-input
            v-model="ruleForm.futurePlan"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
        <el-form-item label="需求协助：" prop="queryHelp">
          <el-input
            v-model="ruleForm.queryHelp"
            type="textarea"
            :rows="2"
            placeholder="请输入内容"
            class="mb20"
          />
        </el-form-item>
        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
      </el-form>
    </div>
  </div>
</template>

<script>
import { fetchAddReport } from '@/api/workReport'
export default {
  name: 'AddWork',
  data() {
    return {
      ruleForm: {
        dailyDateVal: '',
        dailyContent: '',
        dailyTrobule: '',
        dailyPlan: '',
        futurePlan: '',
        queryHelp: ''
      },
      rules: {
        dailyDateVal: [
          { required: true, message: '请选择日期', trigger: 'blur' }
        ],
        dailyContent: [
          { required: true, message: '请填写工作内容', trigger: 'change' }
        ],
        dailyTrobule: [],
        dailyPlan: [],
        futurePlan: [],
        queryHelp: []
      },
      loading: false
    }
  },
  created() {
    if(parseInt(parseInt(new Date().getMonth())+1) < 10) {
      this.ruleForm.dailyDateVal = new Date().getFullYear() + "-0" + (new Date().getMonth()+1) + "-" + new Date().getDate()
    } else {
      this.ruleForm.dailyDateVal = new Date().getFullYear() + "-" + (new Date().getMonth()+1) + "-" + new Date().getDate()
    }

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
    fetchAdd() {
      this.loading = true
      fetchAddReport({
        date: this.ruleForm.dailyDateVal,
        content: this.ruleForm.dailyContent,
        question: this.ruleForm.dailyTrobule,
        solution: this.ruleForm.dailyPlan,
        plan: this.ruleForm.futurePlan,
        assist: this.ruleForm.queryHelp
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '添加成功!'
          })
          history.go(-1)
        } else {
          if(res.data.data.date) {
            this.$message.error(res.data.error_msg + "，今天日期：" + res.data.data.date)
          }else{
            this.$message.error(res.data.error_msg)
          }
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
  .add-work {
    padding: 20px;
    box-sizing: border-box;
  }
  .main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
  }
  .demo-ruleForm{
    width: 600px;
  }
  .mb5{
    margin-bottom: 5px;
  }
  .mb20{
    margin-bottom: 20px;
  }
</style>
