<template>
  <div class="tjyl">
    <m-tips ref="tips"/>
    <div class="top_bg">
      <div class="top_text">
        <p>每推荐一位好友使用APP即可</p>
      </div>
      <div class="top_banner">
        <img src="../../assets/img/tjyl/top-title2.png" alt="">
      </div>
      <div class="top_order">
        <p>1.发送邀请给好友 - 2.好友登录齐装APP - 3.大额奖励到账</p>
      </div>
      <div class="rule_btn" @click="ruleBtn">
        活动规则
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

      <div class="scroll-wrap">
        <div class="scroll-content" :style="'top:' + ulTop + 'px'">

          <p v-for="item in prizeList">
            {{item}}
          </p>
        </div>
      </div>

      <div class="btn_title" @click="setShare">
        <span> 立即邀请好友</span>
      </div>

      <div class="title">
        <h3>
          <span>我的邀请</span>
          <span class="title-left"></span>
          <span class="title-right"></span>
        </h3>
      </div>

      <div class="my_text">
        <p v-if="myList.length>0">已获得 {{score}}现金券奖励，成功邀请{{count}}位新朋友</p>
        <p v-else>{{my}}</p>
      </div>
      <div class="my_list">
        <ul>
          <li class="my_list_item clearfix" v-for="(item,index) in myList" v-if="index<3 || isMore">
            <div class="pic fl">
              <img :src="item.avatar" alt="" v-if="item.avatar">
              <img src="../../assets/img/tjyl/head.png" alt="" v-else>
            </div>
            <div class="fl nickname">
              <h5>{{item.nickname}}</h5>
              <p>{{item.phone}}</p>
            </div>
            <div class="time fr">
              <span>{{item.date}}</span>
            </div>
            <div class="line"></div>
          </li>
        </ul>
      </div>


      <div class="btn_title" v-if="!isMore && myList.length>3">
        <span @click="moreBtn">查看更多</span>
      </div>

    </div>

    <div class="popup" v-if="popup">
      <div class="popup_main">
        <div class="popup_rule">
          <h3 class="popup_rule_title">推荐有礼活动规则</h3>
          <div class="popup_rule_line"></div>
          <div class="popup_rule_text">
            <p>1.活动期间，点击分享生成您的专属邀请活动，好友通过您的分享链接注册下载并登录齐装APP；</p>
            <p>2.邀请好友成功登齐装APP，即可获得现金券奖励，即时生效；</p>
            <p>3.邀请用户已有齐装网账号则视为邀请无效；</p>
            <p>4.要想得到更多现金券奖励则需要邀请不同新用户哦；</p>
            <p>5.获得现金券奖励仅在齐装APP内有效，满足礼品商城兑换条件可兑换商城礼品。</p>
          </div>
        </div>
      </div>
      <div class="popup_close_btn" @click="popupClose"></div>
    </div>

  </div>
</template>
<script>
  import mTips from '../common/mTips.vue'
  import {getRecommendList, getMyRecommendList, getInfo} from '@/api/tjylApi.js'

  export default {
    name: "tjyj.vue",
    components: {
      mTips
    },
    data() {
      return {
        popup: false,
        isApp: true,
        isMore: false,
        endCall: false, //查询
        score: 0,
        count: 0,
        more: false,
        my: "暂未邀请好友，快去发起邀请对话～",
        prizeList: ['小 ***子 成功推荐 猫 ***女 获得奖励'],
        ulTop: 0,
        myList: [],
        activeIndex: 0,
        intnum: undefined
      }
    },
    computed: {},
    created() {
      this.changeHeader();
      getRecommendList().then(res => {
        if(res.data.error_code === 0){
          this.prizeList = res.data.data.list
        }
      })
    },
    mounted() {
      setTimeout(() => {
        this.prizedDisplay(0)
      }, 1000)
    },
    methods: {

      changeHeader() {
        if (!this.endCall) {
          setTimeout(() => {
            this.changeHeader()
            this.getAppStatus()
          }, 200)
        }
      },
      //获取我的推荐列表
      getAppStatus() {
        this.$bridge.callNative('base_userData', {}, res => {
          if (res) {
            this.endCall = true;
            this.$cookies.set('token', res)
            getInfo().then(res => {
              if (res.data.error_code == 0) {
                let uuid = res.data.data.user_id;
                getMyRecommendList({
                  inviter: uuid
                }).then(res => {
                  if (res.data.error_code === 0) {
                    this.myList = res.data.data.list
                    this.score = res.data.data.score
                    this.count = res.data.data.count
                  }
                })
              }
            })
          }
        })
      },
      ruleBtn() {
        this.popup = true;
      },
      popupClose() {
        this.popup = false;
      },
      moreBtn() {
        this.isMore = true;
      },
      //广播
      prizedDisplay(index) {
        let firstItem = this.prizeList[index]
        this.prizeList.push(firstItem)
        this.ulTop = -index * 25
        index = index + 1
        setTimeout(() => {
          this.prizedDisplay(index)
        }, 1000)
      },
      setShare() {
        let that = this
        // 分享成功，实际环境
        that.$bridge.callNative('membership_recommendShare', {}, function (res) {})
      },
    },
  }
</script>

<style scoped>
  .tjyl {
    overflow: hidden;
  }

  .tjyl .top_bg {
    background: url("../../assets/img/tjyl/tjyl_bg.png") no-repeat;
    -webkit-background-size: 100%;
    background-size: 100%;
    height: 2.688rem;
    position: relative;
  }

  .tjyl .top_text {
    color: #FFFFFF;
    text-align: center;
    line-height: 2.5;
    font-size: 0.13rem;
    letter-spacing: 0.02rem;
    padding-top: 0.1rem;
  }

  .tjyl .top_banner {
    text-align: center;
    padding: 0.04rem 0;
  }

  .tjyl .top_banner img {
    width: 2.5rem;
  }

  .tjyl .top_order {
    margin: 0 0.2rem;
    font-size: 0.1rem;
    line-height: 0.2389rem;
    border-radius: 0.09rem;
    background: #FFF2D8;
    color: #F84D4C;
  }

  .tjyl .top_order p {
    text-align: center;
  }

  .tjyl .rule_btn {
    position: absolute;
    right: 0;
    top: 0.0853rem;
    color: #FFFFFF;
    width: 0.5333rem;
    height: 0.2133rem;
    line-height: 0.2133rem;
    background-color: #EA4545;
    text-align: center;
    border-radius: 0.1rem 0 0 .1rem;
    font-size: 0.1024rem;
  }

  .tjyl .main {
    background: #FEF5EE;
    margin-top: -0.08rem;
    padding-bottom: .15rem;
    border-radius: .05rem .05rem 0 0;
    overflow: hidden;
  }

  .tjyl .main .title {
    text-align: center;
    color: #F79732;
    margin-top: .1rem;
  }

  .tjyl .main .title h3 {
    display: inline-block;
    color: #5E1F11;
    position: relative;
    font-size: 0.17rem;
    line-height: 0.58rem;
  }

  .tjyl .main .title-left,
  .tjyl .main .title-right {
    position: absolute;
    top: 0.25rem;
    display: block;
    width: 0.7rem;
    height: 0.09rem;
  }

  .tjyl .main .title-left {
    left: -0.75rem;
    background: url("../../assets/img/tjyl/zuo.png") no-repeat;
    background-size: contain;
  }

  .tjyl .main .title-right {
    right: -0.75rem;
    background: url("../../assets/img/tjyl/you.png") no-repeat;
    background-size: contain;
  }

  .tjyl .main .invitation {
    margin: 0 0.128rem .25rem;
  }

  .tjyl .main .invitation_item {
    display: inline-block;
    width: 0.947rem;
    height: 1.195rem;
    background: url("../../assets/img/tjyl/hbao.png");
    background-size: contain;
    text-align: center;
  }

  .tjyl .integral {
    height: .35rem;
    margin-top: .2rem;
    color: #E5483F;
    font-size: 0.1536rem;
    font-weight: bold;
  }

  .tjyl .integral_last {
    height: .45rem;
    margin-top: .1rem;
  }

  .tjyl .main .invitation_item h4 {
    font-size: 0.111rem;
    color: #FFECC6;
    line-height: 3;
  }

  .tjyl .main .invitation_item p {
    color: #E3771A;
    font-size: 0.1109rem;
    line-height: 1.5;
  }

  .tjyl .scroll-wrap {
    /*height: 0.2rem;*/
    height: 25px;
    overflow: hidden;
    margin: 0 .1rem .25rem;
    position: relative;
    border-radius: .05rem;
    background: #FFECC6 url("../../assets/img/tjyl/laba.png") no-repeat .07rem center;
    background-size: 0.1rem .1rem;
  }

  .tjyl .scroll-content {
    position: absolute;
    transition: top 0.5s;
    /*height: .2rem;*/
    width: 100%;
    height: 25px;
  }

  .tjyl .scroll-content p {
    padding-left: .25rem;
    line-height: 25px;
    /*line-height: 0.2rem;*/
    color: #E3771A;
    font-size: .09rem;
  }

  .tjyl .scroll-content .num {
    margin: 0 .2rem 0 .06rem;
  }

  .tjyl .scroll-content .name {
    margin: 0 .06rem 0 .04rem;
  }

  .tjyl .btn_title {
    text-align: center;
  }

  .tjyl .btn_title span {
    display: inline-block;
    background-color: #FF5353;
    text-align: center;
    padding: 0 0.55rem;
    line-height: 0.3755rem;
    color: #ffffff;
    font-size: 0.14rem;
    letter-spacing: 0.02rem;
    -webkit-border-radius: 0.2rem;
    -moz-border-radius: 0.2rem;
    border-radius: 0.2rem;
    font-weight: bold;
  }

  .tjyl .my_text {
    text-align: center;
    font-size: .1rem;
    line-height: 1;
    color: #E3771A;
    margin-top: -.08rem;
  }

  .tjyl .my_text span {
    color: #F84C4B;
    font-weight: bold;
    font-size: .12rem;
  }

  .tjyl .my_list {
    margin: 0.2rem .128rem .45rem;
  }

  .tjyl .my_list_item {
    margin: .08rem 0;
    position: relative;
  }

  .tjyl .my_list_item .pic img {
    width: 0.34rem;
    height: 0.34rem;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    border-radius: 50%;
    margin-right: .128rem;
  }

  .tjyl .my_list_item .nickname {

  }

  .tjyl .my_list_item .nickname h5 {
    color: #E5483F;
    font-size: .128rem;
    line-height: 1.5;
    margin-top: .02rem;

  }

  .tjyl .my_list_item .nickname p {
    color: #E3771A;
    font-size: .11rem;
    line-height: 1.2;
    margin-bottom: .1rem;
    letter-spacing: .008rem;
  }

  .tjyl .my_list_item .time {
    font-size: .11rem;
    color: #E3771A;
    line-height: .34rem;
  }

  .tjyl .my_list_item .line {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 2.6rem;
    height: 1px;
    margin-right: -.1rem;
    border-top: 1px #FFC48B dotted;
  }

  .tjyl .popup {
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    background: rgba(0, 0, 0, .7);
  }

  .tjyl .popup_main {
    border-radius: .064rem;
    margin: 1rem .256rem 0;
    background: #ffffff;
  }

  .tjyl .popup_rule_title {
    text-align: center;
    border-radius: .064rem .064rem 0 0;
    background-image: linear-gradient(to right, #FF5555, #EB3939);
    line-height: .512rem;
    color: #ffffff;
    font-size: .17rem;
    letter-spacing: .03rem;
  }

  .tjyl .popup_rule_line {
    height: .017rem;
    background-image: linear-gradient(to right, #FF8063, #FF5353);
    margin-top: .0155rem;
  }

  .tjyl .popup_rule_text {
    margin: .12rem .15rem 0;
    padding-bottom: .16rem;
    font-size: .1rem;
    line-height: .17rem;
  }

  .tjyl .popup_rule_text p {
    margin-bottom: .15rem;
  }

  .tjyl .popup_close_btn {
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

  .tjyl .popup_close_btn::before,
  .tjyl .popup_close_btn::after {
    content: '';
    position: absolute;
    height: .01rem;
    width: 70%;
    top: 50%;
    left: .047rem;
    margin-top: -.005rem;
    background: rgba(255, 255, 255, .3);
  }

  .tjyl .popup_close_btn::before {
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
  }

  .tjyl .popup_close_btn::after {
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
  }

  .tjyl .popup_more {
    text-align: center;
    padding-bottom: .3rem;
  }

  .tjyl .popup_more_pic {
    width: 1.7rem;
    margin: .12rem auto;
  }

  .tjyl .popup_more_title {
    color: #333333;
    font-size: .128rem;
    line-height: .24rem;
    font-weight: normal;
  }

  .tjyl .popup_more_h5 {
    color: #999999;
    font-size: .11rem;
    line-height: .2rem;
    margin-bottom: .1rem;
  }

  .tjyl .popup_more_toapp {
    background: #FF5353;
    margin: .3rem .17rem 0;
    line-height: .38rem;
    border-radius: .19rem;
    font-size: .15rem;
    color: #fff;
  }
</style>

