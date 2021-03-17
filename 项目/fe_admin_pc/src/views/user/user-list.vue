<template>
  <div class="user-list">
    <tableSearch title="用户列表">
      <el-form slot="from" :inline="true">
        <el-form-item label="用户账号">
          <el-input v-model="name" clearable placeholder="用户账号" />
        </el-form-item>
        <el-form-item label="用户昵称">
          <el-input v-model="name" clearable placeholder="用户昵称" />
        </el-form-item>
        <el-form-item label="注册时间">
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
          <el-button @click="add">添加</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        border
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column prop="orderNo" label="用户ID" align="center" />
        <el-table-column prop="custName" label="用户账号" align="center" />
        <el-table-column prop="goodsName" label="用户昵称" align="center" />
        <el-table-column prop="salePrice" label="认证类型" align="center" />
        <el-table-column prop="saleNum" label="注册时间" align="center" />
        <el-table-column prop="saleNum" label="最近一次登录时间" align="center" />
        <el-table-column prop="saleNum" label="账户启用状态" align="center">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.id"
              active-color="#13ce66"
              inactive-color="#ff4949"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center" width="180">
          <template slot-scope="scope">
            <el-button type="text" @click="orderInfoTo(scope.row.id)">查看</el-button>
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
  name: 'UserList',
  data() {
    return {
      pageNo: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      tableData: [],
      multipleSelection: [],
      date: '',
      name: '',
      team: ''
    }
  },
  watch: {},
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
    },
    handleSelectionChange(val) {
      console.log(val)
      this.multipleSelection = val
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.user-list{
  padding: 20px;
}
</style>
