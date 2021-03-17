<template>
  <div class="sales-city-table">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="分单时间">
          <el-date-picker
            v-model="fenDate"
            size="small"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="申请补轮时间">
          <el-date-picker
            v-model="applyDate"
            size="small"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="城市">
          <el-select v-model="city_ids" size="small" filterable placeholder="请选择城市(可多选)" clearable multiple collapse-tags>
            <el-option v-for="item in cityList" :key="item.city_id" :label="item.value" :value="item.city_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="会员">
          <el-select
            v-model="company_id"
            size="small"
            filterable
            clearable
            remote
            reserve-keyword
            placeholder="请输入会员id或会员简称"
            :remote-method="remoteMethod"
            :loading="searchLoading"
          >
            <el-option
              v-for="item in companyList"
              :key="item.company_id"
              :label="item.company_jc"
              :value="item.company_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="订单号">
          <el-input v-model="order_id" clearable size="small" placeholder="请输入订单号" />
        </el-form-item>
        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
        </el-form-item>
        <el-form-item>
          <el-button v-loading="exportLoading" round plain class="export-btn ml-10 plr20" size="small" @click="exportExcel">导出</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="qz-table">
      <el-table
        v-loading="tableLoading"
        :data="tableData"
        :cell-style="rowClass"
        :header-cell-style="tableHeaderColor"
        style="width: 100%"
        max-height="800"
      >
        <el-table-column prop="company_id" align="center" label="会员ID" min-width="70" />
        <el-table-column prop="company_jc" align="center" label="会员简称" min-width="150" />
        <el-table-column prop="city_name" align="center" label="城市" min-width="100" />
        <el-table-column prop="order_id" align="center" label="订单号" min-width="152" />
        <el-table-column prop="round_money" align="center" label="轮单费(元)" min-width="100" />
        <el-table-column prop="fen_date" align="center" label="分单时间" min-width="160" />
        <el-table-column prop="apply_date" align="center" label="申请补轮时间" min-width="160" />
        <el-table-column prop="apply_reason_remark" align="center" label="申请原因（备注）" min-width="160" />
        <el-table-column prop="exam_status_name" align="center" label="补轮审核状态" min-width="105" />
        <el-table-column prop="exam_remark" align="center" label="驳回原因（备注）" min-width="160" />
        <el-table-column prop="exam_date" align="center" label="补轮审核时间" min-width="160" />
      </el-table>
    </div>
    <el-pagination
      v-if="total > 0"
      class="mt10"
      background
      :current-page="currentPage"
      :page-sizes="[10, 20, 50, 100]"
      :page-size="pageSize"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
      prev-text="上一页"
      next-text="下一页"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />
  </div>
</template>

<script>
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'CityTable',
  props: {
    cityId: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      tableLoading: false,
      exportLoading: false,
      searchLoading: false,
      tableData: [],
      currentPage: 1,
      pageSize: 20,
      total: 0,
      fenDate: '',
      fen_begin: '',
      fen_end: '',
      applyDate: '',
      apply_begin: '',
      apply_end: '',
      order_id: '',
      company_id: '',
      export: 0,
      city_ids: [],
      cityList: [],
      companyList: []
    }
  },
  watch: {
    fenDate(val) {
      const that = this
      if (val) {
        that.fen_begin = val[0]
        that.fen_end = val[1]
      } else {
        that.fen_begin = ''
        that.fen_end = ''
      }
    },
    applyDate(val) {
      const that = this
      if (val) {
        that.apply_begin = val[0]
        that.apply_end = val[1]
      } else {
        that.apply_begin = ''
        that.apply_end = ''
      }
    }
  },
  created() {
    const cityId = sessionStorage.getItem('memberCityId')
    const time = sessionStorage.getItem('memberTime')
    if (cityId) {
      this.city_ids = [cityId]
    }
    if (time) {
      this.fen_begin = time.split(',')[0]
      this.fen_end = time.split(',')[1]
    }
    this.getCityList()
    if (cityId && time) {
      this.getList()
    }
  },
  methods: {
    onSubmit() {
      const that = this
      that.getList()
    },
    resetForm() {
      const that = this
      that.applyDate = ''
      that.fenDate = ''
      that.city_ids = []
      that.order_id = ''
      that.company_id = ''
    },
    getSubmitdata(val) {
      const that = this
      const obj = {
        city_ids: that.city_ids.join(','),
        fen_begin: that.fen_begin,
        fen_end: that.fen_end,
        apply_begin: that.apply_begin,
        apply_end: that.apply_end,
        order_id: that.order_id,
        company_id: that.company_id,
        export: 0
      }
      if (!val) {
        obj.page = that.currentPage
        obj.page_size = that.pageSize
      } else {
        obj.export = 1
      }
      return obj
    },
    getList() {
      this.tableLoading = true
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.roundapplyDetailed(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list
          this.currentPage = res.data.page.page_current
          this.total = res.data.page.total_number
          this.fenDate = [res.data.options.fen_begin, res.data.options.fen_end]
          this.applDate = [res.data.options.apply_begin, res.data.options.apply_end]
        } else {
          this.$message.error(res.error_msg)
          this.tableData = []
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
    // 导出
    exportExcel() {
      this.exportLoading = true
      var tHeader = [
        '会员ID',
        '会员简称',
        '城市',
        '订单号',
        '轮单费(元)',
        '分单时间',
        '申请补轮时间',
        '申请原因（备注）',
        '补轮审核状态',
        '驳回原因（备注）',
        '补轮审核时间'
      ]
      var filterVal = [
        'company_id',
        'company_jc',
        'city_name',
        'order_id',
        'round_money',
        'fen_date',
        'apply_date',
        'apply_reason_remark',
        'exam_status_name',
        'exam_remark',
        'exam_date'
      ]
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '会员补轮明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.roundapplyDetailed(obj).then((res) => {
        this.exportLoading = false
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            exportData.push(item)
          })
          this.exportData = exportData
          cb & cb.call()
        } else {
          this.$message.error(res.error_msg)
          this.exportData = []
        }
      }).catch(res => {
        this.exportLoading = false
      })
    },
    getCityList() {
      this.$apis.COMMON_API.getCityList().then(res => {
        if (res.error_code === 0) {
          this.cityList = res.data.list
        } else {
          this.cityList = []
        }
      })
    },
    getCompanySearch(val) {
      this.$apis.COMMON_API.getCompanySearch({ keyword: val }).then(res => {
        if (res.error_code === 0) {
          this.companyList = res.data.list
        } else {
          this.companyList = []
        }
      })
    },
    remoteMethod(query) {
      if (query !== '') {
        this.searchLoading = true
        setTimeout(() => {
          this.searchLoading = false
          this.getCompanySearch(query)
        }, 200)
      } else {
        this.companyList = []
      }
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
