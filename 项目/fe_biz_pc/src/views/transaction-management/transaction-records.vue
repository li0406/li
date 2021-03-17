<template>
  <div class="transaction-records">
    <el-card class="mb20 pb0">
      <el-row>
        <el-col :span="12">
          <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="全部记录" name="0" />
            <el-tab-pane label="用户支付记录" name="2" />
            <el-tab-pane label="我的支付记录" name="1" />
          </el-tabs>
        </el-col>
        <el-col :span="12">
          <el-form :inline="true" class="fr">
            <el-form-item label="订单编号">
              <el-input v-model="orderNo" placeholder="订单编号" />
            </el-form-item>
            <el-form-item label="返佣状态">
              <el-select v-model="status" placeholder="返佣状态">
                <el-option label="未结算" value="0" />
                <el-option label="已结算" value="2" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="onSubmit">查询</el-button>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <el-card>
      <el-table
        ref="table"
        v-loading="tableLoading"
        border
        :data="tableData"
      >
        <el-table-column prop="payFlowNo" label="流水号" align="center" />
        <el-table-column prop="custName" label="用户" align="center" />
        <el-table-column prop="phoneNumber" label="手机号" align="center" />
        <el-table-column prop="orderNo" label="订单号" align="center" />
        <el-table-column prop="orderPrice" label="支付金额" align="center" />
        <el-table-column label="支付方式" align="center">
          <template slot-scope="scope">
            {{ scope.row.payType | payType }}
          </template>
        </el-table-column>
        <el-table-column prop="salePrice" label="销售价" align="center" />
        <el-table-column prop="payTime" label="购买时间" align="center" />
        <el-table-column prop="refundPrice" label="实际退款" align="center" />
        <!-- <el-table-column prop="salePrice" label="返佣" align="center" /> -->
        <el-table-column prop="address" label="返佣状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.status | status }}
          </template>
        </el-table-column>
      </el-table>
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
export default {
  name: 'TransactionRecords',
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
    status(value) {
      switch (value) {
        case '0':
          return '未结算'
        case '1':
          return '已结算'
        default:
          return '----'
      }
    }
  },
  data() {
    return {
      pageNo: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      ifSelf: '', // 区分:1自己 2用户
      orderNo: '',
      status: '',
      activeName: '',
      tableData: []
    }
  },
  created() {
    this.getList()
  },
  methods: {
    onSubmit() {
      this.pageNo = 1
      this.getList()
    },
    getList() {
      this.tableLoading = true
      const obj = {
        ifSelf: this.ifSelf,
        orderNo: this.orderNo,
        status: this.status,
        pageNo: this.pageNo,
        pageSize: this.pageSize
      }
      this.$apis.TRANSACTION.chargeList(obj).then((res) => {
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
    handleSizeChange(val) {
      this.pageNo = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.pageNo = val
      this.getList()
    },
    handleClick() {
      this.pageNo = 1
      if (this.activeName === '0') {
        this.ifSelf = ''
      } else {
        this.ifSelf = this.activeName
      }
      this.getList()
    }
  }
}
</script>

<style lang="scss" scoped>
.transaction-records{
  .pb0 ::v-deep .el-card__body{
    padding-bottom: 0;
  }
  ::v-deep .el-tabs__nav-wrap::after{
    background-color: #fff;
  }
}
</style>
