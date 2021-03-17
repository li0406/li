<template>
  <div class="MixedLineandBar">
    <div ref="MixedLineandBar" v-loading="applypassorderfullbul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'MixedLineandBar',
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
      applypassorderfullbul: true,
      option: {}
    }
  },
  mounted() {
    this.applypassorderfull()
  },
  methods: {
    //  分单数据-补轮通过率图表
    applypassorderfull() {
      const params = {}
      this.$apis.SALES_FEN.applypassorderfull(params).then(res => {
        if (res.error_code === 0) {
          const fixeddata = [
            {
              itemStyle: {
                color: new this.$echarts.graphic.LinearGradient(
                  0, 0, 0, 1,
                  [
                    { offset: 0, color: '#E7A4C3' },
                    { offset: 1, color: '#CB6A90' }
                  ]
                )
              }
            },
            {
              itemStyle: {
                color: new this.$echarts.graphic.LinearGradient(
                  0, 0, 0, 1,
                  [
                    { offset: 0, color: '#F5C951' },
                    { offset: 1, color: '#D1A11E' }
                  ]
                )
              }
            },
            {
              yAxisIndex: 2,
              itemStyle: {
                normal: { color: '#68BD98' }
              }
            }
          ]
          const series = []
          fixeddata.forEach((item, i) => {
            res.data.series.forEach((ele, e) => {
              if (i === e) {
                series.push({ ...item, ...ele })
              }
            })
          })
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}<br />{a2}：{c2}%'
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
            legend: {
              x: 'right',
              y: 'top',
              selectedMode: false, // 取消图例上的点击事件
              textStyle: {
                color: '#fff'
              }
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
      const dom = this.$refs.MixedLineandBar
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.applypassorderfullbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
