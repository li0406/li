<template>
  <div class="invoice-report-add">
    <div class="qian-main">
      <div class="red">
        <p>填写提示：</p>
        <p>1、开票金额要核对清楚，不得超出实际收款金额</p>
        <p>2、开票类型和开票公司要选择清楚，有不明处可向财务咨询</p>
        <p>3、是否到账根据实际是否收款选择，若收款后开票选是，若未收款提前开票选否</p>
        <p>4、金额以人民币为单位</p>
        <p>5、仅先开票，后报备的发票报备，可不关联小报备</p>
        <p>6、周一下午15:00之前推送到财务部，周四开票；周四下午15:00之前推送到财务部，周一开票</p>
      </div>
      <div class="main">
        <div class="flex selreport ali-cen">
          <div class="sel_btn">
            <el-button type="primary" plain @click="getSmallReportList()">选择小报备</el-button>
          </div>
          <div v-if="sel_smallReportList!=''">
            <el-table class="sel_tab" :data="sel_smallReportList" border>
              <el-table-column type="index" align="center" label="序号" />
              <el-table-column prop="company_name" align="center" width="100" label="公司名称" />
              <el-table-column prop="city_name" align="center" label="城市" />
              <el-table-column prop="cooperation_type_name" align="center" label="合作类型" />
              <el-table-column prop="creator_name" align="center" label="报备人" />
              <el-table-column prop="payment_total_money" align="center" label="收款金额" />
              <el-table-column prop="payment_date" align="center" width="100" label="汇款时间" />
              <el-table-column prop="exam_status_name" align="center" label="状态" />
              <el-table-column align="center" label="操作">
                <template slot-scope="scope">
                  <span class="check-btn" @click="removetabdata(scope.row)">移除</span>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
        <div class="selreport">
          <div class="invoice-title">开票信息</div>
          <div class="flex pl30">
            <div class="mt20 invoice-div">
              <div class="item">
                <span class="danger-color">*</span>
                开票类型：
                <el-select v-model="type" placeholder="请选择">
                  <el-option
                    v-for="item of invoiceTypeList"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  />
                </el-select>
              </div>
              <div class="item">
                <span class="danger-color">*</span>
                开票公司：
                <el-select v-model="billing_company" placeholder="请选择">
                  <el-option
                    v-for="item of invoiceCompanyList"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  />
                </el-select>
              </div>
              <div class="item">
                <span class="danger-color">*</span>
                是否到账：
                <el-select v-model="is_in_account" placeholder="请选择">
                  <el-option
                    v-for="item of isToAccountList"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  />
                </el-select>
              </div>
              <div class="item flex ali-cen">
                <span class="danger-color">*&nbsp;</span>
                开票金额：&nbsp;
                <el-input
                  v-model="invoice_price"
                  type="text"
                  class="input"
                  onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                  placeholder="请输入开票金额"
                />
              </div>
            </div>
            <div class="mt20 invoice-div">
              <div class="item flex ali-cen">
                <span v-if="type==3" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>申请人邮箱：&nbsp;
                <el-input v-model="apply_email" type="email" class="input" placeholder="请输入邮箱" />
              </div>
              <div class="item flex ali-cen">
                <span v-if="type!=3" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>收件人姓名：&nbsp;
                <el-input v-model="receiver_name" class="input" placeholder="请输入收件人姓名" />
              </div>
              <div class="item flex ali-cen">
                <span v-if="type!=3" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>收件人电话：&nbsp;
                <el-input
                  v-model="receiver_phone"
                  class="input"
                  onkeyup="value=value.replace(/[^\d.]/g,'');"
                  maxlength="11"
                  placeholder="请输入收件人电话"
                />
              </div>
              <div class="item flex ali-cen">
                <span v-if="type!=3" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>收件人地址：&nbsp;
                <el-input v-model="receiver_address" class="input" placeholder="请输入收件人地址" />
              </div>
            </div>
          </div>
        </div>
        <div class="selreport">
          <div class="invoice-title">
            <span class="mr10">开票资料</span>
            <span class="titletips">如开普票和电子普票，只需提供抬头税号即可，如对方要求开全，则提供完整的开票资料，开具专用发票所有开票资料都要填写</span>
          </div>
          <div class="flex pl30">
            <div class="mt20 invoice-div">
              <div class="item flex ali-cen">
                <span class="danger-color">*&nbsp;</span>
                <span class="tit">发票抬头：</span>
                <el-input v-model="invoice_title" class="input" placeholder="请输入发票抬头" />
              </div>
              <div class="item flex ali-cen">
                <span class="danger-color">*&nbsp;</span>
                纳税人识别号：&nbsp;
                <el-input
                  v-model="taxpayer_identification_number"
                  class="input"
                  placeholder="请输入纳税人识别号"
                />
              </div>

              <div class="item flex ali-cen">
                <span v-if="type==1" class="danger-color">*&nbsp;</span>
                <span class="tit">开户行：</span>
                <el-input v-model="company_bank" class="input" placeholder="请输入开户行" />
              </div>

            </div>
            <div class="mt20 invoice-div pl49">
              <div class="item flex ali-cen">
                <span v-if="type==1" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>电话：&nbsp;
                <el-input
                  v-model="company_phone"
                  class="input"
                  maxlength="20"
                  placeholder="请输入电话"
                />
              </div>
              <div class="item flex ali-cen">
                <span v-if="type==1" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>地址：&nbsp;
                <el-input v-model="company_address" class="input" placeholder="请输入地址" />
              </div>

              <div class="item flex ali-cen">
                <span v-if="type==1" class="danger-color">*&nbsp;</span>
                <div v-else>&nbsp;&nbsp;</div>银行账号：&nbsp;
                <el-input v-model="company_bank_account" class="input" placeholder="请输入银行账号" />
              </div>

            </div>
          </div>
        </div>
        <div class="selreport">
          <div class="invoice-title">
            <span class="mr10">回传合同和营业执照</span>
            <span class="titletips">扫描件或拍照内容与公司盖章，一定要清楚可见，否则财务处一律不通过</span>
          </div>
          <div v-if="licenseBul" class="imgs-div">
            <elUpload :imglist="license_imgs" @upimglist="contract" />
          </div>
        </div>
        <div class="selreport">
          <div class="invoice-title">
            <span class="mr10">其他说明</span>
            <span class="titletips">有其他要求说明时填写，若申请的小报备为上系统之前，在此处上传小报备截图</span>
          </div>
          <div v-if="otherBul" class="imgs-div">
            <elUpload :imglist="other_imgs" @upimglist="other" />
          </div>
        </div>
        <div class="selreport">
          <el-input v-model="other_remarks" class="inputmark" placeholder="请输入" maxlength="255" />
        </div>
        <div class="foot-div">
          <div class="foot-btns">
            <el-button type="primary" @click="save(1)">保存</el-button>
            <el-button type="primary" plain @click="save(2)">提交</el-button>
          </div>
        </div>
      </div>
    </div>
    <!-- Table -->
    <el-dialog title="选择小报备" center width="60%" top="8vh" :visible.sync="dialogTableVisible">
      <div class="flex spa-bet">
        <div class="flex">
          <div class="screen">
            <div>公司名称：</div>
            <el-input v-model="company_name" class="dialog-input" placeholder="请输入" />
          </div>
          <div class="screen">
            <div>报备人：</div>
            <el-input v-model="creator_name" class="dialog-input" placeholder="请输入" />
          </div>
          <div class="screen">
            <div>城市：</div>
            <el-autocomplete
              v-model="citySelectStr"
              class="dialog-input"
              :fetch-suggestions="queryCity"
              placeholder="请输入"
              @select="handleSelect"
              @blur="handleBlur"
            />
          </div>
        </div>
        <div class="dialog-btn">
          <el-button
            class="dialog-seach"
            icon="el-icon-search"
            type="primary"
            @click="getSmallReportList()"
          >查询</el-button>
        </div>
      </div>
      <el-table class="sel_tab" :data="smallReportList" border>
        <el-table-column type="index" align="center" label="序号" />
        <el-table-column prop="company_name" align="center" label="公司名称" />
        <el-table-column prop="city_name" align="center" label="城市" />
        <el-table-column prop="cooperation_type_name" align="center" label="合作类型" />
        <el-table-column prop="creator_name" align="center" label="报备人" />
        <el-table-column prop="payment_total_money" align="center" label="收款金额" />
        <el-table-column prop="payment_date" align="center" width="100" label="汇款时间" />
        <el-table-column align="center" label="发票报备">
          <template slot-scope="scope">
            <span v-if="scope.row.has_invoice==1">有</span>
            <span v-else>无</span>
          </template>
        </el-table-column>
        <el-table-column align="center" label="操作">
          <template slot-scope="scope">
            <span v-if="scope.row.sel" class="cancel-btn" @click="removetabdata(scope.row)">取消</span>
            <span v-else class="check-btn" @click="seltabdata(scope.row)">选择</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="page-div">
        <el-pagination
          :current-page="smallReportPage.page_current"
          :page-size="smallReportPage.page_size"
          layout="total, prev, pager, next, jumper"
          :total="smallReportPage.total_number"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchSmallReportList } from '@/api/smallReport'
import api from '@/api/invoiceReport'
import { fetchCityList } from '@/api/common'
export default {
  components: {
    elUpload: () => import('./components/el-upload')
  },
  data() {
    return {
      pleaseSel: {
        id: '',
        name: '请选择'
      },
      emailReg: /^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/, //  邮箱正则
      loading: false,
      dialogTableVisible: false,
      cityBlurFlag: null,
      citys: [],
      citySelectStr: '',
      licenseBul: false, // 证件图片组件显示隐藏
      otherBul: false, // 其他说明图片显示隐藏
      //  报备列表入参
      company_name: '', //  公司名称
      creator_name: '', //  报备人姓名
      citySelectVal: '', // 城市id
      paramPage: 1, //  当前页
      //  报备列表返回参数
      smallReportList: [],
      smallReportPage: {
        total_number: 0, // 总条数
        page_total_number: 0, // 总页数
        page_size: 0, //  每页条数
        page_current: 0 // 当前页
      }, // 分页信息

      //  选中的报备列表
      sel_smallReportList: [],

      //  发票报备：添加/编辑发票
      id: '', //  	非必填，若传入则为编辑修改接口
      type: [], // 发票类型 1 增值税专用发票 2 增值税普通发票  3增值税电子普通发票,
      billing_company: [], // 开票公司 1 云网通 2 齐装网络科技,
      is_in_account: '', // 是否到账 1 是 2否
      invoice_price: '', // 开票金额
      invoice_title: '', // 发票抬头
      taxpayer_identification_number: '', //  纳税人识别号
      company_address: '', // 公司地址
      company_phone: '', // 电话 座机或手机号
      company_bank:'', //开户行
      company_bank_account:'',  //开户行账号
      apply_email: '', // 申请人邮箱
      receiver_name: '', // 收件人姓名
      receiver_phone: '', //  收件人电话 座机或手机号
      receiver_address: '', //  收件人地址
      license_imgs: [], //  证件图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
      other_imgs: [], //  其他说明图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
      other_remarks: '', // 其他说明
      report_payment_ids: [], //  关联报备ID ，多个ID以半角逗号分隔
      is_submit: '', // 保存提交 1 保存 2 提交

      //  发票报备列表搜索选项
      invoiceStatusList: [], // 开票状态列表
      invoiceTypeList: [], // 开票类型列表
      invoiceCompanyList: [], // 开票公司列表
      isToAccountList: [], // 是否到账选项

      //  查看发票报备
      //  入参从路由中query取id
      //  返回参数
      invoiceDetails: {},
      paymentDetails: [], //  小报备详情信息
      paymentPics: [], //  小报备截图
      invoicePics: [], //  发票报备图片
      invoiceOtherPics: [] //  发票报备其他图片


    }
  },
  watch: {
    citySelectStr(value) {
      if (!value) {
        this.citySelectVal = ''
        this.cityBlurFlag = null
      }
    }
  },
  created() {
    this.loadAllCity()
    this.getSelList()
    if (this.$route.query.id) {
      // 编辑 //映射数据
      this.id = this.$route.query.id //  	非必填，若传入则为编辑修改接口
      this.view()
    } else {
      this.loadCom()
    }
    if (this.$route.query.reportItem) {
      this.sel_smallReportList.push(this.$route.query.reportItem)
      this.report_payment_ids.push(this.$route.query.reportItem.id)
    }
  },
  methods: {
    //  加载组件
    loadCom() {
      this.licenseBul = true
      this.otherBul = true
    },
    //  查看发票报备
    view() {
      const params = {
        id: this.$route.query.id
      }
      api
        .view(params)
        .then((res) => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            this.invoiceDetails = res.data.data.invoiceDetails
            this.paymentDetails = res.data.data.paymentDetails //  小报备详情信息
            this.paymentPics = res.data.data.paymentPics //  小报备截图
            this.invoicePics = res.data.data.invoicePics //  发票报备图片
            this.invoiceOtherPics = res.data.data.invoiceOtherPics //  发票报备其他图片

            //  映射
            this.sel_smallReportList = this.paymentDetails

            if (this.paymentDetails !== '') {
              this.paymentDetails.forEach((item) => {
                this.report_payment_ids.push(item.id)
              })
            } else {
              this.report_payment_ids = [] //  关联报备ID ，多个ID以半角逗号分隔
            }
            this.type = this.invoiceDetails.type // 发票类型 1 增值税专用发票 2 增值税普通发票  3增值税电子普通发票,
            this.billing_company = this.invoiceDetails.billing_company // 开票公司 1 云网通 2 齐装网络科技,
            this.is_in_account = this.invoiceDetails.is_in_account // 是否到账 1 是 2否
            this.invoice_price = this.invoiceDetails.invoice_price // 开票金额
            this.invoice_title = this.invoiceDetails.invoice_title // 发票抬头
            this.taxpayer_identification_number = this.invoiceDetails.taxpayer_identification_number //  纳税人识别号
            this.company_address = this.invoiceDetails.company_address // 公司地址
            this.company_phone = this.invoiceDetails.company_phone // 电话 座机或手机号
            this.company_bank = this.invoiceDetails.company_bank //  开户行
            this.company_bank_account = this.invoiceDetails.company_bank_account  //银行账号
            this.apply_email = this.invoiceDetails.apply_email // 申请人邮箱
            this.receiver_name = this.invoiceDetails.receiver_name // 收件人姓名
            this.receiver_phone = this.invoiceDetails.receiver_phone //  收件人电话 座机或手机号
            this.receiver_address = this.invoiceDetails.receiver_address //  收件人地址

            this.license_imgs = [] //  证件图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
            this.invoicePics.forEach((item) => {
              this.license_imgs.push(item.img_src)
            })

            this.other_imgs = [] //  其他说明图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
            this.invoiceOtherPics.forEach((item) => {
              this.other_imgs.push(item.img_src)
            })

            this.other_remarks = this.invoiceDetails.other_remarks // 其他说明

            this.loadCom()
          } else {
            this.$message({
              type: 'warning',
              message: res.data.error_msg
            })
          }
        })
        .catch((err) => {
          this.$message.error(err)
        })
    },
    //  发票报备列表搜索选项
    getSelList() {
      const params = {}
      api.options(params).then((res) => {
        if (
          parseInt(res.status) === 200 &&
          parseInt(res.data.error_code) === 0
        ) {
          this.invoiceStatusList = [this.pleaseSel, ...res.data.data.status] // 开票状态列表
          this.invoiceTypeList = [this.pleaseSel, ...res.data.data.type] // 开票类型列表
          this.invoiceCompanyList = [this.pleaseSel, ...res.data.data.billing_company]// 开票公司列表
          this.isToAccountList = [this.pleaseSel, ...res.data.data.is_in_account]// 是否到账选项
        } else {
          this.$message({
            type: 'warning',
            message: res.data.error_msg
          })
        }
      })
    },
    //  发票报备：添加/编辑发票
    save(is_submit) {
      if (this.type === '') {
        this.$message.error('请选择开票类型')
        return
      }
      if (this.billing_company === '') {
        this.$message.error('请选择开票公司')
        return
      }
      if (this.is_in_account === '') {
        this.$message.error('请选择是否到账')
        return
      }
      if (this.invoice_price === '') {
        this.$message.error('请填写开票金额')
        return
      }
      if (this.type === 3) {
        if (this.apply_email === '') {
          this.$message.error('请填写申请人邮箱')
          return
        }
      }
      if (this.apply_email !== '') {
        if (!this.emailReg.test(this.apply_email)) {
          this.$message.error('邮箱格式不正确')
          return
        }
      }
      if (this.type !== 3) {
        if (this.receiver_name === '') {
          this.$message.error('请填写收件人姓名')
          return
        }
        if (this.receiver_phone === '') {
          this.$message.error('请填写收件人电话座机或手机号')
          return
        }
        if (this.receiver_address === '') {
          this.$message.error('请填写收件人地址')
          return
        }
      }
      if (this.invoice_title === '') {
        this.$message.error('请填写发票抬头')
        return
      }
      if (this.taxpayer_identification_number === '') {
        this.$message.error('请填写纳税人识别号')
        return
      } else {
        if (
          this.taxpayer_identification_number.length !== 15 &&
          this.taxpayer_identification_number.length !== 18 &&
          this.taxpayer_identification_number.length !== 20
        ) {
          this.$message.error('请输入正确的纳税人识别号')
          return
        }
      }
      if (this.type === 1) {
        if (this.company_phone === '') {
          this.$message.error('请填写电话')
          return
        }
        if (this.company_address === '') {
          this.$message.error('请填写公司地址')
          return
        }
        if (this.company_bank === '') {
          this.$message.error('请输入开户行')
          return
        }
        if (this.company_bank_account === '') {
          this.$message.error('请输入银行账号')
          return
        }
      }
      if (this.license_imgs === '') {
        this.$message.error('请上传证件照')
        return
      }
      const params = {
        id: this.id, //  	非必填，若传入则为编辑修改接口
        type: this.type, // 发票类型 1 增值税专用发票 2 增值税普通发票  3增值税电子普通发票,
        billing_company: this.billing_company, // 开票公司 1 云网通 2 齐装网络科技,
        is_in_account: this.is_in_account, // 是否到账 1 是 2否
        invoice_price: this.invoice_price, // 开票金额
        invoice_title: this.invoice_title, // 发票抬头
        taxpayer_identification_number: this.taxpayer_identification_number, //  纳税人识别号
        company_address: this.company_address, // 公司地址
        company_phone: this.company_phone, // 电话 座机或手机号
        company_bank: this.company_bank, //  开户行及账号
        company_bank_account: this.company_bank_account, // 银行账号
        apply_email: this.apply_email, // 申请人邮箱
        receiver_name: this.receiver_name, // 收件人姓名
        receiver_phone: this.receiver_phone, //  收件人电话 座机或手机号
        receiver_address: this.receiver_address, //  收件人地址
        license_imgs: this.license_imgs, //  证件图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
        other_imgs: this.other_imgs, //  其他说明图片，数组传入（若以字符串传入，多张 图片以半角逗号分隔）
        other_remarks: this.other_remarks, // 其他说明
        report_payment_ids: this.report_payment_ids.join(','), //  关联报备ID ，多个ID以半角逗号分隔
        is_submit: is_submit// 保存提交 1 保存 2 提交
      }
      api
        .save(params)
        .then((res) => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            if (is_submit === 1) {
              // 保存
              this.$message({
                message: '保存成功',
                type: 'success'
              })
            } else {
              //  提交
              this.$message({
                message: '提交成功',
                type: 'success'
              })
            }
            this.$router.push({
              path: '/invoiceReport/reportList'
            })
          } else {
            this.loading = false
            this.$message.error(res.data.error_msg)
          }
        })
        .catch((err) => {
          this.$message.error(err)
        })
    },
    //  匹配选中状态
    issel() {
      this.smallReportList.forEach((ele, i) => {
        this.sel_smallReportList.forEach((item) => {
          if (ele.id === item.id) {
            ele.sel = true
            this.$set(this.smallReportList, i, ele)
          }
        })
      })
    },
    //  选择选择小报备
    seltabdata(tabdata) {
      this.sel_smallReportList.push(tabdata)
      this.report_payment_ids.push(tabdata.id)
      this.issel()
    },
    //  取消/移除小报备
    removetabdata(tabdata) {
      this.sel_smallReportList.forEach((item, index) => {
        if (item.id === tabdata.id) {
          this.sel_smallReportList.splice(index, 1)
          this.report_payment_ids.splice(index, 1)
        }
      })
      this.smallReportList.forEach((ele, i) => {
        if (ele.id === tabdata.id) {
          ele.sel = false
          this.$set(this.smallReportList, i, ele)
        }
      })
    },
    //  获取城市数据
    loadAllCity() {
      fetchCityList().then((res) => {
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
    handleBlur() {
      if (!this.cityBlurFlag) {
        this.citySelectStr = ''
        this.citySelectVal = ''
      }
    },
    handleSelect(item) {
      this.cityBlurFlag = item
      this.citySelectVal = item.cid
      this.citySelectStr = item.value
    },
    queryCity(queryString, cb) {
      const citys = this.citys
      const results = queryString
        ? citys.filter(this.createFilter(queryString))
        : citys
      this.cityBlurFlag = null
      cb(results)
    },
    createFilter(queryString) {
      return (city) => {
        return city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1
      }
    },
    //  回传合同和营业执照图片
    contract(conimglist) {
      this.license_imgs = conimglist
    },
    //  其他说明
    other(otherimglist) {
      this.other_imgs = otherimglist
    },
    handleSizeChange(val) {
      console.log(`每页 ${val} 条`)
    },
    handleCurrentChange(val) {
      this.paramPage = val
      this.getSmallReportList()
    },
    //  小报备-列表页
    getSmallReportList() {
      this.loading = true
      const params = {
        company_name: this.company_name, //  公司名称
        payment_uname: '', // 汇款方
        city_id: this.citySelectVal, //  城市
        cooperation_type: '', //  合作类型（见备注）
        exam_status: '', // 审核状态（见备注）
        is_related: '', //  是否报备会员（1：否；2：是）
        keyword: '', // 搜索关键词（小程序使用）
        limit: 10, // 每页显示条数（默认：20）
        pass_status: '1', // 查询审核通过/不通过 1 所有审核通过 2 所有审核不通过
        creator_name: this.creator_name, //  报备人姓名
        page: this.paramPage // 当前页
      }
      fetchSmallReportList(params)
        .then((res) => {
          if (
            parseInt(res.status) === 200 &&
            parseInt(res.data.error_code) === 0
          ) {
            this.dialogTableVisible = true //  打开弹窗
            this.smallReportList = res.data.data.list
            this.smallReportPage = res.data.data.page
            this.issel()
          } else {
            this.loading = false
          }
        })
        .catch((err) => {
          this.$message.error(err)
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.invoice-report-add {
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
  .flex {
    display: flex;
  }
  .qian-main {
    padding: 0 20px 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
    .main {
      border: 1px solid rgba(228, 228, 228, 1);
      border-radius: 6px;
    }
    .lh-40 {
      line-height: 40px;
    }
    .mb-20 {
      margin-bottom: 20px;
    }
    .selreport {
      width: 1200px;
      margin: 0 auto;
    }
    .ali-cen {
      align-items: center;
    }
    .sel_btn {
      width: 200px;
      text-align: center;
      margin: 20px 0 0 0;
    }
    .sel_tab {
      width: 1000px;
      margin: 20px 0;
    }
    .invoice-title {
      padding: 20px 0;
      border-bottom: 1px dashed #ccc;
      font-size: 18px;
    }
    .danger-color {
      color: #ff0000;
    }
    .mt20 {
      margin: 20px 0 0 0;
    }
    .item {
      margin: 0 0 20px 0;
    }
    .invoice-div {
      width: 600px;
    }
    .input {
      width: 200px;
    }
    .inputlong {
      width: 770px;
    }
    .mr {
      margin: 0 10px 0 0;
    }
    .titletips {
      color: #ff0000;
      font-size: 13px;
    }
    .tit {
      width: 118px;
    }
    .pl49 {
      padding: 0 0 0 49px;
    }
    .pl30 {
      padding: 0 0 0 30px;
    }
    .pl31 {
      padding: 0 0 0 31px;
    }
    .imgs-div {
      padding: 20px 0 0 0;
    }
    .inputmark {
      width: 926px;
      margin: 10px 0 20px 0;
    }
    .foot-div {
      width: 1200px;
      height: 100px;
      line-height: 100px;
      text-align: center;
      margin: 10px auto 0 auto;
      border-top: 1px dashed #ccc;
    }
  }
  .screen {
    margin-right: 100px;
  }
  .dialog-input {
    margin: 20px 0;
  }
  .check-btn {
    color: #409eff;
    cursor: pointer;
  }
  .cancel-btn {
    color: #ff0000;
    cursor: pointer;
  }
  .spa-bet {
    justify-content: space-between;
  }
  .dialog-btn {
    position: relative;
  }
  .dialog-seach {
    position: absolute;
    bottom: 20px;
    right: 0;
  }
  .page-div {
    padding: 30px 0;
    text-align: center;
  }
}
</style>
