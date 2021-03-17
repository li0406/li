<template>
  <div class="FunnelChart">
    <div class="tabs">
      <TimeSlot @getdateparams="setdateparams" />
    </div>
    <div
      ref="FunnelChart"
      v-loading="transformingdatabul"
      :style="{ width, height }"
    />
  </div>
</template>

<script>
export default {
  name: 'FunnelChart',
  components: {
    TimeSlot: () => import('@/components/TimeSlot/index')
  },
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
      transformingdatabul: true,
      tabs: [
        { name: '最近一个月', on: 1 },
        { name: '最近三个月', on: 2 },
        { name: '近半年', on: 3 },
        { name: '近一年', on: 4 }
      ],
      on: 1, // 默认最近一个月 切换样式
      option: {}
    }
  },
  mounted() {
    this.setdateparams(this.on) //  城市报表-订单转化
  },
  methods: {
    //  城市报表-订单转化
    setdateparams(step) {
      this.on = step
      const params = {
        step // 时间阶段 默认1 1.近一个月 2.近三个月 3.近半年 4.近一年
      }
      this.$apis.SALES_CITY.transformingdata(params)
        .then(res => {
          if (res.error_code === 0) {
            const fixedfig = [
              {
                label: {
                  position: 'inside',
                  formatter: d => `${d.data.name}:${d.data.value}`
                },
                itemStyle: {
                  normal: {
                    color: '#68BD98'
                  }
                }
              },
              {
                label: {
                  position: 'inside',
                  formatter: d => `${d.data.name}:${d.data.value}`
                },
                itemStyle: {
                  normal: {
                    color: '#2384EB'
                  }
                }
              },
              {
                label: {
                  position: 'inside',
                  formatter: d => `${d.data.name}:${d.data.value}`
                },
                itemStyle: {
                  normal: {
                    color: '#8966E1'
                  }
                }
              },
              {
                label: {
                  position: 'inside',
                  formatter: d => `${d.data.name}:${d.data.value}`
                },
                itemStyle: {
                  normal: {
                    color: '#CB6A90'
                  }
                }
              },
              {
                label: {
                  position: 'inside',
                  formatter: d => `${d.data.name}:${d.data.value}`
                },
                itemStyle: {
                  normal: {
                    color: '#D1A11E'
                  }
                }
              }
            ]
            const fixedfig1 = [
              {
                label: {
                  position: 'right',
                  show: false,
                  formatter: d => `${d.data.oriname}:${d.data.number}%`
                },
                labelLine: {
                  show: false,
                  length: 0 // 设置每一块的名字前面的线的长度
                  // lineStyle: {
                  //   width: 1, // 设置每一块的名字前面的线的宽度
                  //   type: 'solid' // 设置每一块的名字前面的线的类型
                  // }
                },
                itemStyle: {
                  normal: {
                    color: '#68BD98'
                  }
                }
              },
              {
                label: {
                  position: 'right',
                  formatter: d => `${d.data.oriname}:${d.data.number}%`
                },
                labelLine: {
                  length: 120, // 设置每一块的名字前面的线的长度
                  lineStyle: {
                    width: 1, // 设置每一块的名字前面的线的宽度
                    type: 'solid' // 设置每一块的名字前面的线的类型
                  }
                },
                itemStyle: {
                  normal: {
                    color: '#2384EB'
                  }
                }
              },
              {
                label: {
                  position: 'right',
                  formatter: d => `${d.data.oriname}:${d.data.number}%`
                },
                labelLine: {
                  length: 120, // 设置每一块的名字前面的线的长度
                  lineStyle: {
                    width: 1, // 设置每一块的名字前面的线的宽度
                    type: 'solid' // 设置每一块的名字前面的线的类型
                  }
                },
                itemStyle: {
                  normal: {
                    color: '#8966E1'
                  }
                }
              },
              {
                label: {
                  position: 'right',
                  formatter: d => `${d.data.oriname}:${d.data.number}%`
                },
                labelLine: {
                  length: 120, // 设置每一块的名字前面的线的长度
                  lineStyle: {
                    width: 1, // 设置每一块的名字前面的线的宽度
                    type: 'solid' // 设置每一块的名字前面的线的类型
                  }
                },
                itemStyle: {
                  normal: {
                    color: '#CB6A90'
                  }
                }
              },
              {
                label: {
                  position: 'right',
                  formatter: d => `${d.data.oriname}:${d.data.number}%`
                },
                labelLine: {
                  length: 120, // 设置每一块的名字前面的线的长度
                  lineStyle: {
                    width: 1, // 设置每一块的名字前面的线的宽度
                    type: 'solid' // 设置每一块的名字前面的线的类型
                  }
                },
                itemStyle: {
                  normal: {
                    color: '#D1A11E'
                  }
                }
              }
            ]
            const rep = [
              {
                value: res.data.all_count,
                oriname: '转化率',
                number: -1,
                name: '发单量'
              },
              {
                value: res.data.p_count,
                oriname: '转化率',
                number: res.data.p_rate,
                name: '派单量'
              },
              {
                value: res.data.yx_count,
                oriname: '转化率',
                number: res.data.yx_rate,
                name: '有效单量'
              },
              {
                value: res.data.fen_count,
                oriname: '转化率',
                number: res.data.fen_rate,
                name: '分单量'
              },
              {
                value: res.data.qian_count,
                oriname: '转化率',
                number: res.data.qian_rate,
                name: '分单签约量'
              }
            ]
            const data = []
            const data1 = []
            rep.forEach((item, i) => {
              fixedfig.forEach((el, e) => {
                if (i === e) {
                  data.push({ ...item, ...el })
                }
              })
            })

            rep.forEach((item, i) => {
              fixedfig1.forEach((el, e) => {
                if (i === e) {
                  data1.push({ ...item, ...el })
                }
              })
            })
            this.option = {
              tooltip: {
                trigger: 'item',
                ...this.$echartsconfig.tooltip,
                formatter: d => {
                  let tips
                  if (d.data.number === -1) {
                    tips = `${d.data.name}：${d.data.value}`
                  } else {
                    tips = `${d.data.name}：${d.data.value}<br />${d.data.oriname}：${d.data.number}%`
                  }
                  return tips
                }
              },

              series: [
                {
                  type: 'funnel',
                  width: '50%',
                  height: '80%',
                  minSize: '50%',
                  top: 24,
                  itemStyle: {
                    borderColor: '#fff',
                    borderWidth: 0
                  },
                  data: data
                },
                {
                  type: 'funnel',
                  width: '50%',
                  height: '80%',
                  minSize: '50%',
                  top: 24,
                  itemStyle: {
                    borderColor: '#fff',
                    borderWidth: 0
                  },
                  data: data1
                }
              ]
            }
            this.resetmyChart()
            this.drawLine()
          } else {
            console.log(res.error_msg)
          }
        })
        .catch(err => {
          console.log(err)
        })
    },
    resetmyChart() {
      const dom = this.$refs.FunnelChart
      const myChart = this.$echarts.init(dom)
      myChart.dispose()
    },
    drawLine() {
      const dom = this.$refs.FunnelChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.transformingdatabul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.FunnelChart {
  position: relative;
}
</style>
