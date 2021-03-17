<template>
  <div class="all-statistics">
    <h3 class="title">数据总览</h3>
    <div class="main">
      <div class="top">
        <h4>业绩总览</h4>
        <el-date-picker
          v-model="dateDate"
          type="daterange"
          value-format="yyyy-MM-dd"
          format="yyyy-MM-dd"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          class="date"
          @change="dateCheng"
        />
        <el-row>
          <el-col :span="18">
            <div>
              <h6>今日业绩 <el-button type="text" class="fr" @click="todayPerformance">单日业绩查询</el-button></h6>
              <div class="top-today"><span class="today-num">{{ todayNum }}</span>万元 <span class="fr">合计{{ totalTodoy }}万元</span></div>
              <div style="height:400px;width:100%">
                <div id="today" style="width: 100%;height:400px;" />
              </div>
            </div>
          </el-col>
          <el-col :span="6">
            <el-row class="mb30 mt60">
              <el-col :span="12">
                <h5>小报备</h5>
                <div class="num">
                  {{ paymentCount }}
                  <span>次</span>
                </div>
              </el-col>
              <el-col :span="12">
                <h5>大报备</h5>
                <div class="num">
                  {{ reportCount }}
                  <span>次</span>
                </div>
              </el-col>
            </el-row>
            <el-row class="mb30">
              <el-col :span="12">
                <h5>上会员</h5>
                <div class="num">
                  {{ memberCount }}
                  <span>次</span>
                </div>
              </el-col>
              <el-col :span="12">
                <h5>新会员</h5>
                <div class="num">
                  {{ newCount }}
                  <span>次</span>
                </div>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </div>
      <div>
        <h4>会员总览</h4>
        <el-row>
          <el-col :span="8">
            <div id="vip-type" class="pie" />
          </el-col>
          <el-col :span="8">
            <div id="vip-num" class="pie" />
          </el-col>
          <el-col :span="8">
            <div id="vip" class="pie" />
          </el-col>
        </el-row>
      </div>
      <div>
        <h4>团队总览</h4>
        <div>
          <h6>团队业绩统计</h6>
          <div id="team" style="height:400px;width:100%;" />
        </div>
        <div class="team-top">
          <el-row>
            <el-col :span="12">
              <div id="team-type" class="pie" />
            </el-col>
            <el-col :span="12">
              <div id="team-mode" class="pie" />
            </el-col>
          </el-row>
        </div>
      </div>
    </div>
    <el-dialog v-loading="loading" title="单日业绩数据" :center="true" :visible.sync="dialogTableVisible" width="30%">
      <div class="picker">
        <el-date-picker
          v-model="todayDate"
          type="date"
          :clearable="false"
          :editable="false"
          value-format="yyyy-MM-dd"
          placeholder="选择日期"
          size="mini"
          style="width:160px;color:#c00"
          @change="todayChange"
        />
      </div>
      <el-row v-for="item in todayDataTeamList" :key="item.current_id" class="mb30">
        <el-col :span="12">{{ item.current_name }}总业绩 </el-col>
        <el-col :span="12">{{ item.achievement }} </el-col>
      </el-row>
      <el-row v-if="refundAchievementTotal === '有无数据都不显示'" class="mb30">
        <el-col :span="12">退款业绩</el-col>
        <el-col :span="12">{{ refundAchievementTotal }} </el-col>
      </el-row>
      <el-row class="mb30">
        <el-col :span="12">合计总业绩</el-col>
        <el-col :span="12">{{ achievementTotal }} </el-col>
      </el-row>
      <el-row class="mb30">
        <el-col :span="12">最高个人业绩金额</el-col>
        <el-col :span="12">{{ personMaxAchievement }} </el-col>
      </el-row>
      <el-row class="mb30">
        <el-col :span="12">最高业绩人</el-col>
        <el-col :span="12">
          <span v-for="item in todayDataPersonList" :key="item.saler_id" class="mr10">{{ !!item?(item.saler_name?item.saler_name:item.saler_user):'' }}</span>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>

<script>
import { getperformancechart, getotherchart, getmemberchart, getteamindicatorschart, getToday } from '@/api/performanceStatistics.js'
var echarts = require('echarts/lib/echarts') // 引入基本模板
require('echarts/lib/chart/bar') // 引入柱状图组件
require('echarts/lib/chart/line') // 引入柱状图组件
require('echarts/lib/chart/pie') // 引入柱状图组件
// 引入提示框和title组件
require('echarts/lib/component/title')
require('echarts/lib/component/tooltip')
require('echarts/lib/component/legend')
require('echarts/lib/component/dataset')
export default {
  name: 'AllStatistics',
  data() {
    return {
      loading: true,
      paymentCount: '', // 小报备
      reportCount: '', // 大报备
      newCount: '', // 新会员
      unNewCount: '', // 老会员
      memberCount: '', // 上会员
      todayNum: '', // 今日
      totalTodoy: ' ', // 合计
      todayDate: '', // 今日业绩查询时间
      todayDataTeamList: '', // 今日业绩团队业绩列表
      todayDataPersonList: '', // 今日业绩最高业绩人
      achievementTotal: '', // 合计总业绩
      personMaxAchievement: '', // 最高个人业绩金额
      refundAchievementTotal: '', // 退款业绩
      dialogTableVisible: false,
      option: {
        tooltip: {
          trigger: 'axis',
          formatter: '{b} <br/> 业绩 : {c}'
        },
        xAxis: {
          type: 'category',
          data: []
        },
        yAxis: {
          type: 'value'
        },
        series: {
          data: [],
          type: 'line'
        }
      },
      vipData: {
        title: {
          text: '新/老会员占比',
          left: 'center',
          textStyle: {
            fontWeight: 'normal'
          }
        },
        legend: {
          data: []
        },
        series: {
          type: 'pie',
          radius: '55%',
          center: ['50%', '50%'],
          data: [],
          label: {
            formatter: '{b}: ' + '\n' + '{@2012}占({d}%)'
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      },
      vipTypeData: {
        title: {
          text: '合作类型占比',
          left: 'center',
          textStyle: {
            fontWeight: 'normal'
          }
        },
        legend: { data: [] },
        series: {
          name: '合作类型占比',
          type: 'pie',
          radius: '50%',
          center: ['50%', '50%'],
          data: [],
          label: {
            formatter: '{b}: ' + '\n' + '{@2012}占({d}%)'
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      },
      vipNumData: {
        title: {
          text: '合作类型金额占比（单位：万元）',
          left: 'center',
          textStyle: {
            fontWeight: 'normal'
          }
        },
        legend: { data: [] },
        series: {
          name: '合作类型金额占比（单位：万元）',
          type: 'pie',
          radius: '50%',
          center: ['50%', '50%'],
          data: [],
          label: {
            formatter: '{b}: ' + '\n' + '{@2012}占({d}%)'
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      },
      teamData: {
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          data: []
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        xAxis: {
          type: 'category',
          data: []
        },
        yAxis: {
          type: 'value', name: '单位：万元', nameLocation: 'middle', nameGap: 50, min: 0
        },
        series: []
      },
      teamType: {
        title: {
          text: '收款方名称',
          left: 'center',
          textStyle: {
            fontWeight: 'normal'
          }
        },
        legend: { data: [] },
        series: {
          type: 'pie',
          radius: '40%',
          center: ['50%', '50%'],
          data: [],
          label: {
            formatter: '{b}: ' + '\n' + '{@2012}占({d}%)'
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      },
      teamMode: {
        title: {
          text: '收款类型占比',
          left: 'center',
          textStyle: {
            fontWeight: 'normal'
          }
        },
        legend: { data: [] },
        series: {
          type: 'pie',
          radius: '40%',
          center: ['50%', '50%'],
          data: [],
          label: {
            formatter: '{b}: ' + '\n' + '{@2012}占({d}%)'
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      },
      pickerMinDate: '',
      dateDate: [],
      pickerOptions: {
        onPick: ({ maxDate, minDate }) => {
          this.pickerMinDate = minDate.getTime()
          if (maxDate) {
            this.pickerMinDate = ''
          }
        },
        disabledDate: (time) => {
          if (this.pickerMinDate !== '') {
            const day30 = (30 - 1) * 24 * 3600 * 1000
            let maxTime = this.pickerMinDate + day30
            if (maxTime > new Date()) {
              maxTime = new Date()
            }
            return time.getTime() > maxTime
          }
          return time.getTime() > Date.now()
        }
      }
    }
  },
  created() {
    var now = new Date()
    var year = now.getFullYear() // 得到当前年份
    var month = now.getMonth() + 1 // 默认得到月份是上一个月，如果当前是3月，这个值为2月
    var day = now.getDate()// 得到当天
    month = month.toString().padStart(2, '0')	// 当小于10时，显示为01.02.03
    day = day.toString().padStart(2, '0')	// 当小于10时，显示为01.02.03
    this.startdate = `${year}-${month}-01`// 拼接日期
    this.enddate = `${year}-${month}-${day}`
    this.dateDate[0] = this.startdate
    this.dateDate[1] = this.enddate
  },
  mounted() {
    this.getDataList()
  },
  methods: {
    drawChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('today'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawVipChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('vip'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawVipTypeChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('vip-type'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawVipNumChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('vip-num'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawTeamChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('team'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawTeamTypeChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('team-type'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    drawTeamModeChart(obj) {
      this.$nextTick(function() {
        // 基于准备好的dom，初始化echarts实例
        const myChart = echarts.init(document.getElementById('team-mode'))
        // 绘制图表
        myChart.setOption(obj, true)
      })
    },
    getDataList() {
      const that = this
      const val = {}
      if (that.dateDate) {
        val.begin = that.dateDate[0]
        val.end = that.dateDate[1]
      }
      getperformancechart(val).then(res => {
        // console.log('今日', res)
        if (res.status === 200 && res.data.error_code === 0) {
          that.todayNum = res.data.data.now
          that.totalTodoy = res.data.data.achievement
          that.option.xAxis.data = []
          let chartList = []
          chartList = Object.values(res.data.data.chart)
          that.option.series.data = []
          chartList.forEach(item => {
            that.option.series.data.push(item.achievement)
            that.option.xAxis.data.push(item.day + '(' + item.week + ')')
          })
          that.drawChart(that.option)
        } else {
          that.option.xAxis.data = []
          that.option.series.data = []
          that.drawChart(that.option)
          this.$message.error(res.data.error_msg)
        }
      })
      getotherchart(val).then(res => {
        // console.log('其他', res)
        if (res.status === 200 && res.data.error_code === 0) {
          that.paymentCount = res.data.data.payment_count
          that.reportCount = res.data.data.report_count
          that.newCount = res.data.data.new_count
          that.unNewCount = res.data.data.un_new_count
          that.memberCount = res.data.data.member_count
          that.vipData.series.data = []
          that.vipData.series.data.push({ value: that.newCount, name: '新会员' })
          that.vipData.series.data.push({ value: that.unNewCount, name: '老会员' })
          // console.log('this.vipData', this.vipData)
          this.drawVipChart(this.vipData)
        } else {
          that.vipData.series.data = []
          // this.$message.error(res.data.error_msg)
          this.drawVipChart(this.vipData)
        }
      })
      getmemberchart(val).then(res => {
        // console.log('会员', res)
        if (res.status === 200 && res.data.error_code === 0) {
          that.vipTypeData.series.data = []
          that.vipNumData.series.data = []
          let dataTypeList = []
          let dataMoneyList = []
          dataTypeList = Object.values(res.data.data.pie_type)
          dataMoneyList = Object.values(res.data.data.pie_money)
          dataTypeList.forEach((item) => {
            that.vipTypeData.series.data.push({ value: item.count, name: item.type_name })
          })
          dataMoneyList.forEach((item) => {
            that.vipNumData.series.data.push({ value: item.money, name: item.type_name })
          })
          this.drawVipTypeChart(this.vipTypeData)
          this.drawVipNumChart(this.vipNumData)
        } else {
          that.vipTypeData.series.data = []
          that.vipNumData.series.data = []
          // this.$message.error(res.data.error_msg)
          this.drawVipTypeChart(this.vipTypeData)
          this.drawVipNumChart(this.vipNumData)
        }
      })
      getteamindicatorschart(val).then(res => {
        // console.log('团队', res)
        if (res.status === 200 && res.data.error_code === 0) {
          that.teamData.legend.data = []
          that.teamData.xAxis.data = []
          that.teamData.series = []
          that.teamData.legend.data = res.data.data.legend
          that.teamData.xAxis.data = []
          res.data.data.date.forEach((item) => {
            that.teamData.xAxis.data.push(item.date + '(' + item.week + ')')
          })
          res.data.data.series.forEach((item) => {
            item.type = 'line'
            that.teamData.series.push(item)
          })
          that.teamType.series.data = []
          that.teamMode.series.data = []
          let dataAccountList = []
          let dataTypeList = []
          dataAccountList = Object.values(res.data.data.pie.payee)
          dataTypeList = Object.values(res.data.data.pie.type)
          dataAccountList.forEach((item) => {
            that.teamType.series.data.push({ value: item.count, name: item.name })
          })
          dataTypeList.forEach((item) => {
            that.teamMode.series.data.push({ value: item.count, name: item.name })
          })
          this.drawTeamChart(this.teamData)
          this.drawTeamTypeChart(this.teamType)
          this.drawTeamModeChart(this.teamMode)
        } else {
          that.teamData.legend.data = []
          that.teamData.xAxis.data = []
          that.teamData.series = []
          that.teamType.series.data = []
          that.teamMode.series.data = []
          // this.$message.error(res.data.error_msg)
          this.drawTeamChart(this.teamData)
          this.drawTeamTypeChart(this.teamType)
          this.drawTeamModeChart(this.teamMode)
        }
      })
    },
    dateCheng() {
      this.getDataList()
    },
    // 今日业绩查询
    getTodayPerformance(val) {
      const that = this
      const obj = {}
      obj.date = val
      getToday(obj).then(res => {
        // console.log('今日业绩查询', res.data.data)
        if (res.status === 200 && res.data.error_code === 0) {
          that.todayDate = res.data.data.date
          that.todayDataTeamList = res.data.data.team_list
          that.todayDataPersonList = res.data.data.person_list
          that.achievementTotal = res.data.data.achievement_total
          that.personMaxAchievement = res.data.data.person_max_achievement
          that.refundAchievementTotal = res.data.data.refund_achievement_total
        }
      })
    },
    todayPerformance() {
      this.dialogTableVisible = true
      this.getTodayPerformance()
    },
    todayChange() {
      this.getTodayPerformance(this.todayDate)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.all-statistics {
  padding: 0 20px;
  .fr{
    float: right;
  }
  .fl{
    float: left;
  }
  .mr10{
    margin-right: 10px;
  }
  .mb30 {
    margin-bottom: 30px;
  }
  .mt60 {
    margin-top: 60px;
  }
  .title {
    font-weight: normal;
  }
  .el-date-editor {
    .el-range-separator {
      padding: 0;
    }
  }
  .el-dialog__body{
    padding-top: 10px;
  }
  .el-input__prefix{
    display: none;
  }
  .el-input__inner{
    border: none;
    color: #409EFF;
  }
  .picker{
    position: relative;
    right:20px;
    top: -45px;
    text-align: right;
  }
  .main {
    background: #fff;
    padding: 10px 20px;
    h4 {
      font-weight: normal;
    }
    .pie{
      height:550px;
      width:550px;
      margin:0 auto
    }
    .top {
      position: relative;
      .top-today{
          padding: 0 100px 0 30px;
          .today-num{
            font-size: 20px;
          }
        }
      .date {
        position: absolute;
        right: 0;
        top: 0px;
      }
      h5 {
        font-weight: normal;
        font-size: 20px;
      }
      .num {
        font-size: 50px;
        span {
          font-size: 16px;
        }
      }
      h6 {
        font-weight: normal;
        padding-right: 100px;
      }
    }
    .team-top{
      padding-top: 40px;
    }
    h6 {
        font-weight: normal;
        font-size: 14px;
      }
  }
}
</style>
