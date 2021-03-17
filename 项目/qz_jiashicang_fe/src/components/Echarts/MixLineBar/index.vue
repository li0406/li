<template>
  <div class="MixLineBar">
    <div ref="MixLineBar" v-loading="databul" :style="{ width, height }" />
    <div class="MixLineBar-Tips">
      <div>签单占比=该签单距离内的签单量/总签单量</div>
    </div>
  </div>
</template>

<script>

export default {
  name: 'MixLineBar',
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
    this.getsigndistance()
  },
  methods: {
    //  城市报表-签单距离占比
    getsigndistance() {
      const params = {}
      this.$apis.SALES_CITY.getsigndistance(params)
        .then((res) => {
          if (res.error_code === 0) {
            const range = []
            const sign_amount = []
            const sign_percentage = []
            res.data.forEach((item) => {
              range.push(item.range)
              sign_amount.push(item.sign_amount)
              sign_percentage.push(item.sign_percentage)
            })
            this.option = {
              tooltip: {
                trigger: 'axis',
                ...this.$echartsconfig.tooltip,
                formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}%'
              },
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
                  axisLabel: {
                    color: '#fff',
                    textStyle: {
                      fontSize: 12,
                      padding: [5, 0, 0, 0]
                    }
                  },
                  data: range
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
                  // min: 0,
                  // max: 2000,
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
                  // min: 0,
                  // max: 40,
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
                  name: '签单量',
                  type: 'bar',
                  itemStyle: {
                    normal: { color: '#2384EB' }
                  },
                  barWidth: '40%',
                  data: sign_amount
                },
                {
                  name: '签单占比',
                  type: 'line',
                  itemStyle: {
                    normal: { color: '#D1A11E' }
                  },
                  barWidth: '40%',
                  yAxisIndex: 1,
                  data: sign_percentage
                }
              ]
            }
            this.drawLine()
          } else {
            console.log(res.error_msg)
          }
        })
        .catch((err) => {
          console.log(err)
        })
    },
    drawLine() {
      const dom = this.$refs.MixLineBar
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
<style scoped>
.MixLineBar-Tips{
  font-size: 12px;
  color: #2C9CFA;
  text-align: left;
  margin: -24px 20px 0 20px;
}
.mt5{
  line-height: 18px;
  margin: 5px 0 0 0;
}
</style>
