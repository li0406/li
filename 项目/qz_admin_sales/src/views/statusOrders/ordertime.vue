<template>
  <div class="content-ordertime">
    <div class="search-box">
      <h2>订单查看时间</h2>
      <div class='content-form'>
        <el-form :inline="true" ref="formInline" :model="formInline">
          <div>
            <el-form-item label="开始时间:" prop="start">
              <el-date-picker
                v-model="formInline.start"
                type="date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                placeholder="请选择开始时间"/>
            </el-form-item>
            <el-form-item label="结束时间:" prop="end">
              <el-date-picker
                v-model="formInline.end"
                type="date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                placeholder="请选择结束时间"/>
            </el-form-item>
            <el-form-item label="装修公司:" prop="company"
            >
              <el-autocomplete
                v-model="company_name"
                :fetch-suggestions="querySearch"
                :trigger-on-focus="false"
                placeholder="请输入装修公司名称/ID"
                ref="company"
                @select="handleSelect"
              >
              </el-autocomplete>
            </el-form-item>
            <el-form-item label="城市:" prop="isread">
              <el-select
                v-model="formInline.cs"
                placeholder="请选择"
                filterable
              >
                <el-option value="0" label="请选择"/>
                <el-option
                  v-for="item in citys"
                  :key="item.cid"
                  :value="item.cid"
                  :label="item.city_name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="订单编号:" prop="order">
              <el-input placeholder="请输入订单编号" v-model="formInline.order"></el-input>
            </el-form-item>
            <el-form-item label="是否阅单:" prop="isread">
              <el-select placeholder="请选择" v-model="formInline.isread">
                <el-option value="" label="请选择"></el-option>
                <el-option value="1" label="否"></el-option>
                <el-option value="2" label="是"></el-option>
              </el-select>

            </el-form-item>
            <el-form-item class="search-btn">
              <el-button type="primary" @click="searchHandle">查询</el-button>
            </el-form-item>
          </div>
        </el-form>
      </div>
    </div>
    <div class="content-box">
      <h2></h2>
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
            prop="order_id"
            align="center"
            label="订单编号"
            width="180"
          >
            <template slot-scope="scope" @click="orderNumHandle(scope.row.order_id)">
              <router-link class="companyId" :to="{path:'/statusOrders/detail',query:{order:scope.row.order_id}}">
                {{scope.row.order_id}}
              </router-link>
            </template>
          </el-table-column>
          <el-table-column
            prop="cname"
            align="center"
            width="240px"
            label="城市">
          </el-table-column>
          <el-table-column
            prop="qc"
            align="center"
            label="公司名称">
          </el-table-column>
          <el-table-column
            prop="fen_time"
            align="center"
            sortable="custom"
            label="分单时间">
          </el-table-column>
          <el-table-column
            prop="readtime"
            align="center"
            sortable="custom"
            label="阅单时间">
          </el-table-column>
          <el-table-column
            prop="diff_time"
            align="center"
            label="阅单时间差">
          </el-table-column>
          <el-table-column
            prop="isread"
            align="center"
            label="是否阅单">
            <template slot-scope="scope">
              {{scope.row.isread==0?'否':'是'}}
            </template>
          </el-table-column>
        </el-table>
        <el-row type="flex" justify="end" style="padding: 20px 0;">
          <el-pagination
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
  import {fetchGetList, fetchGetAllCity, FetchGetCompany} from '@/api/orderManage'
import { formatDate } from '@/utils/index'

  export default {
    name: "ordertime",
    data() {
      return {
        formInline: {
          start: '',
          end: '',
          company: '',
          cs: '',
          order: '',
          isread: '',
        },
        companys: [],
        company_name: '',
        citys: [],
        tableData: [],
        // 分页
        page_current: 1,
        page_size: 5,
        total_number: 0,
        timeout: null,
        visibleTable: false,
      }
    },
    created() {
      let temp = localStorage.getItem('skip')
      if (temp != null) {
        this.formInline.start = localStorage.getItem('beginTime')
        this.formInline.end = localStorage.getItem('endTime')
        this.company_name = localStorage.getItem('companyName')
        this.formInline.company = localStorage.getItem('companyId')
        this.formInline.cs = localStorage.getItem('city')
        this.formInline.order = localStorage.getItem('orderNum')
        this.formInline.isread = localStorage.getItem('isRead')
        this.getOrderList(this.formInline);
        localStorage.removeItem('beginTime')
        localStorage.removeItem('endTime')
        localStorage.removeItem('companyName')
        localStorage.removeItem('companyId')
        localStorage.removeItem('orderNum')
        localStorage.removeItem('isRead')
        localStorage.removeItem('city')
        localStorage.removeItem('skip')
      } else{
        this.getOrderList(this.formInline)
        this.formInline.start=''
        this.formInline.end=''
        this.formInline.company=''
        this.formInline.order=''
        this.formInline.isread=''
        this.company_name=''

      }
      this.getAllCity();

    },
    destroyed() {
      localStorage.removeItem('skip')
    },
    beforeRouteLeave(to, from, next) {
      if (this.formInline.start == null) {
        this.formInline.start = ''
      }
      if (this.formInline.end == null) {
        this.formInline.end = ''
      }
      localStorage.setItem('beginTime', this.formInline.start)
      localStorage.setItem('endTime', this.formInline.end)
      localStorage.setItem('companyName', this.company_name)
      localStorage.setItem('companyId', this.formInline.company)
      localStorage.setItem('orderNum', this.formInline.order)
      localStorage.setItem('isRead', this.formInline.isread)
      localStorage.setItem('city', this.formInline.cs)
      localStorage.setItem('skip', true)
      next()
    },
    methods: {
      getAllCity() {
        fetchGetAllCity().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.citys = res.data.data[0]
          } else {
            this.citys = []
            this.$message.warning('未查找到符合要求数据')
          }
        })
      },
      getOrderList(params) {
        this.visibleTable = true
        let query = Object.assign({}, params, this.formInline, {
          p: this.page_current,
          psize: 20
        })
        fetchGetList(query).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (this.tableData.length <= 0 && this.page_current > 1) {
              this.page_current--;
              this.getOrderList();
              this.visibleTable = true
            } else {
              this.tableData = []
              this.visibleTable = false
              this.tableData = res.data.data.list
              this.formInline.start = formatDate(res.data.data.search.start_time)
              this.formInline.end = formatDate(res.data.data.search.end_time)
              this.page_current = res.data.data.page.page_current
              this.page_size = res.data.data.page.page_size
              this.total_number = res.data.data.page.total_number
            }
          } else {
            this.visibleTable = false
            this.tableData = []
            this.$message.error(res.data.error_msg)
          }
        })
          .catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
          })
      },
      FetchGetCompany(query, cb) {
        FetchGetCompany({uid: query}).then(res => {
          let data = res.data.data
          this.companys = data[0]
          cb && cb.call()
        })
      },
      querySearch(queryString, cb) {
        this.FetchGetCompany(queryString, () => {
          clearTimeout(this.timeout)
          this.timeout = setTimeout(() => {
            if (this.companys.length == 0) {
              this.formInline.company = this.$refs.company.value
            }
            cb(this.companys)
          }, 1500)
        })
      },
      handleSelect(val) {
        this.formInline.company = val.id
        this.company_name = val.value
      },
      searchHandle() {
        if (this.formInline.company == ''|| this.company_name == '') {
          this.formInline.company = this.company_name
        }
        let begin = this.formInline.start
        let end = this.formInline.end
        if (begin == '' && end != '') {
          this.$message.warning('请输入开始时间')
          return false
        }
        if (begin != '' && end != '' && begin > end) {
          this.$message.warning('开始时间不能大于结束时间')
          return false
        }
        this.getOrderList()
      },
      orderNumHandle(val) {
        // console.log(111, val)
      },
      // 每页显示多少条数
      handleSizeChange(val) {
        this.page_current = 1
        this.page_size = val
        this.getOrderList()
      },
      // 跳转到第几页
      handleCurrentChange(val) {
        this.page_current = val
        this.getOrderList()
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
        this.getOrderList({orderby: sortBy})
      },
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .content-ordertime {
    padding: 10px 15px;
    .content-form {
      background: #fff;
      padding: 5px 15px;
    }
    .search-box h2, .content-box h2 {
      font-size: 16px;
      font-weight: normal;
      color: #666;
    }
    .el-form--inline .el-form-item {
      margin-right: 0;
      width: 14%;
    }
    .content-table {
      padding: 10px;
      background: #fff;
    }
    .el-pagination {
      margin: 0 auto;
    }
    .search-btn {
      padding-top: 35px;
    }
    .companyId {
      color: #42b983;
    }
  }
</style>
