<template>
    <div class="add-signature">
      <el-dialog :title="title" :visible="dialogFormVisible" :close-on-click-modal="false" @close="closeDia">
        <el-form :model="applicationForm" ref="applicationForm" :rules="rules">
          <el-form-item label="签名" :label-width="formLabelWidth" prop="name">
            <el-input v-model="applicationForm.name" auto-complete="off"></el-input>
          </el-form-item>
          <el-form-item label="是否启用" :label-width="formLabelWidth" prop="startUp">
            <el-switch
              v-model="applicationForm.startUp"
              active-value="1"
              inactive-value="0"
              @change="switchSignatureStatus()"
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
  import { fetchSignatureEdit } from '@/api/smsTpl'
  export default {
    name: 'addSignature',
    props: {
      dialogFormVisible: {
        type: Boolean,
        default: false
      },
      signatureText: {
        type: String,
        default: ''
      },
      signatureStatus: {
        type: String,
        default: '1'
      },
      signatureId: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        formLabelWidth: '30',
        title: '新增签名',
        applicationForm: {
          name: '',
          startUp: '1'
        },
        rules: {
          name: [
            { required: true, message: '请输入签名', trigger: 'blur' }
          ],
          startUp: [
            // { required: true, message: '请启用签名' }
          ]
        }
      }
    },
    watch: {
      dialogFormVisible(val) {
        if(!val) {
          this.applicationForm.name = ''
          this.applicationForm.startUp = '1'
        }
        this.applicationForm.name = this.signatureText
        this.applicationForm.startUp = this.signatureStatus
        if(!this.applicationForm.name) {
          this.applicationForm.startUp = '1'
        }
        if(this.signatureId) {
          this.title = '编辑签名'
        }else{
          this.title = '新增签名'
        }
      }
    },
    methods: {
      saveSignature() {
        fetchSignatureEdit({
          id: parseInt(this.signatureId),
          name: this.applicationForm.name,
          enabled: parseInt(this.applicationForm.startUp)
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '保存成功',
              type: 'success'
            })
            this.closeDia('updata')
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
        })
      },
      saveAndClose(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.saveSignature()
          } else {
            console.log('error submit!!')
            return false
          }
        })

      },
      closeDia(tag) {
        this.$emit('closeAddDia', tag)
      },
      switchSignatureStatus() {
        if(parseInt(this.applicationForm.startUp) === 0) {
          this.$confirm('一旦禁用了该签名，跟该签名关联的模板都不可使用，请谨慎操作。', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            // 什么都不做
          }).catch(() => {
            // 还原原有状态
            this.applicationForm.startUp = '1'
          });
        }
      },
    }
  }
</script>

<style scoped>

</style>
