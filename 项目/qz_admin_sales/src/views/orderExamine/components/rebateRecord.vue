<template>
  <div class="rebate-record">
    <el-table v-loading="loading" :data="tableData" style="width: 100%">
      <el-table-column
        prop="payment_date"
        label="汇款日期"
        align="center"
        width="150"
      />
      <el-table-column
        prop="back_money"
        label="返点金额"
        align="center"
        width="150"
      />
      <el-table-column
        prop="creator_name"
        label="报备人"
        align="center"
        width="150"
      />
      <el-table-column
        prop="company"
        label="收款方名称"
        align="center"
      >
        <template slot-scope="scope">
          <div v-for="item in scope.row.payee_list" :key="item.payee_px" class="mr20">{{ item.payee_type_name }}</div>
        </template>
      </el-table-column>
      <el-table-column
        prop="company_name"
        label="公司名称"
      />
      <el-table-column label="操作" align="center" fixed="right" width="80">
        <template slot-scope="scope">
          <el-button type="text" size="small" @click="check(scope.row)">查看</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import { fetchReportPaymentList } from '@/api/orderExamine'
export default {
  name: 'RebateRecord',
  props: {
    orderId: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      loading: false,
      tableData: []
    }
  },
  watch: {
    orderId(val) {
      this.getList(val)
    }
  },
  created() {
    const id = this.orderId
    this.getList(id)
  },
  methods: {
    getList(id) {
      const that = this
      that.loading = true
      fetchReportPaymentList({ order_id: id }).then(res => {
        that.loading = false
        if (res.status === 200 && res.data.error_code === 0) {
          that.tableData = res.data.data.list
        } else {
          that.tableData = []
          that.totals = 0
          this.$message({
            type: 'error',
            message: res.data.error_msg
          })
        }
      })
    },
    check(val) {
      const { href } = this.$router.resolve({
        name: 'reportDetail',
        path: '/smallReport/detail',
        query: {
          id: val.id,
          type: val.cooperation_type
        }
      })
      window.open(href, '_blank')
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.rebate-record{
  background: #fff;
}
</style>
