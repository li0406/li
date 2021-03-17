<template>
  <div class="cropper-style">
    <el-dialog title="裁切图片尺寸"
               width="40%"
               :visible.sync="dialogVisible">
      <div class="cropper-dialog">
        <div>
          <div>
            <div class="cropper-content"
                 style="margin-top:60px;margin-left:60px;">
              <div class="cropper">
                <vueCropper ref="cropper"
                            :img="option.img"
                            :outputSize="option.size"
                            :outputType="option.outputType"
                            :info="true"
                            :full="option.full"
                            :canMove="option.canMove"
                            :canMoveBox="option.canMoveBox"
                            :original="option.original"
                            :autoCrop="option.autoCrop"
                            :autoCropWidth="option.autoCropWidth"
                            :autoCropHeight="option.autoCropHeight"
                            :fixedBox="option.fixedBox"
                            @realTime="realTime"
                            @imgLoad="imgLoad"></vueCropper>
              </div>
            </div>
          </div>
          <div class="original">原始图片</div>
          <!--
          <div>
            <el-button type="primary" icon="el-icon-refresh-right" circle @click="rotateRight"></el-button>
            <el-button type="success" icon="el-icon-refresh-left" circle @click="rotateLeft"></el-button>
            <el-button type="danger" icon="el-icon-plus" circle @click="changeScale(1)"></el-button>
            <el-button type="warning" icon="el-icon-minus" circle @click="changeScale(-1)"></el-button>
          </div>
          -->
        </div>
        <div>
          <div class="actualImg"
               v-html="previews.html"></div>
          <div class="actual">实际展示图片</div>
        </div>
      </div>
      <div class="reason-button">
        <el-button class="btn-back"
                   @click="cancel()">取消</el-button>
        <el-button type="danger"
                   @click="submitUpload()">确定</el-button>
      </div>
    </el-dialog>
    <div class="eleme">
      <el-upload class="upload-demo"
                 ref="upload"
                 :action="actionApi"
                 :data="uploadExtendData"
                 :headers="uploadContentType"
                 :before-upload="beforeUpload"
                 :on-preview="handlePreview"
                 :on-remove="handleRemove"
                 :auto-upload="true"
                 :show-file-list="false">
        <div v-if="lastHead"
             class="lastHead">
          <img :src="lastHead"
               alt />
        </div>
        <div v-else
             slot="trigger"
             class="uphead">
          <img src="../../../assets/step-ico/yzAvatar.png"
               alt="">
          <div class="uphead-img">
            <div class="el-icon-upload2">
              <div>上传头像</div>
            </div>
          </div>
        </div>

        <!--
        <el-button style="margin-left: 10px;" size="small" type="success" @click="submitUpload">上传头像</el-button>
        -->
      </el-upload>
    </div>
  </div>
</template>

<script>
import QZ_CONFIG from '@/utils/config';
import axios from 'axios';

export default {
  props: ['imgUrl'],
  watch: {
    imgUrl (val) {
      this.lastHead = val;
    },
  },
  data () {
    return {
      lastHead: '',
      dialogVisible: false,
      uploadExtendData: {
        prefix: '',
      },
      uploadContentType: {
        token: localStorage.getItem('token'),
      },
      actionApi: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
      headImg: '',
      // 剪切图片上传
      crap: false,
      previews: {},
      option: {
        img: '',
        outputSize: 1, // 剪切后的图片质量（0.1-1）
        full: false, // 输出原图比例截图 props名full
        outputType: 'png',
        canMove: true,
        original: false,
        canMoveBox: true,
        autoCrop: true,
        autoCropWidth: 200,
        autoCropHeight: 200,
        fixedBox: false,
      },
      fileName: '', // 本机文件地址
      downImg: '#',
      imgFile: '',
      uploadImgRelaPath: '', // 上传后的图片的地址（不带服务器域名）
    };
  },
  mounted () {
    this.lastHead = this.imgUrl;
  },
  methods: {
    submitUpload () {
      //  this.$refs.upload.submit();
      // console.log(file, 1);
      this.finish('blob');
      this.dialogVisible = false;
    },
    handleRemove () {
      // console.log(file, fileList);
    },
    handlePreview () {
      // console.log(file);
      //    const data = window.URL.createObjectURL(new Blob([file]));
      //    this.option.img = data;
    },
    beforeUpload (file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
        return;
      }
      this.dialogVisible = true;
      const data = window.URL.createObjectURL(new Blob([file]));
      this.fileName = file.name;
      this.option.img = data;
      // console.log('上传文件');
      // console.log(file);
    },
    // 放大/缩小
    changeScale (num) {
      // console.log('changeScale');
      const num1 = num || 1;
      this.$refs.cropper.changeScale(num1);
    },
    // 坐旋转
    rotateLeft () {
      // console.log('rotateLeft');
      this.$refs.cropper.rotateLeft();
    },
    // 右旋转
    rotateRight () {
      // console.log('rotateRight');
      this.$refs.cropper.rotateRight();
    },
    cancel () {
      this.dialogVisible = false;
    },
    // 上传图片（点击上传按钮）
    finish (type) {
      // console.log(type, 'finish');
      // const _this = this;
      const formData = new FormData();
      //  输出
      if (type === 'blob') {
        this.$refs.cropper.getCropBlob((data) => {
          const img = window.URL.createObjectURL(data);
          this.model = true;
          this.modelSrc = img;
          formData.append('file', data, this.fileName);
          axios
            .post(this.actionApi, formData, {
              contentType: false,
              processData: false,
              headers: {
                token: localStorage.getItem('token'),
              },
            })
            .then((res) => {
              if (res.data.error_code === 0) {
                this.lastHead = `${QZ_CONFIG.OSS_IMG_HOST}${res.data.data.img_path}`;
                this.$emit('getHead', {
                  imgUrl: this.lastHead,
                });
                // 0成功 其余失败
                this.$message({
                  message: '上传成功！',
                  type: 'success',
                });
              } else {
                this.$message.error(res.data.error_msg);
              }
            })
            .catch((err) => {
              this.$message.error(err);
            });
        });
      } else {
        this.$refs.cropper.getCropData((data) => {
          this.model = true;
          this.modelSrc = data;
        });
      }
    },
    //  实时预览函数
    realTime (data) {
      // console.log(data, 'realTime');
      this.previews = data;
    },
    imgLoad () {
      // console.log('imgLoad');
      // console.log(msg);
    },
  },
};
</script>
<style scoped>
.cropper-dialog {
  display: flex;
}
.cropper-dialog > div {
  width: 50%;
  height: 400px;
}
.original {
  margin-top: 30px;
  color: #000;
  font-size: 18px;
  text-align: center;
}
.actualImg {
  width: 200px;
  height: 200px;
  margin: 70px 0 0 80px;
  text-align: center;
  border-radius: 50%;
}
.actual {
  margin-top: 60px;
  color: #000;
  font-size: 18px;
  text-align: center;
}
/* 补轮 确定 取消按钮 */
.reason-button {
  text-align: center;
}
.reason-button > button {
  width: 100px;
}
/* 取消按钮样式覆盖 */
.btn-back {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.btn-back:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.uphead {
  position: relative;
  width: 60px;
  height: 60px;
  line-height: 60px;
  text-align: center;
  border: 1px solid #bbb;
  border-radius: 50%;
}
.uphead:hover > .uphead-img {
  display: block;
}
.uphead-img {
  position: absolute;
  top: 0;
  left: 0;
  display: none;
  width: 60px;
  height: 60px;
  color: #fff;
  line-height: 60px;
  text-align: center;
  background-color: #aaa;
  border-radius: 50%;
}

.uphead-img > div {
  margin-top: 10px;
}
.uphead-img > div > div {
  margin-top: 3px;
  font-size: 12px;
}
.lastHead {
  width: 60px;
  height: 60px;
  line-height: 60px;
  border: 1px solid #bbb;
  border-radius: 50%;
}
.lastHead > img {
  width: 60px;
  height: 60px;
  line-height: 60px;
  border-radius: 50%;
}
</style>
<style lang="less">
.cropper-style {
  .show-preview {
    width: 100%;
    height: 100%;
    border-radius: 50%;
  }
}
.cropper-content {
  display: flex;
  .cropper {
    width: 260px;
    height: 260px;
    margin: -20px 0 0 -15px;
  }
  .show-preview {
    display: flex;
    display: -webkit-flex;
    -webkit-flex: 1;
    flex: 1;
    -webkit-justify-content: center;
    justify-content: center;
    .preview {
      margin-left: 40px;
      overflow: hidden;
      background: #ccc;
      border: 1px solid #ccc;
      border-radius: 50%;
    }
  }
}
</style>
