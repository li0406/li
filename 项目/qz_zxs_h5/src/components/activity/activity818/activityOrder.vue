<template>
  <section>
    <div class="activity-fadan">
      <ul class="new-fadan">
        <li class="input-box jsq-city" v-if="formInfo.isCity">
          <div class="address-box" @click="openPicker">
            <img src="../../../assets/img/qzw/map.png" class="map">
            {{addrText}}
          </div>
        </li>
        <li>
          <input class="new-tele" type="tel" maxlength="11" name="tel-number" v-model="formInfo.inputArray[0].value" placeholder="请输入手机号，津贴先到先得！">
        </li>
      </ul>
      <div id="new-shenming">
        <input type="checkbox" checked="checked" id="new-mianze">
        <label @click="isDisclaimer">
          <i class="fa fa-check" v-if="disclaimer"></i>
        </label>
        <span>我已阅读并同意齐装网的</span>
        <a href="javascript:void(0)" @click='toMianze'><span>《免责声明》</span></a>
      </div>
      <div class="lingqu">
        <button type="button" @click="getHandleAllowance">领取装修津贴</button>
      </div>
      <div class="rule"><span @click="ruleShow">使用规则</span></div>
    </div>
    <city-select ref='city' v-on:getCityVlaue="getCityVlaue"></city-select>
    <m-tips ref="tips"/>
    <!--津贴领取成功弹窗-->
    <div class="lqsuccess-dialog success-dialog" v-if="jinTieDisabled">
      <div class="lqsuccess-box">
        <img class="close-btn" @click="closeDialog" src="../../../assets/img/activity/activity818/close-btn.png"/>
        <div class="success-top">领取成功！</div>
        <div class="lqsuccess-content">
          <img src="../../../assets/img/activity/activity818/success-content.png"/>
        </div>
        <p>装修津贴已放入您的账户中</p>
        <p>齐装APP「我的-我的账户-我的卡券-抽奖券」</p>
        <div class="open-qzapp fadan-btn" @click="toQuan">查看卡券</div>
      </div>
    </div>
    <!--使用规则弹窗-->
    <div class="rule-dialog success-dialog" v-if="ruleDisabled">
      <div class="rule-box">
        <img class="close-btn" @click="closeDialog" src="../../../assets/img/activity/activity818/close-btn.png"/>
        <div class="success-top">装修津贴使用规则</div>
        <div class="rule-content">
          <p>1.即日起,9月30日活动期间，用户可在线免费领取装修津贴红包；</p>
          <p>2.装修津贴为1000元（满4万元可用）、4999元（满10万元可用）；</p>
          <p>3.装修津贴可使用城市仅限：芜湖、成都、天津；</p>
          <p>4.领取津贴用户可凭领取成功短信或APP红包券到店使用；</p>
          <p>5.一个装修地点仅可使用一张装修津贴。</p>
        </div>
      </div>
    </div>
    <!--预约到店弹窗-->
    <div class="yuyue-dialog success-dialog" v-if="reserveDisabled">
      <div class="yuyue-box">
        <img class="close-btn" @click="closeDialog" src="../../../assets/img/activity/activity818/close-btn.png"/>
        <div>
          <div class="success-top">预约到店</div>
          <div class="yuyue-content">
            <ul class="new-fadan">
              <li>
                <div class="address-box" @click="openPicker">
                  <img src="../../../assets/img/qzw/map.png" class="map">
                  {{addrText}}
                </div>
              </li>
              <li>
                <input class="new-tele" type="tel" maxlength="11" name="yuyue-tel" v-model="formInfo1.inputArray[0].value" placeholder="请输入手机号，让我们为您服务">
              </li>
            </ul>
            <div id="new-shenming2">
              <input type="checkbox" checked="checked" id="new-mianze2">
              <label @click="isDisclaimer1">
                <i class="fa fa-check" v-if="disclaimer1"></i>
              </label>
              <span>我已阅读并同意齐装网的</span>
              <a href="javascript:void(0)" @click='toMianze'><span>《免责声明》</span></a>
            </div>
            <div class="yuyue-again-btn fadan-btn" @click="getHandleReserve">一键预约</div>
          </div>
        </div>
      </div>
    </div>

  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import citySelect from '../../common/citySelect.vue'
import router from "@/router";
import {getInfo, getAllowance, getReserve} from "@/api/activityApi";
export default {
  name: 'activityOrder',
  components: {
    citySelect,
    mTips
  },
  data() {
    return {
      isLoad: false, // 是否登录
      addrText: '请选择您所在的城市',
      telephone: '',
      interVal: null,
      endCall: false,
      disclaimer: true,
      disclaimer1: true,
      orderId: '',
      formInfo: {
        source: '',
        inputArray: [
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
        isCity: true
      },
      formInfo1: {
        source: '',
        inputArray: [
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
        isCity: true
      },
      jinTieDisabled: false,
      ruleDisabled: false,
      reserveDisabled: false,
    }
  },
  created() {
    var that = this
    that.formInfo.source = that.$globalFun.setSource(that.$route.query.source, 'gift')
    that.formInfo1.source = that.$globalFun.setSource(that.$route.query.source, 'gift')
    that.interVal = setInterval(() => {
      if (!that.endCall) {
        that.getPhone()
      } else {
        clearInterval(that.interVal)
      }
    }, 200)
  },
  methods: {
    isDisclaimer () {
      this.disclaimer = !this.disclaimer
    },
    isDisclaimer1 () {
      this.disclaimer1 = !this.disclaimer1
    },
    toMianze () {
      this.$bridge.callNative('Native_disclaimer', null, function (res) {})
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
    ruleShow() {
      this.ruleDisabled = true
    },
    closeDialog() {
      this.jinTieDisabled = false
      this.ruleDisabled = false
      this.reserveDisabled = false
    },
    // 唤起app原生登录函数
    callAppLogin () {
      this.$bridge.callNative('base_login', {}, res => {
        if (res) {
          this.$cookies.set('token', res)
          this.getPhone()
        }
      })
    },
    // 获取APP信息
    getPhone () {
      let that = this
      that.$bridge.callNative('base_userData', {}, function (res) {
        if (res) {
          that.$cookies.set('token', res)
          getInfo().then(res => {
            if (res.data.error_code === 0) {
              that.isLoad = true
              that.endCall = true
              that.formInfo.inputArray[0].value = res.data.data.info.phone
              that.formInfo1.inputArray[0].value = res.data.data.info.phone
            }
          })
        }
      })
    },
    validateForm (data, disclaimer) {
      let that = this
      let yzData = data.inputArray
      if (that.addrText === '请选择您所在的城市') {
        that.$refs.tips.tipsFadeIn({
          text: '请选择您所在的城市'
        })
        return false
      }
      for (let i in yzData) {
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
      if (!disclaimer) {
        that.$refs.tips.tipsFadeIn({
          text: '请勾选我已阅读并同意齐装网的《免责声明》'
        })
        return false
      }
      return true
    },
    getHandleAllowance() {
      let that = this
      let from = that.$route.query.from
      let parms = {}
      if (that.validateForm(that.formInfo, that.disclaimer)) {
        let yzData = that.formInfo.inputArray
        console.log(yzData)
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = that.cs
        parms['qx'] = that.qx
        parms['source'] = that.formInfo.source
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            that.getAllowance(parms,that.$parent,src)
          })
        } else {
          that.getAllowance(parms,that.$parent,'zxs-h5')
        }
      }
    },
    getAllowance(obj, parent, src){
      getAllowance(obj, src).then((res) => {
        if (res.data.error_code === 0) {
          this.jinTieDisabled = true
        } else {
          alert(res.data.error_msg)
        }
      })
    },
    getHandleReserve() {
      let that = this
      let from = that.$route.query.from
      let parms = {}
      if (that.validateForm(that.formInfo1, that.disclaimer1)) {
        let yzData = that.formInfo1.inputArray
        for (let i in yzData) {
          parms[yzData[i].name] = yzData[i].value
        }
        parms['cs'] = that.cs
        parms['qx'] = that.qx
        parms['source'] = that.formInfo1.source
        if (from === 'app') {
          that.$bridge.callNative('getChannelSrc', {}, function (src) {
            that.getReserve(parms, that.$parent, src)
          })
        } else {
          that.getReserve(parms, that.$parent, 'zxs-h5')
        }
      }
    },
    getReserve(obj, parent, src) {
      getReserve(obj, src).then((res) => {
        if (res.data.error_code === 0) {
          this.reserveDisabled = false
          this.$refs.tips.tipsFadeIn({
            text: "预约成功！客服将在24小时内致电联系您~"
          })
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
      })
    },
    toQuan() {
      this.$parent.checkQuan()
    }

  }
}
</script>
<style scoped>
* {
  margin: 0;
  padding: 0;
}
img{
  width: 100%;
}
.activity-box .activity-fadan{
  width: 2.638rem;
  height: 2.09rem;
  margin: 0 auto;
  background: url(../../../assets/img/activity/activity818/bg_818.png) no-repeat;
  background-size: 100% 100%;
  position: relative;
  top: -0.24rem;
  padding: 2.172rem 0.236rem 0;
}
.activity-fadan ul li{
  display: block;
  height: 0.384rem;
  background: #fff;
  border-radius: 3px;
  margin-bottom: 0.192rem;
  overflow: hidden;
}
.activity-fadan ul li:nth-child(2){
  margin-bottom: 0.15rem;
}
ul li .map{
  display: block;
  width: 0.125rem;
  height: 0.125rem;
  position: absolute;
  top: 0.129rem;
  left: 10px;
  z-index: 99;
}
ul li .address-box,.activity-fadan ul li input{
  width: 100%;
  height: 100%;
  line-height: 0.384rem;
  background: #F7F7F7;
  border: none;
  outline: none;
  padding-left: 0.128rem;
  font-size: 0.115rem;
  text-align: left;
  display: block;
  border-radius: 3px;
  position: relative;
}
ul li .address-box{
  padding-left: 0.22rem;
}
#new-shenming,#new-shenming2 {
  text-align: left;
  border:none;
  font-size: 0.1rem;
  height: 20px;
  box-sizing: border-box;
  margin-bottom: 0.05rem;
}
#new-shenming a,#new-shenming2 a {
  color: #51B7FF;
  border-bottom: 1px solid #51B7FF;
  display: inline-block;
  margin-left: -2px;
  line-height: 13px;
  width: 5em;
  height: 14px;
}
#new-shenming a span,#new-shenming2 a span {
  width: 6em;
  text-indent: -0.5em;
  color: #51B7FF;
}
#new-shenming span {
  display: inline-block;
  color: #E6E6E6;
}
#new-shenming2 span {
  display: inline-block;
  color: #ccc;
}
#new-mianze,#new-mianze2 {
  display: none;
}
#new-mianze + label,#new-mianze2 + label {
  width: 13px;
  height:13px;
  background: none;
  border:1px solid #E6E6E6;
  display: inline-block;
  text-align: center;
  vertical-align: middle;
  line-height: 13px;
  box-sizing: border-box;
  font-size: 10px;
  position: relative;
  top: -2px;
  color:#51B7FF;
}
#new-mianze:checked + label,#new-mianze2:checked + label {
  color:#51B7FF;
}
.activity-fadan .lingqu{
  width: 100%;
  height: 0.41rem;
  margin-bottom: 0.102rem;
}
.activity-fadan .lingqu button{
  display: block;
  width: 100%;
  height: 100%;
  background: url("../../../assets/img/activity/activity818/lingqu.png") no-repeat;
  background-size: 100% 100%;
  border: none;
  outline: none;
  font-size: 0.179rem;
  color: #D21E12;
  text-shadow:0px 1px 0px rgba(255,255,255,0.5);
}
.activity-fadan .rule{
  text-align: center;
}
.activity-fadan .rule span{
  padding-bottom: 5px;
  border-bottom: 1px solid #FFEABB;
  color: #FFEABB;
}
/*发单领取成功弹窗*/
.success-dialog{
  width: 100%;
  height: 100%;
  position: fixed;
  top:0;
  background: rgba(0,0,0,0.6);
  z-index: 99;
}
.success-dialog .close-btn{
  display: block;
  width: 0.256rem;
  height: 0.256rem;
  position: absolute;
  right: 0;
  top: -0.384rem;
}
.success-dialog .success-top{
  height: 0.461rem;
  background: url("../../../assets/img/activity/activity818/success-top.png") no-repeat;
  background-size: 100% 100%;
  text-align: center;
  line-height: 0.461rem;
  font-size: 0.179rem;
  color: #fff;
  font-weight: bold;
}
.lqsuccess-dialog .lqsuccess-box{
  width: 2.688rem;
  height: 2.654rem;
  background: #fff;
  border-radius: 0.043rem;
  position: absolute;
  top:0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
}
.success-dialog .success-top{
  height: 0.461rem;
  background: url("../../../assets/img/activity/activity818/success-top.png") no-repeat;
  background-size: 100% 100%;
  text-align: center;
  line-height: 0.461rem;
  font-size: 0.179rem;
  color: #fff;
  font-weight: bold;
}
.lqsuccess-box .lqsuccess-content{
  width: 0.998rem;
  height: 0.956rem;
  margin: 0.213rem auto 0.128rem;
}
.lqsuccess-box p{
  font-size: 0.102rem;
  color: #666;
  text-align: center;
  margin-bottom: 0.06rem;
}
.fadan-btn{
  width: 2.432rem;
  height: 0.341rem;
  margin: 0 auto;
  line-height: 0.341rem;
  text-align: center;
  font-size: 0.128rem;
  color: #fff;
  cursor: pointer;
  border-radius: 3px;
  background: #F2203E;
}
/*使用规则弹窗*/
.rule-dialog .rule-box{
  width: 2.688rem;
  height: 2.193rem;
  background: #fff;
  border-radius: 0.043rem;
  position: absolute;
  top:0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
}
.rule-dialog .rule-content{
  padding: 0.192rem;
}
.rule-dialog .rule-content p{
  font-size: 0.102rem;
  color: #666;
  margin-bottom: 0.05rem;
}
/*预约到店弹窗*/
.yuyue-dialog .yuyue-box{
  width: 2.688rem;
  height: 2.291rem;
  background: #fff;
  border-radius: 0.043rem;
  position: absolute;
  top:0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
}
.yuyue-dialog .yuyue-content{
  padding: 0.171rem 0.128rem;
}
.yuyue-dialog .yuyue-content ul li{
  display: block;
  height: 0.341rem;
  margin-bottom: 0.128rem;
  border-radius: 3px;
  overflow: hidden;
}
.yuyue-dialog .yuyue-content ul li button,.yuyue-dialog .yuyue-content ul li input{
  display: block;
  width: 100%;
  height: 100%;
  background: #F7F7F7;
  border: none;
  outline: none;
  text-align: left;
  padding-left: 0.1rem;
}
.yuyue-box .yuyue-success-content .gift-img{
  width: 0.631rem;
  height: 0.73rem;
  margin: 0.08rem auto;
}
.yuyue-box .yuyue-success-content p{
  font-size: 0.119rem;
  color: #666;
  text-align: center;
}
.yuyue-box .yuyue-success-content p:first-child{
  margin-top: 0.1rem;
}
.yuyue-box .yuyue-success-content p.more{
  font-size: 0.107rem;
  color: #999;
  margin-bottom: 0.06rem;
}
.fadan-btn{
  width: 2.432rem;
  height: 0.341rem;
  margin: 0 auto;
  line-height: 0.341rem;
  text-align: center;
  font-size: 0.128rem;
  color: #fff;
  cursor: pointer;
  border-radius: 3px;
  background: #F2203E;
}
</style>
