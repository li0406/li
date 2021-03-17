<template>
  <div class="MultipleYAxes">
    <div ref="MultipleYAxes" v-loading="orderwastebul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'MultipleYAxes',
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
    this.orderwaste()
  },
  methods: {
    //  分单数据-申请补轮量
    orderwaste() {
      const params = {}
      this.$apis.SALES_FEN.orderwaste(params).then(res => {
        if (res.error_code === 0) {
          const fixeddata = [
            {
              itemStyle: {
                normal: { color: '#68BD98' }
              }
            },
            {
              itemStyle: {
                normal: { color: '#CB6A90' }
              }
            },
            {
              itemStyle: {
                normal: { color: '#2384EB' }
              }
            },
            {
              itemStyle: {
                normal: { color: '#8966E1' }
              }
            },
            {
              itemStyle: {
                normal: { color: '#B99026' }
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
              formatter: '{b1}<br />{a0}：{c0}%<br />{a1}：{c1}%<br />{a2}：{c2}%<br />{a3}：{c3}%<br />{a4}：{c4}%'
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
              // data: res.data.legend,
              x: 'left',
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
                  // interval: 0,
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
                  formatter: '{value}%',
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
                  formatter: '{value}%',
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
                  formatter: '{value}%',
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
                  formatter: '{value}%',
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
      const dom = this.$refs.MultipleYAxes
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
