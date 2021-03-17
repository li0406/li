<template>
  <div class="finance-manage">
    <tableSearch title="财务管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="订单编号" prop="orderNo">
          <el-input v-model="fromData.orderNo" clearable placeholder="订单编号" />
        </el-form-item>
        <el-form-item label="订单状态" prop="orderStatus">
          <el-select v-model="fromData.orderStatus" clearable placeholder="交易状态">
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
        <el-form-item label="结算状态（百度）" prop="supplySettleStatus">
          <el-select v-model="fromData.supplySettleStatus" clearable placeholder="请选择">
            <el-option label="已结算" value="1" />
            <el-option label="未结算" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="结算状态（装企）" prop="shopSettleStatus">
          <el-select v-model="fromData.shopSettleStatus" clearable placeholder="请选择">
            <el-option label="已结算" value="1" />
            <el-option label="未结算" value="2" />
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
        border
        :span-method="arraySpanMethod"
        style="width: 100%"
      >
        <el-table-column prop="orderNo" label="订单编号" align="center" />
        <el-table-column prop="custName" label="用户名称" align="center" />
        <el-table-column prop="goodsName" label="商品" align="center" />
        <el-table-column prop="salePrice" label="单价" align="center" />
        <el-table-column prop="saleNum" label="数量" align="center" />
        <el-table-column prop="purchasePrice" label="齐装采购价" align="center" />
        <el-table-column prop="supplyPrice" label="装企供销价" align="center" />
        <el-table-column prop="orderPrice" label="实际付款" align="center" />
        <el-table-column prop="sumPrice" label="总收款" align="center" />
        <el-table-column prop="createDate" label="购买时间" align="center" />
        <el-table-column prop="orderStatus" label="订单状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.orderStatus | orderStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="shopSettleStatus" label="结算状态（百度）" align="center">
          <template slot-scope="scope">
            {{ scope.row.orderStatus | settleStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="shopSettlePrice" label="结算金额（百度）" align="center" />
        <el-table-column prop="supplySettleStatus" label="结算状态（装企）" align="center">
          <template slot-scope="scope">
            {{ scope.row.orderStatus | settleStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="supplySettlePrice" label="结算金额（装企）" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="orderInfoTo(scope.row.id, scope.row.orderType)">详情</el-button>
            <el-button v-if="scope.row.orderStatus === '2'" type="text" @click="payment(scope.row.id, scope.row.orderType)">转款百度</el-button>
            <el-button v-if="scope.row.orderStatus === '4'" type="text" class="pos-r" @click="voucherView(scope.row.id)">查看凭证</el-button>
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
    <financeDialog
      title="请确认是否已经将提现款项转给该账户"
      tips="请提供金额以及转款凭证（最多上传3张）。"
      :pice="pice"
      :img-dialog="dialogVisibleImg"
      :confirm-dialog="dialogVisible"
      :pic-list="dialogVisibleImgSrc"
      @imgClose="imgClose"
      @confirmClose="confirmClose"
      @dialogOk="dialogOk"
    />
  </div>
</template>

<script>
import financeDialog from '@/components/financeDialog/index'
export default {
  name: 'FinanceManage',
  components: {
    financeDialog
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      dialogVisible: false,
      exportLoading: false,
      pice: '',
      paymentId: '',
      settleType: '',
      paymentVoucher: [],
      fromData: {
        orderNo: '',
        orderStatus: '',
        shopSettleStatus: '',
        supplySettleStatus: ''
      },
      startTime: '',
      endTime: '',
      tableData: [],
      action: '',
      fileList: [],
      uploadExtendData: {},
      dialogImageUrl: '',
      dialogImg: false,
      dialogVisibleImgSrc: [],
      dialogVisibleImg: false
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
      this.currentPage = 1
      this.getList()
    },
    payment(id, type) {
      this.paymentVoucher = []
      this.fileList = []
      this.paymentId = id
      this.settleType = type
      this.dialogVisible = true
      this.$apis.FINANCE.getSettleInfo({ id }).then(res => {
        if (res.code === 0) {
          this.pice = res.data.supplySettlePrice
        } else {
          this.$message.error(res.message)
        }
      })
    },
    voucherView(id) {
      this.$apis.FINANCE.getSettleInfo({ id }).then(res => {
        this.dialogVisibleImg = true
        if (res.code === 0) {
          this.dialogVisibleImgSrc = res.data.paymentVoucher.split(',') || []
        } else {
          this.$message.error(res.message)
        }
      })
    },
    onExport() {
      const that = this
      that.exportLoading = true
      const obj = that.handleData()
      that.$apis.FINANCE.financeExport(obj).then(res => {
        that.exportLoading = false
        const utf8decoder = new TextDecoder()
        const u8arr = new Uint8Array(res)
        // 将二进制数据转为字符串
        const temp = utf8decoder.decode(u8arr)
        if (!temp) {
          this.$message.error(temp.message)
        } else {
          const link = document.createElement('a')
          const blob = new Blob([res], { type: 'application/vnd.ms-excel' })
          link.style.display = 'none'
          link.href = URL.createObjectURL(blob)
          link.download = '财务管理订单列表' // 下载的文件名
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
        }
      }).catch(err => {
        that.exportLoading = false
        that.$message.error(err.message)
      })
    },
    handleData(val) {
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize,
        startTime: this.startTime,
        endTime: this.endTime,
        orderStatus: this.fromData.orderStatus,
        orderNo: this.fromData.orderNo,
        custNamshopSettleStatuse: this.fromData.shopSettleStatus,
        supplySettleStatus: this.fromData.supplySettleStatus
      }
      return obj
    },
    getList() {
      const obj = this.handleData()
      this.$apis.FINANCE.financeList(obj).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.pageNo = res.page.pageNo
          this.convertTableData()
        }
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
            const rdata = {
              ...item,
              ...newData
            }
            rdata.combineNum = item.goodsDetailVOList.length
            arr.push(rdata)
            if (i === 0) {
              rowspan.push(item.goodsDetailVOList.length)
            } else {
              rowspan.push(0)
            }
          }
        } else {
          arr.push(item)
          rowspan.push(0)
        }
      })
      this.tableData = arr
      this.rowspan = rowspan
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 8, 9, 10, 11, 12, 13, 14, 15, 16].includes(columnIndex)) {
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
    orderInfoTo(id, type) {
      this.$router.push(`/finance/finance-info?id=${id}&type=${type}`)
    },
    dialogOk(billVoucher) {
      const obj = {
        id: this.paymentId,
        settleType: this.settleType,
        billVoucher
      }
      console.log(obj)
      /* this.$apis.FINANCE.financeSettle(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      }) */
    },
    imgClose() {
      this.dialogVisibleImg = false
    },
    confirmClose() {
      this.dialogVisible = false
      this.fileList = []
      this.confirmPicList = []
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.finance-manage{
  padding: 20px;
  .tips{
    color: #ccc;
  }
  .el-dialog__wrapper{
    .el-input{
      width: 200px;
    }
  }
  ::v-deep .el-image{
    width: 100%;
    height: 100%;
  }
}
</style>
