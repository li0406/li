<template>
  <div>
    <FilterMonth @setMonthParams="departtransform" />
    <div class="StackedAreaChart">
      <div id="StackedAreaChart" ref="StackedAreaChart" v-loading="databul" :style="{ width, height }" />
    </div>
  </div>
</template>

<script>

export default {
  name: 'StackedAreaChart',
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
      databul: true,
      option: {}
    }
  },
  mounted() {
    this.departtransform(1)
  },
  methods: {
    departtransform(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.PUBDATA.departtransform(params).then((res) => {
        if (res.error_code === 0) {
          const configList = [
            {
              symbolSize: 1, // 折线点的大小
              areaStyle: {},
              stack: '总量',
              itemStyle: {
                normal: {
                  color: '#1890FF', // 折线点的颜色
                  lineStyle: {
                    color: '#1890FF' // 折线的颜色
                  }
                }
              }
            },
            {
              symbolSize: 1, // 折线点的大小
              areaStyle: {},
              stack: '总量',
              itemStyle: {
                normal: {
                  color: '#2FC25B', // 折线点的颜色
                  lineStyle: {
                    color: '#2FC25B' // 折线的颜色
                  }
                }
              }
            },
            {
              symbolSize: 1, // 折线点的大小
              areaStyle: {},
              stack: '总量',
              itemStyle: {
                normal: {
                  color: '#D5B119', // 折线点的颜色
                  lineStyle: {
                    color: '#D5B119' // 折线的颜色
                  }
                }
              }
            },
            {
              symbolSize: 1, // 折线点的大小
              areaStyle: {},
              stack: '总量',
              itemStyle: {
                normal: {
                  color: '#CC405D', // 折线点的颜色
                  lineStyle: {
                    color: '#CC405D' // 折线的颜色
                  }
                }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip
            },
            xAxis: {
              // axisTick: {
              //   show: false
              // },
              boundaryGap: false,
              axisLine: {
                lineStyle: {
                  color: '#004C86',
                  width: 1
                }
              },
              axisLabel: {
                color: '#fff',
                textStyle: {
                  fontSize: 12,
                  padding: [5, 0, 0, 0]
                },
                interval: 0
              },
              data: res.data.xAxis
            },
            yAxis: [
              {
                axisLine: {
                  lineStyle: {
                    color: 'transparent',
                    width: 0
                  }
                },
                // min: 0,
                // max: 100,
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
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
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch((err) => {
        console.log(err)
      })
    },
    drawLine() {
      const dom = this.$refs.StackedAreaChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.databul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
