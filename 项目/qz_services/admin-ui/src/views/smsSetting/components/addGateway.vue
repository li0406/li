<template>
    <div class="add-gateway">
      <el-dialog :title="title" :visible="dialogFormVisible" @close="closeDia" :close-on-click-modal="false">
        <el-form :model="applicationForm" :rules="rules" ref="applicationForm">
          <el-form-item label="唯一标识 (格式：字母+数字+中划线/下划线，该标识不能重复)" :label-width="formLabelWidth" prop="ident">
            <el-input v-model="applicationForm.ident" :disabled="forbiddenEdit" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="通道名称" :label-width="formLabelWidth" prop="name">
            <el-input v-model="applicationForm.name" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="通道类型" :label-width="formLabelWidth" prop="type">
            <el-checkbox-group v-model="applicationForm.type">
              <el-checkbox label="1">验证码通知(行业)</el-checkbox>
              <el-checkbox label="2">营销</el-checkbox>
              <el-checkbox label="3">国际验证码</el-checkbox>
              <el-checkbox label="4">语音验证码</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="配置文件(JSON)" :label-width="formLabelWidth" prop="setting_json">
            <el-input
              type="textarea"
              :rows="2"
              placeholder="请输入内容"
              v-model="applicationForm.setting_json"
            >
            </el-input>
          </el-form-item>
          <el-form-item label="优先级 (提示:只能输入纯数字；数字越大优先级越高。优先级高的通道网关优先使用)" :label-width="formLabelWidth" prop="level">
            <el-input v-input-filter:int v-model="applicationForm.level" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="备注" :label-width="formLabelWidth" prop="note">
            <el-input
              type="textarea"
              :rows="2"
              placeholder="请输入内容"
              v-model="applicationForm.note"
            >
            </el-input>
          </el-form-item>
          <el-form-item label="是否启用" :label-width="formLabelWidth" prop="startUp">
            <el-switch
              v-model="applicationForm.startUp"
              active-value="1"
              inactive-value="0"
            >
            </el-switch>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click="saveAndClose('applicationForm')">保存</el-button>
        </div>
      </el-dialog>
    </div>
</template>

<script>
  import { fetchGatewayDetail, fetchGatewaySave } from '@/api/gateway'
  import { checkPowerfulStr, isJsonString } from '@/utils/index'
  import inputFilter from '@/directives/input-filter/index.js'  // 引入
  export default {
    name: 'addGateway',
    props: {
      dialogFormVisible: {
        type: Boolean,
        default: false
      },
      gatewayId: {
        type: String,
        default: '0'
      }
    },
    directives: {
      inputFilter
    },
    watch: {
      dialogFormVisible(val) {
        if(val){
          this.id = this.gatewayId
          this.getGatewayDetail()
        }
        if(this.gatewayId) {
          this.title = '编辑通道网关'
          this.forbiddenEdit = true
        }else{
          this.title = '新增通道网关'
          this.forbiddenEdit = false
        }
      }
    },
    data() {
      const checkIdent = (rule, value, callback) => {
        // TODO 验证规则改成只要8位，字母开头就行
        if (!value) {
          return callback(new Error('请输入标识'))
        }
        setTimeout(() => {
          if(value.length < 8) {
            callback(new Error('唯一标识最小长度为8个字符'))
          }else if (!checkPowerfulStr(value)) {
            callback(new Error('格式必须以字母开头且不小于8位'))
          } else {
            callback()
          }
        }, 200);
      }
      const checkJson = (rule, value, callback) => {
        if (!value) {
          return callback(new Error('请输入配置信息'))
        }
        setTimeout(() => {
          if(!isJsonString(value)) {
            callback(new Error('配置文件格式不正确，请重新输入'))
          }else{
            callback()
          }
        })
      }
      return {
        id: '',
        title: '新增通道网关',
        formLabelWidth: '30',
        forbiddenEdit: true,
        applicationForm: {
          ident: '',
          name: '',
          type: [],
          setting_json: '',
          level: '',
          note: '',
          startUp: '1'
        },
        rules: {
          ident: [
            { required: true, validator: checkIdent, trigger: 'change' }
          ],
          name: [
            { required: true, message: '请输入通道名称', trigger: 'change' }
          ],
          type: [
            { required: true, message: '请选择通道类型', trigger: 'change' }
          ],
          setting_json: [
            { required: true, validator: checkJson, trigger: 'change' }
          ],
          level: [
            { required: true, message: '请输入优先级', trigger: 'change' }
          ]
        }
      }
    },
    methods: {
      getGatewayDetail() {
        fetchGatewayDetail({
          id: this.id
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0){
            this.applicationForm.ident = res.data.data.list.slot
            this.applicationForm.name = res.data.data.list.name
            this.applicationForm.type = res.data.data.list.type.split(',')
            console.log(this.applicationForm.type)
            this.applicationForm.setting_json = res.data.data.list.config
            this.applicationForm.level = res.data.data.list.level
            this.applicationForm.note = res.data.data.list.note
            this.applicationForm.startUp = String(res.data.data.list.is_enable)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
        })
      },
      saveAndClose(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
              const formdata = new FormData()
              formdata.append('id', this.id)
              formdata.append('slot', this.applicationForm.ident)
              formdata.append('type', this.applicationForm.type.join(','))
              formdata.append('name', this.applicationForm.name)
              formdata.append('config', this.applicationForm.setting_json)
              formdata.append('level', this.applicationForm.level)
              formdata.append('note', this.applicationForm.note)
              formdata.append('is_enable', this.applicationForm.startUp)
            fetchGatewaySave(formdata).then(res => {
              if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  message: '保存成功',
                  type: 'success'
                })
                this.closeDia('updata')
              }else{
                this.$message.error(res.data.error_msg)
                if(parseInt(res.data.error_code) === 4000101) {
                  this.applicationForm.startUp = '0'
                }
              }
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      },
      closeDia(tag) {
        this.applicationForm.ident = ''
        this.applicationForm.name = ''
        this.applicationForm.type = []
        this.applicationForm.setting_json = ''
        this.applicationForm.level = ''
        this.applicationForm.note = ''
        this.applicationForm.startUp = '1'
        this.$emit('closeAddDia', tag)
      }
    }
  }
</script>

<style scoped>

</style>
