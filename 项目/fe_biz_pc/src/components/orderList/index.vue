<template>
  <div class="com-order-list">
    <div class="filterResult">
      <table v-loading="tableLoading">
        <thead class="orderHead">
          <tr>
            <th v-if="type !== 2" width="150">{{ type === 1? '购买用户' : '兑换人' }}</th>
            <th>订单详情</th>
            <!-- <th width="520">订单详情</th> -->
            <th width="220">收货人</th>
            <th v-if="type !== 3" width="100">金额</th>
            <th v-else width="100">兑换码</th>
            <th width="150">状态</th>
            <th width="130">操作</th>
          </tr>
        </thead>
        <tbody v-for="(order,orderIndex) in tableData" :key="orderIndex" class="orderBody">
          <tr class="separate" />
          <tr class="tr_th">
            <td colspan="6">
              <span class="dealTime" :title="order.createTime">下单时间：{{ order.payTime }}</span>
              <span>订单号：{{ order.orderNo }}</span>
            </td>
          </tr>
          <tr class="order">
            <td class="orderMessage" :colspan="type !== 2 ? '2':''">
              <div v-for="(item,itemIndex) in order.goodsDetailVOList" :key="itemIndex" class="orderMessage-item">
                <div v-if="itemIndex === 0 && type !== 2" class="goodItem">
                  <div class="orderImg">
                    <el-avatar size="large" :src="order.avatarUrl" />
                  </div>
                  <div class="orderName">{{ order.custName }}</div>
                </div>
                <div class="goods-info" :class="{ml150: type !== 2 }">
                  <div class="goods-pic clearfix">
                    <div class="pic">
                      <img class="imgSty" :src="item.goodsCover">
                    </div>
                    <div class="text">
                      <span class="name">{{ item.goodsName }}</span>
                      <span>{{ item.goodsSkuName }}</span>
                      <span>{{ item.goodsSkuDesc }}</span>
                      <span class="saleNum">X {{ item.saleNum }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </td>
            <!-- 收货人 -->
            <td class="receiver">
              <p class="name-tel">
                <span class="name">{{ order.receiverName }}</span>
                <span>{{ order.receiverPhone }}</span>
              </p>
              <p>{{ order.receiveAreaDetails }}</p>
            </td>
            <!-- 金额 -->
            <td v-if="type !== 3" class="order-price">
              <p class="price">{{ order.orderPrice }}</p>
              <p class="pay-type">{{ order.payType | payType }}</p>
            </td>
            <!-- 兑换码 -->
            <td v-else class="order-exchange-no">
              <p class="exchange-no">{{ order.exchangeNo }}</p>
            </td>
            <!-- 状态 -->
            <td v-if="type !== 3" class="order-status">
              <p>{{ order.orderStatus | orderStatus }}</p>
              <p v-if="order.deliverNo">运单号<br>{{ order.deliverNo }}</p>
              <p v-if="order.refundReason" class="reason">{{ order.refundReason }}</p>
            </td>
            <!-- 兑换状态 -->
            <td v-else class="order-status">
              <p>{{ order.exchangeStatus | exchangeStatus }}</p>
              <p v-if="order.refundReason" class="reason">{{ order.refundReason }}</p>
            </td>
            <td class="orderOperate">
              <template v-if="type !== 3 && order.orderStatus === '2'">
                <div><el-button type="text" @click="confirm(order.id)">确认</el-button></div>
                <span class="text">24小时自动确认</span>
                <div><el-button type="text" @click="cancel(order.id)">取消</el-button></div>
              </template>

              <div v-if="order.deliverNo"><el-button type="text" @click="copyUrl(order.deliverNo)">复制运单号</el-button></div>
              <div v-if="type !== 1 && order.exchangeStatus === 0 && order.payType === 3"><el-button type="text" @click="getExchangeNo(order.id)">获取兑换码</el-button></div>
              <div v-if="order.orderStatus === '1'"><el-button type="text" @click="goPay">立即支付</el-button></div>
              <div v-if="order.orderStatus === '2' && order.payType === '3'"><el-button type="text" @click="confirmSend(order.id)">直接发货</el-button></div>
              <!-- <div v-if="1===2"><el-button type="text">申请退款</el-button></div> -->
              <div v-if="order.exchangeNo" class="clearfix code-text">
                <span class="">{{ order.exchangeNo }}</span>
                <el-button class="fr" type="text" @click="copyUrl(order.exchangeNo)">复制</el-button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="tableData.length===0" class="noItems">
        <div class="noItems-text">
          暂无数据
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: '',
  filters: {
    payType(value) {
      switch (value) {
        case '1':
          return 'c端微信支付'
        case '2':
          return '商户兑换卷方式'
        case '3':
          return '商户直接购买'
        default:
          return '----'
      }
    },
    orderStatus(val) {
      switch (val) {
        case '1':
          return '待付款'
        case '2':
          return '已付款(待确认)'
        case '3':
          return '待收货'
        case '4':
          return '已收货'
        case '5':
          return '待发货'
        case '6':
          return '退款中'
        case '7':
          return '退款完成'
        case '8':
          return '已取消'
        case '9':
          return '待退货'
        case '10':
          return '已退货'
        case '11':
          return '退款失败'
        default:
          return '----'
      }
    },
    exchangeStatus(val) {
      switch (val) {
        case '0':
          return '未兑换'
        case '1':
          return '兑换中'
        case '2':
          return '已兑换'
        case '3':
          return '已失效'
        default:
          return '----'
      }
    }
  },
  props: {
    type: {
      type: Number,
      default: 1
    },
    tableData: {
      type: Array,
      default: null
    },
    tableLoading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      currentPage: 1,
      size: 20
    }
  },
  watch: {},
  created() {
  },
  mounted() {},
  methods: {
    handleSizeChange(val) {
      this.$emit('changeData', val)
    },
    handleCurrentChange(val) {
      this.$emit('changeData', val)
    },
    goPay() {
      this.$router.push('/distribution-mall/cash-register')
    },
    confirm(id) {
      this.$apis.ORDER.confirmWaitSend({ id }).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.$emit('getList')
        } else {
          this.$message.error(res.message)
        }
      })
    },
    confirmSend(id) {
      this.$apis.ORDER.confirmSend({ id }).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.$emit('getList')
        } else {
          this.$message.error(res.message)
        }
      })
    },
    cancel(id) {
      this.$confirm('确定取消订单?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.ORDER.orderCancel({ id }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.$emit('getList')
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    getExchangeNo(id) {
      this.$prompt('请输入安全手机：189****060的短信验证码', '获取兑换码验证', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputPattern: /^\d{4}|\d{6}$/,
        inputErrorMessage: '验证码格式不正确'
      }).then(({ value }) => {
        this.$apis.ORDER.getExchangeNo({ id }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '取消输入'
        })
      })
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.com-order-list{
  .filterResult{
    width: 100%;
    table{
      width: 100%;
    }
  }
  .noItems{
    min-height: 60px;
    text-align: center;
    width: 100%;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    .noItems-text{
      font-size: 14px;
      line-height: 60px;
      width: 50%;
      color: #909399;
    }
  }
  .orderHead {
    width: 100%;
    text-align: center;
    font-size: 14px;
    color: #909399;
    background: #f5f5f5;
    height: 40px;
    line-height: 40px;
  }

  .orderBody .separate {
    height: 10px;
  }

  .orderBody .tr_th {
    background: #f5f5f5;
    color: #909399;
    height: 36px;
    line-height: 36px;
    font-size: 14px;
    margin-top: 10px;
  }

  /*设置边框*/
  .orderBody td {
    border: 1px solid #e5e5e5;
  }

  .orderBody .tr_th .dealTime {
    padding: 0 30px;
  }

  .order{
    color: #999;
  }
  .orderMessage{
    padding: 14px 0;
    font-size: 1px;
  }
  .orderMessage-item{
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
    padding:0 10px 10px;
    &:last-child{
      border: 0;
      margin-bottom: 0px;
    }
    .goodItem{
      float: left;
      width: 130px;
      padding-left: 20px;
      .orderImg{
        margin-bottom: 10px;
        padding-bottom: 0;
      }
      .orderName{
        color: #999;
        font-size: 12px;
        line-height: 20px;
      }
    }
    .ml150{
      margin-left: 150px;
    }
    .goods-info{
      .goods-pic{
        .pic{
          float: left;
          width: 60px;
          height: 60px;
          img{
            width: 100%;
            height: 100%;
          }
        }
        .text{
          margin-left: 100px;
          color: #333;
          font-size: 14px;
          .name{
            margin-right: 10px;
          }
          .saleNum{
            float: right;
            margin-right: 20px;
            i{
              font-size: 18px;
            }
          }
        }
      }
    }
  }
  .receiver{
    color: #999;
    font-size: 14px;
    vertical-align: top;
    padding: 10px;
    .name-tel{
      line-height: 40px;
      .name{
        margin-right: 10px;
      }
    }
  }
  .order-price{
    vertical-align: top;
    text-align: center;
    padding: 10px;
    font-size: 14px;
    .price{
      line-height: 30px;
      border-bottom: 1px solid #ccc;
    }
    .pay-type{
      margin-top: 10px;
    }
  }
  .order-exchange-no{
    .exchange-no{
      text-align: center;
    }
  }
  .order-status{
    padding: 10px;
    text-align: center;
    font-size: 14px;
    .reason{
      margin: 10px 0;
    }
  }
  .orderOperate{
    padding: 10px;
    .text{
      font-size: 12px;
    }
    .code-text{
      font-size: 14px;
      padding: 12px 0;
      ::v-deep .el-button{
        padding: 0;
      }
    }
  }
}
</style>
