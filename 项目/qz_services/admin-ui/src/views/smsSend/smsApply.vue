<template>
    <div class="smsapply">
      <div class="qian-main">
        <el-form :model="applicationForm" ref="applicationForm" :rules="rules">
          <el-form-item label="签名" :label-width="formLabelWidth" prop="signatureVal">
            <el-select v-model="applicationForm.signatureVal" placeholder="请选择">
              <el-option
                v-for="item in signatureOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="短信模板" :label-width="formLabelWidth" prop="tplVal">
            <el-button type="primary" @click="showSmsTplDia" style="margin-bottom: 20px;">选择已有短信模板</el-button>
            <el-input v-model="applicationForm.tplVal" :disabled="true" type="textarea" :rows="4"></el-input>
            <div style="display: inline-block;color: #999;">当前已输入{{ biteSize }}个字</div>
          </el-form-item>
          <el-form-item label="变量值：" :label-width="formLabelWidth" prop="variableVal">
            <el-input v-model="applicationForm.variableVal" placeholder="请输入内容"></el-input>
            <div style="display: inline-block; color: #999;">模板中存在多个变量时，用英文“，”隔开</div>
          </el-form-item>
          <el-form-item label="通道网关" :label-width="formLabelWidth" prop="gateways" v-if="gatewayOption.length > 0">
            <el-checkbox-group v-model="applicationForm.gateways">
              <el-checkbox v-for="item in gatewayOption" :label="item.Slot" :key="item.Slot">{{ item.Name }}</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="发送手机号" :label-width="formLabelWidth" prop="phone">
            <el-input v-model="applicationForm.phone" placeholder="请输入内容"></el-input>
          </el-form-item>
          <el-form-item label="">
            <el-button type="primary" @click="submitForm('applicationForm')">立即发送</el-button>
          </el-form-item>
        </el-form>
      </div>
      <smsTplDialog
        :dialog-form-visible="dialogFormVisible"
        :tpl-type="tplType"
        @closeAddDia="closeDia"
      ></smsTplDialog>
    </div>
</template>

<script>
  import { fetchFilterOptions, fetchSendSmsSave } from '@/api/smsTpl'
  import smsTplDialog from './components/smsTplDialog'
  import { checkPhoneNum } from '@/utils/index'
  export default {
    name: 'smsApply',
    components: {
      smsTplDialog
    },
    data() {
      const validateSignatureVal = (rule, value, callback) => {
        if (parseInt(value) <= 0) {
          callback(new Error('请选择签名'));
        } else {
          callback();
        }
      }
      return {
        dialogFormVisible: false,
        formLabelWidth: '100px',
        tplType: [{
          value: '0',
          label: '请选择'
        }],
        gatewayOption: [],
        applicationForm: {
          name: '',
          gateways: [],
          signatureVal: '0',
          variableVal: '',
          phone: '',
          tplVal: '',
          smsTplId: ''
        },
        signatureOptions: [{
          value: '0',
          label: '请选择'
        }],
        rules: {
          signatureVal: [
            { required: true, validator: validateSignatureVal, trigger: 'change' },
          ],
          tplVal: [
            { required: true, message: '请选择短信模板', trigger: 'change' }
          ],
          gateways: [
            { required: true, message: '请选择通道网关', trigger: 'change' }
          ],
          phone: [
            { required: true, message: '请输入手机号码', trigger: 'change' }
          ]
        }
      }
    },
    computed: {
      'biteSize'() {
        return this.applicationForm.tplVal.length
      }
    },
    created() {
      this.getFilterOptions()
    },
    methods: {
      getFilterOptions() {
        fetchFilterOptions().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            // if(res.data.data.gateway_list && res.data.data.gateway_list.length > 0) {
            //   res.data.data.gateway_list.forEach(item => {
            //     this.gatewayOption.push(item)
            //   })
            // }
            if(res.data.data.type_list && res.data.data.type_list.length > 0) {
              res.data.data.type_list.forEach(item => {
                this.tplType.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
            if(res.data.data.sign_list && res.data.data.sign_list.length > 0) {
              res.data.data.sign_list.forEach(item => {
                this.signatureOptions.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      handleSave() {
        if(!checkPhoneNum(this.applicationForm.phone)) {
          this.$message.error('手机号格式不正确')
          return
        }
        fetchSendSmsSave({
          signature: this.applicationForm.signatureVal,
          content: this.applicationForm.tplVal,
          template_id: this.applicationForm.smsTplId,
          params: this.applicationForm.variableVal,
          gateway: this.applicationForm.gateways.join(','),
          tel: this.applicationForm.phone
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '发送成功',
              type: 'success'
            })
          }else{
            this.$message.error(res.data.error_code)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      submitForm(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.handleSave()
          } else {
            console.log('error submit!!')
            return false
          }
        })
      },
      showSmsTplDia() {
        this.dialogFormVisible = true
      },
      closeDia(val) {
        console.log(val)
        // TODO 会有无通道网关情况
        this.dialogFormVisible = false
        if(val && val.content) {
          this.applicationForm.tplVal = val.content
          this.applicationForm.smsTplId = val.id
        }
        if(val && val.gateway) {
          this.gatewayOption = []
          for(let p in val.gateway) {
            this.gatewayOption.push(val.gateway[p])
          }
        }
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .smsapply{
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .el-dialog__header{
      border-bottom: 1px dashed #CCC;
    }
    .dia_table{
      width: 100%;
    }
    .dia_table td{
      line-height: 2.5;
    }
    .fix_letter_spance{
      letter-spacing: 3px;
    }
    .el-input{
      display: inline-block;
      width: 400px;
    }
    .el-textarea{
      display: block;
      width: 400px;
    }
  }
</style>
