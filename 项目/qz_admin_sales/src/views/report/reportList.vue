<template>
  <div class="small-report">
    <div class="search">
      <div class="clearfix">
        <div class="yixiang fl mr20">
          发票抬头：<br>
          <el-input v-model="invoice_title" placeholder="请输入" />
        </div>
        <div class="fl mr20">
          开票状态：<br>
          <el-select v-model="status" placeholder="请选择">
            <el-option
              v-for="item of invoiceStatusList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          开票类型：<br>
          <el-select v-model="type" placeholder="请选择">
            <el-option
              v-for="item of invoiceTypeList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          开票公司：<br>
          <el-select v-model="billing_company" placeholder="请选择">
            <el-option
              v-for="item of invoiceCompanyList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          是否到账：<br>
          <el-select v-model="is_in_account" placeholder="请选择">
            <el-option
              v-for="item of isToAccountList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          保存时间：<br>
          <div class="block mt4">
            <el-date-picker
              v-model="dateTime"
              type="daterange"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            />
          </div>
        </div>
         <div class="fl mr20">
          所属部门：<br>
          <el-select v-model="current_id" placeholder="请选择">
            <el-option
              v-for="item of currentList"
              :key="item.current_id"
              :label="item.current_name"
              :value="item.current_id"
            />
          </el-select>
        </div>
        <div class="fl">
          <br>
          <el-button type="primary" @click="getReportList('')">查询</el-button>
          <el-button type="default" @click="resetReportList()">重置</el-button>
        </div>
      </div>
      <div><br><el-button type="success" @click="toReportAddorEdit()">添加</el-button></div>
    </div>
    <div class="qian-main">
      <el-table
        :data="reportList"
        border
      >
        <el-table-column
          align="center"
          width="50"
          label="序号"
          type="index"
        />
        <el-table-column
          align="center"
          label="发票抬头"
        >
          <template slot-scope="scope">
            {{ scope.row.invoice_title }} <span v-if="scope.row.warning_info" class="sign el-icon-warning" @click="tips(scope.row.warning_info)" />
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="纳税人识别号"
          prop="taxpayer_identification_number"
        />
        <el-table-column
          align="center"
          label="开票类型"
          prop="type_name"
        />
        <el-table-column
          align="center"
          label="开票金额"
          prop="invoice_price"
        />
        <el-table-column
          align="center"
          label="实际到账金额"
          prop="account_money"
        />
        <el-table-column
          align="center"
          label="是否到账"
          prop="is_in_account_name"
        />
        <el-table-column
          align="center"
          label="开票公司"
          prop="billing_company_name"
        />
        <el-table-column
          align="center"
          label="开票状态"
          prop="status_name"
        />
        <el-table-column
          align="center"
          label="报备人"
          prop="invoice_report_username"
        />
        <el-table-column
          align="center"
          label="保存时间"
          prop="created_date"
          width="100"
        />
        <el-table-column
          align="center"
          label="操作"
          width="200"
        >
          <template slot-scope="scope">
            <span
              v-if="(scope.row.status == 1)||(scope.row.status == 5)"
              class="edit-btn"
              @click="toReportAddorEdit(scope.row.id)"
            >
              编辑
            </span>
            <span
              v-if="(scope.row.status == 1)||(scope.row.status == 5)"
              class="del-btn"
              @click="deleteReport(scope.row.id)"
            >
              删除
            </span>
            <span
              v-if="(scope.row.status == 1)||(scope.row.status == 5)"
              class="widthdraw-btn"
              @click="submitReport(scope.row.id)"
            >
              提交
            </span>
            <span
              class="check-btn"
              @click="toReportDetail(scope.row.id)"
            >
              查看
            </span>
            <span
              v-if="(scope.row.status == 2)"
              class="del-btn"
              @click="recallReport(scope.row.id)"
            >
              撤回
            </span>
          </template>
        </el-table-column>
      </el-table>
      <div>
        <el-pagination
          :current-page="reportPage.page_current"
          :page-size="reportPage.page_size"
          layout="total, prev, pager, next, jumper"
          :total="reportPage.total_number"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>
  </div>
</template>
<script>
import api from '@/api/invoiceReport'
export default {
  data() {
    return {
      pleaseSel: {
        id: '',
        name: '请选择'
      },
      //    发票报备列表
      //    入参
      invoice_title: '', //  发票抬头
      status: '', // 开票状态：0 待提交 1 已提交/待审核 2 开票中 3 已开票 4 已驳回
      type: '', // 开票类型：1增值税专用发票，2增值税普通发票，3增值税电子普通发票
      billing_company: '', // 开票公司
      is_in_account: '', // 是否到账：0未到账，1已到账
      paramPage: 1,
      //    返回参数
      reportList: [], //  发票报备列表
      reportPage: {
        total_number: 0, // 总条数
        page_total_number: 0, // 总页数
        page_size: 0, //  每页条数
        page_current: 0// 当前页
      }, //  发票报备分页信息

      //    发票报备列表搜索选项
      invoiceStatusList: [], // 开票状态列表
      invoiceTypeList: [], // 开票类型列表
      invoiceCompanyList: [], // 开票公司列表
      isToAccountList: [], // 是否到账选项
      dateTime: '', // 保存时间
      current_data: '', // 所属部分绑定的属性
      currentList: [
        {
          current_id: '',
          current_name: '请选择'
        }
      ], // 所属部门
      current_id: '',
      created_start_date: '',
      created_end_date: ''
    }
  },
  created() {
    this.loadAll()
    this.getReportList()
    this.loadSelect()
  },
  methods: {
    //  删除报备
    deleteReport(id) {
      this.$confirm('确认删除?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const params = {
          id
        }
        api.delete(params).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '删除成功!',
              type: 'success'
            })
            this.getReportList()
          } else {
            this.$message.error(res.data.error_msg)
          }
        }).catch(err => {
          this.$message.error(err)
        })
      }).catch(() => {

      })
    },
    //  提交报备
    submitReport(id) {
      this.$confirm('确认提交?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const params = {
          id
        }
        api.submit(params).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '提交成功!',
              type: 'success'
            })
            this.getReportList()
          } else {
            this.$message.error(res.data.error_msg)
          }
        }).catch(err => {
          this.$message.error(err)
        })
      }).catch(() => {

      })
    },
    //  撤回报备
    recallReport(id) {
      this.$confirm('确认撤回?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const params = {
          id
        }
        api.recall(params).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '撤回成功!',
              type: 'success'
            })
            this.getReportList()
          } else {
            this.$message.error(res.data.error_msg)
          }
        }).catch(err => {
          this.$message.error(err)
        })
      }).catch(() => {

      })
    },
    //  查看报备
    toReportDetail(id) {
      const { href } = this.$router.resolve({
        path: '/invoiceReport/reportDetail',
        query: {
          id
        }
      })
      window.open(href, '_blank')
    },
    resetReportList() {
      this.invoice_title = '' // 发票抬头
      this.status = '' // 开票状态：0 待提交 1 已提交/待审核 2 开票中 3 已开票 4 已驳回
      this.type = '' // 开票类型：1增值税专用发票，2增值税普通发票，3增值税电子普通发票
      this.billing_company = '' // 开票公司
      this.is_in_account = '' // 是否到账：0未到账，1已到账
      this.paramPage = 1// 当前页
      this.current_id = ''
      this.created_start_date = ''
      this.created_end_date = ''
      this.dateTime = []
      this.getReportList('')
    },
    //  发票报备列表
    getReportList(payment_id = this.$route.query.payment_id) {
      if (this.dateTime && this.dateTime.length > 0) {
        this.created_start_date = this.dateTime[0]
        this.created_end_date = this.dateTime[1]
      }else{
        this.created_start_date = ''
        this.created_end_date = ''
      }
      const params = {
        payment_id, //    报备编号
        invoice_title: this.invoice_title, // 发票抬头
        status: this.status, // 开票状态：0 待提交 1 已提交/待审核 2 开票中 3 已开票 4 已驳回
        type: this.type, // 开票类型：1增值税专用发票，2增值税普通发票，3增值税电子普通发票
        billing_company: this.billing_company, // 开票公司
        is_in_account: this.is_in_account, // 是否到账：0未到账，1已到账
        page: this.paramPage, // 当前页
        top_team_id: this.current_id, // 当前团队id,
        created_start_date: this.created_start_date,
        created_end_date: this.created_end_date
      }
      api.list(params).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.reportList = res.data.data.list || []
          this.reportPage = res.data.data.page
        } else {
          this.$message.error(res.data.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    handleSizeChange(val) {
      console.log(`每页 ${val} 条`)
    },
    handleCurrentChange(val) {
      this.paramPage = val
      this.getReportList()
    },
    createFilter(queryString) {
      return (restaurant) => {
        return (restaurant.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0)
      }
    },
    loadAll() {
      const params = {}
      api.options(params).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
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
    loadSelect() {
      api.select({
        select_type: 1
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0 ) {
          this.currentList = this.currentList.concat(res.data.data.list)
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    tips(warning_info) {
      this.$alert(warning_info, '预警信息', {
        confirmButtonText: '确定',
        customClass: 'alert-content'
      })
    },
    toReportAddorEdit(id = '') {
      this.$router.push({
        path: '/invoiceReport/reportAddorEdit',
        query: {
          id
        }
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss">
    .small-report {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .sign{
          color: #ff0000;
        }
        .el-date-editor{
            .el-range-separator{
                padding: 0;
            }
        }
        .search {
            background: #fff;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
        }
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
        .el-pagination{
            text-align: center;
            margin-top: 20px;
        }
        .el-dialog__header{
            border-bottom: 1px dashed #CCC;
        }
        .dia_table{
            width: 100%;
        }
        .dia_table td{
            line-height: 2.5;
        }
        .fix_letter_spance{
            letter-spacing: 3px;
        }
        .fl {
            float: left;
        }
        .mr20 {
            margin-right: 20px;
        }
        .search{
            line-height: 36px;
        }
        .el-select{
            margin-top: 0px;
        }
        .c1{
            color: #FF0000;
        }
        .c2{
            color: #FF33CC;
        }
        .c3{
            color: #FF6600;
        }
        .c4{
            color: #008000;
        }
        .c5{
            color: #339999;
        }
        .c6{
            color: #409eff;
        }
        .edit-btn{
            color: #e6a23c;
            cursor: pointer;
        }
        .supplement-btn{
            color: #008000;
            cursor: pointer;
        }
        .del-btn{
            color: #f56c6c;
            cursor: pointer;
        }
        .report-btn{
            color:#6600CC;
            cursor: pointer;
        }
        .check-btn{
            color: #409eff;
            cursor: pointer;
        }
        .widthdraw-btn{
            color: #67c23a;
            cursor: pointer;
        }
        .el-form-item {
            margin-bottom: 0;
        }
        .flex {
            display: flex;
            .el-form-item__label {
                // width: 160px;
                text-align: left;
                flex: 1;
            }
            .el-form-item__content {
                flex: 6;
            }
        }
        .hk-img {
            width: 100px;
            height: 100px;
            background: #eee;
            margin: 10px;
            float: left;
            img {
                display: block;
                width: 100%;
                height: 100%;
            }
        }
        .text-blue {
            color: #409eff;
        }
        .s-table {
            margin-top: 20px;
        }
    }
    .alert-content p{
      color:#ff0000;
    }
</style>
