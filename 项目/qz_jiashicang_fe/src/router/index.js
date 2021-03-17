import Vue from 'vue'
import Router from 'vue-router'
import core from '@/utils/core'

Vue.use(Router)

/* Layout */
// import Layout from '@/layout'

/**
 * Note: sub-menu only appear when route children.length >= 1
 * Detail see: https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 *
 * hidden: true                   如果设置为true，则不会在侧边栏中显示该项（默认为false）
 * alwaysShow: true               如果设置为true，将始终显示根菜单
 *                                如果未设置alwaysShow，则当项目有多个子路线时,
 *                                它将变成嵌套模式，否则不显示根菜单
 * redirect: noRedirect           如果set noRedirect将不会在breadcrumb中重定向
 * name:'router-name'             名称由<keep alive>使用（必须设置！！！）
 * meta : {
    roles: ['admin','editor']    控制页面角色（可以设置多个角色）
    title: 'title'               在边栏和面包屑中显示的名称（推荐集）
    icon: 'svg-name'/'el-icon-x' 图标显示在侧栏中
    breadcrumb: false            如果设置为false，则项目将隐藏在breadcrumb中（默认值为true）
    activeMenu: '/example/list'  如果设置路径，侧栏将突出显示您设置的路径
  }
 */
export const constantRoutes = [
  {
    path: '/',
    redirect: '/jump'
  },
  {
    path: '/jump',
    name: 'Jump',
    component: () => import('@/views/jump/jump'),
    meta: { title: '齐装网驾驶舱', icon: 'el-icon-folder-opened' }
  }
  // {
  //   path: '/pubdata',
  //   component: () => import('@/layout'),
  //   redirect: '/pubdata/citydata',
  //   name: 'Pubdata',
  //   alwaysShow: true,
  //   meta: { title: '公共数据分析', icon: 'el-icon-odometer' },
  //   children: [
  //     {
  //       path: 'citydata',
  //       name: 'Citydata',
  //       component: () => import('@/views/pubdata/citydata'),
  //       meta: { title: '城市数据报表', icon: 'el-icon-aim', noCache: true }
  //     },
  //     {
  //       path: 'splitorderdata',
  //       name: 'Splitorderdata',
  //       component: () => import('@/views/pubdata/splitorderdata'),
  //       meta: { title: '分单数据分析', icon: 'el-icon-aim', noCache: true }
  //     },
  //     {
  //       path: 'memberdata',
  //       name: 'Memberdata',
  //       component: () => import('@/views/pubdata/memberdata'),
  //       meta: { title: '会员数据分析', icon: 'el-icon-aim', noCache: true }
  //     }
  //   ]
  // }
]
export const asyncRoutes = []

const menulist = sessionStorage.getItem('menulist')
let list = []
if (menulist) {
  list = core.formatmenudata(JSON.parse(menulist))
}

const createRouter = () =>
  new Router({
    mode: 'history', // require service support
    scrollBehavior: () => ({ y: 0 }),
    routes: [...constantRoutes, ...list]
  })

const router = createRouter()
// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter()
  router.matcher = newRouter.matcher // reset router
}
router.selfaddRoutes = (params) => {
  router.matcher = new Router().matcher
  router.addRoutes(params)
}
export default router
