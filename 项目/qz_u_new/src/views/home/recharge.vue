<template>
  <div class="recharge">
    <qz-card title="资金管理" style="margin-bottom:24px;">
      <el-row :gutter="24">
        <el-col :span="6">
          <div class="balance">
            <p class="title">轮单余额</p>
            <p class="price">
              {{data.account_amount}}
              <span class="unit">元</span>
            </p>
          </div>
        </el-col>
        <el-col :span="16">
          <div class="deposit">
            <p class="title">保证金</p>
            <p class="price">
              {{data.deposit_money}}
              <span class="unit">元</span>
            </p>
          </div>
        </el-col>
      </el-row>
    </qz-card>
    <div>
      <!-- FIXME: 点击按钮切换二维码显示 -->
      <qz-card title="预充值">
        <p>承蒙贵公司对我司的支持，为保障贵公司与我司业务往来时的资金安全，请贵公司将所有款项汇至我司以下指定账号，</p>
        <p style="color: #ffa81e;">支付完成后及时将打款凭证提交给城市运营经理，提交后2个工作日内即可到贵公司轮单费余额。</p>
        <div class="pay">
          <div :class="{'item': true,'current': menu == 1}" @click="change(1)">
            <img src="@/assets/weixin.png" alt />
            <span>微信支付</span>
          </div>
          <div :class="{'item': true,'current': menu == 2}" @click="change(2)">
            <img src="@/assets/zhifubao.png" alt />
            <span>支付宝支付</span>
          </div>
          <div :class="{'item': true,'current': menu == 3}" @click="change(3)">
            <img src="@/assets/huabei.png" alt />
            <span>花呗</span>
          </div>
        </div>
        <div>
          <img v-show="menu == 1" src="@/assets/qrcode/u278.png" alt="微信" />
          <img v-show="menu == 2" src="@/assets/qrcode/u285.png" alt="支付宝" />
          <img v-show="menu == 3" src="@/assets/qrcode/u281.png" alt="花呗" />
          <div>
            <p>温馨提示：</p>
            <p>为了您的资金安全，请勿将款项汇款至非我司指定账户中。</p>
            <p>转账时请备注您公司名：建议格式：城市+公司简称，如：苏州云网通装饰。</p>
            <p>因支付宝、微信内部风险管控，部分客户转账过程中会出现安全风险提示，请不必担心。</p>
          </div>
        </div>
      </qz-card>
    </div>
  </div>
</template>

<script>
import ApiHome from '@/api/home';
import QzCard from '@/components/card.vue';

export default {
  async created() {
    const res = await ApiHome.home();
    if (res.data.data.capital) {
      this.data.account_amount = res.data.data.capital.account_amount;
      this.data.deposit_money = res.data.data.capital.deposit_money;
    }
  },
  data() {
    return {
      data: {
        account_amount: 0, // 派单余额
        deposit_money: 0, // 保证金
      },
      menu: '1',
    };
  },
  components: {
    QzCard,
  },
  methods: {
    change(index) {
      this.menu = index;
    },
  },
};
</script>

<style lang="less" scoped>
.recharge {
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
  .pay {
    width: 100%;
    margin-bottom: 20px;
    overflow: hidden;
    .item {
      display: flex;
      align-items: center;
      justify-content: center;
      float: left;
      width: 120px;
      height: 40px;
      margin-right: 20px;
      background: rgba(242, 242, 242, 1);
      border-radius: 2px;
      cursor: pointer;
      img {
        display: block;
        width: 18px;
        height: 18px;
        margin-right: 10px;
      }
    }
    .current {
      background: rgba(255, 255, 255, 1);
      border: 1px solid rgba(255, 83, 83, 1);
    }
  }
}
</style>
