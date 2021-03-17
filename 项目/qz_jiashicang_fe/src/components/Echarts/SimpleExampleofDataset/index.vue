<template>
  <div class="SimpleExampleofDataset">
    <div id="SimpleExampleofDataset" ref="SimpleExampleofDataset" v-loading="pricebul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'SimpleExampleofDataset',
  props: {
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '252px'
    }
  },
  data() {
    return {
      pricebul: true,
      option: {}
    }
  },
  mounted() {
    this.orderprice()//  分单数据-分单客单价
  },
  methods: {
    //  分单数据-分单客单价
    orderprice() {
      const params = {}
      this.$apis.SALES_FEN.orderprice(params).then(res => {
        if (res.error_code === 0) {
          const fixeddata = [
            {
              name: '分单客单价',
              type: 'bar',
              itemStyle: {
                normal: { color: '#6460E5' }
              }
            },
            {
              name: '2.0会员分单客单价',
              type: 'bar',
              itemStyle: {
                normal: { color: '#0EB7EE' }
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
            legend: {
              //   orient: 'vertical',
              x: 'right',
              y: 'top',
              selectedMode: false, // 取消图例上的点击事件
              textStyle: {
                color: '#fff'
              }
            },
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}'
            },
            grid: {
              top: 50,
              left: 10,
              right: 10,
              bottom: 10,
              containLabel: true
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
              type: 'category',
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
                // max: 1000,
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
            // Declare several bar series, each will be mapped
            // to a column of dataset.source by default.
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
      const dom = this.$refs.SimpleExampleofDataset
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.pricebul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
