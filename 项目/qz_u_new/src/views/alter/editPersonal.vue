<template>
  <div class="editPersonal-contains" v-loading="loading">
    <el-card class="box-card">
      <div class="clearfix">
        <span>编辑</span>
      </div>
    </el-card>
    <el-form
      :model="ruleForm"
      :rules="rules"
      ref="ruleForm"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-form-item v-loading="picLoading" label="头像：" prop="logo">
        <el-col class="logo-item">
          <cropper :imgUrl="ruleForm.logo" @getHead="getHead"></cropper>
        </el-col>
        <el-col :span="6">
          <el-popover placement="right-start" width="400" trigger="hover">
            <div class="popcontent">
              <div class="img">
                <img src="@/assets/people.png" alt />
              </div>
              <div class="content">
                <span>头像上传要求(参照左图标准头像)</span>
                <br />1.人物免冠正面彩色半身照；
                <br />2.人物面部完整，无遮挡，光线充足；
                <br />3.衣物色彩与背景对比明显，清晰度高；
                <br />4.照片不含有水印，联系方式等广告信息；
                <br />5.其他(包含盗用明星照片等)
              </div>
            </div>
            <span class="tips" slot="reference">上传头像要求</span>
          </el-popover>
        </el-col>
      </el-form-item>
      <el-form-item label="姓名：" prop="nick_name">
        <el-col class="form-item">
           <el-input
            v-model="ruleForm.nick_name"
            oninput="value=value.replace(/[^\a-zA-Z\u4E00-\u9FA5]/g,'')"
            maxlength="8"
            placeholder="请输入姓名"
          ></el-input>
        </el-col>
      </el-form-item>
      <el-form-item label="职位：" prop="position">
        <el-col class="form-item">
          <el-select v-model="ruleForm.position" disabled placeholder="请选择">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="工作经验：" prop="experience">
        <el-col class="form-item">
          <el-select v-model="ruleForm.experience" placeholder="请选择">
            <el-option
              v-for="item in works"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-col>
      </el-form-item>
      <el-row v-if="isMore">
        <el-col :span="4">
          <el-form-item label="设计收费：" prop="charge">
            <el-input v-model="ruleForm.charge" placeholder="400～800"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="pay-title">元/平米</div>
        </el-col>
      </el-row>
      <el-form-item label="擅长风格：" prop="checkedFg" v-if="isMore" key="checkedFg">
        <el-checkbox
          :indeterminate="isIndeterminateFg"
          v-model="checkAll"
          @change="handleCheckAllChange"
        >全选</el-checkbox>
        <div style="margin: 15px 0;"></div>
        <el-checkbox-group v-model="ruleForm.checkedFg" @change="handleCheckedCitiesChange">
          <el-checkbox v-for="item in fg" :label="item.id" :key="item.id">{{item.name}}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="擅长领域：" prop="checkedLy" v-if="isMore" key="checkedLy">
        <el-checkbox
          :indeterminate="isIndeterminateLy"
          v-model="checkAllLy"
          @change="handleCheckAllChangeLy"
        >全选</el-checkbox>
        <div style="margin: 15px 0;"></div>
        <el-checkbox-group v-model="ruleForm.checkedLy" @change="handleCheckedCitiesChangeLy">
          <el-checkbox v-for="item in ly" :label="item.id" :key="item.id">{{item.name}}</el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="荣誉奖励：">
        <el-col :span="6">
          <el-input
            type="textarea"
            :rows="5"
            maxlength="600"
            placeholder="请输入内容"
            v-model="ruleForm.honor"
          ></el-input>
          <span class="item-words">{{oddC}}/600</span>
        </el-col>
      </el-form-item>
      <el-form-item label="代表作品：" v-if="isMore">
        <el-col :span="6">
          <el-input
            type="textarea"
            :rows="5"
            maxlength="600"
            placeholder="请输入内容"
            v-model="ruleForm.works"
          ></el-input>
          <span class="item-words">{{oddD}}/600</span>
        </el-col>
      </el-form-item>
      <el-form-item label="设计理念：" v-if="isMore">
        <el-col :span="6">
          <el-input
            type="textarea"
            :rows="5"
            maxlength="600"
            placeholder="请输入内容"
            v-model="ruleForm.concept"
          ></el-input>
          <span class="item-words">{{oddE}}/600</span>
        </el-col>
      </el-form-item>
      <el-form-item label="个人简介：">
        <el-col :span="6">
          <el-input
            type="textarea"
            :rows="5"
            maxlength="600"
            placeholder="请输入内容"
            v-model="ruleForm.profile"
          ></el-input>
          <span class="item-words">{{oddF}}/600</span>
        </el-col>
      </el-form-item>
      <el-form-item class="last-btn">
        <el-button type="danger" @click="submitForm('ruleForm')">保存</el-button>
        <el-button plain @click="resetForm('ruleForm')">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import alter from '@/api/alter';
import QZ_CONFIG from '@/utils/config';

export default {
  components: {
    cropper: () => import('../workers-list/components/cropper'),
  },
  name: 'EditPersonal',
  data() {
    const validatelogo = (rule, value, callback) => {
      if (this.ruleForm.logo === '') {
        callback(new Error('请上传头像'));
      } else {
        callback();
      }
    };

    const validateLy = (rule, value, callback) => {
      if (this.ruleForm.checkedLy.length < 1) {
        callback(new Error('请选择擅长领域'));
      } else {
        callback();
      }
    };

    const validateFg = (rule, value, callback) => {
      if (this.ruleForm.checkedFg.length < 1) {
        callback(new Error('请选择擅长风格'));
      } else {
        callback();
      }
    };
    const validateCharge = (rule, value, callback) => {
      const reg = /^[+]{0,1}(\d+)$/;
      if (this.ruleForm.charge === '') {
        callback(new Error('请填写设计收费'));
      } else if (value && !reg.test(value)) {
        callback(new Error('设计收费价格必须是纯数字'));
      } else {
        callback();
      }
    };
        const validateName = (rule, value, callback) => {
      if (this.ruleForm.nick_name.length < 1) {
        callback(new Error('请填写姓名'));
      } else if (this.ruleForm.nick_name.length < 2) {
        callback(new Error('姓名不得少于2个字'));
      } else {
        callback();
      }
    };
    return {
      options: [
        {
          value: '',
          label: '请选择职位',
        },
        {
          value: '1',
          label: '客服',
        },
        {
          value: '2',
          label: '设计师',
        },
        {
          value: '3',
          label: '首席设计师',
        },
        {
          value: '4',
          label: '设计总监',
        },
      ],
      works: [
        {
          value: '',
          label: '请选择工作经验',
        },
        {
          value: '1',
          label: '应届',
        },
        {
          value: '2',
          label: '1年',
        },
        {
          value: '3',
          label: '2年',
        },
        {
          value: '4',
          label: '3-5年',
        },
        {
          value: '5',
          label: '5-8年',
        },
        {
          value: '6',
          label: '8-10年',
        },
        {
          value: '7',
          label: '10年以上',
        },
      ],
      fg: [
        { id: '现代简约', name: '现代简约' },
        { id: '欧式风格', name: '欧式风格' },
        { id: '中式风格', name: '中式风格' },
        { id: '古典风格', name: '古典风格' },
        { id: '田园风格', name: '田园风格' },
        { id: '地中海风格', name: '地中海风格' },
        { id: '美式风格', name: '美式风格' },
        { id: '混搭风格', name: '混搭风格' },
        { id: '其他', name: '其他' },
      ],
      // 风格勾选
      checkAll: false,
      isIndeterminateFg: true,
      ly: [
        { id: '住宅公寓', name: '住宅公寓' },
        { id: '写字楼', name: '写字楼' },
        { id: '别墅', name: '别墅' },
        { id: '专卖展示店', name: '专卖展示店' },
        { id: '酒店宾馆', name: '酒店宾馆' },
        { id: '餐饮酒吧', name: '餐饮酒吧' },
        { id: '歌舞迪厅', name: '歌舞迪厅' },
        { id: '其他', name: '其他' },
      ],
      // 领域勾选
      checkAllLy: false,
      isIndeterminateLy: true,
      // 图片上传
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
      ruleForm: {
        logo: '',
        nick_name: '',
        position: '',
        experience: '',
        charge: '',
        checkedFg: [],
        checkedLy: [],
        honor: '',
        works: '',
        concept: '',
        profile: '',
      },
      rules: {
        logo: [{ required: true, validator: validatelogo, trigger: 'change' }],
        nick_name: [{ required: true, validator: validateName, trigger: 'blur' }],
        position: [{ required: true, message: '请选择职位', trigger: 'blur' }],
        experience: [{ required: true, message: '请选择工作经验', trigger: 'blur' }],
        charge: [{ required: true, validator: validateCharge, trigger: 'blur' }],
        checkedFg: [{ required: true, validator: validateFg, trigger: 'change' }],
        checkedLy: [{ required: true, validator: validateLy, trigger: 'change' }],
      },
      account: '',
      loading: false,
      isMore: false,
    };
  },
  computed: {
    oddC() {
      return this.ruleForm.honor.length;
    },
    oddD() {
      return this.ruleForm.works.length;
    },
    oddE() {
      return this.ruleForm.concept.length;
    },
    oddF() {
      return this.ruleForm.profile.length;
    },
  },
  created() {
    const account = this.$route.query.account;
    this.account = account;
    this.getData(account);
  },
  methods: {
    getData() {
      this.loading = true;
      alter.getCenter().then((res) => {
        if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
          this.loading = false;
          const datas = res.data.data;
          const positionData = this.options.find(function(item) {
            return item.label === datas.position;
          });
          if (positionData.value !== '1') {
            // 区分客服与设计
            this.isMore = true;
            this.ruleForm.checkedFg = datas.fengge;
            this.ruleForm.checkedLy = datas.lingyu;
            this.ruleForm.works = datas.works;
            this.ruleForm.concept = datas.concept;
            this.ruleForm.charge = datas.charge;
          }
          const workData = this.works.find(function(item) {
            return item.label === datas.experience;
          });
          this.ifOne = 'picOne';
          const obj = { url: datas.logo };
          this.fileList.push(obj);
          this.ruleForm.logo = datas.logo;
          this.ruleForm.nick_name = datas.nick_name;
          this.ruleForm.position = positionData.value;
          this.ruleForm.experience = workData.value;
          this.ruleForm.honor = datas.honor;
          this.ruleForm.profile = datas.profile;
        } else {
          this.loading = false;
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    submitForm(formName) {
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const data = {
            account: {
              logo: this.ruleForm.logo,
              nick_name: this.ruleForm.nick_name,
              position: this.ruleForm.position,
              experience: this.ruleForm.experience,
              honor: this.ruleForm.honor,
              profile: this.ruleForm.profile,
              account: this.account,
            },
            designer: {
              charge: this.ruleForm.charge,
              fengge: this.ruleForm.checkedFg,
              lingyu: this.ruleForm.checkedLy,
              works: this.ruleForm.works,
              concept: this.ruleForm.concept,
            },
          };
          if (!this.isMore) {
            delete data.designer;
          }
          alter.accountup(data).then((res) => {
            // eslint-disable-next-line radix
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '编辑成功',
                type: 'success',
              });
              this.$router.push({
                path: '/personal',
              });
            } else {
              this.$message({
                message: res.data.error_msg,
                type: 'error',
              });
            }
          });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetForm(formName) {
      this.$refs[formName].resetFields();
      this.$router.push({
        path: '/personal',
      });
    },
    handleCheckAllChange(val) {
      this.ruleForm.checkedFg = [];
      this.fg.forEach((item) => {
        this.ruleForm.checkedFg.push(item.id);
      });
      this.ruleForm.checkedFg = val ? this.ruleForm.checkedFg : [];
      this.isIndeterminateFg = false;
    },
    handleCheckedCitiesChange(value) {
      const checkedCount = value.length;
      this.checkAll = checkedCount === this.fg.length;
      this.isIndeterminateFg = checkedCount > 0 && checkedCount < this.fg.length;
    },
    handleCheckAllChangeLy(val) {
      this.ruleForm.checkedLy = [];
      this.ly.forEach((item) => {
        this.ruleForm.checkedLy.push(item.id);
      });
      this.ruleForm.checkedLy = val ? this.ruleForm.checkedLy : [];
      this.isIndeterminateLy = false;
    },
    handleCheckedCitiesChangeLy(value) {
      const checkedCount = value.length;
      this.checkAllLy = checkedCount === this.ly.length;
      this.isIndeterminateLy = checkedCount > 0 && checkedCount < this.ly.length;
    },
    // 上传图片
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
    // 更改
    getHead(data) {
      this.ruleForm.logo = data.imgUrl;
    },
  },
};
</script>

<style lang="less">
.editPersonal-contains {
  background: #fff;
  .demo-ruleForm {
    margin-top: 20px;
    margin-left: 30px;
  }
  .tips {
    color: rgba(233, 71, 71, 1);
    font-weight: 400;
    font-size: 12px;
    cursor: pointer;
  }
  .logo-item {
    width: 120px;
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
  .pay-title {
    margin-left: 10px;
    color: rgba(48, 49, 51, 1);
    font-weight: 400;
    font-size: 12px;
    line-height: 42px;
  }
  .form-item {
    width: 240px;
  }
  .el-select {
    width: 100%;
  }
  .el-checkbox__label {
    font-size: 12px;
  }
  .el-textarea {
    font-size: 12px;
  }
  .last-btn {
    margin-bottom: 200px;
  }
  .item-words {
    float: right;
    font-size: 12px;
  }
  .el-checkbox__input.is-checked + .el-checkbox__label {
    color: rgba(255, 83, 83, 1);
  }
  .el-checkbox__input.is-checked .el-checkbox__inner,
  .el-checkbox__input.is-indeterminate .el-checkbox__inner {
    background-color: rgba(255, 83, 83, 1);
    border-color: rgba(255, 83, 83, 1);
  }
  // 上传图片
  .avatar-uploader .el-upload {
    position: relative;
    overflow: hidden;
    border: 1px dashed #d9d9d9;
    border-radius: 50%;
    cursor: pointer;
  }

  .avatar-uploader .el-upload:hover {
    border-color: #409eff;
  }

  .el-icon-upload2 {
    color: #fff;
    font-size: 20px;
  }
  .el-upload--picture-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 60px;
    height: 60px;
    overflow: hidden;
    color: #fff;
    font-size: 10px;
    line-height: 63px;
    text-align: center;
    background: rgba(0, 0, 0, 0.5);
    span {
      height: 20px;
      line-height: 20px;
    }
  }

  .el-upload-list--picture-card .el-upload-list__item {
    display: block;
    width: 60px;
    height: 60px;
    border-radius: 50%;
  }

  .avatar {
    display: block;
    width: 60px;
    height: 60px;
    border-radius: 50%;
  }

  .picOne {
    .el-upload--picture-card {
      display: none;
    }
  }
  .popcontent {
    .img {
      float: left;
      width: 80px;
      height: 80px;
    }
    .content {
      float: right;
    }
  }
  .el-card.is-always-shadow {
    box-shadow: none;
  }
  .el-card {
    border: none;
    border-bottom: 2px solid #e4e7ed;
  }
}
</style>
<style lang="less" scoped>
.popcontent {
  width: 340px;
  .img {
    float: left;
    width: 80px;
    height: 80px;
    img {
      display: block;
      width: 80px;
      margin-top: 20px;
    }
  }
  .content {
    float: right;
    color: rgba(144, 147, 153, 1);
    font-weight: 400;
    font-size: 12px;
    line-height: 28px;
    span {
      color: rgba(48, 49, 51, 1);
      font-weight: 400;
      font-size: 14px;
      line-height: 40px;
    }
  }
}
</style>
