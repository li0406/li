<template>
  <div class="RefererOfaWebsite">
    <div id="RefererOfaWebsite" ref="RefererOfaWebsite" v-loading="arearatebul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'RefererOfaWebsite',
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
      arearatebul: true,
      option: {}
    }
  },
  mounted() {
    this.orderarearate()
  },
  methods: {
    //  城市报表-订单面积占比
    orderarearate() {
      const params = {}
      this.$apis.SALES_CITY.orderarearate(params).then(res => {
        if (res.error_code === 0) {
          const fixedfig = [
            {
              itemStyle: {
                normal: {
                  color: '#2384EB'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#CB6A90'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#8966E1'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#FF8331'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#ED7FE3'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#68BD98'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#D1A11E'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#506CFF'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#32C5FF'
                }
              }
            }
          ]
          const rep = []
          fixedfig.forEach((item, i) => {
            res.data.forEach((ele, e) => {
              if (i === e) {
                const obj = {
                  value: ele.count,
                  name: ele.name
                }
                rep.push({ ...item, ...obj })
              }
            })
          })
          this.option = {
            tooltip: {
              trigger: 'item',
              ...this.$echartsconfig.tooltip,
              formatter: '{b} <br/> 总单量：{c} <br/> 占比：({d}%)'
            },
            grid: {
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              containLabel: true
            },
            series: [
              {
                label: { // 饼图图形上的文本标签
                  normal: {
                    position: 'outside', // 标签的位置
                    textStyle: {
                      lineHeight: 20,
                      fontSize: 12 // 文字的字体大小
                    },
                    formatter: '{b}\n总单量:{c}'
                  }
                },
                hoverAnimation: false,
                type: 'pie',
                radius: '70%',
                data: rep
              },
              {
                label: { // 饼图图形上的文本标签
                  normal: {
                    position: 'inner', // 标签的位置
                    textStyle: {
                      lineHeight: 20,
                      fontSize: 12 // 文字的字体大小
                    },
                    formatter: '{d}%'
                  }
                },
                hoverAnimation: false,
                type: 'pie',
                radius: '70%',
                data: rep
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
      const dom = this.$refs.RefererOfaWebsite
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.arearatebul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
