<template>
  <div class="LineChart">
    <div ref="LineChart" v-loading="orderfillbul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'LineChart',
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
      orderfillbul: true,
      option: {}
    }
  },
  mounted() {
    this.orderfill()
  },
  methods: {
    //  分单数据-补轮通过率图表
    orderfill() {
      const params = {}
      this.$apis.SALES_FEN.orderfill(params).then(res => {
        if (res.error_code === 0) {
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b}：{c}%'
            },
            xAxis: {
              axisTick: {
                show: false
              },
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
                }
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
                  }
                },
                type: 'value'
              }
            ],
            series: [
              {
                type: 'line',
                symbolSize: 1, // 折线点的大小
                itemStyle: {
                  normal: {
                    color: '#CB6A90', // 折线点的颜色
                    lineStyle: {
                      color: '#CB6A90' // 折线的颜色
                    }
                  }
                },
                data: res.data.series[0].data
              }
            ]
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
      const dom = this.$refs.LineChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.orderfillbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
