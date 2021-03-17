<template>
  <div class="sales-member-data">
    <p class="qz-top-tips">提示：本页面所有会员数不包含暂停会员和标杆会员数；会员数量变化趋势图表，采用了落档数据（11月23日开始落档），因此，该图表于2020年11月23日开始可正常使用；</p>
    <el-row :gutter="10">
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="会员概览">
          <RingChart height="415px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20 mh" title="会员续费率">
          <RenewChart height="391px" />
        </qzBox>
      </el-col>
      <el-col :md="24" :lg="8" :xl="8" class="qz-col-lg-12">
        <qzBox class="mb20" title="会员数量变化趋势" subtitle="显示近期内装修会员数量的变化">
          <SmoothCharts height="415px" />
        </qzBox>
      </el-col>
    </el-row>
    <el-row :gutter="10">
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="useramountbul"
          class="mb20"
          title="总会员数"
          tips="总会员数=1.0有效会员（考虑倍数）+2.0有效会员"
          :num="useramount.amount+''"
          :yoy="useramount.month_percent.percent+'%'"
          :yclassname="useramount.month_percent.symbol"
          :mom="useramount.month_current_compare.percent+'%'"
          :mclassname="useramount.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="userconsumptiontotalbul"
          class="mb20"
          title="会员总消耗金额"
          tips="会员总消耗金额=1.0会员消耗金额+2.0会员消耗金额"
          unit="(元)"
          :num="userconsumptiontotal.amount+''"
          :yoy="userconsumptiontotal.month_percent.percent+'%'"
          :yclassname="userconsumptiontotal.month_percent.symbol"
          :mom="userconsumptiontotal.month_current_compare.percent+'%'"
          :mclassname="userconsumptiontotal.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="useramountv1bul"
          class="mb20"
          title="1.0会员数"
          :num="useramountv1.amount+''"
          :yoy="useramountv1.month_percent.percent+'%'"
          :yclassname="useramountv1.month_percent.symbol"
          :mom="useramountv1.month_current_compare.percent+'%'"
          :mclassname="useramountv1.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="userconsumptionv1bul"
          class="mb20"
          title="1.0会员消耗金额"
          tips="1.0会员消耗金额=查询时间段内,累加每个会员每天的日消耗金额"
          unit="(元)"
          :num="userconsumptionv1.amount+''"
          :yoy="userconsumptionv1.month_percent.percent+'%'"
          :yclassname="userconsumptionv1.month_percent.symbol"
          :mom="userconsumptionv1.month_current_compare.percent+'%'"
          :mclassname="userconsumptionv1.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="inputandoutputv1bul"
          class="mb20"
          title="1.0装企投入产出比"
          tips="1.0装企投入产出比=1.0总签单金额/1.0总消耗金额"
          :num="inputandoutputv1.amount+'%'"
          :yoy="inputandoutputv1.month_percent.percent+'%'"
          :yclassname="inputandoutputv1.month_percent.symbol"
          :mom="inputandoutputv1.month_current_compare.percent+'%'"
          :mclassname="inputandoutputv1.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="usercustomerpricev1bul"
          class="mb20"
          title="1.0会员客单价"
          tips="1.0会员客单价=1.0会员总收款/当月1.0有效会员数"
          unit="(元)"
          :num="usercustomerpricev1.amount+''"
          :yoy="usercustomerpricev1.month_percent.percent+'%'"
          :yclassname="usercustomerpricev1.month_percent.symbol"
          :mom="usercustomerpricev1.month_current_compare.percent+'%'"
          :mclassname="usercustomerpricev1.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard v-loading="userexceptgapbul" class="mb20" title="会员数差距" :num="userexceptgap.user_gap+''" tips="会员数差距=期望会员数-总会员数" title-item1="期望会员数" :title-item-num1="userexceptgap.except_user+''" :span-num1="24" title-item-tips1="由销售人员人工上传" />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="usercustomerpricebul"
          class="mb20"
          title="会员客单价"
          tips="会员客单价=总收款/总会员数"
          unit="(元)"
          :num="usercustomerprice.amount+''"
          :yoy="usercustomerprice.month_percent.percent+'%'"
          :yclassname="usercustomerprice.month_percent.symbol"
          :mom="usercustomerprice.month_current_compare.percent+'%'"
          :mclassname="usercustomerprice.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="useramountv2bul"
          class="mb20"
          title="2.0会员数"
          :num="useramountv2.amount+''"
          :yoy="useramountv2.month_percent.percent+'%'"
          :yclassname="useramountv2.month_percent.symbol"
          :mom="useramountv2.month_current_compare.percent+'%'"
          :mclassname="useramountv2.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="userconsumptionv2bul"
          class="mb20"
          title="2.0会员消耗金额"
          tips="累加从当月开始截止到当前，所有会员的轮单费支出金额"
          unit="(元)"
          :num="userconsumptionv2.amount+''"
          :yoy="userconsumptionv2.month_percent.percent+'%'"
          :yclassname="userconsumptionv2.month_percent.symbol"
          :mom="userconsumptionv2.month_current_compare.percent+'%'"
          :mclassname="userconsumptionv2.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="inputandoutputv2bul"
          class="mb20"
          title="2.0装企投入产出比"
          tips="2.0装企投入产出比=2.0总签单金额/2.0会员当月总消耗金额"
          :num="inputandoutputv2.amount+'%'"
          :yoy="inputandoutputv2.month_percent.percent+'%'"
          :yclassname="inputandoutputv2.month_percent.symbol"
          :mom="inputandoutputv2.month_current_compare.percent+'%'"
          :mclassname="inputandoutputv2.month_current_compare.symbol"
        />
      </el-col>
      <el-col :xs="12" :sm="8" :md="8" :lg="4" :xl="4" class="qz-col-lg-6 qz-col-xx-6 qz-col-xl-4">
        <qzCard
          v-loading="usercustomerpricev2bul"
          class="mb20"
          title="2.0会员客单价"
          tips="2.0会员客单价=2.0会员总收款/当月2.0有效会员数"
          unit="(元)"
          :num="usercustomerpricev2.amount+''"
          :yoy="usercustomerpricev2.month_percent.percent+'%'"
          :yclassname="usercustomerpricev2.month_percent.symbol"
          :mom="usercustomerpricev2.month_current_compare.percent+'%'"
          :mclassname="usercustomerpricev2.month_current_compare.symbol"
        />
      </el-col>
    </el-row>
    <div>
      <qzBox class="mb20" title="1" :border="0">
        <qzTabSwith slot="tab" tab1="城市数据明细" tab2="会员详情" tab3="会员补轮明细" fn="member" :tab-bj="showTable" :width="502" @tabSwitch="swithTab" />
        <qzMemberTable v-if="showTable == 1" @setTab="setTab" />
        <qz-member-info-table v-else-if="showTable == 2" :city-id="cityId" />
        <qzMemberMendingTable v-else-if="showTable == 3" :city-id="cityId" />
      </qzBox>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Memberdata',
  components: {
    qzCard: () => import('@/components/qzCard'),
    qzMemberTable: () => import('./member/memberTable'),
    qzMemberInfoTable: () => import('./member/memberInfoTable'),
    qzMemberMendingTable: () => import('./member/memberMendingTable'),
    RingChart: () => import('@/components/Echarts/RingChart/index'),
    RenewChart: () => import('@/components/Echarts/RenewChart/index'),
    SmoothCharts: () => import('@/components/Echarts/SmoothCharts/index')
  },
  data() {
    return {
      showTable: 1,
      cityId: '',
      //  会员总数量获取
      useramountbul: true,
      //  返回参数
      useramount: {
        amount: 0, // 总会员数
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      //  会员数差距
      userexceptgapbul: true,
      //  返回参数
      userexceptgap: {
        user_gap: 0, // 用户差距数
        except_user: 0// 预期会员数
      },
      //  会员总消耗金额
      userconsumptiontotalbul: true,
      //  返回参数
      userconsumptiontotal: {
        amount: 0, // 会员总消耗金额
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      //  会员客单价
      usercustomerpricebul: true,
      //  返回参数
      usercustomerprice: {
        amount: 0, // 会员客单价
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 1.0会员数指标获取
      useramountv1bul: true,
      //  返回参数
      useramountv1: {
        amount: 0, // 总会员数
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 2.0会员数指标获取
      useramountv2bul: true,
      //  返回参数
      useramountv2: {
        amount: 0, // 总会员数
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 1.0会员消耗金额
      userconsumptionv1bul: true,
      //  返回参数
      userconsumptionv1: {
        amount: 0, // 1.0会员消耗金额
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 2.0会员消耗金额
      userconsumptionv2bul: true,
      //  返回参数
      userconsumptionv2: {
        amount: 0, // 总会员数
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // //  1.0装企投入产出比
      inputandoutputv1bul: true,
      //  返回参数
      inputandoutputv1: {
        amount: 0, // 1.0装企投入产出比当前月实时
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 2.0装企投入产出比
      inputandoutputv2bul: true,
      //  返回参数
      inputandoutputv2: {
        amount: 0, // 2.0装企投入产出比当前月实时
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 1.0会员客单价
      usercustomerpricev1bul: true,
      //  返回参数
      usercustomerpricev1: {
        amount: 0, // 1.0会员客单价
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      },
      // 2.0会员客单价
      usercustomerpricev2bul: true,
      //  返回参数
      usercustomerpricev2: {
        amount: 0, // 2.0会员客单价
        month_percent: { // 月环比
          percent: 0, // 百分比
          symbol: 'plus'//  符号 空 无符号  加 plus  ；减  reduce ；
        },
        month_current_compare: { // 月同比
          percent: 0, // 百分比
          symbol: 'plus'// 符号 空 无符号  加 plus  ；减  reduce ；
        }
      }

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
    this.getuseramount()//  会员总数量获取
    this.getuserexceptgap()//  会员数差距
    this.getuserconsumptiontotal()//  会员总消耗金额
    this.getusercustomerprice()// 会员客单价
    this.getuseramountv1()//  1.0会员数
    this.getuseramountv2()//  2.0会员数
    this.getuserconsumptionv1()// 1.0会员消耗金额
    this.getuserconsumptionv2()// 2.0会员消耗金额
    this.getinputandoutputv1()//  1.0装企投入产出比
    this.getinputandoutputv2()// 2.0装企投入产出比
    this.getusercustomerpricev1()// 1.0会员客单价
    this.getusercustomerpricev2()// 2.0会员客单价
  },
  mounted() {},
  methods: {
    //  会员总数量获取
    getuseramount() {
      const params = {}
      this.$apis.SALES_MEMBER.getuseramount(params).then(res => {
        if (res.error_code === 0) {
          this.useramount = res.data
          this.useramountbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  会员数差距
    getuserexceptgap() {
      const params = {}
      this.$apis.SALES_MEMBER.getuserexceptgap(params).then(res => {
        if (res.error_code === 0) {
          this.userexceptgap = res.data
          this.userexceptgapbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  会员总消耗金额
    getuserconsumptiontotal() {
      const params = {}
      this.$apis.SALES_MEMBER.getuserconsumptiontotal(params).then(res => {
        if (res.error_code === 0) {
          this.userconsumptiontotal = res.data
          this.userconsumptiontotalbul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  会员总消耗金额
    getusercustomerprice() {
      const params = {}
      this.$apis.SALES_MEMBER.getusercustomerprice(params).then(res => {
        if (res.error_code === 0) {
          this.usercustomerprice = res.data
          this.usercustomerpricebul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  1.0会员数
    getuseramountv1() {
      const params = {}
      this.$apis.SALES_MEMBER.getuseramountv1(params).then(res => {
        if (res.error_code === 0) {
          this.useramountv1 = res.data
          this.useramountv1bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  2.0会员数
    getuseramountv2() {
      const params = {}
      this.$apis.SALES_MEMBER.getuseramountv2(params).then(res => {
        if (res.error_code === 0) {
          this.useramountv2 = res.data
          this.useramountv2bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  1.0会员消耗金额
    getuserconsumptionv1() {
      const params = {}
      this.$apis.SALES_MEMBER.getuserconsumptionv1(params).then(res => {
        if (res.error_code === 0) {
          this.userconsumptionv1 = res.data
          this.userconsumptionv1bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  2.0会员消耗金额
    getuserconsumptionv2() {
      const params = {}
      this.$apis.SALES_MEMBER.getuserconsumptionv2(params).then(res => {
        if (res.error_code === 0) {
          this.userconsumptionv2 = res.data
          this.userconsumptionv2bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  1.0装企投入产出比
    getinputandoutputv1() {
      const params = {}
      this.$apis.SALES_MEMBER.getinputandoutputv1(params).then(res => {
        if (res.error_code === 0) {
          this.inputandoutputv1 = res.data
          this.inputandoutputv1bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  2.0装企投入产出比
    getinputandoutputv2() {
      const params = {}
      this.$apis.SALES_MEMBER.getinputandoutputv2(params).then(res => {
        if (res.error_code === 0) {
          this.inputandoutputv2 = res.data
          this.inputandoutputv2bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  1.0会员客单价
    getusercustomerpricev1() {
      const params = {}
      this.$apis.SALES_MEMBER.getusercustomerpricev1(params).then(res => {
        if (res.error_code === 0) {
          this.usercustomerpricev1 = res.data
          this.usercustomerpricev1bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    //  2.0会员客单价
    getusercustomerpricev2() {
      const params = {}
      this.$apis.SALES_MEMBER.getusercustomerpricev2(params).then(res => {
        if (res.error_code === 0) {
          this.usercustomerpricev2 = res.data
          this.usercustomerpricev2bul = false
        } else {
          this.$message.error(res.error_msg)
        }
      }).catch(err => {
        this.$message.error(err)
      })
    },
    setTab(num) {
      this.showTable = num
    },
    swithTab(val) {
      this.showTable = val
      this.cityId = ''
      sessionStorage.removeItem('memberCityId')
      sessionStorage.removeItem('memberTime')
      sessionStorage.setItem('memberType', val)
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
}
</style>
