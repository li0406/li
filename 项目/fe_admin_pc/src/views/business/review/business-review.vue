<template>
  <div class="business-review warp">
    <editPage title="商家审核">
      <template slot="btn">
        <el-button v-if="bul" type="primary" @click="check(3)">审核通过</el-button>
        <el-button v-if="bul" type="danger" @click="reconfirm()">审核不通过</el-button>
        <el-button @click="tobusinesslist">关闭</el-button>
      </template>
      <el-row class="mt">
        <el-col :span="24">
          <el-col :span="24">
            <h3 class="mb"><i class="el-icon-s-operation" /> 基本信息</h3>
          </el-col>
          <el-col :span="24">
            <el-table
              :data="basicTableData"
              border
              style="width: 90%"
            >
              <el-table-column
                prop="companyId"
                label="商家ID"
                align="center"
              />
              <el-table-column
                prop="shopName"
                label="商家名称"
                align="center"
              />
              <el-table-column
                prop="cityName"
                label="所属城市"
                align="center"
              />
              <el-table-column
                prop="createDate"
                label="会员创建时间"
                align="center"
              />
              <el-table-column
                prop="accountAmount"
                label="账户余额"
                align="center"
              />
            </el-table>
          </el-col>
        </el-col>
      </el-row>
      <!-- <el-row class="mt">
        <el-col :span="24">
          <el-col :span="24">
            <h3 class="mb"><i class="el-icon-s-check" />收款信息</h3>
          </el-col>
          <el-col :span="24">
            <el-table
              :data="collectionTableData"
              border
              style="width: 90%"
            >
              <el-table-column
                prop="collectionname"
                label="收款账号名"
                align="center"
              />
              <el-table-column
                prop="platform"
                label="银行/平台"
                align="center"
              />
              <el-table-column
                prop="openaccount"
                label="开户行"
                align="center"
              />
              <el-table-column
                prop="cardnumber"
                label="账号/卡号"
                align="center"
              />
            </el-table>
          </el-col>
        </el-col>
      </el-row> -->
    </editPage>
    <el-dialog
      center
      title="请填写审核不通过的原因"
      :visible.sync="dialogVisible"
      width="30%"
      :before-close="handleClose"
    >
      <el-row>
        <el-col :span="24">
          <el-input v-model="applyRefuseReason" placeholder="请填写审核不通过的原因" />
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="check(2)">确认并不通过</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'BusinessReview',
  data() {
    return {
      bul: '',
      basicTableData: [],
      collectionTableData: [],
      dialogVisible: false,
      applyRefuseReason: ''
    }
  },
  created() {
    this.bul = this.$route.query.bul
    this.detail()
  },
  methods: {
    tobusinesslist() {
      this.$router.push({
        path: '/business/business-manage'
      })
    },
    reconfirm() {
      this.dialogVisible = true
    },
    handleClose() {
      this.dialogVisible = false
    },
    detail() {
      const id = Number(this.$route.query.id) || ''
      const data = {
        id
      }
      this.$apis.BUSINESS.detail(data).then(res => {
        if (res.code === 0) {
          this.basicTableData = [res.data]
        } else {
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    check(bul) {
      if (bul === 2 && this.applyRefuseReason === '') { //  不通过
        this.$message.error('请输入审核不通过原因')
        return
      }
      const data = {
        id: this.basicTableData[0].id,
        applyStatus: bul,
        applyRefuseReason: this.applyRefuseReason
      }
      this.$apis.BUSINESS.check(data).then(res => {
        if (res.code === 0) {
          this.$message({
            message: '操作成功',
            type: 'success'
          })
          this.tobusinesslist()
        } else {
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.mt{
    margin-top: 40px;
}
.mb{
    margin-bottom: 20px;
}
</style>
