<template>
    <div class="home-decoration">
        <el-form :model="ruleForm" :rules="rules" v-loading="loading" ref="ruleForm" label-width="100px" class="demo-ruleForm">
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>1、公司名称：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="companyName">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.companyName" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.companyName" :maxlength="20" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>2、合作类型：</div></el-col>
                <el-col :span="18">
                    <el-input v-if="operationActionTag === 'add'" v-model="memberType" :disabled="true"></el-input>
                    <span v-if="operationActionTag === 'edit'">
                        <el-select v-if="!unCheckEditTag" v-model="memberTypeVal" placeholder="请选择">
                            <el-option
                                v-for="item in memberTypeOptions"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                        <el-input v-else v-model="memberType" :disabled="true"></el-input>
                    </span>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、城市：</div></el-col>
                <el-col :span="18">
                    <el-form-item
                        prop="citySelectStr"
                        :rules='{ required: true, message: "请选择城市" }'
                    >
                        <el-autocomplete
                            v-model="ruleForm.citySelectStr"
                            class="inline-input mt4"
                            :fetch-suggestions="queryCity"
                            placeholder="请输入"
                            @select="handleSelect"
                        />
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20" v-if='backOrRatio'>
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、返点比例：</div></el-col>
                <el-col :span="18">
                    <el-form-item
                        prop="backRatio"
                        :rules='{ required: true, message: "请选择返点比例" }'
                    >
                        <el-select v-if="unCheckEditTag" v-model="ruleForm.backRatio" placeholder="请选择" :disabled="true">
                            <el-option
                                    v-for="item in backRatioOptions"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                        <el-select v-else v-model="ruleForm.backRatio" placeholder="请选择">
                            <el-option
                                    v-for="item in backRatioOptions"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20" v-else>
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、单倍/几倍：</div></el-col>
                <el-col :span="18">
                    <el-form-item
                        prop="ratio"
                        :rules='{ required: true, message: "请选择单倍/几倍" }'
                    >
                        <el-select v-if="unCheckEditTag" v-model="ruleForm.ratio" placeholder="请选择" :disabled="true">
                            <el-option
                                    v-for="item in ratioOptions"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                        <el-select v-else v-model="ruleForm.ratio" placeholder="请选择">
                            <el-option
                                    v-for="item in ratioOptions"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">5、汇款方名称：</div></el-col>
                <el-col :span="18">
                    <el-form-item>
                        <el-input v-model="ruleForm.paymentName" :maxlength="20" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>6、汇款时间：</div></el-col>
                <el-col :span="18">
                    <div v-if="unCheckEditTag">
                        <el-form-item prop="paymentTime" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.paymentTime"
                                type="date"
                                placeholder="汇款日期"
                                :disabled="true"
                                :picker-options="pickerOptions"
                            >
                            </el-date-picker>
                        </el-form-item>
                    </div>
                    <div v-else>
                        <el-form-item prop="paymentTime" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.paymentTime"
                                type="date"
                                placeholder="汇款日期"
                                :picker-options="pickerOptions"
                            >
                            </el-date-picker>
                        </el-form-item>
                    </div>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>7、收款金额：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="paymentMoney">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.paymentMoney" maxlength="10" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.paymentMoney" type="text" maxlength="10" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>



            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red" style="width: 30px; display: inline-block"></i>轮单费：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="round_order_money">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.round_order_money" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.round_order_money" type="text" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red" style="width: 30px; display: inline-block"> </i>保证金：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="deposit_money">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.deposit_money" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.deposit_money" type="text" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>







            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>8、收款类型：</div></el-col>
                <el-col :span="18">
                    <el-form-item
                        prop="paymentType"
                        :rules='{ required: true, message: "请选择收款类型" }'
                    >
                        <el-select v-model="ruleForm.paymentType" placeholder="请选择">
                            <el-option
                                v-for="item in paymentOptions"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>9、收款方名称：</div></el-col>
            </el-row>
            <el-row clas="mb-20">
                <el-checkbox-group v-model="payee_list">
                    <el-checkbox-button v-for="item in payment_payee_list" :key="item.paixu" :label="item.id">{{item.name}}</el-checkbox-button>
                </el-checkbox-group>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">10、其他业绩人：</div></el-col>
                <el-col :span="18" class="lh-40">
                    <template>
                        <el-radio v-model="radio" label="1">无</el-radio>
                        <el-radio v-model="radio" label="2">有</el-radio>
                    </template>
                </el-col>
            </el-row>
            <el-row class="mb-20" v-if="radio == '2'">
                <el-row v-for="(item, index) in ruleForm.person_list" :key='index'>
                  <el-col :span="2"><br/></el-col>
                  <el-col :span="3"><div class="lh-40">账号名称：</div></el-col>
                  <el-col :span="5">
                      <el-form-item>
                          <el-select v-model="item.saler_id" filterable placeholder="请选择">
                              <el-option
                                  v-for="item in personList"
                                  :key="item.saler_id"
                                  :label="item.full_name"
                                  :value="item.saler_id">
                              </el-option>
                          </el-select>
                      </el-form-item>
                  </el-col>
                  <el-col :span="3"><div class="lh-40">分成比例：</div></el-col>
                  <el-col :span="4">
                      <el-form-item>
                          <el-select v-model="item.share_ratio" filterable placeholder="请选择">
                              <el-option
                                  v-for="item in radioArry"
                                  :key="item"
                                  :label="item"
                                  :value="item">
                              </el-option>
                          </el-select>
                      </el-form-item>
                  </el-col>
                  <el-col :span="6" style="margin-left: 10px;margin-top:4px;">
                      <el-button type="primary" @click="addRule">继续添加</el-button>
                      <el-button type="danger" @click="remRule(item)">删除</el-button>
                  </el-col>
                </el-row>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>11、汇款凭证：</div></el-col>
                <el-col :span="18">
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
                        list-type="picture">
                        <div class="el-upload__text"><em>点击上传</em></div>
                    </el-upload>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">12、备注：</div></el-col>
                <el-col :span="18">
                    <el-form-item>
                        <el-input type="textarea" v-model="ruleForm.remark" maxlength="255" placeholder="备注"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <!--图片预览-->
            <el-dialog
                :visible.sync="dialogVisiblePreview"
                :close-on-click-modal="false"
                width="60%"
            >
            <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"></div>
            </el-dialog>
            <el-row>
                <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
                <el-button type="success" v-if="!unCheckEditTag" @click="handleSubmit('ruleForm')">提交</el-button>
                <el-button @click="handleCancel">取消</el-button>
            </el-row>
            <el-row>
                <p class="red">提示：提交后将推送至副总裁审核</p>
            </el-row>
        </el-form>
    </div>

</template>

<script>
    import { fetchSmallReportSave, fetchPerPerformance } from '@/api/smallReport'
    import { fortmatDate } from '@/utils/index'
    import smallReportEdit from '@/mixins/memberReport'
    import { fetchCityList } from '@/api/common'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "commissionMember",
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
        mixins: [smallReportEdit],
        data() {
            const validateRatio = (rule, value, callback) => {
                if (parseFloat(value) === 0) {
                    callback(new Error('请选择单倍/几倍'))
                } else {
                    callback()
                }
            }
            const validateBackRatio = (rule, value, callback) => {
                if (parseFloat(value) === 0) {
                    callback(new Error('请选择返点比例'))
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
            const valDeCompare = (rule, value, callback) => {
              if (Number(this.ruleForm.deposit_money) > Number(this.ruleForm.paymentMoney)) {
                callback(new Error('保证金不能超出收款金额'))
              } else {
                callback()
              }
            }
            const valRoundCompare = (rule, value, callback) => {
                if (Number(this.ruleForm.round_order_money) > Number(this.ruleForm.paymentMoney)) {
                  callback(new Error('轮单费不能超出收款金额'))
                } else {
                  callback()
                }
              }
            return {
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now();
                    }
                },
                uploadApi: this.$qzconfig.base_api + '/uploads/upimg',
                citySelectStr: '',
                citySelectVal: '',
                citys: [],
                cityBlurFlag: null,
                comBlurFlag: null,
                ruleForm: {
                    id: '',
                    cooperation_type: '',
                    companyName: '',
                    citySelectVal: '',
                    citySelectStr: '',
                    ratio: '',
                    backRatio: '',
                    paymentName: '',
                    remark: '',
                    paymentTime: '',
                    paymentMoney: '',
                    round_order_money: '',
                    deposit_money: '',
                    paymentType: '',
                    payee_list: [],
                    person_list: [
                        {
                            saler_id: '',
                            share_ratio: ''
                        }
                    ]
                },
                rules: {
                    companyName: [
                        { required: true, message: '请输入公司名称', trigger: 'change' }
                    ],
                    paymentTime: [
                        { required: true, message: '请选择汇款时间', trigger: 'change' }
                    ],
                    paymentMoney: [
                        { required: true, message: '请输入收款金额', trigger: 'change' },
                        { validator: validateInt, trigger: 'change' }
                    ],
                    deposit_money: [
                        { validator: validateInt, trigger: 'change' },
                        { validator: valDeCompare, trigger: 'change' }
                    ],
                    round_order_money: [
                        { validator: validateInt, trigger: 'change' },
                        { validator: valRoundCompare, trigger: 'change' }
                    ],
                    paymentType: [
                        { required: true, message: '请选择收款类型', trigger: 'change' }
                    ],
                },
                backRatioOptions: [{
                    value: '',
                    label: '请选择'
                }, {
                    value: '5%',
                    label: '5%'
                }, {
                    value: '3%',
                    label: '3%'
                }, {
                    value: '2%',
                    label: '2%'
                }, {
                    value: '1%',
                    label: '1%'
                }],
                ratioOptions: [{
                    value: '',
                    label: '请选择'
                }, {
                    value: '0.5',
                    label: '0.5'
                }, {
                    value: '1',
                    label: '1'
                }, {
                    value: '1.5',
                    label: '1.5'
                }, {
                    value: '2',
                    label: '2'
                }, {
                    value: '2.5',
                    label: '2.5'
                }, {
                    value: '3',
                    label: '3'
                }, {
                    value: '3.5',
                    label: '3.5'
                }, {
                    value: '4',
                    label: '4'
                }, {
                    value: '4.5',
                    label: '4.5'
                }, {
                    value: '5',
                    label: '5'
                }, {
                    value: '5.5',
                    label: '5.5'
                }, {
                    value: '6',
                    label: '6'
                }, {
                    value: '6.5',
                    label: '6.5'
                }, {
                    value: '7',
                    label: '7'
                }, {
                    value: '7.5',
                    label: '7.5'
                }, {
                    value: '8',
                    label: '8'
                }, {
                    value: '8.5',
                    label: '8.5'
                }, {
                    value: '9',
                    label: '9'
                }, {
                    value: '9.5',
                    label: '9.5'
                }, {
                    value: '10',
                    label: '10'
                }],
                paymentOptions: [],
                memberTypeOptions: [{
                    value: '1',
                    label: '装修会员'
                }, {
                    value: '2',
                    label: '独家会员'
                }, {
                    value: '3',
                    label: '垄断会员'
                }, {
                    value: '6',
                    label: '签单返点会员'
                }],
                memberTypeVal: '1',
                submitStatus: 0, // 保存状态为0，提交状态为1
                unCheckEditTag: false,
                loading: false,
                uploadExtendData: {
                    prefix: 'sale_report'
                },
                uploadedImg: [],
                previewTemp: [],
                dialogVisiblePreview: false,
                uploadContentType: {
                    'token': localStorage.getItem('token')
                },
                fileList: [],
                backOrRatio: true,
                radio: '1',
                personList: [],
                radioArry: [],
                payee_list: [],
                payment_payee_list: []
            }
        },
        watch: {
            'memberTypeVal'(val) {
                if(parseInt(val) === 6){
                    this.backOrRatio = true
                }else{
                    this.backOrRatio = false
                }
            }
        },
        mounted() {
            this.loadAllCity()
        },
        created() {
            // 根据编辑还是添加标识符确定是否需要请求数据
            if(this.operationActionTag === 'edit') {
                this.id = this.$route.query.id
                if(this.$route.query && this.$route.query.status && parseInt(this.$route.query.status) === 6) {
                    this.unCheckEditTag = true
                }
                if (this.ruleForm.person_list) {
                    this.radio = '2'
                } else {
                    this.radio = '1'
                    this.ruleForm.person_list = [{
                        saler_id: '',
                        share_ratio: ''
                    }]
                }
                this.$nextTick(() => {
                    const el = document.getElementsByClassName('tags-view-item')[0]
                    el.innerHTML = el.innerHTML.replace('添加', '编辑')
                })
            } else {
                this.radio = '1'
                this.ruleForm.person_list = [{
                    saler_id: '',
                    share_ratio: ''
                }]
                this.formatTime()
            }
            for(var i = 1; i <= 100; i++) {
                this.radioArry.push(i)
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
                   this.radio = '1'
                    this.ruleForm.person_list = [{
                        saler_id: '',
                        share_ratio: ''
                    }]
                }
            },
            loadAllCity() {
                fetchCityList().then(res => {
                    if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        const data = res.data.data[0]
                        data.forEach((item, index) => {
                            this.citys.push(item)
                        })
                    } else {
                        this.citys = []
                    }
                })
            },
            handleSelect(item) {
                this.cityBlurFlag = item
                this.citySelectVal = item.cid
                this.citySelectStr = item.value
            },
            queryCity(queryString, cb) {
                const citys = this.citys
                const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
                this.cityBlurFlag = null
                cb(results)
            },
            createFilter(queryString) {
                return (city) => {
                    return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
                }
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
                const _this = this
                for(var i = 0; i < _this.ruleForm.person_list.length; i++) {
                    if (_this.ruleForm.person_list[i].saler_id !== '' && _this.ruleForm.person_list[i].share_ratio === '') {
                        _this.$message.error('请选择其他业绩人的分成比例')
                        return
                    } else if (_this.ruleForm.person_list[i].saler_id === '') {
                        _this.$message.error('请选择其他业绩人的账号名称')
                        return
                    }
                    percent += parseInt(_this.ruleForm.person_list[i].share_ratio)
                }
                if (percent > 100) {
                    _this.$message.error('业绩人的分成比例总和不得超出100%')
                    return false
                }
                _this.$refs[formName].validate((valid) => {
                    if (valid) {
                        _this.handleAjax()
                    } else {
                        return false
                    }
                })
            },
            handleAjax() {
                // queryObj有可能返回一个false
                const queryObj = this.handleArguments()
                if(!queryObj) {
                    return
                }
                this.loading = true
                fetchSmallReportSave(queryObj).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.$message({
                            type: 'success',
                            message: '保存成功'
                        })
                        setTimeout(() => {
                            this.$router.push('/smallReport/list')
                        }, 1000)
                    }else{
                        this.$message.error(res.data.error_msg)
                    }
                    this.loading = false
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                    this.loading = false
                })
            },
            handleArguments() {
                if(this.uploadedImg && this.uploadedImg.length >= 0) {
                    if(Array.isArray(this.uploadedImg)) {
                        this.uploadedImg = this.uploadedImg.join(',')
                    }
                }else{
                    this.uploadedImg = []
                }
                const queryObj = {
                    id: this.id,
                    company_name: this.ruleForm.companyName,
                    cooperation_type: this.memberTypeId,
                    city_id: this.citySelectVal,
                    back_ratio: this.ruleForm.backRatio,
                    viptype: this.ruleForm.ratio,
                    payment_uname: this.ruleForm.paymentName,
                    remark: this.ruleForm.remark,
                    payment_date: this.ruleForm.paymentTime,
                    payment_total_money: this.ruleForm.paymentMoney,
                    deposit_money: this.ruleForm.deposit_money,
                    round_order_money: this.ruleForm.round_order_money,
                    payment_type: this.ruleForm.paymentType,
                    // payment_account_type: this.ruleForm.paymentAccountType,
                    payee_list: this.payee_list.toString(),
                    auth_imgs: this.uploadedImg,
                    is_submit: this.submitStatus,
                    person_list: this.ruleForm.person_list
                }
                if(this.operationActionTag === 'edit') {
                    queryObj.cooperation_type = this.memberTypeVal
                }
                if(parseInt(this.memberTypeVal) === 6){
                    queryObj.viptype = ''
                }else{
                    queryObj.backOrRatio = ''
                }
                return queryObj
            },
            handleSave(formName) {
                if (this.radio === '1') {
                    this.ruleForm.person_list = []
                }
                this.submitStatus = 0
                this.submitForm(formName)
            },
            handleSubmit(formName) {
                if (this.radio === '1') {
                    this.ruleForm.person_list = []
                }
                this.submitStatus = 1
                this.submitForm(formName)
            },
            handleCancel() {
                this.$router.push('/smallReport/list')
            },
            getSmallReportEdit() {
                const queryObj = {
                    cooperation_type: String(this.memberTypeId),
                    id: this.id
                }
                this.loading = true
                this.smallReportEdit(queryObj, this.setData)
            },
            setData(data) {
              const ratioList = data.back_ratio_list.map((item) => {
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
                this.paymentOptions = data.payment_type_list
                this.payment_payee_list = data.payment_payee_list
                const list = data.cooperation_type_list
                this.memberTypeOptions = list.map((it) => {
                  return {
                    value: it.id + '',
                    label: it.name
                    }
                 })
                if (this.operationActionTag == 'edit') {
                    const ret = data.detail
                    for(var i = 0;i < ret.auth_imgs.length;i++){
                        let obj = {url:ret.auth_imgs[i].img_full}
                        this.fileList.push(obj)
                        this.uploadedImg.push(ret.auth_imgs[i].img_src)
                    }
                    this.ruleForm.id = ret.id
                    this.ruleForm.cooperation_type = ret.cooperation_type
                    this.ruleForm.companyName = ret.company_name
                    this.memberTypeVal = String(ret.cooperation_type)
                    this.ruleForm.memberType = ret.cooperation_type_name
                    this.citySelectVal = ret.city_id
                    this.ruleForm.citySelectStr = ret.city_name
                    this.ruleForm.ratio = String(ret.viptype) === '0.0' ? '' : ret.viptype
                    this.ruleForm.backRatio = ret.back_ratio
                    this.ruleForm.paymentName = ret.payment_uname
                    this.ruleForm.remark = ret.remark
                    this.ruleForm.paymentTime = ret.payment_date
                    this.ruleForm.paymentMoney = ret.payment_total_money
                    this.ruleForm.round_order_money = parseInt(ret.round_order_money)
                    this.ruleForm.deposit_money = parseInt(ret.deposit_money)
                    this.ruleForm.paymentType = ret.payment_type
                    // this.ruleForm.paymentAccountType = ret.payment_account_type
                    this.payee_list = ret.payee_list.map((item) => {
                      return Number(item.payee_type)
                    })
                    if (ret.person_list.length === 0) {
                        this.radio = '1'
                        this.ruleForm.person_list = [{
                            saler_id: '',
                            share_ratio: ''
                        }]
                    } else {
                        this.ruleForm.person_list = ret.person_list
                    }
                }
                this.loading = false
            },
            beforeAction(file) {
                const isImg = (file.type === 'image/jpeg' || file.type === 'image/png')
                if (!isImg) {
                    this.$message.error('上传汇款凭证只能是JPG或PNG 格式!');
                    return isImg
                }
            },
            handleUploadSuccess(res, file,fileList) {
                if(this.uploadedImg == ''){
                    this.uploadedImg = []
                }
                this.uploadedImg.push(res.data.img_path)
            },
            handleRemove(res,file, fileList) {
                if (res.response) {
                    this.uploadedImg.forEach((item, index) => {
                        if(res.response.data.img_path.indexOf(item)!= -1){
                            this.uploadedImg.splice(index,1)
                        }
                    })
                } else {
                    this.uploadedImg.forEach((item, index) => {
                        if(res.url.indexOf(item)!= -1){
                            this.uploadedImg.splice(index,1)
                        }
                    })
                }
            },
            preview(item) {
                this.previewTemp = []
                this.previewTemp.push(item)
                this.dialogVisiblePreview = true
            },
            add0(m) { return m < 10 ? '0' + m : m },
            formatTime() {
                var time = new Date()
                var y = time.getFullYear()
                var m = time.getMonth() + 1
                var d = time.getDate()
                this.ruleForm.paymentTime = y + '-' + this.add0(m) + '-' + this.add0(d)
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .home-decoration {
        .text-center{
            text-align: center;
        }
        .keftips{
            width: 600px;
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
            top: 55px;
        }
        .el-form-item{
            margin-bottom: 0 !important;
        }
        .el-form-item__content{
            margin-left: 0 !important;
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
        .inline-block{
            display: inline-block;
        }
        .order-width{
            width: 120px;
        }
        .order-time-width{
            width: 150px;
        }
        .total-contract{
            margin-bottom: 20px;
        }
        .fixwidth01{
            width: 80px;
        }
        .total-contract{
            margin-right: -315px;
            width: 1172px;
        }
        .current-contract{
            margin-right: -315px;
            width: 1172px;
        }
        .note{
            margin-top: 20px;
        }
        .ml-20{
            margin-left: 20px;
        }
        .beizhu{
            margin-top: 15px;
        }
        .beizhu.w105{
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
        .el-checkbox-button.is-checked .el-checkbox-button__inner{
            border: none;
        }
        .el-checkbox-button__inner{
            border: none;
            background: #e6f1fe;
        }
        .el-checkbox-button:first-child .el-checkbox-button__inner{
            border: none;
        }
        .el-checkbox-button{
            margin-right: 20px;
            margin-bottom:20px;
        }
        .el-checkbox-group{
            padding-left: 30px;
        }

    }

</style>
