<template>
  <div>
    <el-upload
      :action="uploadApi"
      list-type="picture-card"
      :data="uploadExtendData"
      :headers="uploadContentType"
      :on-preview="handlePictureCardPreview"
      :on-success="upimg"
      :file-list="upfilelist"
      :before-upload="beforeAvatarUpload"
      :on-remove="handleRemove"
    >
      <i class="el-icon-plus" />
    </el-upload>
    <el-dialog :visible.sync="dialogVisible">
      <img width="100%" :src="dialogImageUrl" alt>
    </el-dialog>
  </div>
</template>

<script>
export default {
  props: ['imglist'],
  data() {
    return {
      dialogImageUrl: '',
      dialogVisible: false,
      uploadApi: this.$qzconfig.base_api + '/uploads/upimg',
      uploadExtendData: {
        prefix: 'sale_report'
      },
      uploadContentType: {
        token: localStorage.getItem('token')
      },
      upfilelist: []
    }
  },
  created() {
    if (this.imglist !== '') {
      this.imglist.forEach((item) => {
        this.upfilelist.push({
          response: {
            data: {
              img_full: `https:${this.$qzconfig.oss_img_host}${item}`,
              img_path: item
            }
          },
          url: `https:${this.$qzconfig.oss_img_host}${item}`,
          half_src: item
        })
      })
    }
  },
  methods: {
    beforeAvatarUpload(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      if (!isImg) {
        this.$message.error('上传文件只能是JPG或PNG 格式!')
        return isImg
      }
    },
    upimg(response, file, fileList) {
      const upimglist = []
      fileList.forEach((item) => {
        upimglist.push(item.response.data.img_path)
      })
      this.$emit('upimglist', upimglist)
    },
    handleRemove(file, fileList) {
      const upimglist = []
      fileList.forEach((item) => {
        upimglist.push(item.response.data.img_path)
      })
      this.$emit('upimglist', upimglist)
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    }
  }
}
</script>
