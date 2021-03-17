<template>
  <div v-loading="shiprenewalbul" class="RenewChart">
    <div id="RenewChart" ref="RenewChart" :style="{ width, height }" />
    <div class="RenewChart-Tips">
      <div>1.0会员续费率=续费的会员总数/当月的到期会员数，一个会员当月多次续费算一次</div>
      <div class="mt5">2.0会员续费率=当月续费的次数/(会员在当月余额不足(小于3000元)的次数+会员在当月余额充足且在当月续费的续费次数)；当月新开会员的首次充值不算在内</div>
    </div>
  </div>
</template>

<script>

export default {
  name: 'RenewChart',
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
      shiprenewalbul: true,
      echartHeight: '260px',
      option: {}
    }
  },
  mounted() {
    this.membershiprenewal()//  会员数据-会员续费率
  },
  methods: {
    //  会员数据-会员续费率
    membershiprenewal() {
      const params = {}
      this.$apis.SALES_MEMBER.membershiprenewal(params).then(res => {
        if (res.error_code === 0) {
          const fixeddata = [
            {
              symbolSize: 1, // 折线点的大小
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
          fixeddata.forEach((item, i) => {
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
              formatter: '{b1}<br />{a0}：{c0}%<br />{a1}：{c1}%'
            },
            legend: {
              selectedMode: false, // 取消图例上的点击事件
              textStyle: {
                color: '#fff'
              },
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
                // max: 50,
                splitLine: {
                // 网格线
                  lineStyle: {
                    type: 'dashed', // 设置网格线类型 dotted：虚线   solid:实线
                    color: '#004C86'
                  },
                  show: true // 隐藏或显示
                },
                axisLabel: {
                  formatter: '{value}%',
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
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    drawLine() {
      const dom = this.$refs.RenewChart
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.shiprenewalbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>
<style scoped>
.RenewChart-Tips{
  font-size: 12px;
  color: #2C9CFA;
  text-align: left;
  margin: -24px 10px 0 10px;
}
.mt5{
  line-height: 20px;
  margin: 5px 0 0 0;
}
</style>
