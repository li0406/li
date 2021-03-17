// 基本资料
<template>
  <div class="base-main" v-loading="loading">
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      status-icon
      :rules="rules"
      label-position="right"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-form-item v-loading="picLoading" label="公司LOGO:" prop="logo">
        <el-col :span="6">
          <el-upload
            v-bind:class="['avatar-uploader ',ifOne]"
            :action="actionApi"
            :limit="1"
            :data="uploadExtendData"
            :headers="uploadContentType"
            list-type="picture-card"
            :on-success="handleUploadSuccess"
            :on-remove="remove"
            :before-upload="beforeAvatarUpload"
            :file-list="fileList"
          >
            <i class="el-icon-plus avatar-uploader-icon" />
            <span>添加图片</span>
          </el-upload>
        </el-col>
        <el-col :span="24">
          <div class="f2-info">
            提示：
            <br />1.在此上传您的企业logo标志，最好为126*63像素且为白底。
            <br />2.标志图片上不得有联系方式及网址, 否则就会被取消上传logo权限。
          </div>
        </el-col>
      </el-form-item>
      <el-form-item label="公司全称:" prop="qc">
        <el-col :span="4">
          <el-input v-model="ruleForm.qc" maxlength="20" placeholder="公司全称" />
        </el-col>
      </el-form-item>
      <el-form-item label="公司简称:" prop="jc">
        <el-col :span="4">
          <el-input v-model="ruleForm.jc" maxlength="8" placeholder="公司简称，最多8个中文" />
        </el-col>
      </el-form-item>
      <el-form-item label="公司口号:" prop="kouhao">
        <el-col :span="4">
          <el-input v-model="ruleForm.kouhao" maxlength="50" placeholder="最好输入9个字" />
        </el-col>
      </el-form-item>
      <el-row>
        <el-col :span="4">
          <el-form-item label="所在区域:">
            <el-select v-model="cname" filterable disabled></el-select>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item label label-width="20px">
            <el-select class="qx" v-model="ruleForm.qx" placeholder="选区域" filterable>
              <el-option
                v-for="item in area"
                :key="item.area_name"
                :value="item.area_id"
                :label="item.area_name"
              />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="4">
          <el-form-item label="联系人:" prop="name">
            <el-input v-model="ruleForm.name" placeholder="请填写姓名" />
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item class="sex" prop="sex" label-width="20px">
            <el-radio-group v-model="ruleForm.sex">
              <el-radio label="先生"></el-radio>
              <el-radio label="女士"></el-radio>
            </el-radio-group>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col class="w274">
          <el-form-item label="手机号:" prop="tel">
            <el-input v-model="ruleForm.tel" maxlength="11" placeholder="手机号" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col class="w274">
          <el-form-item label="固定电话:" prop="cals">
            <el-input v-model="ruleForm.cal" placeholder="请输入固定电话/400电话" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item label="公司地址" prop="dz">
        <el-col :span="4">
          <el-input v-model="ruleForm.dz" placeholder="公司详细地址" />
        </el-col>
      </el-form-item>
      <el-row>
        <el-col :span="4">
          <el-form-item label="QQ客服1:" prop="nickname">
            <el-col>
              <el-input v-model="ruleForm.nickname" placeholder="客服昵称" />
            </el-col>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="qq" label-width="20px">
            <el-col>
              <el-input v-model="ruleForm.qq" placeholder="QQ号码" />
            </el-col>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row>
        <el-col :span="4">
          <el-form-item label="QQ客服2:" prop="nickname1">
            <el-col>
              <el-input v-model="ruleForm.nickname1" placeholder="客服昵称" />
            </el-col>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="qq1" label-width="20px">
            <el-col>
              <el-input v-model="ruleForm.qq1" placeholder="QQ号码" />
            </el-col>
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item class="lastItem">
        <el-button type="danger" class="danger" @click="submitForm('ruleForm')">更新</el-button>
      </el-form-item>
    </el-form>
    <div class="maps">
      <p>请在地图上设置公司的位置</p>
      <p>如果想要修改标记 请先删除已有标记</p>
      <div class="make" v-if="make" @click="biaoji">
        <i class="el-icon-place"></i> 标记
      </div>
      <baidu-map
        class="bm-view"
        :center="center"
        :zoom="zoom"
        scroll-wheel-zoom
        @ready="handler"
        @click="getPoint"
      >
        <bm-navigation anchor="BMAP_ANCHOR_TOP_LEFT"></bm-navigation>
        <bm-marker :position="postionMap" v-if="mapinfo" @mouseover="infoWindowOpen">
          <bm-info-window :show="mapshow" @close="infoWindowClose" @open="infoWindowOpen">
            <el-row class="row">
              <el-col :span="4">描述:</el-col>
              <el-col :span="16">
                <el-input
                  class="com"
                  type="textarea"
                  v-model="add.com"
                  :rows="1"
                  placeholder="请输入内容"
                ></el-input>
              </el-col>
            </el-row>
            <el-row class="row">地址: {{add.site}}</el-row>
            <el-row>
              <el-col :span="12">
                <el-button type="primary" class="allow" @click="subMap">确定</el-button>
              </el-col>
              <el-col :span="12">
                <el-button type="danger" @click="delMap">删除</el-button>
              </el-col>
            </el-row>
          </bm-info-window>
        </bm-marker>
      </baidu-map>
    </div>
  </div>
</template>

<script>
import { validPhone, validCall, validCal, validQQ } from '@/utils/validate';
import base from '@/api/base-info';
import QZ_CONFIG from '@/utils/config';

export default {
  name: 'Basemain',
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请填写手机号'));
      } else if (!validPhone(value)) {
        callback(new Error('请填写11位正确手机号'));
      } else {
        callback();
      }
    };
    // const validateArea = (rule, value, callback) => {
    //   if (!value) {
    //     callback();
    //   } else if (!validArea(value)) {
    //     callback(new Error('请填写正确区号'));
    //   } else {
    //     callback();
    //   }
    // };
    const validateCall = (rule, value, callback) => {
      if (!value) {
        callback();
      } else if (this.ruleForm.cals) {
        if (!validCall(value)) {
          callback(new Error('请填写正确固定电话'));
        } else {
          callback();
        }
      } else if (!this.ruleForm.cals) {
        if (!validCal(value)) {
          callback(new Error('请填写正确固定电话'));
        } else {
          callback();
        }
      } else {
        callback();
      }
    };
    const validateQq = (rule, value, callback) => {
      if (!value) {
        callback();
      } else if (!validQQ(value)) {
        callback(new Error('请填写正确qq'));
      } else {
        callback();
      }
    };
    const validatelogo = (rule, value, callback) => {
      callback();
    };
    return {
      ruleForm: {
        qc: '',
        jc: '',
        kouhao: '',
        logo: '',
        cs: '',
        qx: '',
        name: '',
        sex: '',
        tel: '',
        cals: '',
        cal: '',
        nickname: '',
        qq: '',
        nickname1: '',
        qq1: '',
        dz: '',
      },
      cname: '',
      citys: {},
      area: [],
      rules: {
        logo: [{ required: true, validator: validatelogo, trigger: 'change' }],
        qc: [{ required: true, message: '请填写公司全称', trigger: 'blur' }],
        jc: [{ required: true, message: '请填写公司简称', trigger: 'blur' }],
        dz: [{ required: true, message: '请填写正确的公司地址', trigger: 'blur' }],
        tel: [{ required: true, validator: validatePhone, trigger: 'change' }],
        // cals: [{ required: false, validator: validateArea, trigger: 'change' }],
        cal: [{ required: false, validator: validateCall, trigger: 'change' }],
        qq: [{ validator: validateQq, trigger: 'change' }],
        qq1: [{ validator: validateQq, trigger: 'change' }],
      },
      loading: false,
      picLoading: false,
      uploadExtendData: {
        prefix: '',
      },
      uploadContentType: {
        token: localStorage.getItem('token'),
      },
      actionApi: `${QZ_CONFIG.OSS_AUDIO_HOST}cp/v1/upload/img`,
      fileList: [],
      ifOne: '',
      // 地图配置
      center: { lng: 0, lat: 0 },
      zoom: 3,
      postionMap: {
        lng: '',
        lat: '',
      },
      mapshow: false,
      add: {
        jd: '',
        wd: '',
        com: '',
        site: '',
      },
      mapinfo: false,
      make: true,
      dragPoint: '',
    };
  },
  created() {
    this.getData();
  },
  methods: {
    // 地图配置
    // eslint-disable-next-line no-unused-vars
    handler({ BMap, map }) {
      // console.log(BMap, map);
      this.center.lng = this.postionMap.lng ? this.postionMap.lng : 120.648754;
      this.center.lat = this.postionMap.lat ? this.postionMap.lat : 31.422956;
      this.zoom = 10;
    },
    infoWindowClose() {
      this.mapshow = false;
    },
    infoWindowOpen() {
      this.mapshow = true;
    },
    // eslint-disable-next-line consistent-return
    getPoint(e) {
      if (!this.dragPoint) {
        return false;
      }
      // eslint-disable-next-line no-unreachable
      this.show = true;
      this.postionMap.lng = e.point.lng;
      this.postionMap.lat = e.point.lat;
      this.add.jd = e.point.lng;
      this.add.wd = e.point.lat;
      this.zoom = e.target.getZoom();

      // eslint-disable-next-line no-undef
      const geocoder = new BMap.Geocoder();
      geocoder.getLocation(e.point, (rs) => {
        this.add.site = rs.address;
      });
    },
    subMap() {
      this.$message({
        message: '标记成功',
        type: 'success',
      });
    },
    delMap() {
      this.mapinfo = false;
      this.make = true;
    },
    biaoji() {
      this.mapinfo = true;
      this.make = false;
      this.dragPoint = true;
    },
    // 回显数据
    getData() {
      this.loading = true;
      base.get({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.loading = false;
          const form = res.data.data;
          this.ruleForm.qc = form.qc;
          this.ruleForm.jc = form.jc;
          this.ruleForm.kouhao = form.kouhao;
          this.ruleForm.logo = form.logo;
          this.ruleForm.cs = form.cs;
          this.cname = form.cname;
          this.ruleForm.qx = Number(form.qx);
          this.ruleForm.name = form.name;
          this.ruleForm.sex = form.sex;
          this.ruleForm.tel = form.tel;
          this.ruleForm.cals = form.cals;
          this.ruleForm.cal = form.cal;
          this.ruleForm.nickname = form.nickname;
          this.ruleForm.qq = form.qq;
          this.ruleForm.nickname1 = form.nickname1;
          this.ruleForm.qq1 = form.qq1;
          this.ruleForm.qc = form.qc;
          this.ruleForm.dz = form.dz;
          if (form.logo.length > 0) {
            this.ifOne = 'picOne';
            const obj = { url: form.logo };
            this.fileList.push(obj);
          }
          // 地图回显
          if (form.map_address.length > 0) {
            this.mapinfo = true;
            this.make = false;
            // 是否可以点击标记点
            this.dragPoint = false;
            this.add = {
              jd: form.lng,
              wd: form.lat,
              com: form.map_info,
              site: form.map_address,
            };
            this.postionMap = {
              lng: form.lng,
              lat: form.lat,
            };
          }
          base
            .getareainfobycityid({
              city_name: form.cname,
            })
            .then((result) => {
              this.area = result.data.data;
            });
        } else {
          this.loading = false;
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    // 提交操作
    submitForm(formName) {
      this.ruleForm.lng = this.add.jd;
      this.ruleForm.lat = this.add.wd;
      this.ruleForm.map_info = this.add.com;
      this.ruleForm.map_address = this.add.site;
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;
          base.edit(this.ruleForm).then((res) => {
            // eslint-disable-next-line radix
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.loading = false;
              this.$message({
                message: '更新成功',
                type: 'success',
              });
            } else {
              this.loading = false;
              this.$message({
                message: res.data.error_msg,
                type: 'error',
              });
            }
          });
        } else {
          return false;
        }
      });
    },
    handleUploadSuccess(res) {
      this.ruleForm.logo = `${QZ_CONFIG.OSS_IMG_HOST}${res.data.img_path}`;
      this.ifOne = 'picOne';
    },
    beforeAvatarUpload(file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
      }
      return isJPG;
    },
    remove() {
      this.ruleForm.logo = '';
      this.ifOne = '';
    },
  },
};
</script>
<style lang="less">
.base-main {
  position: relative;
  .demo-ruleForm {
    background: #fff;
  }
  .el-form-item__label {
    font-size: 12px;
  }

  .el-input__inner {
    height: 36px;
    padding: 0 10px;
    padding-right: 32px;
    font-size: 12px;
    line-height: 36px;
  }

  .line {
    height: 40px;
    line-height: 40px;
    text-align: center;
  }

  .f1-info {
    color: #ff5353;
    font-size: 12px;
    line-height: 24px;
  }

  .f2-info {
    color: #ff5353;
    font-size: 12px;
    line-height: 24px;
  }

  .phone-line {
    width: 40px;
  }
  // 上传图片
  .avatar-uploader .el-upload {
    position: relative;
    overflow: hidden;
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
  }

  .avatar-uploader .el-upload:hover {
    border-color: #409eff;
  }

  .el-upload--picture-card {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 126px;
    height: 63px;
    padding-top: 6px;
    overflow: hidden;
    color: #8c939d;
    font-size: 12px;
    line-height: 63px;
    text-align: center;
    background: #e3e3e3;

    span {
      height: 20px;
      line-height: 20px;
    }
  }

  .el-upload-list--picture-card .el-upload-list__item {
    display: block;
    width: 126px;
    height: 63px;
  }

  .avatar {
    display: block;
    width: 126px;
    height: 63px;
  }

  .picOne {
    .el-upload--picture-card {
      display: none;
    }
  }

  .maps {
    position: absolute;
    top: 0;
    left: 620px;
    padding-left: 20px;
    background: #fff;

    .bm-view {
      width: 400px;
      height: 400px;
    }

    p {
      margin-bottom: 4px;
      font-size: 12px;
      line-height: 20px;
    }

    .el-textarea {
      vertical-align: middle;
    }

    .row {
      line-height: 40px;
    }

    .el-button {
      padding: 6px 12px;
    }

    .allow {
      margin-left: 30px;
      background-color: #409eff;
      border-color: #409eff;
    }

    .make {
      position: absolute;
      top: 0;
      right: 0;
      display: inline-block;
      font-size: 12px;
      cursor: pointer;
    }

    .el-icon-place {
      color: #32abff;
    }
  }
  .danger {
    margin-bottom: 80px;
  }
  .lastItem{
    margin-bottom:120px;
  }
  .w274{
    width: 274px;
  }
}
</style>
