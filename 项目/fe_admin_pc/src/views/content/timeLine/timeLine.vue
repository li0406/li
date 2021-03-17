<template>
  <div class="content-time">
    <h2 class="title">时间轴管理</h2>
    <el-button type="primary" style="margin-bottom: 30px;" @click="onHandleAdd">新增</el-button>
    <el-table
      slot="table"
      :data="tableData"
      border
      style="width: 100%"
      @selection-change="handleSelectionChange"
    >
      <el-table-column prop="goodsNo" label="展示内容" align="center" />
      <el-table-column prop="goodsNo" label="展示方式" align="center" />
      <el-table-column label="样例" align="center">
        <template slot-scope="scope">
          <el-image
            style="width: 100px; height: 100px"
            :src="scope.row.goodsCover"
            :preview-src-list="[scope.row.goodsCover]"
            fit="fit"
          />
        </template>
      </el-table-column>
      <el-table-column prop="goodsNo" label="调用规则" align="center" />
      <el-table-column prop="goodsName" label="推荐时效" align="center" />
      <el-table-column prop="goodsSkuName" label="显示顺序" align="center" />
      <el-table-column label="启用状态" align="center">
        <template slot-scope="scope">
          <el-switch v-model="scope.row.status" disabled/>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center">
        <template slot-scope="scope">
          <el-button class="operate" @click="handleEdit(scope.row.id)">编辑</el-button>
          <span class="line">|</span>
          <el-button class="operate" @click="deleteItem(scope.row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog
      title="展示内容编辑"
      :visible.sync="dialogVisible"
      width="25%"
      :before-close="handleClose"
    >
      <div>
        <el-form slot="from" ref="ruleForm" :rules="rules" :inline="true" :model="formData" label-width="120px">
          <el-form-item label="展示内容：">
            <el-select v-model="formData.contentId" clearable placeholder="请选择数据来源">
              <el-option v-for="item in contentSource" :key="item.id" :label="item.sourceName" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="展示方式：">
            <el-select v-model="formData.styleId" clearable placeholder="请选择展示方式">
              <el-option v-for="item in styleList" :key="item.id" :label="item.styleName" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="展示样例：">
            <el-upload
              class="upload"
              :action="action"
              :limit="5"
              :on-success="handleUploadSuccess"
              :data="uploadExtendData"
              :before-upload="beforeAction"
              :on-preview="handlePreview"
              :on-remove="handleRemove"
              :on-exceed="handleExceed"
              :file-list="fileList"
              list-type="picture-card"
            >
              <el-button size="small" type="primary">点击上传</el-button>
              <div slot="tip" class="el-upload__tip">
                图片标准：图片大小在1M以内，图片必须保证清晰。
              </div>
              <el-dialog :visible.sync="dialogImg" width="30%" append-to-body>
                <img width="100%" :src="dialogImageUrl">
              </el-dialog>
            </el-upload>
          </el-form-item>
          <el-form-item label="调用规则：">
            <el-select v-model="formData.ruleId" clearable placeholder="请选择数据调用规则">
              <el-option v-for="item in ruleList" :key="item.id" :label="item.ruleName" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="推荐内容时效：" prop="age">
            <el-input v-model.number="formData.age" clearable placeholder="请输入推荐时效性" />
          </el-form-item>
          <el-form-item label="内容显示顺序：" prop="px">
            <el-input v-model.number="formData.px" clearable placeholder="请输入显示顺序" />
          </el-form-item>
          <el-form-item label="启用状态：">
            <el-switch v-model="formData.status" />
          </el-form-item>
          <el-form-item class="button">
            <el-button type="primary" @click="onHandleDiaSubmit">确定</el-button>
            <el-button @click="dialogVisible = false">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'ContentTime',
  components: {},
  data() {
    var verificationInteger = (rule, value, callback) => {
      console.log(value)
      if (!value) {
        callback()
      } else {
        const reg = /^[+]{0,1}(\d+)$/
        if (reg.test(value)) {
          callback()
        } else {
          return callback(new Error('请输入整数'))
        }
      }
    }
    return {
      formData: {
        contentId: '',
        styleId: '',
        ruleId: '',
        imgList: [],
        age: '',
        px: '',
        status: true
      },
      contentSource: [
        {
          id: 1,
          sourceName: '商品'
        },
        {
          id: 2,
          sourceName: '笔记'
        },
        {
          id: 3,
          sourceName: '大家问'
        },
        {
          id: 4,
          sourceName: '广告'
        }
      ],
      styleList: [
        {
          id: 1,
          styleName: '单图片'
        },
        {
          id: 2,
          styleName: '单视频'
        },
        {
          id: 3,
          styleName: '图片组'
        },
        {
          id: 4,
          styleName: '视频组'
        },
        {
          id: 5,
          styleName: '纯文字（左排列）'
        },
        {
          id: 6,
          styleName: '纯文字（右排列）'
        }
      ],
      ruleList: [
        {
          id: 1,
          ruleName: '先按“推荐值倒序”再按“发布时间倒序”排序'
        },
        {
          id: 2,
          ruleName: '按“发布时间倒序”排序'
        },
        {
          id: 3,
          ruleName: '只按“推荐值倒序”排序'
        },
        {
          id: 4,
          ruleName: '只按“发布时间倒序”排序'
        }
      ],
      tableData: [
        {
          status: true
        },
        {
          status: false
        }
      ],
      action: '',
      uploadExtendData: {},
      dialogImageUrl: '',
      dialogImg: false,
      fileList: [],
      dialogVisible: false,
      rules: {
        age: [
          { validator: verificationInteger, trigger: 'blur' }
        ],
        px: [
          { validator: verificationInteger, trigger: 'blur' }
        ]
      }
    }
  },
  computed: {},
  watch: {},
  created() {
    this.action = this.$config.sevenCattleUrl
    this.getSevenCattleToken()
  },
  methods: {
    getSevenCattleToken() {
      const params = {}
      this.$apis.COMMON.getSevenCattleToken(params).then(res => {
        if (res.code === 0) {
          this.uploadExtendData.token = res.data
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    upImageKey() {
      const date = new Date().toLocaleDateString()
      const time = new Date().getTime()
      const math = Math.ceil(Math.random() * 100000)
      return `img/${date}/${time}${math}.jpg`
    },
    beforeAction(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      const isLt1M = file.size / 1024 / 1024 < 1
      if (!isImg) {
        this.$message.error('上传图片只能是JPG或PNG 格式!')
        return isImg
      }
      if (!isLt1M) {
        this.$message.error('上传图片大小不能超过 1MB!')
        return isLt1M
      }
      const key = this.upImageKey()
      this.uploadExtendData.key = key
    },
    handleUploadSuccess(res, file, fileList) {
      this.formData.imgList = []
      this.fileList = []
      const imgUrl = this.$config.imgServerDomain + res.key
      this.fileList.push(file)
      this.formData.imgList.push(imgUrl)
      // console.log(this.formData.imgList)
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.formData.imgList.forEach((item, index) => {
          if (item.indexOf(file.response.key) !== -1) {
            this.fileList.splice(index, 1)
            this.formData.imgList.splice(index, 1)
          }
        })
      } else {
        this.formData.imgList.forEach((item, index) => {
          if (item.url.indexOf(file.url) !== -1) {
            this.fileList.splice(index, 1)
            this.formData.imgList.splice(index, 1)
          }
        })
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogImg = true
    },
    handleExceed(files, fileList) {
      console.log(files)
      console.log(fileList)
      this.$message.warning('图片上传超出个数限制')
    },
    handleClose(done) {
      this.dialogVisible = false
    },
    // 添加
    onHandleAdd() {
      this.dialogVisible = true
      console.log(this.formData.contentId)
      console.log(this.formData.styleId)
      console.log(this.formData.ruleId)
      console.log(this.formData.status)
    },
    // 取消
    onHandleCancel() {
      this.$refs['ruleForm'].resetFields()
      this.formData.contentId = ''
      this.formData.styleId = ''
      this.formData.ruleId = ''
      this.formData.imgList = []
      this.fileList = []
      this.formData.status = true
    },
    handleEdit(id) {
      this.dialogVisible = true
    },
    handleSelectionChange(val) {
      console.log(val)
    },
    // 删除
    deleteItem(id) {
      this.$confirm('确认删除该内容？', '', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$message({
          type: 'success',
          message: '删除成功!'
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    onHandleDiaSubmit() {
      console.log(this.formData.contentId)
      console.log(this.formData.styleId)
      console.log(this.formData.ruleId)
      console.log(this.formData.status)
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-time{
  padding: 20px;
  .title{
    font-size: 20px;
    line-height: 30px;
    border-bottom: 3px solid #999;
    padding-bottom: 20px;
    margin-bottom: 30px;
  }
  .rule{
    font-size: 16px;
    line-height: 20px;
    margin-bottom: 20px;
  }
  .el-form-item{
    display: block;
  }
  .button{
    margin-left: 120px;
  }
  .operate{
    border: none;
    outline: none;
    background: none;
    color: rgb(2,125,180);
  }
  .line{
    color: rgb(2,125,180);
  }
}
</style>
