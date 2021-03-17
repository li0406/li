<template>
  <div class="payment-detailed">
    <tableSearch title="支付明细">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="订单编号" prop="orderNo">
          <el-input v-model="fromData.orderNo" clearable placeholder="订单编号" />
        </el-form-item>
        <el-form-item label="流水号" prop="payFlowNo">
          <el-input v-model="fromData.payFlowNo" clearable placeholder="流水号" />
        </el-form-item>
        <el-form-item label="支付类型" prop="businessType">
          <el-select v-model="fromData.businessType" placeholder="交易状态">
            <el-option label="百度货款" value="1" />
            <el-option label="装企购买" value="2" />
            <el-option label="装企提现" value="3" />
            <el-option label="用户购买" value="4" />
            <el-option label="百度退回" value="5" />
            <el-option label="装企退回" value="6" />
          </el-select>
        </el-form-item>
        <el-form-item label="财务科目" prop="transType">
          <el-select v-model="fromData.transType" placeholder="请选择">
            <el-option label="收入" value="1" />
            <el-option label="支出" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="收款账户" prop="holderName">
          <el-input v-model="fromData.holderName" clearable placeholder="收款账户" />
        </el-form-item>
        <el-form-item label="对帐状态" prop="verifyStatus">
          <el-select v-model="fromData.verifyStatus" clearable placeholder="请选择">
            <el-option label="已对帐" value="1" />
            <el-option label="未对帐" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="开票状态" prop="billStatus">
          <el-select v-model="fromData.billStatus" clearable placeholder="请选择">
            <el-option label="已开票" value="1" />
            <el-option label="未开票" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="支付时间" prop="date">
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
        <el-form-item label="对帐时间" prop="verifyDate">
          <el-date-picker
            v-model="fromData.verifyDate"
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
        style="width: 100%"
      >
        <el-table-column prop="payTime" label="支付时间" align="center" />
        <el-table-column prop="payFlowNo" label="流水号" align="center" />
        <el-table-column prop="orderNo" label="订单编号" align="center" />
        <el-table-column prop="orderPrice" label="订单金额" align="center" />
        <el-table-column prop="holderName" label="账户" align="center" />
        <el-table-column prop="cardNo" label="账号" align="center" />
        <el-table-column prop="bankName" label="开户行" align="center" />
        <el-table-column prop="businessType" label="支付类型" align="center">
          <template slot-scope="scope">
            {{ scope.row.businessType | businessType }}
          </template>
        </el-table-column>
        <el-table-column prop="transType" label="财务科目" align="center">
          <template slot-scope="scope">
            {{ scope.row.transType | transType }}
          </template>
        </el-table-column>
        <el-table-column label="对帐状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.verifyStatus | verifyStatus }}
          </template>
        </el-table-column>
        <el-table-column label="开票状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.billStatus | billStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="verifyTime" label="对帐时间" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="verify(scope.row.id)">确认对帐</el-button>
            <el-button type="text" @click="queryVoucher(scope.row.id)">查看凭证</el-button>
            <el-button type="text" @click="confirmOpenBill(scope.row.id)">开具发票</el-button>
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
      title="请确认是否已经为该用户开具发票"
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
  name: 'PaymentDetailed',
  components: {
    financeDialog
  },
  filters: {
    transType(value) {
      switch (value) {
        case '1':
          return '收入'
        case '2':
          return '支出'
        default:
          return '----'
      }
    },
    verifyStatus(value) {
      switch (value) {
        case '1':
          return '已对账'
        case '0':
          return '未对账'
        default:
          return '----'
      }
    },
    billStatus(value) {
      switch (value) {
        case '1':
          return '已开发票'
        case '0':
          return '未开发票'
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
      dialogVisible: false,
      exportLoading: false,
      billId: '', // 发票弹窗id
      pice: '----', // 发票弹窗金额
      billInfo: {}, // 发票弹窗数据
      confirmPicList: [], // 发票弹窗图片列表
      fromData: {
        billStatus: '',
        businessType: '',
        holderName: '',
        orderNo: '',
        payFlowNo: '',
        transType: '',
        verifyStatus: ''
      },
      startTime: '',
      endTime: '',
      verifyStartTime: '',
      verifyEndTime: '',
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
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    },
    'fromData.verifyDate': {
      handler(val) {
        this.verifyStartTime = !val ? '' : val[0] + ' 00:00:00'
        this.verifyEndTime = !val ? '' : val[1] + ' 23:59:59'
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
    onExport() {
      const that = this
      that.exportLoading = true
      const obj = that.handleData()
      that.$apis.FINANCE.transExport(obj).then(res => {
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
          link.download = '平台支付明细列表' // 下载的文件名
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
        verifyStartTime: this.verifyStartTime,
        verifyEndTime: this.verifyEndTime,
        billStatus: this.fromData.billStatus,
        businessType: this.fromData.businessType,
        holderName: this.fromData.holderName,
        orderNo: this.fromData.orderNo,
        payFlowNo: this.fromData.payFlowNo,
        transType: this.fromData.transType,
        verifyStatus: this.fromData.verifyStatus
      }
      return obj
    },
    getList() {
      const obj = this.handleData()
      this.$apis.FINANCE.transList(obj).then(res => {
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
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    verify(id) {
      this.$confirm('确认对账？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        /* this.$apis.FINANCE.verify({ ids: [id] }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
          } else {
            this.$message.error(res.message)
          }
        }) */
      }).catch(() => {})
    },
    queryVoucher(id) {
      this.$apis.FINANCE.queryVoucher({ id }).then(res => {
        this.dialogVisibleImg = true
        if (res.code === 0) {
          if (res.data.paymentVoucher) {
            this.dialogVisibleImgSrc = res.data.paymentVoucher.split(',') || []
          } else {
            this.$message.warning('暂无数据')
            this.dialogVisibleImg = false
          }
        } else {
          this.$message.error(res.message)
        }
      })
    },
    confirmOpenBill(id) {
      this.dialogVisible = true
      this.billId = id
      this.$apis.FINANCE.openBill({ id }).then(res => {
        if (res.code === 0) {
          this.billInfo = res.data
          this.pice = res.data.orderPrice
        } else {
          this.$message.error(res.message)
        }
      })
    },
    dialogOk(billVoucher) {
      const obj = {
        id: this.dialogId,
        billVoucher
      }
      this.$apis.FINANCE.confirmOpenBill(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      })
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
.payment-detailed{
  padding: 20px;
  .tips{
    color: #ccc;
  }
  .el-dialog__wrapper{
    .el-input{
      width: 200px;
    }
  }
}
</style>
