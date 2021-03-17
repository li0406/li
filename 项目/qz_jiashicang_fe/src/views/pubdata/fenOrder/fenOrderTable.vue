<template>
  <div class="sales-fen-table">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="分单时间：">
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
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="city_name" fixed align="center" label="城市" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="vip_total_count" fixed align="center" label="会员数" min-width="90" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="city_order_price" align="center" min-width="162">
          <template slot="header">
            <span>城市2.0单价(元) </span>
            <el-tooltip effect="dark" content="轮单费单价，数据源：销售系统->报价管理（轮单费单价）" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="order_price" align="center" min-width="170">
          <template slot="header">
            <span>实际订单售价(元) </span>
            <el-tooltip effect="dark" content="实际订单售价=当月总扣款*（3个月总收款/3个月总充值）/(城市订单价格*4*当月实际分单量)*城市订单价格" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="month_need_orders" align="center" min-width="147">
          <template slot="header">
            <span>月所需分单量 </span>
            <el-tooltip effect="dark" content="人工已维护时，统计该城市人工填写的实际所需分单；人工未维护时，统计该城市的预估当月所需分单；" placement="right">
              <div slot="content">人工已维护时，统计该城市人工填写的实际所需分单；<br> 人工未维护时，统计该城市的预估当月所需分单；</div>
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_fendan" fixed align="center" label="实际分单量" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_quedan" align="center" min-width="135">
          <template slot="header">
            <span>实际缺单量 </span>
            <el-tooltip effect="dark" content="实际缺单量=月所需分单量-实际分单量" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="recall_lv" align="center" min-width="120">
          <template slot="header">
            <span>撤回占比 </span>
            <el-tooltip effect="dark" content="撤回占比=撤回次数/（总单量+撤回状态的订单量）" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="allot_max_num" align="center" min-width="135">
          <template slot="header">
            <span>理论分配数 </span>
            <el-tooltip effect="dark" content="" placement="right">
              <div slot="content">当会员个数&lt;4时，理论分配会员数=会员个数*总实际分单量；<br> 当会员个数&ge;4时，理论分配会员数=4*该时间段内的总实际分单量；</div>
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="allot_fen_num" align="center" min-width="135">
          <template slot="header">
            <span>实际分配数 </span>
            <el-tooltip effect="dark" content="总分单以分单的形式实际分配到装企的次数" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="allot_waste_num" align="center" min-width="135">
          <template slot="header">
            <span>浪费分配数 </span>
            <el-tooltip effect="dark" content="分配数差额=理论分配数-实际分配数" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="allot_waste_lv" align="center" label="分单浪费率" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="month_expend" align="center" label="总消耗金额(元)" min-width="138" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="user_price" align="center" min-width="176">
          <template slot="header">
            <span>1.0分单客单价(元) </span>
            <el-tooltip effect="dark" content="1.0会员总消耗金额/以分单形式分配给1.0会员的分单次数" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="newuser_price" align="center" min-width="176">
          <template slot="header">
            <span>2.0分单客单价(元) </span>
            <el-tooltip effect="dark" content="2.0会员总消耗金额/以分单形式分配给2.0会员的分单次数" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="sback_fendan" align="center" label="2.0会员的实际分单量" min-width="180" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_order_num" align="center" label="申请补轮量" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_company_num" align="center" label="申请补轮次数" min-width="130" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_pass_num" align="center" label="补轮量" min-width="90" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_company_pass_num" align="center" label="补轮次数" min-width="100" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="round_pass_money" align="center" label="补轮金额" min-width="100" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_company_lv" align="center" label="申请补轮率" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_pass_lv" align="center" label="补轮率" min-width="90" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_sback_pass_lv" align="center" label="补轮通过率" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_unpass_num" align="center" label="补轮驳回单量" min-width="130" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_company_unpass_num" align="center" label="补轮驳回次数" min-width="130" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="round_unpass_money" align="center" label="补轮驳回金额(元)" min-width="152" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="apply_company_unpass_lv" align="center" label="补轮驳回率" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="round_foul_num" align="center" label="违规补轮次数" min-width="130" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="round_foul_lv" align="center" label="违规补轮占比" min-width="130" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" align="center" label="操作" fixed="right">
          <template slot-scope="scope">
            <el-button v-if="scope.row.city_id > 2" type="text" class="btn-text-color" @click="toBlInfo(scope.row.city_id)">补轮明细</el-button>
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
      currentPage: 1, // 当前页数
      total: 0, // 总条数
      pageSize: 20, // 总共页数
      allData: [],
      tableData: [],
      tableLoading: false,
      exportLoading: false,
      date: '',
      tableDate: '', // 列表的请求时间
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
      if (val) {
        this.date_begin = val[0]
        this.date_end = val[1]
      } else {
        this.date_begin = ''
        this.date_end = ''
      }
    }
  },
  created() {
    this.getCityList()
    this.getList()
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
    getList() {
      this.tableLoading = true
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityFendanDetailed(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.allData = res.data.list.map(item => {
            item.recall_lv = item.recall_lv + '%'
            item.allot_waste_lv = item.allot_waste_lv + '%'
            item.apply_company_lv = item.apply_company_lv + '%'
            item.apply_pass_lv = item.apply_pass_lv + '%'
            item.apply_sback_pass_lv = item.apply_sback_pass_lv + '%'
            item.apply_company_unpass_lv = item.apply_company_unpass_lv + '%'
            item.round_foul_lv = item.round_foul_lv + '%'
            return item
          })
          this.total = res.data.list.length
          this.currentPage = 1
          this.date = [res.data.options.date_begin, res.data.options.date_end]
          this.tableDate = [res.data.options.date_begin, res.data.options.date_end]
          this.pagination()
        } else {
          this.$message.error(res.error_msg)
          this.tableData = []
        }
      })
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
    getCityList() {
      this.$apis.COMMON_API.getCityList().then(res => {
        if (res.error_code === 0) {
          this.cityList = res.data.list
        } else {
          this.cityList = []
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
        '会员数',
        '城市2.0单价(元)',
        '实际订单售价(元)',
        '月所需分单量',
        '实际分单量',
        '实际缺单量',
        '撤回占比',
        '理论分配数',
        '实际分配数',
        '浪费分配数',
        '分单浪费率',
        '总消耗金额(元)',
        '1.0分单客单价(元)',
        '2.0分单客单价(元)',
        '2.0会员的实际分单量',
        '申请补轮量',
        '申请补轮次数',
        '补轮量',
        '补轮次数',
        '补轮金额',
        '申请补轮率',
        '补轮率',
        '补轮通过率',
        '补轮驳回单量',
        '补轮驳回次数',
        '补轮驳回金额(元)',
        '补轮驳回率',
        '违规补轮次数',
        '违规补轮占比'
      ]
      var filterVal = [
        'city_name',
        'vip_total_count',
        'city_order_price',
        'order_price',
        'month_need_orders',
        'csos_fendan',
        'csos_quedan',
        'recall_lv',
        'allot_max_num',
        'allot_fen_num',
        'allot_waste_num',
        'allot_waste_lv',
        'month_expend',
        'user_price',
        'newuser_price',
        'sback_fendan',
        'apply_order_num',
        'apply_company_num',
        'apply_pass_num',
        'apply_company_pass_num',
        'round_pass_money',
        'apply_company_lv',
        'apply_pass_lv',
        'apply_sback_pass_lv',
        'apply_unpass_num',
        'apply_company_unpass_num',
        'round_unpass_money',
        'apply_company_unpass_lv',
        'round_foul_num',
        'round_foul_lv'
      ]
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市分单数据明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityFendanDetailed(obj).then((res) => {
        this.exportLoading = false
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            item.recall_lv = item.recall_lv + '%'
            item.allot_waste_lv = item.allot_waste_lv + '%'
            item.apply_company_lv = item.apply_company_lv + '%'
            item.apply_pass_lv = item.apply_pass_lv + '%'
            item.apply_sback_pass_lv = item.apply_sback_pass_lv + '%'
            item.apply_company_unpass_lv = item.apply_company_unpass_lv + '%'
            item.round_foul_lv = item.round_foul_lv + '%'
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
    toBlInfo(val) {
      this.$router.push({ path: '/pubdata/memberdata' })
      sessionStorage.setItem('memberType', 3)
      sessionStorage.setItem('memberCityId', val)
      sessionStorage.setItem('memberTime', this.tableDate.join(','))
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
