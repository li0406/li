<template>
  <div class="system-member">
    <tableSearch title="成员管理">
      <el-form slot="from" :inline="true">
        <el-form-item label="用户名/姓名">
          <el-input v-model="name" clearable placeholder="用户名/姓名" />
        </el-form-item>
        <el-form-item label="所属部门">
          <el-select v-model="team" clearable placeholder="请选择">
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
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button @click="add">添加</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        border
        style="width: 100%"
      >
        <el-table-column prop="orderNo" label="成员账号" align="center" />
        <el-table-column prop="custName" label="姓名" align="center" />
        <el-table-column prop="goodsName" label="邮箱地址" align="center" />
        <el-table-column prop="salePrice" label="所属部门" align="center" />
        <el-table-column prop="saleNum" label="添加时间" align="center" />
        <el-table-column prop="purchasePrice" label="最后登录" align="center" />
        <el-table-column prop="supplyPrice" label="是否启用" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="orderInfoTo(scope.row.id)">权限设置</el-button>
            <el-button type="text" @click="orderInfoTo(scope.row.id)">编辑</el-button>
            <el-button type="text" @click="orderInfoTo(scope.row.id)">删除</el-button>
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
  </div>
</template>

<script>
export default {
  name: 'SystemMember',
  data() {
    return {
      pageNo: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      tableData: [],
      name: '',
      team: ''
    }
  },
  created() {},
  methods: {
    onSearch() {},
    orderInfoTo() {},
    add() {},
    handleData() {
      const obj = {
        pageNo: this.pageNo,
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
          that.pageNo = res.page.pageNo
        } else {
          that.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err.message)
        that.tableLoading = false
        // that.$message.error(err.message)
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
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.system-member{
  padding: 20px;
}
</style>
