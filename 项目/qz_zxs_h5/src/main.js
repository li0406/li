// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import Mint from 'mint-ui'
import Element from 'element-ui'
import '@/assets/css/public.css'
import 'mint-ui/lib/style.css'
import 'element-ui/lib/theme-chalk/index.css'
import 'font-awesome/css/font-awesome.min.css'
import bridge from './utils/bridge.js'
import globalFun from './utils/globalFun.js'
import globalData from './utils/globalData.js'
import config from './utils/config.js'
import VueVideoPlayer from 'vue-video-player'
import 'video.js/dist/video-js.css'
// import vshare from 'vshare'
import Share from 'vue-social-share'
import 'vue-social-share/dist/client.css'
import VueCookies from 'vue-cookies'

import initGeetest from './utils/gt.js'

Vue.use(VueCookies)
Vue.use(Share)
Vue.config.productionTip = false
Vue.use(Mint)
Vue.prototype.$bridge = bridge
Vue.prototype.$globalFun = globalFun
Vue.prototype.$globalData = globalData
Vue.prototype.$initGeet = initGeetest

Vue.prototype.$qzConfig = config

Vue.use(VueVideoPlayer)
Vue.use(Element)

globalFun.disableDefault()

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
