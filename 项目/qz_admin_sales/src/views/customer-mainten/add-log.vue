<template>
  <div class="add-log">
    <div v-loading="loading" class="rizhi-main">
      <el-form ref="ruleForm" status-icon :model="ruleForm" :rules="rules" class="demo-ruleForm">
        <!-- 第一行input -->
        <div class="bai">
          <div class="fl mr20">
            <span class="red">*</span>公司简称：<br>
            <el-form-item prop="companyName">
              <el-autocomplete
                v-model="ruleForm.companyName"
                :fetch-suggestions="querySearchUser"
                :trigger-on-focus="false"
                class="inline-input"
                @select="handleSelectUser"
                placeholder=""
                @input="findCom"
                @blur="handleComBlur"
              ></el-autocomplete>
            </el-form-item>
          </div>
          <div class="fl mr20">
            <span class="red">*</span>会员ID：<br>
            <el-form-item prop="memberid">
              <el-autocomplete
                v-model="ruleForm.memberid"
                class="inline-input"
                style="width: 200px"
                placeholder=""
                clearable
                :disabled="true"
              />
            </el-form-item>
          </div>
          <div class="fl mr20">
            <span class="red">*</span>城市/区域：<br>
            <el-form-item prop="citySearch">
              <el-select v-model="ruleForm.citySearch" filterable placeholder="城市" @change="fetchArea">
                <el-option
                  v-for="item in citySelect"
                  :key="item.cid "
                  :label="item.cname"
                  :value="item.cid"
                />
              </el-select>
            </el-form-item>
          </div>
          <div class="fl mr20">
            <br>
            <el-form-item prop="area">
              <el-select v-model="ruleForm.area" filterable placeholder="区域">
                <el-option
                  v-for="item in areaSelect"
                  :key="item.area_id"
                  :label="item.area"
                  :value="item.area_id"
                />
              </el-select>
            </el-form-item>
          </div>
        </div>
        <!-- 第二行input -->
        <div class="bai">
          <div>
            <div class="fl mr20">
              <span class="red">*</span>联系人：<br>
              <el-form-item prop="contact">
                <el-input v-model="ruleForm.contact" style="width: 200px" placeholder="" />
              </el-form-item>
            </div>
            <div class="fl mr20">
              联系职务：<br>
              <el-form-item prop="job">
                <el-input v-model="ruleForm.job" style="width: 200px" placeholder="" />
              </el-form-item>
            </div>
          </div>
        </div>
        <!-- 第三行input -->
        <div class="bai">
          <div>
            <div class="fl mr20">
              联系方式（必填一项）：<br>
              <el-form-item prop="tel">
                <el-input v-model="ruleForm.tel" style="width: 200px" placeholder="手机"/>
              </el-form-item>
            </div>
            <el-form-item class="fl mr20">
              <i class="fa fa-headphones" style="font-size:30px;color:skyblue;cursor:pointer; margin-top:20px;" @click="telphone" />
            </el-form-item>
            <div class="fl mr20">
              <br>
              <el-form-item prop="wx">
                <el-input v-model="ruleForm.wx" style="width: 200px" placeholder="微信" />
              </el-form-item>
            </div>
            <div class="fl mr20">
              <br>
              <el-form-item prop="qq">
                <el-input v-model="ruleForm.qq" style="width: 200px" placeholder="QQ" />
              </el-form-item>
            </div>
          </div>
        </div>
        <!-- 第四行input -->
        <div class="bai">
          <div>
            <div class="fl mr20">
              <span class="red">*</span>客户类型：<br>
              <el-form-item prop="customerType">
                <el-select v-model="ruleForm.customerType" placeholder="请选择">
                  <el-option
                    v-for="item in customerTypeSelect"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
                </el-select>
              </el-form-item>
            </div>
            <div class="fl mr20">
              <span class="red">*</span>客户来源：<br>
              <el-form-item prop="customerSource">
                <el-select v-model="ruleForm.customerSource" placeholder="请选择">
                  <el-option
                    v-for="item in customerSourceSelect"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
                </el-select>
              </el-form-item>
            </div>
          </div>
        </div>
        <!-- 公司地址 -->
        <div class="bai">
          <span class="red">*</span>公司地址：<br>
          <el-form-item prop="companyArea">
            <el-input v-model="ruleForm.companyArea" type="textarea" cols="80" rows="3" style="resize:none" />
          </el-form-item>
        </div>
        <!-- 意向等级 -->
        <div class="h40">
          <el-form-item prop="radio">
            <span class="red">*</span>意向等级：&nbsp;
            <el-radio-group v-model="ruleForm.radio">
              <el-radio label="1">A</el-radio>
              <el-radio label="2">B</el-radio>
              <el-radio label="3">C</el-radio>
            </el-radio-group>
            <span class="zhu">注：A代表意向很大，B代表意向还行，C代表意向一般</span>
          </el-form-item>
        </div>
        <!-- 谈判，拜访时间，预计 -->
        <div class="bai mt20">
          <div>
            <div class="fl mr20">
              <span class="red">*</span>谈判方式：<br>
              <el-form-item prop="chatway">
                <el-select v-model="ruleForm.chatway" placeholder="请选择">
                  <el-option v-for="item in chatwayselect" :key="item.value" :label="item.label" :value="item.value"/>
                </el-select>
              </el-form-item>
            </div>
            <div class="fl mr20">
              <span class="red">*</span>拜访时间：<br>
              <el-form-item prop="vistTimea">
                <el-date-picker
                  v-model="ruleForm.vistTimea"
                  type="datetime"
                  placeholder="选择日期时间"
                />
              </el-form-item>
            </div>
            <div class="fl mr20">
              <br>
              至
            </div>
            <div class="fl mr20">
              <br>
              <el-form-item prop="vistTimeb">
                <el-date-picker
                  v-model="ruleForm.vistTimeb"
                  type="datetime"
                  placeholder="选择日期时间"
                />
              </el-form-item>
            </div>
            <div class="fl mr20">
              预计签约：<br>
              <el-form-item prop="expectSigning">
                <el-select v-model="ruleForm.expectSigning" placeholder="请选择" @change="changeSign">
                  <el-option
                    v-for="item in expectSigningSelect"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
                </el-select>
              </el-form-item>
            </div>
            <div v-show="isShow" class="fl mr20">
              尾款时间：<br>
              <el-form-item prop="retainage_time">
                <el-date-picker
                  v-model="ruleForm.retainage_time"
                  style="resize:none"
                  type="datetime"
                  format="yyyy-MM-dd HH:mm"
                  value-format="yyyy-MM-dd HH:mm"
                  placeholder="选择日期"
                />
              </el-form-item>
            </div>
          </div>
        </div>
        <!-- 谈话内容 -->
        <div class="bai">
          <span class="red">*</span>谈话内容：<br>
          <el-form-item prop="conversation">
            <el-input v-model="ruleForm.conversation" type="textarea" cols="80" rows="6" style="resize:none" placeholder="谈话内容（请尽量多描述本次谈话，35字以上）" />
          </el-form-item>
        </div>
        <!-- 预计收款 -->
        <div class="bai mt20">
          <div class="fl mr20">
            预计收款：<br>
            <el-form-item prop="expectMoney">
              <el-input v-model="ruleForm.expectMoney" style="width: 200px" placeholder="" />
            </el-form-item>
          </div>
        </div>
        <!-- 下次联系时间 -->
        <div class="bai">
          <div class="fl mr20">
            <span class="red">*</span>下次联系时间：<br>
            <el-form-item prop="nextTelTime">
              <el-date-picker
                v-model="ruleForm.nextTelTime"
                style="resize:none"
                type="datetime"
                format="yyyy-MM-dd HH:mm"
                value-format="yyyy-MM-dd HH:mm"
                placeholder="选择日期"
              />
            </el-form-item>
          </div>
        </div>
        <el-button type="primary" @click="submitForm('ruleForm')">提交</el-button>
      </el-form>
    </div>
  </div>
</template>

<script>
import { fetchCompanyList, fetchReportAdd, fetchAreaList, fetchTelPhone, fetchFindUser } from '@/api/addLog'
import { fetchCityList } from '@/api/common'
import { isvalidPhone } from '../../../src/utils/validate'

export default {
  name: 'AddLog',
  data() {
    var validPhone = (rule, value, callback) => {
      if (this.ruleForm.tel === '' && this.ruleForm.wx === '' && this.ruleForm.qq === '') {
        callback(new Error('请至少输入一项'))
      } else if (!isvalidPhone(value) && this.ruleForm.wx === '' && this.ruleForm.qq === '') {
        callback(new Error('请输入正确的11位手机号码'))
      } else {
        callback()
      }
    }
    var check = (rule, value, callback) => {
      if (this.ruleForm.tel === '' && this.ruleForm.wx === '' && this.ruleForm.qq === '') {
        callback(new Error('  '))
      }
      setTimeout(() => {
        callback()
      }, 1000)
    }
    return {
      callId: '',
      createTime: '',
      ruleForm: {
        companyName: '',
        memberid: '',
        citySearch: '',
        area: '',
        contact: '',
        job: '',
        tel: '',
        wx: '',
        qq: '',
        customerType: '',
        customerSource: '',
        companyArea: '',
        radio: '',
        chatway: '',
        vistTimea: '',
        visttimeb: '',
        expectSigning: '',
        conversation: '',
        expectMoney: '',
        nextTelTime: '',
        retainage_time: ''
      },
      chatwayselect: [
        {
          value: '1',
          label: '上门'
        },
        {
          value: '2',
          label: '电话'
        },
        {
          value: '3',
          label: 'QQ'
        },
        {
          value: '4',
          label: '微信'
        }
      ],
      rules: {
        companyName: [
          { required: true, message: '请输入公司名称', trigger: 'change' }
        ],
        memberid: [],
        citySearch: [
          { required: true, message: '请填写城市', trigger: 'change' }
        ],
        area: [
          { required: true, message: '请填写区域', trigger: 'change' }
        ],
        contact: [
          { required: true, message: '请输入联系人', trigger: 'blur' }
        ],
        tel: [
          { validator: validPhone, trigger: 'blur' }
        ],
        wx: [
          { validator: check, trigger: 'blur' }
        ],
        qq: [
          { validator: check, trigger: 'blur' }
        ],
        customerType: [
          { required: true, message: '请选择客户类型', trigger: 'change' }
        ],
        customerSource: [
          { required: true, message: '请选择客户来源', trigger: 'change' }
        ],
        companyArea: [
          { required: true, message: '请输入地址', trigger: 'blur' }
        ],
        radio: [
          { required: true, message: '请选择意向等级', trigger: 'change' }
        ],
        chatway: [
          { required: true, message: '请选择谈判方式', trigger: 'change' }
        ],
        vistTimea: [
          { required: true, message: '请选择拜访开始时间', trigger: 'change' }
        ],
        vistTimeb: [
          { required: true, message: '请选择拜访结束时间', trigger: 'change' }
        ],
        conversation: [
          { required: true, message: '请输入谈话内容', trigger: 'blur' },
          { min: 35, message: '字数未满35字，请继续输入', trigger: 'blur' }
        ],
        nextTelTime: [
          { required: true, message: '请选择下次联系时间', trigger: 'change' }
        ]
      },
      // 电话
      dialogphoneVisible: false,
      tableDataperson: [
        {
          name: '施星辰',
          tel: 17696761244
        }
      ],
      // 区域二级联动
      citySelect: [],
      areaSelect: [],
      customerTypeSelect: [
        {
          value: '1',
          label: '新客户'
        },
        {
          value: '2',
          label: '老客户'
        }
      ],
      customerSourceSelect: [
        {
          value: '1',
          label: '已签会员'
        },
        {
          value: '2',
          label: '客服咨询转接'
        },
        {
          value: '3',
          label: '会员转介绍'
        },
        {
          value: '4',
          label: '后台注册公司'
        },
        {
          value: '5',
          label: '扫楼'
        },
        {
          value: '6',
          label: '同行网站'
        },
        {
          value: '7',
          label: '订单轰炸'
        },
        {
          value: '8',
          label: '空间营销'
        },
        {
          value: '9',
          label: '其他'
        },
        {
          value: '10',
          label: '合作页面'
        }
      ],
      expectSigningSelect: [
        {
          value: '1',
          label: '未签约'
        },
        {
          value: '2',
          label: '已签约'
        },
        {
          value: '3',
          label: '本周内'
        },
        {
          value: '4',
          label: '半月内'
        },
        {
          value: '5',
          label: '一个月内'
        },
        {
          value: '6',
          label: '三个月内'
        },
        {
          value: '7',
          label: '暂无意向'
        }
      ],
      // 模糊匹配公司简介
      restaurants: [],
      // 尾款时间
      isShow: false,
      loading: false,
      // 城市id
      citys: undefined,
      qus: undefined,
      comBlurFlag: null,
      zxComs: [],
      timeout: null,
      time: ''
    }
  },
  mounted() {
    this.loadAllCity()
    var date = new Date();
    this.time = date.getFullYear() + "-" +((date.getMonth()+1)<10?0:"")+(date.getMonth()+1)+"-"+(date.getDate()<10?0:"")+date.getDate()+" "+date.getHours()+":"+date.getMinutes();
    this.ruleForm.vistTimea = this.time
  },
  methods: {
    // 最后弹框验证会员ID
    loadAllCity() {
      fetchCityList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.citySelect = []
          const cityAll = res.data.data[0]
          cityAll.forEach((item, index) => {
            item.cid = parseInt(item.cid)
            this.citySelect.push(item)
          })
        } else {
          this.citySelect = []
        }
      })
    },
    submitForm(formName) {
      if (!this.ruleForm.memberid && Number(this.ruleForm.expectSigning) === 2) {
        this.$message({
          message: '已签约状态必填ID',
          type: 'error',
          duration: 5 * 1000
        })
        return false
      }
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.fetchAdd()
          this.$router.push({
            path: 'list'
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    fetchAdd() {
      const queryObj = this.handleArguments()
      if (isNaN(queryObj.cs)) {
        queryObj.cs = this.citys
        queryObj.qx = this.qus
      }
      this.loading = true
      fetchReportAdd(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '添加成功!'
          })
        } else {
          this.$message.error(res.data.error_msg)
          this.loading = false
        }
      }).catch(res => {
        this.$message({
          message: '保存失败，请重新保存',
          type: 'error',
          duration: 5 * 1000
        })
        this.loading = false
      })
    },
    handleArguments() {
      let re_time = ''
      const queryObj = {
        company_id: '', // 公司id
        call_id: this.callId,
        company_name: this.ruleForm.companyName, // 公司名称
        user_id: parseInt(this.ruleForm.memberid), // 会员ID
        cs: parseInt(this.ruleForm.citySearch), // 城市
        qx: parseInt(this.ruleForm.area), // 区县
        user_name: this.ruleForm.contact, // 联系人姓名
        user_tel: this.ruleForm.tel, // 联系人电话
        user_wx: this.ruleForm.wx, // 联系人微信
        user_qq: this.ruleForm.qq, // 联系人qq
        is_new: this.ruleForm.customerType, // 是否新用户:1不是,2是
        address: this.ruleForm.companyArea, // 公司地址
        intention: this.ruleForm.radio, // 意向:1A,2B,3C
        visit_style: this.ruleForm.chatway, // 谈判方式
        visit_start_time: '', // 拜访开始时间
        visit_end_time: '', // 拜访结束时间
        qianyue_status: this.ruleForm.expectSigning, // 签约状态(注意)
        retainage_time: '', // 尾款时间
        desc: this.ruleForm.conversation, // 谈话内容
        visit_next_time: '', // 下次联系时间
        user_job: this.ruleForm.job, // 用户职务 默认:1233456
        pre_money: this.ruleForm.expectMoney, // 预计收款
        customer_source: this.ruleForm.customerSource
      }
      if (this.ruleForm.nextTelTime) {
        queryObj.visit_next_time = this.ruleForm.nextTelTime
      }
      if (this.ruleForm.retainage_time) {
        queryObj.retainage_time = this.ruleForm.retainage_time
      }
      queryObj.visit_start_time = this.ruleForm.vistTimea
      queryObj.visit_end_time = this.ruleForm.vistTimeb
      return queryObj
    },
    // 获取公司列表
    fetchCompanyList(com) {
      fetchCompanyList(
        { company_name: com }
      ).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.restaurants = res.data.data
        } else {
          console.log('fetchCompanyList')
          this.restaurants = []
        }
      })
    },
    // 模糊匹配会员公司
    querySearchUser(queryString, cb) {
      this.comBlurFlag = null
      this.searchUser(queryString, () => {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          cb(this.zxComs)
        }, 1000)
      })
    },
    searchUser(query, cb) {
      // fetchFindUser({ uid: query }).then(res => {
      //   const data = res.data.data
      //   this.zxComs = data[0]
      //   console.log(this.zxComs)
      //   cb && cb.call()
      // })
      fetchCompanyList({ company_name: query }).then(res => {
        const data = res.data.data
        this.zxComs = []
        if (data != null) {
          data.forEach((item, index) => {
            item.id = item.company_id
            item.jc = item.company_name
            item.qc = item.company_name
            item.value = item.cname + ': ' + item.company_jc
            this.zxComs.push(item)
          })
        }
        cb && cb.call()
      })
    },
    handleSelectUser(item) {
      this.comBlurFlag = item
      this.ruleForm.companyName = item.jc
      const value = item.jc
      fetchCompanyList(
        { company_name: value }
      ).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.restaurants = res.data.data
          this.ruleForm.memberid = this.restaurants[0].company_id + ''
          this.ruleForm.citySearch = this.restaurants[0].cname
          this.ruleForm.area = this.restaurants[0].aname
          this.citys = parseInt(this.restaurants[0].cs)
          this.qus = parseInt(this.restaurants[0].qx)
        } else {
          this.restaurants = []
        }
      })
    },
    handleComBlur() {
      if (!this.comBlurFlag) {
        // this.ruleForm.companyName = ''
      }
    },
    findCom() {
      this.ruleForm.memberid = ''
      this.ruleForm.citySearch = ''
      this.ruleForm.area = ''
    },
    // 区域联动
    fetchArea(value) {
      this.ruleForm.area = ''
      fetchAreaList({
        cid: value
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.areaSelect = res.data.data[0]
        } else {
          this.areaSelect = []
        }
      })
    },
    // 签约状态
    changeSign(value) {
      if (Number(value) === 2) {
        if (!this.ruleForm.memberid) {
          this.$message({
            message: '请填写会员ID',
            type: 'error',
            duration: 5 * 1000
          })
        }
        this.isShow = true
      } else {
        this.isShow = false
      }
    },
    telphone(name, tel) {
      if (this.ruleForm.tel) {
        this.ruleForm.chatway = "2"
        console.log(this.ruleForm.chatway);
        fetchTelPhone({
          tel: Number(this.ruleForm.tel)
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 1000010) {
            this.callId = res.data.data.callId
            this.createTime = res.data.data.createTime
            this.$message({
              message: res.data.error_msg,
              type: 'success',
              duration: 5 * 1000
            })
            
          } else {
            this.createTime = res.data.data.createTime
            this.$message({
              message: res.data.error_msg,
              type: 'error',
              duration: 5 * 1000
            })
          }
        })
      } else {
        this.$message({
          message: '请先填写号码再打电话',
          type: 'error',
          duration: 5 * 1000
        })
      }
    }
  }
}
</script>

<style scoped>
  .h40 {
    height: 40px;
  }
  .bai {
    height: auto;
    width: 1168px;
    overflow: hidden;
  }
  .mt20 {
    margin-top: 20px;
  }
  .mb20{
    margin-bottom:20px;
  }
  .mt80 {
    margin-top: 80px;
  }
  .red {
    color: red;
  }
  .fl {
    float: left;
  }
  .mr20 {
    margin-right: 20px;
  }
  .add-log {
    padding: 20px;
    box-sizing: border-box;
  }
  .rizhi-main {
    width: 100%;
    height: auto;
    overflow: hidden;
    padding: 20px 40px;
    background-color: #fff;
    border-top: 3px solid rgb(197, 196, 196);
  }
  .zhu {
    margin-left:20px;
    color: orangered;
  }
</style>
