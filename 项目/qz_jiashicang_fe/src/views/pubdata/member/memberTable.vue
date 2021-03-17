<template>
  <div class="sales-city-table">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="时间：">
          <el-date-picker
            v-model="date"
            size="small"
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="城市：">
          <el-select v-model="city_ids" size="small" filterable placeholder="请选择城市(可多选)" clearable multiple collapse-tags>
            <el-option v-for="item in cityList" :key="item.city_id" :label="item.value" :value="item.city_id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
        </el-form-item>
        <el-form-item class="fr">
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
        @sort-change="sortChange"
      >
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="city_name" align="center" label="城市" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_total_num" align="center" label="总会员数" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="expect_user_num" align="center" label="期望会员数" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_total_left" align="center" label="会员数量差距" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_count" align="center">
          <template slot="header">
            <span>1.0会员数 </span>
            <el-tooltip effect="dark" content="" placement="right">
              <div slot="content">含老签返</div>
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_num" align="center" label="1.0会员倍数">
          <template slot="header">
            <span>1.0会员倍数 </span>
            <el-tooltip effect="dark" content="" placement="right">
              <div slot="content">含老签返</div>
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="signback_valid_count" align="center" label="2.0有效会员数" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="signback_invalid_count" align="center" label="2.0无效会员数" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_quanwu_count" align="center" label="全屋定制会员" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_price" align="center" label="1.0会员客单价(元)" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="signback_price" align="center" label="2.0会员客单价(元)" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="user_renew_lv" align="center" label="1.0会员续费率" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="signback_renew_lv" align="center" label="2.0会员续费率" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="user_output_lv" align="center" label="1.0装企投入产出比" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="signback_output_lv" align="center" label="2.0装企投入产出比" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" fixed="right" align="center" label="操作">
          <template slot-scope="scope">
            <el-button v-if="scope.row.city_id > 2" type="text" class="btn-text-color" @click="toBlDetailed(scope.row.city_id)">会员明细</el-button>
          </template>
        </el-table-column>
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
</template>

<script>
import { export_json_to_excel } from '@/excel/Export2Excel'
import { pagination } from '@/utils/index'
export default {
  name: 'CityTable',
  data() {
    return {
      tableLoading: false,
      exportLoading: false,
      tableData: [],
      currentPage: 1,
      pageSize: 20,
      total: 0,
      date: '',
      tableDate: '', // 列表中时间
      date_begin: '',
      date_end: '',
      sort_field: '',
      sort_rule: '',
      city_ids: [],
      cityList: [],
      sortOrders: ['descending', 'ascending']
    }
  },
  watch: {
    date(val) {
      const that = this
      if (val) {
        that.date_begin = val[0]
        that.date_end = val[1]
      } else {
        this.date_begin = ''
        this.date_end = ''
      }
    }
  },
  created() {
    this.getList()
    this.getCityList()
  },
  methods: {
    sortChange(data) {
      this.sort_field = data.prop
      this.sort_rule = data.order === 'ascending' ? 'asc' : 'desc'
      this.getList()
    },
    onSubmit() {
      this.getList()
    },
    resetForm() {
      const that = this
      that.date = ''
      that.city_ids = []
    },
    getSubmitdata() {
      const that = this
      const obj = {
        city_ids: that.city_ids.join(','),
        date_begin: that.date_begin,
        date_end: that.date_end,
        sort_field: that.sort_field,
        sort_rule: that.sort_rule
      }
      return obj
    },
    getList() {
      this.tableLoading = true
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityUserDetailed(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.allData = res.data.list.map(item => {
            item.user_renew_lv = item.user_renew_lv + '%'
            item.signback_renew_lv = item.signback_renew_lv + '%'
            item.user_output_lv = item.user_output_lv + '%'
            item.signback_output_lv = item.signback_output_lv + '%'
            return item
          })
          this.total = res.data.list.length
          this.currentPage = 1
          this.date = [res.data.options.date_begin, res.data.options.date_end]
          this.tableDate = [res.data.options.date_begin, res.data.options.date_end]
          this.pagination()
        } else {
          this.tableData = []
        }
      })
    },
    pagination() {
      this.tableLoading = true
      setTimeout(() => {
        this.tableLoading = false
        this.tableData = pagination(this.allData, this.pageSize, this.currentPage)
      }, 500)
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.pagination()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.pagination()
    },
    // 导出
    exportExcel() {
      this.exportLoading = true
      var tHeader = [
        '城市',
        '总会员数',
        '期望会员数',
        '会员数量差距',
        '1.0会员数',
        '1.0会员倍数',
        '2.0有效会员数',
        '2.0无效会员数',
        '全屋定制会员',
        '1.0会员客单价(元)',
        '2.0会员客单价(元)',
        '1.0会员续费率',
        '2.0会员续费率',
        '1.0装企投入产出比',
        '2.0装企投入产出比'
      ]
      var filterVal = [
        'city_name',
        'vip_total_num',
        'expect_user_num',
        'vip_total_left',
        'vip_count',
        'vip_num',
        'signback_valid_count',
        'signback_invalid_count',
        'vip_quanwu_count',
        'vip_price',
        'signback_price',
        'user_renew_lv',
        'signback_renew_lv',
        'user_output_lv',
        'signback_output_lv'
      ]
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市会员明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityUserDetailed(obj).then((res) => {
        this.exportLoading = false
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            item.user_renew_lv = item.user_renew_lv + '%'
            item.signback_renew_lv = item.signback_renew_lv + '%'
            item.user_output_lv = item.user_output_lv + '%'
            item.signback_output_lv = item.signback_output_lv + '%'
            exportData.push(item)
          })
          this.exportData = exportData
          cb & cb.call()
        } else {
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
    toBlDetailed(val) {
      sessionStorage.setItem('memberTime', this.tableDate.join(','))
      sessionStorage.setItem('memberCityId', val)
      this.$emit('setTab', 2)
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
