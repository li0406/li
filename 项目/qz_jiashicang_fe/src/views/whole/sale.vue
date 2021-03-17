<template>
  <div class="whole-sale">
    <h2 class="top-title">销售中心驾驶舱</h2>
    <div class="main">
      <el-row :gutter="20">
        <el-col :span="6">
          <div class="policy-decision-top">
            <h4 class="mian-title main-title-pd">签约企业决策图</h4>
            <qzNewBox title="签约企业分析" :border="0">
              <FilterMonth @setMonthParams="signvipanalysis" />
              <el-row class="enterprise">
                <el-col
                  v-for="(item, index) of resDataObj.target"
                  :key="index"
                  :span="8"
                >
                  <div class="font15 mb10">{{ item.name }}</div>
                  <div class="font28">{{ item.count }}</div>
                </el-col>
              </el-row>
              <div class="mtb text-center">
                {{
                  showcut === "sign_city"
                    ? "城市新签企业数量占比"
                    : "城市到期企业数量占比"
                }}
              </div>
              <div class="flex spa-aro ali-ite font50 piechart-div">
                <div
                  class="el-icon-arrow-left leftarrow point"
                  @click="cutcharts('sign_city')"
                />
                <div>
                  <PieSpecialLabel
                    ref="piedom"
                    :key="showcut"
                    width="350px"
                    height="320px"
                    :pie-data-array="pieDataArray"
                  />
                </div>
                <div
                  class="el-icon-arrow-right rightarrow point"
                  @click="cutcharts('mature_city')"
                />
              </div>
            </qzNewBox>
          </div>
        </el-col>
        <el-col :span="12">
          <div v-loading="mapLoading" class="map">
            <qz-sale-map v-if="mapList.data" :map-list="mapList" />
            <div class="switch-btn">
              <el-button type="text" @click="dqSwitch">切换</el-button>
            </div>
            <div v-show="isShowData" class="switch-data">
              <qz-sale-map-data :map-data="mapDetail" />
            </div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="feedback-top">
            <h4 class="mian-title main-title-f">订单问题反馈图</h4>
            <qzNewBox title="城市订单价格不足TOP20" :border="0">
              <StackedBarChart height="415px" />
            </qzNewBox>
          </div>
        </el-col>
        <el-col :span="7">
          <div class="policy-decision-bottom">
            <qzNewBox class="mt30 pos-r" title="城市企业潜力分析" :border="0">
              <i class="el-icon-location icon-location" @click="showMap" />
              <qzPotentialTable />
            </qzNewBox>
            <StackedLineChart height="398px" />
          </div>
        </el-col>
        <el-col :span="10" class="mt10">
          <div class="profit">
            <h4 class="mian-title main-title-p">成本效益图</h4>
            <MultipleColumnar />
            <qzNewBox title="全国城市重点数据" :border="0">
              <keyCitiesTable />
              <el-button
                type="text"
                class="fr"
                @click="toCityData"
              >更多</el-button>
            </qzNewBox>
          </div>
        </el-col>
        <el-col :span="7">
          <div class="feedback-bottom">
            <qzNewBox class="mt30 pos-r" title="当月城市缺单TOP20" :border="0">
              <el-button
                type="text"
                class="qd-btn"
                @click="toFendanData"
              >更多</el-button>
              <qzQdTable />
            </qzNewBox>
            <DynamicData height="391px" />
          </div>
        </el-col>
      </el-row>
    </div>
    <el-dialog top="5vh" :visible.sync="isShowMap">
      <PopulationDensityOfHongKong ref="chinaMapEcharts" height="600px" />
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'Sale',
  components: {
    PieSpecialLabel: () => import('@/components/Echarts/PieSpecialLabel/index'),
    qzSaleMap: () => import('./sales/qzSaleMap'),
    qzPotentialTable: () => import('./sales/potentialTable'),
    qzQdTable: () => import('./sales/qdTable'),
    keyCitiesTable: () => import('./sales/keyCitiesTable'),
    MultipleColumnar: () =>
      import('@/components/Echarts/MultipleColumnar/index'),
    DynamicData: () => import('@/components/Echarts/DynamicData/index'),
    StackedLineChart: () =>
      import('@/components/Echarts/StackedLineChart/index'),
    StackedBarChart: () => import('@/components/Echarts/StackedBarChart/index'),
    PopulationDensityOfHongKong: () =>
      import('@/components/Echarts/PopulationDensityOfHongKong/index'),
    QzSaleMapData: () => import('./sales/qzSaleMapData')
  },
  data() {
    return {
      showcut: 'sign_city',
      pieDataArray: [],
      resDataObj: {
        target: [],
        sign_city: [], // 城市新签企业数量占比
        mature_city: [] // 城市到期企业数量占比
      },
      mapLoading: false,
      mapDetail: {},
      mapList: {},
      showTable: 1,
      isShowMap: false,
      isShowData: false
    }
  },
  created() {
    this.getMapInfo()
    this.signvipanalysis(1)
  },
  methods: {
    cutcharts(showcut) {
      this.showcut = showcut
      const dataagg = {
        sign_city: this.resDataObj.sign_city.map((item) => {
          return { name: item.name, value: item.count }
        }),
        mature_city: this.resDataObj.mature_city.map((item) => {
          return { name: item.name, value: item.count }
        })
      }
      this.pieDataArray = dataagg[showcut]
    },
    signvipanalysis(tab_month) {
      const params = {
        tab_month
      }
      this.$apis.SALES_MAIN.signvipanalysis(params)
        .then((res) => {
          if (res.error_code === 0) {
            this.resDataObj = res.data
            this.cutcharts(this.showcut)
          } else {
            this.$message.error(res.error_msg)
          }
        })
        .catch((err) => {
          console.log(err)
        })
    },
    toCityData() {
      this.$router.push({ path: '/pubdata/citydata' })
    },
    toFendanData() {
      this.$router.push({ path: '/pubdata/splitorderdata' })
    },
    swithTab(val) {
      this.showTable = val
    },
    showMap() {
      this.isShowMap = true
      this.$nextTick(() => {
        this.$refs.chinaMapEcharts.sfsignanalysis()
      })
    },
    dqSwitch() {
      this.isShowData = !this.isShowData
    },
    getMapInfo() {
      this.mapLoading = true
      this.$apis.SALES_MAIN.departachievement().then((res) => {
        this.mapLoading = false
        if (res.error_code === 0) {
          this.mapList = res.data.list
          this.mapDetail = res.data.detail
        } else {
          this.$message.error(res.error_msg)
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
$myColor: #04e3ff;
$centerHeight: 500px;
.whole-sale {
  padding: 20px;
  .top-title {
    text-align: center;
    font-size: 32px;
    margin: 0;
  }
  .main {
    .map {
      height: $centerHeight;
      position: relative;
      .switch-btn {
        z-index: 4;
        position: absolute;
        right: 20px;
        top: 10px;
        ::v-deep .el-button {
          font-size: 18px;
        }
      }
      .switch-data {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 820px;
        height: 500px;
        background: rgba($color: #0a145f, $alpha: 0.8);
        z-index: 3;
      }
    }
    .mian-title {
      width: 286px;
      height: 50px;
      position: absolute;
      left: -2px;
      top: -25px;
      font-size: 26px;
      line-height: 50px;
      padding-left: 80px;
    }
    .mian-title::after {
      content: "";
      display: block;
      width: 20px;
      height: 50px;
      position: absolute;
      right: -20px;
      top: 0;
      background: #0a145f;
    }
    .main-title-pd {
      background: #0a145f url(~@/assets/imgs/sale/qy-bj.png) 0 0 no-repeat;
      color: #fff131;
    }

    .main-title-p {
      background: #0a145f url(~@/assets/imgs/sale/cb-bj.png) 0 0 no-repeat;
      color: #ffa71d;
      padding-left: 100px;
    }

    .main-title-f {
      background: #0a145f url(~@/assets/imgs/sale/dd-bj.png) 0 0 no-repeat;
      color: #f3392d;
    }
    .policy-decision-top {
      position: relative;
      padding: 40px 20px 20px;
      height: $centerHeight;
      border: 2px solid #fff131;
      border-bottom: none;
    }
    .policy-decision-bottom {
      position: relative;
      padding: 20px;
      border: 2px solid #fff131;
      border-top: none;
    }
    .policy-decision-top::after {
      content: "";
      position: absolute;
      right: -77px;
      bottom: -55px;
      display: block;
      width: 77px;
      height: 55px;
      border-bottom: 2px solid #fff131;
      border-left: 2px solid #fff131;
      border-bottom-left-radius: 100%;
    }
    .policy-decision-bottom::after {
      content: "";
      position: absolute;
      right: -7px;
      top: 53px;
      display: block;
      width: 5px;
      height: 2px;
      background: #0a145f;
    }
    .policy-decision-bottom::before {
      content: "";
      position: absolute;
      right: -2px;
      top: 0;
      display: block;
      width: 2px;
      height: 53px;
      background: #0a145f;
    }

    .feedback-top {
      position: relative;
      padding: 40px 20px 20px;
      height: $centerHeight;
      border: 2px solid #f3392d;
      border-bottom: none;
    }
    .feedback-bottom {
      position: relative;
      padding: 20px;
      border: 2px solid #f3392d;
      border-top: none;
    }
    .feedback-top::after {
      content: "";
      position: absolute;
      left: -77px;
      bottom: -57px;
      display: block;
      width: 77px;
      height: 57px;
      border-bottom: 2px solid #f3392d;
      border-right: 2px solid #f3392d;
      border-bottom-right-radius: 100%;
    }
    .feedback-bottom::after {
      content: "";
      position: absolute;
      left: -7px;
      top: 55px;
      display: block;
      width: 5px;
      height: 2px;
      background: #0a145f;
    }
    .feedback-bottom::before {
      content: "";
      position: absolute;
      left: -2px;
      top: 0;
      display: block;
      width: 2px;
      height: 55px;
      background: #0a145f;
    }
    .profit {
      margin-top: 50px;
      position: relative;
      padding: 40px 20px 20px;
      border: 2px solid #ffa71d;
    }
    .mt30 {
      margin-top: 30px;
    }
    .fr {
      float: right;
    }
    .pos-r {
      position: relative;
    }
    .qd-btn {
      position: absolute;
      right: 0;
      top: 5px;
    }
    .icon-location {
      position: absolute;
      left: 170px;
      top: 14px;
      font-size: 22px;
      color: #2c9cf9;
      z-index: 2;
      cursor: pointer;
    }
    .enterprise {
      margin: 40px 0 0 0;
      text-align: center;
    }
    .font28 {
      font-size: 28px;
    }
    .font50 {
      font-size: 50px;
    }
    .spa-aro {
      justify-content: space-around;
    }
    .spa-bet {
      justify-content: space-between;
    }
    .ali-ite {
      align-items: center;
    }
    .mtb {
      margin: 40px 0 -20px 0;
    }
    .text-center {
      text-align: center;
    }
    .point {
      cursor: pointer;
    }
    .font15 {
      font-size: 15px;
    }
    .mb10 {
      margin-bottom: 10px;
    }
    .piechart-div {
      position: relative;
    }
    .leftarrow {
      position: absolute;
      top: 100px;
      left: 0;
      opacity: 0.3;
      z-index: 1;
    }
    .rightarrow {
      position: absolute;
      top: 100px;
      right: 0;
      opacity: 0.3;
      z-index: 1;
    }
    ::v-deep .FilterMonth {
      position: absolute;
      right: 0px;
      top: 10px;
    }
    .bor {
      border: 1px solid;
    }
  }
}
</style>
