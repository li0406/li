<template>
  <section v-bind:class="[isMask ? 'static-style' : '', 'bg-color']">
    <div class="top-head owf" style="padding-top:10px;">
      <div class="fl logo" style="margin-top:5px;margin-left:10px;">
        <img src="../../../assets/img/activity/luckdraw/logo.png">
      </div>
      <div class="fr" style="margin-top:8px;">
        <span class="luckdraw-times">我抽奖的次数：{{userTimes}}</span>
      </div>
    </div>
    <div class="main-area">
      <img src="../../../assets/img/activity/luckdraw/yjj.png" class="yjj">
      <div class="change-box">
        <img src="../../../assets/img/activity/luckdraw/prize-arrow.png" class="prize-arrow">
        <ul class="change-list-box">
          <li v-for="item in prizeList" :key="item.id" :id='item.id'>
            <img :src="item.imgUrl" class="yjj">
          </li>
        </ul>
      </div>
      <div class="btn-box">
        <div class="btn-body" @click="getPrize">
          <img src="../../../assets/img/activity/luckdraw/btn-1.png" v-show="btnActive">
          <img src="../../../assets/img/activity/luckdraw/btn-2.png" v-show="!btnActive">
        </div>
      </div>
      <div class="bottom-btn-box">
        <div class="bottom-btn-item">
          <img
            src="../../../assets/img/activity/luckdraw/share-button-bg.png"
            style="width:100%"
            class="yjj"
          >
          <table class="item-tr" @click="setShare">
            <tr>
              <td>
                <span>即刻分享</span>
                <p>即刻分享领取更多抽奖机会</p>
              </td>
            </tr>
          </table>
        </div>
        <div class="bottom-btn-item">
          <img
            src="../../../assets/img/activity/luckdraw/share-button-bg.png"
            style="width:100%"
            class="yjj"
          >
          <table class="item-tr">
            <tr>
              <td @click="toRecord">
                <span>中奖记录</span>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="activity-rule-box">
      <div style="text-align:center; margin-bottom:0.2rem;">
        <div class="activity-rule-title">活动规则</div>
      </div>
      <ol>
        <li>1、活动时间：2019.6.25-2019.6.30</li>
        <li>2、注册登录齐装APP，通过启动页、首页BANNER进入活动页面参与活动；</li>
        <li>3、新老用户均有一次抽奖机会，分享活动即可再获得一次抽奖机会，每人最多拥有两次抽奖机会，奖品数量有限，先到先得，发完即止；</li>
        <li>4、奖品将会以邮寄的形式发放，请中奖用户如实填写相关信息并确认；</li>
        <li>5、奖品中奖名单将会在活动结束两天后在首页BANNER发布中奖名单；</li>
        <li>6、活动最终解释权归齐装网所有。</li>
      </ol>
    </div>
    <div class="bottom-bg-flower">
      <img
        src="../../../assets/img/activity/luckdraw/bottom-bj-flower.png"
        style="width:100%"
        class="yjj"
      >
    </div>
    <div class="confirm-mask" v-show="isMask">
      <div class="confirm-box">
        <table class="confirm-header">
          <img
            src="../../../assets/img/activity/luckdraw/cha.png"
            style="width:25px;position:absolute; top:-0.2rem;right:0px;"
            @click="closeWin"
          >
          <tr>
            <td>
              <span>{{errorTitle}}</span>
            </td>
          </tr>
        </table>
        <table style="height:0.55rem">
          <tr>
            <td>{{errorResult}}</td>
          </tr>
        </table>
        <button type="button" class="jk-share-button" @click="setShare">即刻分享</button>
      </div>
    </div>
    <m-tips ref="tips"/>
    <div class="tip-box" v-if="gaunbi">
      <div class="cha" @click="noFoBack">
        <img src="../../../assets/img/activity/luckdraw/cha.png" alt="">
      </div>
      <div class="tip-box-center">
        <div class="tip-head">提示</div>
        <div class="xian"></div>
        <div class="tip-center">确定放弃本次抽奖所获的奖品吗？</div>
        <button @click="canGoBack" type="submit" class="queding">确认</button>
        <button @click="noFoBack" type="submit" class="quxiao">取消</button>
      </div>
    </div>
  </section>
</template>
<script>
import prize1 from '../../../assets/img/activity/luckdraw/prize-1.png'
import prize2 from '../../../assets/img/activity/luckdraw/prize-3.png'
import prize3 from '../../../assets/img/activity/luckdraw/prize-2.png'
import prize4 from '../../../assets/img/activity/luckdraw/prize-4.png'
import mTips from '../../common/mTips.vue'
import { Indicator } from 'mint-ui'
import { getActivityData, drawPrizeInfo, shareAddTimes, getUserPartis } from '@/api/api'
export default {
  name: 'luckdraw',
  components: {
    mTips
  },
  data () {
    return {
      btnActive: true,
      prizeImgList: [prize1, prize2, prize3, prize4],
      staticPrizeList: [],
      changeOnce: true,
      originArray: [],
      prizeList: [],
      startTime: 10,
      changeButton: '',
      changePrize: '',
      errorTitle: '很遗憾，暂未中奖',
      hasActive: false, // 判断是否可以点击
      prizeId: '',
      isMask: false, // 遮罩层
      userTimes: 0,
      activityId: '',
      errorResult: '',
      logid: '',
      gaunbi: false,
      goBackCallBack: '',
      isWin: 0,
      maxTimes: 0,
      errorMsg: '',
      endCall: false,
      endTimes: false
    }
  },
  created () {
    let that = this
    that.changeHeader()
    that.getPrizeList()
    that.getDrawTimes()
  },
  mounted () {
    let that = this
    that.changeGetPrize()
  },
  methods: {
    // 获取奖品信息
    getPrizeList () {
      let that = this
      Indicator.open({
        text: '加载中...',
        spinnerType: 'fading-circle'
      })
      getActivityData().then(res => {
        if (res.data.error_code === 0) {
          that.activityId = res.data.data.info.id
          for (let i in res.data.data.info.prize_list) {
            res.data.data.info.prize_list[i].imgUrl = that.prizeImgList[i]
          }
          that.prizeList = res.data.data.info.prize_list
          that.originArray = JSON.stringify(res.data.data.info.prize_list)
          Indicator.close()
        }
      })
    },
    // 抽奖按钮跳动效果
    changeGetPrize () {
      let that = this
      that.btnActive = !that.btnActive
      that.changeButton = setTimeout(() => {
        that.changeGetPrize()
      }, 300)
    },
    getDrawTimes () {
      let that = this
      that.$bridge.callNative('base_userData', {}, function (res) {
        if (res) {
          that.endTimes = true
          sessionStorage.token = res
        }
        if (res === '') {
          that.endTimes = true
        }
      })
      if (!that.endTimes) {
        setTimeout(function () {
          that.getDrawTimes()
        }, 200)
      } else {
        getUserPartis().then(response => {
          if (response.data.error_code === 0) {
            that.userTimes = response.data.data.user_num_times
          }
        })
      }
    },
    // 点击按钮奖品跳转
    getPrize () {
      let that = this
      // 清除跳动
      clearTimeout(that.changeButton)
      // 清除旋转
      clearTimeout(that.changePrize)
      that.startTime = 10
      // 检测登录状态 真实环境
      let info = sessionStorage.token
      if (!info) {
        that.checkLogin()
      } else {
        // 第一次点击按钮，开始旋转
        if (!that.hasActive) {
          that.hasActive = true
          // 开始抽奖，获取抽奖结果
          drawPrizeInfo(that.activityId).then(res => {
            if (res.data.error_code === 0) {
              that.prizeId = res.data.data.prize_id
              that.logid = res.data.data.id
              that.isWin = res.data.data.win_status
              that.userTimes = res.data.data.user_num_times
              // 旋转列表
              that.prizeList = JSON.parse(that.originArray)
              that.changeOnce = true
              that.changePrizeList()
              setTimeout(() => {
                that.prizeList = JSON.parse(that.staticPrizeList)
                window.clearTimeout(that.changePrize)
                that.changePrize = null
                if (that.isWin === 1) {
                  setTimeout(function () {
                    location.href = '/activity/luckdraw/prizeform?logid=' + that.logid + '&prizeId=' + that.prizeId
                  }, 1000)
                } else {
                  if (res.data.data.is_share) {
                    that.isMask = true
                    that.errorTitle = '很遗憾，未中奖'
                    that.errorResult = '谢谢参与活动，祝您生活愉快~'
                  } else {
                    that.isMask = true
                    that.errorTitle = '很遗憾，未中奖'
                    that.errorResult = '分享即可再次参与抽奖活动哦~'
                  }
                }
              }, 4000)
            } else {
              if (res.data.data === null || res.data.data.is_share !== false) {
                that.errorMsg = res.data.error_msg
                that.$refs.tips.tipsFadeIn({
                  text: res.data.error_msg
                })
              } else {
                that.isMask = true
                that.errorTitle = '再来一次'
                that.errorResult = '分享即可再次参与抽奖活动哦~'
              }
            }
            that.hasActive = false
          })
        }
      }
    },
    // 旋转效果方法
    changePrizeList () {
      let that = this
      if (that.prizeList[1].id === that.prizeId && that.changeOnce) {
        that.staticPrizeList = JSON.stringify(that.prizeList)
        that.changeOnce = false
      }
      that.startTime = that.startTime + 3
      let first = that.prizeList.shift()
      that.prizeList.push(first)
      that.changePrize = setTimeout(() => {
        that.changePrizeList()
      }, that.startTime)
    },
    // 中奖记录页跳转
    toRecord () {
      location.href = '/activity/luckdraw/prizeinfo?activityId=' + this.activityId + '&logid=' + this.logid
    },
    // 关闭按钮
    closeWin () {
      this.isMask = false
      let that = this
      that.changeButton = setTimeout(() => {
        that.changeGetPrize()
      }, 300)
    },
    // 获取用户信息
    checkLogin () {
      this.$bridge.callNative('base_login', {}, function (res) {
        if (res) {
          sessionStorage.token = res
          location.reload()
        }
      })
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
      that.$bridge.callNative('header', {
        navTitle: '下载送好礼',
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
      // 回退
      that.$bridge.callNative('UI_onBackClick', '000', function (res) {
        if (res) {
          that.endCall = true
        }
      })
      that.$bridge.registWebNew('JS_onBackClick', function (data, nativeCallBack) {
        nativeCallBack(1)
      })
      // 右上角分享回调
      that.$bridge.registWebNew('JS_onNavClick', function (res, nativeCallBack) {
        let data = JSON.parse(res)
        if (data.key === 'share') {
          that.setShare()
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
.bg-color {
  background: -webkit-linear-gradient(#6646d3 40%, #95c4e3);
  background: -o-linear-gradient(#6646d3 40%, #95c4e3);
  background: -moz-linear-gradient(#6646d3 40%, #95c4e3);
  background: linear-gradient(#6646d3 40%, #95c4e3);
  position: relative;
}
.static-style {
  height: 100%;
  overflow: hidden;
}
.logo {
  width: 26%;
}
.logo img {
  width: 100%;
}
.luckdraw-times {
  background: rgba(255, 255, 255, 0.5);
  font-size: 0.13rem;
  padding: 0.04rem 0.1rem;
  border-radius: 20px;
  display: block;
  margin-right: -15px;
  padding-right: 20px;
  color: #fff;
}
.main-area {
  background: url("../../../assets/img/activity/luckdraw/red-package.png")
    no-repeat;
  background-position: left top;
  position: relative;
  background-size: 100% auto;
}
.yjj {
  width: 100%;
}
.change-box {
  width: 100%;
  height: 1.33rem;
  position: absolute;
  top: 2.23rem;
}
.change-list-box {
  width: 78%;
  margin: 0px auto;
  height: 100%;
  overflow: hidden;
  transition: all 2s;
  -moz-transition: all 2s; /* Firefox 4 */
  -webkit-transition: all 2s; /* Safari 和 Chrome */
  -o-transition: all 2s; /* Opera */
}
.change-list-box li {
  height: 33.33%;
  overflow: hidden;
  position: relative;
}
.change-list-box li img {
  width: 96%;
  position: absolute;
  left: -7px;
  right: 0px;
  top: 4px;
  bottom: 0px;
  margin: auto;
}
.btn-box {
  position: absolute;
  height: 0.63rem;
  width: 100%;
  text-align: center;
  left: 0px;
  top: 3.73rem;
}
.btn-box .btn-body {
  height: 100%;
}
.btn-box .btn-body img {
  height: 100%;
}
.bottom-btn-box {
  height: 1rem;
  position: absolute;
  top: 4.7rem;
  width: 100%;
  text-align: center;
}
.bottom-btn-item {
  width: 80%;
  height: 0.46rem;
  margin: 0px auto;
  color: #fff;
  position: relative;
}
.item-tr {
  position: absolute;
  width: 100%;
  height: 0.4rem;
  left: 0px;
  top: 0px;
  font-size: 0.11rem;
}
.item-tr span {
  font-size: 0.15rem;
}
.activity-rule-box {
  padding: 0.3rem;
  color: #fff;
}
.activity-rule-box ol li {
  margin-bottom: 0.16rem;
  line-height: 0.16rem;
  font-size: 0.12rem;
  text-align: justify;
}
.activity-rule-title {
  display: inline-block;
  border-left: 6px solid #ffbf37;
  padding-left: 8px;
  font-size: 0.16rem;
  line-height: 0.15rem;
  margin: 0px auto;
  font-weight: bold;
}
.bottom-bg-flower {
  width: 100%;
  position: absolute;
  bottom: 0px;
  left: 0px;
}
.prize-arrow {
  position: absolute;
  left: 0.33rem;
  z-index: 99;
  top: 0.6rem;
}
.confirm-mask {
  position: fixed;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  top: 0px;
  left: 0px;
  z-index: 999;
  display: table;
}
.confirm-box {
  width: 66.66%;
  height: 2rem;
  background: url("../../../assets/img/activity/luckdraw/confirm-window.png")
    no-repeat;
  margin: auto;
  background-size: 100% auto;
  left: 0px;
  top: 0px;
  right: 0px;
  bottom: 0px;
  position: absolute;
  text-align: center;
}
.confirm-box table {
  width: 100%;
}
.confirm-header {
  text-align: center;
  margin-top: 0.1rem;
  height: 28%;
  width: 100%;
  font-size: 0.14rem;
  color: #fff;
}
.confirm-header span {
  background: url("../../../assets/img/activity/luckdraw/face.png") no-repeat;
  background-size: 30px;
  background-position: left center;
  display: inline-block;
  height: 30px;
  line-height: 30px;
  padding-left: 40px;
}
.jk-share-button {
  padding: 0.1rem 0.5rem;
  background: #ff5252;
  color: #fff;
  margin-top: 0.16rem;
  font-size: 0.13rem;
  border-radius: 10px;
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
  overflow: hidden;
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
  top: 32%;
  left: 82%;
  transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  -moz-transform: translate(-50%,-50%);
  -webkit-transform: translate(-50%,-50%);
  -o-transform: translate(-50%,-50%);
  width: 0.175rem;
  height: 0.175rem;
}
.cha img {
  display: block;
  width: 0.175rem;
  height: 0.175rem;
}
</style>
