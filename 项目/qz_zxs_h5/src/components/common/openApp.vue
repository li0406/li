<template>
  <div class="open-app">
    <!--改成根据不同版本使用不同唤起方式-->
    <header class="qz-header clearfix" id="qztopshare" v-if="thirdParty" @click="qzOpenTips">
      <div class="left"><img src="../../assets/img/share/new-logo.png" class="img-responsive v-middle"></div>
      <div class="middle"><span class="c3" style="line-height: 1.5">有房要装，就上齐装！</span></div>
      <div class="right"><span class="openapp">去APP打开</span></div>
    </header>
    <header class="qz-header clearfix" v-else @click="qzOpenApp">
      <div class="left"><img src="../../assets/img/share/new-logo.png" class="img-responsive v-middle"></div>
      <div class="middle"><span class="c3" style="line-height: 1.5">有房要装，就上齐装！</span></div>
      <div class="right"><span class="openapp">去APP打开</span></div>
    </header>
  </div>
</template>

<script>
import qzOpenApp from '../../mixins/qzOpenApp'
import { getIosVer, mobileSystem } from '../../utils/globalFun'
export default {
  name: 'openApp',
  mixins: [qzOpenApp],
  data () {
    return {
      iosVer: '',
      sys: '',
      urlValue: 'qz/go?action=QZ_DECORATION_GUARANTEE',
      thirdParty: false
    }
  },
  mounted() {
      sessionStorage.setItem("urlValue", this.urlValue);
  },
  created () {
    this.iosVer = getIosVer().toLowerCase()
    this.sys = mobileSystem().toLowerCase()
    this.thirdParty = (this.iosVer >= 12) && (this.sys === 'ios')
  }
}
</script>

<style scoped>
.open-app{
  margin-bottom:0.078rem;
}
.qz-header{
  background-color: #FEE0E0;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  color: #fff;
  font-size: 14px;
  line-height: 1;
  padding: 5px 0.05rem;
  position: relative;
  text-align: center;
  white-space: nowrap;
}
.qz-header>div{
  float: left;
}
.qz-header > .left{
  width: 0.32rem;
  height: 0.32rem;
}
.qz-header > .middle{
  width: 1.7rem;
  line-height: 0.32rem;
  text-align: left;
  padding-left: 0.1rem;
}
.qz-header > .right{
  margin-top: 0.11rem;
  float: right;
}
.qz-header .openapp{
  background-color: white;
  color: #FF5353;
  border-radius: 0.5rem;
  padding: 0.02rem 0.06rem;
}
</style>
