<template>
  <div class="down-btn">
    <div class="download" id="qzbottomshare" v-if="thirdParty">下载齐装APP，寻找家装新灵感</div>
    <div class="download" @click="qzOpenApp" v-else>下载齐装APP，寻找家装新灵感</div>
    <p class="lose">有房要装，就上齐装</p>
  </div>
</template>

<script>
import qzOpenApp from '../../mixins/qzOpenApp'
import { getIosVer, mobileSystem } from '../../utils/globalFun'
export default {
  name: 'downBtn',
  mixins: [qzOpenApp],
  data () {
    return {
      iosVer: '',
      sys: '',
      thirdParty: false
    }
  },
  created () {
    this.iosVer = getIosVer().toLowerCase()
    this.sys = mobileSystem().toLowerCase()
    this.thirdParty = (this.iosVer >= 12) && (this.sys === 'ios')
  }
}
</script>

<style scoped>
  .down-btn{
    padding: 0.3rem 0;
    text-align: center;
    background:#fff;
  }
  .down-btn .download{
    width: 2.218rem;
    height: 0.34rem;
    line-height: 0.34rem;
    border-radius: 0.34rem;
    display: inline-block;
    background-color: #FF5353;
    color: white;
    font-size: 0.1194rem;
    font-weight: 600;
  }
  .down-btn .lose{
    color: #FF5353;
    line-height: 0.34rem;
    font-size: 0.1194rem;
  }
</style>
