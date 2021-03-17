// 资金管理
<template>
  <div class="fund">
    <el-row :gutter="24" class="tips">
      <div>资金管理</div>
      <div v-show="!info_eyes" class="sty-eye">
        <img src="@/assets/open-eye.png" @click="bindEye" />
      </div>
      <div v-show="info_eyes" class="sty-eye">
        <img src="@/assets/close-eye.png" @click="bindEye" />
      </div>
    </el-row>
    <el-row :gutter="24">
      <el-col :span="col1">
        <div class="balance">
          <p class="title">轮单余额</p>
          <p class="price">
            {{info_eyes ? '***' : data.account_amount}}
            <span class="unit">元</span>
          </p>
          <!-- <el-button type="text" @click="gotoRecharge" class="btn">充值</el-button> -->
          <el-button type="text" @click="gotoFinance" class="btn">交易记录</el-button>
        </div>
      </el-col>
      <el-col :span="col2">
        <div class="deposit balance no-line">
          <p class="title">保证金</p>
          <p class="price">
            {{info_eyes ? '***' : data.deposit_money}}
            <span class="unit">元</span>
          </p>
          <el-button type="text" @click="gotoRound" class="btn">交易记录</el-button>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import ApiHome from '@/api/home';

export default {
  async created() {
    const res = await ApiHome.home();
    if (res.data.data.capital) {
      this.data.account_amount = res.data.data.capital.account_amount;
      this.data.deposit_money = res.data.data.capital.deposit_money;
      this.data.gift_amount = res.data.data.capital.gift_amount;
    }
  },
  props:['col1','col2'],
  data() {
    return {
      data: {
        account_amount: 0, // 派单余额
        deposit_money: 0, // 保证金
        gift_amount: 0, // 赠送金
      },
      info_eyes: true,
    };
  },
  methods: {
    gotoRecharge() {
      this.$router.push('recharge');
    },
    gotoFinance() {
      this.$router.push('finance');
    },
    gotoRound() {
      this.$router.push({ name: 'Finance', params: { id: 1 } });
    },
    bindEye() {
      this.info_eyes = !this.info_eyes;
    },
  },
};
</script>

<style lang="less" scoped>
.fund {
  padding: 30px;
  overflow: hidden;
  .balance {
    border-right: 1px solid #e3e3e3;
    .title {
      margin-bottom: 4px;
      color: #666;
      font-size: 13px;
    }
    .price {
      margin-bottom: 0;
      font-weight: bold;
      font-size: 24px;
      .unit {
        font-size: 14px;
      }
    }
    .btn {
      margin-right: 30px;
      padding: 4px 0;
      color: #ff5353;
    }
  }
  .no-line {
    border-right: none;
  }
  .deposit {
    .title {
      margin-bottom: 4px;
      color: #666;
      font-size: 13px;
    }
    .price {
      margin-bottom: 0;
      font-weight: bold;
      font-size: 24px;
      .unit {
        font-size: 14px;
      }
    }
  }
  .tips {
    display: flex;
    align-items: center;
    margin: 0 0 26px !important;
    color: rgba(0, 0, 0, 0.85);
    font-size: 18px;
    line-height: 1.5715;
    .sty-eye > img {
      margin-left: 20px;
      vertical-align: middle;
      cursor: pointer;
    }
  }
}
.qz-card .content {
  overflow: auto;
}
</style>
