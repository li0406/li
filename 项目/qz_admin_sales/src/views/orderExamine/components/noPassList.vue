<template>
  <div class="order-fail-list">
    <el-form :inline="true">
      <el-form-item label="订单查询：">
        <el-input v-model="name" placeholder="订单号" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="search">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table v-loading="loading" :data="tableData" style="width: 100%">
      <el-table-column
        prop="id"
        label="订单编号"
        align="center"
        width="180"
      />
      <el-table-column
        prop="qiandan_addtime"
        align="center"
        label="申请签单时间"
      />
      <el-table-column label="城市/区县">
        <template slot-scope="scope">
          {{ scope.row.cname }}/{{ scope.row.qz_area }}
        </template>
      </el-table-column>
      <el-table-column
        prop="xiaoqu"
        align="center"
        label="签单小区"
      />
      <el-table-column
        label="签单金额"
        align="center"
      >
        <template slot-scope="scope">
          <span v-if="scope.row.jine">{{ scope.row.jine }}万</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="company"
        align="center"
        label="申请公司"
        width="200"
      />
      <el-table-column
        prop="reason"
        align="center"
        label="不通过原因"
      />
      <el-table-column
        prop="chk_user"
        align="center"
        label="审核人"
      />
    </el-table>
    <el-pagination
      :current-page="currentPage"
      :page-size="limit"
      layout="total, prev, pager, next, jumper"
      :total="totals"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />
  </div>
</template>

<script>
import { fetchOrderNoList } from '@/api/orderExamine'
export default {
  name: 'FailList',
  data() {
    return {
      name: '',
      currentPage: 1,
      limit: 10,
      totals: 0,
      loading: false,
      tableData: []
    }
  },
  created() {
    this.getList()
  },
  methods: {
    search() {
      this.currentPage = 1
      this.getList()
    },
    getList() {
      const that = this
      const obj = {}
      if (that.name) {
        obj.order_id = that.name
      }
      obj.page = this.currentPage
      obj.limit = this.limit
      fetchOrderNoList(obj).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          that.tableData = res.data.data.list
          that.totals = res.data.data.page.total_number
        } else {
          that.tableData = []
          that.totals = 0
        }
      })
    },
    handleSizeChange() {
      this.currentPage = 1
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.order-fail-list{
  background: #fff;
}
</style>
