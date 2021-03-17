<template>
  <div class="curriculum-type">
    <tableSearch title="课程分类管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="分类ID" prop="type_id">
          <el-input v-model="fromData.type_id" placeholder="分类ID" />
        </el-form-item>
        <el-form-item label="分类名称" prop="typeName">
          <el-input v-model="fromData.typeName" clearable placeholder="分类名称" />
        </el-form-item>
        <el-form-item label="启用状态" prop="enableFlag">
          <el-select v-model="fromData.enableFlag" clearable placeholder="启用状态">
            <el-option label="全部" value="" />
            <el-option label="启用中" value="1" />
            <el-option label="未启用" value="2" />
          </el-select>
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
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button @click="onReset">重置</el-button>
          <el-button type="success" @click="addType">添加分类</el-button>
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
        <el-table-column prop="orderNo" label="分类ID" align="center" />
        <el-table-column prop="custName" label="分类名称" align="center" />
        <el-table-column prop="goodsName" label="启用状态" align="center" />
        <el-table-column prop="payTime" label="添加时间" align="center" />
        <el-table-column prop="payTime" label="排序值" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
            <el-button v-if="scope.row.enableFlag === '2'" type="text" @click="state(scope.row.id,1)">启用</el-button>
            <el-button v-else type="text" @click="state(scope.row.id,2)">停用</el-button>
            <el-button type="text" @click="deleteItem(scope.row.id)">删除</el-button>
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
  </div>
</template>

<script>
export default {
  name: 'OrderManage',
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      exportLoading: false,
      orderNo: '',
      fromData: {
        type_id: '',
        typeName: '',
        enableFlag: '1',
        typeLevel: '',
        dete: ''
      },
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
      this.currentPage = 1
      this.getList()
    },
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    addType() {
      this.$router.push('/curriculum/curriculum-type')
    },
    handleData(val) {
      const obj = {
        pageNo: this.currentPage,
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
          that.currentPage = res.page.pageNo
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
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    orderInfoTo(id) {
      this.$router.push('/order/order-info?id=' + id)
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.curriculum-type{
  padding: 20px;
  // background: #f8f8f8;
}
</style>
