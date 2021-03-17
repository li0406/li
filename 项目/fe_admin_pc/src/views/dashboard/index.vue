<template>
  <div class="dashboard-container">
    <h2 class="title">首页</h2>
    <div class="dashboard-box">
      <h3>今日数据</h3>
      <el-row class="w1200" :gutter="20">
        <el-col :span="8">
          <el-card>
            <h5>今日收入（元）</h5>
            <div class="pice">{{ info.totalBenefit }}</div>
            <el-card class="bottom">日环比：- -</el-card>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <h5>今日付费用户</h5>
            <div class="pice">{{ info.totayConsumers }}</div>
            <el-card class="bottom">
              <el-row>
                <el-col :span="14">昨日付费用户：{{ info.preConsumers }}</el-col>
                <el-col :span="10">较昨日：{{ info.addConsumers }}</el-col>
              </el-row>
            </el-card>
          </el-card>
        </el-col>
      </el-row>
    </div>
    <div class="dashboard-box">
      <div class="picker">
        <h3>总揽数据</h3>
        <el-date-picker
          v-model="date"
          type="daterange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
        />
      </div>
      <el-row class="w1200" :gutter="20">
        <el-col :span="8">
          <el-card>
            <h5>今日收入（元）</h5>
            <div class="pice">{{ info.totalBenefit }}</div>
            <el-card class="bottom">日环比：- -</el-card>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <h5>今日付费用户</h5>
            <div class="pice">{{ info.totayConsumers }}</div>
            <el-card class="bottom">
              <el-row>
                <el-col :span="14">昨日付费用户：{{ info.preConsumers }}</el-col>
                <el-col :span="10">较昨日：{{ info.addConsumers }}</el-col>
              </el-row>
            </el-card>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card>
            <h5>总付费用户</h5>
            <div class="pice">{{ info.totalConsumers }}</div>
          </el-card>
        </el-col>
      </el-row>
    </div>
    <div class="dashboard-box">
      <h3>用户订单信息</h3>
      <el-row class="w1200" :gutter="20">
        <el-col :span="6">
          <el-card>
            <h5>待确认</h5>
            <div class="pice">{{ info.waitConfirmOrderList.length }}</div>
            <i class="el-icon-document-add icon" />
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <h5>待发货</h5>
            <div class="pice">{{ info.waitSendOrderList.length }}</div>
            <i class="el-icon-shopping-cart-full icon" />
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <h5>已兑换</h5>
            <div class="pice">{{ info.exchangeOrderList.length }}</div>
            <i class="el-icon-shopping-cart-1 icon" />
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <h5>已发货</h5>
            <div class="pice">{{ info.receivedOrderList.length }}</div>
            <i class="el-icon-shopping-cart-2 icon" />
          </el-card>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'Dashboard',
  data() {
    return {
      date: '',
      info: {
        accountBalance: '88',
        addConsumers: '88',
        countBenefit: '88',
        countConsumers: '88',
        exchangeOrderList: [
          { id: 9 },
          { id: 9 },
          { id: 9 },
          { id: 9 },
          { id: 9 },
          { id: 9 },
          { id: 9 }
        ],
        receivedOrderList: [
          { id: 19 },
          { id: 19 },
          { id: 19 },
          { id: 19 },
          { id: 19 },
          { id: 19 },
          { id: 19 }
        ],
        waitConfirmOrderList: [
          { id: 29 },
          { id: 29 },
          { id: 29 },
          { id: 29 },
          { id: 29 },
          { id: 29 },
          { id: 29 }
        ],
        waitSendOrderList: [
          { id: 39 },
          { id: 39 },
          { id: 39 },
          { id: 39 },
          { id: 39 },
          { id: 39 },
          { id: 39 }
        ],
        preConsumers: '88',
        totalBenefit: '88',
        totalConsumers: '88',
        totayConsumers: '88'
      }
    }
  },
  computed: {
    ...mapGetters([
      'name'
    ])
  }
}
</script>

<style lang="scss" scoped>
.dashboard {
  &-container{
    padding: 20px;
    .title{
      font-size: 20px;
      line-height: 30px;
      border-bottom: 3px solid #999;
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
  }
  &-box{
    margin-bottom: 30px;
    .picker{
      h3{
        display: inline-block;
        margin-right: 30px;
      }
    }
    h3{
      font-size: 18px;
      font-weight: normal;
      margin-bottom: 30px;
    }
    h5{
      font-weight: normal;
      font-size: 14px;
    }
    .pice{
      margin: 40px 0 30px;
      font-size: 30px;
    }
    .icon{
      font-size: 50px;
      float: right;
      margin-top: -70px;
    }
    .bottom{
      line-height: 20px;
      background-color: #f0f3f7;
    }
  }
}
</style>
