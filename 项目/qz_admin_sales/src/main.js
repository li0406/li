import Vue from 'vue'

import Cookies from 'js-cookie'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import Element from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import 'font-awesome/css/font-awesome.min.css'

import '@/styles/index.scss' // global css

import App from './App'
import store from './store'
import router from './router'
import qzconfig from './utils/config'

import i18n from './lang' // Internationalization
import './icons' // icon
import './errorLog' // error log
import './permission' // permission control

import * as filters from './filters' // global filters

import vueXlsxTable from 'vue-xlsx-table'

import preventReClick from './utils/preventReClick' // 防多次点击，重复提交

Vue.use(vueXlsxTable, {rABS: false})

Vue.use(Element, {
  size: Cookies.get('size') || 'medium', // set element-ui default size
  i18n: (key, value) => i18n.t(key, value)
})

// register global utility filters.
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key])
})

Vue.config.productionTip = false

Vue.prototype.$qzconfig = qzconfig

new Vue({
  el: '#app',
  router,
  store,
  i18n,
  render: h => h(App)
})
