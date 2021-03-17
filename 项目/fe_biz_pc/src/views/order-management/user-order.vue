<template>
  <div class="user-order">
    <el-card class="mb20 pb0">
      <el-row>
        <el-col :span="12">
          <el-tabs v-model="orderStatus" @tab-click="handleClick">
            <el-tab-pane label="全部订单" name="0" />
            <el-tab-pane label="待确认" name="2" />
            <el-tab-pane label="待发货" name="5" />
            <el-tab-pane label="已发货" name="3" />
            <el-tab-pane label="已兑换" name="12" />
            <el-tab-pane label="已退款" name="7" />
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
      <comTableList :table-data="tableData" :table-loading="tableLoading" :type="1" @getList="getList" />

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
  name: 'UserOrder',
  filters: {
    payType(value) {
      switch (value) {
        case '1':
          return 'c端微信支付'
        case '2':
          return '商户兑换卷方式'
        case '3':
          return '商户直接购买'
        default:
          return '----'
      }
    },
    orderStatus(val) {
      switch (val) {
        case '1':
          return '待付款'
        case '2':
          return '待发货'
        case '3':
          return '待收货'
        case '4':
          return '已收货'
        case '5':
          return '已完成'
        case '6':
          return '退款中'
        case '7':
          return '退款完成'
        case '8':
          return '已取消'
        case '9':
          return '待退货'
        case '10':
          return '已退货'
        case '11':
          return '退款失败'
        default:
          return '----'
      }
    }
  },
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
    copyUrl(url) {
      const oInput = document.createElement('input')
      oInput.value = url
      document.body.appendChild(oInput)
      oInput.select()
      document.execCommand('Copy')
      this.$message.success('复制成功')
      oInput.remove()
    },
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
      this.$apis.ORDER.orderList(obj).then(res => {
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
.user-order{
  .pb0 ::v-deep .el-card__body{
    padding-bottom: 0;
  }
  ::v-deep .el-tabs__nav-wrap::after{
    background-color: #fff;
  }
}
</style>
