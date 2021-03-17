<template>
  <section :style='{"height":screenHeight}' class="section">
    <div class="feedback-box">
      <textarea name="feedback" id="feedback" placeholder="感谢您的建议，让我们可以做的更好~"  v-model="feedText" maxlength="1000"></textarea>
      <div class="upload-img">
        <div v-for="(item, index) in uploadImg" :key="index" class="feedback-img" >
          <div class="removeImg" @click="removeImg(index)">
            <div style="position:relative;width:100%; height:100%">
              <img src="../../assets/img/feedback/close.png" style="width:65%;position:absolute;margin:auto;left:0px; top:0px;right:0px;bottom:0px;">
            </div>
          </div>
          <div v-html="item" style="width:100%; height:100%; overflow:hidden;position:relative"></div>
        </div>
        <div class="upload-button" v-if="hideButton">
          <div class="dashed-box"></div>
          <p class="add-text">添加图片</p>
          <input type="file" class="file-input" @change="getFile" multiple="multiple" accept="image/png,image/gif,image/jpeg">
        </div>
      </div>
     </div>
    <div class="btn-submit" @click="submit">
      提交
    </div>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../common/mTips'
import { Indicator } from 'mint-ui'
import { setFeedBack, upLoadImg } from '@/api/api'
export default {
  name: 'feedback',
  components: {
    mTips
  },
  data () {
    return {
      screenHeight: '',
      uploadImg: [],
      imgValue: [],
      feedText: '',
      hideButton: true
    }
  },
  mounted () {
    let winHeight = window.innerHeight
    this.screenHeight = winHeight - 10 + 'px'
    sessionStorage.token = this.$route.query.token
  },
  methods: {
    getFile (e) {
      if (e.target.files.length > 5 - this.uploadImg.length) {
        this.$refs.tips.tipsFadeIn({
          text: '图片最多只能上传5张~'
        })
        return false
      }
      Indicator.open('读取中...')
      this.simpleUpload(e.target.files, 0)
    },
    simpleUpload (fileData, num) {
      let that = this
      let formData = new FormData()
      that.compressImg(fileData[num], function (newImg) {
        formData.append('file', newImg)
        upLoadImg(formData).then((res) => {
          if (res.data.error_code === 0) {
            let img = new Image()
            img.src = 'https://zxsqn.qizuang.com/' + res.data.data.img_path
            let imgDom = ''
            img.onload = function () {
              let width = this.width
              let height = this.height
              let boxWidth = (window.innerWidth - 20) / 3 - 10
              if (width <= height) {
                let p = width / boxWidth // 缩小的倍数
                let topHeight = height / p
                imgDom = '<img style="width:100%;margin:auto;left:0px;top:50%;margin-top:' + (-topHeight / 2) + 'px;position:absolute" src="' + img.src + '"/>'
              } else {
                let p = height / boxWidth // 缩小的倍数
                let leftWidth = width / p
                imgDom = '<img style="height:100%;margin:auto;top:0px;left:50%;margin-left:' + (-leftWidth / 2) + 'px;position:absolute" src="' + img.src + '"/>'
              }
              that.uploadImg.push(imgDom)
              that.imgValue.push(img.src)
              if (num < fileData.length - 1) {
                that.simpleUpload(fileData, ++num)
              } else {
                Indicator.close()
                if (that.uploadImg.length === 5) {
                  this.hideButton = false
                }
              }
            }
          }
        })
      })
    },
    removeImg (index) {
      this.uploadImg.splice(index, 1)
      this.imgValue.splice(index, 1)
    },
    // 图片压缩
    compressImg (file, callBack) {
      let type = file.type
      if (file.size > 512000) { // 如果大于500kb
        let reader = new FileReader()
        reader.readAsDataURL(file) // 可将input读取的文件转换为base64
        reader.onload = function () {
          let img = new Image() // 创建image对象
          img.src = reader.result // reader.result 是路径
          img.onload = function () {
            let canvas = document.createElement('canvas') // 创建canvas对象
            let context = canvas.getContext('2d')
            // canvas 缩放
            canvas.width = 1000
            canvas.height = img.height / (img.width / 1000)
            context.clearRect(0, 0, canvas.width, canvas.height)
            context.drawImage(img, 0, 0, canvas.width, canvas.height) // 绘图
            let newUrl = canvas.toDataURL(type, 0.9)
            let arr = newUrl.split(',')
            let mime = arr[0].match(/:(.*?);/)[1]
            let bstr = atob(arr[1])
            let n = bstr.length
            let u8arr = new Uint8Array(n)
            while (n--) {
              u8arr[n] = bstr.charCodeAt(n)
            }
            let newFile = new File([u8arr], file.name, {type: mime})
            callBack(newFile)
          }
        }
      } else {
        callBack(file)
      }
    },
    submit () {
      if (this.feedText.length === 0) {
        this.$refs.tips.tipsFadeIn({
          text: '您还没有填写建议哦~'
        })
        return false
      }
      if (this.feedText.length < 5) {
        this.$refs.tips.tipsFadeIn({
          text: '反馈内容最少5个字哦~'
        })
        return false
      }
      let parms = {
        content: this.feedText,
        imgs: this.uploadImg
      }
      setFeedBack(parms).then((res) => {
        if (res.data.error_code === 0) {
          this.$refs.tips.tipsFadeIn({
            text: '提交成功 感谢您的宝贵意见！'
          })
          this.feedText = ''
          this.imgValue = []
          this.uploadImg = []
        } else {
          this.$refs.tips.tipsFadeIn({
            text: ''
          })
        }
      })
    }
  }
}
</script>
<style scoped>
  .section {
    height: calc(100% - 10px);
    background:#F7F7F7;
    padding-top:10px;
    position: relative;
  }
  .feedback-box{
    background:#fff;
    padding:10px;
  }
  #feedback{
    width: 100%;
    height: 1.8rem;
    resize: none;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    font-size: 0.12rem;
  }
  textarea::-webkit-input-placeholder {
      color:#999;
  }
  textarea:-moz-placeholder {
      color:#999;
  }
  textarea::-moz-placeholder {
      color:#999;
  }
  textarea:-ms-input-placeholder {
      color:#999;
  }
  .btn-submit{
    position: absolute;
    bottom: 0px;
    padding:0.08rem 0px;
    background: #ff5353;
    color:#fff;
    text-align: center;
    width: 100%;
    font-size: 0.14rem;
  }
  .upload-button{
    border:1px dashed #ccc;
    width:0.8rem;
    height: 0.85rem;
    margin:8px;
    position: relative;
    background: url("../../assets/img/feedback/plusIcon.png") no-repeat;
    background-position: center 28%;
    background-size: 40% 40%;
    float: left;
  }
  .dashed-box {
    width: 40%;
    height: 40%;
    left: 50%;
    margin:16% auto;
  }
  .add-text{
    color: #D8D8D8;
    text-align: center;
    font-size: 0.1rem;
    position: absolute;
    width:100%;
    bottom: 10px;
  }
  .upload-img::after{
    content: "";
    clear: both;
    display: block;
  }
  .upload-img .feedback-img{
    width:calc(33.3333% - 10px);
    border:5px solid #fff;
    float: left;
    text-align: center;
    height: calc(1rem - 10px);
    position: relative;
  }
  .removeImg {
    position: absolute;
    width:17px;
    height: 17px;
    background: rgba(0,0,0,0.7);
    color:#fff;
    right: -6px;
    top:-6px;
    z-index:999;
    border-radius: 50%;
  }
  .file-input{
    width:100%;
    height: 100%;
    position: absolute;
    appearance: none;
    -webkit-appearance: none;
    display: block;
    top:0px;
    bottom:0px;
    opacity: 0;
  }
</style>
