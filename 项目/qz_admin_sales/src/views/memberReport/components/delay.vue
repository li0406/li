<template>
  <div class="delay-member-order">
    <el-form ref="ruleForm" v-loading="loading" :model="ruleForm" :rules="rules" label-width="100px" class="demo-ruleForm">
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>网店代码：</div>
        </el-col>
        <el-col :span="8">
          <el-form-item prop="companyId">
            <el-input v-model="ruleForm.companyId" placeholder="请输入" @keyup.enter.native="getCompanyInfo" />
          </el-form-item>
        </el-col>
        <el-col :span="3" style="text-align: center;">
          <el-button @click="getCompanyInfo">确定</el-button>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">公司名称：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item>
            <el-input v-model="ruleForm.company_name" placeholder="输入网店代码后展示" :disabled="true" />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">会员类型：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item>
            <el-select v-model="ruleForm.company_mode" class="w300" placeholder="输入网店代码后展示" :disabled="true">
              <el-option
                v-for="item in companModeList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>本次会员时间：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="current_vip_id">
            <el-select v-model="ruleForm.current_vip_id" class="w300" placeholder="请选择" @change="currentVipChange">
              <el-option
                v-for="item in vipContractList"
                :key="item.id"
                :label="item.start_time + '至' + item.end_time"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">本次分单量：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.current_fen_orders" :disabled="true" placeholder="选择本次会员时间后展示" />
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>本次报备合同：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="report_id">
            <el-select v-model="ruleForm.report_id" class="w300" placeholder="请选择" @change="promiseOrdersChange">
              <el-option
                v-for="item in reportContractList"
                :key="item.report_id"
                :label="item.current_contract_start + '至' + item.current_contract_end "
                :value="item.report_id"
              />
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">承诺订单量：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.promise_orders" :disabled="true" placeholder="选择大报备后展示" />
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>延期承诺单量：</div>
        </el-col>
        <el-col :span="16">
          <el-form-item prop="delay_promise_orders">
            <el-input v-model="ruleForm.delay_promise_orders" maxlength="5" placeholder="需要补的分单数量" />
          </el-form-item>
        </el-col>
        <el-col :span="2" style="position:relative;"><p class="red keftips">承诺订单量减去本次分单量</p></el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">应延期天数：</div>
        </el-col>
        <el-col :span="16">
          <el-input v-model="ruleForm.delay_days" :disabled="true" />
        </el-col>
        <el-col :span="2" style="position:relative;"><p class="red keftips">系统自动计算（本次分单差额/最近三个月每日平均分单数）</p></el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>实际延期天数：</div>
        </el-col>
        <el-col :span="16" prop="delay_promise_orders">
          <el-form-item prop="delay_real_days">
            <el-input v-model="ruleForm.delay_real_days" maxlength="6" placeholder="系统自动计算，可手动修改" />
          </el-form-item>
        </el-col>
        <el-col :span="2" style="position:relative;"><p class="red keftips">应延期天数*70%</p></el-col>
      </el-row>

      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>延期合同日期：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="delay_contract_start" class="inline-block">
            <el-date-picker
              v-model="ruleForm.delay_contract_start"
              value-format="yyyy-MM-dd"
              type="date"
              placeholder="合同开始日期"
            />
          </el-form-item>
          <el-form-item class="inline-block">
            <el-date-picker
              v-model="ruleForm.delay_contract_end"
              type="date"
              placeholder="合同结束日期"
              :disabled="true"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">上传图片：</div>
        </el-col>
        <el-col :span="18">
          <el-upload
            ref="certUpload"
            class="upload-demo"
            :action="upload_img_url"
            :limit="20"
            :data="uploadExtendData"
            :headers="uploadContentType"
            :on-success="handleUploadSuccess"
            :file-list="fileList"
            :on-remove="handleRemove"
            drag
            list-type="picture"
          >
            <div class="el-upload__text"><em>点击上传</em></div>
          </el-upload>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">备注：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.remarke" type="textarea" :rows="5" :maxlength="500" placeholder="请输入" />
        </el-col>
      </el-row>
      <!--图片预览-->
      <el-dialog
        :visible.sync="dialogVisiblePreview"
        :close-on-click-modal="false"
        width="60%"
      >
        <div v-for="item in previewTemp" :key="item" class="img"><img
          :src="item"
          alt=""
          style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"
        >
        </div>
      </el-dialog>
      <el-row class="ml-40">
        <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
        <el-button v-if="!unCheckEditTag" type="success" @click="handleSubmit('ruleForm')">提交</el-button>
        <el-button @click="handleCancel">取消</el-button>
      </el-row>
      <el-row class="ml-40">
        <p class="red">提示：提交后将推送至副总裁审核</p>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { fetchMemberReportAdd, fetchFindDelayCompany, fetchCompanyAllotOrders } from '@/api/memberReport'
import { fortmatDate } from '@/utils/index'
import memberReportDetail from '@/mixins/memberReport'
import QZ_CONFIG from '@/utils/config'

export default {
  name: 'Delay',
  mixins: [memberReportDetail],
  props: {
    memberType: {
      type: String,
      default: ''
    },
    memberTypeId: {
      type: String,
      default: ''
    },
    operationActionTag: {
      type: String,
      default: ''
    }
  },
  data() {
    const validateInt = (rule, value, callback) => {
      const reg = /^[+]{0,1}(\d+)$/
      if (value && !reg.test(value)) {
        callback(new Error('请输入正整数'))
      } else {
        callback()
      }
    }
    const validateisDate = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请选择合同开始时间'))
      } else {
        callback()
      }
    }
    const valDelayRealDays = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入实际延期天数'))
      } else if (value < 1) {
        callback(new Error('实际延期天数大于0'))
      } else {
        callback()
      }
    }
    return {
      ruleForm: {
        id: '',
        cooperation_type: '',
        status: '',
        companyName: '',
        contractTime: '', // 总合同时间数组
        memberTime: '', // 本次会员时间
        companyId: '', // 网店代码
        remarke: '',
        // 会员报备数据
        current_vip_id: '',
        current_fen_orders: '', // 本次分单量
        promise_orders: '', // 订单承诺量
        delay_promise_orders: '', // 延期承诺单量
        delay_days: '', // 应延期天数
        delay_real_days: '', // 实际延期天数
        delay_contract_start: '', // 延期合同开始时间
        delay_contract_end: '', // 延期合同结束时间
        report_id: '', // 报备合同ID
        report_cooperation_type: '' // 报备合同ID
      },
      rules: {
        companyId: [
          { required: true, message: '请输入网店代码', trigger: 'change' }
        ],
        current_vip_id: [
          { required: true, message: '请选择本次会员时间', trigger: 'change' }
        ],
        report_id: [
          { required: true, message: '请选择本次报备合同', trigger: 'change' }
        ],
        delay_promise_orders: [
          { required: true, message: '请输入延期承诺单量', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        delay_real_days: [
          { required: true, message: '请输入实际延期天数', trigger: 'change' },
          { validator: valDelayRealDays, trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        delay_contract_start: [
          { validator: validateisDate, trigger: 'change' }
        ]
      },
      companModeList: [
        { id: 1, name: '常规会员' },
        { id: 2, name: '新签返' },
        { id: 3, name: '老签返' }
      ],
      loading: false,
      isEdit: false,
      shopName: '',
      submitStatus: 1,
      unCheckEditTag: false, // 无需审核编辑标识符
      uploadExtendData: {
        prefix: 'sale_report'
      },
      uploadedImg: [],
      kfuploadImg: [],
      previewTemp: [],
      dialogVisiblePreview: false, // 图片预览
      uploadContentType: {
        'token': localStorage.getItem('token')
      },
      timer: '',
      fileList: [],
      upload_img_url: this.$qzconfig.base_api + '/uploads/upimg',
      vipContractList: [], // 会员列表
      reportContractList: [], // 报备列表
      orderFenAvg: '', // 近三个月平均每天分单
      isFirst: false// 是否第一次进入页面
    }
  },
  watch: {
    'ruleForm.current_fen_orders'(val) {
      const that = this
      const patrn = /(^-?[0-9]\d*$)/
      const current = val
      const promise = this.ruleForm.promise_orders
      const isFirst = that.isFirst
      if (patrn.test(current) && patrn.test(promise) && isFirst) {
        that.ruleForm.delay_promise_orders = Number(promise) - Number(current)
      }
    },
    'ruleForm.promise_orders'(val) {
      const that = this
      const patrn = /(^-?[0-9]\d*$)/
      const current = this.ruleForm.current_fen_orders
      const promise = val
      const isFirst = that.isFirst
      if (patrn.test(current) && patrn.test(promise) && isFirst) {
        that.ruleForm.delay_promise_orders = Number(promise) - Number(current)
      }
    },
    'ruleForm.delay_promise_orders'(val) {
      const that = this
      const patrn = /(^-?[0-9]\d*$)/
      if (patrn.test(val) && that.orderFenAvg) {
        if (that.orderFenAvg) {
          that.ruleForm.delay_days = Math.ceil(val / that.orderFenAvg)
        } else {
          that.$message.warning('近三个月平均每天分单为0')
        }
      } else {
        if (this.isFirst) {
          that.ruleForm.delay_days = ''
        }
      }
    },
    'ruleForm.delay_days'(val) {
      const that = this
      const patrn = /(^-?[0-9]\d*$)/
      if (patrn.test(val)) {
        that.ruleForm.delay_real_days = Math.ceil(val * 0.7)
      } else {
        that.ruleForm.delay_real_days = ''
        that.$message.warning('近三个月平均每天分单为0')
      }
    },
    'ruleForm.delay_real_days'(val) {
      const that = this
      const startTime = that.ruleForm.delay_contract_start
      if (val) {
        let dateTime = new Date(startTime)
        dateTime = dateTime.setDate(dateTime.getDate() + Number(val) - 1)
        dateTime = fortmatDate(new Date(dateTime))
        that.ruleForm.delay_contract_end = dateTime
      } else {
        that.ruleForm.delay_contract_end = ''
      }
    },
    'ruleForm.delay_contract_start'(val) {
      const that = this
      const addDay = Number(that.ruleForm.delay_real_days)
      let dateTime = new Date(val)
      dateTime = dateTime.setDate(dateTime.getDate() + addDay - 1)
      dateTime = fortmatDate(new Date(dateTime))
      if (val && addDay) {
        that.ruleForm.delay_contract_end = dateTime
      }
    }

  },
  mounted() {
  },
  created() {
    // 根据编辑还是添加标识符确定是否需要请求数据
    if (this.operationActionTag === 'edit') {
      this.id = this.$route.query.id
      this.isEdit = true
      this.getMemberReportDetail()
      this.$nextTick(() => {
        const el = document.getElementsByClassName('tags-view-item')[0]
        el.innerHTML = el.innerHTML.replace('添加', '编辑')
      })
    } else {
      this.ruleForm.delay_contract_start = fortmatDate(new Date())
    }
  },
  beforeDestroy() {
    clearTimeout(this.timer)
  },
  methods: {
    // 网店代码获取公司信息
    getCompanyInfo(n) {
      const that = this
      if (!that.ruleForm.companyId) {
        that.$message.warning('请输入网店代码')
        return
      }
      that.loading = true
      fetchFindDelayCompany({
        company_id: that.ruleForm.companyId
      }).then(res => {
        that.loading = false
        if (res.data.error_code === 0 && res.data.data) {
          const data = res.data.data
          const companyInfo = data.company_info
          that.ruleForm.company_name = companyInfo.company_qc
          that.ruleForm.company_mode = companyInfo.company_mode
          if (companyInfo.company_mode === 1) {
            that.ruleForm.viptype = companyInfo.viptype
          } else {
            that.ruleForm.back_ratio = companyInfo.back_ratio
          }
          if (data.report_contract_list.length === 0 && data.vip_contract_list.length === 0) {
            that.$message.warning('暂无合同记录')
            return
          }
          if (data.report_contract_list.length > 0) {
            const list = data.report_contract_list
            that.reportContractList = list
            if (n !== 1) {
              that.ruleForm.report_id = list[0].report_id
              that.ruleForm.promise_orders = list[0].current_promised_orders_fen
              that.ruleForm.current_contract_start = list[0].current_contract_start
              that.ruleForm.current_contract_end = list[0].current_contract_end
            }
          } else {
            that.reportContractList = []
            that.ruleForm.report_id = ''
            that.$message.warning('暂无本次报备')
          }
          if (data.vip_contract_list.length > 0) {
            const list = data.vip_contract_list
            that.vipContractList = list
            if (n !== 1) {
              that.ruleForm.current_vip_id = list[0].id
              that.ruleForm.report_cooperation_type = list[0].company_mode
              that.ruleForm.current_vip_start = list[0].start_time
              that.ruleForm.current_vip_end = list[0].end_time
              that.getCompanyAllotOrders()
            }
          } else {
            that.ruleForm.current_vip_id = ''
            that.vipContractList = []
            that.$message.warning('暂无本次会员')
          }
        } else {
          that.$message.error('抱歉未匹配到会员公司')
          that.setNoDate()
        }
      })
    },
    setNoDate() {
      const that = this
      that.reportContractList = []
      that.vipContractList = []
      that.ruleForm.company_name = ''
      that.ruleForm.company_mode = ''
      that.ruleForm.current_vip_id = ''
      that.ruleForm.report_id = ''
      that.ruleForm.viptype = ''
      that.ruleForm.back_ratio = ''
    },
    // 本次会员时间
    currentVipChange(e) {
      const that = this
      if (!that.ruleForm.current_vip_id) {
        that.$message.error('请选择本次会员时间')
        return
      }
      let obj = {}
      obj = that.vipContractList.find(function(item) {
        return item.id === e
      })
      that.ruleForm.current_vip_start = obj.start_time
      that.ruleForm.current_vip_end = obj.end_time
      that.ruleForm.report_cooperation_type = obj.company_mode
      that.getCompanyAllotOrders()
    },
    // 本次报备合同
    promiseOrdersChange(e) {
      const that = this
      let obj = {}
      obj = that.reportContractList.find(function(item) {
        return item.report_id === e
      })
      that.ruleForm.promise_orders = obj.current_promised_orders_fen
      that.ruleForm.current_contract_start = obj.current_contract_start
      that.ruleForm.current_contract_end = obj.current_contract_end
    },
    getCompanyAllotOrders() {
      const that = this
      fetchCompanyAllotOrders({
        company_id: that.ruleForm.companyId,
        date_begin: that.ruleForm.current_vip_start,
        date_end: that.ruleForm.current_vip_end
      }).then(res => {
        if (res.data.error_code === 0 && res.data.data) {
          that.ruleForm.current_fen_orders = res.data.data.fen_orders
          that.orderFenAvg = res.data.data.order_fen_avg
        } else {
          that.$message.error('重新选择')
        }
      })
    },
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.handleAjax()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    handleAjax() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchMemberReportAdd(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '保存成功'
          })
          setTimeout(() => {
            this.$router.push('/memberReport/list')
          }, 1000)
        } else {
          this.$message.error(res.data.error_msg)
        }
        this.loading = false
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
        this.loading = false
      })
    },
    // 上传数据获取
    handleArguments() {
      // 图片列表
      if (Array.isArray(this.uploadedImg)) {
        this.uploadedImg = this.uploadedImg.join(',')
      }
      const queryObj = {
        id: this.id,
        company_name: this.ruleForm.company_name,
        cooperation_type: this.memberTypeId,
        company_id: this.ruleForm.companyId,
        remarke: this.ruleForm.remarke,
        status: this.submitStatus,
        imgs: this.uploadedImg,
        current_vip_id: this.ruleForm.current_vip_id,
        current_vip_start: this.ruleForm.current_vip_start,
        current_vip_end: this.ruleForm.current_vip_end,
        company_mode: this.ruleForm.company_mode,
        current_fen_orders: this.ruleForm.current_fen_orders,
        promise_orders: this.ruleForm.promise_orders,
        delay_promise_orders: this.ruleForm.delay_promise_orders,
        delay_days: this.ruleForm.delay_days,
        delay_real_days: this.ruleForm.delay_real_days,
        delay_contract_start: this.ruleForm.delay_contract_start,
        delay_contract_end: this.ruleForm.delay_contract_end,
        current_contract_start: this.ruleForm.current_contract_start,
        current_contract_end: this.ruleForm.current_contract_end,
        report_id: this.ruleForm.report_id,
        report_cooperation_type: this.ruleForm.report_cooperation_type
      }
      return queryObj
    },
    handleSave(formName) {
      this.submitStatus = 1
      this.submitForm(formName)
    },
    handleSubmit(formName) {
      this.submitStatus = 2
      this.submitForm(formName)
    },
    handleCancel() {
      this.$router.push('/memberReport/list')
    },
    getMemberReportDetail() {
      const queryObj = {
        cooperation_type: this.memberTypeId,
        id: this.id
      }
      this.loading = true
      this.memberReportDetail(queryObj, this.setData)
    },
    setData(data) {
      const ret = data.info
      this.ruleForm.id = ret.id
      this.ruleForm.cooperation_type = ret.cooperation_type
      this.ruleForm.status = ret.status
      for (var i = 0; i < ret.img_list.length; i++) {
        const obj = { url: QZ_CONFIG.oss_img_host + ret.img_list[i] }
        this.fileList.push(obj)
      }
      this.uploadedImg = ret.img_list
      for (let i = 0; i < ret.kf_voucher_img.length; i++) {
        this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
      }
      this.kfuploadImg = this.kfuploadImg
      this.ruleForm.companyName = ret.company_name
      this.ruleForm.memberType = ret.cooperation_type_name
      this.ruleForm.companyId = ret.company_id
      this.ruleForm.remarke = ret.remarke
      this.ruleForm.current_vip_id = ret.current_vip_id
      this.ruleForm.current_vip_start = ret.current_vip_start
      this.ruleForm.current_vip_end = ret.current_vip_end
      this.ruleForm.company_mode = ret.company_mode
      this.ruleForm.current_fen_orders = ret.current_fen_orders
      this.ruleForm.promise_orders = ret.promise_orders
      this.ruleForm.delay_promise_orders = ret.delay_promise_orders
      this.ruleForm.delay_days = ret.delay_days
      this.ruleForm.delay_real_days = ret.delay_real_days
      this.ruleForm.delay_contract_start = fortmatDate(ret.delay_contract_start * 1000)
      this.ruleForm.delay_contract_end = fortmatDate(ret.delay_contract_end * 1000)
      this.ruleForm.current_contract_start = fortmatDate(ret.current_contract_start * 1000)
      this.ruleForm.current_contract_end = fortmatDate(ret.current_contract_end * 1000)
      this.ruleForm.report_id = ret.report_id
      this.ruleForm.report_cooperation_type	 = ret.report_cooperation_type
      if (ret.company_mode === 1) {
        this.ruleForm.viptype = ret.viptype
      } else {
        this.ruleForm.back_ratio = ret.back_ratio
      }
      this.getCompanyInfo(1)
      this.loading = false
      this.timer = setTimeout(this.setIsFirst, 1000)
    },
    handleUploadSuccess(res, file, fileList) {
      this.uploadedImg.push(res.data.img_path)
    },
    handleRemove(res, file, fileList) {
      this.uploadedImg.forEach((item, index) => {
        if (res.url.indexOf(item) !== -1) {
          this.uploadedImg.splice(index, 1)
        }
      })
    },
    preview(item) {
      this.previewTemp = []
      this.previewTemp.push(item)
      this.dialogVisiblePreview = true
    },
    setIsFirst() {
      this.isFirst = true
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .delay-member-order {
    .text-center {
      text-align: center;
    }

    .keftips {
      width: 500px;
      position: absolute;
      left: 30px;
      top: -6px;
    }

    .el-range-separator {
      padding: 0;
    }

    .exclude-time {
      position: absolute;
      width: 850px;
      left: 0px;
      top: 0px;
    }

    .el-form-item {
      margin-bottom: 0 !important;
    }

    .el-form-item__content {
      margin-left: 0 !important;
    }

    .el-upload-list--picture .el-upload-list__item {
      width: 100px;
      float: left;
      margin-right: 10px;
    }

    .el-upload-dragger {
      width: 100px;
      height: 100px;
      line-height: 100px;
      text-align: center;
      float: left;
    }

    .inline-block {
      display: inline-block;
    }

    .ml-20 {
      margin-left: 20px;
    }
    .ml-40 {
      margin-left: 40px;
    }
    .lh-40{
      padding-left: 40px;
    }
    .w300{
      width: 300px;
    }

    .upimg {
      display: inline-block;
      width: 100px;
      height: 100px;
      margin-right: 10px;
    }
  }

</style>
