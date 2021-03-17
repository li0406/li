<template>
    <div class="liangfang">
        <div class="banner">
            <div class="logo-box"><img src="../../../assets/img/activity/liangfang/logo.png" alt="" class="logo"></div>
            <img src="http://staticqn.qizuang.com/custom/20190923/Fqw1ZXjFzo8USzT_dayszXgZ7YZr" alt="" class="caption">
        </div>
        <div class="my-center-tip clearfix">
            <div class="user-tips">尊敬的<span class="fix-user-width">{{user}}</span>，您好!</div>
            <div class="user-in" @click='toMyPackage'>我的礼包</div>
        </div>
        <div class="discount-box">
            <img src="http://staticqn.qizuang.com/custom/20190923/Fsbxuy7K_UhPc-iIwYCHLrq_0Ady" alt="" class="title">
            <ul class="alldiscount">
                <li class="one">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1.免费甲醛检测券</p>
                            <p class="discount-desc">2.空气治理抵用券满3000减200</p>
                            <p class="discount-desc">3.七折优惠券</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="two">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1.100元礼品（9月21日到店领取）</p>
                            <p class="discount-desc">2.500元提货卡（满3000使用）</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.10.08</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="three">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1.空气治理抵用券满3500元减500元</p>
                            <p class="discount-desc">2.空气治理抵用券满6000元减1000元</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="four" v-if="discoutSketch">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1.价值1399元追觅无限吸尘器1台</p>
                            <p class="discount-desc">2.400元优惠券</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="five" v-if="discoutSketch">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1.10元无门槛国内机票抵扣券</p>
                            <p class="discount-desc">2.20元无门槛国际机票抵用券</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="six" v-if="discoutSketch">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">首刷赠送艺伙APP一年会员价值365元</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
                <li class="seven" v-if="discoutSketch">
                    <div class="right-content">
                        <div class="discount-desc-box">
                            <p class="discount-desc">1000元优惠券，下单立享9.6折</p>
                        </div>
                        <div class="discount-get-box clearfix">
                            <span class="discount-deadline">2019.09.12~2019.12.31</span>
                            <div class="discount-btn-box"><button type='button' class="btn" @click="getDiscount"></button></div>
                        </div>
                    </div>
                </li>
            </ul>
            <p class="get-more" @click='HandleSketchMore' v-if='!discoutSketch'>
                <span class="vtop">展开更多</span>
                <img src="../../../assets/img/activity/liangfang/arrow-n.png" alt="" class="vtop" style="width: 0.2rem;">
            </p>
        </div>
        <div class="dercoation-line">
            <div class="top"></div>
            <div class="bottom"></div>
        </div>

        <div class="why-lf-box">
            <img src="http://staticqn.qizuang.com/custom/20190923/FrqpVX8BhiohKvenAnA3NjvCvc6S" alt="" class="title">
            <ul class="alllfreason">
                <li class="clearfix one">
                    <div class="pic l"></div>
                    <div class="desc r">
                        <p class="top-title">了解房屋详细尺寸数据</p>
                        <div class="desc-bottom">
                            <span class="tbline"></span>
                            <p>装修报价更精准</p>
                            <p> 设计更合理</p>
                        </div>
                    </div>
                </li>
                <li class="clearfix two">
                    <div class="pic r"></div>
                    <div class="desc l">
                        <p class="top-title">了解房屋格局利弊情况</p>
                        <div class="desc-bottom">
                            <span class="tbline"></span>
                            <p>空间充分利用</p>
                            <p> 布局更加合理</p>
                        </div>
                    </div>
                </li>
                <li class="clearfix three">
                    <div class="pic l"></div>
                    <div class="desc r">
                        <p class="top-title">保证后期房屋装修质量</p>
                        <div class="desc-bottom">
                            <span class="tbline"></span>
                            <p>户型缺陷及时发现改造</p>
                            <p>精确施工，做到不返工</p>
                        </div>
                    </div>
                </li>
                <li class="clearfix four">
                    <div class="pic r"></div>
                    <div class="desc l">
                        <p class="top-title">方便与设计师交流</p>
                        <div class="desc-bottom">
                            <span class="tbline"></span>
                            <p>业主设计想法可行性</p>
                            <p>量身定制，与众不同</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="dercoation-line">
            <div class="top"></div>
            <div class="bottom"></div>
        </div>
        <div class="lf-process">
            <img src="http://staticqn.qizuang.com/custom/20190923/FkxDPaWET6USOCJaV4seOISxyyAG" alt="" class="title">
            <p class="slogan">极简的流程 完善的服务</p>
            <img src="../../../assets/img/activity/liangfang/process.png" alt="" class="process-bar">
            <ul class="processitem">
                <li>
                    <img src="../../../assets/img/activity/liangfang/online.png" alt="">
                    <p>在线预约</p>
                </li>
                <li>
                    <img src="../../../assets/img/activity/liangfang/communicate-time.png" alt="">
                    <p>沟通时间</p>
                </li>
                <li>
                    <img src="../../../assets/img/activity/liangfang/access-home.png" alt="">
                    <p>上门量房</p>
                </li>
                <li>
                    <img src="../../../assets/img/activity/liangfang/layout.png" alt="">
                    <p>领取布局图</p>
                </li>
            </ul>
        </div>
        <div class="get-coupon">
            <button type="button" class="get-coupon-btn" @click="getDiscount">一键领取价值8000元优惠券</button>
        </div>
        <getDiscountTips v-if="showDiscountTips" :userPhone='userPhone' @listenerHideAction='hiddenDiscountTips'></getDiscountTips>
        <uploadLiangFangPic v-if="showUploadPic" @listenerHideUploadAction='hiddenUploadPic'></uploadLiangFangPic>
        <unGetAnything v-if='isGetDiscountStatus' @listenerHideUnGetAction='hiddenUnGetDiscount'></unGetAnything>
        <successGet v-if="successGetDiscount" @listenerHideSuccessGetAction='hiddenSuccessGetDia'></successGet>
        <hasGet v-if='hasGetDiscountStatus' @listenerHideHasGetAction='hiddenHasGetDia'></hasGet>
    </div>
</template>
<script>
import successGet from './components/successGet'
import unGetAnything from './components/unGetAnything'
import hasGet from './components/hasGet'
import getDiscountTips from './components/getDiscountTips'
import uploadLiangFangPic from './components/uploadLiangFangPic'
import { getInfo, isGetDiscount, lastOrderInfo } from '@/api/activityApi.js'
export default {
  name: 'LiangeFang',
  components: {
    getDiscountTips,
    uploadLiangFangPic,
    successGet,
    unGetAnything,
    hasGet
  },
  data () {
    return {
      user: '用户',
      userPhone: '',
      isLoad: false, // 是否登录
      isOrder: false, // 是否发单
      isGetDiscountStatus: false, // 是否领取过优惠券
      showDiscountTips: false,
      showUploadPic: false,
      successGetDiscount: false, // 是否成功领取优惠券
      loginStatus: false, // 登录状态
      discoutSketch: false,
      endCall: false,
      hasGetDiscountStatus: false
    }
  },
  created () {
    this.changeHeader()
  },
  methods: {
    changeHeader () {
      this.$bridge.callNative('header', {
        navTitle: '量房享好礼'
      }, (res) => {})
      if (!this.endCall) {
        setTimeout(() => {
          this.changeHeader()
          this.getAppStatus()
        }, 200)
      }
    },
    // 唤起app原生登录函数
    callAppLogin () {
      this.$bridge.callNative('base_login', {}, res => {
        if (res) {
          this.$cookies.set('token', res)
        }
      })
    },
    getDiscount () {
      if (!this.isLoad) {
        // 若未登录，调用app原生登录接口即可
        this.callAppLogin()
      } else {
        // 先判断是否领取过
        this.isGetDiscountInfo()
      }
    },
    getAppStatus () {
      this.$bridge.callNative('base_userData', {}, res => {
        if (res) {
          this.$cookies.set('token', res)
          getInfo().then(res => {
            if (res.data.error_code === 0) {
              this.user = res.data.data.info.nick_name
              this.userPhone = res.data.data.info.phone
              if (this.userPhone) {
                this.userPhone = this.userPhone.replace(/(\d{3})\d{5}(\d{3})/, '$1*****$2')
              }
              this.isLoad = true
              this.endCall = true
            } else {
              this.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      })
    },
    hiddenDiscountTips (msg) {
      this.showDiscountTips = false
      if (!msg) {
        this.successGetDiscount = true
      }
    },
    hiddenUploadPic () {
      this.showUploadPic = false
    },
    hiddenUnGetDiscount () {
      this.isGetDiscountStatus = false
    },
    hiddenSuccessGetDia () {
      this.successGetDiscount = false
      this.$router.push({
        path: 'mygiftpackage'
      })
    },
    hiddenHasGetDia () {
      this.hasGetDiscountStatus = false
    },
    toMyPackage () {
      if (!this.isLoad) {
        // 若未登录，调用app原生登录接口即可
        this.callAppLogin()
      } else {
        // 若已登录，判断用户是否已经领取过优惠券，领了跳转到我的礼包，没领弹出没领的提示
        isGetDiscount().then(res => {
          if (parseInt(res.data.data.count) === 0) {
            this.isGetDiscountStatus = true
          } else {
            // 已经领取优惠券，跳转到我的礼包
            this.$router.push({
              path: 'mygiftpackage'
            })
          }
        })
      }
    },
    isGetDiscountInfo () {
      isGetDiscount().then(res => {
        if (parseInt(res.data.data.count) === 0) {
          // 没有领取任何优惠券，判断7天内是否发单
          lastOrderInfo({
            day: 7
          }).then(res => {
            if (parseInt(res.data.error_code) === 0) {
              // 已发单，弹出上传图片提示框
              this.isOrder = true
              this.showUploadPic = true
            } else {
              // 未发单，弹出领取优惠提示框
              this.isOrder = false
              this.showDiscountTips = true
            }
          })
        } else {
          // 已经领取优惠券，弹出提示
          this.hasGetDiscountStatus = true
        }
      })
    },
    HandleSketchMore () {
      this.discoutSketch = true
    }
  }
}
</script>

<style scoped>
    @import url('../../../assets/css/liangfang/index.css');
</style>
