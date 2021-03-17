<template>
  <div class="order-manage">
    <tableSearch title="订单管理">
      <el-form slot="from" :inline="true">
        <el-form-item label="订单编号">
          <el-input v-model="orderNo" clearable placeholder="订单编号" />
        </el-form-item>
        <el-form-item label="用户名称">
          <el-input v-model="custName" clearable placeholder="用户名称" />
        </el-form-item>
        <el-form-item label="交易状态">
          <el-select v-model="orderStatus" clearable placeholder="交易状态">
            <el-option label="待付款" value="1" />
            <el-option label="待发货" value="2" />
            <el-option label="待收货" value="3" />
            <el-option label="已收货" value="4" />
            <el-option label="已完成" value="5" />
            <el-option label="退款中" value="6" />
            <el-option label="退款完成" value="7" />
            <el-option label="已取消" value="8" />
          </el-select>
        </el-form-item>
        <el-form-item label="订单交易时间">
          <el-date-picker
            v-model="date"
            clearable
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button v-loading="exportLoading" @click="onExport">导出</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        :span-method="arraySpanMethod"
        border
        style="width: 100%"
      >
        <el-table-column prop="orderNo" label="订单编号" align="center" />
        <el-table-column prop="custName" label="用户名称" align="center" />
        <el-table-column prop="goodsName" label="商品" align="center" />
        <el-table-column prop="salePrice" label="单价" align="center" />
        <el-table-column prop="saleNum" label="数量" align="center" />
        <el-table-column prop="purchasePrice" label="齐装采购价" align="center" />
        <el-table-column prop="supplyPrice" label="装企供销价" align="center" />
        <el-table-column prop="salePrice" label="实际付款" align="center" />
        <el-table-column prop="orderPrice" label="总收款" align="center" />
        <el-table-column prop="payTime" label="购买时间" align="center" />
        <el-table-column label="交易状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.orderStatus | status }}
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="orderInfoTo(scope.row.id)">订单详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'OrderManage',
  filters: {
    status(value) {
      switch (value) {
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
        default:
          return '----'
      }
    }
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      exportLoading: false,
      orderNo: '',
      custName: '',
      orderStatus: '',
      date: '',
      startTime: '',
      endTime: '',
      tableData: [],
      exportData: []
    }
  },
  watch: {
    date: {
      handler(val) {
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    onSearch() {
      this.currentPage = 1
      this.getList()
    },
    onExport() {
      const that = this
      that.exportLoading = true
      const obj = that.handleData()
      that.$apis.ORDER.exportOrderList(obj).then(res => {
        that.exportLoading = false
        const utf8decoder = new TextDecoder()
        const u8arr = new Uint8Array(res)
        // 将二进制数据转为字符串
        const temp = utf8decoder.decode(u8arr)
        if (!temp) {
          this.$message.error('暂无数据')
        } else {
          const link = document.createElement('a')
          const blob = new Blob([res], { type: 'application/vnd.ms-excel' })
          link.style.display = 'none'
          link.href = URL.createObjectURL(blob)
          link.download = '订单列表' // 下载的文件名
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
        }
      }).catch(err => {
        that.exportLoading = false
        that.$message.error(err.message)
      })
    },
    handleData() {
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize,
        startTime: this.startTime,
        endTime: this.endTime,
        orderStatus: this.orderStatus,
        orderNo: this.orderNo,
        custName: this.custName
      }
      return obj
    },
    getList() {
      const that = this
      that.tableLoading = true
      const obj = this.handleData()
      this.$apis.ORDER.orderList(obj).then(res => {
        that.tableLoading = false
        if (res.code === 0) {
          that.tableData = res.data || []
          that.total = res.page.total
          that.pageSize = res.page.pageSize
          that.currentPage = res.page.pageNo
          that.convertTableData()
        } else {
          that.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err.message)
        that.tableLoading = false
        // that.$message.error(err.message)
      })
    },
    convertTableData() {
      const data = this.tableData
      const arr = []
      const rowspan = []
      data.forEach(item => {
        if (item.goodsDetailVOList) {
          for (let i = 0; i < item.goodsDetailVOList.length; i++) {
            const newData = item.goodsDetailVOList[i]
            if (newData.id) {
              newData.itemId = newData.id
              delete newData.id
            }
            const rdata = { ...item, ...newData }
            rdata.combineNum = item.goodsDetailVOList.length
            arr.push(rdata)
            if (i === 0) {
              rowspan.push(item.goodsDetailVOList.length)
            } else {
              rowspan.push(0)
            }
          }
        }
      })
      this.tableData = arr
      this.rowspan = rowspan
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 8, 9, 10, 11].includes(columnIndex)) {
        const _row = this.rowspan[rowIndex]
        const _col = _row > 0 ? 1 : 0 // 如果这一行隐藏了，这列也隐藏
        return {
          rowspan: _row,
          colspan: _col
        }
      }
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    orderInfoTo(id) {
      this.$router.push('/order/order-info?id=' + id)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.order-manage{
  padding: 20px;
  // background: #f8f8f8;
}
</style>
