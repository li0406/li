<template>
  <div class="order-return-handing">
    <tableSearch title="退款货管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="订单编号" prop="orderNo">
          <el-input v-model="fromData.orderNo" clearable placeholder="订单编号" />
        </el-form-item>
        <el-form-item label="流水号" prop="payFlowNo">
          <el-input v-model="fromData.payFlowNo" clearable placeholder="流水号" />
        </el-form-item>
        <el-form-item label="订单状态" prop="orderStatus">
          <el-select v-model="fromData.orderStatus" clearable placeholder="请选择">
            <el-option label="待付款" value="1" />
            <el-option label="待发货" value="2" />
            <el-option label="待收货" value="3" />
            <el-option label="已收货" value="4" />
            <el-option label="已完成" value="5" />
            <el-option label="退款中" value="6" />
            <el-option label="退款完成" value="7" />
            <el-option label="已取消" value="8" />
            <el-option label="待退货" value="9" />
            <el-option label="已退货" value="10" />
            <el-option label="退款失败" value="11" />
          </el-select>
        </el-form-item>
        <el-form-item label="购买时间" prop="date">
          <el-date-picker
            v-model="fromData.date"
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
        <el-table-column prop="salePrice" label="装企销售价" align="center" />
        <el-table-column prop="sumPrice" label="实际付款" align="center" />
        <el-table-column prop="orderPrice" label="总收款" align="center" />
        <el-table-column prop="createDate" label="购买时间" align="center" />
        <el-table-column prop="orderStatus" label="交易状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.orderStatus | orderStatus }}
          </template>
        </el-table-column>
        <el-table-column label="结算状态（百度）" align="center">
          <template slot-scope="scope">
            {{ scope.row.supplySettleStatus | settleStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="supplySettlePrice" label="结算金额（百度）" align="center" />
        <el-table-column label="结算状态（装企）" align="center">
          <template slot-scope="scope">
            {{ scope.row.shopSettleStatus | settleStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="shopSettlePrice" label="结算金额（装企）" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="examine(scope.row.id)">审核</el-button>
            <el-button v-if="scope.row.orderStatus === '6'" type="text" @click="examine(scope.row.id)">审核</el-button>
            <el-button v-else-if="scope.row.orderStatus === '9'" type="text" @click="confirm(scope.row.id)">确认退货</el-button>
            <span v-else>----</span>
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
    </tableSearch>
    <el-dialog title="请确认是否将款项退回该用户" :visible.sync="dialogVisible" width="30%" :center="false">
      <el-form ref="form" :model="form" label-width="100px">
        <el-form-item label="转账金额" prop="name">
          <span>200</span>
        </el-form-item>
        <el-form-item label="转账信息" prop="name">
          <br>
          <div>收款账户名：<span>200</span></div>
          <div>开户行：<span>*******</span></div>
          <div>收款卡号：<span>*********</span></div>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
        <el-button @click="dialogVisible = false">取 消</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'OrderReturnHanding',
  data() {
    return {
      pageNo: 1,
      pageSize: 20,
      total: 0,
      fromData: {
        orderNo: '',
        payFlowNo: '',
        orderStatus: ''
      },
      form: {
        name: ''
      },
      exportLoading: false,
      dialogVisible: false,
      startTime: '',
      endTime: '',
      tableData: []
    }
  },
  computed: {},
  watch: {
    'fromData.date': {
      handler(val) {
        console.log(val)
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
  },
  mounted() {},
  methods: {
    onSearch() {
      this.pageNo= 1
      this.getList()
    },
    onExport() {
      const that = this
      that.exportLoading = true
      const obj = that.handleData()
      that.$apis.ORDER.exchangeExport(obj).then(res => {
        that.exportLoading = false
        const utf8decoder = new TextDecoder()
        const u8arr = new Uint8Array(res)
        // 将二进制数据转为字符串
        const temp = JSON.parse(utf8decoder.decode(u8arr))
        if (temp.code === 81000107) {
          this.$message.error(temp.message)
        } else {
          const link = document.createElement('a')
          const blob = new Blob([res], { type: 'application/vnd.ms-excel' })
          link.style.display = 'none'
          link.href = URL.createObjectURL(blob)
          link.download = '退换货列表' // 下载的文件名
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
      const that = this
      const obj = {
        pageNo: that.pageNo,
        pageSize: that.pageSize,
        startTime: that.startTime,
        endTime: that.endTime,
        orderNo: that.fromData.orderNo,
        orderStatus: that.fromData.orderStatus,
        payFlowNo: that.fromData.payFlowNo
      }
      return obj
    },
    getList() {
      const obj = this.handleData()
      this.$apis.ORDER.exchangeList(obj).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.pageNo = res.page.pageNo
          this.convertTableData()
        } else {
          this.$message.error(res.message)
        }
      })
    },
    convertTableData() {
      const data = this.tableData
      console.log(data)
      const arr = []
      const rowspan = []
      data.forEach(item => {
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
      })
      this.tableData = arr
      this.rowspan = rowspan
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 9, 10, 11, 12, 13, 14, 15, 16].includes(columnIndex)) {
        const _row = this.rowspan[rowIndex]
        const _col = _row > 0 ? 1 : 0 // 如果这一行隐藏了，这列也隐藏
        return {
          rowspan: _row,
          colspan: _col
        }
      }
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
    examine() {
      this.dialogVisible = true
    },
    confirm(id) {
      this.$confirm('是否确认退货？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.ORDER.confirmReturn({ id }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.order-return-handing{
  padding: 20px;
  .dialog-footer{
    display: flex;
    justify-content: space-around
  }
}
</style>
