<template>
  <div class="work-list">
    <div class="search">
      <div class="city fl mr20">
        销售人员: <br>
        <el-input v-model="salerVal" placeholder="请输入内容" />
      </div>
      <div class="yixiang fl mr20">
        日期：<br>
        <el-date-picker
          v-model="queryTimeRange"
          type="daterange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
        />
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">搜索</el-button>
      </div>
      <p><el-button type="primary" icon="el-icon-search" @click="handleAdd">添加</el-button></p>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          prop="date"
          label="日期"
        />
        <el-table-column
          align="center"
          prop="username"
          label="销售人员"
        />
        <el-table-column
          align="center"
          prop="content"
          label="工作内容"
        />
        <el-table-column
          align="center"
          prop="reply_text"
          label="回复"
        />
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row.id)">查看</span>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
  </div>
</template>

<script>
import { fetchWorkReportList, fetchReportInfo } from '@/api/workReport'
export default {
  name: 'WorkList',
  data() {
    return {
      salerVal: '',
      tableData: [],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      loading: false,
      queryTimeRange: ''
    }
  },
  created() {
    this.fetchWorkReports()
  },
  methods: {
    fetchWorkReports() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchWorkReportList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.fetchWorkReports()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              this.tableData.push(item)
            })
            if(res.data.data.query && res.data.data.query.date_begin && res.data.data.query.date_end) {
              this.queryTimeRange = [res.data.data.query.date_begin, res.data.data.query.date_end]
            }
            this.loading = false
          }
        }
      })
    },
    handleArguments() {
      let time_begin = ''
      let time_end = ''
      const queryObj = {
        date_begin: '', // 开始时间
        date_end: '', // 结束时间
        user_search: this.salerVal, // 销售人员
        page_size: 20,
        page: this.currentPage
      }
      if (this.queryTimeRange && this.queryTimeRange.length > 0) {
        time_begin = new Date(this.queryTimeRange[0]).getFullYear() + '-' + (new Date(this.queryTimeRange[0]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[0]).getDate()
        time_end = new Date(this.queryTimeRange[1]).getFullYear() + '-' + (new Date(this.queryTimeRange[1]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[1]).getDate()
        queryObj.date_begin = time_begin
        queryObj.date_end = time_end
      }
      return queryObj
    },
    handleSearch() {
      this.fetchWorkReports()
    },
    handleAdd(item) {
      this.$router.push({
        path: 'add-work'
      })
    },
    handleDetail(id) {
      this.$router.push({
        path: 'work-detail/' + id
      })
    },
    handleSizeChange() {
      this.currentPage = 1
      this.fetchWorkReports()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchWorkReports()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .work-list {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .fl {
      float: left;
    }
    .mr20 {
      margin-right: 20px;
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
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
  }
</style>
