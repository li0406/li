<template>
  <qzNewBox :border="0">
    <qzTab
      :tab-array="tabArray"
      @setTabParams="getTabParams"
    />
    <div class="StackedLineChart">
      <div ref="StackedLineChart" v-loading="renewanalysisbul" :style="{ width, height }" />
    </div>
  </qzNewBox>

</template>

<script>

export default {
  name: 'StackedLineChart',
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
      tabArray: [
        { name: '企业续费率', type: 1 },
        { name: '企业量房/签单走势图', type: 2 }
      ],
      renewanalysisbul: true,
      option: {},
      showTable: 1
    }
  },
  mounted() {
    this.getTabParams(1)
  },
  methods: {
    getTabParams(val) {
      this.showTable = val
      switch (val) {
        case 1:
          this.renewanalysis()
          break
        case 2:
          this.liangfangandandsign()
          break
      }
    },
    renewanalysis() {
      const params = {}
      this.$apis.SALES_MAIN.renewanalysis(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              symbolSize: 1, // 折线点的大小
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
              itemStyle: {
                normal: {
                  color: '#2FC25B', // 折线点的颜色
                  lineStyle: {
                    color: '#2FC25B' // 折线的颜色
                  }
                }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}%<br />{a1}：{c1}%'
            },
            xAxis: {
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
                  padding: [15, 0, 0, 0]
                },
                interval: 0,
                rotate: -20
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
                  formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  },
                  interval: 0
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
      }).catch(err => {
        console.log(err)
      })
    },
    liangfangandandsign() {
      const params = {}
      this.$apis.SALES_MAIN.liangfangandandsign(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              symbolSize: 1, // 折线点的大小
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
              itemStyle: {
                normal: {
                  color: '#2FC25B', // 折线点的颜色
                  lineStyle: {
                    color: '#2FC25B' // 折线的颜色
                  }
                }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}%<br />{a1}：{c1}%'
            },
            xAxis: {
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
                  padding: [15, 0, 0, 0]
                },
                interval: 0,
                rotate: -20
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
                  formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  },
                  interval: 0
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
      }).catch(err => {
        console.log(err)
      })
    },
    drawLine() {
      const dom = this.$refs.StackedLineChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.renewanalysisbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
