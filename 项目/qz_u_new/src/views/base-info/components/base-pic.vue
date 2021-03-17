<template>
  <div class="base-pic">
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      status-icon
      :rules="rules"
      label-position="right"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-row :gutter="10">
        <span class="pic-title">企业图片</span>
      </el-row>
      <el-row>
        <el-col :span="12" class="pic-back">
          <el-form-item v-loading="picLoading" label-width="0px" prop="imgs">
            <div class="photo">
              <el-upload
                ref="upload"
                :limit="uploadSize"
                :data="uploadExtendData"
                :headers="uploadContentType"
                :file-list="fileList"
                :action="actionApi"
                list-type="picture-card"
                :before-upload="beforeAction"
                :on-exceed="handleExceed"
                :on-preview="handlePictureCardPreview"
                :on-remove="handleRemove"
                :on-success="handleUploadSuccess"
                multiple
              >
                <i class="el-icon-plus"></i>
              </el-upload>
              <el-dialog :visible.sync="dialogVisible">
                <img :src="dialogImageUrl" width="100%" alt />
              </el-dialog>
            </div>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="10">
        <span class="pic-info">提示：在此上传企业形象和荣誉证书照片，最多可上传15张图片，建议图片尺寸为800*600PX</span>
      </el-row>

      <el-form-item label-width="0px">
        <el-button type="danger" @click="submit">保存</el-button>
      </el-form-item>
      <el-row :gutter="10">
        <span class="pic-title">企业形象图片</span>
      </el-row>
      <el-row>
        <el-col :span="12" class="pic-back">
          <el-form-item v-loading="picLoading" label-width="0px" prop="imgs">
            <div class="photo">
              <el-upload
                v-bind:class="[ifOne]"
                ref="upload"
                :limit="uploadSizeTwo"
                :data="uploadExtendData"
                :headers="uploadContentType"
                :file-list="fileListTwo"
                :action="actionApi"
                list-type="picture-card"
                :before-upload="beforeActionTwo"
                :on-exceed="handleTwo"
                :on-preview="handlePictureCardPreview"
                :on-remove="removeTwo"
                :on-success="uploadTwo"
              >
                <i class="el-icon-plus"></i>
              </el-upload>
              <el-dialog :visible.sync="dialogVisible">
                <img :src="dialogImageUrl" width="100%" alt />
              </el-dialog>
            </div>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="10">
        <span class="pic-info">提示：在此上传企业形象图，最多可上传一张，建议图片尺寸为1148*800PX</span>
      </el-row>

      <el-form-item label-width="0px">
        <el-button type="danger" @click="submitFigure">保存</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import base from '@/api/base-info';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'Basepic',
  data() {
    return {
      ruleForm: {
        imgs: [],
      },
      rules: {},
      // 图片上传
      picLoading: false,
      imgs: [],
      dialogVisible: false,
      dialogImageUrl: '',
      uploadSize: 15,
      uploadExtendData: {
        prefix: '',
      },
      uploadContentType: {
        token: localStorage.getItem('token'),
      },
      actionApi: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
      uploadedImg: [],
      fileList: [],
      // 图片上传2
      uploadSizeTwo: 1,
      fileListTwo: [],
      uploadedtwo: [],
      ifOne: '',
    };
  },
  created() {
    this.setData();
  },
  methods: {
    beforeAction(file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
      }
      return isJPG;
    },
    // 上传限制
    handleExceed() {
      this.$message.error(`最多上传${this.uploadSize}张图片`);
    },
    // 看缩览图
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url;
      this.dialogVisible = true;
    },
    handleUploadSuccess(res) {
      if (this.uploadedImg === '') {
        this.uploadedImg = [];
      }
      this.uploadedImg.push(res.data.img_path);
    },
    handleRemove(res) {
      if (res.response) {
        this.uploadedImg.forEach((item, index) => {
          if (res.response.data.img_path.indexOf(item) !== -1) {
            this.uploadedImg.splice(index, 1);
          }
        });
      } else {
        this.uploadedImg.forEach((item, index) => {
          if (res.url.indexOf(item) !== -1) {
            this.uploadedImg.splice(index, 1);
          }
        });
      }
    },
    // 上传形象图片
    handleTwo() {
      this.$message.error(`最多上传${this.uploadSizeTwo}张图片`);
    },
    beforeActionTwo(file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
      }
      return isJPG;
    },
    uploadTwo(res) {
      if (this.uploadedtwo === '') {
        this.uploadedtwo = [];
      }
      this.uploadedtwo.push(res.data.img_path);
      // eslint-disable-next-line no-unused-expressions
      this.uploadedtwo.length === 1 ? (this.ifOne = 'picOne') : (this.ifOne = '');
    },
    removeTwo(res) {
      if (res.response) {
        this.uploadedtwo.forEach((item, index) => {
          if (res.response.data.img_path.indexOf(item) !== -1) {
            this.uploadedtwo.splice(index, 1);
          }
        });
        this.ifOne = '';
      } else {
        this.ifOne = '';
        this.uploadedtwo.forEach((item, index) => {
          if (res.url.indexOf(item) !== -1) {
            this.uploadedtwo.splice(index, 1);
          }
        });
      }
    },
    // 提交
    submit() {
      // eslint-disable-next-line consistent-return
      base
        .editImages({
          images: this.uploadedImg,
        })
        .then((result) => {
          // eslint-disable-next-line radix
          if (parseInt(result.status) === 200 && parseInt(result.data.error_code) === 0) {
            this.$message({
              message: '保存成功',
              type: 'warning',
            });
          } else {
            this.$message({
              message: `企业图片${result.data.error_msg}`,
              type: 'warning',
            });
          }
        });
    },
    submitFigure() {
      const uploadedtwo = this.uploadedtwo.toString();
      base
        .enterImg({
          img: uploadedtwo,
        })
        .then((result) => {
          // eslint-disable-next-line radix
          if (parseInt(result.status) === 200 && parseInt(result.data.error_code) === 0) {
            this.$message({
              message: '保存成功',
              type: 'warning',
            });
          } else {
            this.$message({
              message: `企业形象图片${result.data.error_msg}`,
              type: 'warning',
            });
          }
        });
    },
    // 回显
    setData() {
      // 企业图片
      base.comImage({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const authImgs = res.data.data;
          // eslint-disable-next-line no-plusplus
          for (let i = 0; i < authImgs.length; i++) {
            const obj = { url: authImgs[i].img_path };
            this.fileList.push(obj);
            this.uploadedImg.push(authImgs[i].img_path_original);
          }
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });

      // 企业形象图片
      base.get({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const form = res.data.data;
          if (form.img.length > 0) {
            this.ifOne = 'picOne';
            const obj = { url: form.img };
            this.fileListTwo.push(obj);
            this.uploadedtwo.push(form.img_original);
          }
        }
      });
    },
  },
};
</script>


<style lang="less">
.base-pic {
  min-height: 1100px;
  .pic-title {
    padding-left: 10px;
    color: rgba(102, 102, 102, 1);
    font-weight: bold;
    font-size: 14px;
    line-height: 60px;
  }

  .el-upload-list--picture-card .el-upload-list__item {
    width: 100px;
    height: 100px;
  }

  .el-upload--picture-card {
    width: 100px;
    height: 100px;
    margin-bottom: 20px;
    line-height: 100px;
  }

  .pic-back {
    box-sizing: border-box;
    width: 582px;
    padding: 20px;
    padding-bottom: 0;
    background: rgba(250, 250, 250, 1);
    border: 1px solid rgba(227, 227, 227, 1);

    .el-form-item {
      margin-bottom: 0;
    }
  }

  .pic-info {
    padding-left: 10px;
    color: rgba(153, 153, 153, 1);
    font-weight: 400;
    font-size: 12px;
    line-height: 60px;
  }

  .picOne {
    .el-upload--picture-card {
      display: none;
    }
  }
}
</style>
