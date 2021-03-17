<template>
  <div class="advertising-add">
    <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px" class="demo-ruleForm">
      <el-form-item label="广告名称" prop="name">
        <el-input v-model="ruleForm.name" placeholder="请输入广告名称" />
      </el-form-item>
      <el-form-item class="m0">
        <p>广告名称只是作为辨别多个广告条目之用，并不显示在广告中</p>
      </el-form-item>
      <el-form-item label="广告位置" prop="pageLocation">
        <el-select v-model="ruleForm.pageLocation" placeholder="请选择活动区域">
          <el-option label="APP首页轮播" value="1" />
          <el-option label="PC端首页轮播" value="2" />
          <el-option label="PC端分类广告" value="3" />
        </el-select>
      </el-form-item>
      <el-form-item label="开始时间" prop="startTime">
        <el-date-picker
          v-model="ruleForm.startTime"
          type="datetime"
          value-format="yyyy-MM-dd HH:mm:ss"
          placeholder="选择日期时间"
        />
      </el-form-item>
      <el-form-item label="到期时间" prop="endTime">
        <el-date-picker
          v-model="ruleForm.endTime"
          value-format="yyyy-MM-dd HH:mm:ss"
          type="datetime"
          placeholder="选择日期时间"
        />
      </el-form-item>
      <el-form-item label="广告图片" prop="bannerUrl">
        <el-upload
          class="upload"
          :action="action"
          :limit="1"
          :on-success="handleUploadSuccess"
          :data="uploadExtendData"
          :before-upload="beforeAction"
          :on-preview="handlePreview"
          :on-remove="handleRemove"
          :file-list="fileList"
          list-type="picture-card"
        >
          <el-button size="small" type="primary">点击上传</el-button>
          <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过50kb</div>
        </el-upload>
        <el-dialog :visible.sync="dialogImg" width="30%" append-to-body>
          <img width="100%" :src="dialogImageUrl">
        </el-dialog>
      </el-form-item>
      <el-form-item label="上线/下线" prop="status">
        <el-radio-group v-model="ruleForm.status">
          <el-radio label="1">上线</el-radio>
          <el-radio label="2">下线</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="广告链接" prop="bannerTargetUrl">
        <el-input v-model="ruleForm.bannerTargetUrl" type="url" />
      </el-form-item>
      <el-form-item label="广告备注" prop="remark">
        <el-input v-model="ruleForm.remark" type="textarea" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'AdvertisingAdd',
  data() {
    return {
      id: '',
      imgList: [],
      action: '',
      dialogImg: false,
      dialogImageUrl: [],
      fileList: [],
      uploadExtendData: {
        key: '',
        token: ''
      },
      ruleForm: {
        id: '',
        name: '',
        bannerUrl: '',
        bannerTargetUrl: '',
        pageLocation: '',
        startTime: '',
        endTime: '',
        status: '1',
        remark: ''
      },
      rules: {
        name: [
          { required: true, message: '请输入活动名称', trigger: 'blur' }
        ],
        bannerUrl: [
          { required: true, message: '请上传广告图片', trigger: 'blur' }
        ],
        bannerTargetUrl: [
          { validator: (rule, value, callback) => {
            if (/^http:\/\/([\w-]+\.)+[\w-]+(\/[\w-./?%&=]*)?$/.test(value) === false) {
              callback(new Error('请输入正确的Url'))
            } else {
              callback()
            }
          }, required: true, trigger: 'blur'
          }
        ],
        pageLocation: [
          { required: true, message: '请选择广告位置', trigger: 'change' }
        ],
        startTime: [
          { required: true, message: '请选择日期', trigger: 'change' }
        ],
        endTime: [
          { required: true, message: '请选择时间', trigger: 'change' }
        ],
        status: [
          { required: true, message: '请选择上下线', trigger: 'change' }
        ]
      }
    }
  },
  watch: {},
  created() {
    this.action = this.$config.sevenCattleUrl
    this.getSevenCattleToken()
    const id = this.$route.query.id
    if (id) {
      this.ruleForm.id = id
      this.getInfo(id)
    }
  },
  methods: {
    getInfo(id) {
      this.$apis.ADVERT.detail({ id }).then(res => {
        if (res.code === 0) {
          this.ruleForm.status = res.data.status
          this.ruleForm.bannerTargetUrl = res.data.bannerTargetUrl
          this.ruleForm.endTime = res.data.endTime
          this.ruleForm.startTime = res.data.startTime
          this.ruleForm.name = res.data.name
          this.ruleForm.pageLocation = res.data.pageLocation
          this.ruleForm.bannerUrl = res.data.bannerUrl
          this.ruleForm.remark = res.data.remark
          this.imgList = [res.data.bannerUrl]
          this.fileList = [{ url: res.data.bannerUrl }]
        } else {
          this.$message.error(res.message)
        }
      })
    },
    submitForm(formName) {
      console.log(this.ruleForm)
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const obj = this.ruleForm
          console.log(obj)
          this.$apis.ADVERT.save(obj).then(res => {
            if (res.code === 0) {
              this.$message.success(res.message)
              this.$router.push('/advertising/advertising-manage')
            } else {
              this.$message.error(res.message)
            }
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
    },
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
      const isLt2M = file.size / 1024 < 50
      const key = this.upImageKey()
      this.uploadExtendData.key = key
      if (!isImg) {
        this.$message.error('上传图片只能是JPG或PNG 格式!')
      }
      if (!isLt2M) {
        this.$message.error('上传头像图片大小不能超过 50KB!')
      }
      return isImg && isLt2M
    },
    handleUploadSuccess(res, file, fileList) {
      const imgUrl = this.$config.imgServerDomain + res.key
      this.fileList = fileList
      this.ruleForm.bannerUrl = imgUrl
    },
    handleRemove(file, fileList) {
      this.fileList.splice(0, 1)
      this.ruleForm.bannerUrl.splice(0, 1)
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogImg = true
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.advertising-add{
  padding: 20px;
  .demo-ruleForm {
    margin: 30px 0 0 100px;
    width: 500px;
    .m0{
      margin: 0;
      p{
        line-height: 22px;
        margin-bottom: 5px;
      }
    }
  }
}
</style>
