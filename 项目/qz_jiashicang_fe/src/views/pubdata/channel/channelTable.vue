<template>
  <div class="channel-table">
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
          />
        </el-form-item>
        <template v-if="titleTabIndex === 1">
          <el-form-item v-if="tab === 2" label="城市：">
            <el-select v-model="city_id" size="small" filterable placeholder="请选择城市" clearable>
              <el-option v-for="item in cityList" :key="item.city_id" :label="item.value" :value="item.city_id" />
            </el-select>
          </el-form-item>
          <el-form-item label="部门：">
            <el-select v-model="dept_id" size="small" filterable placeholder="请选择部门" clearable>
              <el-option v-for="item in deptList" :key="item.id" :label="item.name" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="渠道组：">
            <el-select v-model="group_id" filterable clearable size="small" placeholder="请选择渠道组">
              <el-option
                v-for="item in groupList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="渠道名称：">
            <el-select
              v-model="src"
              filterable
              remote
              reserve-keyword
              clearable
              placeholder="请输入渠道名称"
              :remote-method="remoteMethod"
              :loading="srcLoading"
              size="small"
            >
              <el-option
                v-for="item in srcList"
                :key="item.src"
                :label="item.name"
                :value="item.src"
              />
            </el-select>
          </el-form-item>
        </template>

        <template v-if="titleTabIndex === 2">
          <!-- <el-form-item label="发单位置组：">
            <el-select v-model="group_id" filterable clearable size="small" placeholder="请选择发单位置组">
              <el-option
                v-for="item in positionList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </el-form-item> -->
          <el-form-item label="发单位置来源：">
            <el-select
              v-model="source"
              filterable
              remote
              reserve-keyword
              placeholder="请输入发单位置来源"
              :remote-method="remoteMethodFd"
              :loading="srcFdLoading"
              size="small"
            >
              <el-option
                v-for="item in srcFdList"
                :key="item.src"
                :label="item.name"
                :value="item.src"
              />
            </el-select>
          </el-form-item>
        </template>

        <el-form-item>
          <el-button round plain class="reset-btn" size="mini" @click="resetForm">重置</el-button>
          <el-button round plain class="search-btn" size="mini" @click="onSubmit">查询</el-button>
          <el-button v-loading="exportLoading" round plain class="export-btn ml-10 plr20" size="small" @click="exportExcel">导出</el-button>
        </el-form-item>
        <el-form-item class="fr">
          <el-radio-group v-model="tab" size="small">
            <el-radio-button v-if="titleTabIndex === 1" name="by" :label="1">按渠道</el-radio-button>
            <el-radio-button v-if="titleTabIndex === 1" name="by" :label="2">按城市</el-radio-button>
            <el-radio-button v-if="titleTabIndex === 2" name="by" :label="3">按发单位置</el-radio-button>
            <el-radio-button name="by" :label="4">按日期</el-radio-button>
          </el-radio-group>
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
        :default-sort="{prop: tab === 4?'date':'csos_fendan', order: 'descending'}"
        @sort-change="sortChange"
      >
        <template v-if="titleTabIndex === 1 && (tab === 1 || tab === 2)">
          <el-table-column prop="name" align="center" label="渠道名称" />
          <el-table-column prop="group_name" align="center" label="渠道组" />
        </template>
        <el-table-column v-if="tab === 3 && titleTabIndex === 2" prop="name" align="center" label="发单位置来源" />
        <el-table-column v-if="tab === 4" prop="date" align="center" label="发单时间" />

        <el-table-column :sortable="tab !==4 ? 'custom' : false" :sort-orders="['descending','ascending']" prop="fadan" align="center" label="发单量" />
        <el-table-column prop="fendan" align="center" label="分单量" />
        <el-table-column :sortable="tab !==4 ? 'custom' : false" :sort-orders="['descending','ascending']" prop="csos_fendan" align="center" label="实际分单量" />
        <el-table-column prop="csos_zendan" align="center" label="实际赠单量" />
        <el-table-column prop="csos_all" align="center" label="总单量" />
        <el-table-column prop="csos_fendan_lv_show" align="center" label="实际分单率" />
        <el-table-column :sortable="tab !==4 ? 'custom' : false" :sort-orders="['descending','ascending']" prop="liangfang" align="center" label="量房量" />
        <el-table-column prop="liangfang_lv_show" align="center" label="量房率" />
        <el-table-column prop="csos_fen_lflv_show" align="center" label="分单量房率" />
        <el-table-column prop="csos_zen_lflv_show" align="center" label="赠单量房率" />
        <el-table-column :sortable="tab !==4 ? 'custom' : false" :sort-orders="['descending','ascending']" prop="qiandan" align="center" label="签单量" />
        <el-table-column prop="qiandan_lv_show" align="center" label="签单率" />
        <el-table-column prop="csos_fen_qdlv_show" align="center" label="分单签单率" />
        <el-table-column prop="csos_zen_qdlv_show" align="center" label="赠单签单率" />
        <el-table-column prop="lf_qiandan_lv_show" align="center" label="量房签单率" />
        <el-table-column prop="round_num" align="center" label="补轮单" />
        <el-table-column prop="round_lv_show" align="center" label="补轮率" />
        <el-table-column prop="qiandan_amount" align="center" label="签单金额（万元）" />
        <el-table-column prop="round_amount" align="center" label="补轮金额（元）" />
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
export default {
  name: 'ChannelTable',
  props: {
    itemIndex: {
      type: Number,
      default: 1
    }
  },
  data() {
    return {
      titleTabIndex: '',
      currentPage: 1, // 当前页数
      total: 0, // 总条数
      pageSize: 20, // 总共页数
      tableData: [], // 表格数据
      tab: 1,
      city_id: [], // 城市id
      dept_id: '', // 部门id
      group_id: '', // 渠道组
      src: '', // 渠道名称
      date_begin: '', // 发单开始时间
      date_end: '', // 发单结束时间
      source_group_id: '', // 发单位置组
      source: '', // 发单位置标识
      tableKey: 0,
      date: '', // 控件时间
      sort_field: '',
      sort_rule: '',
      cityList: [], // 城市列表
      deptList: [], // 渠道部门列表
      groupList: [], // 渠道来源组列表
      srcList: [], // 渠道来源组列表
      srcFdList: [], // 发单位置来源列表
      srcLoading: false,
      srcFdLoading: false,
      tableLoading: false,
      exportLoading: false
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
    itemIndex: {
      handler(val) {
        this.titleTabIndex = val
        if (val === 1) {
          this.tab = 1
        } else {
          this.tab = 3
        }
      },
      immediate: true
    },
    tab(val) {
      this.tableKey++
      this.currentPage = 1 // 当前页数
      this.total = 0 // 当前页数
      this.tableData = []
      this.resetForm()
      if (val !== 2) {
        this.getList()
      }
    }
  },
  created() {
    this.getList()
    this.getCityList()
    this.getOptionsList(1)
    this.getOptionsList(2)
  },
  methods: {
    remoteMethod(query) {
      if (query !== '') {
        this.srcFdLoading = true
        setTimeout(() => {
          this.srcFdLoading = false
          this.srcSearch(query, 1)
        }, 200)
      } else {
        this.srcList = []
      }
    },
    remoteMethodFd(query) {
      if (query !== '') {
        this.srcFdLoading = true
        setTimeout(() => {
          this.srcFdLoading = false
          this.srcSearch(query, 2)
        }, 200)
      } else {
        this.srcFdList = []
      }
    },
    sortChange(data) { // 排序
      this.sort_field = data.prop
      this.sort_rule = data.order === 'ascending' ? 'asc' : 'desc'
      this.getList()
    },
    getSubmitdata() {
      const that = this
      const obj = {}
      if (that.titleTabIndex === 1) {
        if (that.tab === 1 || that.tab === 2) {
          obj.tab = that.tab
          if (that.tab === 2) {
            obj.city_id = that.city_id
          }
        }
        obj.dept_id = that.dept_id
        obj.group_id = that.group_id
        obj.src = that.src
      } else {
        obj.group_id = that.source_group_id
        obj.source = that.source
      }
      obj.date_begin = that.date_begin
      obj.date_end = that.date_end
      obj.sort_field = that.sort_field
      obj.sort_rule = that.sort_rule
      obj.page = that.currentPage
      obj.limit = that.pageSize
      return obj
    },
    onSubmit() {
      const that = this
      if (that.tab === 2 && !that.city_id) {
        that.$message.warning('请选择城市')
        return
      }
      that.getList()
    },
    resetForm() {
      const that = this
      that.date = ''
      that.group_id = ''
      that.source = ''
      that.dept_id = ''
      that.group_id = ''
      that.src = ''
      that.sort_field	 = ''
      that.sort_rule	 = ''
    },
    getList() {
      this.tableLoading = true
      const obj = this.getSubmitdata()
      if (this.titleTabIndex === 1) {
        if (this.tab === 1 || this.tab === 2) {
          this.$apis.PUBDATA.srcDetailed(obj).then(res => {
            this.handleData(res)
          })
        } else {
          this.$apis.PUBDATA.srcDateDetailed(obj).then(res => {
            this.handleData(res)
          })
        }
      } else {
        if (this.tab === 3) {
          this.$apis.PUBDATA.sourceDetailed(obj).then(res => {
            this.handleData(res)
          })
        } else {
          this.$apis.PUBDATA.sourceDateDetailed(obj).then(res => {
            this.handleData(res)
          })
        }
      }
    },
    handleData(data) {
      this.tableLoading = false
      if (data.error_code === 0) {
        this.tableData = data.data.list
        this.total = data.data.page.total_number
        this.currentPage = data.data.page.page_current
        this.date = [data.data.options.date_begin, data.data.options.date_end]
      } else {
        this.$message.error(data.error_msg)
        this.tableData = []
      }
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
    getOptionsList(val) {
      this.$apis.PUBDATA.srcOptions({ actfrom: val }).then(res => {
        if (res.error_code === 0) {
          if (val === 1) {
            this.deptList = res.data.department_list
            this.groupList = res.data.group_list
          } else {
            this.positionList = res.data.group_list
          }
        } else {
          this.deptList = []
          this.groupList = []
        }
      })
    },
    // 页面变化
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    // 翻页
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    // 检索来源
    srcSearch(val, num) {
      const obj = {
        actfrom: num,
        keyword: val
      }
      this.$apis.PUBDATA.srcSearch(obj).then((res) => {
        console.log(res)
        if (res.error_code === 0) {
          if (num === 1) {
            this.srcList = res.data.list
          } else {
            this.srcFdList = res.data.list
          }
        }
      })
    },
    // 导出
    exportExcel() {
      const that = this
      if (that.tab === 2 && !that.city_id) {
        that.$message.warning('请选择城市')
        return
      }
      that.exportLoading = true
      var tHeader = [
        '发单量',
        '分单量',
        '实际分单量',
        '实际赠单量',
        '总单量',
        '实际分单率',
        '量房量',
        '量房率',
        '分单量房率',
        '赠单量房率',
        '签单量',
        '签单率',
        '分单签单率',
        '赠单签单率',
        '量房签单率',
        '补轮单',
        '补轮率',
        '签单金额（万元）',
        '补轮金额（元）'
      ]
      var filterVal = [
        'fadan',
        'fendan',
        'csos_fendan',
        'csos_zendan',
        'csos_all',
        'csos_fendan_lv_show',
        'liangfang',
        'liangfang_lv_show',
        'csos_fen_lflv_show',
        'csos_zen_lflv_show',
        'qiandan',
        'qiandan_lv_show',
        'csos_fen_qdlv_show',
        'csos_zen_qdlv_show',
        'lf_qiandan_lv_show',
        'round_num',
        'round_lv_show',
        'qiandan_amount',
        'round_amount'
      ]
      if (this.titleTabIndex === 1 && (this.tab === 1 || this.tab === 2)) {
        tHeader.unshift('渠道名称', '渠道组')
        filterVal.unshift('name', 'group_name')
      }
      if (this.titleTabIndex === 2 && this.tab === 3) {
        tHeader.unshift('发单位置来源')
        filterVal.unshift('name')
      }
      if (this.tab === 4) {
        tHeader.unshift('发单时间')
        filterVal.unshift('date')
      }
      this.fetchPerformExport(() => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '渠道数据明细')
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]))
    },
    fetchPerformExport(cb) {
      const obj = this.getSubmitdata()
      obj.export = 1
      if (this.titleTabIndex === 1) {
        if (this.tab === 1 || this.tab === 2) {
          this.$apis.PUBDATA.srcDetailed(obj).then((res) => {
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
        } else {
          this.$apis.PUBDATA.srcDateDetailed(obj).then((res) => {
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
        }
      } else {
        if (this.tab === 3) {
          this.$apis.PUBDATA.sourceDetailed(obj).then((res) => {
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
        } else {
          this.$apis.PUBDATA.sourceDateDetailed(obj).then((res) => {
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
        }
      }
    },
    // exportHandleData(){

    // },
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
<style rel=" stylesheet/scss" lang="scss" scoped>
.channel-table{
  line-height: 1.5;
}
</style>
