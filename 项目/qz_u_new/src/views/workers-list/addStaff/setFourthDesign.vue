<!--
 * @Author: your name
 * @Date: 2020-06-29 16:11:43
 * @LastEditTime: 2020-07-10 10:07:12
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_u_new\src\views\workers-list\addStaff\setFourthDesign.vue
-->
<!--
 * @Author: your name
 * @Date: 2020-06-28 10:25:46
 * @LastEditTime: 2020-06-29 15:24:05
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_u_new\src\views\workers-list\addStaff\setFourth.vue
-->
<template>
  <div>
    <div>
      <h3>个性化信息</h3>
    </div>
    <div class="mes-con">
      <el-form :model="ruleForm"
               :rules="rules"
               ref="ruleForm"
               label-width="100px"
               class="demo-ruleForm">
        <el-form-item v-loading="picLoading"
                      label="头像:"
                      prop="logo">
          <el-col class="logo-item">
            <!--
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
              <i class="el-icon-upload2 avatar-uploader-icon" />
              <span>上传头像</span>
            </el-upload>
            -->
            <cropper :imgUrl="ruleForm.logo"
                     @getHead="getHead"></cropper>
          </el-col>
          <el-col :span="6">
            <el-popover placement="right-start"
                        width="400"
                        trigger="hover">
              <div class="popcontent">
                <div class="img">
                  <img src="@/assets/people.png"
                       alt />
                </div>
                <div class="content">
                  <span>头像上传要求(参照左图标准头像)</span>
                  <br />1.人物免冠正面彩色半身照； <br />2.人物面部完整，无遮挡，光线充足；
                  <br />3.衣物色彩与背景对比明显，清晰度高； <br />4.照片不含有水印，联系方式等广告信息；
                  <br />5.其他(包含盗用明星照片等)
                </div>
              </div>
              <span class="tips"
                    slot="reference">上传头像要求</span>
            </el-popover>
          </el-col>
        </el-form-item>
        <el-form-item label="姓名："
                      prop="name">
          <el-col class="form-item">
            <el-input v-model="ruleForm.name"
                      maxlength="8"
                      :oninput="ruleForm.name=ruleForm.name.replace(/[^\a-zA-Z\u4E00-\u9FA5]/g,'')"
                      placeholder="请输入姓名"></el-input>
          </el-col>
        </el-form-item>
        <!-- <el-form-item label="职位：" prop="jobs">
          <el-col class="form-item">
            <el-select v-model="ruleForm.jobs" placeholder="请选择职位">
              <el-option
                v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-col>
        </el-form-item>-->
        <el-form-item label="工作经验："
                      prop="works">
          <el-col class="form-item">
            <el-select v-model="ruleForm.works"
                       placeholder="请选择工作经验">
              <el-option v-for="item in works"
                         :key="item.value"
                         :label="item.label"
                         :value="item.value"></el-option>
            </el-select>
          </el-col>
        </el-form-item>
        <el-row>
          <el-col :span="4">
            <el-form-item label="设计收费"
                          prop="charge">
              <el-input v-model="ruleForm.charge"
                        placeholder="请填写设计收费"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="2">
            <div class="pay-title">元/平米</div>
          </el-col>
        </el-row>
        <el-form-item label="擅长风格："
                      prop="checkedFg">
          <el-checkbox :indeterminate="isIndeterminateFg"
                       v-model="checkAll"
                       @change="handleCheckAllChange">全选</el-checkbox>
          <div style="margin: 15px 0;"></div>
          <el-checkbox-group v-model="ruleForm.checkedFg"
                             @change="handleCheckedCitiesChange">
            <el-checkbox v-for="item in fg"
                         :label="item.id"
                         :key="item.id">{{ item.name }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="擅长领域："
                      prop="checkedLy">
          <el-checkbox :indeterminate="isIndeterminateLy"
                       v-model="checkAllLy"
                       @change="handleCheckAllChangeLy">全选</el-checkbox>
          <div style="margin: 15px 0;"></div>
          <el-checkbox-group v-model="ruleForm.checkedLy"
                             @change="handleCheckedCitiesChangeLy">
            <el-checkbox v-for="item in ly"
                         :label="item.id"
                         :key="item.id">{{ item.name }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="荣誉奖励：">
          <el-col :span="6">
            <el-input type="textarea"
                      :rows="5"
                      maxlength="600"
                      placeholder="请输入内容"
                      v-model="ruleForm.honor"></el-input>
            <span class="item-words">{{ ruleForm.honor.length }}/600</span>
          </el-col>
        </el-form-item>
        <el-form-item label="代表作品：">
          <el-col :span="6">
            <el-input type="textarea"
                      :rows="5"
                      maxlength="600"
                      placeholder="请输入内容"
                      v-model="ruleForm.artwork"></el-input>
            <span class="item-words">{{ ruleForm.artwork.length }}/600</span>
          </el-col>
        </el-form-item>
        <el-form-item label="设计理念：">
          <el-col :span="6">
            <el-input type="textarea"
                      :rows="5"
                      maxlength="600"
                      placeholder="请输入内容"
                      v-model="ruleForm.content"></el-input>
            <span class="item-words">{{ ruleForm.content.length }}/600</span>
          </el-col>
        </el-form-item>
        <el-form-item label="个人简介：">
          <el-col :span="6">
            <el-input type="textarea"
                      :rows="5"
                      maxlength="600"
                      placeholder="请输入内容"
                      v-model="ruleForm.sketch"></el-input>
            <span class="item-words">{{ ruleForm.sketch.length }}/600</span>
          </el-col>
        </el-form-item>
        <el-form-item class="last-btn">
          <el-button class="can-btn-sty"
                     @click="goBack()">上一步</el-button>
          <el-button type="danger"
                     @click="complete()">完成</el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>
<script>
import QZ_CONFIG from '@/utils/config';
import api from '@/api/staff';

export default {
  components: {
    cropper: () => import('../components/cropper'),
  },
  name: 'Message',
  data () {
    const validatelogo = (rule, value, callback) => {
      if (this.ruleForm.logo === '') {
        callback(new Error('请上传头像'));
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
    const validateLy = (rule, value, callback) => {
      if (this.ruleForm.checkedLy.length < 1) {
        callback(new Error('请选择擅长领域'));
      } else {
        callback();
      }
    };
    return {
      bul: 0,
      options: [
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
        name: '',
        // jobs: '',
        honor: '',
        works: '',
        charge: '',
        checkedFg: [],
        checkedLy: [],
        artwork: '',
        content: '',
        sketch: '',
      },
      rules: {
        logo: [{ required: true, validator: validatelogo, trigger: 'change' }],
        name: [{ required: true, message: '请输入姓名', trigger: 'blur' }],
        // jobs: [{ required: true, message: '请选择', trigger: 'blur' }],
        honor: [{ required: true, message: '请选择', trigger: 'blur' }],
        works: [{ required: true, message: '请选择', trigger: 'blur' }],
        charge: [{ required: true, message: '请填写设计收费', trigger: 'blur' }],
        checkedFg: [{ required: true, validator: validateFg, trigger: 'change' }],
        checkedLy: [{ required: true, validator: validateLy, trigger: 'change' }],
      },
    };
  },

  methods: {
    // getData(url) {
    //   this.ruleForm.logo = url;
    //   this.ifOne = 'picOne';
    //   const obj = { url };
    //   this.fileList.push(obj);
    // },
    submitForm (formName) {
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // console.log(this.ruleForm);
        } else {
          // console.log('error submit!!');
          return false;
        }
      });
    },
    resetForm (formName) {
      this.$refs[formName].resetFields();
    },
    handleCheckAllChange (val) {
      this.ruleForm.checkedFg = [];
      this.fg.forEach((item) => {
        this.ruleForm.checkedFg.push(item.id);
      });
      this.ruleForm.checkedFg = val ? this.ruleForm.checkedFg : [];
      this.isIndeterminateFg = false;
    },
    handleCheckedCitiesChange (value) {
      const checkedCount = value.length;
      this.checkAll = checkedCount === this.fg.length;
      this.isIndeterminateFg = checkedCount > 0 && checkedCount < this.fg.length;
    },
    handleCheckAllChangeLy (val) {
      this.ruleForm.checkedLy = [];
      this.ly.forEach((item) => {
        this.ruleForm.checkedLy.push(item.id);
      });
      this.ruleForm.checkedLy = val ? this.ruleForm.checkedLy : [];
      this.isIndeterminateLy = false;
    },
    handleCheckedCitiesChangeLy (value) {
      const checkedCount = value.length;
      this.checkAllLy = checkedCount === this.ly.length;
      this.isIndeterminateLy = checkedCount > 0 && checkedCount < this.ly.length;
    },
    // 上传图片
    handleUploadSuccess (res) {
      this.ruleForm.logo = `${QZ_CONFIG.OSS_IMG_HOST}${res.data.img_path}`;
      this.ifOne = 'picOne';
    },
    beforeAvatarUpload (file) {
      const isJPG = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
      if (!isJPG) {
        this.$message.error('请上传png、jpg、jpeg图片');
      }
      return isJPG;
    },
    saveFourthData () {
      this.$locSet('setFourth', {
        account: {
          logo: this.ruleForm.logo, //  头像
          nick_name: this.ruleForm.name, //  名字
          // position: this.ruleForm.jobs, //  职位
          experience: this.ruleForm.works, //  工作经验
          account: '',
          profile: this.ruleForm.sketch, // 个人简介
          honor: this.ruleForm.honor, // 荣誉奖励
        },
        designer: {
          charge: this.ruleForm.charge, // 设计收费
          fengge: this.ruleForm.checkedFg, // 风格
          lingyu: this.ruleForm.checkedLy, // 领域
          works: this.ruleForm.artwork, // 代表作品
          concept: this.ruleForm.content, // 设计理念
        },
      });
    },
    //  上一步存入第四步的数据
    goBack () {
      this.saveFourthData();
      this.$router.push({
        path: '/set-third',
      });
    },
    //  获取四个阶段用户所选择的数据
    getAllstageData () {
      const setFirst = this.$locGet('setFirst');
      const setSecond = this.$locGet('setSecond');
      const setThird = this.$locGet('setThird');
      const setFourth = this.$locGet('setFourth');
      return {
        setFirst,
        setSecond,
        setThird,
        setFourth,
      };
    },
    //  清除四个阶段用户所选择的数据
    clearAllstageData () {
      this.$locRemove('setFirst');
      this.$locRemove('setSecond');
      this.$locRemove('setThird');
      this.$locRemove('setFourth');
    },
    //  点击完成
    complete () {
      if (this.ruleForm.logo === '') {
        this.$message.error('请上传头像');
        return;
      }
      if (this.ruleForm.name === '') {
        this.$message.error('请填写姓名');
        return;
      }
      if (this.ruleForm.name !== '' && this.ruleForm.name.length < 2) {
        this.$message.error('姓名不得少于2个字');
        return;
      }
      if (this.ruleForm.works === '') {
        this.$message.error('请填选择工作经验');
        return;
      }
      if (this.ruleForm.charge === '') {
        this.$message.error('请填写设计收费');
        return;
      }
      this.saveFourthData(); //  保存第四阶段数据
      const allParams = this.getAllstageData();
      const params = {
        account: {
          // 基础信息
          position: allParams.setFirst.role, // 职位
          nick_name: allParams.setFourth.account.nick_name, // 姓名
          experience: allParams.setFourth.account.experience, //  工作经验
          honor: allParams.setFourth.account.honor, // 荣誉
          profile: allParams.setFourth.account.profile, // 简介
          account: allParams.setFirst.tel, // 帐号
          logo: allParams.setFourth.account.logo, // logo
        },
        role: allParams.setThird.selRoleId, //  角色权限数组,
        designer: {
          // 设计师
          fengge: allParams.setFourth.designer.fengge, //  擅长风格
          lingyu: allParams.setFourth.designer.lingyu, //  擅长领域
          works: allParams.setFourth.designer.works, //  代表作品
          concept: allParams.setFourth.designer.concept, //  设计理念
          charge: allParams.setFourth.designer.charge, //  设计收费
        },
      };
      // console.log(JSON.stringify(params));
      this.AddAccountUp(params);
    },
    //  员工入职离职
    accountstateup (account) {
      const params = {
        account,
      };
      api
        .accountstateup(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败
            this.$message({
              message: '入职成功！',
              type: 'success',
            });
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  添加绑定员工
    AddAccountUp (params) {
      api
        .accountup(params)
        .then((res) => {
          if (res.data.error_code === 0) {
            // 0成功 其余失败

            //  添加成功清除四个阶段的数据 并跳转到列表页

            if (this.bul) {
              // 入职
              this.accountstateup(params.account.account);
            } else {
              //  添加
              this.$message({
                message: '添加成功',
                type: 'success',
              });
            }
            this.clearAllstageData();
            this.$locRemove('bul');
            this.$router.push({
              path: '/workers-list',
            });
          } else {
            this.$message.error(res.data.error_msg);
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    remove () {
      this.ruleForm.logo = '';
      this.ifOne = '';
    },
    intercept () {
      // if (this.ruleForm.jobs === '') {
      //   this.$message.error('请填选择职位');
      // }
      // if(this.ruleForm.checkedFg===''){
      //   this.$message.error('请选择擅长风格');
      // }
      // if(this.ruleForm.checkedLy===''){
      //   this.$message.error('请选择擅长领域');
      // }
      // if (this.ruleForm.honor === '') {
      //   this.$message.error('请填写荣誉奖励');
      // }
      // if (this.ruleForm.artwork === '') {
      //   this.$message.error('请填写代表作品');
      // }
      // if (this.ruleForm.content === '') {
      //   this.$message.error('请填写设计理念');
      // }
      // if (this.ruleForm.sketch === '') {
      //   this.$message.error('请填写个人简介');
      // }
    },
    getHead (data) {
      this.ruleForm.logo = data.imgUrl;
    },
  },
  created () {
    const params = this.$locGet('setFourth');
    this.bul = this.$locGet('bul');
    const setFirstObj = this.$locGet('setFirst');
    // console.log(params);
    this.ruleForm.account = setFirstObj.account;
    if (params === null) {
      this.ifOne = '';
      this.fileList = [];
      this.ruleForm = {
        logo: '',
        name: '',
        // jobs: '',
        honor: '',
        works: '',
        charge: '',
        checkedFg: [],
        checkedLy: [],
        artwork: '',
        content: '',
        sketch: '',
      };
    } else {
      // this.getData(params.account.logo);
      this.ruleForm.logo = params.account.logo; // 头像
      this.ruleForm.name = params.account.nick_name; //  名字
      // this.ruleForm.jobs = params.account.position;
      this.ruleForm.works = params.account.experience; //  工作经验
      this.ruleForm.honor = params.account.honor; // 荣誉奖励
      this.ruleForm.sketch = params.account.profile; //  个人简介
      this.ruleForm.checkedFg = params.designer.fengge; // 擅长风格
      this.ruleForm.checkedLy = params.designer.lingyu; // 擅长领域
      if (params.account.logo === '') {
        this.ifOne = '';
        this.fileList = [];
      }
      if (params.designer.works === undefined) {
        this.ruleForm.artwork = '';
      } else {
        this.ruleForm.artwork = params.designer.works; //  代表作
      }
      if (params.designer.concept === undefined) {
        this.ruleForm.content = '';
      } else {
        this.ruleForm.content = params.designer.concept; //  设计理念
      }
      if (params.designer.charge === undefined) {
        this.ruleForm.charge = '';
      } else {
        this.ruleForm.charge = params.designer.charge; //  设计价格
      }
    }
  },
};
</script>
<style lang="less">
.mes-con {
  .demo-ruleForm {
    margin-top: 20px;
  }
  width: 100%;
  .tips {
    color: rgba(233, 71, 71, 1);
    font-weight: 400;
    font-size: 12px;
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
    margin-top: 10px;
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
}
</style>
<style lang="less" scoped>
/* 上一步按钮样式覆盖 */
.can-btn-sty {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.can-btn-sty:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
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
