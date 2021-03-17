<template>
  <div class="potential-table page-table">
    <div class="qz-table">
      <el-table v-loading="tableLoading" :data="tableData" max-height="500">
        <el-table-column align="center" label="全国大区">
          <template slot-scope="scope">
            <span class="color-fff">{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="count" align="center" label="已开通城市" />
        <el-table-column prop="potential" align="center" label="企业潜力值" />
        <el-table-column prop="sign_amount" align="center" label="已签约企业" />
        <el-table-column align="center" label="签约率">
          <template slot-scope="scope">
            <span>{{ scope.row.sign_percent }}%</span>
          </template>
        </el-table-column>
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
      const obj = {
        tab_month: this.tabMonth
      }
      this.tableLoading = true
      this.$apis.SALES_MAIN.cityCitypotential(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data
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
