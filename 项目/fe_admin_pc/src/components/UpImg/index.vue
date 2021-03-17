<template>
  <div class="up-img">
    <el-upload
      class="upload"
      :action="action"
      :limit="limit"
      :on-success="handleUploadSuccess"
      :data="uploadExtendData"
      :before-upload="beforeAction"
      :on-preview="handlePreview"
      :on-remove="handleRemove"
      :file-list="fileList"
      list-type="picture-card"
    >
      <el-button size="small" type="primary">点击上传</el-button>
      <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过{{ size }}kb</div>
    </el-upload>
    <el-dialog :visible.sync="dialogImg" width="30%" append-to-body>
      <img width="100%" :src="dialogImageUrl">
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: '',
  props: {
    picList: {
      type: Array,
      default: null
    },
    limit: {
      type: Number,
      default: 10
    },
    size: {
      type: Number,
      default: 500
    }
  },
  data() {
    return {
      action: '',
      fileList: [],
      uploadExtendData: {
        key: '',
        token: ''
      },
      imgList: [], // 视频列表
      dialogImageUrl: '',
      dialogImg: false
    }
  },
  watch: {
    /* picList(val) {
      this.imgList = val
      this.fileList = val.map(item => {
        return { url: item }
      })
    } */
  },
  created() {
    this.action = this.$config.sevenCattleUrl
    this.getSevenCattleToken()
  },
  methods: {
    upImageKey() {
      const date = new Date().toLocaleDateString()
      const time = new Date().getTime()
      const math = Math.ceil(Math.random() * 100000)
      return `img/${date}/${time}${math}.jpg`
    },
    beforeAction(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      if (!isImg) {
        this.$message.error('上传图片只能是JPG或PNG 格式!')
        return isImg
      }
      const key = this.upImageKey()
      this.uploadExtendData.key = key
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
    handleUploadSuccess(res, file, fileList) {
      const imgUrl = this.$config.imgServerDomain + res.key
      this.fileList = fileList
      this.imgList.push(imgUrl)
      // this.$emit('changeData', this.imgList)
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.imgList.forEach((item, index) => {
          if (item.indexOf(file.response.key) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
            // this.$emit('changeData', this.imgList)
          }
        })
      } else {
        this.imgList.forEach((item, index) => {
          if (item.indexOf(file.url) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
            // this.$emit('changeData', this.imgList)
          }
        })
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogImg = true
    }
    /* handleSizeChange(val) {
      this.$emit('changeData', val)
    },
    handleCurrentChange(val) {
      this.$emit('changeData', val)
    } */
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.up-img{}
</style>
