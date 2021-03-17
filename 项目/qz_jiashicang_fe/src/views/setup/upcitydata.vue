<template>
  <div class="up-city-data">
    <p>
      批量导入：Excel表结构（城市/期望会员数），请按照此结构设置批量数据，勿调整该结构
    </p>
    <el-upload
      class="upload-demo"
      drag
      :action="importUrl"
      multiple
      name="file"
      :headers="uploadContentType"
      :before-upload="beforeUpload"
      :on-error="uploadFail"
      :on-success="uploadSuccess"
      :file-list="fileList"
      :show-file-list="false"
      :limit="1"
    >
      <i class="el-icon-upload" />
      <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
    </el-upload>
    <el-button
      v-loading="exportLoading"
      size="small"
      class="export-btn"
      @click="handleReport"
    >下载城市模板</el-button>
  </div>
</template>

<script>
import { export_json_to_excel } from '@/excel/Export2Excel'
import domain from '@/config/config'
import { getToken } from '@/utils/auth'
export default {
  name: 'Upcitydata',
  data() {
    return {
      importUrl: '',
      exportData: [],
      exportLoading: false,
      importLoading: false,
      fileList: [],
      uploadContentType: {}
    }
  },
  created() {
    const token = getToken()
    this.importUrl = domain.BASE_URL + '/v1/template/uploadcitydata'
    this.uploadContentType = { token }
  },
  methods: {
    handleReport() {
      this.exportLoading = true
      const tHeader = [
        '城市',
        '期望会员数'
      ] // 设置Excel的表格第一行的标题
      const filterVal = ['city', 'qw']
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市模板')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      this.$apis.COMMON_API.getCityList().then((res) => {
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            const obj = {
              city: item.city_name,
              qw: '',
              llfd: '',
              llfphy: ''
            }
            exportData.push(obj)
          })
          this.exportData = exportData
          cb & cb.call()
        } else {
          this.exportData = []
        }
      })
    },
    beforeUpload(file) { // 上传前配置
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
      } else {
        this.$message.error(res.error_msg)
      }
      this.fileList = []
      this.importLoading = false
    }
  }
}
</script>

<style lang="scss" scoped>
.up-city-data {
  padding: 30px;
  .upload-demo {
    float: left;
  }
  .export-btn {
    border-color: #2C9CFA;
    color: #2C9CFA;
    background-color: #0A145F;
    margin-left: 30px;
    margin-top: 0px;
    float: left;
  }

  .export-btn:focus,
  .export-btn:hover {
    background-color: #2C9CFA;
    color: #fff;
  }
}
</style>
<style lang="scss">
.up-city-data {
  .el-upload-dragger {
    width: 500px;
    color: #02d1ec;
    background: #0a145f;
    .el-upload__text{
      color: #fff;
    }
  }
}
</style>
