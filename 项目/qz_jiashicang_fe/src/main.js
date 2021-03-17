import Vue from 'vue'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import ElementUI from 'element-ui'
import echarts from 'echarts'
import elementResizeDetectorMaker from 'element-resize-detector'
import 'element-ui/lib/theme-chalk/index.css'

import '@/styles/index.scss' // global css

import App from './App'
import store from './store'
import router from './router'

import exports from './api/exports'
import echartsconfig from './components/Echarts/config/config'
import core from './utils/core'

import '@/icons' // icon
import '@/permission' // permission control

import FilterMonth from '@/components/FilterMonth/index'
import qzBox from '@/components/qzBox/index'
import qzNewBox from '@/components/qzNewBox/index'
import qzTabSwith from '@/components/tabSwitch/index'
import qzTab from '@/components/qzTab/index'

/**
 * If you don't want to use mock-server
 * you want to use MockJs for mock api
 * you can execute: mockXHR()
 *
 * Currently MockJs will be used in the production environment,
 * please remove it before going online ! ! !
 */
// if (process.env.NODE_ENV === 'production') {
//   const { mockXHR } = require('../mock')
//   mockXHR()
// }

Vue.use(ElementUI)

Vue.config.productionTip = false
Vue.prototype.$echarts = echarts//  图表
Vue.prototype.$echartsconfig = echartsconfig
Vue.prototype.$apis = exports// 接口
Vue.prototype.$formatechartsdata = core.formatechartsdata
Vue.prototype.$elMaker = elementResizeDetectorMaker()
Vue.component('FilterMonth', FilterMonth)
Vue.component('qzBox', qzBox)
Vue.component('qzNewBox', qzNewBox)
Vue.component('qzTabSwith', qzTabSwith)
Vue.component('qzTab', qzTab)
new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
