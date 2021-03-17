<template>
  <div class="exchange-record">
    <el-card class="mb20 pb0">
      <el-row>
        <el-col :span="12">
          <el-tabs v-model="status" @tab-click="handleClick">
            <el-tab-pane label="全部兑换" name="-1" />
            <el-tab-pane label="可兑换" name="0" />
            <el-tab-pane label="兑换中" name="1" />
            <el-tab-pane label="兑换成功" name="2" />
            <el-tab-pane label="已失效" name="3" />
          </el-tabs>
        </el-col>
        <el-col :span="12">
          <el-form :inline="true" class="fr">
            <el-form-item label="商品名称">
              <el-input v-model="goodsName" placeholder="商品名称" />
            </el-form-item>
            <el-form-item label="订单编号">
              <el-input v-model="orderNo" placeholder="订单编号" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="onSubmit">查询</el-button>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <el-card>
      <comTableList :table-data="tableData" :table-loading="tableLoading" :type="3" @getList="getList" />

      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="pageNo"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-card>
  </div>
</template>

<script>
import comTableList from '@/components/orderList/index'
export default {
  name: 'ExchangeRecord',
  components: {
    comTableList
  },
  data() {
    return {
      tableLoading: false,
      pageNo: 1,
      total: 0,
      pageSize: 20,
      status: '0',
      orderNo: '',
      goodsName: '',
      tableData: []
    }
  },
  created() {
    this.getList()
  },
  methods: {
    handleClick(tab, event) {
      this.pageNo = 1
      this.getList()
    },
    onSubmit() {
      this.pageNo = 1
      this.getList()
    },
    handleData(val) {
      const obj = {
        pageNo: this.pageNo,
        pageSize: this.pageSize,
        goodsName: this.goodsName,
        orderNo: this.orderNo,
        status: this.status === '-1' ? '' : this.status
      }
      return obj
    },
    getList() {
      this.tableLoading = true
      this.tableData = []
      const obj = this.handleData()
      this.$apis.ORDER.recordListV2(obj).then(res => {
        this.tableLoading = false
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.pageNo = res.page.pageNo
        } else {
          this.$message.error(res.message)
        }
      })
    },
    handleCurrentChange(val) {
      this.pageNo = val
      this.getList()
    },
    handleSizeChange(val) {
      this.pageNo = 1
      this.pageSize = val
      this.getList()
    }
  }
}
</script>

<style lang="scss" rel="stylesheet/scss" scoped>
.exchange-record{
  .pb0 ::v-deep .el-card__body{
    padding-bottom: 0;
  }
  ::v-deep .el-tabs__nav-wrap::after{
    background-color: #fff;
  }
}
</style>
