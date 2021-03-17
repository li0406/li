<template>
  <div class="bank-card-management">
    <el-card>
      <div class="clearfix mb20">
        <h3 class="fl">银行卡管理</h3>
        <div class="fr">
          <el-button type="primary" @click="dialogFormVisible = true">添加银行卡</el-button>
        </div>
      </div>
      <el-table :data="tableData" border style="width: 100%">
        <el-table-column prop="holderName" label="姓名" align="center" />
        <el-table-column prop="bankName" label="开户行（精确至支行）" align="center" />
        <el-table-column prop="cardNo" label="银行卡号" align="center" />
        <el-table-column prop="cellPhone" label="银行预留手机号" align="center" />
        <el-table-column prop="idCard" label="身份证号" align="center" />
        <el-table-column label="操作" width="180" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row)">编辑</el-button>
            <el-button type="text" @click="delete(scope.row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog title="添加银行卡" :visible.sync="dialogFormVisible" :close-on-click-modal="false" width="400px">
      <el-form :model="form" label-width="100">
        <el-form-item label="姓名">
          <el-input v-model="form.holderName" autocomplete="off" maxlength="20" placeholder="请输入" />
        </el-form-item>
        <el-form-item label="开户行（精确至支行）">
          <el-input v-model="form.bankName" autocomplete="off" maxlength="50" placeholder="请输入" />
        </el-form-item>
        <el-form-item label="银行卡号">
          <el-input v-model="form.cardNo" autocomplete="off" maxlength="20" placeholder="请输入" />
        </el-form-item>
        <el-form-item label="银行预留手机号">
          <el-input v-model="form.cellPhone" autocomplete="off" maxlength="11" placeholder="请输入" />
        </el-form-item>
        <el-form-item label="身份证号">
          <el-input v-model="form.idCard" autocomplete="off" maxlength="18" placeholder="请输入" />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="editOk">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'BankCardManagement',
  data() {
    return {
      dialogFormVisible: false,
      tableData: [],
      dialogData: {},
      form: {
        id: '',
        holderName: '',
        bankName: '',
        cardNo: '',
        cellPhone: '',
        idCard: ''
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.$apis.TRANSACTION.shopBankList().then((res) => {
        if (res.code === 0) {
          console.log(res)
          this.tableData = res.data || []
        } else {
          this.$message.error(res.message)
        }
      })
    },
    editOk() {
      console.log(this.form)
      this.$apis.TRANSACTION.addShopBank(this.form).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      })
    },
    edit(data) {
      this.dialogFormVisible = true
      this.form = JSON.parse(JSON.stringify(data))
    },
    delete(id) {
      this.$confirm('确认删除银行卡？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.TRANSACTION.deleteShopBank({ ids: [id] }).then(res => {
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

</style>
