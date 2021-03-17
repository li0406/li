<template>
  <div class="tjylshare">
    <m-tips ref="tips"/>
    <div class="top_bg">
      <div class="logo">
        <img src="../../assets/img/tjyl/logo.png" alt="">
      </div>
      <div class="top_text">
        <p>每推荐一位好友使用APP即可</p>
      </div>
      <div class="top_banner">
        <img src="../../assets/img/tjyl/top-title.png" alt="">
      </div>
      <div class="top_order">
        <p>1.发送邀请给好友 - 2.好友登录齐装APP - 3.大额奖励到账</p>
      </div>
      <div class="top_invitation">
        <h4>邀请好友一起嗨</h4>
        <!--<p>—— 有&nbsp;房&nbsp;要&nbsp;装&nbsp;&nbsp;就&nbsp;上&nbsp;齐&nbsp;装 ——</p>-->
        <p>—— 有房要装 就上齐装 ——</p>
      </div>
    </div>
    <div class="main">
      <div class="title">
        <h3>
          <span>邀请奖励</span>
          <span class="title-left"></span>
          <span class="title-right"></span>
        </h3>
      </div>
      <div class="invitation">
        <div class="invitation_item">
          <h4>首次邀请好友</h4>
          <div class="integral">
            <span>200现金券</span>
          </div>
          <p>两人各获得</p>
        </div>
        <div class="invitation_item">
          <h4>再次邀请好友</h4>
          <div class="integral">
            <span>200现金券</span>
          </div>
          <p>本人获得</p>
        </div>
        <div class="invitation_item">
          <h4>多次邀请好友</h4>
          <div class="integral integral_last">
            <span>邀请越多<br>奖励越多</span>
          </div>
          <p>更多活动</p>
        </div>
      </div>
      <div class="gift">
        <div class="line">
          <div class="people_top">
            已有 <span>{{num}}</span>人参与活动
          </div>
          <div class="form">
            <form action="">
              <div class="form_item">
                <input type="tel" placeholder="输入您的手机号参与活动" v-model="loginData.tel" maxlength="11">
                <span v-if="isTel" class="tel_warn">请输入正确的手机号码</span>
              </div>
              <div class="form_item clearfix">
                <input class="code fl" type="tel" placeholder="输入正确的验证码" v-model="loginData.code" maxlength="6">
                <template v-if="clickAble">
                  <button class="code_btn fr code_disabled" type="button" @click="onYzmClick">{{codeText}}</button>
                  <!--<button class="code_btn fr code_disabled" type="button" @click="onYzmClick">{{codeText}}</button>-->
                  <Geet :isGeet="isgt" @geetPath="GeetPath" @clickChange="GeetChange"></Geet>
                </template>
                <template v-else>
                  <button class="code_btn fr " type="button">{{codeText}}</button>
                </template>
              </div>
              <div class="form_item">
                <button type="button" class="invitation_btn" @click="accept">接受邀请</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="title">
        <h3>
          <span>新会员有机会获得</span>
          <span class="title-left"></span>
          <span class="title-right"></span>
        </h3>
      </div>
      <div class="gift">
        <div class="line">
          <ul>
            <li>
              <img src="../../assets/img/tjyl/gift1.png" alt="">
              <h6>多肉植物</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift2.png" alt="">
              <h6>网红抱枕</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift3.png" alt="">
              <h6>USB小电扇</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift4.png" alt="">
              <h6>多功能充电线</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift5.png" alt="">
              <h6>晴雨折叠伞</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift6.png" alt="">
              <h6>爱国者U盘</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift7.png" alt="">
              <h6>迷你加湿器</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift8.png" alt="">
              <h6>空气净化器</h6>
            </li>
            <li>
              <img src="../../assets/img/tjyl/gift9.png" alt="">
              <h6>格力空调</h6>
            </li>
          </ul>
        </div>
      </div>
      <div class="title">
        <h3>
          <span>参与规则</span>
          <span class="title-left"></span>
          <span class="title-right"></span>
        </h3>
      </div>
      <div class="bottom">
        <p>1.活动期间，点击好友分享链接注册并打开APP 登录，即可获得新人奖励;</p>
        <p>2.邀请用户已有齐装网账号则视为邀请无效；</p>
        <p>3.获得现金券奖励仅在齐装APP内有效，满足兑换条件可兑换相应商品。</p>
      </div>

      <div class="popup" v-show="popup">
        <div class="popup_main">
          <div class="popup_more">
            <div class="popup_more_pic">
              <img src="../../assets/img/tjyl/gift.png" alt="" width="100%">
            </div>
            <h3 class="popup_more_title" v-if="isApp">您已注册齐装APP 可直接登录</h3>
            <h3 class="popup_more_title" v-else>恭喜获得200现金券</h3>
            <h5 class="popup_more_h5" v-if="isApp">登录即享会员福利</h5>
            <h5 class="popup_more_h5" v-else>可抵现金使用</h5>

            <h4 class="popup_more_toapp" @click="openApp">
              <span v-if="isApp">去APP内登录</span>
              <span v-else>去APP内领取</span>
            </h4>

          </div>
        </div>
        <div class="popup_close_btn" @click="popupClose"></div>
      </div>
    </div>
    <div class="wx_tips">
      <div class="wechat-mask" v-if="isWechat">
        <img src="@/assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
      </div>
    </div>
  </div>
</template>

<script>
  import callappLib from '../../mixins/callappLib'
  import {mobileSystem} from '../../utils/globalFun'
  import mTips from '../common/mTips.vue'
  import {sendMessage, getRegister, activityNum, getGeet2} from '@/api/tjylApi.js'
  import Geet from "./Geet.vue"
  export default {
    name: "tjylshare.vue",
    mixins: [callappLib],
    components: {
      mTips,
      Geet
    },
    data() {
      return {
        codeText: "",
        disabled: false,
        isTel: false,
        clickAble: true,
        uuid: '',
        env: '',
        num: '',
        isRule: true,
        popup: false,
        isApp: false,
        loginData: {
          tel: ''
        },
        isWechat: false,
        isgt: false
      }
    },
    created() {
      this.codeText = "获取验证码";
      this.uuid = this.$route.query.uuid;
      this.env = "deviceInfo=" + this.$route.query.deviceInfo + "&platformVersion=" + this.$route.query.platformVersion + "&appName=" + this.$route.query.appName + "&appVersion=" + this.$route.query.appVersion;
    },
    mounted() {
      this.checkUuid();
      this.isDefaultBrowser();
    },
    methods: {

      isDefaultBrowser() {
        const sys = mobileSystem().toLowerCase()
        const ua = navigator.userAgent.toLowerCase()
        // 判断是否是ios的qq内置浏览器和qq浏览器的区别
        if (sys === 'ios' && / QQ/i.test(navigator.userAgent)) {
          this.isWechat = true
        }
        // 判断是否是android的qq内置浏览器和qq浏览器的区别
        if (sys === 'android' && /MQQBrowser/i.test(navigator.userAgent) && /QQ/i.test((navigator.userAgent).split('MQQBrowser'))) {
          this.isWechat = true
        }
        // 判断是否是微信、微博、手机百度内
        if (ua.indexOf('micromessenger') !== -1 || ua.indexOf('weibo') !== -1|| ua.indexOf('baiduboxapp') !== -1) {
          if (this.$parent.$parent.isWechat === undefined) {
            this.isWechat = true
          }
        }
      },
      openApp(){
        const sys = mobileSystem().toLowerCase()
        if (sys === "ios"){
          this.qzOpenTips()
        }else{
          this.qzOpenApp()
        }
      },

      // 验证码获取
      onYzmClick() {
        let checRes = this.checkTel()
        if (checRes) {
          console.log("2,按钮被点击，进行图形验证");
          this.isgt = !this.isgt;
        }
      },
      // 极验图片加载之后，通过更改控制变量实现可以再次加载
      GeetChange(val) {
        this.isgt = val;
      },
      GeetPath(val) {
        console.log("4,接受到图形验证参数，将参数发往服务端进行验证");
        console.log(val);
        this.isgt = false;
        getGeet2(val).then(res=>{
          if(res.data.status === 'success'){
            this.getSendMessage(val.geetest_validate)
          }
        })
      },

      getSendMessage(value){
        console.log("challenge", value)
        let env = this.env
        sendMessage({
          phone: this.loginData.tel,
          type: 3,
          verify_type: 2,
          challenge:value
        }, env).then(res => {
          if (res.data.error_code === 0) {
            this.clickAble = false
            this.timeChange(60)
            this.$refs.tips.tipsFadeIn({
              text: '短信发送成功'
            })
          } else if (res.data.error_code === 4000004) {
            this.popup = true
            this.isApp = true
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

      //接收邀请按钮
      accept() {
        let checRes = this.checkCode();
        let checRes1 = this.checkTel();
        let env = this.env
        if (checRes1 && checRes) {
          getRegister({
            phone: this.loginData.tel,
            verify_code: this.loginData.code,
            inviter: this.uuid
          }, env).then(res => {
            if (res.data.error_code == 0) {
              this.popup = true;
              this.isApp = false;
            } else {
              this.$refs.tips.tipsFadeIn({
                text: res.data.error_msg
              })
            }
          })
        }
      },

      //倒计时
      timeChange(num) {
        let _me = this
        if (num > 0) {
          num = num - 1
          _me.codeText = num + '秒后重新获取'
          setTimeout(() => {
            _me.timeChange(num)
          }, 1000)
        } else {
          _me.codeText = '获取验证码'
          this.clickAble = true
        }
      },
      //手机号验证
      checkTel() {
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
      checkCode() {
        let reg = new RegExp(/^\d{6}$/)
        if (this.loginData.code === '') {
          this.$refs.tips.tipsFadeIn({
            text: '请输入验证码'
          })
          return false
        }
        if (!reg.test(this.loginData.code)) {
          this.$refs.tips.tipsFadeIn({
            text: '请输入正确的验证码'
          })
          return false
        }
        return true
      },
      //随机数
      checkUuid() {
        let reg = new RegExp('4', 'g')//g代表全部
        var random = Math.floor(Math.random() * (25000) + 5000)
        this.num = JSON.stringify(random).replace(reg, '8');


        activityNum({}).then(res => {
          if (res.data.error_code === 0) {
            this.num = 50000 + parseInt(res.data.data.num)
          }

        }).catch(res => {
          this.$refs.tips.tipsFadeIn({
            text: '网络错误，请稍后重试'
          })
        })

      },
      //关闭弹窗
      popupClose() {
        this.popup = false;
      },
    }
  }
</script>

<style scoped>
  .tjylshare .logo {
    padding: 0.1024rem 0 0 0.1365rem;
  }

  .tjylshare .logo img {
    width: 0.239rem;
    height: 0.239rem;
  }

  .tjylshare .top_bg {
    background: url("../../assets/img/tjyl/top-pg.png") no-repeat;
    -webkit-background-size: 100%;
    background-size: 100% 100%;
    height: 4.565rem;
  }

  .tjylshare .top_text {
    color: #F9C47E;
    font-weight: bold;
    text-align: center;
    line-height: 1.1;
    font-size: 0.13rem;
    opacity: .8;
    letter-spacing: 0.01rem;
    margin-top: 0.005rem;
  }

  .tjylshare .top_banner {
    text-align: center;
    padding: 0 .2rem;
  }
  .tjylshare .top_banner img{
    width: 100%;
  }
  .tjylshare .top_order {
    margin: 0 0.2rem;
    font-size: 0.08rem;
    line-height: 0.2389rem;
    border-radius: 0.09rem;
    background: #FFDDB0;
    color: #9D151A;
  }

  .tjylshare .top_order p {
    text-align: center;
  }

  .tjylshare .top_invitation {
    text-align: center;
    margin-top: 1.8rem;
  }

  .tjylshare .top_invitation h4 {
    font-size: 0.205rem;
    color: #FDB964;
    line-height: 0.4rem;
    letter-spacing: 0.04rem;
  }

  .tjylshare .top_invitation p {
    font-size: 0.1rem;
    color: #F8AF49;
    line-height: 0.255rem;
    opacity: .8;
    letter-spacing: 0.01rem;
  }

  .tjylshare .main {
    background: #990910;
  }

  .tjylshare .main .title {
    text-align: center;
    color: #F79732;
  }

  .tjylshare .main .title h3 {
    display: inline-block;
    background-image: -webkit-gradient(linear, 0 0, 0 bottom, from(#FEDFB4), to(#F79732));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
    font-size: 0.205rem;
    line-height: 0.58rem;
  }

  .tjylshare .main .title-left,
  .tjylshare .main .title-right {
    position: absolute;
    top: 0.26rem;
    display: block;
    width: 0.7rem;
    height: 0.09rem;
  }

  .tjylshare .main .title-left {
    left: -0.75rem;
    background: url("../../assets/img/tjyl/zuo.png") no-repeat;
    background-size: contain;
  }

  .tjylshare .main .title-right {
    right: -0.75rem;
    background: url("../../assets/img/tjyl/you.png") no-repeat;
    background-size: contain;
  }

  .tjylshare .main .invitation {
    margin: 0 0.128rem .25rem;
  }

  .tjylshare .main .invitation_item {
    display: inline-block;
    width: 32.3%;
    height: 1.195rem;
    background: url("../../assets/img/tjyl/hbao.png") no-repeat;
    background-size: 100% 100%;
    text-align: center;
  }

  .tjylshare .people_top {
    color: #333333;
    font-size: 0.16rem;
    text-align: center;
    line-height: 0.47rem;
  }

  .tjylshare .people_top span {
    color: #FFBA14;
    font-size: 0.2rem;
  }

  .tjylshare .form_item {
    margin: 0 0.11rem 0.15rem;
  }

  .tjylshare .form input {
    width: 2.45rem;
    height: 0.384rem;
    background: #F2F2F2;
    border-radius: .4rem;
    padding-left: 0.15rem;
    color: #000;
  }

  .tjylshare .tel_warn {
    font-size: .1rem;
    line-height: 2;
    color: #c00;
    padding-left: .1rem;
  }

  .tjylshare .form input::-webkit-input-placeholder {
    color: #999999;
  }

  .tjylshare .form input:-moz-placeholder {
    color: #999999;
  }

  .tjylshare .form input::-moz-placeholder {
    color: #999999;
  }

  .tjylshare .form input:-ms-input-placeholder {
    color: #999999;
  }

  .tjylshare .form .code {
    width: 1.3rem;
  }

  .tjylshare .form .code_btn {
    width: 1rem;
    height: 0.384rem;
    border-radius: .4rem;
    background: rgba(249, 149, 44, .3);
    color: #ffffff;
    cursor: pointer;
    font-size: .11rem;
  }

  .tjylshare .form .code_disabled {
    background: rgba(249, 149, 44, 1);
  }

  .tjylshare .form .invitation_btn {
    background: #FF5353;
    display: block;
    width: 2.45rem;
    height: 0.384rem;
    color: #fff;
    border-radius: .4rem;
    font-size: 0.1536rem;
    cursor: pointer;
  }

  .tjylshare .integral {
    height: .35rem;
    margin-top: .2rem;
    color: #E5483F;
    font-size: 0.1536rem;
    font-weight: bold;
  }

  .tjylshare .integral_last {
    height: .45rem;
    margin-top: .1rem;
  }

  .tjylshare .main .invitation_item h4 {
    font-size: 0.111rem;
    color: #FFECC6;
    line-height: 3;
  }

  .tjylshare .main .invitation_item p {
    color: #E3771A;
    font-size: 0.1109rem;
    line-height: 1.5;
  }

  .tjylshare .main .gift {
    background: #FFFFFF;
    margin: 0 0.128rem;
    overflow: hidden;
    border-radius: 0.064rem;
  }

  .tjylshare .main .gift .line {
    border: 2px solid #e7bdbe;
    -webkit-border-radius: 0.64rem;
    -moz-border-radius: 0.64rem;
    border-radius: 0.064rem;
    margin: 0.064rem;
  }

  .tjylshare .main .gift ul li {
    display: inline-block;
    margin: 0.16rem 0;
    text-align: center;
    color: #333;
    font-size: 0.111rem;
    width: 32%;
  }

  .tjylshare .main .gift ul li img {
    width: 0.341rem;
    height: 0.341rem;

  }

  .tjylshare .main .bottom {
    font-size: 0.111rem;
    line-height: 0.154rem;
    color: #CCB092;
    margin: 0 0.205rem 0;
    padding-bottom: 0.3rem;
    letter-spacing: 0.01rem;
  }

  .tjylshare .main .bottom p {
    margin-bottom: 0.05rem;
  }


  .tjylshare .popup {
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    background: rgba(0, 0, 0, .7);
  }

  .tjylshare .popup_main {
    border-radius: .064rem;
    margin: 1rem .256rem 0;
    background: #ffffff;
  }

  .tjylshare .popup_rule_title {
    text-align: center;
    border-radius: .064rem .064rem 0 0;
    background-image: linear-gradient(to right, #FF5555, #EB3939);
    line-height: .512rem;
    color: #ffffff;
    font-size: .17rem;
    letter-spacing: .03rem;
  }

  .tjylshare .popup_rule_line {
    height: .017rem;
    background-image: linear-gradient(to right, #FF8063, #FF5353);
    margin-top: .0155rem;
  }

  .tjylshare .popup_rule_text {
    margin: .12rem .15rem 0;
    padding-bottom: .16rem;
    font-size: .1rem;
    line-height: .17rem;
  }

  .tjylshare .popup_rule_text p {
    margin-bottom: .15rem;
  }

  .tjylshare .popup_close_btn {
    width: .314rem;
    height: .314rem;
    border: 0.01rem solid rgba(255, 255, 255, .3);
    border-radius: 50%;
    margin: 0.21rem auto 0;
    line-height: .314rem;
    font-size: .3rem;
    text-align: center;
    overflow: hidden;
    position: relative;
    cursor: pointer;
  }

  .tjylshare .popup_close_btn::before,
  .tjylshare .popup_close_btn::after {
    content: '';
    position: absolute;
    height: .01rem;
    width: 70%;
    top: 50%;
    left: .047rem;
    margin-top: -.005rem;
    background: rgba(255, 255, 255, .3);
  }

  .tjylshare .popup_close_btn::before {
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
  }

  .tjylshare .popup_close_btn::after {
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
  }

  .tjylshare .popup_more {
    text-align: center;
    padding-bottom: .3rem;
  }

  .tjylshare .popup_more_pic {
    width: 1.7rem;
    margin: .12rem auto;
  }

  .tjylshare .popup_more_title {
    color: #333333;
    font-size: .128rem;
    line-height: .24rem;
    font-weight: normal;
  }

  .tjylshare .popup_more_h5 {
    color: #999999;
    font-size: .11rem;
    line-height: .2rem;
    margin-bottom: .1rem;
  }

  .tjylshare .popup_more_toapp {
    background: #FF5353;
    margin: .3rem .17rem 0;
    line-height: .38rem;
    border-radius: .19rem;
    font-size: .15rem;
    color: #fff;
  }

  .tjylshare .wx_tips .wechat-mask{
    position: fixed;
    left:0px;
    top:0px;
    width:100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
  }
</style>
