<template>
  <div class="withdrawal-management">
    <el-card class="mb20 item-top">
      <div class="fl item-l">
        <h3>我的钱包</h3>
        <h5>我的可用余额</h5>
        <div class="bottom">
          <div class="bottom-num">
            <span class="num">{{ accountBalance }}</span>
          </div>
          <div class="bottom-p">
            <el-button type="primary" size="small" @click="withdraw">申请提现</el-button>
            <p>实际到款预计需要7-10个工作日，节假日顺延</p>
          </div>

        </div>
      </div>
      <div class="fl item-r">
        <h5>全部余额</h5>
        <span class="num">{{ totalBalance }}</span>
        <h5>待结算金额</h5>
        <span class="num">{{ settleBalance }}</span>
      </div>
    </el-card>
    <el-card>
      <h2 class="table-title mb20">提现记录</h2>
      <el-table :data="tableData">
        <el-table-column prop="chargeBalance" label="提现金额" align="center" />
        <el-table-column prop="withdrawDate" label="提现时间" align="center" />
        <el-table-column prop="leaveBalance" label="所剩余额" align="center" />
        <el-table-column prop="checkStatus" label="提现状态" align="center">
          <template slot-scope="scope">
            {{ scope.row.checkStatus | checkStatus }}
          </template>
        </el-table-column>
        <el-table-column prop="name" label="操作" align="center">
          <template slot-scope="scope">
            <el-button v-if="scope.row.checkStatus === '4' || scope.row.checkStatus === '2'" type="text" @click="credentialsView(scope.row.paymentVoucher)">查看凭证</el-button>
            <span v-else>----</span>
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
    </el-card>
    <el-dialog title="申请提现" :close-on-click-modal="false" :visible.sync="dialogFormVisible" width="350px">
      <el-form ref="form" :model="form" :rules="rules" label-position="top" label-width="100px">
        <el-form-item label="提现金额" prop="withdrawPrice">
          <el-input v-model.number="form.withdrawPrice" autocomplete="off" />
        </el-form-item>
        <el-form-item class="mb0">
          <div class="flex spa-bet">
            <span>可提现：{{ accountBalance }}</span>
            <el-button type="text" @click="allWithdrawPrice">全部提现</el-button>
          </div>
        </el-form-item>
        <el-form-item label="选择提现银行" prop="shopBankId">
          <el-select v-model="form.shopBankId" placeholder="选择提现银行">
            <el-option v-for="item in shopBankVOList" :key="item.id" :label="item.bankName" :value="item.id" />
          </el-select>
          <el-button class="back-add fr" type="text" @click="addBank">立即添加</el-button>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="submit">提交</el-button>
      </div>
    </el-dialog>
    <el-dialog title="查看凭证" :visible.sync="dialogImgVisible">
      <el-row :gutter="20">
        <el-col v-for="item in paymentVoucher" :key="item" :span="8">
          <img :src="item" alt="" width="100%" max-height="300px">
        </el-col>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogImgVisible = false">知道了</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'WithdrawalManagement',
  filters: {
    checkStatus(value) {
      switch (value) {
        case '1':
          return '审核中'
        case '2':
          return '审核通过'
        case '3':
          return '审核拒绝'
        case '4':
          return '已提现成功'
        default:
          return '----'
      }
    }
  },
  data() {
    const validateNum = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请输入提现金额'))
      }
      setTimeout(() => {
        if (!Number.isInteger(value)) {
          callback(new Error('请输入数字值'))
        } else {
          if (value > this.accountBalance) {
            callback(new Error('提现金额大于可提现金额'))
          } else {
            callback()
          }
        }
      }, 500)
    }
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      formLabelWidth: 200,
      dialogFormVisible: false,
      dialogImgVisible: false,
      shopBankVOList: [],
      accountBalance: '',
      settleBalance: '',
      totalBalance: '',
      form: {
        id: '',
        withdrawPrice: '',
        shopBankId: ''
      },
      rules: {
        withdrawPrice: [
          { validator: validateNum, trigger: 'blur' }
        ],
        shopBankId: [
          { required: true, message: '请选择银行', trigger: 'change' }
        ]
      },
      tableData: []
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      /* const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize
      } */
      this.$apis.TRANSACTION.getShopWithdrawList().then((res) => {
        if (res.code === 0) {
          this.tableData = res.data.withdrawRecordVOList || []
          this.form.id = res.data.id
          this.accountBalance = res.data.accountBalance
          this.settleBalance = res.data.settleBalance
          this.totalBalance = res.data.totalBalance
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
    withdraw() {
      this.dialogFormVisible = true
      this.$apis.TRANSACTION.getUserBalanceDetail().then((res) => {
        this.accountBalance = res.data.accountBalance
        this.shopBankVOList = res.data.shopBankVOList
      })
    },
    allWithdrawPrice() {
      this.form.withdrawPrice = this.accountBalance
    },
    submit() {
      this.$refs['form'].validate((valid) => {
        if (valid) {
          console.log(this.form)
          this.$apis.TRANSACTION.withdrawApply(this.form).then(res => {
            if (res.code === 0) {
              this.$message.success(res.message)
              this.dialogFormVisible = false
              this.getList()
            } else {
              this.$message.error(res.message)
            }
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    addBank() {
      this.$router.push('/transaction-management/bank-card-management')
    },
    credentialsView(val) {
      this.paymentVoucher = val.split(',')
      this.dialogImgVisible = true
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.withdrawal-management{
  .item-top{
    .item-l{
      float: left;
      width: 500px;
      .num{
        line-height: 60px;
      }
      .bottom{
        margin: 0 0 10px 0;
        overflow: hidden;
        .bottom-num{
          float: left;
        }
        .bottom-p{
          float: left;
          margin: 15px 0 0 40px;
        }
      }
      p{
        font-size: 12px;
        color: #999;
        line-height: 16px;
        margin-top: 10px;
      }
    }
    h3{
        color: #666;
        font-size: 26px;
        font-weight: normal;
        line-height: 30px;
        margin-bottom: 10px;
      }
      h5{
        font-size: 14px;
        color: #999;
        line-height: 36px;
      }
      .num{
        font-weight: 600;
        color: #000;
        font-size: 36px;
        line-height: 40px;
      }
  }
  .back-add{
    margin-top: -45px;
  }
}
</style>
