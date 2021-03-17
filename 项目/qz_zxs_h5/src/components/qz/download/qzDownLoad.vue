<template>
  <section>
    <div class="cell-load" v-if="envir.clientEnvir == 'weixin' || envir.clientEnvir == 'weibo'">
      <img src="https://staticqn.qizuang.com/custom/20201212/Fi55XqjGPVdlOdyUC0LYqMJj7XrJ.png" style="width:100%;" @click="weixinLoad">
    </div>
    <div class="mask" v-if="weixinMask" @click="closeMask">
      <img src="../../../assets/img/share/wxTips.png" style="width:80%;position:fixed;right:-20px;top:-0.198rem">
    </div>
  </section>
</template>
<script>
import {getApkUrl} from '../../../api/api.js'
export default {
  name: 'qzDownLoad',
  data () {
    return {
      weixinMask: false,
      envir: {
        system: '',
        clientEnvir: '',
        downLoadUrl: ''
      }
    }
  },
  created () {
    this.gerEnviroment()
    this.getUrl()
  },
  methods: {
    gerEnviroment () {
      let ua = navigator.userAgent.toLowerCase()
      this.envir.system = this.$globalFun.mobileSystem()
      if (ua.indexOf('micromessenger') !== -1) {
        this.envir.clientEnvir = 'weixin'
        return false
      }
      if (ua.indexOf('weibo') !== -1) {
        this.envir.clientEnvir = 'weibo'
        return false
      }
      this.envir.clientEnvir = 'normal'
    },
    toloadApk () {
      if (this.envir.clientEnvir === 'normal') {
        location.href = this.envir.downLoadUrl
      }
    },
    closeMask () {
      this.weixinMask = false
    },
    weixinLoad () {
      this.weixinMask = true
    },
    getUrl () {
      this.errorInfo = 'request'
      getApkUrl(2).then(res => {
        if (res.data.error_code === 0) {
          if (this.envir.system === 'iOS') {
            this.envir.downLoadUrl = res.data.data.ios.link
          } else {
            this.envir.downLoadUrl = res.data.data.android.link
          }
          this.toloadApk()
        }
      })
    }
  }
}
</script>
<style scoped>
  .download-section{
    height: 100%;
    display:table;
    width:100%
  }
  .download-section .cell-load{
    display: table-cell;
    vertical-align: middle;
    text-align: center;
  }
  .download-section .to-load-app{
    color:#fff;
    background:#ff5353;
    padding:0.03rem 0.1rem;
    border-radius: 5px;
    margin-top:10px;
  }
  .mask {
    background: rgba(0, 0, 0, 0.7);
    position:fixed;
    width: 100%;
    height: 100%;
    top:0px;
    left:0px;
  }
</style>
