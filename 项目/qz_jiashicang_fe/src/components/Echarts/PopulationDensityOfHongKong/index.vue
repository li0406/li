<template>
  <div class="BigChinaMaps">
    <div class="bigmaptitle">{{ provincename }}已签企业情况</div>
    <div class="mb">已开通城市：{{ opencitynumber }}</div>
    <div>
      <table class="table">
        <tr class="tr">
          <td>
            <div class="tableheader">
              <div class="city">城市</div>
              <div class="line" />
              <div class="enterprise">已签约企业</div>
            </div>
          </td>
          <td>0家</td>
          <td>1家</td>
          <td>2家</td>
          <td>3家</td>
          <td>4家</td>
          <td>>4家</td>
        </tr>
        <tr class="tr">
          <td>数量</td>
          <td v-for="(item,index) of provincearray" :key="index">{{ item }}</td>
        </tr>
      </table>
    </div>
    <div>
      <div class="PopulationDensityOfHongKong">
        <div id="PopulationDensityOfHongKong" ref="PopulationDensityOfHongKong" v-loading="arearatebul" :style="{ width, height }" />
        <div class="prominent flex">
          <div class="color">
            <div />
            <div />
            <div />
          </div>
          <div class="prodata">
            <div>TOP20%</div>
            <div>TOP70%</div>
            <div>TOP10%</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
//eslint -disable-next-line no-unused-expressions
import './chinamap/china'
export default {
  name: 'PopulationDensityOfHongKong',
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
      arearatebul: true,
      option: {},
      alldata: {},
      provincename: '全国',
      provincearray: [1, 2, 3, 4, 5, 6],
      opencitynumber: 0
    }
  },
  // mounted() {
  //   this.sfsignanalysis()
  //   this.initshowdata()
  // },
  methods: {
    sfsignanalysis() {
      const params = {}
      this.$apis.SALES_MAIN.sfsignanalysis(params).then((res) => {
        if (res.error_code === 0) {
          const sf_city = res.data.sf_city
          const data = sf_city.map(item => {
            let color = 'fff'
            switch (item.color) {
              case 1:
                color = '#D9001B'
                break
              case 2:
                color = '#0000FF'
                break
              case 3:
                color = '#FFFF00'
                break
              default:
                color = '#fff'
            }
            return {
              name: item.name,
              value: item.value,
              itemStyle: {
                normal: { color }
              }
            }
          })
          this.alldata = res.data
          this.option = {
            tooltip: {
              trigger: 'item',
              ...this.$echartsconfig,
              formatter: (a) => {
                // console.log(a)
              }
            },
            toolbox: {
              show: true,
              orient: 'vertical',
              left: 'right',
              top: 'center'
            },
            position: (a, b, c, d, e) => {
              this.provincename = b.data.name
              this.provincearray = b.data.value.detail
              this.opencitynumber = b.data.value.vip_amount
            },
            series: [
              {
                roam: true,
                type: 'map',
                map: 'china',
                top: 40,
                bottom: 20,
                label: {
                  show: false
                },
                emphasis: {
                  itemStyle: {
                    areaColor: '#CCEBEB'
                  }
                },
                data,
                // 自定义名称映射
                nameMap: {
                  '江苏': '江苏省',
                  '浙江': '浙江省',
                  '安徽': '安徽省',
                  '广东': '广东省',
                  '四川': '四川省',
                  '山东': '山东省',
                  '湖北': '湖北省',
                  '河南': '河南省',
                  '湖南': '湖南省',
                  '河北': '河北省',
                  '重庆': '重庆市',
                  '云南': '云南省',
                  '贵州': '贵州省',
                  '江西': '江西省',
                  '陕西': '陕西省',
                  '福建': '福建省',
                  '北京': '北京市',
                  '辽宁': '辽宁省',
                  '天津': '天津市',
                  '上海': '上海市',
                  '广西': '广西壮族自治区',
                  '甘肃': '甘肃省',
                  '山西': '山西省',
                  '新疆': '新疆维吾尔自治区',
                  '内蒙古': '内蒙古自治区',
                  '海南': '海南省',
                  '西藏': '西藏自治区',
                  '吉林': '吉林省',
                  '黑龙江': '黑龙江省',
                  '宁夏': '宁夏回族自治区',
                  '青海': '青海省'
                }
              }
            ]
          }
          this.initshowdata()
          this.drawLine()
        } else {
          console.log(res.error_msg)
        }
      }).catch((err) => {
        console.log(err)
      })
    },
    drawLine() {
      const dom = this.$refs.PopulationDensityOfHongKong
      // 基于准备好的dom，初始化echarts实例
      const myChart = this.$echarts.init(dom)
      // 绘制图表
      myChart.setOption(this.option)
      myChart.on('mouseout', (params) => {
        this.initshowdata()
      })
      this.arearatebul = false
      this.$elMaker.listenTo(dom, () => {
        myChart.resize()
      })
    },
    initshowdata() {
      this.opencitynumber = this.alldata.country.vip_amount
      this.provincearray = this.alldata.country.cs_detail
      this.provincename = '全国'
    }
  }
}
</script>
<style lang="scss" scoped>
.BigChinaMaps{
  color: #fff;
  .bor{
    border: 1px solid #fff;
  }
  .bigmaptitle{
    font-size: 20px;
    margin: -30px 0 20px 0;
  }
  .mb{
    margin-bottom: 20px;
  }
  .table{
    border-collapse:collapse;
  }
  .tr{
    td{
      width: 200px;
      height: 40px;
      text-align: center;
      border: 1px solid #fff;
    }
  }
  .tableheader{
    width: 100%;
    height: 100%;
    position: relative;
    .city{
      position:absolute;
      left: 2px;
      bottom: 2px;
    }
    .line{
      position:absolute;
      width: 136px;
      border-top: 1px solid;
      top: 18px;
      left: -5px;
      transform:rotate(16.5deg);
    }
    .enterprise{
      position:absolute;
      right: 2px;
      top: 2px;
    }
  }
  .PopulationDensityOfHongKong{
      position: relative;
    }
    .prominent{
      position: absolute;
      left: 20px;
      bottom: 20px;
      width: 158px;
      height: 94px;
    }
    .color{
      div{
        height: 32px;
        border: 1px solid #fff;
        border-right: none;
        border-bottom: none;
      }
      div:nth-of-type(1){
        background-color: #0000FF;
      }
      div:nth-of-type(2){
        background-color: #FFFF00;
      }
      div:nth-of-type(3){
        background-color: #D9001B;
        border-bottom: 1px solid #fff;
      }
      width: 60px;
      height: 96px;
    }
    .prodata{
      div{
        height: 32px;
        border: 1px solid white;
        border-bottom: none;
      }
      div:nth-of-type(3){
        border-bottom: 1px solid white;
      }
      width: 98px;
      height: 96px;
      text-align: center;
      line-height: 32px;
    }
}
</style>
