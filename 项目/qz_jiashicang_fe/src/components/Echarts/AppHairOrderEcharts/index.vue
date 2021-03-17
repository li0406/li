<template>
  <div>
    <FilterMonth @setMonthParams="fadancycle" />
    <div class="AppHairOrderEcharts">
      <div class="app-tips">注册用户从注册开始到用户首次/多次发单的周期</div>
      <div
        id="AppHairOrderEcharts"
        ref="AppHairOrderEcharts"
        v-loading="databul"
        :style="{ width, height }"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'AppHairOrderEcharts',
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
    this.fadancycle(1)
  },
  methods: {
    fadancycle(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.PUBDATA.fadancycle(params).then((res) => {
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
              ...this.$echartsconfig.tooltip
            },
            grid: {
              top: 50,
              left: 15,
              right: 40,
              bottom: 80,
              containLabel: true
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
                  padding: [15, 0, 0, 0]
                }
                // interval: 0,
                // rotate: -40
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
            legend: {
              ...this.$echartsconfig.legend,
              bottom: 45
            },
            series
          }
          if (res.data.xAxis && res.data.xAxis.length > 13) {
            this.option.dataZoom = [{
              show: true, // 为true 滚动条出现
              orient: 'horizontal',
              borderColor: '#2c9cf9',
              fillerColor: '#063F7C',
              type: 'slider', // 有type这个属性，滚动条在最下面，也可以不行，写y：36，这表示距离顶端36px，一般就是在图上面。
              // top: 40,
              // width: 24,
              // height: 340, // 表示滚动条的高度，也就是粗细
              start: 0, // 表示默认展示20%～80%这一段。
              end: 50,
              textStyle: {
                color: '#fff'
              }
            }]
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
      const dom = this.$refs.AppHairOrderEcharts
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
.AppHairOrderEcharts {
  position: relative;
  .app-tips {
    position: absolute;
    left: 0;
    top: 0;
    color: #fff;
    font-size: 14px;
  }
}
</style>
