<template>
  <div>
    <FilterMonth @setMonthParams="departwaste" />
    <div class="OrderWasteChart">
      <div ref="OrderWasteChart" v-loading="orderwastebul" :style="{ width, height }" />
    </div>
  </div>
</template>

<script>

export default {
  name: 'OrderWasteChart',
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
      orderwastebul: true,
      option: {}
    }
  },
  mounted() {
    this.departwaste(1)
  },
  methods: {
    departwaste(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.PUBDATA.departwaste(params).then((res) => {
        if (res.error_code === 0) {
          const configList = [
            {
              itemStyle: {
                normal: { color: '#147EE1' }
              }
            },
            {
              stack: '总量',
              itemStyle: {
                normal: { color: '#28A855' }
              }
            },
            {
              stack: '总量',
              itemStyle: {
                normal: { color: '#D5B119' }
              }
            },
            {
              stack: '总量',
              itemStyle: {
                normal: { color: '#999' }
              }
            },
            {
              yAxisIndex: 1,
              itemStyle: {
                normal: { color: '#6D0A1D' }
              }
            }
          ]
          const series = this.$formatechartsdata(configList, res.data.series)
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}<br />{a2}：{c2}<br />{a3}：{c3}<br />{a4}：{c4}%'
              //   axisPointer: {
              //     type: 'cross'
              //   }
            },
            // grid: {
            //   right: '20%'
            // },
            // toolbox: {
            //   feature: {
            //     dataView: { show: true, readOnly: false },
            //     restore: { show: true },
            //     saveAsImage: { show: true }
            //   }
            // },
            xAxis: [
              {
                // axisTick: {
                //   show: false
                // },
                type: 'category',
                // axisTick: {
                //   alignWithLabel: true
                // },
                axisLabel: {
                  color: '#fff',
                  textStyle: {
                    fontSize: 12,
                    padding: [5, 0, 0, 0]
                  },
                  interval: 0
                  // rotate: 60
                },
                axisLine: {
                  lineStyle: {
                    color: '#3274B9',
                    width: 1
                  }
                },
                data: res.data.xAxis
              }
            ],
            yAxis: [
              {
                // min: res.data.yAxis[0].min,
                // max: res.data.yAxis[0].max,
                // interval: res.data.yAxis[0].interval,
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
                  formatter: '{value} %',
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
      const dom = this.$refs.OrderWasteChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.orderwastebul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
