<template>
  <div class="PieSpecialLabel">
    <div id="PieSpecialLabel" ref="PieSpecialLabel" v-loading="arearatebul" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'PieSpecialLabel',
  props: {
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '260px'
    },
    pieDataArray: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      arearatebul: true,
      option: {}
    }
  },
  watch: {
    pieDataArray() {
      this.undetermined()
    }
  },
  mounted() {
    this.undetermined()
  },
  methods: {
    undetermined() {
      const configList = [
        {
          itemStyle: {
            color: '#147EE1'
          }
        },
        {
          itemStyle: {
            color: '#10A8AD'
          }
        },
        {
          itemStyle: {
            color: '#28A855'
          }
        },
        {
          itemStyle: {
            color: '#D5B119'
          }
        },
        {
          itemStyle: {
            color: '#CC405D'
          }
        },
        {
          itemStyle: {
            color: '#713CC7'
          }
        }
      ]
      const data = this.$formatechartsdata(configList, this.pieDataArray)
      this.option = {
        tooltip: {
          trigger: 'item',
          ...this.$echartsconfig.tooltip,
          formatter: '{b}:{c}({d}%)'
        },
        grid: {
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          containLabel: true
        },
        legend: {
          orient: 'horizontal',
          selectedMode: false, // 取消图例上的点击事件
          textStyle: {
            color: '#fff'
          },
          align: 'auto',
          bottom: 20,
          backgroundColor: '#0A145F'
        },
        series: [
          {
            top: -60,
            label: { // 饼图图形上的文本标签
              normal: {
                position: 'outside', // 标签的位置
                textStyle: {
                  lineHeight: 20,
                  fontSize: 12 // 文字的字体大小
                },
                formatter: '{b}:{c}({d}%)'
              }
            },
            labelLine: {
              length: 20,
              length2: 0
            },
            selectedMode: 'single',
            hoverAnimation: false,
            type: 'pie',
            radius: '30%',
            data
          }
        ]
      }
      this.drawLine()
    },
    drawLine() {
      const dom = this.$refs.PieSpecialLabel
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

