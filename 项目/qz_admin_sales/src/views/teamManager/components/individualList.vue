<template>
  <div class="team-member-list">
    <el-form :inline="true" :model="formData" class="demo-form-inline">
      <el-form-item label>
        <el-date-picker v-model="formData.year" type="year" format="yyyy" value-format="yyyy" placeholder="选择年" />
      </el-form-item>
      <el-form-item label="账号名称：">
        <el-input v-model="formData.name" placeholder="请输入" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
        <el-upload
          class="upload-demo"
          :action="importUrl"
          :name="upName"
          :headers="uploadContentType"
          :before-upload="beforeUpload"
          :on-error="uploadFail"
          :on-success="uploadSuccess"
          :file-list="fileList"
          :show-file-list="false"
          :limit="1"
        >
          <el-button v-loading="importLoading" type="primary">导入</el-button>
        </el-upload>
        <el-button type="primary" @click="handleTplDownload">模板下载</el-button>
      </el-form-item>
    </el-form>
    <el-table v-loading="loading" :data="tableData" style="width: 100%">
      <el-table-column prop="name" label="账号名称/月份" />
      <el-table-column prop="Jan" :label="tableYear+'1'" align="center" />
      <el-table-column prop="Feb" :label="tableYear+'2'" align="center" />
      <el-table-column prop="Mar" :label="tableYear+'3'" align="center" />
      <el-table-column prop="Apr" :label="tableYear+'4'" align="center" />
      <el-table-column prop="May" :label="tableYear+'5'" align="center" />
      <el-table-column prop="Jun" :label="tableYear+'6'" align="center" />
      <el-table-column prop="Jul" :label="tableYear+'7'" align="center" />
      <el-table-column prop="Aug" :label="tableYear+'8'" align="center" />
      <el-table-column prop="Sep" :label="tableYear+'9'" align="center" />
      <el-table-column prop="Oct" :label="tableYear+'10'" align="center" />
      <el-table-column prop="Nov" :label="tableYear+'11'" align="center" />
      <el-table-column prop="Dec" :label="tableYear+'12'" align="center" />
      <el-table-column prop="address" label="操作">
        <template slot-scope="scope">
          <span
            class="check-btn"
            @click="handleEdit(scope.row,scope.$index)"
          >编辑</span>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
      v-show="totals>0"
      :current-page="currentPage"
      :page-size="20"
      layout="total, prev, pager, next, jumper"
      :total="totals"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />
    <el-dialog
      title="编辑个人业绩"
      :visible.sync="dialogVisible"
      width="45%"
      :close-on-click-modal="false"
    >
      <h3>{{ dialogData.name }}</h3>
      <el-form :inline="true" :model="dialogData" label-position="top" class="demo-form-inline" label-width="100%">
        <el-form-item :label="dialogData.year + '-1'">
          <el-input v-model="dialogData.Jan" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-2'">
          <el-input v-model="dialogData.Feb" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-3'">
          <el-input v-model="dialogData.Mar" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-4'">
          <el-input v-model="dialogData.Apr" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-5'">
          <el-input v-model="dialogData.May" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-6'">
          <el-input v-model="dialogData.Jun" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-7'">
          <el-input v-model="dialogData.Jul" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-8'">
          <el-input v-model="dialogData.Aug" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-9'">
          <el-input v-model="dialogData.Sep" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-10'">
          <el-input v-model="dialogData.Oct" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-11'">
          <el-input v-model="dialogData.Nov" placeholder="请输入" />
        </el-form-item>
        <el-form-item :label="dialogData.year + '-12'">
          <el-input v-model="dialogData.Dec" placeholder="请输入" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="dialogVisibleSave">保 存</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { getMemberList, fetchIndicatorsEdit } from '@/api/teamManager'

export default {
  name: 'TeamItemList',
  data() {
    return {
      formData: {
        name: '',
        year: ''
      },
      tableData: [],
      tableYear: '',
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      loading: false,
      importUrl: this.$qzconfig.base_api + '/v1/indicators/upteammemberindicators',
      fileList: [],
      upName: 'file',
      uploadContentType: {
        token: localStorage.getItem('token')
      },
      importLoading: false, // 导入
      isEdit: false, // 当前是否有修改
      editIndex: '', // 当前修改index
      dialogVisible: false,
      dialogData: {}
    }
  },
  computed: {
    timeDefault() {
      const date = new Date()
      const year = date.getFullYear()
      return year + ''
    }
  },
  created() {

  },

  mounted() {
    this.formData.year = this.timeDefault
    this.tableYear = this.formData.year + '-'
    this.getList()
  },
  methods: {
    getList() {
      const that = this
      that.loading = true
      const query = {}
      query.page = that.currentPage
      if (that.formData.year) {
        query.year = that.formData.year
        that.tableYear = that.formData.year + '-'
      } else {
        this.formData.year = this.timeDefault
        this.tableYear = this.formData.year + '-'
      }
      if (that.formData.name) {
        query.name = that.formData.name
      }
      getMemberList(query).then(res => {
        console.log(res)
        that.loading = false
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.tableData = res.data.data.list
          this.totals = res.data.data.page.total_number
        } else {
          this.tableData = []
        }
      })
    },
    handleSearch() {
      this.getList()
    },
    handleSizeChange() {
      this.currentPage = 1
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    handleEdit(data, index) {
      const that = this
      that.dialogVisible = true
      that.dialogData = JSON.parse(JSON.stringify(data))
      that.editIndex = index
    },
    dialogVisibleSave() {
      const that = this
      that.dialogVisible = false
      // console.log(that.dialogData)
      const data = {}
      data.year = that.dialogData.year
      data.current_id = that.dialogData.current_id
      data.module = 2
      data.data = []
      data.data.push(that.dialogData.Jan)
      data.data.push(that.dialogData.Feb)
      data.data.push(that.dialogData.Mar)
      data.data.push(that.dialogData.Apr)
      data.data.push(that.dialogData.May)
      data.data.push(that.dialogData.Jun)
      data.data.push(that.dialogData.Jul)
      data.data.push(that.dialogData.Aug)
      data.data.push(that.dialogData.Sep)
      data.data.push(that.dialogData.Oct)
      data.data.push(that.dialogData.Nov)
      data.data.push(that.dialogData.Dec)
      fetchIndicatorsEdit(data).then(res => {
        // console.log(res)
        if (res.data.error_code === 0) {
          this.$message.success('修改成功')
          this.getList()
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    handleTplDownload() {
      const urls = this.$qzconfig.oss_img_host + 'custom/20200429/FvQxNpn3Wd8bhFzufV7yOGy4BFP3.xlsx'
      window.location.href = urls
    },
    beforeUpload(file) {
      // 上传前配置
      const excelfileExtend = '.xls,.xlsx' // 设置文件格式
      const fileExtend = file.name
        .substring(file.name.lastIndexOf('.'))
        .toLowerCase()
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
      console.log(res)
      if (parseInt(res.error_code) === 0) {
        this.$message({
          message: res.error_msg,
          type: 'success'
        })
        this.fileList = []
      } else {
        this.fileList = []
        this.$message.error(res.error_msg)
      }
      this.importLoading = false
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.team-member-list {
  width: 100%;
  padding: 5px;
  box-sizing: border-box;

  .upload-demo {
    display: inline-block;
  }

  .el-pagination {
    text-align: center;
    margin-top: 20px;
  }

  .el-select {
    margin-top: 0;
  }
  .check-btn {
    color: #409eff;
    cursor: pointer;
  }
}
</style>
