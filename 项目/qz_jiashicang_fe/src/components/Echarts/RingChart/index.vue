<template>
  <div class="RingChart">
    <div id="RingChart" ref="RingChart" v-loading="membershipoverbul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'RingChart',
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
      membershipoverbul: true,
      option: {}
    }
  },
  mounted() {
    this.membershipoverview()//  会员数据- 会员概览
  },
  methods: {
    //  会员数据- 会员概览
    membershipoverview() {
      const params = {}
      this.$apis.SALES_MEMBER.membershipoverview(params).then(res => {
        if (res.error_code === 0) {
          const merge = [
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
                  color: '#68BD98'
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#8966E1'
                }
              }
            }
          ]
          const finaldata = []
          merge.forEach((item, i) => {
            res.data.series.data.forEach((ele, e) => {
              if (i === e) {
                finaldata.push({ ...item, ...ele })
              }
            })
          })
          this.option = {
            tooltip: {
              trigger: 'item',
              formatter: '{b}：{c} ({d}%)',
              ...this.$echartsconfig.tooltip
            },
            // width: '90%',
            // height: '90%',
            title: [
              {
                text: `{name|总会员个数}\n{val|${res.data.all_count}}`,
                top: 'center',
                left: 'center',
                textStyle: {
                  rich: {
                    name: {
                      fontSize: 20,
                      fontWeight: 'normal',
                      color: '#fff',
                      padding: [10, 0]
                    },
                    val: {
                      fontSize: 40,
                      fontWeight: 'bold',
                      color: '#fff'
                    }
                  }
                }
              }
            ],
            // legend: {
            //   orient: 'vertical',
            //   selectedMode: false, // 取消图例上的点击事件
            //   textStyle: {
            //     color: '#fff'
            //   },
            //   right: 0,
            //   bottom: 10
            // },
            series: [
              {
                label: {
                  normal: {
                    formatter: '{b}\n{c}({d}%)'
                  }
                },
                hoverAnimation: false,
                type: 'pie',
                radius: ['45%', '65%'],
                labelLine: {
                  length: 25, // 设置每一块的名字前面的线的长度
                  length2: 25, // 设置每一块的名字前面的线的长度
                  lineStyle: {
                    width: 1, // 设置每一块的名字前面的线的宽度
                    type: 'solid' // 设置每一块的名字前面的线的类型
                  }
                },
                data: finaldata
              },
              {
                label: { // 饼图图形上的文本标签
                  normal: {
                    position: 'inner', // 标签的位置
                    textStyle: {
                      fontSize: 12 // 文字的字体大小
                    },
                    formatter: '{d}%'
                  }
                },
                hoverAnimation: false,
                type: 'pie',
                radius: ['45%', '65%'],
                data: finaldata
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
      const dom = this.$refs.RingChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.membershipoverbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
