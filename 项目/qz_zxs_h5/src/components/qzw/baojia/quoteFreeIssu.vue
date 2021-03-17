<template>
  <div class="free_issu">
    <div class="issu_box">
      <p class="money">算算我家装修多少钱？</p>
      <p class="timers">仅需8秒，万级大数据精准报价</p>
        <div class="bill_box">
          <div class="city" @click="openPicker">
            <p class="left"><img  src="../../../assets/img/qzw/map.png"/></p>
            <span>{{ addrText }}</span>
          </div>
          <div class="area">
            <input type="number" name="mianji"  v-model="formInfo.inputArray[0].value" @input="handleInput" placeholder="请输入房屋面积" />
            <p>㎡</p>
          </div>
          <div class="tele">
            <input type="tel" name="tel-number" maxlength="11" v-model="formInfo.inputArray[1].value" @input="handleTel"  placeholder="请输入联系方式">
          </div>
          <p class="input-box" id="shenming" style="border:none" >
            <label @click="isDisclaimer">
              <i class="fa fa-check" v-if="disclaimer"></i>
            </label>
            <span class="agree">我已阅读并同意齐装网的</span>
            <span href="javascript:void(0)" @click='toMianze' class="a"><span>《免责声明》</span></span>
          </p>
          <p class="red_btn" @click="submit">免费获取报价明细</p>
        </div>
    </div>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
    <m-tips ref="tips"/>
  </div>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import citySelect from '../../common/citySelect.vue'
import { zxbj } from '@/api/api'
import router from "@/router";
export default {
  name: 'free_issu',
  data () {
    return {
      addrText:'请选择城市',
      disclaimer: true,
      orderId: '',
      formInfo: {
        // source: '19070117',
        source: '',
        inputArray: [
          {
            name: 'mianji',
            placeholder: '请输入房屋面积',
            type: 'number',
            danwei: '㎡',
            value: '',
            tips: [
              '请填写您的房屋面积^_^!',
              '请填写正确的房屋面积^_^!',
              '房屋面积请输入1-10000之间的数字^_^!'
            ]
          },
          {
            name: 'tel',
            placeholder: '请输入联系方式',
            type: 'tel',
            maxlength: 11,
            value: '',
            tips: [
              '请输入11位手机号!',
              '请输入11位手机号!'
            ]
          }
        ],
        isCity: true,
      },
      radomNum: ['1', '0', '6', '8', '2', '1'],
    }
  },
  created () {
    this.formInfo.source = this.$route.query.authcode
  },
  components: {
    citySelect,
    mTips
  },
  methods: {
    openPicker () {
      this.$refs.city.openPicker()
    },
    hidePicker () {
      this.$refs.city.cancel()
    },
    getCityVlaue (city) {
      this.addrText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
      this.cs = city[1].id
      this.qx = city[2].id
    },
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    toMianze () {
      router.push('/disclaimer')
    },
    handleInput(e) {
      this.formInfo.inputArray[0].value = e.target.value.replace(/\D/g, '').replace(/^0{1,}/g, '');
      if(this.formInfo.inputArray[0].value.length>4){
        this.formInfo.inputArray[0].value=this.formInfo.inputArray[0].value.slice(0,4)
      }
    },
    handleTel(e) {
      this.formInfo.inputArray[1].value = e.target.value.replace(/\D/g, '').replace(/^0{1,}/g, '');
    },
    validateForm () {
      let that = this
      let yzData = that.formInfo.inputArray
      if (that.addrText === '请选择您所在的城市') {
        that.$refs.tips.tipsFadeIn({
          text: '请选择您所在的城市'
        })
        return false
      }
      for (let i in yzData) {
        // 验证面积
        if (yzData[i].name === 'mianji') {
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
            })
            return false
          }
          let reg = /^(-?\d+)(\.\d+)?$/
          if (!reg.test(yzData[i].value)) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[1]
            })
            return false
          }
          if (yzData[i].value < 1 || yzData[i].value > 10000) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[2]
            })
            return false
          }
        }
        // 验证号码
        if (yzData[i].name === 'tel') {
          let reg = new RegExp('^(13|14|15|16|17|18|19)[0-9]{9}$')
          if (yzData[i].value === '') {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[0]
            })
            return false
          }
          if (!reg.test(yzData[i].value)) {
            that.$refs.tips.tipsFadeIn({
              text: yzData[i].tips[1]
            })
            return false
          }
        }
      }
      // 验证免责声明
      if (!this.disclaimer) {
        this.$refs.tips.tipsFadeIn({
          text: '请勾选我已阅读并同意齐装网的《免责声明》'
        })
        return false
      }
      return true
    },
    submit () {
      let that = this
      let from = that.$route.query.from
      let parms = {}
      if (that.validateForm()) {
        let yzData = that.formInfo.inputArray
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = that.cs
        parms['qx'] = that.qx
        parms['source'] = that.formInfo.source
        if(from == "app") {
          if(that.formInfo.source == 19070117) {  //首页
            that.$bridge.callNative('Native_bory_point', 'fadan_bj_count', function (res) {
            })
          } else if(that.formInfo.source == 20121633) {  //
            that.$bridge.callNative('Native_bory_point', 'bikeng_detail_sbjxf_submit', function (res) {
            })
          }
        } else {
          // that.formInfo.callBack(parms, that.$parent, 'zxs-h5')
        }
        zxbj(parms).then((res) =>{
          if (res.data.error_code === 0){
            that.orderId = res.data.data
            that.$router.push({
              path:'/qzw/quote-free-result',
              query:{
                orderid: that.orderId
              }
            })
          } else {
            // that.formInfo.callBack(parms, that.$parent, 'zxs-h5')
          }
        })

      }
    },
  }

}
</script>
<style scoped>
.free_issu {
  width: 100%;
  height: 100%;
  background-color: #F2F3F7;
  padding-top: 0.777rem;
  box-sizing: border-box;
}
.issu_box {
  text-align: center;
}
.money {
  color: #323232;
  font-size: 0.235rem;
  font-weight: bold;
}
.timers {
  font-size: 0.119rem;
  color: #A9ADBA;
  font-weight: 400;
}
/* 发单 */
.marauto {
  width: 100%;
  margin-top: -0.393rem;
  z-index: 1;
  position: relative;
}
.bill_box {
  width: 2.944rem;
  margin: 0 auto;
  border-radius: 0.034rem;
  padding: 0.209rem 0.171rem 0.324rem;
  box-sizing: border-box;
}
.bill_box div {
  margin-bottom: 0.128rem;
  width: 2.603rem;
  height: 0.410rem;
  border-radius: 0.026rem;
  background-color: #fff;
  padding-left: 0.085rem;
  padding-right: 0.085rem;
  box-sizing: border-box;
}
.bill_box div input{
  width: 80%;
  background-color: #fff;
}
.mian {
   width: 80%;
}
.city {
  display: flex;
  align-items: center;
}
.area {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.tele {
  display: flex;
  align-items: center;
  }
.left {
  width:0.128rem;
  height: 0.128rem;
}
.left img {
  width: 100%;
  height: 100%;
}
#shenming{
  margin:0.1rem 0;
  text-align: left;
  font-size: 0.1rem;
  height: 20px;
  position: relative;
  overflow: hidden;
}
#shenming label{
  display: inline-block;
  width: 11px;
  height: 11px;
  border: 1px solid #666;
  position: relative;
  top: 1.4px;
}
#shenming label i{
  color: #468dcc;
  position: absolute;
  font-size: 10px;
}
#shenming .a{
  color: #468dcc;
  border-bottom: 1px solid #468dcc;
  display: inline-block;
  margin-left: -2px;
  line-height: 13px;
  height: 14px;
}
.agree {
  font-size: 0.094rem;
  color: #A9ADBA;
}
.red_btn {
  width: 2.603rem;
  height: 0.410rem;
  background-color: #FF5353;
  color: #fff;
  text-align: center;
  line-height: 0.410rem;
  border-radius: 0.026rem;
  font-size: 0.137rem;
  font-weight: 500;
}

</style>
