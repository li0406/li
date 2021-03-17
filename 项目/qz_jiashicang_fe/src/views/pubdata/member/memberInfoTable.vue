<template>
  <div class="sales-city-table">
    <div class="qz-table-search clearfix">
      <el-form :inline="true" class="demo-form-inline">
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
        <el-form-item label="时间">
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
      >
        <el-table-column prop="company_id" fixed align="center" label="会员ID" />
        <el-table-column prop="company_jc" fixed align="center" label="会员简称" />
        <el-table-column prop="city_name" fixed align="center" label="城市" />
        <el-table-column prop="cooperate_mode_name" fixed align="center" label="会员类型" />
        <el-table-column prop="all_num" align="center" label="总单量" />
        <el-table-column prop="fen_num" align="center" label="实际分单量" min-width="90" />
        <el-table-column prop="zen_num" align="center" label="赠单量" />
        <el-table-column prop="lf_num" align="center">
          <template slot="header">
            <span>量房量</span>
            <el-tooltip effect="dark" content="按照会员点击量房状态的时间进行查询" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="fen_lf_num" align="center" label="分单量房量" min-width="90" />
        <el-table-column prop="lf_lv" align="center" label="量房率" />
        <el-table-column prop="fen_lf_lv" align="center" label="分单量房率" min-width="90" />
        <el-table-column prop="qiandan_num" align="center">
          <template slot="header">
            <span>签单量</span>
            <el-tooltip effect="dark" content="按照会员申请签约的时间进行查询" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="fen_qiandan_num" align="center" label="分单签单量" min-width="90" />
        <el-table-column prop="zen_qiandan_num" align="center" label="赠单签单量" min-width="90" />
        <el-table-column prop="qiandan_lv" align="center" label="签单率" />
        <el-table-column prop="fen_qiandan_lv" align="center" label="分单签单率" min-width="90" />
        <el-table-column prop="fen_jugai_num" align="center">
          <template slot="header">
            <span>局改量</span>
            <el-tooltip effect="dark" content="查询时间段内，分配给该会员的分单局改量" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="fen_jugai_lv" align="center" min-width="90">
          <template slot="header">
            <span>局改占比</span>
            <el-tooltip effect="dark" content="局改占比=局改量/分单量" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="round_apply_num" align="center" label="申请补轮量" min-width="90" />
        <el-table-column prop="round_apply_lv" align="center" label="申请补轮率" min-width="90" />
        <el-table-column prop="round_apply_pass_num" align="center">
          <template slot="header">
            <span>补轮量</span>
            <el-tooltip effect="dark" content="查询时间段内，该会员补轮通过的订单" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="round_apply_pass_lv" align="center">
          <template slot="header">
            <span>补轮率</span>
            <el-tooltip effect="dark" content="补轮通过率=补轮单/分单量" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="round_apply_unpass_num" align="center" label="补轮驳回量" min-width="90" />
        <el-table-column prop="round_apply_unpass_lv" align="center" label="补轮驳回率" min-width="90" />
        <el-table-column prop="round_foul_num" align="center" label="违规补轮次数" min-width="106" />
        <el-table-column prop="expend_amount" align="center" label="总消耗金额(元)" min-width="115" />
        <el-table-column prop="platform_amount" align="center" label="平台使用费(元)" min-width="115" />
        <el-table-column prop="qiandan_amount" align="center" label="签单金额(万元)" min-width="115" />
        <el-table-column prop="fen_qiandan_amount" align="center" label="分单签单金额(万元)" min-width="142" />
        <el-table-column prop="round_apply_pass_amount" align="center" label="补轮金额(元)" min-width="100" />
        <el-table-column prop="round_foul_amount" align="center" label="违规扣款金额(元)" min-width="130" />
        <el-table-column prop="account_amount" align="center" label="轮单费余额(元)" min-width="115" />
        <el-table-column prop="deposit_amount" align="center" label="保证金余额(元)" min-width="115" />
        <el-table-column prop="output_lv" align="center" label="投入产出比" min-width="90" />
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
      currentPage: 1,
      pageSize: 20,
      total: 0,
      tableData: [],
      tableLoading: false,
      exportLoading: false,
      searchLoading: false,
      company_id: '',
      date: '',
      date_begin: '',
      date_end: '',
      city_ids: [],
      cityList: [],
      companyList: []
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
    const cityId = sessionStorage.getItem('memberCityId')
    const time = sessionStorage.getItem('memberTime')
    if (cityId) {
      this.city_ids = [cityId]
    }
    if (time) {
      this.date_begin = time.split(',')[0]
      this.date_end = time.split(',')[1]
    }
    this.getList()
    this.getCityList()
  },
  methods: {
    onSubmit() {
      this.getList()
    },
    resetForm() {
      const that = this
      that.company_id = ''
      that.date = ''
      that.city_ids = []
    },
    getSubmitdata(val) {
      const that = this
      const obj = {
        city_ids: that.city_ids.join(','),
        date_begin: that.date_begin,
        date_end: that.date_end,
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
      this.$apis.PUBDATA.companyDetailed(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.tableData = res.data.list.map(item => {
            item.lf_lv = item.lf_lv + '%'
            item.fen_lf_lv = item.fen_lf_lv + '%'
            item.qiandan_lv = item.qiandan_lv + '%'
            item.fen_qiandan_lv = item.fen_qiandan_lv + '%'
            item.fen_jugai_lv = item.fen_jugai_lv + '%'
            item.round_apply_lv = item.round_apply_lv + '%'
            item.round_apply_pass_lv = item.round_apply_pass_lv + '%'
            item.round_apply_unpass_lv = item.round_apply_unpass_lv + '%'
            item.output_lv = item.output_lv + '%'
            return item
          })
          this.currentPage = res.data.page.page_current
          this.total = res.data.page.total_number
          this.date = [res.data.options.date_begin, res.data.options.date_end]
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
    // 导出
    exportExcel() {
      this.exportLoading = true
      var tHeader = [
        '会员ID',
        '会员简称',
        '城市',
        '会员类型',
        '总单量',
        '实际分单量',
        '赠单量',
        '量房量',
        '分单量房量',
        '量房率',
        '分单量房率',
        '签单量',
        '分单签单量',
        '赠单签单量',
        '签单率',
        '分单签单率',
        '局改量',
        '局改占比',
        '申请补轮量',
        '申请补轮率',
        '补轮量',
        '补轮率',
        '补轮驳回量',
        '补轮驳回率',
        '违规补轮次数',
        '总消耗金额(元)',
        '平台使用费(元)',
        '签单金额(万元)',
        '分单签单金额(万元)',
        '补轮金额(元)',
        '违规扣款金额(元)',
        '轮单费余额(元)',
        '保证金费余额(元)',
        '投入产出比'
      ]
      var filterVal = [
        'company_id',
        'company_jc',
        'city_name',
        'cooperate_mode_name',
        'all_num',
        'fen_num',
        'zen_num',
        'lf_num',
        'fen_lf_num',
        'lf_lv',
        'fen_lf_lv',
        'qiandan_num',
        'fen_qiandan_num',
        'zen_qiandan_num',
        'qiandan_lv',
        'fen_qiandan_lv',
        'fen_jugai_num',
        'fen_jugai_lv',
        'round_apply_num',
        'round_apply_lv',
        'round_apply_pass_num',
        'round_apply_pass_lv',
        'round_apply_unpass_num',
        'round_apply_unpass_lv',
        'round_foul_num',
        'expend_amount',
        'platform_amount',
        'qiandan_amount',
        'fen_qiandan_amount',
        'round_apply_pass_amount',
        'round_foul_amount',
        'account_amount',
        'deposit_amount',
        'output_lv'
      ]
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '会员明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata(1)
      this.$apis.PUBDATA.companyDetailed(obj).then((res) => {
        this.exportLoading = false
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            item.lf_lv = item.lf_lv + '%'
            item.fen_lf_lv = item.fen_lf_lv + '%'
            item.qiandan_lv = item.qiandan_lv + '%'
            item.fen_qiandan_lv = item.fen_qiandan_lv + '%'
            item.fen_jugai_lv = item.fen_jugai_lv + '%'
            item.round_apply_lv = item.round_apply_lv + '%'
            item.round_apply_pass_lv = item.round_apply_pass_lv + '%'
            item.round_apply_unpass_lv = item.round_apply_unpass_lv + '%'
            item.output_lv = item.output_lv + '%'
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
.sales-city-table {
  .mt10 {
    margin-top: 15px;
  }
}
</style>
