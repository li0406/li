<template>
  <section class="bg-color">
    <div class="logo"><img src="../../../assets/img/activity/luckdraw/logo.png" class="no-click"/></div>
    <div class="jiangpin">
      <p>恭喜您获得
      <span v-if="this.prizeid == 199 ? true : false">[美的空调]</span>
      <span v-if="this.prizeid == 200 ? true : false">[多肉植物]</span>
      <span v-if="this.prizeid == 201 ? true : false">[usb电扇]</span>
      </p>
      <img src="../../../assets/img/activity/luckdraw/jiang-bg.png" alt="">
      <img v-if="this.prizeid == 199 ? true : false" class="prize" style="width: 1.7rem" src="../../../assets/img/activity/luckdraw/kongtiao.png" alt="">
      <img v-if="this.prizeid == 200 ? true : false" class="prize" style="width: 1.28rem" src="../../../assets/img/activity/luckdraw/penzai.png" alt="">
      <img v-if="this.prizeid == 201 ? true : false" class="prize" style="width: 1.8rem" src="../../../assets/img/activity/luckdraw/fengshan.png" alt="">
    </div>
    <div class="form-yan">
      <p class="title">填写信息即可领取</p>
      <p>姓名：<input class="name" v-model="name" type="text" placeholder="请填写您的收货姓名" onfocus="this.placeholder=''" onblur="this.placeholder='请填写您的收货姓名'" ></p>
      <p>电话：<input v-model="tel" type="tel" placeholder="请填写您的联系电话" maxlength="11"  onfocus="this.placeholder=''" onblur="this.placeholder='请填写您的联系电话'" ></p>
      <p>地址：<input v-model="address" type="text" placeholder="请填写您的收货地址" onfocus="this.placeholder=''" onblur="this.placeholder='请填写您的收货地址'" ></p>
      <p><button type="submit" class="get" @click="submit">确认领取</button></p>
      <p><button type="button" class="share" @click="setShare">即刻分享</button></p>
      <p class="tip-bottom">即刻分享领取更多抽奖机会</p>
    </div>
    <m-tips ref="tips"/>
    <!-- 弹框 -->
    <div class="tip-box" v-if="tip" @touchmove.prevent>
      <div class="tip-box-center">
        <div class="cha" @click="quxiao">
          <img src="../../../assets/img/activity/luckdraw/cha.png" alt="">
        </div>
        <div class="tip-head">提示</div>
        <div class="xian"></div>
        <div class="tip-center">请填写您的收货信息，我们会以快递的形式将您所获的奖品邮寄给您哦~</div>
        <button @click="queding" type="submit" class="queding">确认</button>
        <button @click="quxiao" type="submit" class="quxiao">取消</button>
      </div>
    </div>
    <div class="tip-box" v-if="gaunbi" @touchmove.prevent>
      <div class="tip-box-center">
        <div class="cha" @click="qx">
          <img src="../../../assets/img/activity/luckdraw/cha.png" alt="">
        </div>
        <div class="tip-head">提示</div>
        <div class="xian"></div>
        <div class="tip-center">确定放弃本次抽奖所获的奖品吗？</div>
        <button @click="qd" type="submit" class="queding">确认</button>
        <button @click="qx" type="submit" class="quxiao">取消</button>
      </div>
    </div>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue' // 引入tips 提示框
import { isvalidPhone } from '../../../utils/validate'
import { shareAddTimes, submitWinInfo, abandon } from '@/api/api'
export default {
  name: 'prizeform',
  components: {
    mTips
  },
  data () {
    return {
      name: '',
      tel: '',
      address: '',
      prizeid: 1,
      logid: 1,
      zhongjiang: true,
      tip: false,
      gaunbi: false,
      goBackCallBack: '',
      endCall: false
    }
  },
  created () {
    var that = this
    that.logid = that.$route.query.logid
    that.prizeid = that.$route.query.prizeId
    that.changeHeader()
  },
  mounted () {
  },
  methods: {
    submit () {
      if (this.name === '') {
        this.tip = !this.tip
        return false
      }
      if (this.tel === '') {
        this.tip = !this.tip
        return false
      }
      if (!isvalidPhone(this.tel)) {
        this.$refs.tips.tipsFadeIn({
          text: '请填写正确的手机号码'
        })
        return false
      }
      if (this.address === '') {
        this.tip = !this.tip
        return false
      }
      submitWinInfo({
        log_id: this.logid,
        user_name: this.name,
        user_phone: this.tel,
        user_address: this.address
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$refs.tips.tipsFadeIn({
            text: '领取成功'
          })
          setTimeout(function () {
            window.location.assign('/activity/luckdraw')
          }, 3000)
        }
      })
    },
    // 信息填写确定取消
    queding () {
      this.tip = !this.tip
    },
    quxiao () {
      this.tip = !this.tip
    },
    // 左上角叉叉
    qd () {
      let that = this
      this.gaunbi = false
      abandon({
        log_id: this.logid
      }).then(res => {
        if (res.status === 200) {
          if (res.data.error_code === 0) {
            that.goBackCallBack(-1)
          } else {
            that.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
            setTimeout(function () {
              window.location.assign('/activity/luckdraw')
            }, 3000)
          }
        } else {
          that.$refs.tips.tipsFadeIn({
            text: '网络错误'
          })
        }
      })
    },
    qx () {
      let that = this
      this.gaunbi = false
      that.goBackCallBack(0)
    },
    // 分享功能调用
    setShare () {
      let that = this
      this.isMask = false
      // 分享成功，实际环境
      that.$bridge.callNative('base_share', {
        mShareUrl: 'https://' + document.domain + '/activity/luckdraw/activityshare',
        mShareTitle: '下载送好礼 惊喜无止境',
        mShareSubTitle: '3000元美的空调等你来领'
      }, function (res) {
        if (res === '0') {
          shareAddTimes(that.activityId).then(res => {
            if (res.data.error_code === 0) {
              location.reload()
            } else {
              that.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      })
    },
    changeHeader () {
      let that = this
      that.$bridge.callNative('UI_onBackClick', '000', function (res) {
      })
      that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        that.gaunbi = true
        that.goBackCallBack = nativeCallBack
      })
      that.$bridge.callNative('header', {
        navTitle: '中奖信息填写',
        customMenuList: [
          {
            text: '',
            name: 'share',
            menuStatus: '2',
            textColor: '',
            menuType: '2',
            menuImageUrl: ''
          }
        ]
      }, function (res) {
        if (res) {
          that.endCall = true
        }
      })
      if (!that.endCall) {
        setTimeout(function () {
          that.changeHeader()
        }, 200)
      }
    }
  }
}
</script>
<style scoped>
.bg-color{
  min-height: 100%;
  background: -webkit-linear-gradient(#6646D3 60%, #7878d9);
  background: -o-linear-gradient(#6646D3 60%, #7878d9);
  background: -moz-linear-gradient(#6646D3 60%, #7878d9);
  background: linear-gradient(#6646D3 60%, #7878d9);
  position: relative;
  overflow: hidden;
}
.jiangpin {
  position: absolute;
  top: 0.418rem;
  width: 100%;
  height: 3.157rem;
}
.jiangpin img {
  display: block;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  -moz-transform: translate(-50%,-50%);
  -webkit-transform: translate(-50%,-50%);
  -o-transform: translate(-50%,-50%);
  width: 3.2rem;
}
.jiangpin .prize {
  position: absolute;
}
.jiangpin p {
  width: 3.2rem;
  position: absolute;
  left: 50%;
  top: 0.3rem;
  transform: translateX(-50%);
  -ms-transform: translateX(-50%);
  -moz-transform: translateX(-50%);
  -webkit-transform: translateX(-50%);
  -o-transform: translateX(-50%);
  color: #fff;
  font-size: 0.205rem;
  font-weight:800;
  text-align: center;
  letter-spacing: 0.04rem;
  z-index: 1;
}
.jiangpin p span {
  color: #FFD859;
}
.logo{
  width:26%;
  position: absolute;
  top: 0.149rem;
  left: 0.128rem;
}
.logo img {
  width: 100%;
}
.form-yan {
  position: relative;
  width: 100%;
  padding: 0.5rem 0.299rem 0.371rem 0.188rem;
  margin-top: 2.5rem;
  z-index: 999;
  box-sizing: border-box;
}
.form-yan p {
  color: #fff;
  font-size: 0.128rem;
  text-align: center;
  margin-bottom: 0.186rem;
}
.form-yan .title {
  font-size: 0.15rem;
  font-weight:500;
  letter-spacing: 0.02rem;
}
.form-yan input {
  width: 2.3rem;
  height: 0.341rem;
  border:1px solid #fff;
  border-radius:5px;
  padding: 0 0.145rem;
  box-sizing: border-box;
  background-color:transparent;
  bFILTER: alpha(opacity=0);
  color: #fff;
}
input::input-placeholder{
  color:  #fff;
}
::-webkit-input-placeholder {
  color: #fff;
}
:-moz-placeholder {
  color: #fff;
}
::-moz-placeholder {
  color: #fff;
}
:-ms-input-placeholder {
  color: #fff;
}
.form-yan button {
  width: 2.3rem;
  height: 0.341rem;
  font-size: 0.128rem;
  margin-left: 0.4rem;
  box-sizing: border-box;
}
.get {
  background-color: #FFC13C;
  color: #fff;
  border-radius:5px;
}
.share {
  color: #FFD648;
  border:1px solid #FFD648;
  border-radius:5px;
}
.form-yan .tip-bottom {
  font-size: 0.1rem;
  color: #FEFEFE;
  margin: -0.1rem 0 0 0.4rem;
}
.tip-box {
  position: fixed;
  top: 0;
  z-index: 1500;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
}
.tip-box-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  -moz-transform: translate(-50%,-50%);
  -webkit-transform: translate(-50%,-50%);
  -o-transform: translate(-50%,-50%);
  width: 2.048rem;
  height: 1.749rem;
  border-radius: 0.085rem;
  background: #fff;
}
.tip-head {
  height: 0.503rem;
  background: -webkit-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: -o-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: -moz-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: linear-gradient(#FF5E5E 0%, #FF2642 100%);
  color: #fff;
  font-size: 0.154rem;
  text-align: center;
  line-height: 0.503rem;
  border-radius: 0.085rem 0.085rem 0 0;
}
.xian {
  width: 100%;
  height: 0.017rem;
  margin-top: 0.017rem;
  background-color: #FF2642;
}
.tip-center {
  width: 1.723rem;
  height: 0.422rem;
  color: #333;
  font-size: 0.111rem;
  margin: 0.149rem auto;
}
.queding {
  width: 0.832rem;
  height: 0.256rem;
  border-radius: 0.17rem;
  border: 1px solid #FF2F46;
  color: #FF2F46;
  float: left;
  margin-left: 0.098rem;
  margin-top: 0.05rem;
}
.quxiao {
  width: 0.832rem;
  height: 0.256rem;
  border-radius: 0.17rem;
  background: -webkit-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: -o-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: -moz-linear-gradient(#FF5E5E 0%, #FF2642 100%);
  background: linear-gradient(#FF5E5E 0%, #FF2642 100%);
  color: #fff;
  float: right;
  margin-right: 0.098rem;
  margin-top: 0.05rem;
}
.cha {
  position: absolute;
  top: -0.25rem;
  right: -0.1rem;
  width: 0.175rem;
  height: 0.175rem;
}
.cha img {
  display: block;
  width: 0.175rem;
  height: 0.175rem;
}
</style>
