<template>
  <div>
    <el-dialog
      title="编辑收货地址"
      :visible.sync="addaddressbul"
      width="40%"
      :before-close="handleClose"
      :close-on-click-modal="false"
      :destroy-on-close="true"
    >
      <div>
        <el-form ref="ruleForm" :model="ruleForm" :rules="rules">
          <el-form-item label="所在地区" prop="area">
            <br>
            <div class="flex spa-bet">
              <div class="flex">
                <div class="bold">省</div>
                <div>
                  <el-select v-model="ruleForm.province" class="ml10" placeholder="请选择" @change="getprovinceval">
                    <el-option
                      v-for="(item) of provincelist"
                      :key="item.value.id"
                      :label="item.label"
                      :value="item.value.code"
                    />
                  </el-select>
                </div>
              </div>
              <div class="flex">
                <div class="bold">市</div>
                <div>
                  <el-select v-model="ruleForm.city" class="ml10" placeholder="请选择" @change="getcityval">
                    <el-option
                      v-for="item of citylist"
                      :key="item.value.id"
                      :label="item.label"
                      :value="item.value.code"
                    />
                  </el-select>
                </div>
              </div>
              <div class="flex">
                <div class="bold">区</div>
                <div>
                  <el-select v-model="ruleForm.area" class="ml10" placeholder="请选择">
                    <el-option
                      v-for="item of arealist"
                      :key="item.value.id"
                      :label="item.label"
                      :value="item.value.id"
                    />
                  </el-select>
                </div>
              </div>
            </div>
          </el-form-item>
          <el-form-item label="收货人" prop="consignee">
            <el-input v-model="ruleForm.consignee" placeholder="请输入收货人名称" />
          </el-form-item>
          <el-form-item label="手机号" prop="tel">
            <el-input v-model="ruleForm.tel" maxlength="11" placeholder="请输入收货人手机号" />
          </el-form-item>
          <el-form-item label="详细地址" prop="address">
            <el-input v-model="ruleForm.address" type="textarea" placeholder="请输入详细地址" />
          </el-form-item>
          <el-form-item>
            <div class="flex spa-bet">
              <div>
                <el-checkbox v-model="ruleForm.checked">设置为默认地址</el-checkbox>
              </div>
              <div>
                <el-button @click="resetForm()">取消</el-button>
                <el-button type="primary" @click="submitForm('ruleForm')">确定添加</el-button>
              </div>
            </div>
          </el-form-item>
        </el-form>
      </div>
    </el-dialog>
  </div>
</template>
<script>
export default {
  props: {
    addaddressbul: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      addressDetails: {},
      provincelist: [],
      citylist: [],
      arealist: [],
      ruleForm: {
        province: '',
        city: '',
        area: '',
        consignee: '',
        tel: '',
        address: '',
        checked: 0
      },
      rules: {
        area: [
          { required: true, message: '请选择所在地区', trigger: 'change' }
        ],
        consignee: [
          { required: true, message: '请输入收货人姓名', trigger: 'change' }
        ],
        tel: [
          { required: true, message: '请输入手机号', trigger: 'change' },
          { pattern: /^1[3-9]\d{9}$/, message: '请输入正确手机号', trigger: 'blur' }
        ],
        address: [
          { required: true, message: '请输入详细地址', trigger: 'change' }
        ]
      }
    }
  },
  /* watch: {
    addressDetails(val) {
      this.ruleForm.consignee = val.receiverName
      this.ruleForm.tel = val.receiverPhone
      this.ruleForm.address = val.receiveAreaDetails
      this.ruleForm.checked = Boolean(Number(val.ifDefult))
      this.getByParentCode(2)
      this.ruleForm.province = val.receivePrinceCode
      this.getByParentCode(3, val.receivePrinceCode)
      this.ruleForm.city = val.receiveCityCode
      this.getByParentCode(4, val.receivePrinceCode)
      this.ruleForm.area = val.receiveProperId
    }
  }, */
  created() {
    this.getByParentCode(2)
  },
  methods: {
    getprovinceval(code) {
      this.ruleForm.city = ''
      this.ruleForm.area = ''
      this.getByParentCode(3, code)
    },
    getcityval(code) {
      this.ruleForm.area = ''
      this.getByParentCode(4, code)
    },
    getByParentCode(level, code) {
      const data = {
        level
      }
      if (level !== 2 && code !== 0) {
        data.pcode = code
      }
      this.$apis.RETAIL.getByParentCode(data).then(res => {
        if (res.code === 0) {
          if (level === 2) {
            this.provincelist = [{ name: '请选择', code: 0, id: 0 }, ...res.data].map(item => {
              return {
                value: {
                  code: item.code,
                  id: item.id
                },
                label: item.name
              }
            })
          }
          if (level === 3) {
            this.citylist = [{ name: '请选择', code: 0, id: 0 }, ...res.data].map(item => {
              return {
                value: {
                  code: item.code,
                  id: item.id
                },
                label: item.name
              }
            })
          }
          if (level === 4) {
            this.arealist = [{ name: '请选择', code: 0, id: 0 }, ...res.data].map(item => {
              return {
                value: {
                  code: item.code,
                  id: item.id
                },
                label: item.name
              }
            })
          }
        } else {
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    expandHandleChange(val) {
      console.log(val, 55)
    },
    handleChange(val) {
      this.getByParentCode(3, val[0])
    },
    handleClose() {
      this.ruleForm.provincelist = []
      this.ruleForm.citylist = []
      this.ruleForm.arealist = []
      this.ruleForm.province = ''
      this.ruleForm.city = ''
      this.ruleForm.area = ''
      this.ruleForm.consignee = ''
      this.ruleForm.tel = ''
      this.ruleForm.address = ''
      this.ruleForm.checked = 0
      this.$parent.addaddressbul = false
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const goodsmsg = {
            receiverName: this.ruleForm.consignee,
            receiverPhone: this.ruleForm.tel,
            receiveProperId: this.ruleForm.area,
            receiveAreaDetails: this.ruleForm.address,
            ifDefult: Number(this.ruleForm.checked)
          }
          
          this.$emit('getgoodsmsg', goodsmsg)
          this.handleClose()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    resetForm() {
      this.handleClose()
      this.$parent.addaddressbul = false
    }
  }
}
</script>
