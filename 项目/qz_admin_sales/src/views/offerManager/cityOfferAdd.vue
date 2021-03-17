<template>
  <div class="city-offer-add">
    <div class="qian-main">
      <el-form ref="ruleForm" v-loading="loading" :model="ruleForm" :rules="rules" label-width="100px" class="demo-ruleForm">
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">城市：</div></el-col>
          <el-col :span="18">
            <el-form-item v-if="!id" prop="city">
              <el-input v-model="ruleForm.city" :maxlength="11" placeholder="请输入" />
            </el-form-item>
            <span v-else>{{ ruleForm.city }}</span>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">所属地级市：</div></el-col>
          <el-col :span="18">
            <el-form-item v-if="!id" prop="prefectureLevelCities">
              <el-input v-model="ruleForm.prefectureLevelCities" :maxlength="11" placeholder="请输入" />
            </el-form-item>
            <span v-else>{{ ruleForm.prefectureLevelCities }}</span>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">主做模式：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="city_mode">
              <el-select v-model="ruleForm.city_mode" placeholder="请选择">
                <el-option
                  v-for="item of city_modeOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">是否标杆：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="is_standard">
              <el-select v-model="ruleForm.is_standard" placeholder="请选择">
                <el-option
                  v-for="item of is_standardOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">报价对应城市等级：</div></el-col>
          <el-col :span="18">
            <el-form-item v-if="!id" prop="levelVal">
              <el-select v-model="ruleForm.levelVal" placeholder="请选择">
                <el-option
                  v-for="item in levelOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
            <span v-else>{{ levelOptions[parseInt(ruleForm.levelVal)] ? levelOptions[parseInt(ruleForm.levelVal) + 1].label : '' }}</span>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">轮单费：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="roundOrderMoney">
              <el-input v-model="ruleForm.roundOrderMoney" :maxlength="9" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">局改价格：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="partReformPrice">
              <el-input v-model="ruleForm.partReformPrice" :maxlength="9" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">返点比例：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="rebates_ratio">
              <el-input v-model="ruleForm.rebates_ratio" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">季度报价：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="quarterQuote">
              <el-input v-model="ruleForm.quarterQuote" :maxlength="9" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">半年报价：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="halfYearQuote">
              <el-input v-model="ruleForm.halfYearQuote" :maxlength="9" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">年度报价：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="yearQuote">
              <el-input v-model="ruleForm.yearQuote" :maxlength="9" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">月承诺订单：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="monthPromiseOrder">
              <el-input v-model="ruleForm.monthPromiseOrder" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">1.0订单上限：</div></el-col>
          <el-col :span="18">
            <el-form-item>
              <el-input v-model="ruleForm.userOrderLimit" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">年度会员时间：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="yearMemberTime">
              <el-input v-model="ruleForm.yearMemberTime" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">A档报价：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="aPrice">
              <el-input v-model="ruleForm.aPrice" type="text" placeholder="请输入" />
            </el-form-item>
          </el-col>
          <el-col :span="6"><div style="line-height:36px;text-align:right;">月承诺单量：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="aOrder">
              <el-input v-model="ruleForm.aOrder" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">B档报价：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="bPrice">
              <el-input v-model="ruleForm.bPrice" type="text" placeholder="请输入" />
            </el-form-item>
          </el-col>
          <el-col :span="6"><div style="line-height:36px;text-align:right;">月承诺单量：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="bOrder">
              <el-input v-model="ruleForm.bOrder" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">C档报价：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="cPrice">
              <el-input v-model="ruleForm.cPrice" type="text" placeholder="请输入" />
            </el-form-item>
          </el-col>
          <el-col :span="6"><div style="line-height:36px;text-align:right;">月承诺单量：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="cOrder">
              <el-input v-model="ruleForm.cOrder" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">D档报价：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="dPrice">
              <el-input v-model="ruleForm.dPrice" type="text" placeholder="请输入" />
            </el-form-item>
          </el-col>
          <el-col :span="6"><div style="line-height:36px;text-align:right;">月承诺单量：</div></el-col>
          <el-col :span="6">
            <el-form-item prop="dOrder">
              <el-input v-model="ruleForm.dOrder" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">是否包干：</div></el-col>
          <el-col :span="10">
            <el-form-item prop="isConsistVal">
              <el-select v-model="ruleForm.isConsistVal" placeholder="请选择">
                <el-option
                  v-for="item in isConsistOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isConsistVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">包干报价：</div></el-col>
          <el-col :span="18">
            <el-form-item
              prop="consistPrice"
            >
              <el-input v-model="ruleForm.consistPrice" placeholder="请输入" />

            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isConsistVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">包干最低承诺分单数：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="consistFen">
              <el-input v-model="ruleForm.consistFen" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isConsistVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">包干赠单承诺：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="consistZeng">
              <el-input v-model="ruleForm.consistZeng" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb-20">
          <el-col :span="6"><div class="lh-40">是否独家：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="isExclusiveVal">
              <el-select v-model="ruleForm.isExclusiveVal" placeholder="请选择">
                <el-option
                  v-for="item in isExclusiveOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isExclusiveVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">独家报价：</div></el-col>
          <el-col :span="18">
            <el-form-item
              prop="exclusivePrice"
            >
              <el-input v-model="ruleForm.exclusivePrice" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isExclusiveVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">独家最低分单数：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="exclusiveFenMin">
              <el-input v-model="ruleForm.exclusiveFenMin" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isExclusiveVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">独家最高分单数：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="exclusiveFenMax">
              <el-input v-model="ruleForm.exclusiveFenMax" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="parseInt(ruleForm.isExclusiveVal) !== 2" class="mb-20">
          <el-col :span="6"><div class="lh-40">独家赠单承诺：</div></el-col>
          <el-col :span="18">
            <el-form-item prop="exclusiveZeng">
              <el-input v-model="ruleForm.exclusiveZeng" placeholder="请输入" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
          <el-button @click="handleCancel">取消</el-button>
        </el-row>
      </el-form>
    </div>
  </div>
</template>

<script>
import { fetchCityOfferSave, fetchCityOfferDetail } from '@/api/memberReport'
import inputFilter from '@/directive/input-filter/index.js'
export default {
  name: 'CityOfferAdd',
  directives: {
    inputFilter
  },
  data() {
    const validateCityLevel = (rule, value, callback) => {
      if (parseInt(value) === 99999999) {
        callback(new Error('请选择报价对应城市等级'))
      } else {
        callback()
      }
    }
    const validateConsist = (rule, value, callback) => {
      if (parseInt(value) === 0) {
        callback(new Error('请选择是否包干'))
      } else {
        callback()
      }
    }
    const validateExclusiveVal = (rule, value, callback) => {
      if (parseInt(value) === 0) {
        callback(new Error('请选择是否独家'))
      } else {
        callback()
      }
    }
    const validateInt = (rule, value, callback) => {
      const reg = /^[+]{0,1}(\d+)$/
      if (value && !reg.test(value)) {
        callback(new Error('请输入正整数'))
      } else {
        callback()
      }
    }
    return {
      id: '',
      ruleForm: {
        city: '',
        prefectureLevelCities: '',
        levelVal: '99999999',
        roundOrderMoney: '',
        partReformPrice: '',
        rebates_ratio: '',
        quarterQuote: '',
        halfYearQuote: '',
        yearQuote: '',
        monthPromiseOrder: '',
        yearMemberTime: '',
        userOrderLimit: '',
        aPrice: '',
        aOrder: '',
        bPrice: '',
        bOrder: '',
        cPrice: '',
        cOrder: '',
        dPrice: '',
        dOrder: '',
        isConsistVal: '0',
        consistPrice: '',
        consistFen: '',
        consistZeng: '',
        isExclusiveVal: '0',
        exclusivePrice: '',
        exclusiveFenMin: '',
        exclusiveFenMax: '',
        exclusiveZeng: '',
        city_mode: 0,
        is_standard: 0
      },
      rules: {
        city: [
          { required: true, message: '请输入城市', trigger: 'change' }
        ],
        prefectureLevelCities: [
          { required: true, message: '请输入所属地级市', trigger: 'change' }
        ],
        levelVal: [
          { required: true, validator: validateCityLevel, trigger: 'change' }
        ],
        roundOrderMoney: [
          { required: true, message: '请输入轮单费', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        partReformPrice: [
          { validator: validateInt, trigger: 'change' }
        ],
        rebates_ratio: [
          { required: true, message: '请输入返点比例', trigger: 'change' }
        ],
        quarterQuote: [
          { required: true, message: '请输入季度报价', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        halfYearQuote: [
          { required: true, message: '请输入半年报价', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        yearQuote: [
          { required: true, message: '请输入年度报价', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        monthPromiseOrder: [
          { required: true, message: '请输入月承诺订单', trigger: 'change' }
        ],
        yearMemberTime: [
          { required: true, message: '请输入年度会员时间', trigger: 'change' }
        ],
        isConsistVal: [
          { required: true, validator: validateConsist, trigger: 'change' }
        ],
        aPrice: [
          { required: false, trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        bPrice: [
          { required: false, trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        cPrice: [
          { required: false, trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        dPrice: [
          { required: false, trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        consistPrice: [
          { required: true, message: '请输入包干报价', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        consistFen: [
          { required: true, message: '请输入包干最低承诺分单数', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        consistZeng: [
          { required: true, message: '请输入包干赠单承诺', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        isExclusiveVal: [
          { required: true, validator: validateExclusiveVal, trigger: 'change' }
        ],
        exclusivePrice: [
          { required: true, message: '请输入独家报价', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        exclusiveFenMin: [
          { required: true, message: '请输入独家最低分单数', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        exclusiveFenMax: [
          { required: true, message: '请输入独家最高分单数', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ],
        exclusiveZeng: [
          { required: true, message: '请输入独家赠单承诺', trigger: 'change' },
          { validator: validateInt, trigger: 'change' }
        ]
      },
      levelOptions: [{
        value: '99999999',
        label: '请选择'
      }, {
        value: '0',
        label: 'A类'
      }, {
        value: '1',
        label: 'B类'
      }, {
        value: '2',
        label: 'C类'
      }, {
        value: '3',
        label: 'D类'
      }, {
        value: '4',
        label: 'S1类'
      }, {
        value: '5',
        label: 'S2类'
      }],
      isConsistOptions: [{
        value: '0',
        label: '请选择'
      }, {
        value: '1',
        label: '是'
      }, {
        value: '2',
        label: '否'
      }],
      isExclusiveOptions: [{
        value: '0',
        label: '请选择'
      }, {
        value: '1',
        label: '是'
      }, {
        value: '2',
        label: '否'
      }],
      city_modeOptions: [{
        value: 0,
        label: '请选择'
      }, {
        value: 1,
        label: '1.0模式'
      }, {
        value: 2,
        label: '2.0模式'
      }],
      is_standardOptions: [{
        value: 0,
        label: '请选择'
      }, {
        value: 1,
        label: '是'
      }, {
        value: 2,
        label: '否'
      }],
      loading: false
    }
  },
  watch: {
    'ruleForm.isConsistVal'(val) {
      if (parseInt(val) === 2) {
        this.ruleForm.consistPrice = ''
        this.ruleForm.consistFen = ''
        this.ruleForm.consistZeng = ''
      }
    },
    'ruleForm.isExclusiveVal'(val) {
      if (parseInt(val) === 2) {
        this.ruleForm.exclusivePrice = ''
        this.ruleForm.exclusiveFenMin = ''
        this.ruleForm.exclusiveFenMax = ''
        this.ruleForm.exclusiveZeng = ''
      }
    }
  },
  created() {
    if (this.$route.query && this.$route.query.id) {
      this.id = this.$route.query.id
      this.getCityOfferDetail()
      this.$nextTick(() => {
        const el = document.getElementsByClassName('tags-view-item')[0]
        el.innerHTML = el.innerHTML.replace('添加', '编辑')
      })
    }
  },
  methods: {
    submitForm(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.handleSaveAjax()
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    handleSave(formName) {
      this.submitForm(formName)
    },
    handleSaveAjax() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchCityOfferSave(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '保存成功'
          })
          setTimeout(() => {
            window.close()
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
    handleArguments() {
      const queryObj = {
        id: this.id,
        city_name: this.ruleForm.city,
        parent_city_name: this.ruleForm.prefectureLevelCities,
        little: this.ruleForm.levelVal,
        round_order_money: this.ruleForm.roundOrderMoney,
        part_reform_price: this.ruleForm.partReformPrice,
        rebates_ratio: this.ruleForm.rebates_ratio,
        quarter_quote: this.ruleForm.quarterQuote,
        half_year_quote: this.ruleForm.halfYearQuote,
        year_quote: this.ruleForm.yearQuote,
        month_promise_order: this.ruleForm.monthPromiseOrder,
        user_order_limit: this.ruleForm.userOrderLimit,
        year_member_time: this.ruleForm.yearMemberTime,
        is_consist: this.ruleForm.isConsistVal,
        consist_price: this.ruleForm.consistPrice,
        consist_fen: this.ruleForm.consistFen,
        consist_zeng: this.ruleForm.consistZeng,
        is_exclusive: this.ruleForm.isExclusiveVal,
        exclusive_price: this.ruleForm.exclusivePrice,
        exclusive_fen_max: this.ruleForm.exclusiveFenMax,
        exclusive_fen_min: this.ruleForm.exclusiveFenMin,
        exclusive_zeng: this.ruleForm.exclusiveZeng,
        grade_a_price: this.ruleForm.aPrice,
        grade_a_order: this.ruleForm.aOrder,
        grade_b_price: this.ruleForm.bPrice,
        grade_b_order: this.ruleForm.bOrder,
        grade_c_price: this.ruleForm.cPrice,
        grade_c_order: this.ruleForm.cOrder,
        grade_d_price: this.ruleForm.dPrice,
        grade_d_order: this.ruleForm.dOrder,
        city_mode: this.ruleForm.city_mode,
        is_standard: this.ruleForm.is_standard

      }
      return queryObj
    },
    getCityOfferDetail() {
      this.loading = true
      fetchCityOfferDetail({
        id: this.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.detail) {
            const detail = res.data.data.detail
            this.ruleForm.city = detail.city_name
            this.ruleForm.prefectureLevelCities = detail.parent_city_name
            this.ruleForm.levelVal = String(detail.little)
            this.ruleForm.roundOrderMoney = detail.round_order_money
            this.ruleForm.partReformPrice = detail.part_reform_price
            this.ruleForm.rebates_ratio = detail.rebates_ratio
            this.ruleForm.quarterQuote = detail.quarter_quote
            this.ruleForm.halfYearQuote = detail.half_year_quote
            this.ruleForm.yearQuote = detail.year_quote
            this.ruleForm.monthPromiseOrder = detail.month_promise_order
            this.ruleForm.userOrderLimit = detail.user_order_limit
            this.ruleForm.yearMemberTime = detail.year_member_time
            this.ruleForm.isConsistVal = String(detail.is_consist)
            this.ruleForm.consistPrice = detail.consist_price
            this.ruleForm.consistFen = detail.consist_fen
            this.ruleForm.consistZeng = detail.consist_zeng
            this.ruleForm.isExclusiveVal = String(detail.is_exclusive)
            this.ruleForm.exclusivePrice = detail.exclusive_price
            this.ruleForm.exclusiveFenMax = detail.exclusive_fen_max
            this.ruleForm.exclusiveFenMin = detail.exclusive_fen_min
            this.ruleForm.exclusiveZeng = detail.exclusive_zeng
            this.ruleForm.aPrice = detail.grade_a_price
            this.ruleForm.aOrder = detail.grade_a_order
            this.ruleForm.bPrice = detail.grade_b_price
            this.ruleForm.bOrder = detail.grade_b_order
            this.ruleForm.cPrice = detail.grade_c_price
            this.ruleForm.cOrder = detail.grade_c_order
            this.ruleForm.dPrice = detail.grade_d_price
            this.ruleForm.dOrder = detail.grade_d_order
            this.ruleForm.city_mode = detail.city_mode
            this.ruleForm.is_standard = detail.is_standard
          }
        } else {
          this.$message.error(res.data.error_msg)
        }
        this.loading = false
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
        this.loading = false
      })
    },
    handleCancel() {
      window.close()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
    .city-offer-add {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .text-center{
            text-align: center;
        }
        .keftips{
            width: 500px;
            position: absolute;
            left: 30px;
            top: -6px;
        }
        .el-range-separator{
            padding: 0;
        }
        .exclude-time{
            position: absolute;
            width: 850px;
            left: 0px;
            top: 0px;
        }
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
        .demo-ruleForm{
            width: 800px;
        }
        .mb-20{
            margin-bottom: 20px;
        }
        .el-form-item{
            margin-bottom: 0 !important;
        }
        .el-form-item__content{
            margin-left: 0 !important;
        }
    }

</style>
