<template>
  <div class="small-report-add">
    <el-form
      ref="ruleForm"
      v-loading="loading"
      :model="ruleForm"
      :rules="rules"
      label-width="150px"
      class="demo-ruleForm"
    >
      <el-form-item v-if="memberType === 10 || memberType === 13" label="网店代码：" prop="companyId">
        <el-input v-model="ruleForm.companyId" :maxlength="20" placeholder="请输入" @keyup.enter.native="getCompanyInfo(ruleForm.companyId)" />
        <el-button type="primary" :loading="companyInfoloading" @click="getCompanyInfoBtn">确定</el-button>
      </el-form-item>
      <el-form-item v-if="memberType !== 8" label="公司名称：" prop="companyName">
        <el-input v-model="ruleForm.companyName" :maxlength="20" :placeholder="memberType !== 10 ?'请输入':'输入网店代码后显示' " :disabled="memberType === 10 || memberType === 13" />
      </el-form-item>
      <el-form-item v-if="memberType === 8" label="返点订单：" prop="order_id">
        <el-input
          v-model="ruleForm.order_id"
          placeholder="请输入"
        />
        <el-button type="primary" @click="getOrderInfo">保存</el-button>
      </el-form-item>
      <el-form-item label="合作类型：" prop="cooperation_type">
        <el-select v-model="ruleForm.cooperation_type" :disabled="!isEdit" placeholder="请选择">
          <el-option
            v-for="item in memberTypeOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType === 8" label="签单会员：" prop="company_name">
        <el-input v-model="ruleForm.company_name" placeholder="请输入" :disabled="true" />
      </el-form-item>
      <el-form-item label="城市：" prop="city_id">
        <el-select v-model="ruleForm.city_id" filterable placeholder="请选择" :disabled="memberType === 8 || memberType === 10 || memberType === 13">
          <el-option
            v-for="item in citys"
            :key="item.cid"
            :label="item.city_name"
            :value="item.cid"
          />
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType === 6 || memberType === 8 || memberType === 10" label="返点比例：" prop="backRatio">
        <el-select v-model="ruleForm.backRatio" placeholder="请选择">
          <el-option
            v-for="item in backRatioOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
            :disabled="memberType === 8 && item.value==='0%'"
          />
        </el-select>
        <span v-if="memberType === 8" style="color:red;">&emsp;返点金额：{{ ruleForm.backRatio ? (ruleForm.orderSignMoney * parseInt(ruleForm.backRatio)/100) : 0 }}</span>
      </el-form-item>
      <el-form-item v-if="memberType === 15" label="返点比例：">
        <el-select key="标杆会员（保产值）" v-model="ruleForm.backRatio" placeholder="请选择" disabled>
          <el-option label="标杆会员（保产值）" value="1.0" />
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType === 1" label="单倍/几倍：" prop="ratio">
        <el-select v-model="ruleForm.ratio" placeholder="请选择">
          <el-option
            v-for="item in ratioOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType === 14" label="单倍/几倍：">
        <el-select key="标杆会员（保产值）" v-model="ruleForm.ratio" placeholder="请选择" disabled>
          <el-option label="标杆会员（保产值）" value="1.0" />
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType === 10 || memberType === 13" label="保证金余额：" prop="deposit_money">
        <el-input v-model="ruleForm.deposit_money" :maxlength="20" placeholder="输入网店代码后显示" :disabled="true" />
      </el-form-item>
      <el-form-item v-if="memberType === 10" label="保证金抵扣轮单费：" prop="depositToRoundMoney">
        <el-input v-model="ruleForm.depositToRoundMoney" :maxlength="20" placeholder="请输入" />
      </el-form-item>
      <el-form-item v-if="memberType === 13" label="保证金抵扣金额：" prop="depositToPlatformMoney">
        <el-input v-model="ruleForm.depositToPlatformMoney" :maxlength="20" placeholder="请输入" />
      </el-form-item>
      <el-form-item :key="memberType === 11 ? '退款':'汇款'" :label="(memberType === 11 ? '退款':'汇款') + '时间：'" prop="paymentTime">
        <el-date-picker
          v-model="ruleForm.paymentTime"
          type="date"
          :placeholder="(memberType === 11 ? '退款':'汇款') + '时间'"
          :picker-options="pickerOptions"
        />
      </el-form-item>
      <el-form-item v-if="memberType !== 10 && memberType !== 11 && memberType !== 13" label="收款金额：" prop="paymentMoney">
        <el-input v-model="ruleForm.paymentMoney" :maxlength="10" :placeholder="memberType === 8?'请输入，未填写默认为0':'请输入'" />
      </el-form-item>
      <el-form-item v-if="memberType === 11" label="退款金额：" prop="refundMoney">
        <el-input v-model="ruleForm.refundMoney" :maxlength="10" :placeholder="memberType === 8?'请输入，未填写默认为0':'请输入'" />
      </el-form-item>
      <el-form-item v-if="memberType === 1 || memberType === 14" label="会员费：" prop="round_order_money">
        <el-input v-model="ruleForm.round_order_money" placeholder="请输入" />
      </el-form-item>
      <el-form-item v-if="memberType === 6 || memberType === 15" label="轮单费：" prop="round_order_money">
        <el-input v-model="ruleForm.round_order_money" placeholder="请输入" />
      </el-form-item>
      <el-form-item v-if="memberType === 1 || memberType === 6 || memberType === 14 || memberType === 15" label="平台使用费：" prop="platform_money">
        <el-input v-model="ruleForm.platform_money" :maxlength="10" placeholder="0" />
      </el-form-item>
      <el-form-item v-if="memberType === 1 || memberType === 6 || memberType === 12 || memberType === 13|| memberType === 14 || memberType === 15" label="平台使用费有效期：" prop="platformMoneyDate">
        <el-date-picker
          v-model="ruleForm.platformMoneyDate"
          type="daterange"
          range-separator="-"
          value-format="yyyy-MM-dd"
          start-placeholder="选择开始日期"
          end-placeholder="选择结束日期"
          :disabled="!ruleForm.platform_money && memberType !== 12 && memberType !== 13"
        />
      </el-form-item>
      <el-form-item v-if="memberType === 6 || memberType === 15" label="保证金：" prop="deposit_money">
        <el-input v-model="ruleForm.deposit_money" placeholder="请输入" />
        <span>(保证金不算业绩)</span>
      </el-form-item>
      <el-form-item v-if="memberType === 8" label="保证金抵扣：" prop="deposit_money">
        <el-input v-model="ruleForm.deposit_money" placeholder="请输入" />
        <span>(保证金抵扣算业绩)</span>
      </el-form-item>
      <el-form-item v-if="memberType === 8" label="轮单费抵扣：" prop="round_order_money">
        <el-input v-model="ruleForm.round_order_money" placeholder="请输入" />
        <span>(轮单费抵扣不算业绩)</span>
      </el-form-item>
      <el-form-item v-if="memberType !== 10 && memberType !== 11 && memberType !== 12 && memberType !== 13" label="其他金额：" prop="otherMoney">
        <el-input v-model="ruleForm.otherMoney" placeholder="无数据可不填写" />
        <span>(其他金额不算业绩)</span>
      </el-form-item>
      <el-form-item v-if="memberType !== 10 && memberType !== 11 && memberType !== 12 && memberType !== 13" label="其他金额类型：" prop="otherMoneyRemark">
        <el-input v-model="ruleForm.otherMoneyRemark" :placeholder="!ruleForm.otherMoney?'请输入其他金额的类型':'如返利，定金退回等'" :disabled="!ruleForm.otherMoney" />
      </el-form-item>
      <el-form-item :label="(memberType === 11 ? '退款':'收款') + '类型：'" prop="paymentType">
        <el-select v-model="ruleForm.paymentType" placeholder="请选择" :disabled="memberType === 8 || memberType === 9 || memberType === 10 || memberType === 13">
          <template v-for="item in paymentOptions">
            <el-option
              v-if="(item.id === 5 && memberType === 8) || (item.id === 6 && memberType === 9) || (item.id !== 5 && item.id !== 6)"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </template>
        </el-select>
      </el-form-item>
      <el-form-item v-if="memberType !== 10 && memberType !== 11 && memberType !== 13" prop="paymentName" label="汇款方名称：">
        <el-input v-model="ruleForm.paymentName" :maxlength="20" placeholder="请输入" />
        <span class="red">(请输入汇款方户名)</span>
      </el-form-item>
      <el-form-item v-if="memberType !== 10 && memberType !== 11 && memberType !== 13" label="收款方名称：" prop="payee_list">
        <el-checkbox-group v-model="ruleForm.payee_list">
          <el-checkbox-button
            v-for="item in payment_payee_list"
            :key="item.paixu"
            :label="item.id"
            type="payee_list"
          >{{ item.name }}</el-checkbox-button>
        </el-checkbox-group>
        <div v-if="memberType === 8" class="red">未收款可不填</div>
      </el-form-item>
      <el-form-item v-if="memberType !== 11" label="其他业绩人：">
        <el-radio v-model="radio" :label="1">无</el-radio>
        <el-radio v-model="radio" :label="2">有</el-radio>
      </el-form-item>
      <el-form-item v-if="radio == 2 && memberType !== 11" class="people">
        <el-row v-for="(item, index) in ruleForm.person_list" :key="index">
          <el-col :span="3">
            <div class="lh-40">账号名称：</div>
          </el-col>
          <el-col :span="5">
            <el-select v-model="item.saler_id" filterable placeholder="请选择" class="userName">
              <el-option
                v-for="t in personList"
                :key="t.saler_id"
                :label="t.full_name"
                :value="t.saler_id"
              />
            </el-select>
          </el-col>
          <el-col :span="3">
            <div class="lh-40">分成比例：</div>
          </el-col>
          <el-col :span="4">
            <el-select v-model="item.share_ratio" filterable placeholder="请选择" class="userName">
              <el-option v-for="t in radioArry" :key="t" :label="t" :value="t" />
            </el-select>
          </el-col>
          <el-col :span="8" style="margin-left: 10px;margin-top:4px;">
            <el-button type="primary" @click="addRule">继续添加</el-button>
            <el-button type="danger" @click="remRule(item)">删除</el-button>
          </el-col>
        </el-row>
      </el-form-item>
      <el-form-item v-if="memberType === 11" prop="person_list" label="相关业绩人：" class="people">
        <el-row v-for="(item, index) in ruleForm.person_list" :key="index">
          <el-col :span="3">
            <div class="lh-40">账号名称：</div>
          </el-col>
          <el-col :span="5">
            <el-select v-model="item.saler_id" filterable placeholder="请选择" class="userName">
              <el-option
                v-for="t in personList"
                :key="t.saler_id"
                :label="t.full_name"
                :value="t.saler_id"
              />
            </el-select>
          </el-col>
          <el-col :span="3">
            <div class="lh-40">分成比例：</div>
          </el-col>
          <el-col :span="4">
            <el-select v-model="item.share_ratio" filterable placeholder="请选择" class="userName">
              <el-option v-for="t in radioArry" :key="t" :label="t" :value="t" />
            </el-select>
          </el-col>
          <el-col :span="8" style="margin-left: 10px;margin-top:4px;">
            <el-button type="primary" @click="addRule">继续添加</el-button>
            <el-button type="danger" @click="remRule(item)">删除</el-button>
          </el-col>
        </el-row>
      </el-form-item>
      <el-form-item :label="(memberType === 11 ? '退款':'汇款')+'凭证：'" prop="uploadedImg">
        <el-upload
          ref="certUpload"
          class="upload-demo"
          :action="uploadApi"
          :limit="20"
          :data="uploadExtendData"
          :headers="uploadContentType"
          :before-upload="beforeAction"
          :on-success="handleUploadSuccess"
          :file-list="fileList"
          :on-remove="handleRemove"
          drag
          list-type="picture"
        >
          <div class="el-upload__text">
            <em>点击上传</em>
          </div>
        </el-upload>
        <el-input v-model="ruleForm.uploadedImg[0]" style="display:none;" />
        <div v-if="memberType === 8" class="red">未收款可不填</div>
        <div v-if="memberType === 11" class="red">请附上财务已经把款退给该公司的退款记录</div>
      </el-form-item>
      <el-form-item label="备注：">
        <el-input v-model="ruleForm.remark" type="textarea" :rows="3" maxlength="255" placeholder="请输入" />
      </el-form-item>
      <!--图片预览-->
      <el-dialog :visible.sync="dialogVisiblePreview" :close-on-click-modal="false" width="60%">
        <div v-for="item in previewTemp" :key="item" class="img">
          <img
            :src="item"
            alt
            style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"
          >
        </div>
      </el-dialog>
      <el-dialog title="收款方明细" :visible.sync="dialogFormVisible" width="700px" :close-on-click-modal="false">
        <p class="red text-center">存在多收款方名称需填写明细</p>
        <el-form ref="payeeForm" :model="ruleForm" label-width="220px">
          <el-form-item label="收款金额:">
            <span>{{ ruleForm.paymentMoney }}</span>
          </el-form-item>
          <el-form-item
            v-for="(item, index) in ruleForm.payee_list2"
            :key="item.payee_type"
            :label="item.payee_type_name + ':'"
            :prop="'payee_list2.' + index + '.payee_money'"
            :rules="[
              {required: true, message: `请输入${item.payee_type_name}收款金额`, trigger: ['blur', 'change']},
              {pattern: /^[+]{0,1}(\d+)$/, message: '请输入正整数', trigger: ['blur', 'change'] }
            ]"
          >
            <el-input v-model="item.payee_money" autocomplete="off" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">取 消</el-button>
          <el-button type="primary" @click="payeeFormOk">确 定</el-button>
        </div>
      </el-dialog>
      <el-form-item>
        <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
        <el-button type="success" @click="handleSubmit('ruleForm')">提交</el-button>
        <el-button @click="handleCancel">取消</el-button>
      </el-form-item>
      <!--  <el-row>
        <p class="red">提示：提交后将推送至{{ memberType !== 11 ?'副总裁':'李亚北处' }}审核</p>
      </el-row> -->
    </el-form>
  </div>
</template>

<script>
import { fetchSmallReportSave, fetchPerPerformance, fetchSigninfo } from '@/api/smallReport'
import { fetchFindUser } from '@/api/memberReport'
import smallReportEdit from '@/mixins/memberReport'
import { fortmatDate } from '@/utils/index'
import { fetchCityList } from '@/api/common'
export default {
  name: 'CommissionMember',
  mixins: [smallReportEdit],
  props: {
    memberTypeId: {
      type: String,
      default: ''
    },
    isEdit: {
      type: Boolean,
      default: false
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
    const validatePositiveInt = (rule, value, callback) => {
      const reg = /^[1-9]\d*$/
      if (value && !reg.test(value)) {
        callback(new Error('请输入正整数'))
      } else {
        callback()
      }
    }
    const valPaymentMoney = (rule, value, callback) => {
      const type = this.memberType
      if (type !== 8 && !value) {
        rule.required = true
        callback(new Error('请输入收款金额'))
      } else {
        callback()
      }
    }
    const valDeCompare = (rule, value, callback) => {
      const type = this.memberType
      const paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      const depositMoney = Number(this.ruleForm.deposit_money) // 保证金
      if (depositMoney > paymentMoney && type !== 8 && type !== 10 && type !== 13) {
        callback(new Error('保证金不能超出收款金额'))
      } else {
        callback()
      }
    }
    const valRoundCompare = (rule, value, callback) => {
      const type = this.memberType
      const paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      const roundOrderMoney = Number(this.ruleForm.round_order_money) // 轮单费
      if (roundOrderMoney > paymentMoney && type !== 8) {
        if (type === 6) {
          callback(new Error('轮单费不能超出收款金额'))
        } else {
          callback(new Error('会员费不能超出收款金额'))
        }
      } else {
        callback()
      }
    }
    const valPlatformMoney = (rule, value, callback) => {
      const type = this.memberType
      const paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      const PlatformMoney = Number(this.ruleForm.platform_money) // 平台使用费
      if (PlatformMoney > paymentMoney && type !== 8) {
        callback(new Error('平台使用费不能超出收款金额'))
      } else {
        callback()
      }
    }
    const valPlatformMoneyDate = (rule, value, callback) => {
      let money = 0
      if (this.memberType === 12) {
        money = Number(this.ruleForm.paymentMoney) // 收款金额
      } else {
        money = Number(this.ruleForm.platform_money) // 平台使用费
      }
      if (money && !value) {
        callback(new Error('请选择平台使用费有效期'))
      } else {
        callback()
      }
    }
    const valOtherMoney = (rule, value, callback) => {
      const paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      const otherMoney = Number(this.ruleForm.otherMoney) // 其他金额
      if (otherMoney > paymentMoney) {
        callback(new Error('其他金额合计不得超出收款金额'))
      } else {
        callback()
      }
    }
    const valOtherMoneyRemark = (rule, value, callback) => {
      if (this.ruleForm.otherMoney && !this.ruleForm.otherMoneyRemark) {
        callback(new Error('请输入其他金额类型'))
      } else {
        callback()
      }
    }
    const valDepositToRoundMoney = (rule, value, callback) => {
      const depositMoney = Number(this.ruleForm.deposit_money)
      const val = Number(value)
      if (depositMoney && val > depositMoney) {
        callback(new Error('抵扣金额不得超出保证金余额'))
      } else {
        callback()
      }
    }
    const valDepositToPlatformMoney = (rule, value, callback) => {
      const depositMoney = Number(this.ruleForm.deposit_money)
      const val = Number(value)
      if (depositMoney && val > depositMoney) {
        callback(new Error('抵扣金额不得超出保证金余额'))
      } else {
        callback()
      }
    }
    const valPayeeList = (rule, value, callback) => {
      const paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      if (paymentMoney && value.length === 0) {
        callback(new Error('请选择收款方名称'))
      } else {
        callback()
      }
    }
    const valUploadedImg = (rule, value, callback) => {
      const type = this.memberType
      let paymentMoney = ''
      if (type === 11) {
        paymentMoney = Number(this.ruleForm.refundMoney) // 退款金额
      } else {
        paymentMoney = Number(this.ruleForm.paymentMoney) // 收款金额
      }
      if (paymentMoney && value.length === 0 && this.memberType !== 13) {
        if (type === 11) {
          callback(new Error('请上传退款凭证'))
        } else {
          callback(new Error('请上传汇款凭证'))
        }
      } else {
        callback()
      }
    }
    return {
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() > Date.now()
        }
      },
      uploadApi: this.$qzconfig.base_api + '/uploads/upimg',
      citys: [],
      cityBlurFlag: null,
      comBlurFlag: null,
      memberType: 0,
      id: '',
      companyId: '',
      ruleForm: {
        domains: [
          {
            payee_type_name: '1',
            payee_money: 0
          },
          {
            payee_type_name: '1',
            payee_money: 0
          }
        ],
        id: '',
        cooperation_type: '',
        companyName: '',
        company_name: '',
        companyId: '',
        city_id: '',
        ratio: '',
        backRatio: '',
        paymentName: '',
        paymentTime: '',
        paymentMoney: '',
        refundMoney: '',
        round_order_money: '',
        deposit_money: '',
        depositToRoundMoney: '',
        paymentType: '',
        payee_list: [],
        uploadedImg: [],
        orderSignMoney: '',
        otherMoney: '',
        otherMoneyRemark: '',
        person_list: [
          {
            saler_id: '',
            share_ratio: ''
          }
        ],
        payee_list2: [
          {
            payee_type: '',
            payee_type_name: '',
            payee_money: ''
          }
        ],
        remark: '',
        depositToPlatformMoney: '', // 保证金抵扣平台使用费金额
        platform_money: '', // 平台使用费
        platformMoneyDate: '', // 平台使用费有效期时间
        platform_money_start_date: '', // 平台使用费有效期开始时间
        platform_money_end_date: '' // 平台使用费有效期结束时间
      },
      uploadedImg: [], // 保存时上传图片字段
      rules: {
        order_id: [
          { required: true, message: '请输入订单号', trigger: 'blur' }
        ],
        companyId: [
          { required: true, message: '请输入网店代码', trigger: 'blur' },
          { validator: validateInt, trigger: ['blur', 'change'] }
        ],
        companyName: [
          { required: true, message: '请输入公司名称', trigger: 'blur' }
        ],
        company_name: [
          { required: true, message: '请输入签单会员', trigger: 'change' }
        ],
        city_id: [
          { required: true, message: '请选择城市', trigger: 'change' }
        ],
        cooperation_type: [
          { required: true, message: '请选择合作类型', trigger: 'change' }
        ],
        paymentTime: [
          { required: true, message: '请选择汇款时间', trigger: 'change' }
        ],
        paymentName: [
          { required: true, message: '请选择汇款方名称', trigger: 'change' }
        ],
        backRatio: [
          { required: true, message: '请选择返点比例', trigger: 'change' }
        ],
        ratio: [
          { required: true, message: '请选择单倍/几倍', trigger: 'change' }
        ],
        depositToRoundMoney: [
          { required: true, message: '请输入保证金抵扣轮单费', trigger: 'blur' },
          { validator: validateInt, trigger: 'change' },
          { validator: valDepositToRoundMoney, trigger: ['blur', 'change'] }
        ],
        paymentMoney: [
          { required: true, message: '请输入收款金额', trigger: 'blur' },
          { validator: valPaymentMoney, trigger: ['blur', 'change'] },
          { validator: validateInt, trigger: ['blur', 'change'] }
        ],
        refundMoney: [
          { required: true, message: '请输入退款金额', trigger: 'blur' },
          { validator: validatePositiveInt, trigger: ['blur', 'change'] }
        ],
        deposit_money: [
          { validator: validateInt, trigger: ['blur', 'change'] },
          { validator: valDeCompare, trigger: 'blur' }
        ],
        round_order_money: [
          { required: true, message: '请输入轮单费', trigger: 'blur' },
          { validator: validateInt, trigger: ['blur', 'change'] },
          { validator: valRoundCompare, trigger: ['blur', 'change'] }
        ],
        platform_money: [
          { validator: validateInt, trigger: ['blur', 'change'] },
          { validator: valPlatformMoney, trigger: ['blur', 'change'] }
        ],
        platformMoneyDate: [
          { required: true, message: '请选择平台使用费有效期', trigger: 'blur' },
          { validator: valPlatformMoneyDate, trigger: ['blur', 'change'] }
        ],
        otherMoney: [
          { validator: validateInt, trigger: 'change' },
          { validator: valOtherMoney, trigger: 'blur' }
        ],
        otherMoneyRemark: [
          { validator: valOtherMoneyRemark, trigger: ['blur', 'change'] }
        ],
        payee_list: [
          { required: true, validator: valPayeeList, trigger: 'change' }
        ],
        person_list: [
          { required: true, message: '请选择相关业绩人', trigger: 'change' }
        ],
        paymentType: [
          { required: true, message: '请选择收款类型', trigger: 'change' }
        ],
        depositToPlatformMoney: [
          { required: true, message: '请输入保证金抵扣金额', trigger: 'blur' },
          { validator: validatePositiveInt, trigger: ['blur', 'change'] },
          { validator: valDepositToPlatformMoney, trigger: ['blur', 'change'] }
        ],
        uploadedImg: [
          { required: true, validator: valUploadedImg, trigger: 'change' }
          // { required: true, message: '请上传汇款凭证' }
        ]
      },
      backRatioOptions: [],
      ratioOptions: [{ value: '', label: '请选择' }],
      paymentOptions: [],
      memberTypeOptions: [],
      submitStatus: 0, // 保存状态为0，提交状态为1
      loading: false,
      previewTemp: [],
      dialogVisiblePreview: false,
      uploadExtendData: {
        prefix: 'sale_report'
      },
      uploadContentType: {
        token: localStorage.getItem('token')
      },
      fileList: [],
      backOrRatio: true,
      radio: 1,
      personList: [],
      radioArry: [],
      payment_payee_list: [],
      companyInfoloading: false,
      dialogFormVisible: false,
      paymentTypeList: [], // 汇款类型
      refundTypeList: [] // 退款类型
    }
  },
  computed: {
    timeDefault() {
      var date = new Date()
      var s1 = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate()
      return s1
    }
  },
  watch: {
    memberTypeId(val) {
      this.ruleForm.cooperation_type = val
    },
    'ruleForm.cooperation_type': {
      handler(val) {
        const value = parseInt(val)
        if (!this.isEdit) {
          this.ruleForm.paymentType = ''
        }
        if (value === 1 || value === 2 || value === 3 || value === 7) {
          this.memberType = 1 // 单倍 公司名称
          this.rules.round_order_money[0].message = '请输入会员费'
        } else if (value === 4) {
          this.memberType = 4 // 三维家
        } else if (value === 6) {
          this.memberType = 6 // 签单返点会员 公司名称 返点比例 轮单费 保证金
          this.rules.round_order_money[0].message = '请输入轮单费'
        } else if (value === 8) {
          this.memberType = 8 // 会员返点 返点比例 订单编号 签单会员
          this.ruleForm.paymentType = 5 // 固定收款类型
          if (this.ruleForm.backRatio === '0%') {
            this.ruleForm.backRatio = ''
          }
          this.rules.uploadedImg[0].required = false
          this.rules.round_order_money[0].required = false
        } else if (value === 9) {
          this.memberType = 9 // 物料
          this.ruleForm.paymentType = 6 // 固定收款类型
          this.ruleForm.backRatio = ''
        } else if (value === 10) {
          this.memberType = 10 // 签返（保证金转轮单费）
          this.ruleForm.paymentType = 2 // 固定收款类型
          this.rules.paymentMoney[0].required = false
          this.rules.uploadedImg[0].required = false
        } else if (value === 11) {
          this.memberType = 11 // 会员退款
          this.rules.person_list[0].required = true
          this.paymentOptions = this.refundTypeList
          this.rules.paymentTime[0].message = '请选择退款时间'
          this.rules.paymentType[0].message = '请选择退款类型'
        } else if (value === 12) {
          this.memberType = 12 // 平台使用费
          this.rules.platformMoneyDate[0].required = true
          this.rules.paymentName[0].required = true
        } else if (value === 13) {
          this.memberType = 13 // 平台使用费
          this.ruleForm.paymentType = 2 // 固定收款类型
          this.rules.paymentMoney[0].required = false
          this.rules.uploadedImg[0].required = false
          this.rules.platformMoneyDate[0].required = true
        } else if (value === 14) {
          this.memberType = 14 // 平台使用费
          this.rules.round_order_money[0].message = '请输入会员费'
          this.ruleForm.ratio = '1.0'
        } else if (value === 15) {
          this.memberType = 15 // 平台使用费
          this.rules.round_order_money[0].message = '请输入轮单费'
          this.ruleForm.backRatio = '1.0'
        }
        if (value !== 8) {
          this.rules.round_order_money[0].required = true
        }
        if (value !== 10 && value !== 13) {
          this.rules.paymentMoney[0].required = true
        }
        if (value !== 8 && value !== 10 && value !== 13) {
          this.rules.uploadedImg[0].required = true
        }
        if (value !== 11) {
          this.rules.paymentTime[0].message = '请选择汇款时间'
          this.rules.paymentType[0].message = '请选择收款类型'
          this.paymentOptions = this.paymentTypeList
          this.rules.person_list[0].required = false
        }
        if (value !== 12) {
          this.rules.paymentName[0].required = false
        }
        if (value !== 12 && value !== 13) {
          this.rules.platformMoneyDate[0].required = false
        }
      },
      immediate: true,
      deep: true
    }
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    // 根据编辑还是添加标识符确定是否需要请求数据
    if (this.isEdit) {
      this.ruleForm.id = this.$route.query.id
      if (this.ruleForm.person_list) {
        this.radio = 2
      } else {
        this.radio = 1
        this.ruleForm.person_list = [
          {
            saler_id: '',
            share_ratio: ''
          }
        ]
      }
      this.$nextTick(() => {
        const el = document.getElementsByClassName('tags-view-item')[0]
        el.innerHTML = el.innerHTML.replace('添加', '编辑')
      })
    } else {
      this.radio = 1
      this.ruleForm.person_list = [
        {
          saler_id: '',
          share_ratio: ''
        }
      ]
      this.ruleForm.paymentTime = this.timeDefault
    }
    this.ruleForm.cooperation_type = this.memberTypeId
    if (this.$route.query.orderId) {
      this.ruleForm.order_id = this.$route.query.orderId
      this.getSigninfo(this.ruleForm.order_id)
    }
    for (var i = 1; i <= 100; i++) {
      this.radioArry.push(i)
    }
    for (var j = 1; j < 21; j++) {
      this.ratioOptions.push({ value: j * 0.5, label: j * 0.5 })
    }
    this.getSmallReportEdit()
    this.fetchPerPerformance()
  },
  methods: {
    addRule() {
      this.ruleForm.person_list.push({
        saler_id: '',
        share_ratio: ''
      })
    },
    remRule(item) {
      var index = this.ruleForm.person_list.indexOf(item)
      if (index !== -1) {
        this.ruleForm.person_list.splice(index, 1)
      }
      if (this.ruleForm.person_list.length === 0) {
        this.radio = 1
        this.ruleForm.person_list = [
          {
            saler_id: '',
            share_ratio: ''
          }
        ]
      }
    },
    getSigninfo(id) {
      fetchSigninfo({ order_id: id }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          const data = res.data.data.orderinfo
          if (data.back_ratio_show !== '0%' && data.back_ratio_show !== '' && data.back_ratio_show) {
            this.ruleForm.backRatio = data.back_ratio_show
          } else {
            this.ruleForm.backRatio = ''
          }
          this.ruleForm.city_id = data.user_city
          this.ruleForm.company_name = data.company_name
          this.ruleForm.orderSignMoney = data.order_sign_money
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    getOrderInfo() {
      if (this.ruleForm.order_id) {
        this.getSigninfo(this.ruleForm.order_id)
      } else {
        this.$message.warning('请输入返点订单')
      }
    },
    loadAllCity() {
      fetchCityList().then(res => {
        if (
          parseInt(res.status) === 200 &&
          parseInt(res.data.error_code) === 0
        ) {
          const data = res.data.data[0]
          data.forEach((item, index) => {
            this.citys.push(item)
          })
        } else {
          this.citys = []
        }
      })
    },
    fetchPerPerformance() {
      fetchPerPerformance({
        id: this.ruleForm.id,
        saler_ids: '',
        saler_name: ''
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.personList = res.data.data.list
        }
      })
    },
    submitForm(formName) {
      let percent = 0
      const that = this
      for (var i = 0; i < that.ruleForm.person_list.length; i++) {
        if (that.ruleForm.person_list[i].saler_id !== '' && that.ruleForm.person_list[i].share_ratio === '') {
          that.$message.error('请选择其他业绩人的分成比例')
          return
        } else if (that.ruleForm.person_list[i].saler_id === '' && that.ruleForm.person_list[i].share_ratio !== '') {
          that.$message.error('请选择其他业绩人的账号名称')
          return
        }
        percent += parseInt(that.ruleForm.person_list[i].share_ratio)
      }
      if (percent > 100) {
        that.$message.error('业绩人的分成比例总和不得超出100%')
        return false
      }
      if (that.memberType === 8 && Number(that.ruleForm.paymentMoney) + Number(that.ruleForm.deposit_money) + Number(that.ruleForm.round_order_money) === 0) {
        that.$message.error('收款金额、轮单费抵扣、保证金额不得同时为0！')
        return false
      }
      if (that.memberType === 13) {
        this.ruleForm.round_order_money = ''
      }
      that.$refs[formName].validate(valid => {
        if (valid) {
          if (that.memberType === 11 && !that.ruleForm.person_list[0].saler_id && !that.ruleForm.person_list[0].share_ratio) {
            that.$message.error('请选择相关业绩人及分成比例')
            return
          }
          if (that.memberType === 11 && !that.ruleForm.paymentType) {
            that.$message.error('请选择退款类型')
            return
          }
          that.setPayeeList()
        } else {
          return false
        }
      })
    },
    handleAjax() {
      // queryObj有可能返回一个false
      const queryObj = this.handleArguments()
      if (!queryObj) {
        return
      }
      this.loading = true
      fetchSmallReportSave(queryObj)
        .then(res => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            this.$message({
              type: 'success',
              message: '保存成功'
            })
            setTimeout(() => {
              this.$router.push('/smallReport/list')
            }, 1000)
          } else {
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        })
        .catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.loading = false
        })
    },
    handleArguments() {
      if (this.ruleForm.uploadedImg.length >= 0) {
        this.uploadedImg = this.ruleForm.uploadedImg.join(',')
      }
      const queryObj = {
        id: this.ruleForm.id, // 小报备ID（编辑时必填）
        company_name: this.ruleForm.companyName, // 公司名称
        company_id: this.companyId, // 公司id(网店代码)
        cooperation_type: this.ruleForm.cooperation_type, // 合作类型
        city_id: this.ruleForm.city_id, // 城市ID
        back_ratio: this.ruleForm.backRatio, // 返点比例（签返会员）
        viptype: this.ruleForm.ratio, // 倍数
        payment_uname: this.ruleForm.paymentName, // 汇款方名称
        payment_date: this.ruleForm.paymentTime, // 汇款时间
        payment_total_money: this.ruleForm.paymentMoney, // 收款金额
        refund_money: this.ruleForm.refundMoney, // 退款金额
        deposit_money: this.ruleForm.deposit_money, // 保证金
        deposit_to_round_money: this.ruleForm.depositToRoundMoney, // 保证金
        round_order_money: this.ruleForm.round_order_money, // 轮单金额
        payment_type: this.ruleForm.paymentType, // 收款类型
        payee_list: this.ruleForm.payee_list2, // 收款方
        auth_imgs: this.uploadedImg, // 汇款凭证（多个用，隔开）
        is_submit: this.submitStatus, // 是否提交（0：否；1：是）
        person_list: this.ruleForm.person_list[0].saler_id ? this.ruleForm.person_list : [], // 其他业绩人
        order_id: this.ruleForm.order_id, // 订单ID（会员返点时必填
        remark: this.ruleForm.remark, // 销售备注
        other_money: this.ruleForm.otherMoney, // 其他金额
        other_money_remark: this.ruleForm.otherMoneyRemark, // 其他金额类型
        deposit_to_platform_money: this.ruleForm.depositToPlatformMoney, // 保证金抵扣平台使用费金额
        platform_money: this.ruleForm.platform_money, // 平台使用费
        platform_money_start_date: this.ruleForm.platformMoneyDate ? this.ruleForm.platformMoneyDate[0] : '', // 平台使用费有效期开始时间
        platform_money_end_date: this.ruleForm.platformMoneyDate ? this.ruleForm.platformMoneyDate[1] : '' // 平台使用费有效期结束时间
      }
      if (!this.ruleForm.payee_list2[0].payee_money) {
        queryObj.payee_list = []
      }
      if (this.memberType === 6 || this.memberType === 8 || this.memberType === 10 || this.memberType === 15) {
        queryObj.viptype = ''
      } else {
        queryObj.back_ratio = ''
      }
      return queryObj
    },
    setPayeeList() {
      const that = this
      if (that.ruleForm.payee_list.length === 0) {
        that.handleAjax()
      } else if (that.ruleForm.payee_list.length === 1) {
        that.ruleForm.payee_list2 = [
          {
            'payee_type': that.ruleForm.payee_list[0],
            'payee_money': that.ruleForm.paymentMoney
          }
        ]
        that.handleAjax()
      } else {
        that.ruleForm.payee_list2 = []
        for (const i in that.payment_payee_list) {
          for (const j in that.ruleForm.payee_list) {
            if (that.ruleForm.payee_list[j] === that.payment_payee_list[i]['id']) {
              that.ruleForm.payee_list2.push({
                'payee_type': that.payment_payee_list[i]['id'],
                'payee_type_name': that.payment_payee_list[i]['name'],
                'payee_money': that.payment_payee_list[i]['money'] || ''
              })
            }
          }
        }
        that.dialogFormVisible = true
      }
    },
    payeeFormOk() {
      const that = this
      that.$refs['payeeForm'].validate(valid => {
        if (valid) {
          var totalMoney = 0
          const paymentMoney = Number(that.ruleForm.paymentMoney)
          for (const i in that.ruleForm.payee_list2) {
            const money = Number(that.ruleForm.payee_list2[i].payee_money)
            totalMoney += money
          }
          if (totalMoney !== paymentMoney) {
            that.$message.warning('金额总和不等于收款金额')
            return false
          } else {
            that.handleAjax()
            that.dialogFormVisible = false
          }
        }
      })
    },
    handleSave(formName) {
      if (this.radio === 1 && this.memberTypeId !== '11') {
        this.ruleForm.person_list = [
          {
            saler_id: '',
            share_ratio: ''
          }
        ]
      }
      this.submitStatus = 0
      this.submitForm(formName)
    },
    handleSubmit(formName) {
      if (this.radio === 1 && this.memberTypeId !== '11') {
        this.ruleForm.person_list = [
          {
            saler_id: '',
            share_ratio: ''
          }
        ]
      }
      this.submitStatus = 1
      this.submitForm(formName)
    },
    handleCancel() {
      this.$router.push('/smallReport/list')
    },
    // 获取小报备选择详情
    getSmallReportEdit() {
      const queryObj = {
        cooperation_type: String(this.memberTypeId),
        id: this.ruleForm.id
      }
      this.loading = true
      this.smallReportEdit(queryObj, this.setData)
    },
    setData(data) {
      const ratioList = data.back_ratio_list.map(item => {
        return {
          value: item,
          label: item
        }
      })
      const arr = [
        {
          value: '',
          label: '请选择'
        }
      ]
      this.backRatioOptions = [...arr, ...ratioList]
      this.paymentOptions = this.memberTypeId === '11' ? data.refund_type_list : data.payment_type_list
      this.refundTypeList = data.refund_type_list
      this.paymentTypeList = data.payment_type_list
      this.payment_payee_list = data.payment_payee_list
      const list = data.cooperation_type_list
      this.memberTypeOptions = list.map((it) => {
        return {
          value: it.id + '',
          label: it.name
        }
      })
      if (this.isEdit) {
        const ret = data.detail
        for (var i = 0; i < ret.auth_imgs.length; i++) {
          const obj = { url: ret.auth_imgs[i].img_full }
          this.fileList.push(obj)
          this.ruleForm.uploadedImg.push(ret.auth_imgs[i].img_src)
        }
        this.ruleForm.id = ret.id
        this.ruleForm.order_id = ret.order_id
        this.ruleForm.cooperation_type = ret.cooperation_type
        this.ruleForm.companyName = ret.company_name
        this.ruleForm.companyId = ret.company_id
        this.companyId = ret.company_id
        this.ruleForm.company_name = ret.company_name
        this.ruleForm.cooperation_type = String(ret.cooperation_type)
        // this.ruleForm.memberType = ret.cooperation_type_name
        this.ruleForm.city_id = ret.city_id
        this.ruleForm.ratio = String(ret.viptype) === '0.0' ? '' : ret.viptype
        this.ruleForm.paymentName = ret.payment_uname
        this.ruleForm.backRatio = ret.back_ratio
        this.ruleForm.orderSignMoney = ret.order_sign_money
        this.ruleForm.paymentTime = fortmatDate(ret.payment_date)
        this.ruleForm.paymentMoney = ret.payment_total_money
        this.ruleForm.round_order_money = parseInt(ret.round_order_money)
        this.ruleForm.deposit_money = parseInt(ret.deposit_money)
        this.ruleForm.depositToRoundMoney = Number(ret.deposit_to_round_money)
        this.ruleForm.paymentType = ret.payment_type
        this.ruleForm.remark = ret.remark
        this.ruleForm.otherMoney = ret.other_money
        this.ruleForm.otherMoneyRemark = ret.other_money_remark
        this.ruleForm.refundMoney = ret.refund_money // 退款金额
        this.ruleForm.depositToPlatformMoney = ret.deposit_to_platform_money // 保证金抵扣平台使用费金额
        this.ruleForm.platform_money = ret.platform_money // 平台使用费
        if (ret.platform_money_start_date && ret.platform_money_end_date) {
          this.ruleForm.platformMoneyDate = [ret.platform_money_start_date || '', ret.platform_money_end_date || ''] // 平台使用费有效期
        } else {
          this.ruleForm.platformMoneyDate = '' // 平台使用费有效期
        }
        this.ruleForm.payee_list = ret.payee_list.map(item => {
          return Number(item.payee_type)
        })
        if (ret.cooperation_type === 13) {
          this.getCompanyInfo(ret.company_id)
        }
        if (ret.person_list.length === 0) {
          this.radio = 1
          this.ruleForm.person_list = [
            {
              saler_id: '',
              share_ratio: ''
            }
          ]
        } else {
          this.ruleForm.person_list = ret.person_list
        }

        if (ret.payee_list.length > 0) {
          for (const i in this.payment_payee_list) {
            for (const j in ret.payee_list) {
              if (this.payment_payee_list[i]['id'] === ret.payee_list[j]['payee_type']) {
                this.payment_payee_list[i]['money'] = ret.payee_list[j]['payee_money']
              }
            }
          }
        }
      }
      this.loading = false
    },
    beforeAction(file) {
      const isImg = file.type === 'image/jpeg' || file.type === 'image/png'
      if (!isImg) {
        this.$message.error('上传汇款凭证只能是JPG或PNG 格式!')
        return isImg
      }
    },
    handleUploadSuccess(res, file, fileList) {
      this.ruleForm.uploadedImg.push(res.data.img_path)
    },
    handleRemove(file, fileList) {
      if (file.response) {
        this.ruleForm.uploadedImg.forEach((item, index) => {
          if (file.response.data.img_path.indexOf(item) !== -1) {
            this.ruleForm.uploadedImg.splice(index, 1)
          }
        })
      } else {
        this.ruleForm.uploadedImg.forEach((item, index) => {
          if (file.url.indexOf(item) !== -1) {
            this.ruleForm.uploadedImg.splice(index, 1)
          }
        })
      }
    },
    preview(item) {
      this.previewTemp = []
      this.previewTemp.push(item)
      this.dialogVisiblePreview = true
    },
    getCompanyInfoBtn() {
      const that = this
      const val = that.ruleForm.companyId
      const reg = /^[+]{0,1}(\d+)$/
      if (!val) {
        that.$message.warning('请输入网店代码')
      } else if (!reg.test(val)) {
        that.$message.warning('请输入正确网店代码')
      } else {
        that.getCompanyInfo(val)
      }
    },
    getCompanyInfo(val) {
      const that = this
      if (val) {
        this.companyId = this.ruleForm.companyId
        this.companyInfoloading = true
        fetchFindUser({ uid: val }).then(res => {
          this.companyInfoloading = false
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0][0]
            if (data) {
              that.ruleForm.companyName = data.jc
              that.ruleForm.city_id = data.cs
              that.ruleForm.deposit_money = Number(data.deposit_money)
              if (this.memberType === 10) {
                that.ruleForm.backRatio = '0%'
              }
            }
          } else {
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.companyInfoloading = false
        })
      }
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.small-report-add {
  .text-center {
    text-align: center;
  }
  .keftips {
    width: 600px;
    position: absolute;
    left: 30px;
    top: -6px;
  }
  .people{
    .el-form-item__content{
      margin-left: 30px;
    }
  }
  .el-input{
    width: 300px;
  }
  .userName{
    .el-input{
      width: 100%;
    }
  }
  .el-range-separator {
    padding: 0;
  }
  .exclude-time {
    position: absolute;
    width: 850px;
    left: 0px;
    top: 55px;
  }
  .month-promise {
    width: 76%;
    margin-left: 3%;
  }
  .sum-promise {
    width: 77%;
    margin-top: 20px;
    margin-left: 2%;
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
  .order-width {
    width: 120px;
  }
  .order-time-width {
    width: 150px;
  }
  .total-contract {
    margin-bottom: 20px;
  }
  .fixwidth01 {
    width: 80px;
  }
  .total-contract {
    margin-right: -315px;
    width: 1172px;
  }
  .current-contract {
    margin-right: -315px;
    width: 1172px;
  }
  .note {
    margin-top: 20px;
  }
  .ml-20 {
    margin-left: 20px;
  }
  .beizhu {
    margin-top: 15px;
  }
  .beizhu.w105 {
    width: 105%;
  }
  .upimg {
    display: inline-block;
    width: 100px;
    height: 100px;
    margin-right: 10px;
  }
  .el-date-editor.el-input.red input {
    color: red;
  }
  .el-checkbox-button.is-checked .el-checkbox-button__inner {
    border: none;
  }
  .el-checkbox-button__inner {
    border: none;
    background: #e6f1fe;
  }
  .el-checkbox-button:first-child .el-checkbox-button__inner {
    border: none;
  }
  .el-checkbox-button {
    margin-right: 20px;
    margin-bottom: 20px;
  }
}
</style>
