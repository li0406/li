<template>
  <div class="channel">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="时间：">
          <el-date-picker
            v-model="date"
            size="small"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="账户：">
          <el-select v-model="account_id" size="small" filterable placeholder="请选择账户" clearable collapse-tags>
            <el-option v-for="item in accountList" :key="item.id" :label="item.account_name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
        </el-form-item>
        <el-form-item>
          <el-button class="export-btn" size="small" @click="addChannel">上传渠道消耗</el-button>
          <el-upload
            class="upload-demo"
            :action="importUrl"
            :name="name"
            :headers="uploadContentType"
            :before-upload="beforeUpload"
            :on-error="uploadFail"
            :on-success="uploadSuccess"
            :file-list="fileList"
            :show-file-list="false"
            :limit="1"
            style="display: inline-block;margin: 0 15px;"
          >
            <el-button v-loading="importLoading" class="export-btn" size="small">批量上传渠道消耗</el-button>
          </el-upload>
          <el-button class="export-btn" size="small" @click="moldboard">下载渠道日消耗模板</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="channel-table">
      <div class="qz-table">
        <el-table
          ref="cityTable"
          v-loading="tableLoading"
          :data="tableData"
          style="width: 100%"
          :cell-style="rowClass"
          :header-cell-style="tableHeaderColor"
        >
          <el-table-column prop="date" align="center" label="日期" />
          <el-table-column prop="account_name" align="center" label="渠道账户名称" />
          <el-table-column prop="expend_amount" align="center" label="消耗金额" />
          <el-table-column prop="operator_name" align="center" label="操作人" />
          <el-table-column prop="updated_date" align="center" label="操作时间" />
          <el-table-column align="center" label="操作">
            <template slot-scope="scope">
              <span class="hand" @click="edit(scope.row)">编辑 | </span>
              <span class="hand" @click="handleDetele(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <el-pagination
        v-if="total > 0"
        class="mt10"
        style="text-align: center;"
        background
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        prev-text="上一页"
        next-text="下一页"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    <el-dialog :title="diaTitle" :visible.sync="dialogFormVisible" width="40%">
      <el-form :model="formData" :rules="rules" ref="formData">
        <el-form-item label="渠道账户名称：" :label-width="formLabelWidth" prop="name">
          <el-select v-model="formData.name" placeholder="请选择渠道账户名称" :disabled="disabled">
            <el-option v-for="item in accountList" :key="item.id" :label="item.account_name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="选择日期：" :label-width="formLabelWidth" prop="new_date">
          <el-date-picker
            v-model="formData.new_date"
            type="date"
            style="width: 100%;"
            :disabled="disabled"
            placeholder="选择日期">
          </el-date-picker>
        </el-form-item>
        <el-form-item label="消耗金额：" :label-width="formLabelWidth" class="money" prop="money">
          <el-input type="number" v-model="formData.money" placeholder="请填写消耗金额" @mousewheel.native.prevent />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="cancelForm('formData')">取 消</el-button>
        <el-button type="primary" @click="submitForm('formData')">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import PUBDATA from '@/api/pubdata'
import domain from '@/config/config'
import { getToken } from '@/utils/auth'
export default {
  name: 'RoiData',
  components: {},
  data() {
    return {
      id: '',
      account_id: '',
      accountList: [],
      date: '',
      date_begin: '',
      date_end: '',
      tableData: [
        {
          city_name: '测试'
        }
      ],
      tableLoading: false,
      currentPage: 1, // 当前页数
      total: 0, // 总条数
      pageSize: 20, // 总共页数
      dialogFormVisible: false,
      diaTitle: '上传渠道消耗金额',
      formData: {
        name: '',
        new_date: '',
        money: ''
      },
      formLabelWidth: '130px',
      disabled: false,
      rules: {
        name: [
          { required: true, message: '请选择渠道账户名称', trigger: 'change' }
        ],
        new_date: [
          { required: true, message: '请选择日期', trigger: 'change' }
        ],
        money: [
          { required: true, message: '消耗金额不能为空' }
        ]
      },
      importLoading: false,
      importUrl: `${domain.BASE_URL}/v1/basic/sourceaccount/expend_upload`,
      importHeaders: {
        enctype: 'multipart/form-data'
      },
      fileList: [],
      errorResults: [],
      name: 'file',
      uploadContentType: {}
    }
  },
  computed: {

  },
  created() {
    this.getAccountList()
    this.getChannelList()
    const token = getToken()
    this.uploadContentType = { token }
  },
  methods: {
    beforeUpload(file) {
      // 上传前配置
      const excelfileExtend = '.xls,.xlsx'// 设置文件格式
      const fileExtend = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
      if (excelfileExtend.indexOf(fileExtend) <= -1) {
        this.$message.error('文件格式错误')
        return false
      }
      this.importLoading = true
    },
    uploadFail(err, file, fileList) {
      this.importLoading = false
      this.$message.error(err)
    },
    uploadSuccess(res, file, fileList) {
      if (parseInt(res.error_code) === 0) {
        this.$message({
          message: res.error_msg,
          type: 'success'
        })
        this.getChannelList()
      } else {
        this.$message.error(res.error_msg)
      }
      this.fileList = []
      this.importLoading = false
    },
    // 查询
    onSubmit() {
      if (this.date) {
        this.date_begin = this.date[0]
        this.date_end = this.date[1]
      } else {
        this.date_begin = ''
        this.date_end = ''
      }
      this.getChannelList()
    },
    // 重置
    resetForm() {
      this.date = ''
      this.account_id = ''
    },
    getChannelList() {
      this.$apis.PUBDATA.getChannelList(
        {
          account_id: this.account_id,
          date_begin: this.date_begin,
          date_end: this.date_end,
          page: this.currentPage,
          limit: this.pageSize
        }
      ).then(res => {
        if (res.error_code === 0) {
          this.tableData = res.data.list
          this.total = res.data.page.total_number
          this.date_begin = res.data.options.date_begin
          this.date_end = res.data.options.date_end
          this.date = [this.date_begin, this.date_end]
        }
      })
    },
    getAccountList() {
      this.$apis.PUBDATA.getAccountList().then(res => {
        if (res.error_code === 0) {
          this.accountList = res.data.list
        }
      })
    },
    // 上传渠道消耗
    addChannel() {
      this.dialogFormVisible = true
      this.diaTitle = '上传渠道消耗金额'
      this.disabled = false
      this.formData.name = ''
      this.formData.new_date = ''
      this.formData.money = ''
      this.id = ''
      this.$nextTick(() => {
        this.$refs['formData'].clearValidate() // 只清除清除验证
      })
    },
    handleChannel() {
      this.$apis.PUBDATA.handleChannel(
        {
          id: this.id,
          expend_amount: this.formData.money,
          account_id: this.formData.name,
          date: this.formData.new_date
        }
      ).then(res => {
        if (res.error_code === 0) {
          if (this.id) {
            this.$message({
              message: '修改成功',
              type: 'success'
            })
          } else {
            this.$message({
              message: '添加成功',
              type: 'success'
            })
          }
          this.getChannelList()
        } else {
          this.$message({
            message: res.error_msg,
            type: 'error'
          })
        }
      })
    },
    cancelForm(formName) {
      this.$refs[formName].resetFields()
      this.dialogFormVisible = false
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.handleChannel()
          this.dialogFormVisible = false
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 编辑
    edit(obj) {
      this.dialogFormVisible = true
      this.diaTitle = '编辑渠道消耗金额'
      this.id = obj.id
      this.formData.name = obj.account_id
      this.formData.new_date = obj.updated_date
      this.formData.money = obj.expend_amount
      this.disabled = true
    },
    // 删除
    handleDetele(obj) {
      this.$confirm('是否删除该条数据？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.PUBDATA.deleteChannel(
          {
            id: obj.id
          }
        ).then(res => {
          if (res.error_code === 0) {
            this.$message({
              message: '删除成功',
              type: 'success'
            })
            this.getChannelList()
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    // 下载模板
    moldboard() {
      const urls = `${domain.STATICQN_URL}/custom/20210309/Fs45gS1CLW0R4VUBtEAO1mYdElUV.xlsx`
      window.location.href = urls
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getChannelList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getChannelList()
    },
    // 修改table header的背景色
    tableHeaderColor({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 0) {
        return 'background-color: #0A145F;color: #fff;font-weight: 500;border-bottom: 1px dashed #02417E;'
      }
    },
    // 表格样式设置
    rowClass() {
      return 'text-align: center;border-bottom: 1px dashed #02417E;color: #2C9CFA;'
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.channel {
  padding: 20px 15px;
  .el-card{
    background: none;
    color: #fff;
    box-shadow: inset 0 0 11px #04e3ff;
    border: none;
  }
  .hand{
    cursor: pointer;
  }
  .el-card__body{
    height: 150px;
    div{
      text-align: left;
    }
    .num{
      font-size: 30px;
      font-weight: bold;
      margin-top:50px;
    }
  }
  .el-select{
    width: 100%;
  }
  .dialog-footer{
    text-align: center;
  }
}
</style>

