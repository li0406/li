<template>
  <div class="word-manage">
    <el-button class="add-btn" type="primary" @click="onHandleAddWord">新增推荐语</el-button>
    <el-table
      slot="table"
      :data="tableData"
      border
      style="width: 100%"
    >
      <el-table-column prop="word" label="默认推荐语" align="center" />
      <el-table-column prop="remark" label="备注" align="center" />
      <el-table-column label="启用状态" align="center">
        <template slot-scope="scope">
          <span v-if="scope.row.status === 0" class="red">待发布</span>
          <span v-else-if="scope.row.status === 1" class="green">已发布</span>
          <span v-else-if="scope.row.status === 2" class="grey">下架</span>
        </template>
      </el-table-column>
      <el-table-column prop="person" label="添加人" align="center" />
      <el-table-column prop="time" label="添加时间" align="center" />
      <el-table-column label="操作" align="center">
        <template slot-scope="scope">
          <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog
      :title="description"
      :visible.sync="dialogVisible"
      width="25%"
      :before-close="handleClose">
      <div>
        <el-form slot="from" ref="ruleForm" :rules="rules" :inline="true" :model="formData" label-width="120px">
          <el-form-item label="推荐语：" prop="word">
            <el-input v-model.number="formData.word" clearable placeholder="请输入推荐语" />
          </el-form-item>
          <el-form-item label="备注：">
            <el-input v-model.number="formData.remark" clearable placeholder="请填写备注" />
          </el-form-item>
          <el-form-item label="是否启用：">
            <el-switch v-model="formData.status"></el-switch>
          </el-form-item>
          <el-form-item class="button">
            <el-button type="primary" @click="onHandleDiaSubmit('ruleForm')">保存</el-button>
            <el-button @click="dialogVisible = false">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'defaultWord',
  components: {},
  data() {
    return {
      description: '新增默认推荐语',
      dialogVisible: false,
      formData: {
        word: '',
        remark: '',
        status: true
      },
      tableData: [1],
      rules: {
        word: [
          { required: true, message: '请输入推荐语', trigger: 'blur' }
        ]
      }
    }
  },
  computed: {},
  watch: {},
  created() {
  },
  methods: {
    onHandleAddWord() {
      this.dialogVisible = true
      this.formData.word = ''
      this.formData.remark = ''
      this.formData.status = true
      this.description = '新增默认推荐语'
    },
    edit() {
      this.dialogVisible = true
      this.description = '编辑默认推荐语'
    },
    handleClose(done) {
      this.dialogVisible = false
    },
    onHandleDiaSubmit(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          console.log(333)
        } else {
          console.log('error submit!!')
          return false
        }
      })
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.recommend-word{
  .add-btn{
    margin: 0 20px 20px;
  }
  .button{
    display: block;
    margin-left: 120px;
  }
}
</style>
