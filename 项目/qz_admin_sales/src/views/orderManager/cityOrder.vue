<template>
  <div class="member-order">
    <div class="search-box">
        <div class="search">
            <div class="fl mr20">
            日期段: <br>
            <div class="block mt4">
            <el-date-picker
                v-model="begin_time"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="请选择日期"/>
                ——
            <el-date-picker
                v-model="end_time"
                value-format="yyyy-MM-dd"
                type="date"
                format="yyyy-MM-dd "
                placeholder="请选择日期"/>
            </div>
            </div>
            <div class="fl mr20">
                城市：<br>
                <el-autocomplete
                v-model="citySelectStr"
                class="inline-input mt4"
                :fetch-suggestions="queryCity"
                placeholder="请选择"
                @select="handleSelect"
                @blur="handleBlur"
                />
            </div>
            <div>
                <br>
                <el-button type="primary" icon="el-icon-search" @click="handleSearch" class="mt4">查询</el-button>
            </div>
        </div>
    </div>
    <div class="qian-main">
      <div class="city-num">
        <span class="title">城市总数：</span><span class="num">{{ total.city_count }}</span>
        <span class="title">签单总数量：</span><span class="num">{{ total.sum_count }}</span>
      </div>
        <div class="content-table">
            <el-table
            :data="tableData"
            border
            style="width: 100%;"
            v-loading="visibleTable"
            @sort-change="sortHandle"
            :default-sort="{prop:'tableData',order:'ascending'}"
            >
            <el-table-column
                prop="cname"
                align="center"
                width="240px"
                label="城市">
            </el-table-column>
            <el-table-column
                prop="qiandan_num"
                align="center"
                sortable="custom"
                label="分配签单数量">
            </el-table-column>
            <el-table-column
                prop="consult_num"
                align="center"
                sortable="custom"
                label="咨询签单数量">
            </el-table-column>
            <el-table-column
                prop="sum_num"
                align="center"
                sortable="custom"
                label="签单总数量">
            </el-table-column>
            </el-table>
            <el-pagination
                :current-page="page_current"
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
    import { fetchGetCityOrderList } from '@/api/orderManage'
  import {fetchCityList} from '@/api/common'
  export default {
    name: 'cityOrder',
    components: {

    },
    data() {
      return {
        begin_time: '',
        end_time: '',
        citys: [],
        citySelectStr: '',
        citySelectVal: '',
        cityBlurFlag: null,
        tableData:[],
        page_current: 1,
        totals: 0,
        pageSize: 20,
        orderby: '',
        total: { // 统计数据
          city_count: '',
          consul_count: '',
          qiandan_count: '',
          sum_count: ''
        },
        visibleTable: false
      }
    },
    watch: {
      citySelectStr(value) {
        if (!value) {
          this.citySelectVal = ''
          this.cityBlurFlag = null
        }
      },
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
      this.fetchGetCityOrderList()
    },
    methods: {
      handleSearch() {
        let begin = this.begin_time
        let end = this.end_time
        if (begin != '' && end != '' && begin > end) {
          this.$message.warning('开始时间不能大于结束时间')
          return false
        }
        if (begin == null) {
          this.$message.warning('请选择开始时间')
          return false
        } else if (end == null) {
            this.$message.warning('请选择结束时间')
            return false
        } else {
            this.page_current = 1
            this.fetchGetCityOrderList()
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
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
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
        fetchFindUser({uid: query}).then(res => {
          let data = res.data.data
          this.zxComs = data[0]
          cb && cb.call()
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
      fetchGetCityOrderList() {
        const queryObj = {
          cs: this.citySelectVal,
          start: this.begin_time,
          end: this.end_time,
          p: this.page_current,
          orderby: this.orderby
        }
        this.visibleTable = true
        fetchGetCityOrderList(queryObj).then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                if (this.tableData.length <= 0 && this.page_current > 1) {
                    this.page_current--;
                    this.getOrderList();
                    this.visibleTable = true
                } else {
                    this.visibleTable = false
                    this.begin_time = res.data.data.time[0]
                    this.end_time = res.data.data.time[1]
                    this.tableData = res.data.data.list
                    this.totals = res.data.data.page.total_number
                    this.page_current = res.data.data.page.page_current
                    this.total = res.data.data.total // 统计数据
                }
            }else{
                this.tableData = []
                this.totals = 0
                this.visibleTable = false
            }
        }).catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
        })
      },
      // 每页显示多少条数
      handleSizeChange(val) {
        this.page_current = 1
        this.page_size = val
        this.fetchGetCityOrderList()
      },
      // 跳转到第几页
      handleCurrentChange(val) {
        this.page_current = val
        this.fetchGetCityOrderList()
      },
      //排序
      sortHandle(column) {
        let order = ''
        let sortBy = ''
        if (column.prop == null) {
          sortBy = ''
        } else {
          column.order == 'descending' ? order = 'desc' : order = 'asc'
          sortBy = column.prop + " " + order
        }
        this.orderby = sortBy
        this.fetchGetCityOrderList()
      },
    }
  }
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
    .member-order{
        .content-table{
            border-top: 0 !important;
        }
        .city-num{
          font-size: 14px;
          line-height: 32px;
          color: #666;
          margin-bottom: 10px;
          margin-left: 30px;
          .title{
            font-weight: bold;
          }
          .num{
            margin-right: 30px;
          }
        }
    }
</style>
