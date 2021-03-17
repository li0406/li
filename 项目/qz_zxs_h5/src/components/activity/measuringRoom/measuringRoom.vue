<template>
  <div class="measuring-room">
    <div class="banner">
      <img class="banner-img" src="@/assets/img/activity/measuringRoom/bannerbg.png" alt />
      <img class="banner-bom" src="@/assets/img/activity/measuringRoom/banner-bom.png" alt />
      <div class="banner-btn" @click="openApp">
        <span>活动规则</span>
      </div>
    </div>
    <div class="main">
      <ul>
        <li v-for="(item, index) in list" :key="index">
          <img :src="urlList[item].url" alt='urlList[item].name' />
        </li>
      </ul>
    </div>
    <div class="back-tbn" @click="openApp">去APP抽奖</div>
    <div class="footer">
      <img class="icon" src="@/assets/img/activity/measuringRoom/logo.png" alt />
      <span>齐装网</span>
      <img class="x" src="@/assets/img/activity/measuringRoom/x.png" alt />
      <img class="icon" src="@/assets/img/activity/measuringRoom/youku.png" alt />
      <span>优酷视频VIP</span>
    </div>
    <div class="wx_tips">
      <div class="wechat-mask" v-if="isWechat">
        <img
          src="@/assets/img/share/wxTips.png"
          style="width:80%;position:fixed;right:-20px;top:-0.198rem"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { mobileSystem } from "@/utils/globalFun"
import callappLib from '@/mixins/callappLib'
export default {
  name: "MeasuringRoom",
  mixins: [callappLib],
  data() {
    return {
      urlList: [
        {url:require("@/assets/img/activity/measuringRoom/kayue.png"), name:'月卡'},
        {url:require("@/assets/img/activity/measuringRoom/kaji.png"), name:'季卡'},
        {url:require("@/assets/img/activity/measuringRoom/kayear.png"), name:'年卡'},
        {url:require("@/assets/img/activity/measuringRoom/btn.png"), name:'抽奖图片'}
      ],
      list: [0, 0, 1, 1, 3, 0, 0, 2, 0],
      isWechat: false
    };
  },
  created() {},
  mounted() {
    this.isDefaultBrowser()
    document.body.style.backgroundColor="#feeced"
  },
  methods: {
    isDefaultBrowser() {
      const sys = mobileSystem().toLowerCase();
      const ua = navigator.userAgent.toLowerCase();
      // 判断是否是ios的qq内置浏览器和qq浏览器的区别
      if (sys === "ios" && / QQ/i.test(navigator.userAgent)) {
        this.isWechat = true;
      }
      // 判断是否是android的qq内置浏览器和qq浏览器的区别
      if (
        sys === "android" &&
        /MQQBrowser/i.test(navigator.userAgent) &&
        /QQ/i.test(navigator.userAgent.split("MQQBrowser"))
      ) {
        this.isWechat = true;
      }
      // 判断是否是微信、微博手机百度内
      if (ua.indexOf("micromessenger") !== -1 || ua.indexOf("weibo") !== -1 || ua.indexOf('baiduboxapp') !== -1) {
        if (this.$parent.$parent.isWechat === undefined) {
          this.isWechat = true;
        }
      }
    },
    openApp() {
      const sys = mobileSystem().toLowerCase();
      if (sys === "ios"){
        this.qzOpenTips()
      }else{
        this.qzOpenApp()
      }

    }
  }
};
</script>

<style scoped>
.measuring-room {
  background: #feeced;
}
.measuring-room .banner {
  width: 100%;
  position: relative;
  z-index: 1;
}
.measuring-room .banner img {
  display: block;
  width: 100%;
  height: auto;
}
.measuring-room .banner-bom {
  position: absolute;
  bottom: 0;
  left: 0;
}
.measuring-room .banner-btn {
  position: absolute;
  top: 2.35rem;
  right: 0;
  width: 0.627rem;
  height: 0.256rem;
  background: url("../../../assets/img/activity/measuringRoom/rulebtn.png")
    no-repeat;
  background-size: contain;
  font-size: 0.102rem;
  line-height: 0.256rem;
}
.measuring-room .banner-btn span {
  padding-left: 0.07rem;
}
.measuring-room .main {
  margin: -0.1rem auto 0.128rem;
  width: 2.859rem;
  height: 2.722rem;
  background: url("../../../assets/img/activity/measuringRoom/zpbj.png")
    no-repeat;
  background-size: contain;
  z-index: 2;
  position: relative;
}
.measuring-room .main ul {
  display: flex;
  padding: 0.171rem;
  flex-wrap: wrap;
  justify-content: space-between;
}
.measuring-room .main ul li {
  text-align: center;
}
.measuring-room .main ul li img {
  display: line-block;
  height: 0.734rem;
  width: 0.819rem;
}
.measuring-room .main ul li p {
  font-size: 0.085rem;
}
.measuring-room .back-tbn {
  width: 1.877rem;
  height: 0.34rem;
  line-height: 0.34rem;
  margin: 0 auto;
  background: url("../../../assets/img/activity/measuringRoom/back.png")
    no-repeat;
  background-size: contain;
  font-size: 0.137rem;
  text-align: center;
}
.measuring-room .footer {
  text-align: center;
  height: 0.137rem;
  line-height: 0.137rem;
  padding: 0.1rem 0 0.2rem;
}
.measuring-room .footer .icon {
  height: 0.137rem;
}
.measuring-room .footer img {
  vertical-align: middle;
}
.measuring-room .footer .x {
  height: 0.043rem;
  padding: 0 0.08rem;
}
.measuring-room .footer span {
  font-size: 0.086rem;
}
.measuring-room .wx_tips .wechat-mask {
  position: fixed;
  left: 0px;
  top: 0px;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  z-index: 9999;
}
</style>
