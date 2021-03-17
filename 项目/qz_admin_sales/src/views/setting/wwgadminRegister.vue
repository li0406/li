<template>
  <div class="wwgadmin-register">
    <div class="title">
      <h2>装企注册</h2>
    </div>
    <div class="search">
      <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm" label-position="top" :inline="true">
        <el-form-item label="城市：" prop="cs">
          <el-select v-model="ruleForm.cs" @change="getAreaList" filterable placeholder="请选择">
            <el-option
              v-for="item in cityOptions"
              :key="item.value"
              :label="item.value"
              :value="item.cid"
            >
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="区域：" prop="qx">
          <el-select v-model="ruleForm.qx" filterable placeholder="请选择">
            <el-option
              v-for="item in areaOptions"
              :key="item.area_id"
              :label="item.value"
              :value="item.area_id"
            >
            </el-option>
          </el-select>
        </el-form-item>
        <br>
        <el-form-item label="用户名:" prop="account">
          <el-input v-model="ruleForm.account" type="text" placeholder="请输入用户名" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="密码:" prop="password">
          <el-input type="password" v-model.trim="ruleForm.password" placeholder="请输入密码"  minlength="6" maxlength="18" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="确认密码:" prop="repassword">
          <el-input v-model.trim="ruleForm.repassword" type="password" placeholder="请输入确认密码" minlength="6" maxlength="18" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <br>
        <el-form-item label="公司简称:" prop="jc">
          <el-input v-model.trim="ruleForm.jc" placeholder="请输入公司简称" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="公司全称:" prop="qc">
          <el-input v-model.trim="ruleForm.qc" placeholder="请输入公司全称" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="联系人:" prop="name">
          <el-input v-model.trim="ruleForm.name" placeholder="请输入联系人" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="手机号码:" prop="tel">
          <el-input v-model.number="ruleForm.tel" placeholder="请输入手机号码" maxlength="11" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <br>
        <el-form-item label="客服QQ:" prop="qq">
          <el-input v-model.number="ruleForm.qq" maxlength="11" placeholder="请输入客服QQ" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="客服QQ2:" prop="qq2">
          <el-input v-model.number="ruleForm.qq2" maxlength="11" placeholder="请输入客服QQ2" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item label="验证码:" :error="errorMsg" prop="verify">
          <el-input v-model.trim="ruleForm.verify" placeholder="请输入验证码" maxlength="4" autocomplete="off" onkeydown="if(event.keyCode==32){return false;}"></el-input>
        </el-form-item>
        <el-form-item>
          <div class="code" @click="getCode" title="刷新">
            <img :src="url+code" alt="" height="36">
          </div>
        </el-form-item>
        <el-form-item>
          <div style="height: 46px"></div>
          <el-button type="primary" @click="submitForm('ruleForm')">确认</el-button>
          <el-button @click="resetForm('ruleForm')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script>
import { fetchAreaList } from '@/api/common'
import { fetchCheckUser, fetchRegister, fetchCityList } from '@/api/setting'

export default {
  name: 'WwgadminRegister',
  data() {
    const validatePass = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入密码'))
      } else {
        const reg = /^\w{5,18}$/
        if (reg.test(value)) {
          if (this.ruleForm.repassword !== '') {
            this.$refs.ruleForm.validateField('repassword')
          }
          callback()
        } else {
          return callback(new Error('请输入6-18位由数字、字母及字符组成的密码'))
        }
      }
    }
    const validatePass2 = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请再次输入密码'))
      } else if (value !== this.ruleForm.password) {
        callback(new Error('两次输入密码不一致!'))
      } else {
        callback()
      }
    }
    const validateUser = (rule, value, callback) => {
      if (value !== '') {
        fetchCheckUser({
          user_name: this.ruleForm.account
        }).then(res => {
          if (res.data.error_code === 0) {
            callback()
          } else {
            callback(new Error(res.data.error_msg))
          }
        })
      }
    }
    return {
      url: this.$qzconfig.base_api + '/verifyimg?ssid=',
      code: '',
      errorMsg: '',
      qqError: '',
      qq2Error: '',
      cityOptions: [{
        cid: '0', cname: '请选择', value: '请选择', true_cname: '请选择', city_name: '请选择'
      }],
      areaOptions: [{
        area_id: '0', area: '请选择', value: '请选择'
      }],
      ruleForm: {
        ssid: '',
        name: '',
        cs: '',
        qx: '',
        verify: '',
        account: '',
        password: '',
        repassword: '',
        jc: '',
        qc: '',
        tel: '',
        qq: '',
        qq2: ''
      },
      rules: {
        name: [
          { required: true, message: '请输入联系人', trigger: 'blur' }
        ],
        cs: [
          { required: true, message: '请选择城市', trigger: 'change' }
        ],
        qx: [
          { required: true, message: '请选择区域', trigger: 'change' }
        ],
        verify: [
          { required: true, message: '请输入验证码', trigger: 'blur' }
        ],
        account: [
          { required: true, message: '请输入用户名', trigger: 'blur' },
          { validator: validateUser, trigger: 'blur' }
        ],
        password: [
          { min: 6, max: 18, message: '请输入6-18位由数字、字母及字符组成的密码', trigger: 'blur' },
          { validator: validatePass, required: true, trigger: 'blur' }
        ],
        repassword: [
          { validator: validatePass2, required: true, trigger: 'blur' }
        ],
        jc: [
          { required: true, message: '请输入公司简称', trigger: 'blur' }
        ],
        qc: [
          { required: true, message: '请输入公司全称', trigger: 'blur' }
        ],
        tel: [
          { required: true, message: '请输入手机号', trigger: 'blur' },
          { pattern: /^(13|14|15|16|17|18|19)[0-9]{9}$/, message: '请输入正确手机号', trigger: 'blur' }
        ],
        qq: [
          { pattern: /^[1-9]\d{4,10}$/, message: '请输入正确qq号', trigger: 'blur' }
        ],
        qq2: [
          { pattern: /^[1-9]\d{4,10}$/, message: '请输入正确qq号', trigger: 'blur' }
        ]
      }
    }
  },
  created() {
    this.getCityList()
    this.getCode()
  },
  methods: {
    getCityList() {
      fetchCityList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.cityOptions = this.cityOptions.concat(res.data.data[0])
        }
      })
    },
    getAreaList() {
      fetchAreaList({
        cid: this.ruleForm.cs
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.areaOptions = [{
            area_id: 0, area: '请选择', value: '请选择'
          }]
          this.ruleForm.qx = '0'
          this.areaOptions = this.areaOptions.concat(res.data.data[0])
        }
      })
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        console.log(valid)
        if (valid) {
          console.log(this.ruleForm)
          fetchRegister(this.ruleForm).then(res => {
            console.log(res)
            if (res.status === 200) {
              if (res.data.error_code === 0) {
                this.$message({
                  message: '注册成功',
                  type: 'success'
                })
                this.$refs[formName].resetFields()
                this.getCode()
              } else if (res.data.error_code === 4000018) {
                this.errorMsg = ''
                this.$nextTick(() => {
                  this.errorMsg = res.data.error_msg
                })
              } else {
                this.$message.error(res.data.error_msg)
              }
            } else {
              this.$message.error('服务器错误')
            }
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
      this.areaOptions = [{
        area_id: 0, area: '请选择', value: '请选择'
      }]
      this.ruleForm.qx = ''
    },
    getCode() {
      const codeSsid = Math.floor(Math.random() * 1000)
      this.code = codeSsid
      this.ruleForm.ssid = codeSsid
    }
  }
}
</script>

<style scoped rel="stylesheet/scss" lang="scss">
  .wwgadmin-register {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;

    .el-select {
      margin-top: 0
    }
    .el-form-item{
      margin-right: 20px;
      width: 200px;
    }

    .fl {
      float: left;
    }

    .mr20 {
      margin-right: 20px;
      margin-bottom: 10px;
    }

    .red {
      color: red;
      padding: 0 5px 0 0;
    }

    .clear {
      clear: both;
    }

    .clearfix {
      &:after {
        visibility: hidden;
        display: block;
        font-size: 0;
        content: " ";
        clear: both;
        height: 0;
      }
    }

    .title {
      h2 {
        font-size: 16px;
        color: #666;
        font-weight: normal;
        margin-top: 0;
      }
    }

    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;

      span {
        display: block;
        line-height: 30px;
      }

      .tips {
        font-size: 12px;
        color: red;
      }

      .code {
        margin-top: 46px;
        cursor: pointer;
      }
    }
  }
</style>
