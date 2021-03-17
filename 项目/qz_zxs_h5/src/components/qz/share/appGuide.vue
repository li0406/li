<template>
  <section>
    <div class="jisuanqi-fandan">
      <!-- from表单填写 -->
      <div class="jisuanqi-form">
        <div class="eight_money">8秒计算装修需要多少钱</div>
        <div class="money-img">
          <div class="quotate" style="float: left;">装修报价</div>
          <div style="float: right;">
            <!-- <div class="num num-gray"></div> -->
            <!-- <div class="num num-gray"></div> -->
            <template v-for="(item, index) in radomNum">
              <template v-if="item==='0'&&index===0">
                <div class="num num-gray" :key="index+item"></div>
              </template>
              <template v-else>
                <div :class="'num num-'+item" :key="index+item"></div>
              </template>
            </template>
            <span> 元</span>
          </div>
        </div>
        <ul class="m-bj-edit">
          <li class="input-box jsq-city" v-if="formInfo.isCity">
            <div class="address-box" @click="openPicker">
              <img src="../../../assets/img/qzw/map.png" class="map">
              {{addrText}}
            </div>
          </li>
          <li class="area">
            <input class="m-row-int1 m-bj-edit-list jsq-mianji" v-model="formInfo.inputArray[0].value" @input="handleInput" name="mianji" placeholder="请输入房屋面积">
            <p class="guide_p">㎡</p>
          </li>
          <li class="input-box">
            <input class="m-row-int1 m-bj-edit-list jsq-tele" v-model="formInfo.inputArray[1].value" @input="handleTel" type="tel" maxlength="11" name="tel-number" placeholder="请输入联系方式">
          </li>
        </ul>
        <div class="input-box" id="shenming" style="border:none" @click="hidePicker">
          <label @click="isDisclaimer">
            <i class="fa fa-check" v-if="disclaimer"></i>
          </label>
          <span>我已阅读并同意齐装网的</span>
          <span href="javascript:void(0)" @click='toMianze' class="a"><span>《免责声明》</span></span>
        </div>
        <!-- 立即计算报价按钮 -->
        <p class="save-submit" @click="submit">免费获取报价明细</p>
      </div>
    </div>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
    <m-tips ref="tips"/>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import citySelect from '../../common/citySelect.vue'
import {zxbj} from '@/api/api'
import router from "@/router";
export default {
  name: 'zxbj',
  components: {
    citySelect,
    mTips
  },
  data () {
    return {
      addrText: '请选择城市',
      disclaimer: true,
      orderId: '',
      formInfo: {
        source: '20121633',
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
        isCity: true
      },
      radomNum: ['1', '0', '6', '8', '2', '1'],
    }
  },
  created () {
    let that = this
    setInterval(function () {
      let num = that.getRadomNumber(30000, 120000) + ''
      num = num.length < 6 ? '0' + num : num
      that.radomNum = num.split('')
    }, 300)
  },
  methods: {
    getRadomNumber (Min, Max) {
      let Range = Max - Min
      let Rand = Math.random()
      return (Min + Math.round(Rand * Range))
    },
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    toMianze () {
      router.push('/disclaimer')
    },
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
        zxbj(parms).then((res) => {
          if (res.data.error_code === 0) {
            that.orderId = res.data.data
            that.$bridge.callNative('get_toogle','/qzw/quote-free-result?orderid=' + that.orderId, function (src) {
            })
          } else {
            parent.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
          }
        })
        that.$bridge.callNative('Native_bory_point', 'bikeng_detail_sbj_submit', function (res) {
        })
      }
    }
  }
}
</script>
<style scoped>
  img{
    width:100%;
    vertical-align: middle;
  }
  .jisuanqi-fandan{
    background:#fff;
    padding:0 0.12rem;
  }
  .jisuanqi-form{
    padding: 0.171rem 0 0.1rem;
    position: relative;
    top:-0.2rem;
  }
  .jisuanqi-form .form-tit{
    font-weight: bold;
    text-align:center;
    font-size: 0.2rem;
    margin-bottom:0.15rem;
  }
  .red{
    color:red;
  }
  .input-box{
    height: 0.389rem;
    position: relative;
    margin: 0.1rem 0px;
    overflow: hidden;
  }
  .area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.128rem!important;
    margin-top: 0.128rem!important;
    height: 0.410rem;
    border-radius: 0.026rem;
    background-color: #F2F3F7;
    padding-right: 0.085rem;
    box-sizing: border-box;
  }
  .address-box{
    height: 100%;
    line-height: 0.38rem;
    margin-left: 0.15rem;
  }
  .m-bj-edit{
    margin: 0 10px;
  }
  .m-bj-edit li{
    margin-bottom: 0;
    overflow: hidden;
    box-sizing: border-box;
    position: relative;
  }
  #shenming{
    margin:0.1rem 10px;
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
  .fa-map-marker{
    display: none;
  }
  .m-bj-edit .map{
    display: block;
    width: 0.125rem;
    height: 0.125rem;
    position: absolute;
    top: 0.129rem;
    left: 10px;
    z-index: 99;
  }
  .m-bj-edit .jsq-city,.m-bj-edit li .jsq-tele,.m-bj-edit .jsq-mianji{
    height:0.389rem;
    color: #333;
    background: #F3F3F3;
    border-radius:4px;
    font-size: 0.115rem;
    border: none;
    outline: none;
    padding-left: 10px;
  }
  .m-bj-edit li .m-row-int1{
    position: relative;
    width: 100%;
  }
  .jisuanqi-form .save-submit{
    width: 2.773rem;
    height: 0.410rem;
    background-color: #FF5353;
    color: #fff;
    text-align: center;
    line-height: 0.410rem;
    margin: 0 auto;
    font-size: 0.137rem;
    border-radius: 0.026rem;
  }
  .total-num {
    text-align: center;
    font-size: 0.119rem;
    color: #666666;
  }
  .quotate {
    font-size: 0.128rem;
    font-weight: bold;
    line-height: 0.512rem;
    margin-left: 0.166rem;
  }
  .eight_money {
    width: 100%;
    text-align: center;
    font-size: 0.205rem;
    font-weight: bold;
    color: #323232;
    /* margin-top: 0.188rem; */
    margin-bottom: 0.162rem;
  }
  .money-img{
    width: 2.773rem;
    height: 0.512rem;
    margin: 0.085rem 0.043rem 0.128rem;
    margin: 0 auto;
    background: url("../../../assets/img/qzw/barquote.png");
    background-size: 100% 100%;
  }
  .money-img span{
    display: inline-block;
    color: #1A1A1A;
    margin-top: 28px;
    font-size: 16px;
    margin-left: 6px;
    margin-right: 12px;
  }
  .num{
    width: 22px;
    height: 38px;
    background: url('../../../assets/img/qzw/num.png') no-repeat;
    background-size: 1250%;
    float: left;
    margin-right: 4px;
    margin-top: 0.13rem;
  }
  .num-gray{background-position: 39px 0 !important;}
  .num-0{background-position: 0px 0 !important;}
  .num-1{background-position: -27px 0 !important;}
  .num-2{background-position: -58px 0 !important;}
  .num-3{background-position: -87px 0 !important;}
  .num-4{background-position: -114px 0 !important;}
  .num-5{background-position: -140px 0 !important;}
  .num-6{background-position: -169px 0 !important;}
  .num-7{background-position: -199px 0 !important;}
  .num-8{background-position: -224px 0 !important;}
  .num-9{background-position: -255px 0 !important;}
</style>
