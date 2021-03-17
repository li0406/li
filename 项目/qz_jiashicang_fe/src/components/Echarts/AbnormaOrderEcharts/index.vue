<template>
  <div>
    <FilterMonth @setMonthParams="abnormalanalysis" />
    <div class="AbnormaOrderEcharts">
      <div
        id="AbnormaOrderEcharts"
        ref="AbnormaOrderEcharts"
        v-loading="membershipoverbul"
        :style="{ width, height }"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'AbnormaOrderEcharts',
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
    this.abnormalanalysis(1) //  会员数据- 会员概览
  },
  methods: {
    abnormalanalysis(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.PUBDATA.abnormalanalysis(params)
        .then(res => {
          if (res.error_code === 0) {
            const configList = [
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
                    color: '#10A8AD'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#28A855'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#D5B119'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#CC405D'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#713CC7'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#66CDAA'
                  }
                }
              },
              {
                itemStyle: {
                  normal: {
                    color: '#147EE1'
                  }
                }
              }
            ]
            const data = this.$formatechartsdata(configList, res.data.series)
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
              legend: this.$echartsconfig.legend,
              series: [
                {
                  label: {
                    normal: {
                      position: 'outside', // 标签的位置
                      textStyle: {
                        fontSize: 12 // 文字的字体大小
                      },
                      formatter: '{b}:\n{c}({d}%)'
                    }
                  },
                  hoverAnimation: false,
                  type: 'pie',
                  radius: ['30%', '50%'],
                  labelLine: {
                    length: 25, // 设置每一块的名字前面的线的长度
                    length2: 0, // 设置每一块的名字前面的线的长度
                    lineStyle: {
                      width: 1, // 设置每一块的名字前面的线的宽度
                      type: 'solid' // 设置每一块的名字前面的线的类型
                    }
                  },
                  data
                }
              ]
            }
            this.drawLine()
          } else {
            console.log(res.error_msg)
          }
        })
        .catch(err => {
          console.log(err)
        })
    },
    drawLine() {
      const dom = this.$refs.AbnormaOrderEcharts
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
