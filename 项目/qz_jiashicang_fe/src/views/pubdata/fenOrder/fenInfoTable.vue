<template>
  <div class="fen-info-table">
    <p class="statistics">统计时间：{{ infoDete }}</p>
    <div class="qz-table">
      <el-table v-loading="tableLoading" :data="tableData" border max-height="500">
        <el-table-column prop="trade_no" label="订单号" align="center" width="160" />
        <el-table-column prop="city_name" label="城市" align="center" width="120" />
        <el-table-column prop="operate_name" label="撤销提出方" align="center" width="120" />
        <el-table-column prop="remark" label="撤回原因" />
        <el-table-column prop="time" label="撤回时间" align="center" width="120" />
      </el-table>
    </div>
    <el-pagination
      v-if="total > 0"
      class="mt10"
      background
      :current-page="currentPage"
      :page-sizes="[10, 20, 50, 100]"
      :page-size="pageSize"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
      prev-text="上一页"
      next-text="下一页"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />
  </div>
</template>

<script>
export default {
  name: 'FenInfoTable',
  props: {
    state: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      infoDete: '',
      tableData: [],
      tableLoading: false
    }
  },
  watch: {
    state(val) {
      if (!val) {
        this.currentPage = 1
        this.pageSize = 20
        this.total = 0
        this.tableData = []
      } else {
        this.getList()
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.tableLoading = true
      const obj = {
        page: this.currentPage,
        page_size: this.pageSize
      }
      this.$apis.SALES_FEN.orderrebutdetail(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list
          this.total = res.data.page.total_number
          this.infoDete = `${res.data.options.date_begin}-${res.data.options.date_end}`
        } else {
          this.$message.error(res.error_msg)
        }
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.fen-info-table {
  .qz-table{
    border: 0;
    padding: 0;
    box-shadow: none;
    padding-bottom: 3px;
  }
  .mt10{
    margin-top: 10px;
  }
  ::v-deep .el-table::before{
    background-color: #2C9CFA;
  }
}
</style>
