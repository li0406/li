<template>
  <div class="bdorder-manage">
    <tableSearch title="百度接单后台">
      <el-form slot="from" :inline="true">
        <el-form-item label="订单编号">
          <el-input v-model="orderNo" clearable placeholder="订单编号" />
        </el-form-item>
        <el-form-item label="是否发货" prop="orderNo">
          <el-select v-model="orderNo" clearable placeholder="商品状态">
            <el-option label="已上架" value="1" />
            <el-option label="已下架" value="2" />
            <el-option label="无效" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="交易状态">
          <el-select v-model="orderStatus" clearable placeholder="交易状态">
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
        <el-form-item label="打款时间">
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
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        :span-method="arraySpanMethod"
        border
        style="width: 100%"
      >
        <el-table-column prop="orderNo" label="订单编号" align="center" />
        <el-table-column prop="custName" label="用户名称" align="center" />
        <el-table-column prop="goodsName" label="商品" align="center" />
        <el-table-column prop="type_name" label="出货价" align="center" />
        <el-table-column prop="saleNum" label="数量" align="center" />
        <el-table-column prop="purchasePrice" label="小计" align="center" />
        <el-table-column prop="supplyPrice" label="下单时间" align="center" />
        <el-table-column prop="salePrice" label="发货状态" align="center" />
        <el-table-column prop="orderPrice" label="物理公司" align="center" />
        <el-table-column prop="payTime" label="物流单号" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="bdorderInfoTo(scope.row.id)">详情</el-button>
            <el-button type="text" @click="bdorderInfoTo(scope.row.id)">查看凭证</el-button>
            <el-button type="text" @click="bdorderInfoTo(scope.row.id)">填写物流信息</el-button>
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
  name: 'BdorderManage',
  data() {
    return {
      pageNo: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      exportLoading: false,
      orderNo: '',
      username: '',
      orderStatus: '',
      date: '',
      startTime: '',
      endTime: '',
      tableData: [],
      exportData: []
    }
  },
  computed: {},
  watch: {
    date: {
      handler(val) {
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    onSearch() {
      this.pageNo= 1
      this.getList()
    },
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    handleData() {
      const obj = {
        pageNo: this.pageNo,
        pageSize: this.pageSize,
        startTime: this.startTime,
        endTime: this.endTime,
        orderStatus: this.orderStatus,
        orderNo: this.orderNo,
        username: this.username
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
          that.tableData = res.data
          that.total = res.page.total
          that.pageSize = res.page.pageSize
          that.pageNo = res.page.pageNo
          that.convertTableData()
        } else {
          that.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err.message)
        that.tableLoading = false
        // that.$message.error(err.message)
      })
    },
    convertTableData() {
      const data = this.tableData
      const arr = []
      const rowspan = []
      data.forEach(item => {
        if (item.goodsDetailList) {
          for (let i = 0; i < item.goodsDetailList.length; i++) {
            const newData = item.goodsDetailList[i]
            if (newData.id) {
              newData.itemId = newData.id
              delete newData.id
            }
            const rdata = {
              ...item,
              ...newData
            }
            rdata.combineNum = item.goodsDetailList.length
            arr.push(rdata)
            if (i === 0) {
              rowspan.push(item.goodsDetailList.length)
            } else {
              rowspan.push(0)
            }
          }
        }
      })
      this.tableData = arr
      this.rowspan = rowspan
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 8, 9, 10, 11].includes(columnIndex)) {
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
    bdorderInfoTo(id) {
      this.$router.push('/bdorder/bdorder-info?id=' + id)
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.bdorder-manage{
  padding: 20px;
  // background: #f8f8f8;
}
</style>
