<template>
  <div class="goods-manage warp">
    <tableSearch title="商家管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="所属城市" prop="cityName">
          <el-input v-model="fromData.cityName" clearable placeholder="请输入" />
        </el-form-item>
        <el-form-item label="会员ID" prop="companyId">
          <el-input v-model="fromData.companyId" clearable placeholder="请输入" />
        </el-form-item>
        <el-form-item label="会员名称" prop="shopName">
          <el-input v-model="fromData.shopName" clearable placeholder="请输入" />
        </el-form-item>
        <el-form-item label="添加时间" prop="date">
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
        <el-form-item label="状态" prop="applyStatus">
          <el-select v-model="fromData.applyStatus" clearable placeholder="状态">
            <el-option v-for="item of applyStatusList" :key="item.status" :label="item.name" :value="item.status" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="list">搜索</el-button>
          <el-button @click="onReset">重置</el-button>
          <el-button type="success" @click="addmerchant = true">添加商家</el-button>
        </el-form-item>
      </el-form>
      <el-table v-loading="loading" :data="tableData" border style="width: 100%">
        <el-table-column prop="companyId" label="会员ID" align="center" />
        <el-table-column prop="shopName" label="会员名称" align="center" />
        <el-table-column prop="cityName" label="城市" align="center" />
        <el-table-column prop="accountAmount" label="账户余额" align="center" />
        <el-table-column prop="vipStatus" label="会员状态" align="center">
          <template slot-scope="scope">
            <div v-if="scope.row.vipStatus==='1'">有效</div>
            <div v-if="scope.row.vipStatus==='2'">无效</div>
          </template>
        </el-table-column>
        <el-table-column prop="createDate" label="添加时间" align="center" />
        <el-table-column prop="applyStatus" label="状态" align="center">
          <template slot-scope="scope">
            <div v-if="scope.row.applyStatus=='1'">待审核</div>
            <div v-if="scope.row.applyStatus=='3'" class="green">通过</div>
            <div v-if="scope.row.applyStatus=='0'" class="red">未申请</div>
            <div v-if="scope.row.applyStatus=='2'">不通过</div>
            <div v-if="scope.row.applyStatus=='4'">停用</div>
          </template>
        </el-table-column>
        <el-table-column prop="applyStatus" label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="todetail(0,scope.row)">详情</el-button>
            <el-button v-if="scope.row.applyStatus=='1'||(scope.row.applyStatus=='4')" type="text" @click="todetail(1,scope.row)">审核</el-button>
            <el-button v-if="(scope.row.applyStatus=='2')||(scope.row.applyStatus=='0')" type="text" @click="todetail(1,scope.row)">重新审核</el-button>
            <el-button v-if="scope.row.applyStatus=='3'" type="text" @click="checkdialog(scope.row)">停用</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-dialog width="20%" center title="请输入需要添加的会员ID" :visible.sync="addmerchant">
        <el-form :model="addform">
          <el-form-item label="会员ID" label-width="55px">
            <el-input v-model="addform.id" maxlength="8" placeholder="请输入需要添加的会员ID" class="memberid" />
            <el-button type="primary" @click="queryByUser">查询</el-button>
          </el-form-item>
          <el-form-item label="公司简称">
            {{ businessmsg.jc }}
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click="add">确 认</el-button>
          <el-button @click="addmerchant = false">取 消</el-button>
        </div>
      </el-dialog>
      <el-dialog
        top="30vh"
        center
        title="是否确认为该公司停用该功能？"
        :visible.sync="outofservice"
        width="20%"
      >
        <span>确认停用后，该装企将不用拥有该功能的使用权限。</span>
        <span slot="footer" class="dialog-footer">
          <el-button type="primary" @click="check">确 认</el-button>
          <el-button @click="outofservice = false">取 消</el-button>
        </span>
      </el-dialog>
      <el-pagination
        v-if="total>0"
        class="mt20 text-center"
        :page-sizes="[10, 20, 50, 100]"
        :current-page="pageNo"
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
  name: 'GoodsManage',
  data() {
    return {
      applyStatusList: [
        { name: '未申请', status: '0' },
        { name: '待审核', status: '1' },
        { name: '不通过', status: '2' },
        { name: '通过', status: '3' },
        { name: '停用', status: '4' }
      ],
      checkdata: {
        id: '',
        applyStatus: '',
        applyRefuseReason: ''
      },
      outofservice: false,
      addmerchant: false,
      loading: false,
      pageNo: 1,
      pageSize: 20,
      total: 0,
      fromData: {
        cityName: '',
        companyId: '',
        shopName: '',
        date: '',
        applyStatus: ''
      },
      addform: {
        id: ''
      },
      businessmsg: {
        id: '',
        type: '',
        on: '',
        qc: '',
        jc: ''
      },
      tableData: []
    }
  },
  computed: {},
  watch: {
    'fromData.date': {
      handler(val) {
        this.createTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.list()
  },
  mounted() {},
  methods: {
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    list() {
      this.loading = true
      const data = {
        pageNo: this.pageNo,
        pageSize: this.pageSize,
        cityName: this.fromData.cityName,
        companyId: Number(this.fromData.companyId) || '',
        shopName: this.fromData.shopName,
        startTime: this.startTime,
        endTime: this.endTime,
        applyStatus: this.fromData.applyStatus
      }
      this.$apis.BUSINESS.list(data).then(res => {
        this.loading = false
        if (res.code === 0) {
          this.tableData = res.data
          this.pageNo = res.page.pageNo
          this.pageSize = res.page.pageSize
          this.total = res.page.total
        } else {
          this.$message.error(res.message)
          console.log(res)
        }
      }).catch(err => {
        this.loading = false
        console.log(err)
      })
    },
    add() {
      const custId = Number(this.businessmsg.id) || ''
      if (custId === '') {
        this.$message.error('请查询商家后重试')
        return
      }
      const data = {
        custId
      }
      this.$apis.BUSINESS.add(data).then(res => {
        if (res.code === 0) {
          this.$message({
            message: '成功添加商家',
            type: 'success'
          })
          this.addmerchant = false
          this.list()
        } else {
          this.$message.error(res.message)
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    checkdialog(data) {
      this.outofservice = true
      this.checkdata = {
        id: data.id,
        applyStatus: 4,
        applyRefuseReason: ''
      }
    },
    check() {
      this.$apis.BUSINESS.check(this.checkdata).then(res => {
        if (res.code === 0) {
          this.$message({
            message: '停用成功',
            type: 'success'
          })
          this.outofservice = false
          this.list()
        } else {
          this.$$message.error(res.message)
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    queryByUser() {
      const id = Number(this.addform.id) || ''
      const data = {
        id
      }
      this.$apis.BUSINESS.queryByUser(data).then(res => {
        if (res.code === 0) {
          this.businessmsg = res.data
        } else {
          this.$$message.error(res.message)
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    handleSizeChange(val) {
      this.pageSize = val
      this.pageNo = 1
      this.list()
    },
    handleCurrentChange(val) {
      this.pageNo = val
      this.list()
    },
    todetail(bul, item) {
      console.log(item)
      this.$router.push({
        path: 'business-review',
        query: {
          bul,
          id: item.id
        }
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.goods-manage{
  height: 100%;
  // background: #f8f8f8;
  .memberid{
    width: 70%;
    margin-right: 10px;
  }
}
</style>
