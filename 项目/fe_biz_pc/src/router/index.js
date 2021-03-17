import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/layout'

/**
 * Note: sub-menu only appear when route children.length >= 1
 * Detail see: https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 *
 * hidden: true                   if set true, item will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu
 *                                if not set alwaysShow, when item has more than one children route,
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noRedirect           if set noRedirect will no redirect in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    roles: ['admin','editor']    control the page roles (you can set multiple roles)
    title: 'title'               the name show in sidebar and breadcrumb (recommend set)
    icon: 'svg-name'/'el-icon-x' the icon show in the sidebar
    breadcrumb: false            if set false, the item will hidden in breadcrumb(default is true)
    activeMenu: '/example/list'  if set path, the sidebar will highlight the path you set
  }
 */

/**
 * constantRoutes
 * a base page that does not have permission requirements
 * all roles can be accessed
 */
export const constantRoutes = [
  {
    path: '/',
    redirect: '/jump',
    hidden: true
  },
  {
    path: '/jump',
    component: () => import('@/views/jump/jump'),
    hidden: true
  },
  {
    path: '/dashboard',
    component: Layout,
    redirect: '/dashboard/index',
    children: [{
      path: 'index',
      name: 'Index',
      component: () => import('@/views/dashboard/index'),
      meta: { title: '概览', icon: 'el-icon-s-home' }
    }]
  },
  {
    path: '/distribution-mall',
    component: Layout,
    redirect: '/distribution-mall/selection-mall',
    alwaysShow: true, // will always show the root menu
    name: 'DistributionMall',
    meta: { title: '分销商城', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'selection-mall',
        component: () => import('@/views/distribution-mall/selection-mall'),
        name: 'SelectionMall',
        meta: { title: '选品商城', icon: '', noCache: true }
      },
      {
        path: 'shopping-cart',
        component: () => import('@/views/distribution-mall/shopping-cart'),
        name: 'ShoppingCart',
        meta: { title: '购物车', icon: '', noCache: true }
      },
      {
        path: 'address-management',
        component: () => import('@/views/distribution-mall/address-management'),
        name: 'AddressManagement',
        meta: { title: '地址管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'submit-order',
        component: () => import('@/views/distribution-mall/submit-order'),
        name: 'SubmitOrder',
        meta: { title: '提交订单', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'cash-register',
        component: () => import('@/views/distribution-mall/cash-register'),
        name: 'CashRegister',
        meta: { title: '结算收银台', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/commodity-management',
    component: Layout,
    redirect: '/commodity-management/commodity-library',
    alwaysShow: true, // will always show the root menu
    name: 'CommodityManagement',
    meta: { title: '商品管理', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'commodity-library',
        component: () => import('@/views/commodity-management/commodity-library'),
        name: 'CommodityLibrary',
        meta: { title: '商品库', icon: '', noCache: true }
      },
      {
        path: 'goods-on-sale',
        component: () => import('@/views/commodity-management/goods-on-sale'),
        name: 'GoodsOnSale',
        meta: { title: '在售商品', icon: '', noCache: true }
      },
      {
        path: 'off-shelf-products',
        component: () => import('@/views/commodity-management/off-shelf-products'),
        name: 'OffShelfProducts',
        meta: { title: '已下架商品', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/order-management',
    component: Layout,
    redirect: '/order-management/user-order',
    alwaysShow: true, // will always show the root menu
    name: 'OrderManagement',
    meta: { title: '订单管理', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'user-order',
        component: () => import('@/views/order-management/user-order'),
        name: 'UserOrder',
        meta: { title: '用户订单', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/exchange-management',
    component: Layout,
    redirect: '/exchange-management/goods-purchased',
    alwaysShow: true, // will always show the root menu
    name: 'ExchangeManagement',
    meta: { title: '购物/兑换管理', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'goods-purchased',
        component: () => import('@/views/exchange-management/goods-purchased'),
        name: 'GoodsPurchased',
        meta: { title: '已购商品', icon: '', noCache: true }
      },
      {
        path: 'exchange-record',
        component: () => import('@/views/exchange-management/exchange-record'),
        name: 'ExchangeRecord',
        meta: { title: '兑换记录', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/transaction-management',
    component: Layout,
    redirect: '/transaction-management/transaction-records',
    alwaysShow: true, // will always show the root menu
    name: 'TransactionManagement',
    meta: { title: '交易管理', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'transaction-records',
        component: () => import('@/views/transaction-management/transaction-records'),
        name: 'TransactionRecords',
        meta: { title: '交易记录', icon: '', noCache: true }
      },
      {
        path: 'withdrawal-management',
        component: () => import('@/views/transaction-management/withdrawal-management'),
        name: 'WithdrawalManagement',
        meta: { title: '提现管理', icon: '', noCache: true }
      },
      {
        path: 'bank-card-management',
        component: () => import('@/views/transaction-management/bank-card-management'),
        name: 'BankCardManagement',
        meta: { title: '银行卡管理', icon: '', noCache: true }
      }
    ]
  }
  // {
  //   path: '/wares',
  //   component: Layout,
  //   redirect: '/wares/goods-manage',
  //   alwaysShow: true, // will always show the root menu
  //   name: 'wares',
  //   meta: { title: '商品管理', icon: 'el-icon-s-goods', noCache: true },
  //   children: [
  //     {
  //       path: 'goods-manage',
  //       component: () => import('@/views/wares/goods/goods-manage'),
  //       name: 'goods-manage',
  //       meta: { title: '商品管理', icon: '', noCache: true }
  //     },
  //     {
  //       hidden: true,
  //       path: 'goods-addedit',
  //       component: () => import('@/views/wares/goods/goods-addedit'),
  //       name: 'goods-addedit',
  //       meta: { title: '商品添加/编辑', activeMenu: '/wares/goods-manage', icon: '', noCache: true }
  //     },
  //     {
  //       path: 'classify-manage',
  //       component: () => import('@/views/wares/classify/classify-manage'),
  //       name: 'classify-manage',
  //       meta: { title: '商品分类管理', icon: '', noCache: true }
  //     },
  //     {
  //       hidden: true,
  //       path: 'classify-addedit',
  //       component: () => import('@/views/wares/classify/classify-addedit'),
  //       name: 'classify-addedit',
  //       meta: { title: '商品分类添加/编辑', activeMenu: '/wares/classify-manage', icon: '', noCache: true }
  //     }
  //   ]
  // },
  // {
  //   path: '/order',
  //   component: Layout,
  //   redirect: '/order/order-manage',
  //   alwaysShow: true, // will always show the root menu
  //   name: 'order',
  //   meta: { title: '订单管理', icon: 'el-icon-s-order', noCache: true },
  //   children: [
  //     {
  //       path: 'order-manage',
  //       component: () => import('@/views/order/order-manage'),
  //       name: 'order-manage',
  //       meta: { title: '订单管理', icon: '', noCache: true }
  //     },
  //     {
  //       hidden: true,
  //       path: 'order-info',
  //       component: () => import('@/views/order/order-info'),
  //       name: 'order-info',
  //       meta: { title: '订单信息', icon: '', noCache: true }
  //     },
  //     {
  //       hidden: true,
  //       path: 'order-return-andling',
  //       component: () => import('@/views/order/order-return-andling'),
  //       name: 'order-info',
  //       meta: { title: '退换货管理', activeMenu: '/order/order-manage', icon: '', noCache: true }
  //     }
  //   ]
  // },
  // {
  //   path: '/finance',
  //   component: Layout,
  //   redirect: '/finance/finance-manage',
  //   alwaysShow: true, // will always show the root menu
  //   name: 'finance',
  //   meta: { title: '财务管理', icon: 'el-icon-s-finance', noCache: true },
  //   children: [
  //     {
  //       path: 'finance-manage',
  //       component: () => import('@/views/finance/finance-manage'),
  //       name: 'finance-manage',
  //       meta: { title: '财务管理', icon: 'dashboard', noCache: true }
  //     }
  //   ]
  // },
  // {
  //   path: '/business',
  //   component: Layout,
  //   redirect: '/business/business-manage',
  //   alwaysShow: true, // will always show the root menu
  //   name: 'business',
  //   meta: { title: '商家管理', icon: 'el-icon-s-shop', noCache: true },
  //   children: [
  //     {
  //       path: 'business-manage',
  //       component: () => import('@/views/business/review/business-manage'),
  //       name: 'business-manage',
  //       meta: { title: '商家管理', icon: '', noCache: true }
  //     },
  //     {
  //       hidden: true,
  //       path: 'business-review',
  //       component: () => import('@/views/business/review/business-review'),
  //       name: 'business-review',
  //       meta: { title: '商家审核', activeMenu: '/business/business-manage', icon: '', noCache: true }
  //     }
  //   ]
  // },
  // {
  //   path: '/curriculum',
  //   component: Layout,
  //   redirect: '/curriculum/study',
  //   alwaysShow: true, // will always show the root menu
  //   name: 'curriculum',
  //   meta: { title: '百度课程管理', icon: 'el-icon-s-platform', noCache: true },
  //   children: [
  //     {
  //       path: 'study',
  //       component: () => import('@/views/curriculum/study'),
  //       name: 'study',
  //       meta: { title: '学习库', icon: '', noCache: true }
  //     },
  //     {
  //       path: 'examination',
  //       component: () => import('@/views/curriculum/examination'),
  //       name: 'examination',
  //       meta: { title: '考试库', icon: '', noCache: true }
  //     },
  //     {
  //       path: 'administer',
  //       component: () => import('@/views/curriculum/administer'),
  //       name: 'administer',
  //       meta: { title: '管理库', icon: '', noCache: true }
  //     }
  //   ]
  // }
]
export const asyncRoutes = [
]
const createRouter = () => new Router({
  mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRoutes
})

const router = createRouter()

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter()
  router.matcher = newRouter.matcher // reset router
}

export default router
