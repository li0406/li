<template>
  <div class="withdrawal">
    <tableSearch title="提现申请">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="会员ID" prop="custId">
          <el-input v-model="fromData.custId" clearable placeholder="会员ID" />
        </el-form-item>
        <el-form-item label="会员名称" prop="custName">
          <el-input v-model="fromData.custName" clearable placeholder="会员名称" />
        </el-form-item>
        <el-form-item label="所属城市" prop="cityName">
          <el-input v-model="fromData.cityName" clearable placeholder="所属城市" />
        </el-form-item>
        <!-- <el-form-item label="所属城市" prop="cityId">
          <el-select v-model="fromData.cityId" clearable placeholder="请选择">
            <el-option label="已上架" value="1" />
            <el-option label="已下架" value="2" />
            <el-option label="无效" value="3" />
          </el-select>
        </el-form-item> -->
        <el-form-item label="状态" prop="status">
          <el-select v-model="fromData.status" clearable placeholder="请选择">
            <el-option label="待打款" value="1" />
            <el-option label="已打款" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="申请时间" prop="date">
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
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        :data="tableData"
        border
        style="width: 100%"
      >
        <el-table-column prop="custId" label="会员ID" align="center" />
        <el-table-column prop="custName" label="会员名称" align="center" />
        <el-table-column prop="cityName" label="城市" align="center" />
        <el-table-column prop="accountBalance" label="账户余额" align="center" />
        <el-table-column prop="holderName" label="收款账户" align="center" />
        <el-table-column prop="cardNo" label="收款账号" align="center" />
        <el-table-column prop="bankName" label="开户行" align="center" />
        <el-table-column prop="chargeBalance" label="提现金额" align="center" />
        <el-table-column prop="withdrawDate" label="申请时间" align="center" />
        <el-table-column prop="checkStatus" label="状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.checkStatus | checkStatus }}
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="confirmPayment(scope.row.id, scope.row.chargeBalance)">确认打款</el-button>
            <el-button type="text" @click="voucherView(scope.row.id)">查看凭证</el-button>
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
  name: 'Withdrawal',
  components: {
    financeDialog
  },
  filters: {
    checkStatus(val) {
      switch (val) {
        case '1':
          return '审核中'
        case '2':
          return '审核通过'
        case '3':
          return '审核拒绝'
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
      pice: '',
      confirmPicList: [],
      dialogId: '',
      fromData: {
        cityId: '',
        custId: '',
        custName: '',
        status: ''
      },
      startTime: '',
      endTime: '',
      tableData: [1],
      action: '',
      fileList: [],
      uploadExtendData: {},
      dialogImageUrl: '',
      dialogImg: false,
      dialogVisibleImg: false,
      dialogVisibleImgSrc: []
    }
  },
  watch: {
    'fromData.date': {
      handler(val) {
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    // this.getList()
  },
  methods: {
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    onSearch() {
      this.currentPage = 1
      this.getList()
    },
    imgClose() {
      this.dialogVisibleImg = false
    },
    confirmClose() {
      this.dialogVisible = false
      this.fileList = []
      this.confirmPicList = []
    },
    handleData(val) {
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize,
        startTime: this.startTime,
        endTime: this.endTime,
        cityId: this.fromData.cityId,
        custId: this.fromData.custId,
        custName: this.fromData.custName,
        status: this.fromData.status
      }
      return obj
    },
    getList() {
      const obj = this.handleData()
      this.$apis.FINANCE.withdrawList(obj).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.pageNo = res.page.pageNo
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
    },
    // 确认打款
    confirmPayment(id, pice) {
      this.dialogVisible = true
      this.dialogId = id
      this.pice = pice
    },
    // 查看凭证
    voucherView(id) {
      this.dialogVisibleImg = true
      this.$apis.FINANCE.viewPayment({ ids: [id] }).then(res => {
        if (res.code === 0) {
          if (res.data && res.data.paymentVoucher) {
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
    dialogOk(billVoucher) {
      const obj = {
        id: this.dialogId,
        billVoucher
      }
      this.$apis.FINANCE.confirmWithdraw(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.withdrawal{
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
