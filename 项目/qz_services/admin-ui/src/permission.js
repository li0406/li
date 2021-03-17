import router from './router'
import store from './store'
import {Message} from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import {getToken, setToken, getAccount, setAccount} from '@/utils/auth' // get token from cookie
import {routeArray} from '@/api/login'

NProgress.configure({showSpinner: false}) // NProgress Configuration

// permission judge function
function hasPermission(roles, permissionRoles) {
  if (roles.includes('admin')) return true // admin permission passed directly
  if (!permissionRoles) return true
  return roles.some(role => permissionRoles.indexOf(role) >= 0)
}

const whiteList = ['/login', '/auth-redirect'] // no redirect whitelist

let add = false
let token, account
const hrefLink = decodeURI(location.href)

/*20190606+*/
if (location.href.indexOf('=') > -1 &&location.href.substring(location.href.indexOf('=') + 1).length >50) {
  token = location.href.substring(location.href.indexOf('=') + 1)
  account = hrefLink.split('?')[1].split("=")[0]
} else {
  token = getToken()
  account = getAccount()
}
setToken(token)
setAccount(account)

store.dispatch('SetUserInfo', {name: account})

router.beforeEach((to, from, next) => {
  NProgress.start() // start progress bar
  // 这里只要获取到角色就能登进去，本次可能还要判断角色和token
  routeArray({
    token: token
  }).then(res => {
    if (res.data.error_code == 4000002 || res.data.error_code == 3000003 || res.data.error_code == 3000004) {
      // token过期，或者失效
      if (token && account) {
        location.reload()
      } else {
        alert('token失效，请重新登录')
        window.location.href = 'http://168uc.qizuang.com/main/'
      }
    } else {
      const data = res.data.data
      const route = []
      for(let p in data) {
        route.push(data[p])
      }
      if (token) {
        if (!add) {
          store.dispatch('GenerateRoutes', route).then((accessedRoutes) => { // 根据roles权限生成可访问的路由表
            add = true
            router.addRoutes(store.getters.addRoutes) // 动态添加可访问路由表
            next({...to, replace: true})
          })
        }
        NProgress.done()
      }
    }
  }).catch(err => {
    // alert('登录失败，请重新登录')
    // window.location.href = 'http://168uc.qizuang.com/main/'
    const data = ''
    store.dispatch('GenerateRoutes', data).then((accessedRoutes) => { // 根据roles权限生成可访问的路由表
      router.addRoutes(store.getters.addRoutes) // 动态添加可访问路由表
      next({...to, replace: true})
    })
  })
  next()
})

router.afterEach(() => {
  NProgress.done() // finish progress bar
})
