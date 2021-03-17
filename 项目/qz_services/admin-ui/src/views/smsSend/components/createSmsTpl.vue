<template>
    <div class="create-sms-tpl">
      <div class="qian-main">
        <el-form :model="tplForm" ref="tplForm" :rules="rules" label-width="600px">
          <el-form-item label="模板内容:" :label-width="formLabelWidth" prop="content">
            <el-input
              v-model="tplForm.content"
              auto-complete="off"
              type="textarea"
              :rows="4"
            ></el-input>
          </el-form-item>
          <div class="tpl-content-note">
            <p>当前已输入<span class="red">{{ contentSize }}</span>个字</p>
            <p>示例：尊敬的齐装网会员：您已成功预约{变量}；联系人：{变量}；电话：{变量}。</p>
            <p>备注：短信模板中的{变量}，在实际发送短信时，会用变量值进行填充。</p>
          </div>
          <el-form-item label="使用场景:" :label-width="formLabelWidth" prop="sence">
            <el-input v-model="tplForm.sence" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="签名:" :label-width="formLabelWidth" prop="signatureVal">
            <el-select v-model="tplForm.signatureVal" placeholder="请选择">
              <el-option
                v-for="item in signatureOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="模板类型:" :label-width="formLabelWidth" prop="tplTypeVal">
            <el-select v-model="tplForm.tplTypeVal" @change="clearGatewayVal" placeholder="请选择">
              <el-option
                v-for="item in tplTypeOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="通道网关:" :label-width="formLabelWidth" prop="gatewayList" v-if="tplForm.tplTypeVal > 0 && hasGateway">
            <table>
              <tr v-for="(item, index) in gatewayOptions" v-if="item.type_list && tplForm.tplTypeVal > 0 && item.type_list.indexOf(tplForm.tplTypeVal) > -1" class="gateways">
                <td>
                  <el-checkbox v-model="tplForm.gatewayList[index].checked" :label="item.id">{{ item.name }}</el-checkbox>
                </td>
                <td><el-input v-model="tplForm.gatewayList[index].third_temp_id" placeholder="渠道商模板ID" class="gatewayinput"></el-input></td>
                <td><el-checkbox v-model="tplForm.gatewayList[index].prepared" :label="item.id">已报备</el-checkbox></td>
              </tr>
            </table>
          </el-form-item>
          <el-form-item label="是否启用:" :label-width="formLabelWidth" prop="startUp">
            <el-switch
              v-model="tplForm.startUp"
              active-value="1"
              inactive-value="0">
            </el-switch>
          </el-form-item>
          <el-form-item label="是否脱敏:" :label-width="formLabelWidth" prop="secret">
            <el-switch
              v-model="tplForm.secret"
              active-value="1"
              inactive-value="0">
            </el-switch>
            <span style="margin-left: 10px;">选择脱敏后 发送号码中间打*</span>
          </el-form-item>
          <el-form-item label="" prop="name">
            <el-button type="primary" @click="submitForm('tplForm')">确认</el-button>
            <el-button @click="goBack('tplForm')">返回</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
</template>

<script>
  import { fetchFilterTplInfo, fetchTplSave } from '@/api/smsTpl'
  export default {
    name: 'createSmsTpl',
    data() {
      const validateSignature = (rule, value, callback) => {
        if (value <= 0) {
          callback(new Error('请选择签名'));
        } else {
          callback();
        }
      }
      const validateTpl = (rule, value, callback) => {
        if (value <= 0) {
          callback(new Error('请选择模板类型'));
        } else {
          callback();
        }
      }
      const validateGateway = (rule, value, callback) => {
        let chosed = false
        let channelId = []
        value.forEach(item => {
          if(item.checked) {
            chosed = true
            if(item.third_temp_id) {
              channelId.push(1)
            }else{
              channelId.push(0)
            }
          }
        })
        if (!chosed) {
          callback(new Error('请至少选择一个网关'));
        } else {
          callback()
        }
      }
      return {
        id: '', // 模板id
        formLabelWidth: '100px',
        signatureOptions: [{
          value: '0',
          label: '请选择'
        }],
        tplTypeOptions: [{
          value: '0',
          label: '请选择'
        }],
        gatewayOptions: [],
        tplForm: {
          content: '',
          sence: '',
          signatureVal: '0',
          tplTypeVal: '0',
          startUp: '1',
          secret: '0',
          gatewayList: []
        },
        rules: {
          content: [
            { required: true, message: '请输入短信模板', trigger: 'blur' }
          ],
          sence: [
            { required: true, message: '请输入模板使用场景', trigger: 'blur' }
          ],
          signatureVal: [
            { required: true, validator: validateSignature, trigger: 'blur' }
          ],
          tplTypeVal: [
            { required: true, validator: validateTpl, trigger: 'blur' }
          ],
          gatewayList: [
            { required: true, validator: validateGateway}
          ]
        },
        hasGateway: false
      }
    },
    computed: {
      contentSize() {
        return this.tplForm.content.length
      }
    },
    watch: {
      'tplForm.tplTypeVal'(val, oldVal) {
        this.hasGateway = false
        this.tplForm.gatewayList.forEach(item => {
          if(this.id) {
            if(!item.checked) {
              item.checked = false
            }else{
              item.checked = true
            }
            if(!item.prepared) {
              item.prepared = false
            }else{
              item.prepared = true
            }
          }else{
            item.checked = false
            item.prepared = false
          }
        })
        if(val > 0) {
          this.gatewayOptions.forEach(item => {
            if(item.type_list && item.type_list.length>0 && item.type_list.indexOf(val) > -1){
              this.hasGateway = true
            }
          })
        }
      }
    },
    created() {
        this.id = this.$route.query.id
        this.getFilterOptions()
    },
      mounted() {
          this.handleFixTitle()
      },
    methods: {
        getFilterOptions() {
        fetchFilterTplInfo({
          id: this.id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if(res.data.data.type_list && res.data.data.type_list.length > 0) {
              res.data.data.type_list.forEach(item => {
                this.tplTypeOptions.push({
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
            if(res.data.data.gateway_list && res.data.data.gateway_list.length > 0) {
              res.data.data.gateway_list.forEach(item => {
                item.prepared = parseInt(item.prepared) === 0 ? false : true
                this.gatewayOptions.push(item)
                this.tplForm.gatewayList.push(item)
              })
            }
            if(res.data.data.info && res.data.data.info.content) {
              this.tplForm.content = res.data.data.info.content
            }
            if(res.data.data.info && res.data.data.info.use_scene) {
              this.tplForm.sence = res.data.data.info.use_scene
            }
              this.tplForm.secret = String(res.data.data.info.encrypt)
            this.$nextTick(() => {
              if(res.data.data.info && res.data.data.info.sign_id) {
                this.tplForm.signatureVal = !res.data.data.info.sign_id ? '' : res.data.data.info.sign_id
              }
              if(res.data.data.info && res.data.data.info.type) {
                this.tplForm.tplTypeVal = !res.data.data.info.type ? '' : res.data.data.info.type
              }
            })
          }else{
            this.$message.error(res.data.error_msg)
          }
        })
        },
        submitForm(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.ajaxGateway()
          } else {
            console.log('error submit!!')
            return false
          }
        })
        },
        ajaxGateway() {
        const queryObj = this.handleArgs()
        fetchTplSave(queryObj).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '保存成功',
              type: 'success'
            })
            this.$router.push({
              path: 'smsTpl'
            })
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
        },
        handleArgs() {
        const queryObj = {
          id: this.id,
          content: this.tplForm.content,
          use_scene: this.tplForm.sence,
          sign_id: this.tplForm.signatureVal,
          type: this.tplForm.tplTypeVal,
          enabled: this.tplForm.startUp,
          encrypt: this.tplForm.secret,
          gateway_json: []
        }
        this.tplForm.gatewayList.forEach(item => {
          if(item.checked) {
            queryObj.gateway_json.push({
              id: item.id,
              third_temp_id: item.third_temp_id,
              prepared: !item.prepared ? 0 : 1
            })
          }
        })
        return queryObj
        },
        clearGatewayVal() {
        this.tplForm.gatewayList.forEach(item => {
          item.checked = false
          item.third_temp_id = ''
          item.prepared = false
        })
        },
        goBack() {
            history.go(-1)
        },
        handleFixTitle() {
            const tags = document.getElementsByClassName('tags-view-item')
            const brumdsTag = document.querySelectorAll('.no-redirect')
            let brumdsTagText = brumdsTag[0].innerText.replace(/^\s+/,'').replace(/\s$/,'')
            if(brumdsTagText === '创建模板' && this.id) {
                brumdsTag[0].innerHTML = brumdsTag[0].innerHTML.replace(/创建/, '编辑')
            }
            if(brumdsTagText === '编辑模板' && this.id) {
                brumdsTag[0].innerHTML = brumdsTag[0].innerHTML.replace(/创建/, '创建')
            }
            for(let i=0, l=tags.length;i<l;i++) {
                const tagName = tags[i].innerText.replace(/^\s+/,'').replace(/\s$/,'')
                if(tagName === '创建模板' && this.id) {
                    tags[i].innerHTML = tags[i].innerHTML.replace(/创建/, '编辑')
                }
                if(tagName === '编辑模板' && !this.id) {
                    tags[i].innerHTML = tags[i].innerHTML.replace(/编辑/, '创建')
                }
            }
        }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .create-sms-tpl {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
      .tpl-content-note{
        padding-left: 100px;
      }
      .red{
        color: red;
      }
      .gateways{
        margin-bottom: 10px;
      }
      .gateways>td{
        padding-right: 10px;
      }
      .gatewayinput{
        width: auto;
      }
    }
  }
</style>
