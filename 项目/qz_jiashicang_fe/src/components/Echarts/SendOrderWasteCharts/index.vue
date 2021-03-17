<template>
  <div>
    <FilterMonth @setMonthParams="fadanwastecity" />
    <div class="SendOrderWasteCharts">
      <div id="SendOrderWasteCharts" ref="SendOrderWasteCharts" v-loading="databul" :style="{ width, height }" />
    </div>
  </div>
</template>

<script>

export default {
  name: 'SendOrderWasteCharts',
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
    this.fadanwastecity(1)
  },
  methods: {
    //  城市报表-签单排行榜
    fadanwastecity(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.PUBDATA.fadanwastecity(params).then(res => {
        if (res.error_code === 0) {
          const city_name = []
          const data_list = []
          const sortdata = res.data.yAxis.sort((a, b) => a.value - b.value)
          sortdata.forEach(item => {
            city_name.push(item.name)
            data_list.push(item.value)
          })
          this.option = {
            tooltip: {
              trigger: 'item',
              ...this.$echartsconfig.tooltip,
              formatter: '{b}：{c}%'
            },
            grid: {
              top: 30,
              left: 15,
              right: 60,
              bottom: 0,
              containLabel: true
            },
            xAxis: {
              type: 'value',
              // min: 0,
              // max: 100,
              show: true,
              splitLine: {
                // 网格线
                lineStyle: {
                  type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                  color: '#004C86'
                }
              },
              axisLine: {
                lineStyle: {
                  color: '#004C86',
                  width: 0
                }
              },
              axisLabel: {
                formatter: '{value}%',
                color: '#fff',
                textStyle: {
                  fontSize: 12
                }
              }
            },
            yAxis: {
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
              data: city_name,
              axisLabel: {
              //   formatter: '{value}%',
                color: '#fff',
                textStyle: {
                  fontSize: 12
                }
              }
            },
            series: [
              {
                type: 'bar',
                label: {
                  normal: {
                    show: true,
                    position: 'insideLeft',
                    formatter: '{c}%',
                    color: '#fff'
                  }
                },
                itemStyle: {
                  normal: { color: '#2384EB' }
                },
                barWidth: '50%',
                data: data_list
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
      }).catch(err => {
        console.log(err)
      })
    },
    drawLine() {
      const dom = this.$refs.SendOrderWasteCharts
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
