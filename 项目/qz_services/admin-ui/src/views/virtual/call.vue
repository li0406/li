<template>
    <div class="call-contain"  v-loading="loading">
        <div class="qian-main">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px" class="demo-ruleForm">
                <el-form-item label="选择供应商:" prop="pnp_provider">
                    <el-select v-model="ruleForm.pnp_provider" placeholder="请选择">
                        <el-option
                                v-for="item in options"
                                :key="item.at"
                                :label="item.name"
                                :value="item.slot">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="设置城市:" prop="open_city">
                    <el-transfer
                        filterable
                        :titles="['所有城市', '选定城市']"
                        :props="{
                          key: 'cid',
                          label: 'cname'
                        }"
                        filter-placeholder="搜索"
                        v-model="ruleForm.open_city"
                        :data="citys">
                    </el-transfer>
                </el-form-item>
                <el-form-item label="绑定时长:" prop="pnp_expire">
                    <el-input class="days" v-model="ruleForm.pnp_expire" placeholder=""></el-input>天 <span class="tips">(提示：虚拟号绑定每个业主手机的最长时间)</span>
                </el-form-item>
                <el-form-item label="开启状态:" prop="pnp_switch">
                    <el-radio-group v-model="ruleForm.pnp_switch ">
                        <el-radio :label="1">开启</el-radio>
                        <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="submitForm('ruleForm')">保存</el-button>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
import { fetchConfig, fetchConfigup, providerdropdownlist } from '@/api/virtual'
export default {
  name: 'Call',
  data() {
    const validateInt = (rule, value, callback) => {
      const reg = /^[+]{0,1}(\d+)$/
      if (value && !reg.test(value)) {
        callback(new Error('请输入正确时长'))
      } else if (Number(value) < 1) {
        callback(new Error('请输入正确时长'))
      } else {
        callback()
      }
    }
    const validateCity = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('请选择城市'))
      } else {
        callback()
      }
    }
    return {
      options: [],
      ruleForm: {
        pnp_provider: '',
        pnp_expire: '',
        pnp_switch: 1,
        open_city: []
      },
      rules: {
        pnp_provider: [
          { required: true, message: '请选择供应商', trigger: 'blur' }
        ],
        pnp_expire: [
          { required: true, message: '请输入绑定时长', trigger: 'blur' },
          { validator: validateInt, trigger: 'blur' }
        ],
        open_city: [
          { required: true, validator: validateCity, trigger: 'blur' }
        ]
      },
      citys: [],
      loading: false
    }
  },
  created() {
    this.getOptions()
    this.getCity()
  },
  methods: {
    getOptions() {
      providerdropdownlist().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const list = res.data.data.list
          this.options = list.map((item) => {
            return {
              name: item.name,
              slot: item.slot,
              at: item.created_at
            }
          })
        } else {
          this.loading = false
          this.$message.error(res.data.error_msg)
        }
      })
    },
    getCity() {
      this.loading = true
      fetchConfig().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.loading = false
          const datas = res.data.data
          this.citys = datas.citys
          this.ruleForm.open_city = datas.open_city.map(item => item.cid)
          datas.option.forEach((item) => {
            if (item.pnp_option === 'pnp_expire') {
              this.ruleForm.pnp_expire = item.pnp_option_value
            }
            if (item.pnp_option === 'pnp_provider') {
              this.ruleForm.pnp_provider = item.pnp_option_value
            }
            if (item.pnp_option === 'pnp_switch') {
              this.ruleForm.pnp_switch = Number(item.pnp_option_value)
            }
          })
        } else {
          this.loading = false
          this.$message.error(res.data.error_msg)
        }
        this.loading = false
      }).catch(res => {
        this.loading = false
      })
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true
          fetchConfigup(this.ruleForm).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.$message({
                message: '保存成功',
                type: 'success'
              })
              this.loading = false
            } else {
              this.$message.error(res.data.error_msg)
              this.loading = false
            }
          })
        } else {
          return false
        }
      })
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
    .call-contain{
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .search {
            background: #fff;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            overflow: hidden;
            span{
                float: left;
                line-height: 36px;
            }
        }
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
            .days{
                width: 120px;
                margin-right: 20px;
            }
            .tips{
                color: red;
                margin-left: 20px;
            }
        }
        .el-transfer-panel{
            height: 500px;
        }
        .el-transfer-panel__list.is-filterable{
            height: 400px;
        }

    }
</style>
