<template>
    <div class="add-Supplier">
        <el-dialog :title="titles" width="600px" :visible="dialogFormVisible" :close-on-click-modal="false" @close="closeDia">
            <el-form :model="applicationForm" :rules="rules" ref="applicationForm">
                <el-form-item label="唯一标识(格式：字母、数字、中划线/下划线，该标识不能重复)" :label-width="formLabelWidth" prop="slot">
                    <el-input v-model="applicationForm.slot" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="供应商名称" :label-width="formLabelWidth" prop="name">
                    <el-input v-model="applicationForm.name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="配置文件（JSON）" :label-width="formLabelWidth" prop="config">
                    <el-input
                            type="textarea"
                            :rows="2"
                            placeholder="请输入内容"
                            v-model="applicationForm.config"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item label="备注" :label-width="formLabelWidth" prop="remark">
                    <el-input
                            type="textarea"
                            :rows="2"
                            placeholder="请输入内容"
                            v-model="applicationForm.remark"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item label="是否启用" :label-width="formLabelWidth" prop="enabled">
                    <el-switch
                            v-model="applicationForm.enabled"
                            active-value="1"
                            inactive-value="2"
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
import { fetchProviderup, fetchSupplierDetail } from '@/api/virtual'
export default {
  name: 'AddSupplier',
  props: {
    dialogFormVisible: {
      type: Boolean,
      default: false
    },
    applicationId: {
      type: String,
      default: ''
    }
  },
  watch: {
    dialogFormVisible(val) {
      if (val && this.applicationId) {
        this.titles = '编辑供应商'
        this.fetchSupplierDetail()
      }
    }
  },
  data() {
    const validateSlot = (rule, value, callback) => {
      const reg = /^[a-zA-Z][a-zA-Z0-9_-]*$/
      if (value && !reg.test(value)) {
        callback(new Error('以字母开头，只能输入字母、数字、中划线/下划线'))
      } else {
        callback()
      }
    }
    return {
      formLabelWidth: '30',
      applicationForm: {
        slot: '',
        name: '',
        config: '',
        remark: '',
        enabled: '1'
      },
      rules: {
        slot: [
          { required: true, message: '请输入唯一标识', trigger: 'blur' },
          { min: 8, message: '该标识不少于8个字符', trigger: 'blur' },
          { validator: validateSlot, trigger: 'blur' }
        ],
        name: [
          { required: true, message: '请输入供应商名称', trigger: 'blur' }
        ],
        config: [
          { required: true, message: '请输入配置文件（JSON）', trigger: 'blur' }
        ]
      },
      titles: '新增供应商'
    }
  },
  methods: {
    fetchSupplierDetail() {
      fetchSupplierDetail({
        id: this.applicationId
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.applicationForm.slot = res.data.data.slot
          this.applicationForm.name = res.data.data.name
          this.applicationForm.config = res.data.data.config
          this.applicationForm.remark = res.data.data.remarks
          this.applicationForm.enabled = String(res.data.data.is_enabled)
        }
      })
    },
    saveAndClose(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          fetchProviderup({
            id: this.applicationId,
            slot: this.applicationForm.slot,
            name: this.applicationForm.name,
            config: this.applicationForm.config,
            remark: this.applicationForm.remark,
            enabled: this.applicationForm.enabled
          }).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '保存成功',
                type: 'success'
              })
              this.closeDia('update')
            } else {
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
      this.applicationForm.slot = ''
      this.applicationForm.name = ''
      this.applicationForm.config = ''
      this.applicationForm.remark = ''
      this.applicationForm.enabled = '1'
      this.$refs['applicationForm'].resetFields()
      this.$emit('closeAddDia', tag)
    }
  }
}
</script>

<style scoped>

</style>
