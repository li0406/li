<template>
  <div class="base-check" v-loading="loading">
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
        <div class="pic-title">
          证件图片：营业执照
          <span>*此项为必上传项</span>
          <div class="status">
            <span v-if="uploadedImg.length > 0">{{state}}</span>
            <i v-if="ifDel" @click="delOne" class="el-icon-delete"></i>
          </div>
        </div>
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
                :disabled="noupload"
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
        <span class="pic-info">（最多传4张，每张图片小于3M）</span>
      </el-row>
      <el-form-item label-width="0px">
        <el-button type="danger" @click="submitobj('ruleForm')">保存</el-button>
      </el-form-item>

      <el-row :gutter="10">
        <div class="pic-title">
          装修资质
          <div class="status">
            <span v-if="uploadedtwo.length > 0">{{state2}}</span>
            <i v-if="ifTwo" @click="delThree" class="el-icon-delete"></i>
          </div>
        </div>
      </el-row>
      <el-row>
        <el-col :span="12" class="pic-back">
          <el-form-item v-loading="picLoading" label-width="0px" prop="imgs">
            <div class="photo">
              <el-upload
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
                :disabled="noupload2"
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
        <span class="pic-info">（最多传4张，每张图片小于3M）</span>
      </el-row>

      <el-form-item label-width="0px">
        <el-button type="danger" @click="submitobj1('ruleForm')">保存</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import base from '@/api/base-info';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'Basecheck',
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
      uploadSize: 4,
      uploadExtendData: {
        prefix: '',
      },
      uploadContentType: {
        token: localStorage.getItem('token'),
      },
      actionApi: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
      uploadedImg: [],
      state: '',
      fileList: [],
      // 图片上传2
      uploadSizeTwo: 4,
      fileListTwo: [],
      uploadedtwo: [],
      state2: '',
      stateNum: '',
      stateNum2: '',
      loading: false,
    };
  },
  computed: {
    ifDel() {
      return (this.stateNum === 2 || this.stateNum === 4 || this.stateNum === 5) && this.uploadedImg.length > 0;
    },
    ifTwo() {
      return (this.stateNum2 === 2 || this.stateNum2 === 4 || this.stateNum2 === 5) && this.uploadedtwo.length > 0;
    },
    noupload() {
      return this.stateNum === 1 || this.stateNum === 3;
    },
    noupload2() {
      return this.stateNum2 === 1 || this.stateNum2 === 3;
    },
  },
  created() {
    this.setData();
  },
  methods: {
    beforeAction(file) {
      const isLt3M = file.size / 1024 / 1024 < 3;
      if (!isLt3M) {
        this.$message.error('上传图片大小不能超过 3MB!');
      }
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
      }
      return isLt3M && isJPG;
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
      const isLt3M = file.size / 1024 / 1024 < 3;
      if (!isLt3M) {
        this.$message.error('上传图片大小不能超过 3MB!');
      }
      return isLt3M;
    },
    uploadTwo(res) {
      if (this.uploadedtwo === '') {
        this.uploadedtwo = [];
      }
      this.uploadedtwo.push(res.data.img_path);
    },
    removeTwo(res) {
      if (res.response) {
        this.uploadedtwo.forEach((item, index) => {
          if (res.response.data.img_path.indexOf(item) !== -1) {
            this.uploadedtwo.splice(index, 1);
          }
        });
      } else {
        this.uploadedtwo.forEach((item, index) => {
          if (res.url.indexOf(item) !== -1) {
            this.uploadedtwo.splice(index, 1);
          }
        });
      }
    },
    // 提交
    submitobj() {
      if (this.uploadedImg.length < 1) {
        this.$message({
          message: '请上传营业执照',
          type: 'error',
        });
        return;
      }
      const obj = [{ type: 1, images: this.uploadedImg }];
      base.saveLicence(obj).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            message: '上传成功',
            type: 'error',
          });
          this.setData();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    submitobj1() {
      if (this.uploadedtwo.length < 1) {
        this.$message({
          message: '请上传装修资质',
          type: 'error',
        });
        return;
      }
      const obj = [{ type: 3, images: this.uploadedtwo }];
      base.saveLicence(obj).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            message: '上传成功',
            type: 'error',
          });
          this.setData();
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    // 回显
    setData() {
      this.loading = true;
      this.fileList = [];
      this.uploadedImg = [];
      this.fileListTwo = [];
      this.uploadedtwo = [];
      this.state = '';
      this.state2 = '';
      this.stateNum = '';
      this.stateNum2 = '';
      base.licence({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.loading = false;
          const authImgs = res.data.data;
          authImgs.forEach((item) => {
            if (item.type === 1) {
              // 证件图片
              const stateNum = item.state;
              this.stateNum = stateNum;
              switch (stateNum) {
                case 1:
                  this.state = '审核中';
                  break;
                case 2:
                  this.state = '初审审核未通过';
                  break;
                case 3:
                  this.state = '初审通过';
                  break;
                case 4:
                  this.state = '复审通过 ';
                  break;
                case 5:
                  this.state = '复审不通过';
                  break;
                default:
              }
              // 全路径
              const path = item.images;
              const pathArr = Object.keys(path).map((value) => {
                return {
                  name: path[value],
                };
              });
              pathArr.forEach((it) => {
                const obj = { url: it.name };
                this.fileList.push(obj);
              });
              // 上传路径
              const original = item.images_original;
              const originalArr = Object.keys(original).map((value) => {
                return {
                  name: original[value],
                };
              });
              originalArr.forEach((it) => {
                this.uploadedImg.push(it.name);
              });
            } else if (item.type === 3) {
              // 装修资质
              const stateNum2 = item.state;
              this.stateNum2 = stateNum2;
              switch (stateNum2) {
                case 1:
                  this.state2 = '审核中';
                  break;
                case 2:
                  this.state2 = '初审审核未通过';
                  break;
                case 3:
                  this.state2 = '初审通过';
                  break;
                case 4:
                  this.state2 = '复审通过 ';
                  break;
                case 5:
                  this.state2 = '复审不通过';
                  break;
                default:
              }
              // 全路径
              const path = item.images;
              if (path) {
                const pathArr = Object.keys(path).map((value) => {
                  return {
                    name: path[value],
                  };
                });
                pathArr.forEach((it) => {
                  const obj = { url: it.name };
                  this.fileListTwo.push(obj);
                });
              }
              // 上传路径
              const original = item.images_original;
              if (original) {
                const originalArr = Object.keys(original).map((value) => {
                  return {
                    name: original[value],
                  };
                });
                originalArr.forEach((it) => {
                  this.uploadedtwo.push(it.name);
                });
              }
            }
          });
          // eslint-disable-next-line no-plusplus
        } else {
          this.loading = false;
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    delOne() {
      this.$confirm('删除后，您需要重新上传并审核，是否确认？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          base
            .delLicence({
              type: '1',
            })
            .then((res) => {
              // eslint-disable-next-line radix
              if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  type: 'success',
                  message: '删除成功!',
                });
                this.setData();
              } else {
                this.$message({
                  message: res.data.error_msg,
                  type: 'error',
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消删除',
          });
        });
    },
    delThree() {
      this.$confirm('删除后，您需要成重新上传并审核，是否确认？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          base
            .delLicence({
              type: '3',
            })
            .then((res) => {
              // eslint-disable-next-line radix
              if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  type: 'success',
                  message: '删除成功!',
                });
                this.setData();
              } else {
                this.$message({
                  message: res.data.error_msg,
                  type: 'error',
                });
              }
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消删除',
          });
        });
    },
  },
};
</script>

<style lang="less">
.base-check {
  min-height: 1100px;
  .pic-title {
    width: 590px;
    padding-left: 10px;
    color: rgba(102, 102, 102, 1);
    font-weight: bold;
    font-size: 14px;
    line-height: 60px;

    span {
      color: #ff5353;
    }
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
  .state {
    margin-left: 20px;
    color: #fff;
    font-size: 12px;
    background: #ff5353;
    span {
      color: #fff;
    }
  }
  .el-upload-list__item-delete {
    display: none !important;
  }
  .status {
    display: block;
    float: right;
    span {
      font-weight: 400;
      font-size: 10px;
    }
    i {
      margin-left: 20px;
    }
    i:hover {
      color: #ff5353;
    }
  }
}
</style>
