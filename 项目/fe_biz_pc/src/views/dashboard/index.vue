<template>
  <div class="dashboard-container">
    <!-- <div class="dashboard-text">概览</div> -->
    <el-row :gutter="50">
      <el-col :span="18">
        <el-card shadow="never" class="mb20">
          <el-row :gutter="20">
            <el-col :span="5">
              <h4 class="top-left-title">四部实现商品转化</h4>
              <div class="top-left-item" :class="{active: showItem === 1}" @click="topItemSelsct(1)">1.如何发布商品？</div>
              <div class="top-left-item" :class="{active: showItem === 2}" @click="topItemSelsct(2)">2.如何兑换商品？</div>
              <div class="top-left-item" :class="{active: showItem === 3}" @click="topItemSelsct(3)">3.完成首单交易</div>
              <div class="top-left-item" :class="{active: showItem === 4}" @click="topItemSelsct(4)">4.收入一键提现</div>
            </el-col>
            <el-col :span="19">
              <el-card>
                <div v-if="showItem === 1" class="top-box-item">
                  <h4 class="title">获取收益从发布一个商品开始</h4>
                  <div class="step">
                    <div class="step-item">
                      <div class="step-item-top">进入</div>
                      <div class="step-item-bottom">选品商城</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">选择</div>
                      <div class="step-item-bottom">家居商品</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">加入</div>
                      <div class="step-item-bottom">选品库</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">设置</div>
                      <div class="step-item-bottom">销售价</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">完成</div>
                      <div class="step-item-bottom">发布商品</div>
                    </div>
                  </div>
                  <div class="btn">
                    <el-button type="primary">
                      <router-link to="/distribution-mall/selection-mall">立即选品发布</router-link>
                    </el-button>
                  </div>
                </div>
                <div v-if="showItem === 2" class="top-box-item">
                  <h4>获取收益从发布一个商品开始</h4>
                  <div class="step">
                    <div class="step-item">
                      <div class="step-item-top">购买</div>
                      <div class="step-item-bottom">家居商品</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">生成</div>
                      <div class="step-item-bottom">兑换码</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">提供</div>
                      <div class="step-item-bottom">兑换码</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">兑换</div>
                      <div class="step-item-bottom">家居商品</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">确认</div>
                      <div class="step-item-bottom">兑换商品</div>
                    </div>
                  </div>
                  <div class="btn">
                    <el-button type="primary">
                      <router-link to="/distribution-mall/selection-mall">前往选品商城</router-link>
                    </el-button>
                  </div>
                </div>
                <div v-if="showItem === 3" class="top-box-item">
                  <h4>获取收益从发布一个商品开始</h4>
                  <div class="step">
                    <div class="step-item">
                      <div class="step-item-top">扫码</div>
                      <div class="step-item-bottom">访问店铺</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">选择</div>
                      <div class="step-item-bottom">家居商品</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">购买</div>
                      <div class="step-item-bottom">完成支付</div>
                    </div>
                    <div class="step-item">
                      <div class="step-item-top">完成</div>
                      <div class="step-item-bottom">首单交易</div>
                    </div>
                  </div>
                  <div class="btn">
                    <el-button type="primary">
                      <router-link to="/distribution-mall/selection-mall">访问店铺</router-link>
                    </el-button>
                  </div>
                </div>
                <div v-if="showItem === 4" class="top-box-item">
                  <h4>获取收益从发布一个商品开始</h4>
                  <p>账户余额大于1元即可申请提现</p>
                  <div class="btn">
                    <el-button type="primary">
                      <router-link to="/transaction-management/withdrawal-management">立即提现</router-link>
                    </el-button>
                  </div>
                </div>
              </el-card>
            </el-col>
          </el-row>
        </el-card>
        <el-card shadow="never" class="dashboard-assets">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-card>
                <div class="center-title center-title-mb">今日收入（元）</div>
                <div class="center-num">{{ info.totalBenefit }}</div>
                <div class="center-bottom">日环比</div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card>
                <div class="center-title center-title-mb">今日付费用户</div>
                <div class="center-num">{{ info.totayConsumers }}</div>
                <div class="center-bottom clearfix">
                  <div class="fl">昨日付费用户：{{ info.preConsumers }}</div>
                  <div class="fr mr5">较昨日：{{ info.addConsumers }}</div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card class="mb10">
                <div class="center-title">总付费用户</div>
                <div class="center-price">{{ info.totalConsumers }}</div>
              </el-card>
              <el-card>
                <div class="center-title">
                  <span>可提现金额（元）</span>
                  <el-button type="text" class="fr">提现>></el-button>
                </div>
                <div class="center-price">{{ info.totalBenefit }}</div>
              </el-card>
            </el-col>
          </el-row>
        </el-card>
        <h3 class="dashboard-h3">用户订单信息</h3>
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card>
              <div class="dashboard-bottom-item">
                <h5>待确认</h5>
                <div class="bottom">
                  <span class="num">{{ info.waitConfirmOrderList.length }}</span>
                  <i class="el-icon-shopping-bag-1 fr" />
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card>
              <div class="dashboard-bottom-item">
                <h5>待发货</h5>
                <div class="bottom">
                  <span class="num">{{ info.waitSendOrderList.length }}</span>
                  <i class="el-icon-shopping-cart-1 fr" />
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card>
              <div class="dashboard-bottom-item">
                <h5>已兑换</h5>
                <div class="bottom">
                  <span class="num">{{ info.exchangeOrderList.length }}</span>
                  <i class="el-icon-shopping-cart-2 fr" />
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card>
              <div class="dashboard-bottom-item">
                <h5>已发货</h5>
                <div class="bottom">
                  <span class="num">{{ info.receivedOrderList.length }}</span>
                  <i class="el-icon-shopping-cart-full fr" />
                </div>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </el-col>
      <el-col :span="6" class="top-right">
        <el-card shadow="never">
          <h5 class="top-right-title">小程序二维码</h5>
          <div class="qr-code clearfix">
            <div class="pic fl">
              <img src="@/assets/images/u311.png" alt="" width="100%" height="100%">
            </div>
            <div class="fl">
              <h6>商家小程序</h6>
              <p>扫码可实时查看订</p>
            </div>
          </div>
          <div class="qr-code clearfix code-line">
            <div class="pic fl">
              <img src="@/assets/images/u311.png" alt="" width="100%" height="100%">
            </div>
            <div class="fl">
              <h6>店铺小程序</h6>
              <p>扫码可查看店铺</p>
            </div>
          </div>
          <div class="bottom">
            <h4>客服中心</h4>
            <div class="tel">
              <i class="el-icon-headset" />
              <span>400-6969-336</span>
            </div>
            <p class="tips">投诉或其他问题可拨号联系客服中心</p>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { getToken } from '@/utils/auth'

export default {
  name: 'Dashboard',
  data() {
    return {
      info: {},
      showItem: 1
    }
  },
  computed: {
    ...mapGetters([
      'name'
    ])
  },
  created() {
    // this.initWebSocket()
    this.getInfo()
  },
  destroyed() {
    this.websock.close() // 离开路由之后断开websocket连接
  },
  methods: {
    topItemSelsct(id) {
      this.showItem = id
    },
    getInfo() {
      this.$apis.COMMON.indexWithSupply().then(res => {
        this.info =
          {
            'accountBalance': 0,
            'addConsumers': 0,
            'countBenefit': 0,
            'countConsumers': 0,
            'exchangeOrderList': [
              {
                'id': 0
              }
            ],
            'preConsumers': 0,
            'receivedOrderList': [
              {
                'id': 0
              }
            ],
            'totalBenefit': 0,
            'totalConsumers': 0,
            'totayConsumers': 0,
            'waitConfirmOrderList': [
              {
                'id': 0
              }
            ],
            'waitSendOrderList': [
              {
                'id': 0
              }
            ]
          }
        if (res.code === 0) {
          this.info = res
        } else {
          this.$message.error(res.message)
        }
      })
    },
    initWebSocket() { // 初始化weosocket
      if (typeof (WebSocket) === 'undefined') {
        alert('您的浏览器不支持socket')
      } else {
        const token = getToken()
        const msgType = 2
        const requestId = '9818212345123451'
        const wsUrl = `ws://192.168.8.142:8400/webSocket/${token}/${msgType}/${requestId}`
        console.log(wsUrl)
        this.websock = new WebSocket(wsUrl)
        this.websock.onmessage = this.websocketonmessage
        this.websock.onopen = this.websocketonopen
        this.websock.onerror = this.websocketonerror
        this.websock.onclose = this.websocketclose
      }
    },
    websocketonopen() { // 连接建立之后执行send方法发送数据
      const actions = { 'test': '12345' }
      this.websocketsend(JSON.stringify(actions))
    },
    websocketonerror() { // 连接建立失败重连
      this.initWebSocket()
    },
    websocketonmessage(e) { // 数据接收
      const redata = JSON.parse(e.data)
      console.log('数据接收', redata)
    },
    websocketsend(Data) { // 数据发送
      this.websock.send(Data)
    },
    websocketclose(e) { // 关闭
      console.log('断开连接', e)
    }
  }
}
</script>

<style lang="scss" scoped>
.dashboard {
  &-text {
    font-size: 30px;
    line-height: 46px;
    margin-bottom: 20px;
  }
  &-container {
    font-size: 16px;
    .top-left-title{
      font-weight: normal;
      color: #000;
      line-height: 40px;
      font-size: 18px;
    }
    .top-left-item{
      line-height: 36px;
      margin: 10px 0;
      padding-left: 10px;
      cursor: pointer;
    }
    .active{
      background: #f0f3f7;
      border-radius: 0 18px 18px 0 ;
    }
    .top-box-item{
      .tetle{
        line-height: 40px;
      }
      .step{
        margin: 33px 0;
        display: flex;
        justify-content: flex-start;
        .step-item{
          margin-right: 30px;
          padding-right: 50px;
          line-height: 26px;
          font-size: 14px;
          background: url('~@/assets/images/u230.png') no-repeat right center;
          background-size: 36px 36px;
          .step-item-top{
            color: #3B7CFF;
          }
          &:last-child{
            background: none;
          }
        }
      }
      .btn{
        margin-bottom: 20px;
      }
      p{
        margin: 40px 0 60px;
      }
    }
    .top-right{
      &-title{
        font-size: 16px;
        line-height: 32px;
        margin-top: 10px;
        font-weight: normal;
      }
      .qr-code{
        .pic{
          width: 100px;
          height: 100px;
          margin: 10px 20px 20px 0;
        }
        h6{
          margin-top: 20px;
          font-size: 14px;
          line-height: 40px;
        }
        p{
          font-size: 14px;
          line-height: 26px;
          color: #666;
        }
      }
      .code-line{
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
      }
      .bottom{
        h4{
          font-size: 16px;
          font-weight: normal;
          line-height: 40px;
        }
        .tel{
          line-height: 40px;
          i{
            font-weight: bold;
            color: #3B7CFF;
          }
          span{
            color: #333;
            margin-left: 10px;
          }
        }
        .tips{
          font-size: 12px;
        }
      }
    }
    .dashboard-h3{
      font-size: 24px;
      font-weight: normal;
      line-height: 48px;
      margin: 10px 0;
    }
    .dashboard-assets{
      .center-title{
        font-size: 14px;
        line-height: 20px;
        margin-bottom: 10px;
      }
      .center-title-mb{
        margin-bottom: 38px;
      }
      .center-num{
        font-size: 36px;
        font-weight: bold;
        line-height: 36px;
      }
      .center-price{
        font-size: 28px;
        font-weight: bold;
        line-height: 36px;
      }
      .center-bottom{
        margin-top: 40px;
        background: #f0f3f7;
        border-radius: 5px;
        padding: 10px 10px;
        line-height: 30px;
        font-size: 14px;
      }
    }
    .dashboard-bottom-item{
      h5{
        font-size: 14px;
        line-height: 36px;
        font-weight: normal;
        padding-bottom: 20px;
        color: #666;
      }
      .num{
        font-size: 28px;
      }
      i{
        font-size: 40px;
      }
    }
  }

}
</style>
