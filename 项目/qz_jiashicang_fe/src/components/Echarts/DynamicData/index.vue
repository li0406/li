<template>
  <qzNewBox :border="0">
    <div class="tabs-div">
      <qzTab
        :tab-array="tabArray"
        @setTabParams="getTabParams"
      />
      <FilterMonth @setMonthParams="getMonthParams" />
    </div>
    <div class="waste-tip">
      <div v-show="showTable===1">
        <span>发单浪费量/发单量</span>&nbsp;
        <el-tooltip effect="dark" placement="right" content="发单浪费量：开站无会员发单、开站有会员未拨打订单、无开站发单等">
          <i class="el-icon-warning-outline" />
        </el-tooltip>
      </div>
      <div v-show="showTable===2">
        浪费分配数/理论分配数
      </div>
    </div>
    <div class="DynamicData">
      <div ref="DynamicData" v-loading="fawastebul" :style="{ width, height }" />
    </div>
  </qzNewBox>

</template>

<script>

export default {
  name: 'DynamicData',
  props: {
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '260px'
    }
  },
  data() {
    return {
      fawastebul: true,
      option: {},
      showTable: 1,
      monthTable: 1,
      tabArray: [
        { name: '发单浪费率', type: 1 },
        { name: '分单浪费率', type: 2 }
      ]
    }
  },
  mounted() {
    this.exchangeTable()
  },
  methods: {
    getMonthParams(val) {
      this.monthTable = val
      this.exchangeTable()
    },
    getTabParams(val) {
      this.showTable = val
      this.exchangeTable()
    },
    exchangeTable() {
      switch (this.showTable) {
        case 1:
          this.fawaste()
          break
        case 2:
          this.fenwaste()
          break
      }
    },
    fawaste() {
      const params = {
        tab_month: this.monthTable
      }
      this.$apis.SALES_MAIN.fawaste(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              symbolSize: 1,
              barWidth: 20, // 柱图宽度
              itemStyle: {
                normal: {
                  color: '#1890FF',
                  lineStyle: {
                    color: '#1890FF'
                  }
                }
              }
            },
            {
              symbolSize: 1,
              barWidth: 20, // 柱图宽度
              itemStyle: {
                normal: {
                  color: '#28A855',
                  lineStyle: {
                    color: '#28A855'
                  }
                }
              }
            },
            {
              symbolSize: 1,
              yAxisIndex: 2,
              itemStyle: {
                normal: {
                  color: '#5B8FF9',
                  lineStyle: {
                    color: '#5B8FF9'
                  }
                }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            grid: {
              left: '60px'
            },
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}<br />{a2}：{c2}%'
            },
            xAxis: [
              {
                axisTick: {
                  show: false
                },
                axisLine: {
                  lineStyle: {
                    color: '#004C86',
                    width: 1
                  }
                },
                type: 'category',
                // axisTick: {
                //   alignWithLabel: true
                // },
                axisLabel: {
                  color: '#fff',
                  textStyle: {
                    fontSize: 12,
                    padding: [5, 0, 0, 0]
                  }
                },
                data: res.data.xAxis
              }
            ],
            yAxis: [
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                // min: res.data.yAxis[0].min,
                // max: res.data.yAxis[0].max,
                // interval: res.data.yAxis[0].interval,
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              },
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              },
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                // min: res.data.yAxis[1].min,
                // max: res.data.yAxis[1].max,
                // interval: res.data.yAxis[0].interval,
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              }
            ],
            legend: this.$echartsconfig.legend,
            series
          }
          this.resetmyChart()
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    fenwaste() {
      const params = {
        tab_month: this.monthTable
      }
      this.$apis.SALES_MAIN.fenwaste(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              barWidth: 20, // 柱图宽度
              symbolSize: 1,
              itemStyle: {
                normal: {
                  color: '#1890FF',
                  lineStyle: {
                    color: '#1890FF'
                  }
                }
              }
            },
            {
              barWidth: 20, // 柱图宽度
              symbolSize: 1,
              itemStyle: {
                normal: {
                  color: '#28A855',
                  lineStyle: {
                    color: '#28A855'
                  }
                }
              }
            },
            {
              barWidth: 20, // 柱图宽度
              symbolSize: 1,
              itemStyle: {
                normal: {
                  color: '#D5B119',
                  lineStyle: {
                    color: '#D5B119'
                  }
                }
              }
            },
            {
              symbolSize: 1,
              yAxisIndex: 2,
              itemStyle: {
                normal: {
                  color: '#5B8FF9',
                  lineStyle: {
                    color: '#5B8FF9'
                  }
                }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            grid: {
              left: '60px'
            },
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}<br />{a2}：{c2}<br />{a3}：{c3}%'
            },
            xAxis: [
              {
                axisTick: {
                  show: false
                },
                axisLine: {
                  lineStyle: {
                    color: '#004C86',
                    width: 1
                  }
                },
                type: 'category',
                // axisTick: {
                //   alignWithLabel: true
                // },
                axisLabel: {
                  color: '#fff',
                  textStyle: {
                    fontSize: 12,
                    padding: [5, 0, 0, 0]
                  }
                },
                data: res.data.xAxis
              }
            ],
            yAxis: [
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                // min: res.data.yAxis[0].min,
                // max: res.data.yAxis[0].max,
                // interval: res.data.yAxis[0].interval,
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              },
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              },
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                // min: res.data.yAxis[1].min,
                // max: res.data.yAxis[1].max,
                // interval: res.data.yAxis[0].interval,
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                },
                type: 'value'
              }
            ],
            legend: this.$echartsconfig.legend,
            series
          }
          this.resetmyChart()
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    resetmyChart() {
      const dom = this.$refs.DynamicData
      const myChart = this.$echarts.init(dom)
      myChart.dispose()
    },
    drawLine() {
      const dom = this.$refs.DynamicData
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.fawastebul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.tabs-div{
  position: relative;
}
.waste-tip{
  font-size: 14px;
  color: #2C9CF9;
}
</style>
