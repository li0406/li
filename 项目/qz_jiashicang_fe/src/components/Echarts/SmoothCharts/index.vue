<template>
  <div class="SmoothCharts">
    <div class="tabs-div">
      <div class="tabs">
        <div v-for="item of tabs" :key="item.on" :class="['tab-item','point',{'on':item.on===on}]" @click="membershipchange(item.on)">{{ item.name }}</div>
      </div>
    </div>
    <div id="SmoothCharts" ref="SmoothCharts" v-loading="shipchange" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'SmoothCharts',
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
      tabs: [
        { name: '最近一个月', on: 1 },
        { name: '最近三个月', on: 2 },
        { name: '近半年', on: 3 },
        { name: '近一年', on: 4 }
      ],
      on: 1, // 默认最近一个月 切换样式
      shipchange: true,
      option: {}
    }
  },
  mounted() {
    this.membershipchange(this.on)//  会员数据-会员数量变化趋势
  },
  methods: {
    //  会员数据-会员数量变化趋势
    membershipchange(step) {
      this.on = step
      const params = {
        step
      }
      this.$apis.SALES_MEMBER.membershipchange(params).then(res => {
        if (res.error_code === 0) {
          const dispose = [
            {
              symbolSize: 1, // 折线点的大小
              smooth: true,
              itemStyle: {
                normal: {
                  color: '#CB6A90', // 折线点的颜色
                  lineStyle: {
                    color: '#CB6A90' // 折线的颜色
                  }
                }
              }
            },
            {
              symbolSize: 1, // 折线点的大小
              smooth: true,
              itemStyle: {
                normal: {
                  color: '#2384EB', // 折线点的颜色
                  lineStyle: {
                    color: '#2384EB' // 折线的颜色
                  }
                }
              }
            },
            {
              symbolSize: 1, // 折线点的大小
              smooth: true,
              itemStyle: {
                normal: {
                  color: '#68BD98', // 折线点的颜色
                  lineStyle: {
                    color: '#68BD98' // 折线的颜色
                  }
                }
              }
            }
          ]
          const series = []
          dispose.forEach((item, i) => {
            res.data.series.forEach((ele, e) => {
              if (i === e) {
                series.push({ ...item, ...ele })
              }
            })
          })
          this.option = {
            tooltip: {
              trigger: 'axis',
              ...this.$echartsconfig.tooltip,
              formatter: '{b1}<br />{a0}：{c0}<br />{a1}：{c1}<br />{a2}：{c2}'
            },
            legend: {
              textStyle: {
                color: '#fff'
              },
              selectedMode: false, // 取消图例上的点击事件
              top: 0,
              right: 0
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
                // max: 3000,
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
            series
          }
          this.resetmyChart()
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    resetmyChart() {
      const dom = this.$refs.SmoothCharts
      const myChart = this.$echarts.init(dom)
      myChart.dispose()
    },
    drawLine() {
      const dom = this.$refs.SmoothCharts
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.shipchange = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
<style lang="scss" scoped>
#SmoothCharts{
  padding-top: 40px;
}
.SmoothCharts{
  position: relative;
}
.tabs-div{
  width: 83%;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}
.tabs{
  height: 30px;
  border-bottom: 1px solid #2C9CFA;
  color: #2C9CFA;
  font-size: 14px;
  text-align: center;
  display: flex;
}
.tab-item{
  width: 88px;
  height: 30px;
  line-height: 30px;
  border-radius: 4px 4px 0 0;
  border: 1px solid #2C9CFA;
  margin-left: 2px;
  &:hover{
    box-shadow: inset 0 0 30px rgba(44, 112, 205, 0.5);
    color: #04E3FF;
  }
}
.on {
  box-shadow: inset 0 0 30px rgba(44, 112, 205, 0.5);
  color: #04E3FF;
}
.point{
  cursor: pointer;
}
</style>
