<template>
  <div class="administer">
    <tableSearch title="订单管理">
      <el-form slot="from" :inline="true">
        <el-form-item label="课程ID">
          <el-input v-model="orderNo" clearable placeholder="课程ID" />
        </el-form-item>
        <el-form-item label="课程标题">
          <el-input v-model="username" clearable placeholder="课程标题" />
        </el-form-item>
        <el-form-item label="支付类型">
          <el-select v-model="orderStatus" clearable placeholder="支付类型">
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
        <el-form-item label="课程状态">
          <el-select v-model="orderStatus" clearable placeholder="支付类型">
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
        <el-form-item label="操作时间">
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
          <el-button v-loading="exportLoading" @click="onExport">导出</el-button>
          <el-button type="success" @click="addCurriculum">添加课程</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        border
      >
        <el-table-column prop="orderNo" label="课程ID" align="center" />
        <el-table-column prop="custName" label="课程标题" align="center" />
        <el-table-column prop="goodsName" label="课程分类" align="center" />
        <el-table-column prop="type_name" label="查看次数" align="center" />
        <el-table-column prop="saleNum" label="课程状态" align="center" />
        <el-table-column prop="purchasePrice" label="操作人" align="center" />
        <el-table-column prop="payTime" label="操作时间" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
            <el-button v-if="scope.row.goodsStatus === '1'" type="text" @click="state(scope.row.id,2)">下架</el-button>
            <el-button v-else type="text" @click="state(scope.row.id,1)">上架</el-button>
            <el-button v-if="scope.row.goodsStatus !== '0'" type="text" @click="deleteItem((scope.row.id))">删除</el-button>
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
import { export_json_to_excel } from '@/excel/Export2Excel'
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
      this.currentPage = 1
      this.getList()
    },
    onExport() {
      console.log('导出')
    },
    addCurriculum() {},
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
    handleExport() {
      this.exportLoading = true
      const tHeader = [
        '订单编号',
        '用户名称',
        '商品',
        '单价',
        '数量',
        '齐装采购价'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'cname',
        'openCity',
        'iptRatio',
        'fa',
        'feng',
        'nearCity'
      ]
      this.fetchExportExcelData('export', () => {
        const list = this.exportData
        this.exportData = []
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '订单管理')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    fetchExportExcelData(val, cb) {
      const queryObj = this.handleData(val)
      this.$apis.ORDER.orderList(queryObj).then(res => {
        this.exportLoading = false
        if (res.code === 0) {
          if (res.data.length > 0) {
            this.exportData = res.data
            cb && cb.call()
          } else {
            this.$message.error('未查询到符合要求的数据')
          }
        } else {
          this.$message.error(res.message)
          this.exportData = []
        }
      }).catch(err => {
        console.log(err)
      })
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

<style lang="scss" scoped>
.administer{
  padding: 20px;
}
</style>
