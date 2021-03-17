<template>
    <div class="uploadliangfangpic-box-mask">
        <div class="uploadliangfangpic-box">
            <span class="close" @click="handleClose">×</span>
            <p class="upload-tips">请上传量房图片凭证</p>
            <div class="upload-main" v-loading='loading'>
                <el-upload
                    class="upload-component"
                    ref="upload"
                    action=''
                    :http-request="uploadAjax"
                    :on-preview="handlePreview"
                    :on-remove="handleRemove"
                    :file-list="fileList"
                    :on-exceed="exceedFile"
                    :limit=1
                    list-type="picture"
                >
                    <el-button slot="trigger" size="small" type="primary">选取文件</el-button>
                    <el-button size="small" type="success" class="uploadtoserver" @click="submitUpload">确认提交</el-button>
                </el-upload>
            </div>
        </div>
        <m-tips ref="tips"/>
    </div>
</template>
<script>
import { submitLiangFangPic } from '@/api/activityApi.js'
import axios from 'axios'
import mTips from '../../../common/mTips.vue'
export default {
  name: 'UploadLiangFangPic',
  components: {
    mTips
  },
  data () {
    return {
      imageUrl: '',
      fileList: [],
      calcFileList: [],
      imgUploadedPath: '',
      axiosIntenface: null,
      loading: false
    }
  },
  created () {
    this.initAxiosFn()
  },
  methods: {
    initAxiosFn () {
      this.axiosIntenface = axios.create({
        baseURL: this.$qzConfig.api_base_url,
        timeout: 60000,
        headers: {
          'Content-Type': 'multipart/form-data',
          token: this.$cookies.get('token')
        }
      })
    },
    // 文件超出个数限制时的钩子
    exceedFile (files, fileList) {
      this.$refs.tips.tipsFadeIn({
        text: '只能选择一张图片'
      })
    },
    handleClose () {
      this.$emit('listenerHideUploadAction')
    },
    uploadAjax (item) {
      const formData = new FormData()
      formData.append('file', item.file)
      this.calcFileList = []
      this.loading = true
      this.axiosIntenface.post('/v1/user/feedback/imgup', formData).then(res => {
        this.calcFileList.push({
          name: '',
          url: res.data.data.img_path
        })
        this.imgUploadedPath = res.data.data.img_path
        this.loading = false
      }).catch(err => {
        this.$message.error(err)
        this.loading = false
      })
    },
    submitUpload () {
      if (!this.imgUploadedPath) {
        this.$refs.tips.tipsFadeIn({
          text: '请先选择图片'
        })
        return
      }
      submitLiangFangPic({
        img_path: this.imgUploadedPath
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.handleClose()
          // 上传成功
          this.$router.push({
            path: 'mygiftpackage'
          })
        }
      })
    },
    handleRemove (file, fileList) {
      console.log(file, fileList)
    },
    handlePreview (file) {
      console.log(file)
    }
  }
}
</script>
<style scope>
    .uploadliangfangpic-box-mask{
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        background-color: rgba(0, 0, 0, 0.5)
    }
    .uploadliangfangpic-box{
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: auto;
        width: 80%;
        height: 3.5rem;
        background-color: #FFEADA;
        border: 2px solid #FE5F29;
        border-radius: 0.15rem;
    }
    .uploadliangfangpic-box .close{
        width: 0.3rem;
        height: 0.3rem;
        line-height: 0.25rem;
        border-radius: 50%;
        position: absolute;
        top: -0.4rem;
        right: 0;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.3);
        color: #FFEADA;
        font-size: 0.25rem;
    }
    .uploadliangfangpic-box .upload-tips{
        text-align: center;
        font-size: 0.1536rem;
        font-weight: bold;
        color: #FB581E;
        line-height: 3;
    }
    .uploadliangfangpic-box .upload-main{
        width: 1.92rem;
        height: 1.92rem;
        background-color: white;
        margin: 0 auto;
        text-align: center;
    }
    .uploadliangfangpic-box .upload-main .upload-component{
        height: 100%;
        position: relative;
    }
    .uploadliangfangpic-box .upload-main .el-upload--picture{
        position: absolute;
        bottom: -0.5rem;
        left: 0;
        border:1px solid #FE5F29;
        color: #FE5F29;
        border-radius: 1rem;
        width: 100%
    }
    .uploadliangfangpic-box .upload-main .el-button--primary{
        background-color: transparent;
        border-color: transparent;
        color: #FE5F29;
        font-size: 0.1536rem;
    }
    .uploadliangfangpic-box .upload-main .el-upload-list{
        position: relative;
        height: 100%;
        overflow: hidden;
        background: url('../../../../assets/img/activity/liangfang/face-upload.png') no-repeat center center;
    }
    .uploadliangfangpic-box .upload-main .el-upload-list::after{
        content: '暂无上传';
        color: #E6B188;
        position: absolute;
        top: 80%;
        left: 50%;
        transform: translateX(-50%);
        z-index: -1;
    }
    .uploadliangfangpic-box .upload-main .el-upload-list--picture .el-upload-list__item{
        border: none;
        width: 100%;
        height: 100%;
        margin-top: 0;
        padding: 0;
        position: relative;
    }
    .uploadliangfangpic-box .upload-main .el-upload-list__item-status-label{
        display: none;
    }
    .uploadliangfangpic-box .upload-main .el-upload-list--picture .el-upload-list__item-thumbnail{
        width: 100%;
        height: auto;
        margin-left: 0;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%)
    }
    .uploadliangfangpic-box .uploadtoserver{
        position: absolute;
        padding: 0;
        bottom: -1.1rem;
        left: -0.06rem;
        width: 2.048rem !important;
        height: 0.5034rem !important;
        background-image: url('http://staticqn.qizuang.com/custom/20190924/Fh2EFowcER1_V0_F-YkvQ_tLpuu8') !important;
        background-repeat: no-repeat !important;
        background-size: 100% 100% !important;
        background-color: transparent !important;
        border-color: transparent !important;
        font-size: 0.1536rem;
    }
    .uploadliangfangpic-box .el-upload-list__item .el-icon-close{
        z-index: 99999999;
        background-color: rgba(0,0,0,0.3);
        color: white;
        font-size: 0.15rem;
        border-radius: 50%;
        padding: 0.02rem;
    }
    .uploadliangfangpic-box .el-upload-list--picture .el-upload-list__item-name{
        display: none;
    }
    .uploadliangfangpic-box .el-upload-list__item .el-icon-close::before{
        content: "\E6DB"
    }
</style>
