<template>
  <div class="sales-city">
    <p class="qz-top-tips">提示：城市订单统计报表，统计当月的数据。其中，签单距离占比，采用了落档数据（2020年11月23日开始落档），因此，该图表于2020年12月开始可正常使用；</p>
    <el-row :gutter="10">
      <el-col :xs="24" :sm="24" :md="24" :lg="13" :xl="13" class="qz-col-xl-13 qz-col-xx-24 qz-col-lg-24">
        <el-row :gutter="10">
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="totalbul"
              class="mb20"
              title="总单量"
              :num="totalorder.count+''"
              tips="有效单，分单+赠单的合计"
              :yoy="totalorder.month_percent.percent+'%'"
              :yclassname="totalorder.month_percent.symbol"
              :mom="totalorder.year_percent.percent+'%'"
              :mclassname="totalorder.year_percent.symbol"
            />
          </el-col>
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="splitbul"
              class="mb20"
              title="实际分单量"
              :num="splitorder.count+''"
              :yoy="splitorder.month_percent.percent+'%'"
              :yclassname="splitorder.month_percent.symbol"
              :mom="splitorder.year_percent.percent+'%'"
              :mclassname="splitorder.year_percent.symbol"
            />
          </el-col>
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="givebul"
              class="mb20"
              title="实际赠单量"
              :num="giveorder.count+''"
              :yoy="giveorder.month_percent.percent+'%'"
              :yclassname="giveorder.month_percent.symbol"
              :mom="giveorder.year_percent.percent+'%'"
              :mclassname="giveorder.year_percent.symbol"
            />
          </el-col>
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="roombul"
              class="mb20"
              title="量房率"
              :num="room.now_percent.rate+'%'"
              tips="量房率=量房量/总单量"
              :yoy="room.month_percent.percent+'%'"
              id-name="Measuringroom"
              :show-echarts="drawroom"
              :used="room.now_percent.count"
              used-color="#2384EA"
              :surplus="room.now_percent.reverse_count"
              :yclassname="room.month_percent.symbol"
              surplus-color="#535A8F"
              used-tips="量房率占比"
              surplus-tips="未量房率比"
            />
          </el-col>
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="signbul"
              class="mb20"
              title="签单率"
              :num="sign.now_rate.sign_rate	+'%'"
              tips="签单率=签单量/总单量"
              :yoy="sign.month_percent.percent+'%'"
              :mom-span="24"
              id-name="Signthebill"
              :show-echarts="drawsign"
              :used="sign.now_rate.sign_amount"
              used-color="#68BD98"
              :surplus="sign.now_rate.sign_amount_reduce"
              :yclassname="sign.month_percent.symbol"
              surplus-color="#535A8F"
              used-tips="签单率占比"
              surplus-tips="未签单率比"
            />
          </el-col>
          <el-col :xs="12" :sm="8" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="signmoneytotalbul"
              class="mb20"
              title="总签单金额"
              :num="signmoneytotal.money_total+''"
              tips="总签单金额：累加每个申请签单的签单金额"
              title-item1="签单量"
              :title-item-num1="signmoneytotal.sign_amount+''"
              :span-num1="10"
              title-item2="平均签单金额"
              :title-item-num2="signmoneytotal.sign_average_price+''"
              :span-num2="14"
              unit="(万元)"
            />
          </el-col>
        </el-row>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="11" :xl="11" class="qz-col-xl-11 qz-col-xx-24 qz-col-lg-24">
        <qzBox id="city-funnel" class="mb20" title="订单转化">
          <FunnelChart height="214px" />
        </qzBox>
      </el-col>
    </el-row>
    <el-row class="row-bg" justify="space-between" :gutter="10">
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="签单距离占比">
          <MixLineBar height="426px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="订单面积占比" subtitle="不同面积的订单在总单量中的占比">
          <RefererOfaWebsite height="415px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="城市签单排行榜">
          <WorldTotalPopulation height="415px" />
        </qzBox>
      </el-col>
    </el-row>
    <div>
      <qzBox title="城市数据明细" :border="0">
        <qzSalesTable />
      </qzBox>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  name: 'Citydata',
  components: {
    qzCard: () => import('@/components/qzCard'),
    qzSalesTable: () => import('./city/cityTable'),
    FunnelChart: () => import('@/components/Echarts/FunnelChart/index'),
    MixLineBar: () => import('@/components/Echarts/MixLineBar/index'),
    RefererOfaWebsite: () => import('@/components/Echarts/RefererOfaWebsite/index'),
    WorldTotalPopulation: () => import('@/components/Echarts/WorldTotalPopulation/index')
  },
  data() {
    return {
      //  loading
      totalbul: true, // 总单量
      splitbul: true, //  分单量
      givebul: true, //  赠单量
      roombul: true, // 量房率
      signbul: true, // 签单率
      signmoneytotalbul: true, // 总签单量金额
      //  城市报表- 总单量/分单量/赠单量返回参数
      totalorder: {
        count: 0,
        month_percent: {
          percent: 0,
          symbol: 'plus'
        },
        year_percent: {
          percent: 0,
          symbol: 'plus'
        }
      },
      splitorder: {
        count: 0,
        month_percent: {
          percent: 0,
          symbol: 'plus'
        },
        year_percent: {
          percent: 0,
          symbol: 'plus'
        }
      },
      giveorder: {
        count: 0,
        month_percent: {
          percent: 0,
          symbol: 'plus'
        },
        year_percent: {
          percent: 0,
          symbol: 'plus'
        }
      },
      //  城市报表-量房率 返回参数
      drawroom: false,
      room: {
        now_percent: {
          rate: 0, // 签单率
          count: 0, // 签单量
          reverse_count: 0// 未签单量
        },
        month_percent: {
          percent: 0, // 环比
          symbol: 'plus'
        }
      },
      //  城市报表-签单率 返回参数
      drawsign: false,
      sign: {
        now_rate: {
          sign_rate: 0, // 签单率
          sign_amount: 0, // 签单量
          sign_amount_reduce: 0// 未签单量
        },
        month_percent: {
          percent: 0, // 环比
          symbol: 'plus'
        }
      },
      //  总签单量金额
      signmoneytotal: {
        money_total: 0, // 本地签单总金额
        sign_amount: 0, // 本月签单量
        sign_average_price: 0//  平均签单金额

      }
    }
  },
  computed: {
    ...mapState({
      sidebar: (state) => state.app.sidebar
    })
  },
  created() {
    //  城市报表- 总单量/分单量/赠单量
    this.allcount('')//  类型 默认空总单量 1分单 2.赠单
    this.allcount(1)//  类型 默认空总单量 1分单 2.赠单
    this.allcount(2)//  类型 默认空总单量 1分单 2.赠单
    this.liangfang()//  城市报表-量房率
    this.getsignrate()//  城市报表-签单率
    this.getsignmoneytotal()// 总签单量金额
  },
  methods: {
    //  城市报表- 总单量/分单量/赠单量
    allcount(type = '') {
      const params = {
        type//  类型 默认空总单量 1分单 2.赠单
      }
      this.$apis.SALES_CITY.allcount(params).then(res => {
        if (res.error_code === 0) {
          if (type === '') {
            this.totalorder = res.data//  总单量
            this.totalbul = false
          } else if ((type === 1)) {
            this.splitorder = res.data//  分单
            this.splitbul = false
          } else if ((type === 2)) {
            this.giveorder = res.data// 赠单
            this.givebul = false
          }
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  城市报表-量房率
    liangfang() {
      const params = {}
      this.$apis.SALES_CITY.liangfang(params).then(res => {
        if (res.error_code === 0) {
          this.room = res.data
          this.drawroom = true
          this.roombul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  城市报表-签单率
    getsignrate() {
      const params = {}
      this.$apis.SALES_CITY.getsignrate(params).then(res => {
        if (res.error_code === 0) {
          this.sign = res.data
          this.drawsign = true
          this.signbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  总签单量金额
    getsignmoneytotal() {
      const params = {}
      this.$apis.SALES_CITY.getsignmoneytotal(params).then(res => {
        if (res.error_code === 0) {
          this.signmoneytotal = res.data
          this.signmoneytotalbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    }
  } // 方法集合
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.sales-city {
  padding: 20px 15px;
}
</style>
