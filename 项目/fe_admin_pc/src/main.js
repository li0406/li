import Vue from 'vue'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

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

import * as filters from '@/utils/filters'

Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key])
})

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

Vue.prototype.$apis = exports// 接口
Vue.prototype.$config = config// 接全局配置

Vue.config.productionTip = false

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
