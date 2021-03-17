<template>
  <div class="status-order">
    <div class="search">
      <div class="yixiang fl mr20">
        城市：<br>
        <el-autocomplete
          v-model="citySelectStr"
          class="inline-input"
          :fetch-suggestions="queryCity"
          placeholder="请选择"
          @select="handleSelect"
          @blur="handleBlur"
        />
      </div>
      <div class="fl mr20">
        公司名称：<br>
        <el-autocomplete
          v-model="zxComvalText"
          :fetch-suggestions="querySearchUser"
          :trigger-on-focus="false"
          class="inline-input"
          @select="handleSelectUser"
          placeholder="请输入公司名称或ID"
          @blur="handleComBlur"
        ></el-autocomplete>
      </div>
      <div class="fl mr20">
        时间: <br>
        <div class="block">
          <el-date-picker
            v-model="queryTimeRange"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
          />
        </div>
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
        <el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          label="城市"
        >
          <template slot-scope="scope">
            {{ scope.row.cname }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="company_name"
          label="公司名称"
        />
        <el-table-column
          align="center"
          label="会员状态"
        >
          <template slot-scope="scope">
            {{ scope.row.company_status }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="fen"
          label="分单"
        />
        <el-table-column
          align="center"
          prop="qiang"
          label="抢单"
        />
        <el-table-column
          align="center"
          prop="zeng"
          label="赠单"
        />
        <el-table-column
          align="center"
          prop="qian"
          label="签单"
        />
        <el-table-column
          align="center"
          label="签单金额(万)"
        >
          <template slot-scope="scope">
            {{ scope.row.qian_money }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row.com, scope.row.company_name)">查看详情</span>
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
import { fetchStautsOrderList, fetchFindUser } from '@/api/statusOrder'
import { fetchCityList } from '@/api/common'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'Fendan',
  filters: {},
  data() {
    return {
      // 搜索字段
      queryTimeRange: [],
      citySelectStr: '',
      zxComvalText: '',
      zxComval: '',
      citys: [],
      citySelectVal: '',
      cityBlurFlag: null,
      comBlurFlag: null,
      // 表格
      // tableData: [{
      //   'company_id': 19,
      //   'cname': '苏州-mock',
      //   'company_name': '红蚂蚁-mock',
      //   'company_status': '会员',
      //   'fen': 129,
      //   'qiang': 139,
      //   'zeng': 149,
      //   'qian': 159,
      //   'qian_money': 509
      // }],
      tableData: [],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      exportData: [],
      // 交互
      timeout: null,
      exportLoading: false,
      loading: false,
      zxComs: []
    }
  },
  watch: {
    citySelectStr(value) {
      if (!value) {
        this.citySelectVal = ''
        this.cityBlurFlag = null
      }
    },
    zxComvalText(value) {
      if (!value) {
        this.zxComval = ''
        this.comBlurFlag = null
      }
    }
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    const fendanObj = JSON.parse(localStorage.getItem('fendandata'))
    const gong = localStorage.getItem('gong')
    const cshi = localStorage.getItem('cshi')
    if (fendanObj !== null && (fendanObj.start!=''|| fendanObj.end!='')) {
      this.queryTimeRange[0] = fendanObj.start
      this.queryTimeRange[1] = fendanObj.end
      this.citySelectVal = fendanObj.cs
      this.zxComval = fendanObj.company_info
      this.currentPage = fendanObj.page
      this.zxComvalText = gong
      this.citySelectStr = cshi
      this.fetchList(fendanObj)
      localStorage.removeItem('fendandata')
      localStorage.removeItem('gong')
      localStorage.removeItem('cshi')
    }
  },
  beforeRouteLeave(to, from, next) {
    const queryObj = this.handleArguments()
    localStorage.setItem('gong', this.zxComvalText)
    localStorage.setItem('cshi', this.citySelectStr)
    localStorage.setItem('fendandata', JSON.stringify(queryObj))
    if (queryObj) {
      next()
    } else {
      next(false)
    }
  },
  methods: {
    // 城市匹配
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
    handleSelect(item) {
      console.log(item)
      this.cityBlurFlag = item
      this.citySelectVal = item.cid
      this.citySelectStr = item.value
    },
    handleBlur() {
      if (!this.cityBlurFlag) {
        // this.citySelectStr = ''
        // this.citySelectVal = ''
      }
    },
    // 装修公司匹配
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
      fetchFindUser({ uid: query }).then(res => {
        const data = res.data.data
        this.zxComs = data[0]
        cb && cb.call()
      })
    },
    handleSelectUser(item) {
      this.comBlurFlag = item
      this.zxComval = item.id
      this.zxComvalText = item.jc
    },
    handleComBlur() {
      if (!this.comBlurFlag) {
        this.zxComval = ''
        // this.zxComvalText = ''
      }
    },
    // 表格数据获取
    handleArguments(val) {
      let time_begin = ''
      let time_end = ''
      const queryObj = {
        start: '', // 发布查询开始时间
        end: '', // 发布查询结束时间
        cs: this.citySelectVal, // 查询城市ID
        company_info: this.zxComval, // 装修公司名称/ID
        page: this.currentPage, // 分页 页码  默认1
        down: '', // 是否导出 非0或非空->导出
        page_count: 20
      }
      if (queryObj.company_info == '') {
        queryObj.company_info = this.zxComvalText
      }
      if (val === 'export') {
        queryObj.down = 1
      }
      if (this.queryTimeRange && this.queryTimeRange.length > 0) {
        queryObj.start = this.queryTimeRange[0]
        queryObj.end = this.queryTimeRange[1]
      }
      return queryObj
    },
    fetchOrderList() {
      const queryObj = this.handleArguments()
      this.loading = true
      fetchStautsOrderList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.fetchOrderList()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              this.tableData.push(item)
            })
            this.loading = false
          }
        } else {
          this.tableData = []
          this.totals = 0
          this.loading = false
        }
      })
    },
    fetchList(obj) {
      fetchStautsOrderList(obj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.fetchList()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              this.tableData.push(item)
            })
          }
        } else {
          this.tableData = []
          this.totals = 0
        }
      })
    },
    // 查询
    handleSearch() {
      const cs = this.citySelectVal
      const gs = this.zxComvalText
      if (cs === '' && (gs == undefined || gs == 0 || gs == '') && (!(this.queryTimeRange && this.queryTimeRange.length > 0))) {
        this.$message({
          message: '请先选择查询条件，再进行查询',
          type: 'error',
          duration: 5 * 1000
        })
        return false
      }
      if (!(this.queryTimeRange && this.queryTimeRange.length > 0)) {
        this.$message({
          message: '请选择查询时间',
          type: 'error',
          duration: 5 * 1000
        })
        return false
      }
      if (cs === '' && (gs == undefined || gs == 0 || gs == '')) {
        this.$message({
          message: '请选择查询城市或公司名称',
          type: 'error',
          duration: 5 * 1000
        })
        return false
      }
      this.fetchOrderList()
    },
    // 导出
    fetchExportOrders(val, cb) {
      const queryObj = this.handleArguments(val)
      fetchStautsOrderList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 0) {
            res.data.data.list.forEach((item, index) => {
              // 此处对数据进行处理(后续)
              this.exportData.push(item)
            })
            cb && cb.call()
          } else {
            this.$message.error('未查询到符合要求的数据')
            this.exportLoading = false
          }
        } else {
          this.$message.error(res.data.error_msg)
          this.exportData = []
          this.exportLoading = false
        }
      })
    },
    handleExport() {
      this.exportLoading = true
      const tHeader = [
        '城市',
        '公司名称',
        '会员状态',
        '分单',
        '抢单',
        '赠单',
        '签单',
        '签单金额(万)'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'cname',
        'company_name',
        'company_status',
        'fen',
        'qiang',
        'zeng',
        'qian',
        'qian_money'
      ]
      this.fetchExportOrders('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        this.exportData = []
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '会员分单统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    // 查看详情
    handleDetail(id, name) {
      const com = parseInt(id)
      const comname = name
      this.$router.push({
        path: 'detail',
        query: { id: com, company: comname }
      })
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.fetchOrderList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchOrderList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .status-order {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
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
  }
</style>
