<template>
    <div class="add-application">
      <el-dialog title="新增消息类型" :visible="dialogFormVisible" :close-on-click-modal="false" @close="closeDia('applicationForm')">
        <el-form :model="applicationForm" :rules="rules" ref="applicationForm">
            <el-form-item label="唯一标识" :label-width="formLabelWidth" prop="slot" >
              <el-input v-model="applicationForm.slot" :disabled="jinYong"></el-input>
            </el-form-item>
            <p>格式： 字母+数字+中划线,该标识不能重复</p>
          <el-form-item label="消息类型" :label-width="formLabelWidth" prop="name">
            <el-input v-model="applicationForm.name" >
            </el-input>
          </el-form-item>
          <el-form-item label="接收方式:" :label-width="formLabelWidth" prop="receive_type">
             <el-radio-group v-model="applicationForm.receive_type">
              <el-radio :label="1">实时接收</el-radio>
              <el-radio :label="2">延后接收</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="提醒方式:" :label-width="formLabelWidth" prop="remind_type">
             <el-radio-group v-model="applicationForm.remind_type">
              <el-radio :label="1">数量提醒</el-radio>
              <el-radio :label="2">弹窗提醒</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="项目应用:" :label-width="formLabelWidth" prop="app_list">
            <el-checkbox-group v-model="applicationForm.app_list">
              <el-checkbox :label="item.slot" :key="item.id" v-for="item in applicationForm.appList" >{{item.name}}</el-checkbox>
            </el-checkbox-group>
             
          </el-form-item>
          <el-form-item label="是否启用" :label-width="formLabelWidth" prop="enabled">
            <el-switch
              v-model="applicationForm.enabled"
              active-value="1"
              inactive-value="2"
              @change="forbiddenTips"
            >
            </el-switch>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click="saveAndClose('applicationForm')">保存</el-button>
        </div>
      </el-dialog>
      <div></div>
    </div>
</template>

<script>
  import { fetchMessageSave, editMessage} from '@/api/messageType'
  export default {
    name: 'addApplication',
    props: {
      dialogFormVisible: {
        type: Boolean,
        default: false
      },
      applicationId: {
        type: Number,
        default: 0
      }
    },
    watch: {
        dialogFormVisible(val) {
            if(val && this.applicationId) {
                this.getAppDetail()
            }
            if(val) {
               this.getAppDetail()
            }
        },
    },
    data() {
        //唯一标识验证 
        const validateSlot = (rule, value, callback) => {
            var reg=/^[a-zA-Z][a-zA-Z0-9-]*$/g ; 
            if(this.applicationId == ''){
                if(value == ''){
                    callback(new Error('请输入唯一标识'));
                }else  if(value.length < 8){
                    callback(new Error('长度不能小于8位'));
                }else  if(!reg.test(value)){   
                    callback(new Error('格式必须以字母开头，由字母、数字、中划线构成'));   
                } else{
                    callback();
                }
            } else if(this.applicationId != ''){
                 if(value == ''){
                    callback();
                }else  if(value.length < 8){
                    callback();
                }else  if(!reg.test(value)){   
                    callback();   
                } else{
                    callback();
                }
            }
           
        }
        return {
            formLabelWidth: '30',
            applicationForm: {
                id:'',// 类型id 可选
                slot:'',// 唯一标识
                name:'',// 类型名称
                receive_type:'',// 接收方式
                remind_type:'', // 提醒方式
                enabled:'1', // 是否启用
                appList: [], // 用来回显多选框
                app_list: [], 
            },
            rules: {
                slot:[
                    { required: true, validator: validateSlot, trigger: 'blur' },
                ],
                app_list: [
                    { required: true, message: '请选择项目应用', trigger: 'change' }
                ],
                name: [
                    { required: true, message: '请输入消息类型', trigger: 'blur' }
                ],
                receive_type: [
                    { required: true, message: '请选择接收方式', trigger: 'change' }
                ],
                remind_type: [
                    { required: true, message: '请选择提醒方式', trigger: 'change' }
                ],
                enabled: [
                    { required: true, message: '请选择是否启用', trigger: 'change' }
                ],
            },
            jinYong:false
        }
    },
    created(){

    },
    methods: {
      // 点击编辑跳转过来请求数据
      getAppDetail() {
        if(this.applicationId != ''){
            this.jinYong = true
          editMessage({
            id: this.applicationId,
          }).then(res => {
            if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              console.log('res.data.data.info', res.data.data.info);
                let info = res.data.data.info
                this.applicationForm.slot = info.slot
                this.applicationForm.name = info.name
                this.applicationForm.remind_type = info.remind_type
                this.applicationForm.receive_type = info.receive_type
                this.applicationForm.enabled = String(info.enabled)
                this.applicationForm.appList = res.data.data.app_list
                // 用于回显选中的数据
                for(let i = 0; i < res.data.data.app_list.length; i++){
                  if (res.data.data.app_list[i].checked == 1){
                    console.log(res.data.data.app_list[i]);
                    this.applicationForm.app_list.push(res.data.data.app_list[i].slot)
                  }
                }
            }
          })
        } else {
          editMessage({}).then(res => {
            if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.applicationForm.appList = res.data.data.app_list
            }
          })
          
        }
      },
      // 保存按钮
      saveAndClose(formName) {
        this.$refs[formName].validate((valid) => {
            console.log('保存',this.applicationForm.app_list.join(','));
          if (valid) {
            fetchMessageSave({
              id: this.applicationId || '',// 如果id为空  表示新增
              slot: this.applicationForm.slot,
              name: this.applicationForm.name,
              receive_type: this.applicationForm.receive_type,
              remind_type: this.applicationForm.remind_type,
              enabled: this.applicationForm.enabled,
              app_list:this.applicationForm.app_list.join(',')
            }).then(res => {
              if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.$message({
                  message: '保存成功',
                  type: 'success'
                })
                this.closeDia('update')
              }else{
                this.$message.error(res.data.error_msg)
              }
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      },
      // 关闭按钮
      closeDia(formName) {
        this.applicationId = ''
        this.applicationForm.slot = ''
        this.applicationForm.name = ''
        this.applicationForm.receive_type = ''
        this.applicationForm.remind_type = ''
        this.applicationForm.enabled = ''
        this.applicationForm.app_list = []
        // this.$nextTick(() => {
        //   this.$refs["applicationForm"].resetFields();
        // });
        this.$emit('closeAddDia', formName)
        this.jinYong = false
        //清空验证
        if (this.$refs[formName] !== undefined) {
            this.$refs[formName].resetFields();
        }
      },
       
      forbiddenTips() {
        // if(parseInt(this.applicationForm.startUp) === 0){
        //   this.$confirm('警告！禁用后，本应用中所有的短信都不能发送', '提示', {
        //     confirmButtonText: '确定',
        //     cancelButtonText: '取消',
        //     type: 'warning'
        //   }).then(() => {
        //     // 同意更改，什么都不做，所有的操作都是点击保存后执行
        //   }).catch(() => {
        //     // 还原原有状态
        //     this.applicationForm.startUp = '1'
        //   });
        // }
      }
    }
  }
</script>

<style  rel="stylesheet/scss" lang="scss">
    .add-application{
        .el-checkbox__label {
            display: inline-grid;
            white-space: pre-line;
            word-wrap: break-word;
            overflow: hidden;
            line-height: 20px;
        }
    }

</style>
