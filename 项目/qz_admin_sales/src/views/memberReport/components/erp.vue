<template>
  <div class="erp-member-order">
    <el-form :model="ruleForm" :rules="rules" v-loading="loading" ref="ruleForm" label-width="100px" class="demo-ruleForm">
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>1、公司名称：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="companyName">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.companyName" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.companyName" :maxlength="30" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>2、合作类型：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="memberType" :disabled="true"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>3、城市：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="citySelectStr" :rules='{ required: true, message: "请选择城市"}'>
            <el-input v-if="unCheckEditTag" v-model="ruleForm.citySelectStr" :disabled="true" placeholder="请输入"></el-input>
            <el-autocomplete
              v-else
              v-model="ruleForm.citySelectStr"
              class="inline-input mt4"
              :fetch-suggestions="queryCity"
              placeholder="请输入"
              @select="handleSelect"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>4、新/老会员：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="isNewMember">
            <el-select v-if="unCheckEditTag" v-model="ruleForm.isNewMember" placeholder="请选择" :disabled="true" @change="isNewMemberChange">
              <el-option
                v-for="item in isNewMemberOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <el-select v-else v-model="ruleForm.isNewMember" placeholder="请选择" @change="isNewMemberChange">
              <el-option
                v-for="item in isNewMemberOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>5、联系人：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="contact">
            <el-input v-model="ruleForm.contact" :maxlength="6" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">6、职务：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.duties" :maxlength="10" placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>7、电话：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="phone">
            <el-input v-model="ruleForm.phone" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">8、微信号：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.wechat" :maxlength="20" placeholder="请输入"></el-input>
        </el-col>
      </el-row>

      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>9、合作模式：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="cooperationMode">
            <el-select v-if="unCheckEditTag" v-model="ruleForm.cooperationMode" placeholder="请选择" :disabled="true">
              <el-option
                v-for="item in cooperaMode"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <el-select v-else v-model="ruleForm.cooperationMode" placeholder="请选择">
              <el-option
                v-for="item in cooperaMode"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>

      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>10、合同时间：</div>
        </el-col>
        <el-col :span="17">
          <div v-if="unCheckEditTag">
            <el-form-item prop="contractTimeBegin" class="inline-block">
              <el-date-picker
                v-model="ruleForm.contractTimeBegin"
                type="date"
                placeholder="开始日期"
                :disabled="true"
              >
              </el-date-picker>
            </el-form-item>
            <el-form-item prop="contractTimeEnd" class="inline-block">
              <el-date-picker
                v-model="ruleForm.contractTimeEnd"
                type="date"
                placeholder="结束日期"
                :disabled="true"
              >
              </el-date-picker>
            </el-form-item>
            <span class="ml-20" v-if="contranDay > 0">{{tranAB}}</span>
          </div>
          <div v-else>
            <el-form-item prop="contractTimeBegin" class="inline-block">
              <el-date-picker
                v-model="ruleForm.contractTimeBegin"
                type="date"
                placeholder="开始日期"
              >
              </el-date-picker>
            </el-form-item>
            <el-form-item prop="contractTimeEnd" class="inline-block">
              <el-date-picker
                v-model="ruleForm.contractTimeEnd"
                type="date"
                placeholder="结束日期"
              >
              </el-date-picker>
            </el-form-item>
            <span class="ml-20" v-if="contranDay > 0">{{tranAB}}</span>
          </div>
        </el-col>
        <el-col :span="1" style="position:relative;"><p class="red keftips">客服注意，请记得按这个上传，销售勿删</p></el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>11、本次款项到账时间：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="moneyInTime">
            <el-date-picker
              v-if="unCheckEditTag"
              v-model="ruleForm.moneyInTime"
              type="date"
              placeholder="请选择"
              :disabled="true"
            >
            </el-date-picker>
            <el-date-picker
              v-else
              v-model="ruleForm.moneyInTime"
              type="date"
              placeholder="请选择">
            </el-date-picker>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">12、下次付款时间：</div>
        </el-col>
        <el-col :span="18">
          <el-date-picker
            v-if="unCheckEditTag"
            v-model="ruleForm.nextpayTime"
            type="date"
            placeholder="请选择"
            :disabled="true"
          >
          </el-date-picker>
          <el-date-picker
            v-else
            v-model="ruleForm.nextpayTime"
            type="date"
            placeholder="请选择">
          </el-date-picker>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>13、总合同金额：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="totalMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.totalMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.totalMoney" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>14、本次到账金额：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="getMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.getMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.getMoney" :maxlength="9" placeholder="请输入"  @blur="()=>this.$refs['ruleForm'].validateField('totalMoney')"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">15、余款多少：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="lastMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.lastMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.lastMoney" :maxlength="9" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>16、网店代码：</div>
        </el-col>
        <el-col :span="8">
          <el-form-item prop="onlineShopCode">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.onlineShopCode" placeholder="请输入"
                      :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.onlineShopCode" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="3" style="text-align: center;">
          <el-button @click="searchCompanyByIdOrName">验证</el-button>
        </el-col>
        <el-col :span="7" style="position: relative;"><p class="red keftips">{{ shopName }}</p></el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>17、账户登录名称：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="account">
            <el-input v-model="ruleForm.account" :minlength="6" :maxlength="18" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">18、备注：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.note" type="textarea" :rows="5" :maxlength="500" placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">19、是否需要客服传凭：</div>
        </el-col>
        <el-col :span="18">
          <el-select v-model="ruleForm.kfVoucher" placeholder="请选择">
            <el-option
              v-for="item in kfVoucherOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">20、上传图片：</div>
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
            list-type="picture">
            <div class="el-upload__text"><em>点击上传</em></div>
          </el-upload>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">21、客服上传图片：</div>
        </el-col>
        <el-col :span="18">
          <el-col :span="5" v-for='item in kfuploadImg' :key="item">
            <img :src="item" alt="" class="upimg" @click="preview(item)">
          </el-col>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>22、开站讨论组备注：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="openCityChatRemark">
            <el-input v-model="ruleForm.openCityChatRemark" type="textarea" :rows="5" :maxlength="500" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <!--图片预览-->
      <el-dialog
        :visible.sync="dialogVisiblePreview"
        :close-on-click-modal="false"
        width="60%"
      >
        <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt=""
                                                                      style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;">
        </div>
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
  import {fetchMemberReportAdd, fetchFindUser, fetchMemberReportSup} from '@/api/memberReport'
  import {fetchCityList} from '@/api/common'
  import {checkPhoneNum} from '@/utils/index'
  import {fortmatDate, compareTime} from '@/utils/index'
  import memberReportDetail from '@/mixins/memberReport'
  import QZ_CONFIG from '@/utils/config'

  export default {
    name: 'erp',
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
    mixins: [memberReportDetail],
    data() {
      const validateRatio = (rule, value, callback) => {
        if (parseFloat(value) === 0) {
          callback(new Error('请选择单倍/几倍'))
        } else {
          callback()
        }
      }
      const validateisNewMember = (rule, value, callback) => {
        if (parseInt(value) === 0) {
          callback(new Error('请选择新/老会员'))
        } else {
          callback()
        }
      }
      const validatePhone = (rule, value, callback) => {
        if (!value) {
          callback(new Error('请输入电话'))
        } else if (!checkPhoneNum(value)) {
          callback(new Error('请输入正确的手机号'))
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
      const validateSize = (rule, value, callback) => {
        if (value.length < 6) {
          callback(new Error('账号最少为6位'))
        } else {
          callback()
        }
      }
      const validateisMode = (rule, value, callback) => {
        if (parseInt(value) === 0) {
          callback(new Error('请选择合作模式'))
        } else {
          callback()
        }
      }
      const validateisDate = (rule, value, callback) => {
        if (parseInt(this.ruleForm.cooperationMode) === 2) {
          callback()
        }
        if (!value) {
          callback(new Error('请选择合同开始时间'))
        } else {
          callback()
        }
      }
      const validateisDateEnd = (rule, value, callback) => {
        if (parseInt(this.ruleForm.cooperationMode) === 2) {
          callback()
        }
        if (!value) {
          callback(new Error('请选择合同结束时间'))
        } else {
          callback()
        }
      }
      const validateisCompare = (rule, value, callback) => {
        if (parseInt(this.ruleForm.getMoney) > parseInt(this.ruleForm.totalMoney) && parseInt(this.ruleForm.getMoney) && parseInt(this.ruleForm.totalMoney)) {
          callback(new Error('本次到账金额不得大于总合同金额'))
        } else {
          callback()
        }
      }
      const validateNumCompare = (rule, value, callback) => {
        if (parseInt(this.ruleForm.getMoney)) {
          this.$refs['ruleForm'].validateField('getMoney')
          callback()
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
          citySelectStr: '',
          ratio: '0',
          isNewMember: '0',
          contact: '',
          duties: '',
          phone: '',
          wechat: '',
          contractTime: '', // 总合同时间数组
          contractTimeBegin: '',
          contractTimeEnd: '',
          memberTime: '', // 本次会员时间
          cooperationMode: '0',
          moneyInTime: '',
          nextpayTime: '',
          totalMoney: '',
          getMoney: '',
          lastMoney: '',
          onlineShopCode: '',
          account: '',
          note: '',
          kfVoucher: '0',
          openCityChatRemark: ''
        },
        rules: {
          companyName: [
            { required: true, message: '请输入公司名称', trigger: 'change' }
          ],
          citySelectStr: [
            { required: true, message: '请输入城市', trigger: 'change' }
          ],
          isNewMember: [
            { validator: validateisNewMember, trigger: 'change' }
          ],
          contact: [
            { required: true, message: '请输入联系人', trigger: 'change' }
          ],
          phone: [
            { validator: validatePhone, trigger: 'change' }
          ],
          contractTimeBegin: [
            { validator: validateisDate, trigger: 'change' }
          ],
          contractTimeEnd: [
            { validator: validateisDateEnd, trigger: 'change' }
          ],
          cooperationMode: [
            { validator: validateisMode, trigger: 'change' }
          ],
          moneyInTime: [
            { required: true, message: '请选择本次款项到账时间', trigger: 'change' }
          ],
          totalMoney: [
            { required: true, message: '请输入总合同金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateNumCompare, trigger: ['blur', 'change'] }
          ],
          getMoney: [
            { required: true, message: '请输入本次到账金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateisCompare, trigger: ['blur', 'change'] }
          ],
          lastMoney: [
            { validator: validateInt, trigger: 'change' }
          ],
          account: [
            { required: true, message: '请输入账户登录名称', trigger: 'change' },
            { validator: validateSize, trigger: 'change' }
          ],
          onlineShopCode: [
            { required: true, message: '请输入网店代码', trigger: 'change' }
          ],
          openCityChatRemark: [
            { required: true, message: '请输入开站讨论组备注', trigger: 'blur' }
          ]
        },
        ratioOptions: [{
          value: '0',
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
        isNewMemberOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '新会员'
        }, {
          value: '2',
          label: '老会员'
        }],
        cooperaMode: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '一年'
        }, {
          value: '2',
          label: '终生'
        }],
        kfVoucherOptions: [{
          value: '0',
          label: '否'
        }, {
          value: '1',
          label: '是'
        }],
        loading: false,
        shopName: '',
        submitStatus: 1,
        unCheckEditTag: false, // 无需审核编辑标识符
        uploadExtendData: {
          prefix: 'sale_report'
        },
        uploadedImg: [],
        kfuploadImg: [],
        previewTemp: [],
        dialogVisiblePreview: false,
        uploadContentType: {
          'token': localStorage.getItem('token')
        },
        fileList: [],
        contranDay: '',
        tranAB: '',
        memberDay: '',
        memberAB: '',
        cs: '',
        citySelectStr: '',
        citySelectVal: '',
        citys: [],
        cityBlurFlag: null,
        upload_img_url: this.$qzconfig.base_api + '/uploads/upimg'
      }
    },
    watch: {
      'ruleForm.onlineShopCode'(value) {
        if (!value) {
          this.shopName = ''
        }
      },
      'ruleForm.contractTimeBegin'(val) {
        let begin = new Date(this.ruleForm.contractTimeBegin).getTime()
        let end = new Date(this.ruleForm.contractTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let contranDay = (end - begin) / 86400000
          let tranA = Math.floor(contranDay / 30)
          let tranB = contranDay % 30 ? contranDay % 30 + '天' : ''
          let tranAB = tranA ? tranA + '月' + tranB : tranB
          this.contranDay = contranDay > -1 ? contranDay : ''
          this.tranAB = tranAB
        }
        if (begin == 0) {
          this.contranDay = ''
        }
        if (this.ruleForm.contractTimeEnd) {
          if (!compareTime(val, this.ruleForm.contractTimeEnd)) {
            this.$message.error('开始时间不能大于结束时间')
            this.ruleForm.contractTimeBegin = ''
          }
        }
      },
      'ruleForm.contractTimeEnd'(val) {
        let begin = new Date(this.ruleForm.contractTimeBegin).getTime()
        let end = new Date(this.ruleForm.contractTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let contranDay = (end - begin) / 86400000
          let tranA = Math.floor(contranDay / 30)
          let tranB = contranDay % 30 ? contranDay % 30 + '天' : ''
          let tranAB = tranA ? tranA + '月' + tranB : tranB
          this.tranAB = tranAB
          this.contranDay = contranDay > -1 ? contranDay : ''
        }
        if (this.ruleForm.contractTimeBegin) {
          if (!compareTime(this.ruleForm.contractTimeBegin, val)) {
            this.$message.error('结束时间不能小于开始时间')
            this.ruleForm.contractTimeEnd = ''
          }
        }
      },
      'ruleForm.cooperationMode'(val) {
        console.log(this.ruleForm.cooperationMode)
      }
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
      // 根据编辑还是添加标识符确定是否需要请求数据
      if (this.operationActionTag === 'edit') {
        this.id = this.$route.query.id
        this.getMemberReportDetail()
        if (this.$route.query && this.$route.query.status && parseInt(this.$route.query.status) === 6) {
          this.unCheckEditTag = true
        }
        this.$nextTick(() => {
          const el = document.getElementsByClassName('tags-view-item')[0]
          el.innerHTML = el.innerHTML.replace('添加', '编辑')
        })
      }
    },
    methods: {
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
        this.ruleForm.citySelectStr = item.true_cname
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
      searchCompanyByIdOrName() {
        if (!this.ruleForm.onlineShopCode) {
          this.$message.error('请输入网店代码')
          return
        }
        fetchFindUser({
          uid: this.ruleForm.onlineShopCode
        }).then(res => {
          const data = res.data.data[0]
          if (data && data.length > 0) {
            this.shopName = '匹配到公司名称为：' + data[0].qc + '&nbsp;注册地点：' + data[0].cname
          } else {
            this.shopName = '抱歉未匹配到公司名称'
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
      handleArguments() {
        if (this.unCheckEditTag) {
          // 当状态等于6，即”客服未通过，普通信息更改“，需要手动将提交的状态码改成5
          this.submitStatus = 3
        }
        if (Array.isArray(this.uploadedImg)) {
          this.uploadedImg = this.uploadedImg.join(',')
        }
        const queryObj = {
          id: this.id,
          company_name: this.ruleForm.companyName,
          cooperation_type: this.memberTypeId,
          city_name: this.ruleForm.citySelectStr,
          cs: this.citySelectVal,
          viptype: this.ruleForm.ratio,
          is_new: this.ruleForm.isNewMember,
          company_contact: this.ruleForm.contact,
          company_contact_position: this.ruleForm.duties,
          company_contact_phone: this.ruleForm.phone,
          company_contact_weixin: this.ruleForm.wechat,
          contract_start: fortmatDate(this.ruleForm.contractTimeBegin),
          contract_end: fortmatDate(this.ruleForm.contractTimeEnd),
          cooperation_mode: this.ruleForm.cooperationMode,
          // current_contract_start: fortmatDate(this.ruleForm.memberTimeBegin),
          // current_contract_end: fortmatDate(this.ruleForm.memberTimeEnd),
          current_payment_time: fortmatDate(this.ruleForm.moneyInTime),
          next_payment_time: this.ruleForm.nextpayTime ? fortmatDate(this.ruleForm.nextpayTime) : '',
          total_contract_amount: this.ruleForm.totalMoney,
          current_contract_amount: this.ruleForm.getMoney,
          // 跟后端协商不填的情况下传-1
          lave_amount: this.ruleForm.lastMoney === '' ? -1 : this.ruleForm.lastMoney,
          company_id: this.ruleForm.onlineShopCode,
          account: this.ruleForm.account,
          remarke: this.ruleForm.note,
          status: this.submitStatus,
          imgs: this.uploadedImg,
          is_kf_voucher: this.ruleForm.kfVoucher,
          open_city_chat_remark: this.ruleForm.openCityChatRemark
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
          let obj = { url: QZ_CONFIG.oss_img_host + ret.img_list[i] }
          this.fileList.push(obj)
        }
        this.uploadedImg = ret.img_list
        for (var i = 0; i < ret.kf_voucher_img.length; i++) {
          this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
        }
        this.kfuploadImg = this.kfuploadImg
        this.ruleForm.companyName = ret.company_name
        this.ruleForm.memberType = ret.cooperation_type_name
        this.citySelectVal = ret.cs
        this.ruleForm.citySelectStr = ret.city_name
        this.ruleForm.ratio = ret.viptype
        this.ruleForm.isNewMember = String(ret.is_new)
        this.ruleForm.contact = ret.company_contact
        this.ruleForm.duties = ret.company_contact_position
        this.ruleForm.phone = ret.company_contact_phone
        this.ruleForm.wechat = ret.company_contact_weixin
        this.ruleForm.contractTimeBegin = fortmatDate(ret.contract_start * 1000)
        this.ruleForm.contractTimeEnd = fortmatDate(ret.contract_end * 1000)
        this.ruleForm.cooperationMode = String(ret.cooperation_mode)
        // this.ruleForm.memberTimeBegin = fortmatDate(ret.current_contract_start * 1000)
        // this.ruleForm.memberTimeEnd = fortmatDate(ret.current_contract_end * 1000)
        this.ruleForm.moneyInTime = fortmatDate(ret.current_payment_time * 1000)
        this.ruleForm.nextpayTime = fortmatDate(ret.next_payment_time * 1000)
        this.ruleForm.totalMoney = ret.total_contract_amount
        this.ruleForm.getMoney = ret.current_contract_amount
        this.ruleForm.lastMoney = String(ret.lave_amount) === '-1' ? '' : ret.lave_amount
        this.ruleForm.onlineShopCode = ret.company_id
        this.ruleForm.account = ret.account
        this.ruleForm.note = ret.remarke
        this.loading = false
        this.ruleForm.openCityChatRemark = ret.open_city_chat_remark
      },
      handleUploadSuccess(res, file, fileList) {
        this.uploadedImg.push(res.data.img_path)
      },
      handleRemove(res, file, fileList) {
        this.uploadedImg.forEach((item, index) => {
          if (res.url.indexOf(item) != -1) {
            this.uploadedImg.splice(index, 1)
          }
        })
      },
      preview(item) {
        this.previewTemp = []
        this.previewTemp.push(item)
        this.dialogVisiblePreview = true
      },
      isNewMemberChange(val) {
        if (val === '1') {
          this.ruleForm.kfVoucher = '0'
        } else if (val === '2') {
          this.ruleForm.kfVoucher = '1'
        }
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .erp-member-order {
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

    .upimg {
      display: inline-block;
      width: 100px;
      height: 100px;
      margin-right: 10px;
    }
  }

</style>
