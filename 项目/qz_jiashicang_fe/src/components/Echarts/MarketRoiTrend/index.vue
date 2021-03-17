<template>
  <div v-loading="loading" id="myChart" style="height: 100%;">
  </div>
</template>

<script>
export default {
  name: 'ROI',
  props: {
    message: {
      type: Object,
      default: null
    }
  },
  watch: {
    message(val) {
      this.start_time = val.start_time
      this.end_time = val.end_time
      this.account_id = val.account_id
      this.getRoiTrend()
      this.drawLine()
    }
  },
  data() {
    return {
      start_time: '',
      end_time: '',
      account_id: '',
      dateList: [],
      series: [],
      loading: false
    }
  },
  mounted() {
    this.getRoiTrend()

  },
  methods: {
    getRoiTrend() {
      this.loading =true
      this.$apis.PUBDATA.getRoiTrend({
        date_begin: this.start_time,
        date_end: this.end_time,
        account_ids: this.account_id
      }).then(res => {
        this.loading = false
        if (res.error_code === 0) {
          this.dateList = res.data.xAxis
          this.series = res.data.series
          this.drawLine()
        }
      }).catch(() => {
        this.loading = false
      })
    },
    drawLine() {
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(document.getElementById('myChart'))
      // 绘制图表
      myChart.setOption({
        color: '#1890FF', // 折线点的颜色
        lineStyle: {
          color: '#1890FF' // 折线的颜色
        },
        title: {
          text: '市场ROI趋势图',
          subtext: '',
          textStyle: {
            color: '#1890FF',
            fontSize: '20',
            fontWeight: 'normal'
          },
          padding: [10, 0, 0, 0]
        },
        tooltip: {
          trigger: 'axis',
          ...this.$echartsconfig.tooltip,
          formatter: '{b} <br/> {a} : {c}%'
        },
        grid: {
          top: 50,
          bottom: 50,
          left: 50,
          right: 50
        },
        xAxis: {
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
          type: 'category',
          boundaryGap: false,
          data: this.dateList
        },
        yAxis: {
          axisLine: {
            lineStyle: {
              color: 'transparent',
              width: 0
            }
          },
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
            },
            interval: 0
          },
          type: 'value'
        },
        series: this.series
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
