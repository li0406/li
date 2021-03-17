import Vue from 'vue'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import ElementUI from 'element-ui'
// import 'element-ui/lib/theme-chalk/index.css'
import '@/styles/element-variables.scss'

import '@/styles/index.scss' // global css

import App from './App'
import store from './store'
import router from './router'

import exports from '@/api/exports'
import config from '@/utils/config'

import tableSearch from '@/components/Tablesearch/index'
import editPage from '@/components/Editpage/index'
Vue.component('tableSearch', tableSearch)
Vue.component('editPage', editPage)

import '@/icons' // icon
import '@/permission' // permission control

Vue.use(ElementUI)

Vue.prototype.$apis = exports// 接口
Vue.prototype.$config = config// 接全局配置

Vue.config.productionTip = false

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
