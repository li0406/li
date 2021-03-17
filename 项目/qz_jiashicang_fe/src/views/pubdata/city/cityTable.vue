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
          <el-select v-model="city_ids" size="small" filterable placeholder="请选择城市(可多选)" clearable multiple collapse-tags @change="citySelsct">
            <el-option v-for="item in cityList" :key="item.city_id" :label="item.value" :value="item.city_id" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="tab === 2" label="区域：">
          <el-select v-model="area_ids" size="small" placeholder="请选择区域(可多选)" clearable multiple collapse-tags>
            <el-option v-for="item in areaList" :key="item.area_id" :label="item.area_name" :value="item.area_id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
        </el-form-item>
        <el-form-item>
          <el-checkbox v-model="show_fadan" class="filter-item" @change="tableKey=tableKey+1">
            <span>发单</span>
            <el-tooltip effect="dark" class="ml-5" content="发单量、派单量、未拨打发单量、已拨打发单量、呼通量、呼通率，按照发单时间查询" placement="top">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </el-checkbox>
          <el-checkbox v-model="show_fendan" class="filter-item" @change="tableKey=tableKey+1">
            <span>分单</span>
            <el-tooltip effect="dark" class="ml-5" content="分单量、赠单量、分单率按照发单时间查询" placement="top">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </el-checkbox>
          <el-checkbox v-model="show_achievement" class="filter-item" @change="tableKey=tableKey+1">业绩</el-checkbox>
          <el-checkbox v-model="show_liangfang" class="filter-item" @change="tableKey=tableKey+1">量房</el-checkbox>
          <el-checkbox v-model="show_qiandan" class="filter-item" @change="tableKey=tableKey+1">签单</el-checkbox>
          <el-checkbox v-model="show_jugai" class="filter-item" @change="tableKey=tableKey+1">局改</el-checkbox>
          <el-checkbox v-model="show_leixing" class="filter-item" @change="tableKey=tableKey+1">家装/公装</el-checkbox>
          <el-checkbox v-model="show_fangshi" class="filter-item" @change="tableKey=tableKey+1">半全包</el-checkbox>
          <el-checkbox v-model="show_mianji" class="filter-item" @change="tableKey=tableKey+1">装修面积</el-checkbox>
        </el-form-item>
        <el-form-item class="fr">
          <el-radio-group v-model="tab" size="small">
            <el-radio-button name="by" :label="1">按城市</el-radio-button>
            <el-radio-button name="by" :label="2">按区域</el-radio-button>
          </el-radio-group>
          <el-button v-loading="exportLoading" round plain class="export-btn ml-10 plr20" size="small" @click="exportExcel">导出</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="qz-table">
      <el-table
        ref="cityTable"
        :key="tableKey"
        v-loading="tableLoading"
        :data="tableData"
        :cell-style="rowClass"
        :header-cell-style="tableHeaderColor"
        style="width: 100%"
        max-height="800"
        @sort-change="sortChange"
      >
        <el-table-column sortable="custom" :sort-orders="sortOrders" fixed prop="city_name" align="center" label="城市" />
        <el-table-column v-if="tab === 2" sortable="custom" :sort-orders="sortOrders" fixed prop="area_name" align="center" label="区域" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_all" align="center" fixed label="总单量" min-width="90" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_fendan" align="center" fixed label="实际分单量" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_zendan" align="center" label="实际赠单量" min-width="115" />
        <el-table-column sortable="custom" :sort-orders="sortOrders" prop="csos_fendan_lv" align="center" fixed label="实际分单率" min-width="115" />
        <el-table-column v-if="show_achievement" sortable="custom" :sort-orders="sortOrders" prop="achievement" align="center" label="业绩(元)" min-width="96" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="fadan" align="center" label="发单量" min-width="90" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="paidan" align="center" label="派单量" min-width="90" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="weihu" align="center" label="未拨打发单量" min-width="130" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="yihu" align="center" label="已拨打发单量" min-width="130" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="hutong" align="center" label="呼通量" min-width="90" />
        <el-table-column v-if="show_fadan" sortable="custom" :sort-orders="sortOrders" prop="hutong_lv" align="center" label="呼通率" min-width="90" />
        <el-table-column v-if="show_fendan" sortable="custom" :sort-orders="sortOrders" prop="fendan" align="center" label="分单量" min-width="90" />
        <el-table-column v-if="show_fendan" sortable="custom" :sort-orders="sortOrders" prop="zendan" align="center" label="赠单量" min-width="90" />
        <el-table-column v-if="show_fendan" sortable="custom" :sort-orders="sortOrders" prop="fendan_lv" align="center" label="分单率" min-width="90" />
        <el-table-column v-if="show_liangfang" sortable="custom" :sort-orders="sortOrders" prop="real_lfnum" align="center" min-width="110">
          <template slot="header">
            <span>量房量 </span>
            <el-tooltip effect="dark" content="查询时间段内，该城市订单被会员点击成“已见面/已到店”、“已量房”、“已签约”的订单量（按照会员点击订单状态的时间进行查询）" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column>
        <!-- <el-table-column v-if="show_liangfang" sortable="custom" :sort-orders="sortOrders" prop="real_unlfnum" align="center" min-width="120">
          <template slot="header">
            <span>未量房量 </span>
            <el-tooltip effect="dark" content="查询时间段内，该城市订单被会员点击成“未量房”的订单量。（按照会员点击订单状态的时间进行统计）" placement="right">
              <i class="el-icon-warning-outline" />
            </el-tooltip>
          </template>
        </el-table-column> -->
        <el-table-column v-if="show_liangfang" sortable="custom" :sort-orders="sortOrders" prop="real_lflv" align="center" label="量房率" min-width="90" />
        <el-table-column v-if="show_liangfang" sortable="custom" :sort-orders="sortOrders" prop="csos_fen_lflv" align="center" label="分单量房率" min-width="115" />
        <el-table-column v-if="show_liangfang" sortable="custom" :sort-orders="sortOrders" prop="csos_zen_lflv" align="center" label="赠单量房率" min-width="115" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="qiandan" align="center" label="签单量" min-width="90" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="csos_fen_qiandan" align="center" label="分单签单量" min-width="115" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="csos_zen_qiandan" align="center" label="赠单签单量" min-width="115" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="qiandan_lv" align="center" label="签单率" min-width="90" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="csos_fen_qiandan_lv" align="center" label="分单签单率" min-width="115" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="csos_zen_qiandan_lv" align="center" label="赠单签单率" min-width="115" />
        <el-table-column v-if="show_qiandan" sortable="custom" :sort-orders="sortOrders" prop="lf_qiandan_lv" align="center" label="量房签单率" min-width="115" />
        <el-table-column v-if="show_jugai" sortable="custom" :sort-orders="sortOrders" prop="csos_jugai" align="center" label="局改量" min-width="90" />
        <el-table-column v-if="show_jugai" sortable="custom" :sort-orders="sortOrders" prop="csos_jugai_lv" align="center" label="局改占比" min-width="100" />
        <el-table-column v-if="show_leixing" sortable="custom" :sort-orders="sortOrders" prop="csos_gongzhuang" align="center" label="公装" />
        <el-table-column v-if="show_leixing" sortable="custom" :sort-orders="sortOrders" prop="csos_jiazhuang" align="center" label="家装" />
        <el-table-column v-if="show_fangshi" sortable="custom" :sort-orders="sortOrders" prop="csos_qingbao" align="center" label="清包" />
        <el-table-column v-if="show_fangshi" sortable="custom" :sort-orders="sortOrders" prop="csos_banbao" align="center" label="半包" />
        <el-table-column v-if="show_fangshi" sortable="custom" :sort-orders="sortOrders" prop="csos_quanbao" align="center" label="全包" />
        <el-table-column v-if="show_fangshi" sortable="custom" :sort-orders="sortOrders" prop="csos_mianyi" align="center" label="面议" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_40" align="center" label="40㎡以下" min-width="105" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_60" align="center" label="40-60㎡" min-width="100" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_80" align="center" label="60-80㎡" min-width="100" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_100" align="center" label="80-100㎡" min-width="106" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_120" align="center" label="100-120㎡" min-width="115" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_150" align="center" label="120-150㎡" min-width="115" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_200" align="center" label="150-200㎡" min-width="115" />
        <el-table-column v-if="show_mianji" sortable="custom" :sort-orders="sortOrders" prop="mianji_max" align="center" label="200㎡以上" min-width="115" />
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
      tab: 1,
      city_ids: [],
      area_ids: [],
      date_begin: '',
      date_end: '',
      show_fadan: true,
      show_fendan: true,
      show_achievement: true,
      show_liangfang: true,
      show_qiandan: true,
      show_jugai: true,
      show_mianji: true,
      show_fangshi: true,
      show_leixing: true,
      tableKey: 0,
      date: '',
      dateStart: '',
      dateEnd: '',
      sort_field: '',
      sort_rule: '',
      cityList: [],
      areaList: [],
      tableLoading: false,
      exportLoading: false,
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
        that.date_begin = ''
        that.date_end = ''
      }
    },
    tab(val) {
      this.currentPage = 1 // 当前页数
      this.total = 0 // 当前页数
      if (val === 1) {
        this.getList()
      } else if (val === 2 && this.city_ids.length > 0) {
        this.getList()
        this.getAreaLists()
      } else {
        this.tableData = []
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
    loadData() {
      const array = this.allData.splice(this.currentPage * this.pageSize, this.pageSize)
      this.tableData = [...this.tableData, ...array]
    },
    getSubmitdata() {
      const that = this
      const obj = {
        tab: that.tab,
        city_ids: that.city_ids.join(','),
        area_ids: that.area_ids.join(','),
        date_begin: that.date_begin,
        date_end: that.date_end,
        sort_field: that.sort_field,
        sort_rule: that.sort_rule,
        show_fadan: that.show_fadan ? 1 : 2,
        show_fendan: that.show_fendan ? 1 : 2,
        show_achievement: that.show_achievement ? 1 : 2,
        show_liangfang: that.show_liangfang ? 1 : 2,
        show_qiandan: that.show_qiandan ? 1 : 2,
        show_jugai: that.show_jugai ? 1 : 2,
        show_mianji: that.show_mianji ? 1 : 2,
        show_fangshi: that.show_fangshi ? 1 : 2,
        show_leixin: that.show_leixin ? 1 : 2
      }
      return obj
    },
    onSubmit() {
      const that = this
      if (that.tab === 2 && that.city_ids.length === 0) {
        that.$message.warning('请选择城市')
        return
      }
      that.getList()
    },
    resetForm() {
      const that = this
      that.date = ''
      that.city_ids = []
      that.area_ids = []
    },
    getList() {
      this.tableLoading = true
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityOrderDetailed(obj).then(res => {
        this.tableLoading = false
        if (res.error_code === 0) {
          this.allData = res.data.list.map(item => {
            item.csos_fendan_lv = item.csos_fendan_lv + '%'
            item.hutong_lv = item.hutong_lv + '%'
            item.fendan_lv = item.fendan_lv + '%'
            item.real_lflv = item.real_lflv + '%'
            item.csos_fen_lflv = item.csos_fen_lflv + '%'
            item.csos_zen_lflv = item.csos_zen_lflv + '%'
            item.csos_fen_qiandan_lv = item.csos_fen_qiandan_lv + '%'
            item.csos_zen_qiandan_lv = item.csos_zen_qiandan_lv + '%'
            item.qiandan_lv = item.qiandan_lv + '%'
            item.lf_qiandan_lv = item.lf_qiandan_lv + '%'
            item.csos_jugai_lv = item.csos_jugai_lv + '%'
            return item
          })
          this.total = res.data.count
          this.currentPage = 1
          this.date = [res.data.options.date_begin, res.data.options.date_end]
          this.pagination()
        } else {
          this.$message.error(res.error_msg)
          this.tableData = []
        }
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
    getAreaLists() {
      this.$apis.COMMON_API.getAreaList({ city_ids: this.city_ids.join(',') }).then(res => {
        if (res.error_code === 0) {
          this.areaList = res.data.list
        } else {
          this.$message.error(res.error_msg)
          this.cityList = []
        }
      })
    },
    citySelsct() {
      if (this.tab === 2 && this.city_ids.length > 0) {
        this.getAreaLists()
      } else {
        this.areaList = []
      }
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
      const that = this
      if (that.tab === 2 && that.city_ids.length === 0) {
        that.$message.warning('请选择城市')
        return
      }
      that.exportLoading = true
      var tHeader = ['城市', '总单量', '实际分单量', '实际分单率', '实际赠单量']
      var filterVal = ['city_name', 'csos_all', 'csos_fendan', 'csos_fendan_lv', 'csos_zendan']
      if (that.tab === 2) {
        tHeader.splice(1, 0, '区域')
        filterVal.splice(1, 0, 'area_name')
      }
      if (that.show_achievement) {
        tHeader = tHeader.concat(['业绩(元)'])
        filterVal = filterVal.concat(['achievement'])
      }
      if (that.show_fadan) {
        tHeader = tHeader.concat(['发单量', '派单量', '未拨打发单量', '已拨打发单量', '呼通量', '呼通率'])
        filterVal = filterVal.concat(['fadan', 'paidan', 'weihu', 'yihu', 'hutong', 'hutong_lv'])
      }
      if (that.show_fendan) {
        tHeader = tHeader.concat(['分单量', '赠单量', '分单率'])
        filterVal = filterVal.concat(['fendan', 'zendan', 'fendan_lv'])
      }
      if (that.show_liangfang) {
        tHeader = tHeader.concat(['量房量', '未量房量', '量房率', '分单量房率', '赠单量房率'])
        filterVal = filterVal.concat(['real_lfnum', 'real_unlfnum', 'real_lflv', 'csos_fen_lflv', 'csos_zen_lflv'])
      }
      if (that.show_qiandan) {
        tHeader = tHeader.concat(['签单量', '分单签单量', '赠单签单量', '签单率', '分单签单率', '赠单签单率', '量房签单率'])
        filterVal = filterVal.concat(['qiandan', 'csos_fen_qiandan', 'csos_zen_qiandan', 'qiandan_lv', 'csos_fen_qiandan_lv', 'csos_zen_qiandan_lv', 'lf_qiandan_lv'])
      }
      if (that.show_jugai) {
        tHeader = tHeader.concat(['局改量', '局改占比'])
        filterVal = filterVal.concat(['csos_jugai', 'csos_jugai_lv'])
      }
      if (that.show_leixing) {
        tHeader = tHeader.concat(['公装', '家装'])
        filterVal = filterVal.concat(['csos_gongzhuang', 'csos_jiazhuang'])
      }
      if (that.show_fangshi) {
        tHeader = tHeader.concat(['清包', '半包', '全包', '面议'])
        filterVal = filterVal.concat(['csos_qingbao', 'csos_banbao', 'csos_quanbao', 'csos_mianyi'])
      }
      if (that.show_mianji) {
        tHeader = tHeader.concat(['40㎡以下', '40-60㎡', '60-80㎡', '80-100㎡', '100-120㎡', '120-150㎡', '150-200㎡', '200㎡以上'])
        filterVal = filterVal.concat(['mianji_40', 'mianji_60', 'mianji_80', 'mianji_100', 'mianji_120', 'mianji_150', 'mianji_200', 'mianji_max'])
      }
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市数据明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata()
      this.$apis.PUBDATA.cityOrderDetailed(obj).then((res) => {
        this.exportLoading = false
        var exportData = []
        if (res.error_code === 0) {
          res.data.list.forEach((item) => {
            item.csos_fendan_lv = item.csos_fendan_lv + '%'
            item.hutong_lv = item.hutong_lv + '%'
            item.fendan_lv = item.fendan_lv + '%'
            item.real_lflv = item.real_lflv + '%'
            item.csos_fen_lflv = item.csos_fen_lflv + '%'
            item.csos_zen_lflv = item.csos_zen_lflv + '%'
            item.csos_fen_qiandan_lv = item.csos_fen_qiandan_lv + '%'
            item.csos_zen_qiandan_lv = item.csos_zen_qiandan_lv + '%'
            item.qiandan_lv = item.qiandan_lv + '%'
            item.lf_qiandan_lv = item.lf_qiandan_lv + '%'
            item.csos_jugai_lv = item.csos_jugai_lv + '%'
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
