<template>
  <div>
    <el-card v-if="!isShowOrderExpired">
      <div class="font18 col333 mt20">填写并核对订单信息</div>
      <div class="font13 col333 mt20">购买方式</div>
      <el-card class="mt20">
        <div class="flex">
          <div v-for="(item,index) of shopmodelist" :key="index" :class="['flex','mr20','lex-start','ali-ite','sel-box','point',{'on':index == modeindex}]" @click="changemode(index)">
            <i class="font50 col878E93 ml20 mr10" :class="[item.icon,{'on':index == modeindex}]" />
            <div class="ml15">
              <div :class="['font16','col333',{'on':index == modeindex}]">{{ item.name }}</div>
              <div :class="['font13','mt10','col878E93',{'on':index == modeindex}]">{{ item.fun }}</div>
            </div>
          </div>
        </div>
      </el-card>
      <el-card v-show="modeindex==1" class="mt10">
        <div class="font14 flex spa-bet mb20">
          <div class="col333">确认收货地址</div>
          <div class="col3B7CFF point" @click="addaddress">新增收货地址>></div>
        </div>
        <div v-for="(item,index) of addresslist" :key="index" class="font12 flex spa-bet address">
          <div class="colB5BBBF">
            <el-radio v-model="radio" :label="item.id" @change="radioChange(item)">{{ item.receiverName }} {{ item.receivePrince }} {{ item.receiveCity }} {{ item.receiveProper }} {{ item.receiveAreaDetails }} {{ item.receiverPhone.replace(item.receiverPhone.substring(3,7), "****") }}</el-radio>
          </div>
          <el-button v-if="item.ifDefult === '1'" type="text" disabled class="mr20">默认地址</el-button>
          <el-button v-else type="text" class="mr20" @click="updateStatus(item.id)">设为默认地址</el-button>
        </div>
      </el-card>
      <el-card class="mt10">
        <el-table :data="tableData" style="width: 100%">
          <el-table-column
            label="商品"
            show-overflow-tooltip
          >
            <template slot-scope="scope">
              <div class="flex">
                <img class="u1243_img" :src="scope.row.goodsCover" alt="">
                <div>
                  <div class="goods-title col333 font16">{{ scope.row.goodsSkuName }}</div>
                  <div class="col878E93 font14">{{ scope.row.goodsSkuDesc }}</div>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="supplyPrice" label="单价" width="200" align="center" />
          <el-table-column label="数量" width="300" align="center">
            <template slot-scope="scope">
              <el-input-number v-model="scope.row.num" :min="1" label="描述文字" @change="handleChange" />
            </template>
          </el-table-column>
          <el-table-column label="价格" width="300" align="center">
            <template slot-scope="scope">
              <div class="colF29126 font16">{{ scope.row.num * scope.row.supplyPrice }}</div>
            </template>
          </el-table-column>
        </el-table>
        <div class="font14 flex spa-bet mt20 col878E93">
          <div>运费</div>
          <div>免运费</div>
        </div>
        <div class="font14 mt20 invoicemsg">
          <div class="col333">发票信息</div>
          <div class="col878E93 mt20">暂不支持电子发票，如需开票请联系城市经理</div>
        </div>
      </el-card>
      <el-card class="mt10">
        <div class="flex spa-bet font14">
          <div />
          <div class="col878E93">
            <div class="flex ali-ite">
              <div>{{ totalCount }}件商品，总商品金额：</div>
              <div class="font16 colF28127">¥{{ totalPrice }}</div>
            </div>
            <div class="flex ali-ite mt10 floatr">运费：¥0.00</div>
          </div>
        </div>
      </el-card>
      <el-card class="mt10">
        <div class="flex spa-bet">
          <div />
          <div class="textright">
            <div class="flex ali-ite">
              <div class="font13 col878E93">应付金额：</div>
              <div class="font18 colF28127">¥{{ totalPrice }}</div>
            </div>
            <el-button type="warning" class="mt20" @click="sumbit">提交并结算</el-button>
          </div>
        </div>
      </el-card>
    </el-card>
    <addressmsg :addaddressbul="addaddressbul" @getgoodsmsg="getAddressList" />
    <div v-if="isShowOrderExpired" class="flex cancelpage jus-con ali-ite">
      <i class="el-icon-warning colF29126 font50" />
      <div class="ml10">
        <div class="bold font24">订单已经超时被取消,请重新下单进行支付</div>
        <div class="col3B7CFF font18 mt10 point" @click="gogoodslist">返回</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SubmitOrder',
  components: {
    addressmsg: () => import('@/components/addressmsg')
  },
  data() {
    return {
      ids: [],
      addaddressbul: false,
      modeindex: 0,
      radio: '',
      supplyPrice: '',
      receiver: {},
      addresslist: [],
      shopmodelist: [
        {
          name: '购买并生成兑换券',
          fun: '购买用于生成兑换券',
          icon: 'el-icon-goods'
        },
        {
          name: '购买并发货',
          fun: '购买自用材料需要进行发货处理',
          icon: 'el-icon-box'
        }
      ],
      tableData: [],
      isShowOrderExpired: false,
      timer: ''
    }
  },
  computed: {
    totalCount() {
      let num = 0
      this.tableData.forEach(item => {
        num += item.num
      })
      return num
    },
    totalPrice() {
      let num = 0
      this.tableData.forEach(item => {
        num += item.supplyPrice * item.num
      })
      return num
    }
  },
  created() {
    const cartList = JSON.parse(localStorage.getItem('cartList'))
    if (cartList) {
      const ids = []
      cartList.forEach(item => {
        const obj = {
          goodsId: item.goodsId,
          id: item.goodsSkuId
        }
        ids.push(obj)
      })
      this.ids = ids
    }
    if (this.$route.query.id) {
      this.ids = [{
        id: this.$route.query.id,
        goodsId: this.$route.query.goodsId
      }]
    }
    this.receiveAddressList(1)
    this.getDetailBySkuList()
  },
  mounted() {
    this.timer = setTimeout(this.orderExpired, 30 * 60 * 1000)
  },
  beforeDestroy() {
    clearTimeout(this.timer)
  },
  methods: {
    orderExpired() {
      this.isShowOrderExpired = true
    },
    radioChange(value) {
      this.receiver = value
    },
    sumbit() {
      const item = this.receiver
      let obj = {}
      if (this.modeindex && !item.receiverName) {
        this.$message.warning('请先选择地址')
        return
      }
      const goodsDetailDTOList = this.tableData.map(item => {
        const obj = {
          goodsDetailId: item.id,
          goodsId: item.goodsId,
          goodsNum: item.num,
          salePrice: item.supplyPrice
        }
        return obj
      })
      if (this.modeindex) {
        obj = {
          orderFrom: 1, // 订单来源
          orderType: 1, // 订单类型
          orderPrice: this.totalPrice, // 订单总金额
          goodsDetailDTOList,
          payWay: 3, // 支付方式
          receiverArea: `${item.receivePrince + item.receiveCity + item.receiveProper + item.receiveAreaDetails}`, // 地址
          receiverName: item.receiverName, // 姓名
          receiverPhone: item.receiverPhone// 电话
        }
      } else {
        obj = {
          orderFrom: 1, // 订单来源
          orderType: 1, // 订单类型
          orderPrice: this.totalPrice, // 订单总金额
          goodsDetailDTOList,
          payWay: 2 // 支付方式
        }
      }
      console.log(obj)
      this.$apis.ORDER.orderCreateV2(obj).then(res => {
        if (res.code === 0) {
          // this.$message.success(res.message)
          this.$router.push(`/distribution-mall/cash-register?id=${res.data.id}&orderNo=${res.data.orderNo}`)
        } else if (res.code === -1) {
          this.$confirm(res.message, '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.$router.push(`/distribution-mall/cash-register`)
          }).catch(() => {})
        } else {
          this.$message.error(res.message)
        }
      }).catch(res => {
        console.log(res)
        this.$router.push(`/distribution-mall/cash-register?id=${res.data.id}&orderNo=${res.data.orderNo}`)
      })
    },
    getDetailBySkuList() {
      const obj = this.ids
      this.$apis.GOODS.getDetailBySkuList(obj).then(res => {
        if (res.code === 0) {
          const data = res.data
          data.forEach(item => {
            item.num = 1
          })
          this.tableData = data
        } else {
          this.$message.error(res.message)
        }
      })
    },
    gogoodslist() {
      this.$router.push({
        path: '/distribution-mall/selection-mall'
      })
    },
    addaddress() {
      this.addaddressbul = true
    },
    getAddressList(data) {
      this.$apis.RETAIL.addAddress(data).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.receiveAddressList()
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
      this.addaddressbul = true
    },
    changemode(index) {
      this.modeindex = index
    },
    handleChange() {
      /* this.totalCount = value
      this.totalPrice = value * this.supplyPrice */
    },
    //  地址管理-设置默认
    updateStatus(id) {
      this.$apis.RETAIL.updateStatus({ id }).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.receiveAddressList()
        } else {
          this.$message.error(res.message)
        }
      })
    },
    receiveAddressList(num) {
      this.$apis.RETAIL.receiveAddressList().then(res => {
        if (res.code === 0) {
          this.addresslist = res.data
          if (num === 1 && res.data) {
            this.radio = this.addresslist[0].id
            this.receiver = this.addresslist[0]
          }
        } else {
          this.$message.error(res.message)
        }
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.sel-box{
    border-width: 0px;
    width: 357px;
    height: 92px;
    background: inherit;
    background-color: rgba(255, 255, 255, 1);
    box-sizing: border-box;
    border-width: 1px;
    border-style: solid;
    border-color: rgba(240, 243, 247, 1);
    border-radius: 9px;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
}
.on{
    background-color: rgba(238, 244, 255, 1);
    border-color: rgba(59, 124, 255, 1);
    color: rgba(59, 124, 255, 1);
}
.address{
  height: 40px;
  line-height: 40px;
}
.address:hover{
  background-color: #EFF2F7;
}
.labsty{
  width: 83px;
  height: 30px;
  line-height: 30px;
  text-align: center;
  background-color: #797979;
  margin: auto 80px auto 0;
}
.invoicemsg{
  border-top: 1px solid #E8E8E8;
  padding: 20px 0 0 0;
}
.textright{
  text-align: right;
}
.floatr{
  float: right;
}
.cancelpage{
  margin: 300px 0 0 0;
}
</style>
