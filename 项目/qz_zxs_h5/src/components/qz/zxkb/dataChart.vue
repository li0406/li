<template>
  <section id="data-chart" style="position: relative;box-shadow: 0px 15px 20px 0px rgba(222,227,240,0.57);
    border-radius: 0.1rem;">
    <table class="tab-header-box">
      <td v-for="(item, index) in prizeInfo" :key="index" @click="changeHeader(index)">
        <template v-if="item.active">
          <span class="tab-header-item active">{{item.title}}</span>
        </template>
        <template v-else>
          <span class="tab-header-item">{{item.title}}</span>
        </template>
      </td>
    </table>
    <div class="data-box">
      <div class="gk-box" @touchstart.prevent="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd">
        均价<span class="gk-title">{{viewData.average}}</span>元/㎡
      </div>
      <table class="gk-detail" @touchstart.prevent="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd">
        <td v-if="viewData.qing">清包<span>{{viewData.qing}}元/㎡</span></td>
        <td v-if="viewData.ban">半包<span>{{viewData.ban}}元/㎡</span></td>
        <td v-if="viewData.quan">全包<span>{{viewData.quan}}元/㎡</span></td>
      </table>
      <div class="data-chart-content">
        <br>
        <div>
          <canvas id="synthesize" v-show="prizeInfo.synthesize.active" height="240"></canvas>
          <canvas id="economic" v-show="prizeInfo.economic.active" height="240"></canvas>
          <canvas id="quality" v-show="prizeInfo.quality.active" height="240"></canvas>
          <canvas id="luxurious" v-show="prizeInfo.luxurious.active" height="240"></canvas>
        </div>
        <div class="lend-list">
          <table class="gk-detail lend-list" style="width:60%; margin:0px auto; vertical-align:middle;">
            <td v-if="viewData.qing"><span class="lenged qing"></span><span style="position:relative; top: -1px">清包</span></td>
            <td v-if="viewData.ban"><span class="lenged ban"></span><span style="position:relative; top: -1px">半包</span></td>
            <td v-if="viewData.quan"><span class="lenged quan"></span><span style="position:relative; top: -1px">全包</span></td>
          </table>
        </div>
      </div>
      <!-- <br> -->
       <div id="showForm"></div>
      <div class="data-shuom">
        <p>*以上价格不含家具家电（数据来源齐装网装修系统）。</p>
      </div>
    </div>
  </section>
</template>
<script>
import '../../../assets/css/zxkb.css'
import {yusuanMarket} from '../../../api/qzZxkb.js'
import Chart from 'chart.js'
export default {
  name: 'dataChart',
  props: ['city'],
  data () {
    return {
      newChart: '',
      chartLabels: [],
      totalData: {},
      viewData: {
        average: '',
        qing: '',
        quan: '',
        ban: ''
      },
      prizeInfo: {
        synthesize: {
          title: '综合',
          active: false,
          data: [
            // {
            //   label: '均价',
            //   typeName: 'average',
            //   backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
            //   data: []
            // },
            {
              label: '半包',
              typeName: 'ban',
              backgroundColor: ['#118CE7', '#118CE7', '#118CE7'],
              data: []
            },
            {
              label: '全包',
              typeName: 'quan',
              backgroundColor: ['#1FBD75', '#1FBD75', '#1FBD75'],
              data: []
            }
          ]
        },
        economic: {
          title: '经济',
          active: true,
          data: [
            // {
            //   label: '均价',
            //   typeName: 'average',
            //   backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
            //   data: []
            // },
            {
              label: '清包',
              typeName: 'qing',
              backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
              data: []
            },
            {
              label: '半包',
              typeName: 'ban',
              backgroundColor: ['#118CE7', '#118CE7', '#118CE7'],
              data: []
            },
            {
              label: '全包',
              typeName: 'quan',
              backgroundColor: ['#1FBD75', '#1FBD75', '#1FBD75'],
              data: []
            }
          ]
        },
        quality: {
          title: '品质',
          active: false,
          data: [
            // {
            //   label: '均价',
            //   typeName: 'average',
            //   backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
            //   data: []
            // },
            {
              label: '清包',
              typeName: 'qing',
              backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
              data: []
            },
            {
              label: '半包',
              typeName: 'ban',
              backgroundColor: ['#118CE7', '#118CE7', '#118CE7'],
              data: []
            },
            {
              label: '全包',
              typeName: 'quan',
              backgroundColor: ['#1FBD75', '#1FBD75', '#1FBD75'],
              data: []
            }
          ]
        },
        luxurious: {
          title: '轻奢',
          active: false,
          data: [
            {
              label: '均价',
              typeName: 'average',
              backgroundColor: ['#FFC529', '#FFC529', '#FFC529'],
              data: [0, 0, 0]
            },
            {
              label: '半包',
              typeName: 'ban',
              backgroundColor: ['#118CE7', '#118CE7', '#118CE7'],
              data: []
            },
            {
              label: '全包',
              typeName: 'quan',
              backgroundColor: ['#1FBD75', '#1FBD75', '#1FBD75'],
              data: []
            }
          ]
        }
      },
      index: 1,
      str: '',
      startX: '',
      endX: '',
      x: '',
      flag: false
    }
  },
  watch: {
    'x': function(newVal) {
      this.flag = true
    }
  },
  created () {
    // 获取行情数据
    this.getMarketData()
  },
  mounted () {},
  methods: {
    //  tab 切换
    changeHeader (index) {
      for (let i in this.prizeInfo) {
        if (i === index) {
          this.prizeInfo[i].active = true
          this.drawLines(i)
          this.showViewData(i, 2)
        } else {
          this.prizeInfo[i].active = false
        }
      }
    },
    handleTouchStart(e) {
      this.startX = e.touches[0].clientX
    },
    handleTouchMove(e) {
      this.endX = e.touches[0].clientX
      this.x = this.endX - this.startX
    },
    handleTouchEnd(e) {
      if (this.flag) {
        if (this.x > 0) {
          if (this.index == 0) {
            this.index = 0
          } else {
            this.index -= 1
          }
        } else {
          if (this.index == 3) {
            this.index = 3
          } else {
            this.index += 1
          }
        }
        if (this.index == 0) {
          this.str = 'synthesize'
        } else if (this.index == 1) {
          this.str = 'economic'
        } else if (this.index == 2){
          this.str = 'quality'
        } else {
          this.str = 'luxurious'
        }
        this.changeHeader(this.str)
        this.flag = false
      }
    },
    // 获取数据
    getMarketData () {
      yusuanMarket(this.city.cs).then(res => {
        if (res.data.error_code === 0) {
          this.totalData = res.data.data
          for (let i in this.prizeInfo) {
            this.drawLines(i)
          }
          this.showViewData('economic', 2)
          const len = this.totalData.economic.month.length
          let monthV = this.totalData.economic.month[len - 1].split('-')[1]
          if (monthV.indexOf('0') === 0) {
            monthV = monthV.replace('0', '')
          }
          this.$parent.currentMonth = monthV
        }
      }).catch()
    },
    //  统计图
    drawLines (index) {
      this.viewData = {}
      let _me = this
      let myCharts = document.getElementById(index)
      // 绘制图
      for (let i in this.totalData[index]) {
        for (let k = 0; k < this.prizeInfo[index].data.length; k++) {
          if (this.prizeInfo[index].data[k].typeName === i) {
            this.prizeInfo[index].data[k].data = this.totalData[index][i]
          }
        }
      }
      this.newChart = new Chart(myCharts, {
        type: 'bar',
        data: {
          labels: this.totalData[index].month,
          datasets: this.prizeInfo[index].data,
          scaleSteps: 10
        },
        options: {
          events: ['click'],
          layout: {
            padding: {
              top: 10
            }
          },
          onClick: function (event) {
            let allWidth = event.target.offsetWidth
            let leftArea = event.offsetX
            let bili = leftArea / allWidth
            if (bili <= 0.432) {
              _me.showViewData(index, 0)
            }
            if (bili >= 0.433 && bili <= 0.71) {
              _me.showViewData(index, 1)
            }
            if (bili > 0.71) {
              _me.showViewData(index, 2)
            }
          },
          legend: {
            position: 'bottom',
            display: false,
            labels: {
              boxWidth: 11
            }
          },
          tooltips: {
            bodyFontColor: '#333',
            titleFontColor: '#333',
            backgroundColor: '#FFEBEB',
            cornerRadius: 2,
            elements: {
              line: {
                borderColor: 'rgba(0, 0, 0, 0)'
              }
            },
            mode: 'label',
            intersect: false,
            xAlign: 'center',
            yAlign: 'top',
            callbacks: {
              label: function (tooptipItem) {
                return _me.prizeInfo[index].data[tooptipItem.datasetIndex].label + tooptipItem.value + '元/㎡'
              }
            }
          },
          showAllTooltips: true,
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                stepSize: 200
              },
              gaxisLine: {
                type: 'dotted'
              }
            }],
            xAxes: [{
              gridLines: {
                display: false
              },
              barPercentage: 0.3 * _me.prizeInfo[index].data.length
            }]
          }
        }
      })
    },
    showViewData (index, num) {
      this.viewData = {}
      for (let i in this.totalData[index]) {
        this.viewData[i] = this.totalData[index][i][num]
      }
    }
  }
}
</script>
<style  scoped>
.data-shuom{
  font-size: 0.08rem;
}
.lend-list{
  font-size: 0.1rem !important;
  color: #333;
}
.lenged{
  display: inline-block;
  width:12px;
  height: 12px;
}
.lenged.qing{
  background: #FFC529;
  padding:0px;
}
.lenged.ban{
  background: #118CE7;
  padding:0px;
}
.lenged.quan{
  background: #1FBD75;
  padding:0px;
}
</style>
