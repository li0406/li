<template>
  <div class="goods-purchased">
    <el-card class="mb20 pb0">
      <el-row>
        <el-col :span="12">
          <el-tabs v-model="orderStatus" @tab-click="handleClick">
            <el-tab-pane label="全部订单" name="0" />
            <el-tab-pane label="待支付" name="1" />
            <el-tab-pane label="待发货" name="2" />
            <el-tab-pane label="已发货" name="4" />
            <el-tab-pane label="已退款" name="7" />
            <el-tab-pane label="已取消" name="8" />
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
      <comTableList :table-data="tableData" :table-loading="tableLoading" :type="2" @getList="getList" />

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
  name: 'GoodsPurchased',
  components: {
    comTableList
  },
  data() {
    return {
      tableLoading: false,
      pageNo: 1,
      total: 0,
      pageSize: 20,
      orderStatus: '0',
      orderNo: '',
      goodsName: '',
      tableData: []
    }
  },
  created() {
    this.getList()
  },
  methods: {
    handleClick() {
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
        orderStatus: this.orderStatus !== '0' ? this.orderStatus : ''
      }
      return obj
    },
    getList() {
      this.tableLoading = true
      this.tableData = []
      const obj = this.handleData()
      this.$apis.ORDER.recordList(obj).then(res => {
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

<style lang="scss" scoped>
.goods-purchased{
  .pb0 ::v-deep .el-card__body{
    padding-bottom: 0;
  }
  ::v-deep .el-tabs__nav-wrap::after{
    background-color: #fff;
  }
}
</style>
