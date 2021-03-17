<template>
  <div class="finance-dialog">
    <el-dialog :visible.sync="imgDialog" width="70%" @closed="dialogImgClosed">
      <el-carousel height="600px" :autoplay="false">
        <el-carousel-item v-for="item in picList" :key="item">
          <el-image :src="item" alt="" fit="contain" width="100%" height="100%" />
        </el-carousel-item>
      </el-carousel>
    </el-dialog>
    <el-dialog
      :visible.sync="confirmDialog"
      :close-on-click-modal="false"
      :title="title"
      width="680px"
      @closed="dialogClosed"
    >
      <el-form label-width="100px">
        <el-form-item label="转账金额">{{ pice }}</el-form-item>
        <el-form-item label="转账信息">
          <!-- <div>收款账户名：博联***</div>
          <div>开户行：博联***</div>
          <div>收款卡号：博联***</div> -->
          <p class="tips">{{ tips }}</p>
          <el-upload
            class="upload"
            :action="action"
            :limit="3"
            :on-exceed="handleExceed"
            :on-success="handleUploadSuccess"
            :data="uploadExtendData"
            :before-upload="beforeAction"
            :on-preview="handlePreview"
            :on-remove="handleRemove"
            :file-list="fileList"
            list-type="picture-card"
          >
            <el-button size="small" type="primary">点击上传</el-button>
          </el-upload>
          <el-dialog :visible.sync="dialogImg" width="30%" append-to-body>
            <img width="100%" :src="dialogImageUrl" alt="">
          </el-dialog>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogOk">确 认</el-button>
        <el-button @click="confirmDialog = false">取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: '',
  props: {
    title: {
      type: String,
      default: ''
    },
    tips: { // 上传图片描述
      type: String,
      default: ''
    },
    pice: { // 价格
      type: [String, Number],
      default: ''
    },
    imgDialog: { // 图片弹窗
      type: Boolean,
      default: false
    },
    picList: { // 显示的图片列表
      type: Array,
      default: null
    },
    confirmDialog: { // 确认弹窗
      type: Boolean,
      default: false
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
      imgList: [],
      dialogImageUrl: '',
      dialogImg: false
    }
  },
  created() {
    this.action = this.$config.sevenCattleUrl
    this.getSevenCattleToken()
  },
  methods: {
    dialogImgClosed() {
      this.$emit('imgClose')
    },
    dialogClosed() {
      this.$emit('confirmClose')
    },
    dialogOk() {
      if (this.imgList.length > 0) {
        const billVoucher = this.imgList.join(',')
        this.$emit('dialogOk', billVoucher)
      } else {
        this.$message.warning('请先上传图片')
      }
    },
    handleExceed() {
      this.$message.warning('最多上传3张')
    },
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
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.imgList.forEach((item, index) => {
          if (item.indexOf(file.response.key) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
          }
        })
      } else {
        this.imgList.forEach((item, index) => {
          if (item.url.indexOf(file.url) !== -1) {
            this.fileList.splice(index, 1)
            this.imgList.splice(index, 1)
          }
        })
      }
    },
    handlePreview(file) {
      this.dialogImageUrl = file.url
      this.dialogImg = true
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>

</style>
