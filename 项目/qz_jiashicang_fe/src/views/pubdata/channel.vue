<template>
  <div class="sales-member-data">
    <!-- <p class="qz-top-tips">提示：会员数量变化趋势图表，采用了落档数据（11月23日开始落档），因此，该图表于2020年11月23日开始可正常使用；</p> -->
    <el-row :gutter="10">
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20" title="各部门订单转化">
          <StackedAreaChart height="415px" />
        </qzNewBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20 mh" title="各部门订单浪费分析">
          <el-tooltip class="item-tips2" effect="dark" content="统计数据时间为T-1" placement="right">
            <i class="el-icon-warning-outline" />
          </el-tooltip>
          <OrderWasteChart height="415px" />
        </qzNewBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20 mh pos-r" title="城市发单浪费率">
          <SendOrderWasteCharts height="415px" />
        </qzNewBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20 mh" title="发单时间段分布">
          <HairOrderChart height="415px" />
        </qzNewBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20 mh" title="注册用户发单周期">
          <AppHairOrderEcharts height="415px" />
        </qzNewBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzNewBox class="mb20 mh" title="订单备注分析">
          <el-tooltip class="item-tips6" effect="dark" content="统计数据时间为T-1" placement="right">
            <i class="el-icon-warning-outline" />
          </el-tooltip>
          <AbnormaOrderEcharts height="415px" />
        </qzNewBox>
      </el-col>
    </el-row>
    <div>
      <qzBox class="mb20" :border="0">
        <qzTabSwith slot="tab" tab1="渠道数据统计" tab2="发单位置数据统计" :tab-bj="showTable" :width="404" @tabSwitch="swithTab" />
        <qzChannelTable :item-index="showTable" />
      </qzBox>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Memberdata',
  components: {
    qzChannelTable: () => import('./channel/channelTable'),
    AbnormaOrderEcharts: () => import('@/components/Echarts/AbnormaOrderEcharts/index'),
    OrderWasteChart: () => import('@/components/Echarts/OrderWasteChart/index'),
    SendOrderWasteCharts: () => import('@/components/Echarts/SendOrderWasteCharts/index'),
    StackedAreaChart: () => import('@/components/Echarts/StackedAreaChart/index'),
    HairOrderChart: () => import('@/components/Echarts/HairOrderChart/index'),
    AppHairOrderEcharts: () => import('@/components/Echarts/AppHairOrderEcharts/index')
  },
  data() {
    return {
      showTable: 1,
      cityId: '',
      //  会员总数量获取
      useramountbul: true,
      dataEasyChart: [],
      xData: []
    }
  },
  watch: {},
  created() {
    const memberType = sessionStorage.getItem('memberType')
    const memberCityId = sessionStorage.getItem('memberCityId')
    if (memberType) {
      this.showTable = Number(memberType)
    }
    if (memberCityId) {
      this.cityId = memberCityId
    }
    this.getData()//  会员总数量获取
  },
  mounted() {},
  methods: {
    setTab(num) {
      this.showTable = num
    },
    getData() {
      this.$apis.SALES_FEN.orderfill().then(res => {
        // console.log(res.data.series[0].data)
        // console.log(res.data.xAxis)
        this.dataEasyChart = res.data.series[0].data
        this.xData = res.data.xAxis
      })
    },
    swithTab(val) {
      this.showTable = val
    }
  }
}
</script>

<style lang="scss" scoped>
.sales-member-data{
  padding: 20px 15px;
  .mh{
    height: 501.36px;
  }
  .pos-r{
    position: relative;
  }
  ::v-deep .FilterMonth{
    $m-tr:30px;
    position: absolute;
    right: $m-tr;
    top: $m-tr;
  }
  .item-tips2{
    position: absolute;
    left: 205px;
    top: 36px;
    z-index: 1;
    font-size: 20px;
  }
  .item-tips6{
    position: absolute;
    left: 145px;
    top: 36px;
    z-index: 1;
    font-size: 20px;
  }
}

</style>
