<template>
  <div class="member-order">
    <div class="search-box">
      <div class="search">
        <div class="fl mr20">
          公司名称：<br>
          <el-input v-model="companyName" class="mt4" clearable placeholder="请输入" />
        </div>
        <div class="fl mr20">
          报备人：<br>
          <el-input v-model="salerName" class="mt4" clearable placeholder="请输入" />
        </div>
        <div class="fl mr20">
          报备类型：<br>
          <el-select v-model="reportType" class="mt4" clearable placeholder="请选择">
            <el-option label="大报备" :value="1" />
            <el-option label="小报备" :value="2" />
            <el-option label="发票报备" :value="3" />
          </el-select>
        </div>
        <div class="fl mr20">
          所属部门：<br>
          <el-select v-model="team" class="mt4" clearable placeholder="请选择">
            <el-option
              v-for="item in teamList"
              :key="item.current_id"
              :label="item.current_name"
              :value="item.current_id"
            />
          </el-select>
        </div>
        <div>
          <br>
          <el-button type="primary" icon="el-icon-search" class="mt4" @click="handleSearch">查询</el-button>
          <el-button v-loading="exportLoading" type="success" icon="el-icon-search" class="mt4" @click="handleExport">导出</el-button>
        </div>
      </div>
    </div>
    <div class="qian-main">
      <div class="content-table">
        <el-table
          v-loading="visibleTable"
          :data="tableData"
          border
          style="width: 100%;"
        >
          <el-table-column
            prop="index"
            align="center"
            label="序号"
            width="80"
          />
          <el-table-column
            prop="company_name"
            align="center"
            label="公司名称"
          />
          <el-table-column
            prop="report_type_name"
            align="center"
            label="报备类型"
          />
          <el-table-column
            prop="report_saler_name"
            align="center"
            label="报备人"
          />
          <el-table-column
            align="center"
            label="所属部门"
          >
            <template slot-scope="scope">
              {{ scope.row.top_team_name }}
            </template>
          </el-table-column>
          <el-table-column
            prop="report_date"
            align="center"
            label="报备时间"
          />
          <el-table-column
            prop="exam_remark"
            align="center"
            label="审核不通过原因"
          />
          <el-table-column
            prop="exam_operator_name"
            align="center"
            label="审核人"
          />
          <el-table-column
            prop="exam_date"
            align="center"
            label="审核时间"
          />
          <el-table-column
            align="center"
            label="操作"
          >
            <template slot-scope="scope">
              <el-button type="text" @click="toDetail(scope.row)">查看详情</el-button>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination
          :current-page="page"
          :page-size="20"
          layout="total, prev, pager, next, jumper"
          :total="totals"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { fetchSaleReportUnpasslog } from '@/api/orderManage'
import { export_json_to_excel } from '@/excel/Export2Excel'
import { fetchSelect } from '@/api/performanceStatistics.js'
export default {
  name: 'FailureStatistics',
  data() {
    return {
      companyName: '',
      salerName: '',
      reportType: '',
      page: 1,
      totals: 0,
      tableData: [],
      exportData: [],
      teamList: [],
      team: '',
      visibleTable: false,
      exportLoading: false
    }
  },
  created() {
    this.handleSearch()
    this.getTeamList()
  },
  methods: {
    handleSearch() {
      let obj = {}
      obj = this.getData()
      this.visibleTable = true
      fetchSaleReportUnpasslog(obj).then(res => {
        this.visibleTable = false
        if (res.status === 200 & res.data.error_code === 0) {
          this.tableData = res.data.data.list
          this.totals = res.data.data.page.total_number
        }
      })
    },
    toDetail(obj) {
      if (obj.report_type === 1) {
        this.$router.push({
          path: '/memberReport/detail',
          query: {
            id: obj.report_id,
            type: obj.cooperation_type
          }
        })
      } else {
        this.$router.push({
          path: '/smallReport/detail',
          query: {
            id: obj.report_id,
            type: obj.cooperation_type
          }
        })
      }
    },
    handleExport() {
      this.export = 1
      this.exportLoading = true
      const tHeader = [
        '序号',
        '公司名称',
        '报备类型',
        '报备人',
        '所属部门',
        '报备时间',
        '审核不通过原因',
        '审核人',
        '审核时间'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'index',
        'company_name',
        'report_type_name',
        'report_saler_name',
        'top_team_name',
        'report_date',
        'exam_remark',
        'exam_operator_name',
        'exam_date'
      ]
      this.fetchSaleReportUnpasslogExport('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '报备不通过统计')
        this.exportLoading = false
      })
    },
    getData() {
      const obj = {}
      obj.company_name = this.companyName
      obj.saler_name = this.salerName
      obj.report_type = this.reportType
      obj.top_team_id = this.team
      obj.page = this.page
      return obj
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    fetchSaleReportUnpasslogExport(val, cb) {
      let query = {}
      query = this.getData()
      query.export = 1
      fetchSaleReportUnpasslog(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 0) {
            this.exportData = []
            this.exportData = res.data.data.list
            cb & cb.call()
          } else {
            this.$message.warning('未查询到符合要求的数据')
            this.exportLoading = false
          }
        } else {
          this.$message.error(res.data.error_msg)
          this.exportData = []
          this.exportLoading = false
        }
      })
    },
    // 分页处理
    handleSizeChange() {
      this.page = 1
      this.handleSearch()
    },
    handleCurrentChange(val) {
      this.page = val
      this.handleSearch()
    },
    getTeamList() {
      const that = this
      const obj = {
        select_type: 1
      }
      fetchSelect(obj).then(res => {
        that.teamList = res.data
        if (res.data.error_code === 0) {
          that.teamList = res.data.data.list
        } else {
          that.$message('部门数据获取失败')
        }
      })
    }
  }
}
</script>

<style>

</style>
