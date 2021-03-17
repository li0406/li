import router from './router'
import store from './store'
// import { Message } from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import { setToken, removeToken } from '@/utils/auth' // get token from cookie
import getPageTitle from '@/utils/get-page-title'

NProgress.configure({ showSpinner: false }) // NProgress Configuration

// async function setinfo(next) {
//   try {
//     // get user info
//     await store.dispatch('user/getInfo')
//     const accessRoutes = await store.dispatch('user/menu')
//     const finalroutes = [...constantRoutes, ...accessRoutes]
//     router.options.routes = finalroutes
//     router.addRoutes(finalroutes)
//     next()
//   } catch (error) {
//     console.log('出错了')
//     removeToken()
//     NProgress.done()
//   }
// }
router.beforeEach((to, from, next) => {
  // start progress bar
  NProgress.start()

  // set page title
  document.title = getPageTitle(to.meta.title)

  // determine whether the user has logged in
  const token = to.query.token
  if (to.path === '/jump' && token) {
    store.state.user.token = token
    setToken(token)
    try {
      // get user info
      store.dispatch('user/getInfo')
      // const accessRoutes = await store.dispatch('user/menu')
      // const finalroutes = [...constantRoutes, ...accessRoutes]
      // router.options.routes = finalroutes
      // router.addRoutes(finalroutes)
      next()
    } catch (error) {
      console.log('出错了')
      removeToken()
      NProgress.done()
    }
  } else {
    const namemsg = JSON.parse(sessionStorage.getItem('name'))
    if (namemsg) {
      store.state.user.name = namemsg
    }
    next()
  }
})

router.afterEach(() => {
  // finish progress bar
  NProgress.done()
})
