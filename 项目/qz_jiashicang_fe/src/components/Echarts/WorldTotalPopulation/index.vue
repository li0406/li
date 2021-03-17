<template>
  <div class="WorldTotalPopulation">
    <div id="WorldTotalPopulation" ref="WorldTotalPopulation" v-loading="databul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'WorldTotalPopulation',
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
    this.getsignranking()
  },
  methods: {
    //  城市报表-签单排行榜
    getsignranking() {
      const params = {}
      this.$apis.SALES_CITY.getsignranking(params).then(res => {
        if (res.error_code === 0) {
          const city_name = []
          const sign_amount = []
          const sortdata = res.data.sort((a, b) => a.sign_amount - b.sign_amount)
          sortdata.forEach(item => {
            city_name.push(item.city_name)
            sign_amount.push(item.sign_amount)
          })
          this.option = {
            tooltip: {
              trigger: 'item',
              ...this.$echartsconfig.tooltip,
              formatter: '{b}：{c}'
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
              show: false
            // axisLabel: {
            //   formatter: '{value}%',
            //   color: '#fff',
            //   textStyle: {
            //     fontSize: 12
            //   }
            // }
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
                    color: '#fff'
                  }
                },
                itemStyle: {
                  normal: { color: '#2384EB' }
                },
                barWidth: '50%',
                data: sign_amount
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
      const dom = this.$refs.WorldTotalPopulation
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
