<template>
  <section id="active-box" v-bind:class="{ maskShow: maskInfo.maskShow }">
    <div class="top-banner">
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/top-banner.jpg"></a>
      <div class="liuhai"></div>
      <div class="login-info">
        <div v-if="!loginStatus"><span @click="onChangLogin(1)" style="color:#FFFD89">登录</span> 获取参与资格</div>
        <div v-else>
          <span>欢迎，{{userInfo.tel}}</span>&nbsp;&nbsp;
          <img src="../../../assets/img/activity/jjys/change-tel.png" v-if="changePhone" style="width:15px;vertical-align: bottom;" @click="onChangLogin(2)">
        </div>
      </div>
      <div class="my-prizes" @click="toMyPrize">
        我的奖品>
      </div>
      <div class="share-text">
        今日剩余抽奖机会：<span style="color:#F5E93D;">{{hasTimes}}</span> 次
        <span style="float:right;width:100px; height:100%;" @click="share"></span>
      </div>
    </div>
    <div class="dzp-body">
      <div class="tuopan">
        <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/tuopan.png" ></a>
      </div>
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/qiqiu.png" class="qiqiu"></a>
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/dzp-bg.png" class="pandi"></a>
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/pantai.png" class="pantai"  id="pantai"></a>
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/btn-getprize.png" class="panxin"  @click="onGetPrize"></a>
    </div>
    <div class="lunbo">
      <div class="lunbo-img">
        <ul :style="'top:' + ulTop + 'px'">
          <li v-for="(item, index) in zjInfoList" :key="item.tel + '-'+index">
            <span style="color:#fff;">恭喜 {{item.tel}} 获得奖品 <span style="color:#FFE284;padding-left:4px">{{item.text}}</span></span>
          </li>
        </ul>
      </div>
    </div>
    <template v-if="enVir !== 'app'">
      <div class="lunbo" style="text-align:center;margin-top:-0.4rem; padding-bottom:0.15rem;">
        <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/index-load.png" @click="toDownloadApp"></a>
      </div>
    </template>
    <div class="bottom-body">
      <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/bottom-body.png"></a>
    </div>
    <div class="activity-mask" v-show="maskInfo.maskShow"></div>
    <div class="win-box" v-show="maskInfo.maskShow">
      <div class="win-box-content">
        <!-- 登录弹框 -->
        <div class="login-win" v-if="maskInfo.loginWinShow">
          <div class="close-win" @click="closeWin('loginWinShow')"></div>
          <p style="padding:0.3rem 0px 0.1rem 0px;">{{loginText}}</p>
          <div class="input-item">
            <div class="input-box">
              <input type="tel" placeholder="请输入手机号" v-model="loginData.tel" maxlength="11">
            </div>
          </div>
          <div class="input-item">
              <div class="input-box fl" style="width:50%">
                <input type="number" placeholder="请输入验证码" v-model="loginData.telYzm">
              </div>
              <template v-if="yzmBtnStatu.clickAble">
                <div class="input-box fr" style="width:42%;border:1px solid #AA66FF;">
                  <button class="getyzm" @click="onYzmClick">{{yzmBtnStatu.yzmButtonText}}</button>
                </div>
              </template>
              <template v-else>
                <div class="input-box fr" style="width:42%;border:1px solid #5E0DC2;">
                  <button class="getyzm noclick">{{yzmBtnStatu.yzmButtonText}}</button>
                </div>
              </template>
          </div>
          <div class="input-item">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/down-btn.png" style="width:100%" @click="onLogin"></a>
          </div>
        </div>
        <!-- 奖品 弹框 -->
        <div class="prize-win" v-if="maskInfo.prizeName">
          <div class="close-win" @click="closeWin('prizeName')" style="right:0px"></div>
          <template v-if="prizeImgShow === 'sheji'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/prize-sheji.png" style="width:100%"></a>
            <div style="width:100%;height:36px;bottom:100px;position:absolute">
              <div style="height:100%;width:82%;margin:0px auto;" @click="duijiang"></div>
            </div>
            <div style="width:100%;height:36px;bottom:32px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
            </div>
          </template>
          <template v-if="prizeImgShow === 'baojia'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/prize-baojia.png" style="width:100%"></a>
            <div style="width:100%;height:36px;bottom:100px;position:absolute">
              <div style="height:100%;width:82%;margin:0px auto;" @click="duijiang"></div>
            </div>
            <div style="width:100%;height:36px;bottom:32px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
            </div>
          </template>
          <template v-if="prizeImgShow === 'kongtiao'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/kongtiao.png" style="width:85%"></a>
            <div style="width:100%;height:36px;bottom:82px;position:absolute">
              <div style="height:100%;width:70%;margin:0px auto;" @click="duijiang"></div>
            </div>
            <div style="width:100%;height:36px;bottom:36px;position:absolute">
              <div style="height:100%;width:70%;margin:0px auto;" @click="share"></div>
            </div>
          </template>
          <template v-if="prizeImgShow === 'dianshiji'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/dianshi.png" style="width:85%"></a>
            <div style="width:100%;height:36px;bottom:82px;position:absolute">
              <div style="height:100%;width:70%;margin:0px auto;" @click="duijiang"></div>
            </div>
            <div style="width:100%;height:36px;bottom:36px;position:absolute">
              <div style="height:100%;width:70%;margin:0px auto; " @click="share"></div>
            </div>
          </template>
          <template v-if="prizeImgShow === 'jinghuaqi'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/jinghuaqi.png" style="width:85%"></a>
            <div style="width:100%;height:36px;bottom:82px;position:absolute; ">
              <div style="height:100%;width:70%;margin:0px auto;" @click="duijiang"></div>
            </div>
            <div style="width:100%;height:36px;bottom:36px;position:absolute">
              <div style="height:100%;width:70%;margin:0px auto;" @click="share"></div>
            </div>
          </template>
          <template v-if="prizeImgShow === 'noprize'">
            <!-- 未中奖 有分享机会-->
            <template v-if="maskInfo.noprize">
              <template v-if="enVir === 'app'">
                <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/app-no-prize.png" style="width:100%"></a>
                <div style="width:100%;height:36px;bottom:50px;position:absolute">
                  <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
                </div>
              </template>
              <template v-else>
                  <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/noprize.png" style="width:100%"></a>
                  <div style="width:100%;height:36px;bottom:106px;position:absolute">
                    <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
                  </div>
                  <div style="width:100%;height:36px;bottom:22px;position:absolute">
                    <div style="height:100%;width:80%;margin:0px auto;" @click="toDownloadApp"></div>
                  </div>
              </template>
            </template>
            <!-- 未中奖，没有分享机会 -->
            <template v-if="maskInfo.noprizeNoshare">
              <template v-if="enVir === 'app'">
                <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/app-share-noprize.png" style="width:100%"></a>
                <!-- <div style="width:100%;height:36px;bottom:82px;position:absolute">
                  <div style="height:100%;width:70%;margin:0px auto; " @click="share"></div>
                </div> -->
              </template>
              <template v-else>
                <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/no-share-noprize.png" style="width:100%"></a>
                <div style="width:100%;height:40px;bottom:50px;position:absolute">
                  <div style="height:100%;width:80%;margin:0px auto;" @click="toDownloadApp"></div>
                </div>
              </template>
            </template>
          </template>
        </div>
        <!-- 完善中奖信息 -->
        <div class="login-win" v-if="maskInfo.wanshan">
          <div class="close-win" @click="closeWin('wanshan')"></div>
          <p style="padding:0.4rem 0px 0.1rem 0px;">完善领奖信息</p>
          <div class="input-item">
            <div class="input-box" style="text-align:left;line-height:33px; text-indent:8px;" @click="openPicker">
              <i class="fa fa-map-marker"></i> {{addrText}}
            </div>
          </div>
          <div class="input-item">
            <div class="input-box">
              <input type="text" placeholder="请输入您的姓名" v-model="loginData.userName">
            </div>
          </div>
          <div class="input-item">
            <img src="../../../assets/img/activity/jjys/qrlq.png" style="width:100%" @click="lingquPrize">
          </div>
        </div>
        <!-- 领取成功 lq-success.png-->
        <template v-else>
          <div class="getprize-success" v-if="maskInfo.getPrizeSuccess">
            <div class="close-win"  @click="closeWin('getPrizeSuccess')" style="right:0px"></div>
            <img src="../../../assets/img/activity/jjys/lq-success.png" style="width:100%">
            <div style="width:100%;height:42px;bottom:34px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="toDownloadApp"></div>
            </div>
          </div>
        </template>
        <!-- 抽奖次数已用完 还有分享机会-->
        <div class="getprize-success" v-if="maskInfo.noTimes && !maskInfo.noprizeNoshare">
          <div class="close-win" @click="closeWin('noTimes')" style="right:0px"></div>
          <template v-if="enVir === 'app'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/app-no-time.png" style="width:100%"></a>
            <div style="width:100%;height:40px;bottom:50px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
            </div>
          </template>
          <template v-else>
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/no-time-share.png" style="width:100%" ></a>
            <div style="width:100%;height:40px;bottom:105px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="share"></div>
            </div>
            <div style="width:100%;height:40px;bottom:20px;position:absolute">
              <div style="height:100%;width:80%;margin:0px auto;" @click="toDownloadApp"></div>
            </div>
          </template>
        </div>
        <!-- 次数用完，没有分享机会 -->
        <div class="getprize-success" v-if="maskInfo.noTimes && maskInfo.noprizeNoshare">
          <div class="close-win" @click="closeWin('noTimes')" style="0px"></div>
           <template v-if="enVir === 'app'">
            <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/app-no-times.png" style="width:100%"></a>
          </template>
          <template v-else>
           <a href="javascript:void(0)"><img src="../../../assets/img/activity/jjys/no-times.png" style="width:100%"></a>
            <div style="width:100%;height:42px;bottom:34px;  position:absolute">
              <div style="height:100%;width:82%;margin:0px auto;" @click="toDownloadApp"></div>
            </div>
          </template>
        </div>
      </div>
    </div>
    <m-tips ref="tips"/>
    <citySelect  ref='city' v-on:getCityVlaue="getCityVlaue"></citySelect>
    <!-- 分享插件 -->
    <div class="share-box"  v-show="showShare" @click="hideShare"></div>
      <div class="share-content" @click="h5share($event)" v-show="showShare">
        <share :config="shareConfig"></share>
        <div  @click.stop>
          <div class="close-share" @click="closeShare">取消</div>
        </div>
      </div>
    <!-- 微信引导分享 -->
    <div class="wxSharebg" v-show="wxShare" @click="closeWxShare">
      <img src="../../../assets/img/activity/jjys/wxbg.png" alt="">
    </div>
    <div class="haibaodiv" v-show="downHaibao">
      <span class="closeHaibao" @click="closeHaibao">X</span>
      <div class="haibao">
        <img src="http://staticqn.qizuang.com/custom/20190918/FiZ3WlWxrZJkxv-lip7fPltHx-us" alt="">
      </div>
      <div class="saveHaibao">长按上方图片保存至手机</div>
    </div>
  </section>
</template>
<script>
import mTips from '../../common/mTips.vue'
import citySelect from '../../common/citySelect.vue'
import { getPrizeData, sendMessage, userLogin, getNowStatus, shareAddTimes, drawNow, toOrder, getInfo } from '@/api/activityApi.js'
// import { Indicator } from 'mint-ui'
export default {
  name: 'jjys',
  components: {
    mTips, citySelect
  },
  data () {
    return {
      enVir: '',
      src: '',
      loginStatus: false,
      loginText: '请先登录以获取抽奖资格',
      addrText: '请选择您所在的城市',
      hasTimes: 1,
      todayShareCount: 0,
      long: 0,
      clickTimes: 0,
      rotateTransition: 4,
      hasClick: false,
      maskInfo: {
        maskShow: false,
        loginWinShow: false,
        prizeName: false,
        wanshan: false,
        getPrizeSuccess: false,
        noTimes: false,
        noprize: false,
        noprizeNoshare: false
      },
      prizeImgShow: '',
      prizeList: [],
      userInfo: {
        tel: '',
        token: ''
      },
      loginData: {
        tel: '',
        telYzm: '',
        userName: '',
        cs: '',
        qx: ''
      },
      yzmBtnStatu: {
        yzmButtonText: '获取验证码',
        clickAble: true
      },
      shareConfig: {
        url: window.location.href, // 网址，默认使用 window.location.href
        source: 'http://zxs.h5.qizuang.com', // 来源（QQ空间会用到）, 默认读取head标签：<meta name="site" content="http://overtrue" />
        title: '幸运大转盘-齐装网', // 标题，默认读取 document.title 或者 <meta name="title" content="share.js" />
        description: '齐装网幸运大转盘，多个大奖等你来拿！下载齐装网APP还可大大提升中奖概率！抽奖规则最终解释权归齐装网所有！', // 描述, 默认读取head标签：<meta name="description" content="PHP弱类型的实现原理分析" />
        image: '//staticqn.qizuang.com/webstatic/img/zxs/activity/jjys/share-haobao.png', // 图片, 默认取网页中第一个img标签
        sites: ['wechat', 'weibo', 'qq', 'wechat', 'weibo', 'qq'], // 启用的站点
        disabled: ['google', 'facebook', 'twitter'], // 禁用的站点
        wechatQrcodeTitle: '微信扫一扫：分享', // 微信二维码提示文字
        wechatQrcodeHelper: '<p>微信里点“发现”，扫一下</p><p>二维码便可将本文分享至朋友圈。</p>'
      },
      endCall: false,
      showShare: false,
      wxShare: false,
      zjInfoList: [
        {
          tel: '153****7932',
          text: '空气净化器'
        },
        {
          tel: '186****3273',
          text: '量房报价服务'
        },
        {
          tel: '177****0984',
          text: '4套设计方案'
        },
        {
          tel: '138****5608',
          text: '4套设计方案'
        },
        {
          tel: '139****4091',
          text: '量房报价服务'
        },
        {
          tel: '183****7325',
          text: '小米电视'
        },
        {
          tel: '173****8826',
          text: '4套设计方案'
        },
        {
          tel: '189****5861',
          text: '量房报价服务'
        },
        {
          tel: '180****7279',
          text: '量房报价服务'
        },
        {
          tel: '186****2810',
          text: '量房报价服务'
        }
      ],
      ulTop: 0,
      downHaibao: false,
      changePhone: true
    }
  },
  created () {
    // 嗅探宿主环境
    if (this.$route.query.from) {
      this.enVir = this.$route.query.from
      if (this.enVir === 'app') {
        this.changePhone = false
        if (!this.loginStatus) {
          this.$cookies.remove('token')
          this.$cookies.remove('jjysTel')
        }
      } else {
        this.changePhone = true
      }
      // 与app交互
      this.changeHeader()
      this.getAppStatus()
    }
    // 拿取奖品信息
    this.getPrizeData()
    if (this.$cookies.isKey('token')) {
      this.loginStatus = true
      this.userInfo.tel = this.$cookies.get('jjysTel')
      this.loginData.tel = this.$cookies.get('jjysTel')
      this.getActivityStatus()
    } else {
      this.loginStatus = false
    }
  },
  mounted () {
    setTimeout(() => {
      this.prizedDisplay(0)
    }, 3000)
  },
  methods: {
    closeHaibao () {
      this.downHaibao = false
    },
    getPrizeData () {
      getPrizeData().then((res) => {
        if (res.data.error_code === 0) {
          this.getPrizeData = res.data.data.info.prize_list
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: '网络错误，请稍后重试'
        })
      })
    },
    // 获取当前抽奖状态
    getActivityStatus () {
      getNowStatus({
        activity_id: 32,
        share: 1
      }).then(res => {
        if (res.data.error_code === 0) {
          this.hasTimes = res.data.data.user_num_times
        } else {
          this.hasTimes = 0
          this.todayShareCount = 0
          // localStorage.jjysTel = ''
          // localStorage.token = ''
          this.loginStatus = false
        }
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: '网络错误，请稍后重试'
        })
      })
    },
    // 抽奖
    onGetPrize () {
      let that = this
      // 点击抽奖时判断环境
      if (this.enVir === 'app') {
        if (that.$cookies.isKey('token')) {
          if (this.hasClick) {
            return
          }
          drawNow().then(res => {
            let that = this
            setTimeout(function () {
              that.hasClick = false
            }, 3000)
            if (res.data.error_code === 0) {
              this.clickTimes = this.clickTimes + 1
              this.hasTimes = res.data.data.user_num_times
              this.todayShareCount = res.data.data.today_share_count
              this.roateDeg(res.data.data.prize_info.prize_name)
            } else {
              // 分享次数未达到上限
              if (res.data.data.today_share_count <= 3) {
                this.maskInfo.maskShow = true
                this.maskInfo.noTimes = true
              } else {
                // 分享次数达到上限
                // this.$refs.tips.tipsFadeIn({
                //   text: res.data.error_msg
                // })
                this.maskInfo.maskShow = true
                this.maskInfo.noTimes = true
                this.maskInfo.noprizeNoshare = true
              }
            }
          }).catch(res => {
            this.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
          })
          this.hasClick = true
        } else {
          this.$bridge.callNative('base_login', {}, function (res) {
            if (res) {
              that.$cookies.set('token', res)
            }
          })
        }
      } else {
        // 用户未登录
        if (!this.loginStatus) {
          this.maskInfo.maskShow = this.maskInfo.loginWinShow = true
          return
        }
        if (this.hasClick) {
          return
        }
        // 已登陆
        // 抽奖接口
        drawNow().then(res => {
          let that = this
          setTimeout(function () {
            that.hasClick = false
          }, 3000)
          if (res.data.error_code === 0) {
            this.clickTimes = this.clickTimes + 1
            this.hasTimes = res.data.data.user_num_times
            this.todayShareCount = res.data.data.today_share_count
            this.roateDeg(res.data.data.prize_info.prize_name)
          } else {
            // 分享次数未达到上限
            if (res.data.data.today_share_count <= 3) {
              this.maskInfo.maskShow = true
              this.maskInfo.noTimes = true
            } else {
              // 分享次数达到上限
              // this.$refs.tips.tipsFadeIn({
              //   text: res.data.error_msg
              // })
              this.maskInfo.maskShow = true
              this.maskInfo.noTimes = true
              this.maskInfo.noprizeNoshare = true
            }
          }
        }).catch(res => {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        })
        this.hasClick = true
      }
    },
    // 旋转方法
    roateDeg (prizeInfo) {
      let roate = 0
      let clickTime = this.clickTimes
      switch (prizeInfo) {
        // 4套设计方案
        case '豪装设计方案4套':
          roate = clickTime * 3600 + 60
          this.xzFun(roate, 'sheji')
          break
        // 量房报价服务
        case '量房报价一体式服务':
          roate = clickTime * 3600 + 240
          this.xzFun(roate, 'baojia')
          break
        // 空调
        case '格力智能变频豪华空调':
          roate = clickTime * 3600
          this.xzFun(roate, 'kongtiao')
          break
        // 空调
        case '海尔除甲醛空气净化器':
          roate = clickTime * 3600 + 120
          this.xzFun(roate, 'jinghuaqi')
          break
        case '小米65英寸4K电视':
          roate = clickTime * 3600 + 180
          this.xzFun(roate, 'dianshiji')
          break
        default:
          roate = clickTime * 3600 + 300
          this.xzFun(roate, 'noprize')
          break
      }
    },
    xzFun (roate, type) {
      let pantai = document.getElementById('pantai')
      let that = this
      pantai.style.webkitTransform = 'rotate(' + roate + 'deg)'
      pantai.style.mozTransform = 'rotate(' + roate + 'deg)'
      pantai.style.msTransform = 'rotate(' + roate + 'deg)'
      pantai.style.oTransform = 'rotate(' + roate + 'deg)'
      pantai.style.transform = 'rotate(' + roate + 'deg)'
      if (type !== 'noprize') {
        toOrder({
          source: 19091155,
          source_in: 5,
          src: this.$route.query.src
        }).then(res => {
          if (res.data.error_code === 0) {
            // this.maskInfo.wanshan = false
            // this.maskInfo.maskShow = this.maskInfo.getPrizeSuccess = true
          }
        }).catch(res => {
          this.$refs.tips.tipsFadeIn({
            text: '网络错误，请稍后重试'
          })
        })
      }
      setTimeout(() => {
        that.maskInfo.maskShow = true
        that.prizeImgShow = type
        that.maskInfo.prizeName = true
        if (type === 'noprize') {
          if (that.todayShareCount > 3) {
            that.maskInfo.noprizeNoshare = false
            that.maskInfo.noprize = true
          } else {
            that.maskInfo.noprize = false
            that.maskInfo.noprizeNoshare = true
          }
        }
      }, 3000)
    },
    // 登录按钮点击
    onLogin () {
      let checRes = this.checkTel()
      if (!checRes) {
        return false
      }
      if (this.loginData.telYzm === '') {
        this.$refs.tips.tipsFadeIn({
          text: '验证码不能为空'
        })
        return false
      }
      userLogin({
        phone: this.loginData.tel,
        verify_code: this.loginData.telYzm
      }).then(res => {
        if (res.data.error_code === 0) {
          this.userInfo.tel = res.data.data.info.phone
          this.userInfo.token = res.data.data.jwt_token
          this.maskInfo.maskShow = this.maskInfo.loginWinShow = false
          this.loginStatus = true
          sessionStorage.token = res.data.data.jwt_token
          this.$cookies.set('token', res.data.data.jwt_token)
          this.$cookies.set('jjysTel', res.data.data.info.phone)
          this.getActivityStatus()
        } else {
          if (res.data.error_code === 4000003) {
            this.$refs.tips.tipsFadeIn({
              text: '验证码不正确'
            })
          } else {
            this.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
          }
        }
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: '网络错误，请稍后重试'
        })
      })
    },
    onChangLogin (t) {
      let that = this
      if (this.enVir === 'app') {
        this.$bridge.callNative('base_login', {}, function (res) {
          if (res) {
            that.$cookies.set('token', res)
          }
        })
      } else {
        if (t === 1) {
          this.loginText = '请先登录以获取抽奖资格'
        } else {
          this.loginText = '是否切换登录手机号'
        }
        this.maskInfo.maskShow = this.maskInfo.loginWinShow = true
        this.loginData.tel = ''
        this.loginData.telYzm = ''
      }
    },
    // 验证码获取
    onYzmClick () {
      let checRes = this.checkTel()
      if (checRes) {
        this.yzmBtnStatu.clickAble = false
        this.timeChange(60)
        sendMessage({
          phone: this.loginData.tel,
          type: 2
        }).then(res => {
          if (res.data.error_code === 0) {
            this.$refs.tips.tipsFadeIn({
              text: '短信发送成功'
            })
          }
        }).catch(res => {
          this.$refs.tips.tipsFadeIn({
            text: '网络错误，请稍后重试'
          })
        })
      }
    },
    timeChange (num) {
      let _me = this
      if (num > 0) {
        num = num - 1
        _me.yzmBtnStatu.yzmButtonText = num + '秒后重新获取'
        setTimeout(() => {
          _me.timeChange(num)
        }, 1000)
      } else {
        _me.yzmBtnStatu.yzmButtonText = '重新获取'
        this.yzmBtnStatu.clickAble = true
      }
    },
    // 领取奖品,发单
    lingquPrize () {
      if (this.loginData.userName === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请填写您的姓名'
        })
        return
      }
      toOrder({
        tel: this.loginData.tel,
        cs: this.loginData.cs,
        source: 19091155,
        source_in: 5,
        orderid: '',
        src: this.$route.query.src
      }).then(res => {
        if (res.data.error_code === 0) {
          this.maskInfo.wanshan = false
          if (this.enVir === 'app') {
            this.$refs.tips.tipsFadeIn({
              text: '领取成功'
            })
            this.maskInfo.maskShow = false
          } else {
            this.maskInfo.maskShow = this.maskInfo.getPrizeSuccess = true
          }
        }
      }).catch(res => {
        this.$refs.tips.tipsFadeIn({
          text: '网络错误，请稍后重试'
        })
      })
    },
    checkTel () {
      let reg = new RegExp('^(13|14|15|16|17|18|19)[0-9]{9}$')
      if (this.loginData.tel === '') {
        this.$refs.tips.tipsFadeIn({
          text: '请输入手机号码'
        })
        return false
      }
      if (!reg.test(this.loginData.tel)) {
        this.$refs.tips.tipsFadeIn({
          text: '请输入正确的手机号码'
        })
        return false
      }
      return true
    },
    getCityVlaue (city) {
      this.addrText = city[0].name + ' ' + city[1].name + ' ' + city[2].name
      this.loginData.cs = city[1].id
      this.loginData.qx = city[2].id
    },
    // 关闭登录框
    closeWin () {
      for (let i in this.maskInfo) {
        this.maskInfo[i] = false
      }
    },
    // 兑奖
    duijiang () {
      this.maskInfo.prizeName = false
      this.maskInfo.maskShow = this.maskInfo.wanshan = true
    },
    toDownloadApp () {
      if (this.enVir === 'pc') {
        location.href = 'https://zxs.h5.qizuang.com/qzdownload?channel=qz-hd-a1'
      } else {
        location.href = 'https://zxs.h5.qizuang.com/qzdownload?channel=qz-hd-a2'
      }
    },
    share () {
      let that = this
      if (this.enVir === 'app') {
        if (this.$cookies.isKey('token')) {
          // app环境下
          that.$bridge.callNative('base_share', {
            mShareUrl: 'http://zxs.h5.qizuang.com/activity/jjys',
            mShareTitle: '每日抽奖有惊喜，品牌家电任性送',
            mShareSubTitle: '下载齐装APP，赢取空调、电视概率更高哦',
            imagArray: ['http://staticqn.qizuang.com/webstatic/img/zxs/activity/jjys-banner-share.jpg']
          }, function (res) {
            if (res === '0') {
              shareAddTimes().then(res => {
                if (res.data.error_code === 0) {
                  this.hasTimes = res.data.data.remain_num
                  this.hasClick = false
                  this.todayShareCount = res.data.data.today_share_count
                  this.$refs.tips.tipsFadeIn({
                    text: '分享成功'
                  })
                  return false
                } else {
                  that.$refs.tips.tipsFadeIn({
                    text: res.data.error_msg
                  })
                }
              })
            }
          })
        } else {
          this.$bridge.callNative('base_login', {}, function (res) {
            if (res) {
              that.$cookies.set('token', res)
            }
          })
        }
      } else {
        // 判断登陆状态
        if (!this.loginStatus) {
          this.maskInfo.maskShow = this.maskInfo.loginWinShow = true
        } else {
          // 判断浏览器是否是微信内置
          const ua = navigator.userAgent.toLowerCase()
          if (ua.indexOf('micromessenger') !== -1) {
            this.wxShare = true
          } else {
            this.showShare = true
          }
        }
      }
    },
    // h5 分享
    h5share (e) {
      // 点击微信分享，弹出海报页
      if (e.target.className === 'social-share-icon icon-wechat') {
        this.downHaibao = true
        this.showShare = false
        shareAddTimes().then(res => {
          if (res.data.error_code === 0) {
            this.hasTimes = res.data.data.remain_num
            this.hasClick = false
            this.todayShareCount = res.data.data.today_share_count
          } else {
            this.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
          }
        })
      } else {
        this.downHaibao = false
      }
      // h5 分享
      shareAddTimes().then(res => {
        if (res.data.error_code === 0) {
          let that = this
          this.hasTimes = res.data.data.remain_num
          this.hasClick = false
          this.todayShareCount = res.data.data.today_share_count
          setTimeout(function () {
            that.$refs.tips.tipsFadeIn({
              text: '分享成功'
            })
          }, 1500)
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
      })
    },
    // 关闭分享
    hideShare () {
      this.showShare = false
    },
    closeShare () {
      this.showShare = false
    },
    closeWxShare () {
      this.wxShare = false
      shareAddTimes().then(res => {
        if (res.data.error_code === 0) {
          this.hasTimes = res.data.data.remain_num
          this.hasClick = false
          this.todayShareCount = res.data.data.today_share_count
        } else {
          this.$refs.tips.tipsFadeIn({
            text: res.data.error_msg
          })
        }
      })
    },
    //  城市选择
    openPicker () {
      this.$refs.city.openPicker()
    },
    // 进入我的奖品页
    toMyPrize () {
      let that = this
      // 先判断是否app环境
      if (this.enVir === 'app') {
        // 判断app是否登陆
        if (this.$cookies.isKey('token')) {
          window.location.href = '/activity/jjys/myprize?from=app' + '&src=' + this.$route.query.src
        } else {
          this.$bridge.callNative('base_login', {}, function (res) {
            if (res) {
              that.$cookies.set('token', res)
            }
          })
        }
      } else {
        if (!this.loginStatus) {
          this.maskInfo.maskShow = this.maskInfo.loginWinShow = true
        } else {
          window.location.href = '/activity/jjys/sharemyprize?sharebtnid=qzbottomshare&from=' + this.$route.query.from + '&src=' + this.$route.query.src
        }
      }
    },
    changeHeader () {
      let that = this
      that.$bridge.callNative('header', {
        navTitle: '幸运大转盘',
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
      // 拿取分享成功方法
      that.$bridge.registWebNew('JS_onShare', function (data, nativeCallBack) {
      // nativeCallBack(1)
        shareAddTimes().then(res => {
          if (res.data.error_code === 0) {
            that.hasTimes = res.data.data.remain_num
            that.hasClick = false
            that.todayShareCount = res.data.data.today_share_count
            that.$refs.tips.tipsFadeIn({
              text: '分享成功'
            })
          } else {
            that.$refs.tips.tipsFadeIn({
              text: res.data.error_msg
            })
          }
        })
      })
      // 右上角分享回调
      that.$bridge.registWebNew('JS_onNavClick', function (res, nativeCallBack) {
        let data = JSON.parse(res)
        if (data.key === 'share') {
          that.share()
        }
      })
      if (!that.endCall) {
        setTimeout(function () {
          that.changeHeader()
          that.getAppStatus()
        }, 200)
      }
    },
    getAppStatus () {
      let that = this
      this.$bridge.callNative('base_userData', {}, function (res) {
        if (res) {
          that.$cookies.set('token', res)
          that.getActivityStatus()
          getInfo().then(res => {
            if (res.data.error_code === 0) {
              that.$cookies.set('jjysTel', res.data.data.info.phone)
              that.userInfo.tel = res.data.data.info.phone
              that.loginData.tel = res.data.data.info.phone
              that.loginStatus = true
            } else {
              that.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      })
    },
    prizedDisplay (index) {
      let firstItem = this.zjInfoList[index]
      this.zjInfoList.push(firstItem)
      this.ulTop = -index * 40
      index = index + 1
      setTimeout(() => {
        this.prizedDisplay(index)
      }, 3000)
    }
  }
}
</script>
<style>
  #active-box a {
    -webkit-tap-highlight-color: rgba(0,0,0,0);
  }
  .maskShow{
    overflow: hidden;
    height:120vh!important;
    position: fixed;
    width:100%;
    top:0px;
    left:0px;
  }
  #active-box{
    max-width: 640px;
    min-width: 320px;
    margin:0px auto;
    overflow-x:hidden;
    min-height: 100%;
  }
  #active-box .liuhai{
    width:280px;
    height:33px;
    background:url('../../../assets/img/activity/jjys/liuhai.png') no-repeat;
    background-size: 100%;
    position: absolute;
    top:0px;
    left:0px;
    right:0px;
    margin:auto;
  }
  #active-box .top-banner{
    position: relative;
  }
  #active-box .top-banner img,.bottom-body img{
    width: 100%;
    vertical-align: top;
  }
  #active-box .login-info{
    position: absolute;
    top:0px;
    color:#fff;
    width:100%;
    text-align: center;
    font-size: 0.1rem;
    left:8%;
    padding-top:0.065rem;
  }
  #active-box .my-prizes{
    position: absolute;
    right: -0.13rem;
    top:9%;
    font-size: 0.1rem;
    color:#FFFD89;
    padding:0.06rem 0.16rem;
    border-radius: 0.15rem;
    background: -webkit-linear-gradient( #6926FF,#370F91 );
    background: -o-linear-gradient(#6926FF,#370F91);
    background: -moz-linear-gradient(#6926FF,#370F91);
    background: linear-gradient(#6926FF,#370F91);
  }
  #active-box .share-text{
    position: absolute;
    width:260px;
    height:45px;
    line-height: 45px;
    bottom:10px;
    color:#fff;
    left:0px;
    right:0px;
    margin:auto;
    font-size: 0.1rem;
    text-indent:0.05em;
    background: url('../../../assets/img/activity/jjys/shar-goon.png') no-repeat center center;
    background-size: auto 100%;
    text-indent: 10px;
  }
  #active-box .dzp-body{
    background: #5E0DC2;
    padding:0.2rem;
    position: relative;
  }
  #active-box .center-content{
    position: absolute;
    width: 100%;
    height: 100%;
    top:0px;
    left: 0px;
    background: blue;
  }
  #active-box .pandi{
    width:100%;
    vertical-align: top;
    position: relative;
  }
  #active-box .pantai{
    position: absolute;
    margin:auto;
    left:0px;
    right:0px;
    top:0px;
    bottom:0px;
    width:60%;
    vertical-align: top;
    transition: all 3s;
    -moz-transition: all 3s;
    -webkit-transition: all 3s;
    -o-transition: all 3s;
  }
  #active-box .panxin{
    position: absolute;
    margin:auto;
    left:0px;
    right:0px;
    top:0px;
    bottom:0px;
    width:30%;
    vertical-align: top;
  }
  #active-box .qiqiu{
    position: absolute;
    right:-5px;
    top:-5px;
  }
  #active-box .tuopan{
    width: 100%;
    text-align: center;
    position: absolute;
    bottom:-0.15rem;
    left:0px;
  }
  #active-box .activity-mask{
    position:fixed;
    width:100%;
    height: 100%;
    left:0px;
    top:0px;
    background: rgba(0, 0, 0, 0.7)
  }
  #active-box .win-box{
    position: fixed;
    margin:auto;
    left:0px;
    top:0px;
    height: 100%;
    width:100%;
    display: table;
  }
  #active-box .win-box-content{
    display: table-cell;
    vertical-align: middle;
    text-align: center;
  }

  #active-box .login-win{
    background:url("../../../assets/img/activity/jjys/login-bg.png") no-repeat;
    background-size: 100% 100%;
    width:90%;
    display: inline-block;
    color:#fff;
    position: relative;
    max-width: 300px;
  }
#active-box .input-item{
  height: 0.5rem;
  max-height:45px;
  width:80%;
  margin:0.08rem auto;
  overflow: hidden;
}
#active-box .input-box{
  height: 33px;
  border-radius: 30px;
  border:1px solid #fff;
  overflow: hidden;
}
#active-box .input-box input{
  background: none;
  width: 100%;
  line-height: 33px;
  height: 100%;
  padding-left: 10px;
  color:#fff;
}
#active-box .getyzm{
    width: 100%;
    height: 100%;
    font-size: 13px;
    background: #AA66FF;
    color:#FFF53E;
    padding-left:0px !important;
}
#active-box .close-win{
  background: url("../../../assets/img/activity/jjys/close-win.png") no-repeat;
  background-size: 100%;
  width:20px;
  height: 20px;
  position: absolute;
  right:4px;
  top:-35px;
}
#active-box .noclick{
  color:#5E0DC2 !important;
  border:1px solid #999;
}
#active-box .prize-win{
  position: relative;
  width: 300px;
  margin:0px auto;
}
#active-box .prize-win .prize-btn{
  position: absolute;
  height: 40px;
  width: 100%;
  bottom: 30px;
}
#active-box .prize-win .prize-btn div{
  width:73%;
  cursor: pointer;
  margin:0px auto;
  height: 100%;
}
#active-box .getprize-success{
  position: relative;
  width:300px;
  margin:0px auto;
}
#active-box .share-box{
  position: fixed;
  bottom:0px;
  width: 100%;
  height: 100%;
  left:0px;
  z-index: 999;
  background: rgba(0, 0, 0, 0.7)
}
#active-box .share-content{
  position: fixed;
  max-width:640px;
  bottom:60px;
  text-align:center;
  padding:10px 0px;
  width:96%;
  margin-left: 2%;
  border-radius: 10px;
  background: #fff;
  z-index: 9999
}
#active-box .lunbo{
  background:#5E0DC2;
  font-size: 0.09rem;
  padding: 0.3rem 0px;
  margin-top:-1px;
  margin-bottom:-1px;
  font-size: 0.11rem;
}
#active-box .lunbo-img{
  width:90%;
  border:0.026rem solid #6C2CFF;
  border-radius: 0.8rem;
  margin:0px auto;
  background:url("../../../assets/img/activity/jjys/lunbo.jpg") no-repeat 0.08rem;
  background-size: 0.18rem;
  text-align: center;
  box-shadow: 0px 0px 15px #000 inset;
  height: 40px;
  overflow: hidden;
  position: relative;
}
#active-box .lunbo-img ul {
  position: absolute;
  width:100%;
  left:0px;
  transition: all 0.5s;
  -moz-transition: all 0.5s;
  -webkit-transition: all 0.5s;
  -o-transition: all 0.5s;
}
#active-box .lunbo-img ul li{
  line-height: 40px;
}
#active-box .close-share {
  position: absolute;
  width: 100%;
  background: #fff;
  bottom: -50px;
  height: 40px;
  line-height: 40px;
  border-radius: 10px;
}
.wxSharebg {
  position: fixed;
  bottom: 0px;
  width: 100%;
  height: 100%;
  left: 0px;
  z-index: 999;
  background: rgba(0, 0, 0, 0.8);
}
.wxSharebg img {
  display: block;
  width: 80%;
  margin: 0 auto;
  margin-top: 10px;
}
.haibaodiv {
  position: fixed;
  z-index: 999;
  top: 0;
  max-width: 640px;
  height: 100%;
  background: rgba(0,0,0,.85);
}
.haibao img {
  display: block;
  width: 84%;
  margin: 0 auto;
  margin-top: 20px;
  -webkit-touch-callout:default;
}
.closeHaibao {
  position: absolute;
  top: 1%;
  right: 3%;
  width: 20px;
  height: 20px;
  line-height: 20px;
  background: #ccc;
  color: #fff;
  border-radius: 50%;
  text-align: center;
}
.saveHaibao {
  text-align: center;
  color: #fff;
  margin-top: 10px;
  width: 80%;
  margin: 10px auto;
  border-radius: 10px;
  height: 30px;
  line-height: 30px;
}
.social-share .icon-wechat:hover .wechat-qrcode {
  display: none;
}
.wechat-qrcode {
  display: none;
}
.social-share .social-share-icon {
  width: 50px;
  height: 50px;
}
.social-share .icon-wechat {
  margin-right: 30px;
}
.social-share .icon-weibo {
  margin-right: 30px;
}
.social-share .icon-qq {
  margin-right: 0;
}
</style>
