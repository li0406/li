<template>
  <div class="content-wxsendlog">
    <div class="search-box">
      <h2>微信通知发送记录</h2>
      <div class='content-form'>
        <el-form :inline="true" ref="formInline" label-width="100px" :model="formInline">
          <div>
            <el-form-item label="订单号" prop="id">
              <el-input v-model="formInline.orderid" placeholder="请输入订单号"></el-input>
            </el-form-item>
            <el-form-item label="装修公司ID" prop="company_id">
              <el-input v-model="formInline.company_id" placeholder="请输入装修公司ID"></el-input>
            </el-form-item>
            <el-form-item label="装修公司简称" prop="company_jc">
              <el-autocomplete
                v-model="formInline.company_jc"
                :fetch-suggestions="querySearchId"
                placeholder="请输入装修公司简称"
                @select="handleIdSelect"
                @blur="handleComBlur"
              ></el-autocomplete>
            </el-form-item>
            <el-form-item label="开始时间" prop="date_begin">
              <el-date-picker
                v-model="formInline.date_begin"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="开始时间"/>
            </el-form-item>
            <el-form-item label="结束时间" prop="date_end">
              <el-date-picker
                v-model="formInline.date_end"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="结束时间"/>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="searchHandle">查询</el-button>
            </el-form-item>
          </div>
        </el-form>
      </div>
    </div>
    <div class="content-box">
      <h2>订单发送记录列表</h2>
      <div class="content-table" v-loading="visibleTable">
        <el-table
          :data="tableData"
          border
          style="width: 100%;">
          <el-table-column
            prop="orderid"
            align="center"
            label="订单号"
            width="150"
          >
          </el-table-column>
          <el-table-column
            prop="company_list"
            align="center"
            label="装修公司简称"
          >
            <template slot-scope="scope">
              <div v-for="(item, index) in scope.row.company_list" :key="index">
                {{item.jc}}（{{item.wx_numbers}}）
              </div>
            </template>
          </el-table-column>
          <el-table-column
            prop="leixing"
            label="通知类型"
            align="center"
            width="150"
          >
          </el-table-column>
          <el-table-column
            prop="result_msg"
            align="center"
            label="发送状态/原因">
          </el-table-column>
          <el-table-column
            prop="admin_user"
            align="center"
            label="操作人"
            width="150"
          >
          </el-table-column>
          <el-table-column
            prop="send_date"
            align="center"
            label="发送时间"
            width="160"
          >
          </el-table-column>
        </el-table>
        <el-row type="flex" justify="end" style="padding: 20px 0;">
          <el-pagination
            v-if="pageShow"
            :current-page="page_current"
            :page-size="page_size"
            :total="total_number"
            layout="total, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"/>
        </el-row>
      </div>
    </div>
  </div>
</template>
<script>
import { FetchGetCompany, fetchGetWxLogList } from '@/api/orderManage'

export default {
  name: 'index',
  components: {},
  created() {
    //   this.getLogList();

  },
  data() {
    return {
      formInline: {
        orderid: '',
        company_id: '',
        company_jc: '',
        date_begin: '',
        date_end: ''
      },
      comBlurFlag: null,
      company: '',
      companyid: '',
      tableData: [],
      // 分页
      page_current: 1,
      page_size: 10,
      total_number: 0,
      visibleTable: false,
      pageShow: false
    }
  },
  methods: {
    querySearchId(queryString, cb) {
      this.comBlurFlag = null
      this.searchUser(queryString, () => {
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          cb(this.zxComs)
        }, 1000)
      })
    },
    searchUser(query, cb) {
      FetchGetCompany({ uid: query }).then(res => {
        const data = res.data.data[0]
        this.zxComs = data
        cb && cb.call()
      })
    },
    handleIdSelect(item) {
      this.comBlurFlag = item
      this.companyid = item.id
    },
    handleComBlur() {
      if (!this.comBlurFlag) {
        this.companyid = ''
        this.company = ''
      }
    },
    searchHandle() {
      let beginTime = this.formInline.date_begin
      let endTime = this.formInline.date_end
      if (beginTime == null) {
        beginTime = ''
      }
      if (endTime == null) {
        endTime = ''
      }

      if (endTime != '' && beginTime == '') {
        this.$message.warning('请选择开始时间')
        return false
      }
      if (endTime == '' && beginTime != '') {
        this.$message.warning('请选择结束时间')
        return false
      }
      if (beginTime != '' && endTime != '' && beginTime > endTime) {
        this.$message.warning('开始时间小于结束时间')
        return false
      }
      this.getLogList()
    },
    getLogList() {
      let query = this.formInline
      query = Object.assign({}, query, {
        page: this.page_current,
        page_size: 10
      })
      this.visibleTable = true
      this.pageShow = true
      fetchGetWxLogList(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (this.tableData.length <= 0 && this.page_current > 1) {
            this.page_current--
            this.getLogList()
            this.visibleTable = false
          } else {
            this.tableData = []
            this.tableData = res.data.data.list
            this.page_current = res.data.data.page.page_current
            this.page_size = res.data.data.page.page_size
            this.total_number = res.data.data.page.total_number
            this.visibleTable = false
          }
        }
      })
    },
    // 每页显示多少条数
    handleSizeChange(val) {
      this.page_current = 1
      this.page_size = val
      this.getLogList()
    },
    // 跳转到第几页
    handleCurrentChange(val) {
      this.page_current = val
      this.getLogList()
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss">
  .content-wxsendlog {
    padding: 10px 15px;

    .search-box h2, .content-box h2 {
      font-size: 16px;
      font-weight: normal;
      color: #666;
    }

    .content-form, .content-table {
      border-top: 3px solid #d2d6de;
      border-radius: 3px;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
      padding: 5px 15px;
      background: #fff;
    }

    .content-form > form, .content-table {
      padding-top: 10px;
    }

    .content-box {
      margin-top: 10px;
    }

    .el-pagination {
      margin: 0 auto;
    }

    .el-form--inline .el-form-item {
      margin-right: 0;
    }

    .el-button--primary {
      margin-left: 10px;
    }
  }
</style>
