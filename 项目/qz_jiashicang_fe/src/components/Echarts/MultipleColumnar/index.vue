<template>
  <div>
    <qzNewBox :border="0">
      <div class="flex tab-div">
        <qzTab
          :tab-array="tabArray"
          @setTabParams="getTabParams"
        />
        <el-dropdown v-if="showTable!=2" trigger="click" placement="bottom-start" @command="switchmenu">
          <div class="dropdown">
            {{ selval.name }}
          </div>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item v-for="item of menuoptions" :key="item.type" :command="item">{{ item.name }}</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
      </div>
    </qzNewBox>
    <div class="MultipleColumnar">
      <div ref="MultipleColumnar" v-loading="orderfillbul" :style="{ width, height }" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'Cost',
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
      tabArray: [
        { name: '业绩月趋势图', type: 1 },
        { name: '人均产出', type: 2 },
        { name: '当月平均业绩档位', type: 3 }
      ],
      showTable: 1,
      menuoptions: [
        // { name: '销售中心', type: 0 },
        // { name: '一战区', type: 1 },
        // { name: '二战区', type: 2 },
        // { name: '三战区', type: 3 }
      ],
      selval: { name: '销售中心', type: 0 },
      orderfillbul: true,
      option: {}
    }
  },
  mounted() {
    this.toplist(1)
    this.getTabParams(1)
  },
  methods: {
    toplist(need_center) {
      const params = {
        need_center
      }
      this.$apis.COMMON_API.toplist(params).then(res => {
        if (res.error_code === 0) {
          const list = res.data.list
          this.menuoptions = list.map(item => {
            return { name: item.team_name, type: item.team_id }
          })
        } else {
          console.log(res.error_msg)
        }
      })
        .catch(err => {
          console.log(err)
        })
    },
    getTabParams(type) {
      this.showTable = type
      this.exchangeTable()
    },
    switchmenu(val) {
      this.selval = val
      this.exchangeTable()
    },
    exchangeTable() {
      switch (this.showTable) {
        case 1:
          this.monthtrend()
          break
        case 2:
          this.outputavg()//  人均产出
          break
        case 3:
          this.gradeavg()
          break
      }
    },
    monthtrend() { //  业绩
      const params = {
        team_id: this.selval.type
      }
      this.$apis.SALES_MAIN.monthtrend(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
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
              itemStyle: {
                normal: {
                  color: '#2FC25B', // 折线点的颜色
                  lineStyle: {
                    color: '#2FC25B' // 折线的颜色
                  }
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#FACC14', // 折线点的颜色
                  lineStyle: {
                    color: '#FACC14' // 折线的颜色
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
              // formatter: '{b}：{c}%'
            },
            xAxis: {
              // boundaryGap: false,
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
                },
                interval: 0
                // rotate: -20
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
                  // formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  },
                  interval: 0
                },
                type: 'value'
              }
            ],
            legend: this.$echartsconfig.legend,
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
    outputavg() { //  人均产出
      const params = {}
      this.$apis.SALES_MAIN.outputavg(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              itemStyle: {
                normal: {
                  color: '#147EE1', // 折线点的颜色
                  lineStyle: {
                    color: '#147EE1' // 折线的颜色
                  }
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#28A855', // 折线点的颜色
                  lineStyle: {
                    color: '#28A855' // 折线的颜色
                  }
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#D5B119', // 折线点的颜色
                  lineStyle: {
                    color: '#D5B119' // 折线的颜色
                  }
                }
              }
            },
            {
              itemStyle: {
                normal: {
                  color: '#1D2E6A', // 折线点的颜色
                  lineStyle: {
                    color: '#1D2E6A' // 折线的颜色
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
              // formatter: '{b}：{c}%'
            },
            xAxis: {
              // boundaryGap: false,
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
                },
                interval: 0
                // rotate: -20
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
                  // formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  },
                  interval: 0
                },
                type: 'value'
              }
            ],
            legend: this.$echartsconfig.legend,
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
    gradeavg() { //  当月平均业绩档位
      const params = {
        team_id: this.selval.type
      }
      this.$apis.SALES_MAIN.gradeavg(params).then(res => {
        if (res.error_code === 0) {
          const configList = [
            {
              barWidth: 30, // 柱图宽度
              itemStyle: {
                normal: {
                  color: '#1890FF', // 折线点的颜色
                  lineStyle: {
                    color: '#1890FF' // 折线的颜色
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
            xAxis: {
              // boundaryGap: false,
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
                },
                interval: 0
                // rotate: -20
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
                  // formatter: '{value}%',
                  color: '#fff',
                  textStyle: {
                    fontSize: 12
                  },
                  interval: 0
                },
                type: 'value'
              }
            ],
            // legend: this.$echartsconfig.legend,
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
      const dom = this.$refs.MultipleColumnar
      const myChart = this.$echarts.init(dom)
      myChart.dispose()
    },
    drawLine() {
      const dom = this.$refs.MultipleColumnar
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      this.orderfillbul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    }
  }
}
</script>

<style>
.dropdown{
  color: #fff;
  width: 85px;
  height: 32px;
  text-align: center;
  line-height: 32px;
  border: 1px solid #fff;
  border-radius: 15px;
  cursor: pointer;
}
.tab-div{
  width: 100%;
  height: 34px;
  justify-content: space-between;
  align-items: center;
}
</style>
