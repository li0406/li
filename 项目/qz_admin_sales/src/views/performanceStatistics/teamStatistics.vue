<template>
  <div class="team-statistics">
    <div class="title">团队业绩统计</div>
    <div class="main">
      <el-form :inline="true" :model="formData" class="demo-form-inline">
        <el-date-picker v-model="startTime" type="month" value-format="yyyyMM" format="yyyy-MM" placeholder="开始月份" />
        <span>-</span>
        <el-date-picker v-model="endTime" type="month" value-format="yyyyMM" format="yyyy-MM" placeholder="结束月份" />
        <el-form-item label="所属部门：">
          <el-select v-model="teamId" placeholder="请选择">
            <el-option
              v-for="item in teamList"
              :key="item.current_id"
              :label="item.current_name"
              :value="item.current_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="启用状态：">
          <el-select v-model="status" clearable placeholder="请选择">
            <el-option label="启用" value="1" />
            <el-option label="禁用" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" @click="getDataList">查询</el-button>
        </el-form-item>
      </el-form>
      <h4 class="chart-name">{{ title }}</h4>
      <div style="height:400px;overflow: hidden;position: relative;">
        <div
          id="main"
          :style="{'top': (activeName === '0'?'0':'400px')}"
          style="width: 100%;height:400px;position: absolute;left:0"
        />
        <div
          id="main2"
          :style="{'top': (activeName === '1'?'0':'400px')}"
          style="width: 100%;height:400px;position: absolute;left:0"
        />
      </div>
      <el-tabs v-model="activeName" tab-position="bottom" class="chart-tabs">
        <el-tab-pane label="按指标统计" name="0" />
        <el-tab-pane label="按业绩统计" name="1" />
      </el-tabs>
      <div v-if="infoTable" class="table">
        <h3>团队数据</h3>
        <el-button v-loading="exportLoading" type="success" class="export" @click="handleReport">导出</el-button>
        <el-tabs v-model="activeTableName">
          <el-tab-pane
            v-for="(item,key,index) in tableList"
            :key="index"
            :label="tableDateList[index]"
            :name="index+''"
          >
            <el-table :data="item" border style="width: 100%;">
              <el-table-column prop="top_name" label="部门" />
              <el-table-column prop="team_name" label="团队名称" />
              <el-table-column prop="team_leader" label="负责人" />
              <el-table-column prop="indicators" label="团队指标">
                <template slot-scope="scope">
                  <span v-if="scope.row.indicators === '0.00' || scope.row.indicators === 0.00">-</span>
                  <span v-else>{{ scope.row.indicators }}</span>
                </template>
              </el-table-column>
              <el-table-column label="团队业绩">
                <template slot-scope="scope">
                  <span v-if="scope.row.performance === 0">-</span>
                  <span v-else>{{ scope.row.performance }}</span>
                </template>
              </el-table-column>
              <el-table-column label="完成率">
                <template slot-scope="scope">
                  <div v-if="scope.row.percentage < 100" class="percentage">
                    <el-progress
                      :show-text="false"
                      :text-inside="true"
                      :stroke-width="18"
                      :percentage="scope.row.percentage < 100 ? scope.row.percentage : 100"
                      status="success"
                    />
                    <span>{{ scope.row.percentage }}%</span>
                  </div>

                  <div v-else class="percentage">
                    <el-progress
                      :show-text="false"
                      :text-inside="true"
                      :stroke-width="18"
                      :percentage="100"
                      status="success"
                    />
                    <span>{{ scope.row.percentage }}%</span>
                  </div>

                </template>
              </el-table-column>
              <el-table-column prop="nofinish_amount" label="未完成金额" />
              <el-table-column label="超出时间进度">
                <template slot-scope="scope">
                  <div v-if="scope.row.percentdate_over < 0" class="percentage">
                    <el-progress
                      :show-text="false"
                      :text-inside="true"
                      :stroke-width="18"
                      :percentage=" scope.row.percentdate_over <= -100 ? 100 : -scope.row.percentdate_over"
                      status="exception"
                    />
                    <span>{{ scope.row.percentdate_over }}%</span>
                  </div>
                  <div v-else class="percentage">
                    <el-progress
                      :show-text="false"
                      :text-inside="true"
                      :stroke-width="18"
                      :percentage="scope.row.percentdate_over>100? 100 : scope.row.percentdate_over"
                      status="success"
                    />
                    <span>{{ scope.row.percentdate_over }}%</span>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
  </div>
</template>

<script>
import { getTeamList, getTeamChart } from '@/api/performanceStatistics.js'
const echarts = require('echarts/lib/echarts') // 引入基本模板
import { export_json_to_excel } from '@/excel/Export2Excel'
require('echarts/lib/chart/bar') // 引入柱状图组件
// 引入提示框和title组件
require('echarts/lib/component/tooltip')
require('echarts/lib/component/title')
require('echarts/lib/component/legend')
require('echarts/lib/component/dataset')

export default {
  name: 'TeamStatistics',
  data() {
    return {
      formData: {
        name: '',
        year: ''
      },
      title: '',
      startTime: '',
      endTime: '',
      activeName: '0',
      status: '1',
      activeTableName: '',
      teamList: [],
      tableData: [],
      teamId: '',
      loading: false,
      dialogVisible: false,
      dialogData: {},
      tableList: [],
      tableDateList: [],
      dateList: [],
      dataObj: {
        legend: {},
        tooltip: {
          formatter: function(params) {
            let res = '<p>' + params.seriesName + '  ' + params.name + '</p>'
            // res += '<p>业绩指标:' + params.data[0] + '</p>'
            res += '<p>完成百分比:' + params.data[params.seriesIndex + 1] + '%</p>'
            return res
          }
        },
        dataset: {
          source: []
        },
        xAxis: [{ type: 'category', gridIndex: 0 }],
        yAxis: [{ type: 'value', name: '单位：%', nameLocation: 'end' }],
        grid: {
          x: 80,
          y: 45,
          borderWidth: 1
        },
        series: []
      },
      dataObj1: {
        legend: {},
        tooltip: {
          formatter: function(params) {
            let res = '<p>' + params.seriesName + '  ' + params.name + '</p>'
            res += '<p>完成业绩:' + params.data[params.seriesIndex + 1] + '(万元)</p>'
            return res
          }
        },
        dataset: {
          source: []
        },
        xAxis: [{ type: 'category', gridIndex: 0 }],
        yAxis: [{ type: 'value', name: '单位：万元', nameLocation: 'end' }],
        grid: {
          x: 80,
          y: 45,
          borderWidth: 1
        },
        series: []
      },
      exportLoading: false,
      exportData: [],
      infoTable: true
    }
  },
  created() {
    var now = new Date()
    var year = now.getFullYear() // 得到当前年份
    var was = '' // 当前年份
    var month = now.getMonth() + 1 // 默认得到月份是上一个月，如果当前是3月，这个值为2月
    var lastMonth = '' // 前3个月
    if (month < 3)	{ // 如果当前是1月，则获取到的数据为12月，年份减一
      was = year - 1
      lastMonth = month + 10
    } else {
      was = year
      lastMonth = month - 2
    }
    month = month.toString().padStart(2, '0')	// 当小于10时，显示为01.02.03
    lastMonth = lastMonth.toString().padStart(2, '0')	// 当小于10时，显示为01.02.03
    this.startTime = `${was}${lastMonth}`// 拼接日期
    this.endTime = `${year}${month}` // 当前日期
    this.getDepartmentList()
  },
  mounted() {
    // this.drawChart(this.dataObj, this.dataObj1)
  },
  methods: {
    drawChart(obj, obj1) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('main'))
        const myChart1 = echarts.init(document.getElementById('main2'))
        // 绘制图表
        myChart.setOption(obj, true)
        myChart1.setOption(obj1, true)
      })
    },
    getCurrentMonthFirst() {
      var date = new Date()
      date.setDate(1)
      var month = parseInt(date.getMonth() + 1)
      this.startTime = month
    },
    getDepartmentList() {
      const that = this
      const obj = {
        select_type: 1
      }
      getTeamList(obj).then(res => {
        if (res.data.error_code === 0) {
          that.teamList = res.data.data.list
          that.teamId = that.teamList[0].current_id
          that.title = that.teamList[0].current_name
          this.getDataList()
        } else {
          that.$message('部门数据获取失败')
        }
      })
    },
    getDataList() {
      const that = this
      const obj = {}
      if (that.startTime) {
        obj.begin = that.startTime
      }
      if (that.endTime) {
        obj.end = that.endTime
      }
      if (that.startTime && that.endTime) {
        if (that.endTime < that.startTime) {
          that.$message.error('结束日期不得小于开始日期')
          return false
        }
      }
      if (that.teamId) {
        obj.team_id = that.teamId
        let selectedWorkName = {}
        selectedWorkName = this.teamList.find((item) => { // 这里的chargingWorkNameList就是上面遍历的数据源
          return item.current_id === that.teamId // 筛选出匹配数据，是对应数据的整个对象
        })
        that.title = selectedWorkName.current_name
      }
      if (this.status) {
        obj.status = this.status
      }
      that.handleSearch(obj)
    },
    handleSearch(obj) {
      getTeamChart(obj).then(res => {
        if (res.status === 200 && res.data.error_code === 0 && res.data.data) {
          const table = res.data.data.table_date
          if (JSON.stringify(table) === '[]') {
            this.infoTable = false
          } else {
            this.infoTable = true
          }
          this.tableList = res.data.data.table
          this.tableDateList = res.data.data.table_date
          this.activeTableName = this.tableDateList.length - 2 + ''
          this.dateList = res.data.data.date
          this.dataObj.dataset.source = []
          this.dataObj1.dataset.source = []
          this.dataObj.series = []
          this.dataObj1.series = []
          let sourceOne = []
          sourceOne.push('product')
          sourceOne = sourceOne.concat(res.data.data.date)
          this.dataObj.dataset.source.push(sourceOne)
          this.dataObj1.dataset.source.push(sourceOne)
          res.data.data.chart.forEach((item) => {
            this.dataObj.series.push({ type: 'bar', seriesLayoutBy: 'row', barMaxWidth: '40' })
            this.dataObj1.series.push({ type: 'bar', seriesLayoutBy: 'row', barMaxWidth: '40' })
            var acmArray = []
            var idtArray = []
            acmArray.push(item.team_name)
            idtArray.push(item.team_name)
            let dataList = []
            dataList = Object.values(item.date)
            dataList.forEach((t) => {
              acmArray.push(t.percentage)
              idtArray.push(t.performance)
            })
            this.dataObj.dataset.source.push(acmArray)
            this.dataObj1.dataset.source.push(idtArray)
          })
          this.drawChart(this.dataObj, this.dataObj1)
        } else {
          this.tableList = []
          this.tableDateList = []
          this.dateList = []
          this.dataObj.dataset.source = []
          this.dataObj1.dataset.source = []
          this.dataObj.series = []
          this.dataObj1.series = []
          this.drawChart(this.dataObj, this.dataObj1)
          if (res.data.data === null) {
            this.$message.error(res.data.error_msg)
          } else {
            this.$message.error(res.data.error_msg)
          }
        }
      })
    },
    // 导出
    handleReport() {
      this.exportLoading = true
      const tHeader = [
        '部门',
        '团队名称',
        '负责人',
        '团队指标',
        '团队业绩',
        '完成率',
        '未完成金额',
        '超出时间进度'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'top_name',
        'team_name',
        'team_leader',
        'indicators',
        'performance',
        'xpercentage',
        'nofinish_amount',
        'xpercentdate_over'
      ]
      this.fetchMemberExport('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '团队业绩统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    fetchMemberExport(val, cb) {
      const obj = {}
      if (this.startTime) {
        obj.begin = this.startTime
      }
      if (this.endTime) {
        obj.end = this.endTime
      }
      if (this.teamId) {
        obj.team_id = this.teamId
      }
      if (this.status) {
        obj.status = this.status
      }
      getTeamChart(obj).then(res => {
        if (res.status === 200 && res.data.error_code === 0 && res.data) {
          const tableList = res.data.data.table
          const index = Number(this.activeTableName)
          const exportData = Object.keys(tableList).map((value) => ({
            list: tableList[value]
          }))
          if (exportData[index].list.length > 0) {
            const list = exportData[index].list
            this.exportData = []
            list.forEach((item) => {
              item.xpercentage = item.percentage + '%'
              item.xpercentdate_over = item.percentdate_over + '%'
              this.exportData.push(item)
            })
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
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.team-statistics {
  padding: 0 20px 20px;
  .title {
    height: 50px;
    line-height: 50px;
  }
  .el-select {
    margin-top: 0;
  }
  .main {
    padding: 20px 20px 40px;
    background: #fff;
    border-top: 3px solid #999;
    .chart-name {
      padding-left: 10px;
      border-left: 2px solid green;
    }
    .table {
      padding: 10px 30px;
      .percentage{
        position: relative;
        span{
          position: absolute;
          right: 0;
          top: 0;
          color: #000;
          margin: 0 5px;
          line-height: 18px;
        }
      }
    }
  }
  .el-tabs__nav {
    width: 100%;
    display: flex;
    justify-content: flex-end;
  }
  .el-tabs__nav-wrap:after {
    background: none;
  }
  .el-tabs__active-bar {
    height: 0;
  }
  .el-date-editor {
    .el-range-separator {
      padding: 0;
    }
  }

  .chart-tabs {
    .el-tabs__nav {
      width: 100%;
      display: flex;
      justify-content: center;
    }
  }
  .el-tabs__nav{
    width: 90%;
  }
  .table{
    position: relative;
    .export{
      position: absolute;
      top: 70px;
      right: 30px;
      cursor: pointer;
      z-index: 999;
    }
  }
}
</style>
