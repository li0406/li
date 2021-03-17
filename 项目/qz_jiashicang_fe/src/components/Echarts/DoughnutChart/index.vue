<template>
  <div class="Chart">
    <div :id="idName" :style="{ width, height }" />
  </div>
</template>

<script>

export default {
  name: 'Chart',
  props: {
    width: {
      type: String,
      default: '61.19px'
    },
    height: {
      type: String,
      default: '61.19px'
    },
    idName: { // 绘制图表的id名字
      type: String,
      default: 'Chart'
    },
    used: { //   使用比
      type: Number,
      default: 0
    },
    usedColor: { // 使用比的颜色
      type: String,
      default: '#000'
    },
    surplus: { //  未使用比
      type: Number,
      default: 0
    },
    surplusColor: { // 未使用比的颜色
      type: String,
      default: '#fff'
    },
    usedTips: { //  使用比提示标题
      type: String,
      default: '使用比提示标题'
    },
    surplusTips: { //  未使用比提示标题
      type: String,
      default: '未使用比提示标题'
    }
  },
  data() {
    return {
      option: {}
    }
  },
  mounted() {
    this.option = {
      tooltip: {
        trigger: 'item',
        ...this.$echartsconfig.tooltip
      },
      series: [
        {
          hoverAnimation: false,
          type: 'pie',
          radius: ['60%', '80%'],
          center: ['50%', '40%'],
          data: [
            {
              tooltip: {
                formatter: '{b}：{c} ({d}%)'
              },
              name: this.usedTips,
              value: ((this.used === 0) ? '' : this.used),
              itemStyle: {
                color: this.usedColor
              }
            },
            {
              tooltip: {
                show: false
                // formatter: '{b}：{c} ({d}%)'
              },
              name: this.surplusTips,
              value: this.surplus,
              itemStyle: {
                color: this.surplusColor
              },
              label: {
                emphasis: {
                  show: false
                }
              }
            }
          ],
          itemStyle: {
            normal: {}
          },
          label: {
            normal: {
              // 默认不显示数据
              show: false,
              position: 'center'
            },
            color: '#fff'
          }
        }
      ]
    }
    this.drawLine()
  },
  methods: {
    drawLine() {
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(
        document.getElementById(this.idName)
      )
      // 绘制图表
      myChart.setOption(this.option)
      this.$elMaker.listenTo(document.getElementById(this.idName), () => {
        myChart.resize()
      })
    }
  }
}
</script>
