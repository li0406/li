<template>
  <div class="qz-card">
    <div class="box">
      <div class="title">
        <span>{{ title }}</span>
        <el-tooltip v-if="tips" effect="dark" placement="right">
          <div slot="content" v-html="tips" />
          <i class="el-icon-warning-outline" />
        </el-tooltip>
      </div>
      <div class="big-num">
        <span class="num">{{ num }}</span>
        <span v-if="unit" class="unit">{{ unit }}</span> <slot name="info" />
      </div>
      <div class="bottom">
        <el-row :gutter="5">
          <el-col v-if="titleItem1" :span="spanNum1">
            <span>{{ titleItem1 }}</span>
            <el-tooltip
              v-if="titleItemTips1"
              effect="dark"
              class="ml-5"
              placement="right"
            >
              <div slot="content" v-html="titleItemTips1" />
              <i class="el-icon-warning-outline" />
            </el-tooltip>
            <span class="ml-10">{{ titleItemNum1 }}</span>
          </el-col>
          <el-col v-if="titleItem2" :span="spanNum2">
            <span>{{ titleItem2 }}</span>
            <el-tooltip
              v-if="titleItemTips1"
              effect="dark"
              class="ml-5"
              placement="right"
            >
              <div slot="content" v-html="titleItemTips2" />
              <i class="el-icon-warning-outline" />
            </el-tooltip>
            <span class="ml-10">{{ titleItemNum2 }}</span>
          </el-col>
          <el-col v-if="mom" :span="momSpan">
            <span>月同比</span>
            <span class="percentage" :class="mclassname">{{ mom }}</span>
          </el-col>
          <el-col v-if="yoy" :span="yoySpan">
            <span>月环比</span>
            <span class="percentage" :class="yclassname">{{ yoy }}</span>
          </el-col>
          <el-col class="tr" :span="24">
            <slot name="more" />
          </el-col>
          <div v-if="showEcharts" class="DoughnutChart">
            <DoughnutChart
              :id-name="idName"
              :used="used"
              :used-color="usedColor"
              :surplus="surplus"
              :surplus-color="surplusColor"
              :used-tips="usedTips"
              :surplus-tips="surplusTips"
            />
          </div>
        </el-row>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  components: {
    DoughnutChart: () => import('@/components/Echarts/DoughnutChart/index')
  },
  props: {
    title: {
      type: String,
      default: ''
    },
    num: {
      type: String,
      default: ''
    },
    yoy: {
      type: String,
      default: ''
    },
    yoySpan: {
      type: Number,
      default: 12
    },
    yclassname: {
      type: String,
      default: ''
    },
    mom: {
      type: String,
      default: ''
    },
    momSpan: {
      type: Number,
      default: 12
    },
    mclassname: {
      type: String,
      default: ''
    },
    tips: {
      type: String,
      default: ''
    },
    unit: {
      type: String,
      default: ''
    },
    signed: {
      type: String,
      default: ''
    },
    signingAmount: {
      type: String,
      default: ''
    },
    titleItem1: {
      type: String,
      default: ''
    },
    titleItem2: {
      type: String,
      default: ''
    },
    titleItemNum1: {
      type: String,
      default: ''
    },
    titleItemNum2: {
      type: String,
      default: ''
    },
    titleItemTips1: {
      type: String,
      default: ''
    },
    titleItemTips2: {
      type: String,
      default: ''
    },
    spanNum1: {
      type: Number,
      default: 12
    },
    spanNum2: {
      type: Number,
      default: 12
    },
    showEcharts: {
      type: Boolean,
      default: false
    },
    idName: {
      // 绘制图表的id名字
      type: String,
      default: 'DoughnutChart'
    },
    used: {
      //   使用比
      type: Number,
      default: 0
    },
    usedColor: {
      // 使用比的颜色
      type: String,
      default: '#000'
    },
    surplus: {
      //  未使用比
      type: Number,
      default: 0
    },
    surplusColor: {
      // 未使用比的颜色
      type: String,
      default: '#fff'
    },
    usedTips: {
      //  使用比提示标题
      type: String,
      default: '使用比提示标题'
    },
    surplusTips: {
      //  未使用比提示标题
      type: String,
      default: '未使用比提示标题'
    }
  },
  data() {
    return {}
  },
  methods: {}
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.qz-card {
  color: #fff;

  .box {
    width: 100%;
    height: 140px;
    box-shadow: inset 0 0 11px #04e3ff;
    overflow: hidden;
    padding: 10px 15px;
    box-sizing: border-box;
    position: relative;
    &::after {
      content: "";
      display: block;
      width: 16px;
      height: 16px;
      position: absolute;
      left: -2px;
      top: -2px;
      border-top: 4px solid #04e3ff;
      border-left: 4px solid #04e3ff;
    }
    &::before {
      content: "";
      display: block;
      width: 16px;
      height: 16px;
      position: absolute;
      right: -2px;
      bottom: -2px;
      border-bottom: 4px solid #04e3ff;
      border-right: 4px solid #04e3ff;
    }
    .title {
      color: #04e3ff;
      font-size: 18px;
      line-height: 28px;
      .el-icon-warning-outline {
        margin-left: 10px;
        font-size: 18px;
      }
      .unit {
        float: right;
        font-size: 12px;
        color: #2c9cfa;
      }
    }
    .big-num {
      margin-top: 7px;
      margin-bottom: 10px;
      .num {
        font-size: 30px;
        font-weight: bold;
        color: #fff;
        line-height: 50px;
      }
      .unit {
        margin-left: 5px;
        font-size: 12px;
        color: #2c9cfa;
      }
    }

    .bottom {
      color: #2c9cfa;
      font-size: 12px;
      line-height: 20px;
      .percentage {
        margin-left: 5px;
        position: relative;
      }
      .tr {
        text-align: right;
      }
      .ml-10 {
        margin-left: 10px;
      }
      .ml-5 {
        margin-left: 5px;
      }
      .plus {
        margin-left: 3px;
        padding-left: 13px;
        &::after{
          content: '';
          position: absolute;
          left: 0;
          top: 5px;
          width: 0;
          height: 0;
          border-bottom: 9px solid #68BD98;
          border-left: 5px solid transparent;
          border-right: 5px solid transparent;
        }
      }
      .reduce {
        margin-left: 3px;
        padding-left: 14px;
        &::after{
          content: '';
          position: absolute;
          left: 1px;
          top: 5px;
          width: 0;
          height: 0;
          border-top: 9px solid #CB6A90;
          border-left: 5px solid transparent;
          border-right: 5px solid transparent;
        }
      }
    }
    .DoughnutChart {
      position: absolute;
      right: 0;
      bottom: 0;
    }
  }
}
</style>
