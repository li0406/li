import router from './router'
import store from './store'
// import { Message } from 'element-ui'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import { getToken, setToken } from '@/utils/auth' // get token from cookie
import domain from '@/config/config'
import common from '@/api/common'

NProgress.configure({ showSpinner: false }) // NProgress Configuration

router.beforeEach(async(to, from, next) => {
  // 开始进度条
  NProgress.start()

  // 设置页面标题
  document.title = to.meta.title
  if (to.path === '/jump' && to.query.token) {
    sessionStorage.clear()
    setToken(to.query.token)
    const accessRoutes = await store.dispatch('permission/generateRoutes')
    router.addRoutes(accessRoutes)
    const info = await common.info()
    if (info.error_code === 0) {
      sessionStorage.setItem('userinfo', JSON.stringify(info.data))
    }
  }
  if (!getToken()) {
    window.location.href = domain.ENTRANCE_URL
  }
  await store.dispatch('permission/generateRoutes')
  // router.addRoutes(accessRoutes)
  // router.selfaddRoutes(accessRoutes)
  next()
  NProgress.done()
})

router.afterEach(() => {
  // 完成进度条
  NProgress.done()
})
