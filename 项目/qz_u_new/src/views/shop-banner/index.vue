<template>
  <div class="banner-contains">
    <el-row class="title">店铺轮播图</el-row>
    <div class="content">
    <!--
      <el-row class="tips">
        图片
        <span>适用于移动端、电脑端，最多5张；拖拽可调整图片排序（尺寸：750x420px，格式：JPG、PNG，大小：2M以内）</span>
        <span class="exam" @click="centerDialogVisible = true">查看示例</span>
        <span class="exam right" @click="drawer = true">审核标准说明</span>
      </el-row>
      -->
      <div class="tips-div pd20">
        <div class="el-icon-warning-outline colFE9028"></div>
        <div class="tips-content">
          <div class="pb15">1、适用于移动端、电脑端，最多5张；拖拽可调整图片排序（尺寸：750x420px，格式：JPG、PNG，大小：2M以内）<span class="underline point" @click="centerDialogVisible = true">查看示例</span></div>
          <div>2、建议上传可以展示公司实力、服务以及特色优势等信息，可以查看店铺轮播图<span class="underline point" @click="drawer = true">审核规范</span></div>
        </div>
      </div>

      <el-form
        ref="ruleForm"
        :model="ruleForm"
        status-icon
        label-position="right"
        label-width="100px"
        class="demo-ruleForm"
      >
        <el-row>
          <el-col :span="24" class="pic-back">
            <el-form-item v-loading="picLoading" label-width="0px">
              <div class="photo">
                <vuedraggable
                  class="vue-draggable"
                  v-model="uploadedImg"
                  animation="200"
                  tag="ul"
                  draggable=".draggable-item"
                  @start="onDragStart"
                  @end="onDragEnd"
                >
                  <li
                    v-for="(item, index) in uploadedImg"
                    :key="item + index"
                    class="draggable-item"
                    :style="{ width: 250 + 'px', height: 180 + 'px' }"
                  >
                    <el-image :src="baseUrl + item.img_path"></el-image>
                    <div class="footer">
                      <i class="el-icon-question blue" v-if="item.status=='wait'">上传待保存</i>
                      <i class="el-icon-s-check yellow" v-if="item.status==0">审核中</i>
                      <i class="el-icon-success green" v-if="item.status==1">已通过</i>
                      <i class="el-icon-error red" v-if="item.status==2">未通过</i>
                    </div>
                    <div class="shadow" @click="onRemoveHandler(item,index)">
                      <i class="el-icon-delete"></i>
                    </div>
                  </li>
                  <el-upload
                    ref="upload"
                    v-if="!picShow"
                    :limit="uploadSize"
                    :data="uploadExtendData"
                    :headers="uploadContentType"
                    :file-list="fileList"
                    :action="actionApi"
                    list-type="picture-card"
                    :before-upload="beforeAction"
                    :on-exceed="handleExceed"
                    :on-success="handleUploadSuccess"
                    multiple
                  >
                    <i class="el-icon-plus"></i>
                    <span class="add-zi">添加图片</span>
                  </el-upload>
                </vuedraggable>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label-width="0px">
          <el-button type="danger" @click="submit">保存</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-dialog title="示例" :visible.sync="centerDialogVisible" width="60%" center>
      <div class="pic">
        <div class="one">
          <h3>列表页</h3>
          <img src="https://staticqn.qizuang.com/custom/20200828/FgmsaWFJD6vulujT2oLjCcCk0WwD" alt />
        </div>
        <div class="two">
          <h3>详情页</h3>
          <img src="https://staticqn.qizuang.com/custom/20200827/FiM_rgdvyj2IPxK_7RBpy_aboKRJ" alt />
        </div>
      </div>
    </el-dialog>
    <!--审核标准-->
    <el-drawer title="审核标准说明" size="24%" :visible.sync="drawer" direction="rtl">
      <div class="dra-div">
        <div>审核不通过</div>
        <div class="mt20">1)含有广告(手机号码、固定电话、二维码、网址等)</div>
        <img class="dra-tit-img mt20"
        src="https://staticqn.qizuang.com/custom/20200923/FkIN94Q7yIsTrdq5CgksKyiPPsHD" alt="">
        <div class="mt20 col999">错误示例1 包含手机号等信息</div>
        <div class="mt20">2) 图片质量差(图片模糊不清、尺寸比例严重变形等)</div>
        <div class="dra-imgdiv mt20">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/FvNfEPCEu6fv41JYxRINF-KOBshQ" alt="">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/FmUmeVWfWWCHduGtfyVSGfLdOBlV" alt="">
        </div>
        <div class="mt20">3)不符合要求的logo(如使用禁用的logo、水印或logo过大)</div>
        <div class="dra-imgdiv mt20">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/FkLEWxKInwZ5tegRWgLDYSSv5fOy" alt="">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/Fn-sYuGU2M2AxPs86NVCp2wP6_-N" alt="">
        </div>
        <div class="mt20">审核成功案例</div>
        <div class="dra-imgdiv mt20">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/FhoRTgRHJ38UW7B7W5S_A7KLBlbS" alt="">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/Fprtxia5FaMKM0Q-7ed49C6RJYCJ" alt="">
        </div>
        <div class="dra-imgdiv mt20">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200916/Ft_4B93SKtMD1mDFjcLziPRRrYTP" alt="">
          <img class="dra-img" src="https://staticqn.qizuang.com/custom/20200923/Fse16U9yJ5-ZrTbs66CnqOT7z8Hx" alt="">
        </div>
      </div>
    </el-drawer>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import base from '@/api/base-info';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'StoreBanner',
  components: { vuedraggable },
  data() {
    return {
      //  审核标准说明
      drawer:false,
      ruleForm: {
        imgs: [],
      },
      // 图片上传
      picLoading: false,
      uploadSize: 5,
      imgs: [],
      uploadExtendData: {
        prefix: '',
      },
      uploadContentType: {
        token: localStorage.getItem('token'),
      },
      actionApi: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
      fileList: [],
      baseUrl: QZ_CONFIG.OSS_IMG_HOST,
      uploadedImg: [],
      delImg: [],
      centerDialogVisible: false,
    };
  },
  computed: {
    picShow() {
      const show = this.uploadedImg.length > 4;
      return show;
    },
  },
  created() {
    this.setData();
  },
  methods: {
    beforeAction(file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      const isLt2M = file.size / 1024 / 1024 < 2;
      if (!isJPG) {
        this.$message.error('图片格式不正确，请调整正确格式再上传图片');
      }
      if (!isLt2M) {
        this.$message.error('图片过大，请压缩图片再尝试上传');
      }

      const isSize =
        isLt2M &&
        new Promise((resolve, reject)=>{
          const width = 750;
          const height = 420;
          // eslint-disable-next-line no-underscore-dangle
          const _URL = window.URL || window.webkitURL;
          const img = new Image();
          img.onload = () => {
            const valid = img.width === width && img.height === height;
            // eslint-disable-next-line no-unused-expressions
            valid ? resolve() : reject();
          };
          img.src = _URL.createObjectURL(file);
        }).then(
          () => {
            return file;
          },
          () => {
            this.$message.error('上传的尺寸为750x420px');
            return Promise.reject();
          },
        );
      return isJPG && isLt2M && isSize;
    },
    // 上传限制
    handleExceed() {
      this.$message.error(`最多上传${this.uploadSize}张图片`);
    },
    handleUploadSuccess(res) {
      if (parseInt(res.error_code, 10) === 0) {
        const obj = {
          id: '',
          img_path: res.data.img_path,
          status: 'wait',
          delete: 0,
        };
        this.uploadedImg.push(obj);
      } else {
        this.$message.error(res.error_msg);
      }
    },
    // 提交
    submit() {
      this.picLoading = true;
      // eslint-disable-next-line camelcase
      const img_info = [];
      this.uploadedImg.forEach((item, index) => {
        const obj = {};
        obj.id = item.id ? item.id : '';
        obj.img_path = item.img_path;
        obj.sorted = index + 1;
        obj.delete = item.delete ? item.delete : 0;
        img_info.push(obj);
      });
      const params = {
        // eslint-disable-next-line camelcase
        img_info: [...img_info, ...this.delImg],
      };
      base.editBanner(params).then((result) => {
        if (parseInt(result.status, 10) === 200 && parseInt(result.data.error_code, 10) === 0) {
          this.$message({
            message: '保存成功',
            type: 'warning',
          });
          this.picLoading = false;
          this.$router.go(0);
        } else {
          this.$message({
            message: result.data.error_msg,
            type: 'warning',
          });
          this.picLoading = false;
        }
      });
    },
    // 回显
    setData() {
      base.banner({}).then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          const imgDatas = res.data.data;
          imgDatas.forEach((item) => {
            const obj = {};
            obj.url = item.img_path;
            this.fileList.push(obj);
          });
          this.uploadedImg = imgDatas.map((item) => {
            return {
              id: item.id,
              img_path: item.img_path.split('qizuang.com/')[1],
              status: item.status,
            };
          });
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    onDragStart(e) {
      e.target.classList.add('hideShadow');
    },
    onDragEnd(e) {
      e.target.classList.remove('hideShadow');
    },
    onRemoveHandler(item, index) {
      this.$confirm('确定删除该图片?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          this.uploadedImg = this.uploadedImg.filter((v, i) => {
            return i !== index;
          });
          this.fileList = this.fileList.filter((v, i) => {
            return i !== index;
          });
          if (item.id) {
            const obj = {
              id: item.id,
              img_path: '',
              sorted: '',
              delete: 1,
            };
            this.delImg.push(obj);
          }
          this.$message({
            type: 'success',
            message: '删除成功!',
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
.banner-contains {
  padding-bottom: 50px;
  background: #fff;
  .content {
    padding: 30px;
    overflow: hidden;
  }
  .title {
    width: 100%;
    height: 60px;
    margin-bottom: 10px;
    padding-left: 30px;
    color: rgba(51, 51, 51, 1);
    font-weight: 400;
    font-size: 16px;
    line-height: 60px;
    border-bottom: 1px solid #f2f2f2;
  }
  .tips {
    font-size: 16px;
    line-height: 60px;
    span {
      color: #999;
      font-size: 12px;
    }
    .exam {
      color: red;
      font-size: 16px;
      line-height: 60px;
      cursor: pointer;
    }
  }
  // 图片上传控件更改
  /deep/ .el-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 250px;
    height: 180px;
    line-height: 180px;
  }

  // 上传按钮
  .uploadIcon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #999;
    font-size: 20px;
    background-color: #fbfdff;
    border: 1px dashed #c0ccda;
    border-radius: 6px;

    .limitTxt,
    .uploading {
      position: absolute;
      bottom: 10%;
      left: 0;
      width: 100%;
      font-size: 14px;
      text-align: center;
    }
  }

  // 拖拽
  .vue-draggable {
    display: flex;
    flex-wrap: wrap;
    padding-left: 0;

    .draggable-item {
      position: relative;
      margin-right: 20px;
      margin-bottom: 20px;
      overflow: hidden;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      cursor: move;

      .el-image {
        width: 100%;
        height: 140px;
      }

      .footer {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        width: 100%;
        padding: 0 20px;
        line-height: 40px;
        text-align: center;
        cursor: auto;
        .red {
          color: #f00;
        }
        .green {
          color: #48c614;
        }
        .yellow {
          color: #f60;
        }
        .blue {
          color: #409eff;
        }
      }
      .shadow {
        position: absolute;
        top: 0;
        right: 0;
        padding: 2px;
        color: #fff;
        font-size: 20px;
        line-height: 20px;
        background-color: rgba(0, 0, 0, 0.5);
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
      }
      &:hover {
        .shadow {
          opacity: 1;
        }
      }
    }
    &.hideShadow {
      .shadow {
        display: none;
      }
    }
    &.single {
      position: relative;
      overflow: hidden;

      .draggable-item {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
      }
    }
    &.maxHidden {
      .uploadBox {
        display: none;
      }
    }
  }
  // el-image
  .el-image-viewer__wrapper {
    .el-image-viewer__mask {
      opacity: 0.8;
    }
    .el-icon-circle-close {
      color: #fff;
    }
  }
  .el-upload-list--picture-card {
    display: none !important;
  }
  .add-zi {
    height: 30px;
    color: #409eff;
    line-height: 30px;
  }
  .pic {
    display: flex;
    align-items: center;
    justify-content: space-around;
    overflow: hidden;
    div {
      float: left;
      text-align: center;
    }
  }
  .right{
    float: right;
  }
  /deep/ :focus {
    outline: 0;
  }
  .dra-div{
    padding: 0 50px 10px 20px;
    font-size:13px;
  }
  .dra-tit-img{
    width: 315px;
    height: 177px;
  }
  .dra-img{
    width: 189px;
    height: 106px;
    margin-right: 10px;
  }
  .dra-imgdiv{
    display: flex;
    justify-content: space-between;
  }
  .mt20{
    margin: 20px 0 0 0;
  }
  .col999{
    color: #999;
  }
  .colFE9028{
    margin-right: 5px;
    color: #FE9028;
    font-size: 16px;
  }
  .pd20{
    padding: 20px;
  }
  .tips-content{
    color: #AA5128;
    font-size: 14px;
  }
  .pb15{
    padding-bottom: 15px;
  }
  .underline{
    border-bottom: 2px solid;
  }
  .point{
    cursor: pointer;
  }
  .tips-div{
    display: flex;
    background-color: #FFD8A5;
  }
  .el-drawer__body{
    overflow: auto;
  }
}
</style>
