<template>
  <section>
    <div class="jisuanqi-fandan">
      <!-- from表单填写 -->
      <div class="jisuanqi-form">
        <div class="money-img">
          <div class="" style="float: right;">
            <div class="num num-gray"></div>
            <div class="num num-gray"></div>
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
          <li class="input-box">
            <input class="m-row-int1 m-bj-edit-list jsq-mianji" v-model="formInfo.inputArray[0].value" name="mianji" placeholder="请输入您的面积">
          </li>
          <li class="input-box">
            <input class="m-row-int1 m-bj-edit-list jsq-tele" v-model="formInfo.inputArray[1].value" type="tel" maxlength="11" name="tel-number" placeholder="请输入手机号获取结果">
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
        <div class="save-submit"  @click="submit">
          <img src="../../../assets/img/qzw/detail-btn.gif" />
        </div>
        <p class="total-num">累计服务超1500万装修业主</p>
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
      addrText: '请选择您所在的城市',
      disclaimer: true,
      orderId: '',
      formInfo: {
        source: '',
        inputArray: [
          {
            name: 'mianji',
            placeholder: '请输入您的面积',
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
            placeholder: '请输入您的手机号获取报价结果',
            type: 'tel',
            maxlength: 11,
            value: '',
            tips: [
              '您填写的手机号码为空^_^!',
              '请填写正确的手机号码^_^!'
            ]
          }
        ],
        isCity: true,
        callBack: function (obj, parent, src) {
          zxbj(obj, src).then((res) => {
            console.log(res)
            if (res.data.error_code === 0) {
              parent.orderId = res.data.data
              location.href = '/zxbj-success?orderId=' + parent.orderId
            } else {
              parent.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
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
    that.formInfo.source = that.$globalFun.setSource(that.$route.query.source, 'baojia')
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
      if (this.validateForm()) {
        let yzData = this.formInfo.inputArray
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = this.cs
        parms['qx'] = this.qx
        parms['source'] = this.formInfo.source
        console.log(parms)
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            that.formInfo.callBack(parms, that.$parent, src)
          })
        } else {
          that.formInfo.callBack(parms, that.$parent, 'zxs-h5')
        }
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
    background:#fff;
    box-shadow:0px 0px 11px 0px rgba(0, 0, 0, 0.1);
    border-radius:16px;
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
    border-radius:4px;
    display: block;
    margin:10px;
  }
  .total-num {
    text-align: center;
    font-size: 0.119rem;
    color: #666666;
  }
  .money-img{
    height: 0.512rem;
    margin: 0.085rem 0.171rem 0.128rem;
    background: url("../../../assets/img/qzw/jisuanqi.png");
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
