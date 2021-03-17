<template>
  <div class="city-account">
    <div class="search">
      <div class="city fl mr20">
        账户名: <br>
        <el-autocomplete
          v-model="accountSelectStr"
          class="inline-input"
          :fetch-suggestions="queryAccount"
          placeholder="请输入内容"
          @select="handleSelect"
        />
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">搜索</el-button>
        <el-button plain @click="resetForm">重置</el-button>
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
          prop="saler_name"
          label="账号"
        />
        <el-table-column
          align="center"
          prop="roler"
          label="角色"
        />
        <el-table-column
          align="center"
          prop="department"
          label="部门"
        />
        <el-table-column
          align="center"
          prop="inPosition"
          label="在职情况"
        />
        <el-table-column
          align="center"
          label="管辖城市"
        >
          <template slot-scope="scope">
            <span v-for="city in scope.row.citys" :key="city.cid" class="i-city">{{ city.cname }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleEdit(scope.row)">编辑</span>
            <span class="red pointer" @click="handleClear(scope.row)">清空</span>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="pageSize"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
  </div>
</template>

<script>
  import { fetchSalesCityList, fetchAccounts, fetchSalerClear } from '@/api/cityAccount'
  // 城市管辖
  export default {
    name: "citys",
    data() {
      return {
        loading: false,
        accountSelectStr: '',
        saler: '',
        tableData: [{
          acount: '唐菡莲',
          role: '客服组长',
          department: '订单审核部',
          inPosition: '在职',
          ownCitys: ''
        }],
        adminAccount: [],
        dataSelectVal: '',
        currentPage: 1,
        totals: 0,
        pageSize: 20
      }
    },
    created() {
      this.fetchSalesCityList()
      this.fetchAccounts()
    },
    methods: {
      fetchSalesCityList() {
        const queryObj = {
          saler: this.saler,
          p: this.currentPage,
          psize: this.pageSize
        }
        this.loading = true
        fetchSalesCityList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.fetchSalesCityList()
            } else {
              this.tableData = []
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                item.inPosition = parseInt(item.state) === 0 ? "离职" : "在职"
                this.tableData.push(item)
              })
              this.loading = false
            }
          }
        })
      },
      fetchAccounts() {
        fetchAccounts({
          uid: this.accountSelectStr,
          psize: 50
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.adminAccount = res.data.data[0]
          }
        })
      },
      handleSearch() {
        this.fetchSalesCityList()
      },
      resetForm() {
        this.accountSelectStr = ''
        this.saler = ''
      },
      queryAccount(queryString, cb) {
        this.fetchAccounts(queryString)
        clearTimeout(this.timeout)
        this.timeout = setTimeout(() => {
          cb(this.adminAccount)
        }, 1000)
      },
      handleSelect(item) {
        this.saler = item.id
      },
      handleEdit(obj) {
        this.$router.push({
          path: 'editCitys/' + obj.id
        })
      },
      handleClear(obj){
        fetchSalerClear({
          id: obj.id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '清除成功',
              type: 'success'
            });
            this.tableData.forEach((item, index) => {
              if(item.id === obj.id) {
                item.citys = []
              }
            })
          }else{
            this.$message({
              message: res.data.error_msg,
              type: 'error'
            });
          }
        })
      },
      handleSizeChange() {
        this.currentPage = 1
        this.fetchSalesCityList()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.fetchSalesCityList()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
.city-account{
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
  .search {
    background: #fff;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
  }
  .fl {
    float: left;
  }
  .mr20 {
    margin-right: 20px;
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
  .i-city{
    background-color: #EEE;
    margin: 0 5px;
    border-radius: 0.5em;
    padding: 5px;
  }
}
</style>
