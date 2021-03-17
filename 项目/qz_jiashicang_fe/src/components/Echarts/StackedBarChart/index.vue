<template>
  <div>
    <FilterMonth @setMonthParams="priceinsuff" />
    <div class="tips">城市实际订单售价与城市订单单价差距最大的前20个城市</div>
    <div class="StackedBarChart">
      <div
        id="StackedBarChart"
        ref="StackedBarChart"
        v-loading="databul"
        :style="{ width, height }"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'StackedBarChart',
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
    this.priceinsuff()
  },
  methods: {
    priceinsuff(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.SALES_MAIN.priceinsuff(params)
        .then((res) => {
          if (res.error_code === 0) {
            const city_name = [] //  城市名称
            const city_order_price = [] // 城市订单售价
            const real_order_price = [] // 实际订单售价
            const order_price_diff = [] // 订单差价
            const sortdata = res.data.list.sort(
              (a, b) => a.order_price_diff - b.order_price_diff
            )
            sortdata.forEach((item) => {
              city_name.push(item.city_name)
              city_order_price.push(item.city_order_price)
              real_order_price.push(item.real_order_price)
              order_price_diff.push(item.order_price_diff)
            })
            this.option = {
              tooltip: {
                trigger: 'axis',
                axisPointer: {
                  // 坐标轴指示器，坐标轴触发有效
                  type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                },
                ...this.$echartsconfig.tooltip
              },
              grid: {
                top: 30,
                left: 15,
                right: 60,
                bottom: 30,
                containLabel: true
              },
              xAxis: {
                type: 'value',
                splitLine: {
                  // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLine: {
                  lineStyle: {
                    color: '#004C86',
                    width: 0
                  }
                },
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                }
                // min: 0,
                // max: 100,
                // show: false,
                // axisLabel: {
                //   formatter: '{value}%',
                //   color: '#fff',
                //   textStyle: {
                //     fontSize: 12
                //   }
                // }
              },
              yAxis: {
                // boundaryGap: false,
                axisLine: {
                  lineStyle: {
                    color: '#004C86',
                    width: 1
                  }
                },
                type: 'category',
                data: city_name,
                axisLabel: {
                  //   formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  }
                }
              },
              legend: this.$echartsconfig.legend,
              series: [
                {
                  name: '城市订单单价',
                  type: 'bar',
                  itemStyle: {
                    normal: { color: '#147EE1' }
                  },
                  barWidth: '50%',
                  stack: '总量',
                  data: city_order_price
                },
                {
                  name: '实际订单售价',
                  type: 'bar',
                  itemStyle: {
                    normal: { color: '#28A855' }
                  },
                  barWidth: '50%',
                  stack: '总量',
                  data: real_order_price
                },
                {
                  name: '订单差价',
                  type: 'bar',
                  itemStyle: {
                    normal: { color: '#D5B119' }
                  },
                  barWidth: '50%',
                  stack: '总量',
                  data: order_price_diff
                }
              ]
            }
            if (sortdata && sortdata.length > 10) {
              this.option.dataZoom = {
                show: true, // 为true 滚动条出现
                orient: 'vertical',
                borderColor: '#2c9cf9',
                fillerColor: '#063F7C',
                type: 'slider', // 有type这个属性，滚动条在最下面，也可以不行，写y：36，这表示距离顶端36px，一般就是在图上面。
                top: 40,
                width: 24,
                height: 340, // 表示滚动条的高度，也就是粗细
                start: 100, // 表示默认展示20%～80%这一段。
                end: 50,
                textStyle: {
                  color: '#fff'
                }
              }
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
      const dom = this.$refs.StackedBarChart
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
<style lang="scss" scoped>
.StackedBarChart {
  margin: 0 0 0 -15px;
}
.tips {
  font-size: 13px;
  text-align: left;
  color: #2c9cf9;
}
</style>
