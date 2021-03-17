<template>
  <div class="potential-table page-table">
    <p class="potential-table-title">全国当月缺单最多的前20个城市</p>
    <div class="qz-table doubleThTable">
      <el-table v-loading="tableLoading" :data="tableData" max-height="250">
        <el-table-column align="center" label="城市">
          <template slot-scope="scope">
            <span class="color-fff">{{ scope.row.city_name }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="predict_orders" align="center" label="月所需分单量" />
        <el-table-column prop="fen_orders" align="center" label="实际分单量" />
        <el-table-column prop="lack_orders" align="center" label="实际缺单量" />
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
      this.$apis.SALES_MAIN.cityOrderlack().then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list
        } else {
          this.$message.error(res.error_msg)
        }
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.potential-table {
  .potential-table-title{
    text-align: left;
    margin: 0 0 10px;
    line-height: 20px;
    font-size: 14px;
    color: #2c9cf9;
  }
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
