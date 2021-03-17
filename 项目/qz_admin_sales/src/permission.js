import router from './router'
import store from './store'
import { Message } from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import { getToken, setToken, getAccount, setAccount } from '@/utils/auth' // get token from cookie
import { routeArray } from '@/api/login'

NProgress.configure({ showSpinner: false }) // NProgress Configuration

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

/* 20190606+*/
/* token = getToken()
account = getAccount()
if (token && account && token.length > 50) {
  token = getToken()
  account = getAccount()
} else {
  token = location.href.substring(location.href.indexOf('=') + 1)
  account = hrefLink.split('?')[1].split("=")[0]

}*/
/* 20190606+*/
if (location.href.indexOf('=') > -1 && location.href.substring(location.href.indexOf('=') + 1).length > 200) {
  token = location.href.substring(location.href.indexOf('=') + 1)
  account = hrefLink.split('?')[1].split('=')[0]
} else {
  token = getToken()
  account = getAccount()
}
setToken(token)
setAccount(account)

store.dispatch('SetUserInfo', { name: account })

router.beforeEach((to, from, next) => {
  NProgress.start() // start progress bar
  // 这里只要获取到角色就能登进去，本次可能还要判断角色和token
  routeArray({
    token: token
  }).then(res => {
    if (res.data.error_code == 3000003 || res.data.error_code == 3000004 || res.data.error_code == 3000002) {
      // token过期，或者失效
      if (token && account) {
        location.reload()
      } else {
        alert('token失效，请重新登录')
        window.location.href = 'http://168uc.qizuang.com/main/'
      }
    } else {
      const data = res.data.data[0]
      if (token) {
        if (!add) {
          store.dispatch('GenerateRoutes', data).then((accessedRoutes) => { // 根据roles权限生成可访问的路由表
            add = true
            router.addRoutes(store.getters.addRoutes) // 动态添加可访问路由表
            next({ ...to, replace: true })
          })
        }
        NProgress.done()
      }
    }
  }).catch(err => {
    alert('登录失败，请重新登录')
    // window.location.href = 'http://168uc.qizuang.com/main/'
  })
  next()
  // if (getToken()) {
  //   if (store.getters.roles.length === 0) {
  //     store
  //       .dispatch('GetUserInfo')
  //       .then(res => {
  //         const roles = []
  //         store.dispatch('GenerateRoutes', { roles }).then(accessRoutes => {
  //           // 根据roles权限生成可访问的路由表
  //           router.addRoutes(store.getters.AllRoutes)
  //           // router.addRoutes(store.getters.AllRoutes) // 动态添加可访问路由表
  //           next({ ...to, replace: true }) // hack方法 确保addRoutes已完成 ,set the replace: true so the navigation will not leave a history record
  //         })
  //       })
  //       .catch(err => {
  //         store.dispatch('FedLogOut').then(() => {
  //           Message.error(err)
  //           next({ path: '/' })
  //         })
  //       })
  //   } else {
  //     next()
  //   }
  // } else {
  //   /* has no token*/
  //   if (whiteList.indexOf(to.path) !== -1) {
  //     // 在免登录白名单，直接进入
  //     next()
  //   } else {
  //     next(`/login?redirect=${to.path}`) // 否则全部重定向到登录页
  //     NProgress.done() // if current page is login will not trigger afterEach hook, so manually handle it
  //   }
  // }
})

router.afterEach(() => {
  NProgress.done() // finish progress bar
})
