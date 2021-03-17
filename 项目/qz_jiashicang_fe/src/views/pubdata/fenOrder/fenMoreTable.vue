<template>
  <div class="fen-more-table">
    <p class="statistics">统计时间：{{ moreDete }}</p>
    <div class="qz-table">
      <el-table v-loading="tableLoading" :data="tableData" border max-height="500">
        <el-table-column prop="city_name" label="城市" width="150" />
        <el-table-column prop="number" label="违规补轮次数" width="200" />
        <el-table-column prop="address" label="会员公司（违规补轮次数）">
          <template slot-scope="scope">
            <span v-for="item in scope.row.detail" :key="item">{{ item.company_name }}({{ item.number }}) &nbsp;</span>
          </template>
        </el-table-column>
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
  name: 'FenMoreTable',
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
      moreDete: '',
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
      this.$apis.SALES_FEN.violateapplydetail(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list
          this.total = res.data.page.total_number
          this.moreDete = `${res.data.options.date_begin}-${res.data.options.date_end}`
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
  } // 方法集合
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.fen-more-table {
  .qz-table{
    border: 0;
    box-shadow: none;
    padding: 0;
    margin-bottom: 10px;
  }
  .mt10{
    margin-top: 10px;
  }
  ::v-deep .el-table::before{
    background-color: #2C9CFA;
  }
}
</style>
