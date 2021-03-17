<template>
  <div class="sales-city">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="发单时间：">
          <el-date-picker
            v-model="date"
            size="small"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            :picker-options="pickerOptions"
          />
        </el-form-item>
        <el-form-item label="账户：">
          <el-select v-model="account_id" size="small" filterable placeholder="请选择账户" clearable collapse-tags>
            <el-option v-for="item in accountList" :key="item.id" :label="item.account_name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
          <el-button class="export-btn" plain size="small" @click="toChannel">上传渠道消耗</el-button>
        </el-form-item>
        <el-form-item class="fr">
          <el-button v-loading="exportLoading" round plain class="export-btn ml-10 plr20" size="small" @click="exportExcel">导出</el-button>
        </el-form-item>
      </el-form>
    </div>
    <qzBox title="数据汇总" :border="0">
      <el-row :gutter="12">
        <el-col :span="5">
          <el-card shadow="always">
            <div>投入产出比</div>
            <div class="num">{{ information.roi_show }}</div>
          </el-card>
        </el-col>
        <el-col :span="5">
          <el-card shadow="always">
            <div>渠道消耗</div>
            <div class="num">{{ information.expend_amount }}</div>
          </el-card>
        </el-col>
        <el-col :span="5">
          <el-card shadow="always">
            <div>订单收入</div>
            <div class="num">{{ information.order_amount }}</div>
          </el-card>
        </el-col>
        <el-col :span="5">
          <el-card shadow="always">
            <div>发单量</div>
            <div class="num">{{ information.fadan }}</div>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card shadow="always">
            <div>分单量</div>
            <div class="num">{{ information.fendan }}</div>
          </el-card>
        </el-col>
      </el-row>
    </qzBox>
    <div style="width: 100%;height: 390px;margin-top:20px;">
      <MarketRoiTrend :message="message" />
    </div>
    <div>
      <qzBox title="数据明细" :border="0">
        <div class="sales-city-table">
          <div class="qz-table">
            <el-table
              ref="cityTable"
              v-loading="tableLoading"
              :data="tableData"
              style="width: 100%"
              :cell-style="rowClass"
              :header-cell-style="tableHeaderColor"
            >
              <el-table-column prop="account_name" align="center" label="账户名称" />
              <el-table-column prop="src_number" align="center" label="渠道数量" />
              <el-table-column prop="roi_show" align="center" label="ROI" />
              <el-table-column prop="expend_amount" align="center" label="渠道消耗" />
              <el-table-column prop="order_amount" align="center" label="订单收入" />
              <el-table-column prop="order_user_amount" align="center" label="1.0会员订单收入" />
              <el-table-column prop="order_sback_amount" align="center" label="2.0会员订单收入" />
              <el-table-column prop="fadan" align="center" label="发单量" />
              <el-table-column prop="fendan" align="center" label="分单量" />
              <el-table-column prop="fendan_lv_show" align="center" label="分单率" />
            </el-table>
          </div>
          <el-pagination
            v-if="total > 0"
            class="mt10"
            background
            :current-page="currentPage"
            :page-sizes="[10, 20, 50, 100]"
            :page-size="pageSize"
            :total="total"
            layout="total, sizes, prev, pager, next, jumper"
            prev-text="上一页"
            next-text="下一页"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </qzBox>
    </div>
  </div>
</template>

<script>
import { export_json_to_excel } from '@/excel/Export2Excel'

export default {
  name: 'RoiData',
  components: {
    MarketRoiTrend: () => import('@/components/Echarts/MarketRoiTrend/index')
  },
  data() {
    return {
      account_id: '',
      accountList: [],
      date: '',
      message: {},
      start_time: '',
      end_time: '',
      information: {},
      tableData: [],
      tableLoading: false,
      exportLoading: false,
      currentPage: 1, // 当前页数
      total: 0, // 总条数
      pageSize: 20, // 总共页数
      exportData: [],
      pickerOptions: {
        disabledDate(v) {
          return v.getTime() > new Date().getTime()
        }
      }
    }
  },
  computed: {

  },
  created() {
    this.getAccountList()
    this.getDataCollect()
    this.getDataList()
  },
  methods: {
    toChannel() {
      this.$router.push({
        path: '/setup/channel'
      })
    },
    getAccountList() {
      this.$apis.PUBDATA.getAccountList().then(res => {
        if (res.error_code === 0) {
          this.accountList = res.data.list
        } else {
          this.$message({
            message: res.error_msg,
            type: 'error'
          })
        }
      })
    },
    getDataCollect() {
      this.$apis.PUBDATA.getDataCollect(
        {
          date_begin: this.start_time,
          date_end: this.end_time,
          account_ids: this.account_id
        }
      ).then(res => {
        if (res.error_code === 0) {
          this.date = [res.data.options.date_begin, res.data.options.date_end]
          this.information = res.data.total
        } else {
          this.$message({
            message: res.error_msg,
            type: 'error'
          })
        }
      })
    },
    getDataList() {
      this.$apis.PUBDATA.getDataList(
        {
          date_begin: this.start_time,
          date_end: this.end_time,
          account_ids: this.account_id,
          page: this.currentPage,
          limit: this.pageSize,
          export: 0
        }
      ).then(res => {
        if (res.error_code === 0) {
          this.tableData = res.data.list
          this.total = res.data.page.total_number
        } else {
          this.$message({
            message: res.error_msg,
            type: 'error'
          })
        }
      })
    },
    // 查询
    onSubmit() {
      if (this.date) {
        this.start_time = this.date[0]
        this.end_time = this.date[1]
        this.message = {
          start_time: this.date[0],
          end_time: this.date[1],
          account_id: this.account_id
        }
      } else {
        this.start_time = ''
        this.end_time = ''
        this.message = {
          start_time: '',
          end_time: '',
          account_id: this.account_id
        }
      }
      this.currentPage = 1
      this.getDataList()
      this.getDataCollect()
    },
    // 重置
    resetForm() {
      this.date = ''
      this.start_time = ''
      this.end_time = ''
      this.account_id = ''
    },
    // 导出
    exportExcel() {
      const that = this
      that.exportLoading = true
      const tHeader = ['账户名称', '渠道数量', 'ROI', '渠道消耗', '订单收入', '1.0会员订单收入', '2.0会员订单收入', '发单量', '分单量', '分单率']
      const filterVal = ['account_name', 'src_number', 'roi_show', 'expend_amount', 'order_amount', 'order_user_amount', 'order_sback_amount', 'fadan', 'fendan', 'fendan_lv_show']
      that.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '市场ROI数据分析明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      this.$apis.PUBDATA.getDataList(
        {
          date_begin: this.start_time,
          date_end: this.end_time,
          account_ids: this.account_id,
          page: this.currentPage,
          limit: this.pageSize,
          export: 1
        }
      ).then((res) => {
        this.exportLoading = false
        if (res.error_code === 0) {
          this.exportData = res.data.list
          cb & cb.call()
        } else {
          this.$message.error(res.error_msg)
          this.exportData = []
        }
      }).catch(res => {
        this.exportLoading = false
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getDataList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getDataList()
    },
    // 修改table header的背景色
    tableHeaderColor({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 0) {
        return 'background-color: #0A145F;color: #fff;font-weight: 500;border-bottom: 1px dashed #02417E;'
      }
    },
    // 表格样式设置
    rowClass() {
      return 'text-align: center;border-bottom: 1px dashed #02417E;color: #2C9CFA;'
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.sales-city {
  padding: 20px 15px;
  .el-card{
    background: none;
    color: #fff;
    box-shadow: inset 0 0 11px #04e3ff;
    border: none;
  }
  .el-card__body{
    height: 150px;
    div{
      text-align: left;
    }
    .num{
      font-size: 30px;
      font-weight: bold;
      margin-top:50px;
    }
  }
}
</style>
