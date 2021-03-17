<template>
  <div class="potential-table page-table">
    <FilterMonth @setMonthParams="getMonthParams" />
    <div class="qz-table">
      <el-table v-loading="tableLoading" :data="tableData" max-height="350">
        <el-table-column align="center" label="城市">
          <template slot-scope="scope">
            <span class="color-fff">{{ scope.row.city_name }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="achievement" align="center" label="业绩(元)" />
        <el-table-column prop="csos_fen" align="center" label="实际分单量" />
        <el-table-column prop="lf_num" align="center" label="量房量" />
        <el-table-column prop="lf_lv_show" align="center" label="量房率" />
        <el-table-column prop="qiandan_num" align="center" label="签单量" />
        <el-table-column prop="qiandan_lv_show" align="center" label="签单率" />
      </el-table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PotentialTable',
  data() {
    return {
      tabMonth: 1,
      tableData: [],
      tableLoading: false
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.tableLoading = true
      const obj = {
        tab_month: this.tabMonth
      }
      this.$apis.SALES_MAIN.cityImportant(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list
        } else {
          this.$message.error(res.error_msg)
        }
      })
    },
    getMonthParams(val) {
      this.tabMonth = val
      this.getList()
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.potential-table {
  .qz-table{
    border: 0;
    box-shadow: none;
    padding: 0;
    margin-bottom: 10px;
  }
  .mt10{
    margin-top: 10px;
  }
  .color-fff{
    color: #fff;
  }
  ::v-deep .el-table::before{
    background-color: #2C9CFA;
  }
}
</style>
