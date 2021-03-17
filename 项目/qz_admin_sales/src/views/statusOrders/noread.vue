<template>
  <div class="status-order">
    <div class="search">
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
      <div class="fl mr20">
        装修公司：<br>
        <el-autocomplete
          v-model="zxComvalText"
          :fetch-suggestions="querySearchUser"
          :trigger-on-focus="false"
          class="inline-input"
          @select="handleSelectUser"
          placeholder="请输入装修公司名称/ID"
          @blur="handleComBlur"
        ></el-autocomplete>
      </div>
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
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
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
          label="未读数量（条）"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row)">{{ scope.row.unread_count }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="market"
          label="销售"
        />
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
import { fetchReadOrderList, fetchFindUser } from '@/api/statusOrder'
import { fetchCityList } from '@/api/common'
export default {
  name: 'Noread',
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
      //   'unread_count': 119,
      //   'market': '王二-mocjk'
      // }],
      tableData: [],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      exportData: [],
      // 交互
      timeout: null,
      exportLoading: false,
      loading: false
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
    this.fetchOrderList()
    const fendanObj = JSON.parse(localStorage.getItem('fendandatab'))
    const gong = localStorage.getItem('gongb')
    const cshi = localStorage.getItem('cshib')
    if (fendanObj !== null && (fendanObj.start!=''|| fendanObj.end!='')) {
      this.queryTimeRange[0] = fendanObj.start
      this.queryTimeRange[1] = fendanObj.end
      this.citySelectVal = fendanObj.cs
      this.zxComval = fendanObj.company_info
      this.currentPage = fendanObj.page
      this.zxComvalText = gong
      this.citySelectStr = cshi
      this.fetchList(fendanObj)
      localStorage.removeItem('fendandatab')
      localStorage.removeItem('gongb')
      localStorage.removeItem('cshib')
    }
  },
  beforeRouteLeave(to, from, next) {
    const queryObj = this.handleArguments()
    localStorage.setItem('gongb', this.zxComvalText)
    localStorage.setItem('cshib', this.citySelectStr)
    localStorage.setItem('fendandatab', JSON.stringify(queryObj))
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
      fetchReadOrderList(queryObj).then(res => {
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
            if(res.data.data.options && res.data.data.options.date_begin && res.data.data.options.date_end) {
                this.queryTimeRange = [res.data.data.options.date_begin, res.data.data.options.date_end]
            }
          }
        } else {
          this.tableData = []
          this.totals = 0
          this.loading = false
        }
      })
    },
    fetchList(obj) {
      fetchReadOrderList(obj).then(res => {
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
      if (!(this.queryTimeRange && this.queryTimeRange.length > 0)) {
        this.$message({
          message: '请选择时间',
          type: 'error',
          duration: 5 * 1000
        })
        return false
      }
      this.fetchOrderList()
    },
    // 查看详情
    handleDetail(item) {
      this.$router.push({
        path: 'detail',
        query: { 
            id: item.company_id, 
            company: item.company_name,
            cid: item.cid, 
            cname: item.cname,
            beginTime:this.queryTimeRange[0],
            endTime:this.queryTimeRange[1]
        }
      })
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
    },
    handleCurrentChange(val) {
      this.currentPage = val
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
