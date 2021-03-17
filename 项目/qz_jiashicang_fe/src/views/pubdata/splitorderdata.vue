<template>
  <div class="splitorderdata">
    <p class="qz-top-tips">提示：本页面除订单回单次数之外的所有数据只针对实际分单量进行统计分析。其中，分单客单价、分单满足率、分单浪费率、补轮通过率，均采用了落档数据（2020年11月23日开始落档），导致12月份之前的数据不完整，因此，这些统计图表于2020年12月开始可正常使用；</p>
    <el-row :gutter="10">
      <el-col :xs="24" :sm="24" :md="24" :lg="13" :xl="13" class="qz-col-lg-24">
        <el-row :gutter="10">
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="consumptionbul"
              class="mb20"
              title="总消耗金额"
              :num="consumption.consumption_total+''"
              tips="会员总消耗金额=1.0会员消耗金额+2.0会员消耗金额"
              title-item1="1.0消耗"
              :title-item-num1="consumption.consumption_v1+''"
              title-item2="2.0消耗"
              :title-item-num2="consumption.consumption_v2+''"
              unit="(元)"
            />
          </el-col>
          <!-- <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="distributebul"
              class="mb20"
              title="分单平均分配次数"
              tips="分单平均分配次数=分配分单量/分单量"
              :num="distribute.distribute_number+''"
              :yoy="distribute.month_percent.percent+'%'"
              :yclassname="distribute.month_percent.symbol"
            />
          </el-col> -->
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="distributebul"
              class="mb20"
              title="分单平均分配次数"
              tips="分单平均分配次数=分配分单量/分单量"
              :num="distribute.distribute_number+''"
            />
          </el-col>
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="rebutbul"
              class="mb20"
              title="订单撤回次数"
              :num="rebut.rebut_number+''"
              tips="统计订单分配给会员后又撤回的订单数量"
              title-item1="撤回占比"
              :title-item-num1="rebut.percent+'%'"
              title-item-tips1="撤回占比=撤回次数/（总单量+撤回状态的订单量）"
              id-name="OrderWithdrawal"
              :show-echarts="drawrebut"
              :used="rebut.rebut_number"
              used-color="#8966E1"
              :surplus="rebut.rebut_number_reduce"
              surplus-color="#535A8F"
              used-tips="订单撤回次数占比"
              surplus-tips="订单未撤回次数占比"
              :span-num1="24"
            >
              <span slot="info" class="info-btn" @click="infoBtn">详情<i class="el-icon-arrow-right" /></span>
            </qzCard>
          </el-col>
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="applybul"
              class="mb20"
              title="申请补轮数量"
              tips="申请补轮数量,统计第一次申请时间在当月的订单个数;<br/>申请补轮率=申请补轮数量/分给2.0会员的分单数"
              :num="apply.now_percent.count+''"
              title-item1="申请补轮率"
              :title-item-num1="apply.now_percent.rate+'%'"
              :yoy="apply.month_percent.percent+'%'"
              :yclassname="apply.month_percent.symbol+''"
            />
          </el-col>
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="applypassbul"
              class="mb20"
              title="实际已补轮数量"
              tips="实际已补轮数量，统计补轮单的第一次审核通过时间在当月的订单数量<br/>补轮通过率=实际已补轮数量/当月分给2.0会员的实际分单量"
              title-item1="补轮通过率 "
              :num="applypass.now_percent.count+''"
              :title-item-num1="applypass.now_percent.rate+'%'"
              :yoy="applypass.month_percent.percent+'%'"
              :yclassname="applypass.month_percent.symbol+''"
            />
          </el-col>
          <el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8" class="qz-col-xl-8 qz-col-xx-6 qz-col-lg-6">
            <qzCard
              v-loading="violatebul"
              class="mb20"
              title="违规补轮次数"
              :num="violate.amount+''"
              tips="按照新会员充值->扣款类型为“违规补轮扣费”进行统计"
            >
              <span slot="more" class="more-btn" @click="moreBtn">更多<i class="el-icon-arrow-right" /></span>
            </qzCard>
          </el-col>
        </el-row>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="11" :xl="11" class="qz-col-lg-24">
        <qzBox class="mb20" title="分单客单价" subtitle="(1.0+2.0)会员消耗金额/实际分配数(单位：元)">
          <SimpleExampleofDataset class="SimpleExampleofDataset" height="214px" />
        </qzBox>
      </el-col>
    </el-row>
    <el-row class="row-bg" :gutter="10">
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="分单满足率">
          <LineChart height="415px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="分单浪费率" subtitle="分单浪费次数/理论分配数">
          <MultipleYAxes height="415px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="补轮通过率" subtitle="补轮量/分给2.0会员实际分单量">
          <MixedLineandBar height="415px" />
        </qzBox>
      </el-col>
    </el-row>
    <div>
      <qzBox class="mb20" title="城市分单数据明细" :border="0">
        <qzFenOrderTable />
      </qzBox>
    </div>

    <el-dialog title="订单撤回详情" :visible.sync="dialogInfo">
      <qzFenInfoTable :state="dialogInfo" />
    </el-dialog>

    <el-dialog title="违规补轮次数详情" :visible.sync="dialogMore">
      <qzFenMoreTable :state="dialogMore" />
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'Splitorderdata',
  components: {
    qzCard: () => import('@/components/qzCard'),
    qzFenOrderTable: () => import('./fenOrder/fenOrderTable'),
    qzFenInfoTable: () => import('./fenOrder/fenInfoTable'),
    qzFenMoreTable: () => import('./fenOrder/fenMoreTable'),
    SimpleExampleofDataset: () => import('@/components/Echarts/SimpleExampleofDataset/index'),
    LineChart: () => import('@/components/Echarts/LineChart/index'),
    MultipleYAxes: () => import('@/components/Echarts/MultipleYAxes/index'),
    MixedLineandBar: () => import('@/components/Echarts/MixedLineandBar/index')
  },
  data() {
    return {
      dialogMore: false,
      dialogInfo: false,
      moreData: [],
      infoData: [],
      statistics: '', // 弹框统计时间

      //  总消耗金额
      consumptionbul: true,
      //  返回参数
      consumption: {
        consumption_total: 0, //  总消耗金额
        consumption_v1: 0, // 1.0消耗金额
        consumption_v2: 0//  2.0消耗金额
      },

      //  分单平均分配次数
      distributebul: true,
      distribute: {
        distribute_number: 0, // 平均分配次数
        month_percent: {
          percent: 0, // 百分比
          symbol: 'plus'
        }
      },

      //  分单数据-申请补轮量
      applybul: true,
      //  返回参数
      apply: {
        now_percent: { //  申请补轮率/量
          rate: 0, // 率
          count: 0 // 量
        },
        month_percent: {
          percent: 0, // 环比
          symbol: 'plus'
        }
      },

      //  分单数据-实际已补轮数量
      applypassbul: true,
      //  返回参数
      applypass: {
        now_percent: { //  实际已补轮率/量
          rate: 0, // 率
          count: 0, // 量
          reverse_count: 0// 未签单量
        },
        month_percent: {
          percent: 0, // 环比
          symbol: 'plus'
        }
      },

      //  订单撤回次数
      rebutbul: true,
      drawrebut: false,
      //  返回参数
      rebut: {
        percent: 0, // 撤回占比
        rebut_number: 0, //  撤回量
        rebut_number_recude: 0, // 未撤回數量
        icon_show: false // 是否显示占比圈，true显示，false不显示
      },

      //  违规补轮次数
      violatebul: true,
      //  返回参数
      violate: {
        amount: 0
      }
    }
  },
  created() {
    this.consumptiontotal()//  总消耗金额
    this.applyorder()//  分单数据-申请补轮量
    this.distributeorder()//  分单平均分配次数
    this.applypassorder()//  分单数据-实际已补轮数量
    this.orderrebut()// 订单撤回次数
    this.violateapply() // 违规补轮次数
  },
  methods: {
    //  总消耗金额
    consumptiontotal() {
      const params = {}
      this.$apis.SALES_FEN.consumptiontotal(params).then(res => {
        if (res.error_code === 0) {
          this.consumption = res.data
          this.consumptionbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  分单数据-申请补轮量
    applyorder() {
      const params = {}
      this.$apis.SALES_FEN.applyorder(params).then(res => {
        if (res.error_code === 0) {
          this.apply = res.data
          this.applybul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  总消耗金额
    distributeorder() {
      const params = {}
      this.$apis.SALES_FEN.distributeorder(params).then(res => {
        if (res.error_code === 0) {
          this.distribute = res.data
          this.distributebul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  分单数据-实际已补轮数量
    applypassorder() {
      const params = {}
      this.$apis.SALES_FEN.applypassorder(params).then(res => {
        if (res.error_code === 0) {
          this.applypass = res.data
          this.applypassbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  订单撤回次数
    orderrebut() {
      const params = {}
      this.$apis.SALES_FEN.orderrebut(params).then(res => {
        if (res.error_code === 0) {
          this.rebut = res.data
          this.drawrebut = true
          this.rebutbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  违规补轮次数
    violateapply() {
      const params = {}
      this.$apis.SALES_FEN.violateapply(params).then(res => {
        if (res.error_code === 0) {
          this.violate = res.data
          this.violatebul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    moreBtn() {
      this.dialogMore = true
    },
    infoBtn() {
      this.dialogInfo = true
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss">
.splitorderdata {
  padding: 20px 15px;
  .more-btn{
    cursor: pointer;
  }
  .info-btn{
    cursor: pointer;
    color: #2C9CFA;
    font-size: 14px;
    line-height: 1;
  }
  .statistics{
    color: #2C9CFA;
    line-height: 30px;
    margin: 0 0 10px;
  }
  .el-dialog{
    .el-dialog__body{
      padding-top: 10px;
    }
  }
}
</style>
