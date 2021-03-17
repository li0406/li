<template>
    <div class="add-application">
      <el-dialog title="新增项目应用" :visible="dialogFormVisible" :close-on-click-modal="false" @close="closeDia">
        <el-form :model="applicationForm" :rules="rules" ref="applicationForm">
          <el-form-item label="应用名称" :label-width="formLabelWidth" prop="name">
            <el-input v-model="applicationForm.name" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="应用说明" :label-width="formLabelWidth" prop="content">
            <el-input
              type="textarea"
              :rows="2"
              placeholder="请输入内容"
              v-model="applicationForm.content"
            >
            </el-input>
          </el-form-item>
          <el-form-item label="应用类型" :label-width="formLabelWidth" prop="systemType">
            <el-radio-group v-model="applicationForm.systemType">
                <el-radio :label="1">前台系统</el-radio>
                <el-radio :label="2">后台系统</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="接入类型" :label-width="formLabelWidth" prop="accessType">
            <el-checkbox-group v-model="applicationForm.accessType">
                <el-checkbox :label="1">短信</el-checkbox>
                <el-checkbox :label="2">消息</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="是否启用" :label-width="formLabelWidth" prop="startUp">
            <el-switch
              v-model="applicationForm.startUp"
              active-value="1"
              inactive-value="0"
              @change="forbiddenTips"
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
  import { fetchApplySave, fetchAppDetail } from '@/api/projectApply'
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
      }
    },
    data() {
      return {
        formLabelWidth: '30',
        applicationForm: {
          name: '',
          content: '',
          startUp: '1',
          systemType: '',
          accessType: []
        },
        rules: {
          name: [
            { required: true, message: '请输入应用名称', trigger: 'blur' }
          ],
          startUp: [
            // { required: true, message: '请选择活动区域', trigger: 'change' }
          ],
          content: [
            { required: true, message: '请输入应用说明', trigger: 'blur' }
          ],
          systemType: [
            { required: true, message: '请选择应用类型', trigger: 'blur' }
          ],
          accessType: [
            { required: true, message: '请选择接入类型', trigger: 'blur' }
          ]
        }
      }
    },
    methods: {
      getAppDetail() {
        fetchAppDetail({
          id: this.applicationId
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.applicationForm.name = res.data.data.list.name
            this.applicationForm.content = res.data.data.list.note
            this.applicationForm.startUp = String(res.data.data.list.is_enable)
            this.applicationForm.systemType = res.data.data.list.app_type
            res.data.data.list.access_list.forEach((item, index) => {
                this.applicationForm.accessType.push(item.access_type)
            })
          }
        })
      },
      saveAndClose(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            fetchApplySave({
              id: this.applicationId,
              name: this.applicationForm.name,
              note: this.applicationForm.content,
              is_enable: this.applicationForm.startUp,
              app_type: this.applicationForm.systemType,
              access_type: this.applicationForm.accessType.join(',')
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
     
      closeDia(tag) {
        this.applicationId = ''
        this.applicationForm.name = ''
        this.applicationForm.content = ''
        this.applicationForm.startUp = '1'
        this.applicationForm.systemType = ''
        this.applicationForm.accessType = []
        this.$emit('closeAddDia', tag)
      },
      forbiddenTips() {
        if(parseInt(this.applicationForm.startUp) === 0){
          this.$confirm('警告！禁用后，本应用中所有的服务都不能发送', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            // 同意更改，什么都不做，所有的操作都是点击保存后执行
          }).catch(() => {
            // 还原原有状态
            this.applicationForm.startUp = '1'
          });
        }
      }
    }
  }
</script>

<style scoped>

</style>
