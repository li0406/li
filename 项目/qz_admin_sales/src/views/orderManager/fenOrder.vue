<template>
  <div class="member-fen-order">
    <div class="search-box">
      <div class="search">
        <div class="fl mr20">
          城市：<br>
          <el-autocomplete
            v-model="citySelectStr"
            clearable
            class="inline-input mt4"
            :fetch-suggestions="queryCity"
            placeholder="请选择"
            @select="handleSelect"
            @blur="handleBlur"
          />
        </div>
        <div class="fl mr20">
          开始时间: <br>
          <el-date-picker
            v-model="begin_time"
            class="mt4"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            placeholder="请选择日期"
          />
        </div>
        <div class="fl mr20">
          结束时间: <br>
          <el-date-picker
            v-model="end_time"
            class="mt4"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            placeholder="请选择日期"
          />
        </div>
        <div>
          <br>
          <el-button
            type="primary"
            icon="el-icon-search"
            class="mt4"
            @click="handleSearch"
          >查询</el-button>
          <el-button
            v-loading="exportLoading"
            type="success"
            icon="el-icon-search"
            class="mt4"
            @click="handleExport"
          >导出</el-button>
        </div>
      </div>
    </div>
    <p class="tip">
      提示：签单数、实际分单量、实际赠单量、实际有效单量，按照分单时间进行统计；其他数据按照发单时间进行统计
    </p>
    <div class="qian-main">
      <div class="content-table">
        <el-table
          v-loading="visibleTable"
          :data="tableData"
          border
          :summary-method="getSummaries"
          show-summary
          max-height="650"
          style="width: 100%"
          :default-sort="{ prop: 'fen', order: 'descending' }"
          @sort-change="sortHandle"
        >
          <el-table-column prop="cname" align="center" label="城市" />
          <el-table-column
            prop="fadan"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="发单量"
          />
          <el-table-column
            prop="weihu"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="未呼量"
          />
          <el-table-column
            prop="yihu"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="已呼量"
          />
          <el-table-column
            prop="fen"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="分单量"
          />
          <el-table-column
            prop="fen_other"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="分没人跟"
          />
          <el-table-column
            prop="fendan_lv"
            align="center"
            sortable="custom"
            :sort-orders="['descending', 'ascending']"
            label="分单率"
          />
          <el-table-column
            prop="zen"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="赠单量"
          />
          <el-table-column
            prop="zen_other"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="赠没人跟"
          />
          <el-table-column
            prop="zendan_lv"
            align="center"
            sortable="custom"
            :sort-orders="['descending', 'ascending']"
            label="赠单率"
          />
          <el-table-column
            prop="zongx_lv"
            align="center"
            sortable="custom"
            :sort-orders="['descending', 'ascending']"
            label="总有效率"
          />
          <el-table-column
            prop="qiandan"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="签单数"
          />
          <el-table-column
            prop="qiandan_lv"
            align="center"
            sortable="custom"
            :sort-orders="['descending', 'ascending']"
            label="签单率"
          />
          <el-table-column
            prop="csos_fendan"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="实际分单量"
            width="130px"
          />
          <el-table-column
            prop="csos_zendan"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="实际赠单量"
            width="130px"
          />
          <el-table-column
            prop="validnum"
            align="center"
            sortable
            :sort-orders="['descending', 'ascending']"
            label="实际有效单量"
            width="130px"
          />
        </el-table>
      </div>
    </div>
  </div>
</template>
<script>
import {
  fetchGetFenOrderList,
  fetchGetFenOrderListExport
} from '@/api/orderManage'
import { fetchCityList } from '@/api/common'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'CityOrder',
  components: {},
  data() {
    return {
      begin_time: '',
      end_time: '',
      citys: [],
      citySelectStr: '',
      citySelectVal: '',
      cityBlurFlag: null,
      tableData: [],
      visibleTable: false,
      exportLoading: false,
      exportData: [],
      countinfo: {},
      fen_lv: '',
      zen_lv: '',
      zongx_lv: '',
      fendan_lv: '',
      zendan_lv: '',
      fenother_lv: '',
      zenother_lv: '',
      zongother_lv: '',
      qiandan_lv: ''
    }
  },
  watch: {
    citySelectStr(value) {
      if (!value) {
        this.citySelectVal = ''
        this.cityBlurFlag = null
      }
    }
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    var date = new Date()
    this.begin_time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-01'
    this.end_time = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate()
    this.fetchGetFenOrderList()
  },
  methods: {
    getSummaries(param) {
      const { columns, data } = param
      const sums = []
      columns.forEach((column, index) => {
        if (index === 0) {
          sums[index] = '合计'
          return
        }
        const values = data.map((item) => item[column.property])
        if (!values.every((value) => isNaN(value))) {
          sums[index] = values.reduce((prev, curr) => {
            const value = curr
            if (!isNaN(value)) {
              return prev + curr
            } else {
              return prev
            }
          }, 0)
          if (index === 6 || index === 9 || index === 10 || index === 12) {
            sums[6] = this.fenother_lv
            sums[9] = this.zenother_lv
            sums[10] = this.zongother_lv
            sums[12] = this.qiandan_lv
          } else {
            sums[index] += ' '
          }
        } else {
          if (index === 6 || index === 9 || index === 10 || index === 12) {
            sums[6] = this.fenother_lv
            sums[9] = this.zenother_lv
            sums[10] = this.zongother_lv
            sums[12] = this.qiandan_lv
          } else {
            sums[index] = '--'
          }
        }
      })
      return sums
    },
    handleSearch() {
      const begin = new Date(this.begin_time).getTime()
      const end = new Date(this.end_time).getTime()
      if (begin !== '' && end !== '' && begin > end) {
        this.$message.warning('开始时间不能大于结束时间')
        return false
      }
      this.fetchGetFenOrderList()
    },
    handleExport() {
      this.export = 1
      this.exportLoading = true
      const tHeader = [
        '城市',
        '发单量',
        '未呼量',
        '已呼量',
        '分单量',
        '分没人跟',
        '分单率',
        '赠单量',
        '赠没人跟',
        '赠单率',
        '总有效率',
        '签单数',
        '签单率',
        '实际分单量',
        '实际赠单量',
        '实际有效单量'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'cname',
        'fadan',
        'weihu',
        'yihu',
        'fen',
        'fen_other',
        'fendan_lv',
        'zen',
        'zen_other',
        'zendan_lv',
        'zongx_lv',
        'qiandan',
        'qiandan_lv',
        'csos_fendan',
        'csos_zendan',
        'validnum'
      ]
      this.fetchGetFenOrderListExport('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市分单统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchGetFenOrderListExport(val, cb) {
      let query = {
        city_id: this.citySelectVal,
        starttime: this.begin_time,
        endtime: this.end_time
      }
      query = Object.assign({}, query, { export: 1 })
      fetchGetFenOrderList(query).then((res) => {
        if (
          parseInt(res.status) === 200 &&
          parseInt(res.data.error_code) === 0
        ) {
          if (res.data.data.list.length > 0) {
            this.exportData = res.data.data.list
            var newObj = {
              cname: '合计',
              fadan: this.countinfo.all_fa,
              weihu: this.countinfo.all_weihu,
              yihu: this.countinfo.all_yihu,
              fen: this.countinfo.all_fen,
              fen_other: this.countinfo.all_fenother,
              fendan_lv: this.countinfo.all_fen_lv,
              zen: this.countinfo.all_zen,
              zen_other: this.countinfo.all_zenother,
              zendan_lv: this.countinfo.all_zen_lv,
              zongx_lv: this.countinfo.all_zong_lv,
              qiandan: this.countinfo.all_qiandan,
              qiandan_lv: this.countinfo.all_qiandan_lv,
              csos_fendan: this.countinfo.all_csos_fendan,
              csos_zendan: this.countinfo.all_csos_zendan,
              validnum: this.countinfo.all_validnum
            }
            this.exportData.push(newObj)
            cb & cb.call()
          } else {
            this.$message.warning('未查询到符合要求的数据')
            this.exportLoading = false
          }
        } else {
          this.$message.error(res.data.error_msg)
          this.exportData = []
          this.exportLoading = false
        }
      })
    },
    loadAllCity() {
      fetchCityList().then((res) => {
        if (
          parseInt(res.status) === 200 &&
          parseInt(res.data.error_code) === 0
        ) {
          const data = res.data.data[0]
          data.forEach((item, index) => {
            this.citys.push(item)
          })
        } else {
          this.citys = []
        }
      })
    },
    queryCity(queryString, cb) {
      const citys = this.citys
      const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
      this.cityBlurFlag = null
      cb(results)
    },
    createFilter(queryString) {
      return (city) => {
        return city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1
      }
    },
    handleBlur() {
      if (!this.cityBlurFlag) {
        this.citySelectStr = ''
        this.citySelectVal = ''
      }
    },
    handleSelect(item) {
      this.cityBlurFlag = item
      this.citySelectVal = item.cid
      this.citySelectStr = item.value
    },
    fetchGetFenOrderList() {
      const queryObj = {
        city_id: this.citySelectVal,
        starttime: this.begin_time,
        endtime: this.end_time,
        fen_lv: this.fen_lv,
        zen_lv: this.zen_lv,
        zongx_lv: this.zongx_lv,
        qiandan_lv: this.qiandan_lv
      }
      this.visibleTable = true
      fetchGetFenOrderList(queryObj)
        .then((res) => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.fenother_lv = res.data.data.countinfo.all_fen_lv
            this.zenother_lv = res.data.data.countinfo.all_zen_lv
            this.zongother_lv = res.data.data.countinfo.all_zong_lv
            this.qiandan_lv = res.data.data.countinfo.all_qiandan_lv
            this.countinfo = res.data.data.countinfo
            if (res.data.data.list) {
              this.tableData = res.data.data.list
              this.visibleTable = false
            } else {
              this.visibleTable = false
            }
          } else {
            this.$message.error(res.data.error_msg)
            this.tableData = []
            this.visibleTable = false
          }
        })
        .catch(() => {
          this.visibleTable = false
          this.$message.error('访问错误，请刷新重试')
        })
    },
    // 排序
    sortHandle(column) {
      if (
        column.prop === 'fendan_lv' ||
        column.prop === 'zendan_lv' ||
        column.prop === 'zongx_lv' ||
        column.prop === 'qiandan_lv'
      ) {
        let num = ''
        if (column.prop == null) {
          num = ''
        } else {
          num = column.order === 'ascending' ? '1' : '2'
        }
        this.fen_lv = column.prop === 'fendan_lv' ? num : ''
        this.zen_lv = column.prop === 'zendan_lv' ? num : ''
        this.zongx_lv = column.prop === 'zongx_lv' ? num : ''
        this.qiandan_lv = column.prop === 'qiandan_lv' ? num : ''
        this.fetchGetFenOrderList()
      }
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.member-fen-order {
  padding: 20px;
  .content-table {
    border-top: 0 !important;
  }
  .el-date-editor {
    .el-range-separator {
      padding: 0;
    }
  }
  .tip {
    color: red;
  }
  .search {
    background: #fff;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
  }

  .qian-main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
    background-color: #fff;
  }

  .el-pagination {
    text-align: center;
    margin-top: 20px;
  }

  .el-dialog__header {
    border-bottom: 1px dashed #ccc;
  }

  .dia_table {
    width: 100%;
  }

  .dia_table td {
    line-height: 2.5;
  }

  .fix_letter_spance {
    letter-spacing: 3px;
  }

  .fl {
    float: left;
  }

  .mr20 {
    margin-right: 20px;
  }

  .mt4 {
    margin-top: 4px;
  }
}
</style>
