import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/views/layout/Layout'

/* Router Modules */

/** note: sub-menu only appear when children.length>=1
 *  detail see  https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 **/

/**
 * hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
 *                                if not set alwaysShow, only more than one route under the children
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    roles: ['admin','editor']    will control the page roles (you can set multiple roles)
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb(default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
 **/
export const constantRoutes = [
  {
    path: '/redirect',
    component: Layout,
    hidden: true,
    children: [
      {
        path: '/redirect/:path*',
        component: () => import('@/views/redirect/index')
      }
    ]
  },
  {
    path: '/login',
    component: () => import('@/views/login/index'),
    hidden: true
  },
  {
    path: '/auth-redirect',
    component: () => import('@/views/login/authredirect'),
    hidden: true
  },
  {
    path: '/404',
    component: () => import('@/views/errorPage/404'),
    hidden: true
  },
  {
    path: '/401',
    component: () => import('@/views/errorPage/401'),
    hidden: true
  },
  {
    path: '/mesList',
    component: Layout,
    redirect: 'message',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'message',
        component: () => import('@/views/mesList/message'),
        name: '消息系统中心',
        hidden: true,
        meta: { title: '消息系统中心', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '',
    component: Layout,
    redirect: 'dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        hidden: true,
        name: 'Dashboard',
        meta: { title: 'dashboard', icon: 'dashboard', noCache: true, affix: true }
      }
    ]
  },
  {
    path: '/memberOrders',
    component: Layout,
    redirect: 'statistics',
    hidden: true,
    children: [
      {
        path: 'statistics',
        component: () => import('@/views/memberOrders/statistics'),
        name: '会员签单统计',
        hidden: true,
        meta: { title: '会员签单统计', icon: 'list', noCache: true }
      },
      {
        path: 'nonMemberCity',
        component: () => import('@/views/cityAccount/nonMemberCity'),
        name: '无会员城市管理',
        hidden: true,
        meta: { title: '无会员城市管理', icon: 'list', noCache: true }
      },
      {
        path: 'nonMemberCityOrder',
        component: () => import('@/views/cityAccount/nonMemberCityOrder'),
        name: '无会员城市/城市订单管理',
        hidden: true,
        meta: { title: '无会员城市/城市订单管理', icon: 'list', noCache: true }
      },
      {
        path: 'orderExamine',
        component: () => import('@/views/memberOrders/orderExamine'),
        name: '咨询签单审核',
        hidden: true,
        meta: { title: '咨询签单审核', icon: 'list', noCache: true }
      }
    ]
  },
  {
    path: '/customer-mainten',
    component: Layout,
    redirect: 'list',
    hidden: true,
    meta: {
      title: '客户维护',
      icon: 'example'
    },
    children: [
      {
        path: 'list',
        component: () => import('@/views/customer-mainten/list'),
        name: '客户维护',
        hidden: true,
        meta: { title: '客户维护', icon: 'list' }
      },
      {
        path: 'report-again/:id',
        component: () => import('@/views/customer-mainten/report-again'),
        hidden: true,
        name: '再次报备',
        meta: { title: '再次报备', icon: 'edit' }
      },
      {
        path: 'add-log',
        component: () => import('@/views/customer-mainten/add-log'),
        name: '添加日志',
        hidden: true,
        meta: { title: '添加日志', icon: 'edit' }
      },
      {
        path: 'log-list/:id',
        component: () => import('@/views/customer-mainten/log-list'),
        name: '日志记录',
        hidden: true,
        meta: { title: '日志记录', icon: 'edit' }
      }
    ]
  },
  {
    path: '/workReport',
    component: Layout,
    redirect: 'reportList',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'reportList',
        component: () => import('@/views/workReport/reportList'),
        name: '工作汇报',
        hidden: true,
        meta: { title: '工作汇报', icon: 'icon', noCache: true }
      },
      {
        path: 'add-work',
        component: () => import('@/views/workReport/addWork'),
        hidden: true,
        name: '新增工作',
        meta: { title: '新增工作', icon: 'icon', noCache: true }
      },
      {
        path: 'work-detail/:id',
        component: () => import('@/views/workReport/workDetail'),
        hidden: true,
        name: '查看工作',
        meta: { title: '查看工作', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/cityAccount',
    component: Layout,
    redirect: 'citys',
    hidden: true,
    children: [
      {
        path: 'citys',
        component: () => import('@/views/cityAccount/citys'),
        name: '城市管辖',
        hidden: true,
        meta: { title: '城市管辖', icon: 'list', noCache: true }
      },
      {
        path: 'editCitys/:id',
        component: () => import('@/views/cityAccount/editCitys'),
        name: '编辑城市管辖',
        hidden: true,
        meta: { title: '编辑城市管辖', icon: 'list', noCache: true }
      }
    ]
  },
  {
    path: '/allOrder',
    component: Layout,
    redirect: 'orderList',
    hidden: true,
    meta: {
      title: '统计管理',
      icon: 'example'
    },
    children: [
      {
        path: 'orderList',
        component: () => import('@/views/orderManager/orderList'),
        name: '订单管理',
        hidden: true,
        meta: { title: '订单管理', icon: 'list', noCache: true }
      },
      {
        path: 'voipRecord',
        component: () => import('@/views/orderManager/voipRecord'),
        name: '呼叫记录',
        hidden: true,
        meta: { title: '呼叫记录', icon: 'icon', noCache: true }
      },
      {
        path: 'wxSendlog',
        component: () => import('@/views/orderManager/wxSendlog'),
        name: '微信通知发送记录',
        hidden: true,
        meta: { title: '微信通知发送记录', icon: 'icon', noCache: true }
      },
      {
        path: 'cityOrder',
        component: () => import('@/views/orderManager/cityOrder'),
        name: '城市签单汇总统计',
        hidden: true,
        meta: { title: '城市签单汇总统计', icon: 'icon', noCache: true }
      },
      {
        path: 'decorate',
        component: () => import('@/views/orderManager/decorate'),
        name: '装修现场管理',
        hidden: true,
        meta: { title: '装修现场管理', icon: 'icon', noCache: true }
      },
      {
        path: 'decorateDetails/:id',
        component: () => import('@/views/orderManager/decorateDetails'),
        name: '装修现场详情',
        hidden: true,
        meta: { title: '装修现场详情', icon: 'icon', noCache: true }
      },
      {
        path: 'fenOrder',
        component: () => import('@/views/orderManager/fenOrder'),
        name: '城市分单统计',
        hidden: true,
        meta: { title: '城市分单统计', icon: 'icon', noCache: true }
      },
      {
        path: 'orderSearch',
        component: () => import('@/views/order/orderSearch'),
        name: '城市订单统计',
        hidden: true,
        meta: { title: '城市订单统计', icon: 'icon', noCache: true }
      },
      {
        path: 'failureStatistics',
        component: () => import('@/views/orderManager/failureStatistics'),
        name: 'failureStatistics',
        hidden: true,
        meta: { title: '报备不通过统计', icon: 'icon', noCache: true }
      },
      {
        path: 'cityvipcount',
        component: () => import('@/views/cityvipcount/cityvipcount'),
        name: 'cityvipcount',
        hidden: true,
        meta: { title: '城市会员统计', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/statusOrders',
    component: Layout,
    redirect: 'fendan',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'fendan',
        component: () => import('@/views/statusOrders/fendan'),
        name: '会员分单统计',
        hidden: true,
        meta: { title: '会员分单统计', icon: 'icon', noCache: true }
      },
      {
        path: 'detail',
        component: () => import('@/views/statusOrders/detail'),
        hidden: true,
        name: '会员分单明细',
        meta: { title: '会员分单明细', icon: 'icon', noCache: true }
      },
      {
        path: 'detail/:order',
        component: () => import('@/views/statusOrders/detail'),
        hidden: true,
        name: '会员分单明细',
        meta: { title: '会员分单明细', icon: 'icon', noCache: true }
      },
      {
        path: 'noread',
        component: () => import('@/views/statusOrders/noread'),
        hidden: true,
        name: '未读订单统计',
        meta: { title: '未读订单统计', icon: 'icon', noCache: true }
      },
      {
        path: 'orderTime',
        component: () => import('@/views/statusOrders/ordertime'),
        hidden: true,
        name: '订单查看时间',
        meta: { title: '订单查看时间', icon: 'icon', onCache: true }
      }
    ]
  },
  {
    path: '/basicInfo',
    component: Layout,
    redirect: 'orderList',
    hidden: true,
    meta: {
      title: '基础信息设置',
      icon: 'example'
    },
    children: [
      {
        path: 'memberCompany',
        component: () => import('@/views/setting/memberCompany'),
        name: '会员公司设置',
        hidden: true,
        meta: { title: '会员公司设置', icon: 'icon', noCache: true }
      },
      {
        path: 'city',
        component: () => import('@/views/setting/city'),
        name: '城市设置',
        hidden: true,
        meta: { title: '城市设置', icon: 'list', noCache: true }
      },
      {
        path: 'memberIdentify',
        component: () => import('@/views/setting/memberIdentify'),
        name: '会员公司标识',
        hidden: true,
        meta: { title: '会员公司标识', icon: 'list', noCache: true }
      },
      {
        path: 'wwgadminRegister',
        component: () => import('@/views/setting/wwgadminRegister'),
        name: '账号直通车',
        hidden: true,
        meta: { title: '账号直通车', icon: 'list', noCache: true }
      },
      {
        path: 'map',
        component: () => import('@/views/map/map'),
        name: 'map',
        hidden: true,
        meta: { title: '地图', icon: 'list', noCache: true }
      }
    ]
  },
  {
    path: '/linceseManager',
    component: Layout,
    redirect: 'orderList',
    hidden: true,
    meta: {
      title: '营业执照',
      icon: 'example'
    },
    children: [
      {
        path: 'licenseCheck',
        component: () => import('@/views/licenseManager/licenseCheck'),
        name: '营业执照审核',
        hidden: true,
        meta: { title: '营业执照审核', icon: 'list', noCache: true }
      },
      {
        path: 'licenseStatistic',
        component: () => import('@/views/licenseManager/licenseStatistic'),
        name: '营业执照数据统计',
        hidden: true,
        meta: { title: '营业执照数据统计', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/teamManager',
    component: Layout,
    redirect: 'orderList',
    hidden: true,
    meta: {
      title: '团队管理',
      icon: 'example'
    },
    children: [
      {
        path: 'teamList',
        component: () => import('@/views/teamManager/teamList'),
        name: '团队管理',
        hidden: true,
        meta: { title: '团队管理', icon: 'list', noCache: true }
      },
      {
        path: 'memberList',
        component: () => import('@/views/teamManager/memberList'),
        name: '成员管理',
        hidden: true,
        meta: { title: '成员管理', icon: 'icon', noCache: true }
      },
      {
        path: 'kpiList',
        component: () => import('@/views/teamManager/kpiList'),
        name: '业绩指标',
        hidden: true,
        meta: { title: '业绩指标', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/repairOrderManager',
    component: Layout,
    redirect: 'repairOrder',
    hidden: true,
    meta: {
      title: '补单管理',
      icon: 'example'
    },
    children: [
      {
        path: 'repairOrder',
        component: () => import('@/views/repairOrderManager/repairOrder'),
        name: '补单管理',
        hidden: true,
        meta: { title: '补单管理', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/memberManager',
    component: Layout,
    redirect: 'memberDeadline',
    hidden: true,
    meta: {
      title: '会员管理',
      icon: 'example'
    },
    children: [
      {
        path: 'memberDeadline',
        component: () => import('@/views/memberManager/memberDeadline'),
        name: '会员到期提醒',
        hidden: true,
        meta: { title: '会员到期提醒', icon: 'list', noCache: true }
      }
    ]
  },
  {
    path: '/performanceStatistics',
    component: Layout,
    redirect: 'performanceStatistics',
    hidden: true,
    meta: {
      title: '业绩统计',
      icon: 'example'
    },
    children: [
      {
        path: 'allStatistics',
        component: () => import('@/views/performanceStatistics/allStatistics'),
        name: '数据总览',
        hidden: true,
        meta: { title: '数据总览', icon: 'icon', noCache: true }
      },
      {
        path: 'teamStatistics',
        component: () => import('@/views/performanceStatistics/teamStatistics'),
        name: '团队业绩统计',
        hidden: true,
        meta: { title: '团队业绩统计', icon: 'icon', noCache: true }
      },
      {
        path: 'memberStatistics',
        component: () => import('@/views/performanceStatistics/memberStatistics'),
        name: '个人业绩统计',
        hidden: true,
        meta: { title: '个人业绩统计', icon: 'icon', noCache: true }
      },
      {
        path: 'performanceDetails',
        component: () => import('@/views/performanceStatistics/performanceDetails'),
        name: '业绩明细统计',
        hidden: true,
        meta: { title: '业绩明细统计', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/auxiliary',
    component: Layout,
    redirect: 'auxiliary',
    hidden: true,
    meta: {
      title: '辅助功能',
      icon: 'list'
    },
    children: [
      {
        path: 'examine',
        component: () => import('@/views/orderExamine/orderExamine'),
        name: 'examine',
        hidden: true,
        meta: { title: '签单审核管理', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/miniMenu',
    component: Layout,
    redirect: 'editMenu',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'addMenu',
        component: () => import('@/views/miniMenu/addMenu'),
        name: '菜单新增/编辑',
        hidden: true,
        meta: { title: '菜单新增/编辑', icon: 'icon', noCache: true }
      },
      {
        path: 'editMenu',
        component: () => import('@/views/miniMenu/editMenu'),
        hidden: true,
        name: '小程序菜单配置',
        meta: { title: '小程序菜单配置', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/teamManager',
    component: Layout,
    redirect: 'list',
    hidden: true,
    meta: {
      icon: 'list'
    },
    children: [
      {
        path: 'teamList',
        component: () => import('@/views/teamManager/teamList'),
        name: '团队管理',
        hidden: true,
        meta: { title: '团队管理', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/memberReport',
    component: Layout,
    redirect: 'list',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'list',
        component: () => import('@/views/memberReport/reportList'),
        name: '会员报备',
        hidden: true,
        meta: { title: '会员报备', icon: 'icon', noCache: true }
      },
      {
        path: 'add',
        component: () => import('@/views/memberReport/reportAdd'),
        name: '会员报备-添加',
        hidden: true,
        meta: { title: '会员报备-添加', icon: 'icon', noCache: true }
      },
      {
        path: 'detail',
        component: () => import('@/views/memberReport/reportDetail'),
        name: '会员报备-详情',
        hidden: true,
        meta: { title: '会员报备-详情', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/invoiceReport',
    component: Layout,
    redirect: '/invoiceReport/reportList',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'reportList',
        component: () => import('@/views/report/reportList'),
        name: '发票报备',
        hidden: true,
        meta: { title: '发票报备', icon: 'icon', noCache: true }
      },
      {
        path: 'reportAddorEdit',
        component: () => import('@/views/report/reportAddorEdit'),
        name: '发票报备-添加/编辑',
        hidden: true,
        meta: { title: '发票报备-添加/编辑', icon: 'icon', noCache: true }
      },
      {
        path: 'reportDetail',
        component: () => import('@/views/report/reportDetail'),
        name: '发票报备-详情',
        hidden: true,
        meta: { title: '发票报备-详情', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/smallReport',
    component: Layout,
    redirect: 'list',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'list',
        component: () => import('@/views/smallReport/index'),
        name: '小报备',
        hidden: true,
        meta: { title: '小报备', icon: 'icon', noCache: true }
      },
      {
        path: 'add',
        component: () => import('@/views/smallReport/reportAdd'),
        name: '小报备-添加',
        hidden: true,
        meta: { title: '小报备-添加', icon: 'icon', noCache: true }
      },
      {
        path: 'detail',
        component: () => import('@/views/smallReport/reportDetail'),
        name: '小报备-详情',
        hidden: true,
        meta: { title: '小报备-详情', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/offerManager',
    component: Layout,
    redirect: 'offerList',
    hidden: true,
    meta: {
      icon: 'example'
    },
    children: [
      {
        path: 'offerList',
        component: () => import('@/views/offerManager/offerList'),
        name: '报价管理',
        hidden: true,
        meta: { title: '报价管理', icon: 'icon', noCache: true }
      },
      {
        path: 'cityOfferRecordList',
        component: () => import('@/views/offerManager/cityOfferRecordList'),
        name: '城市报价记录',
        hidden: true,
        meta: { title: '城市报价记录', icon: 'icon', noCache: true }
      },
      {
        path: 'cityOfferAdd',
        component: () => import('@/views/offerManager/cityOfferAdd'),
        name: '城市报价-添加',
        hidden: true,
        meta: { title: '城市报价-添加', icon: 'icon', noCache: true }
      },
      {
        path: 'sanWeiJiaRecordList',
        component: () => import('@/views/offerManager/swjOfferRecordList'),
        name: '三维家报价记录',
        hidden: true,
        meta: { title: '三维家报价记录', icon: 'icon', noCache: true }
      },
      {
        path: 'sanWeiJiaOfferAdd',
        component: () => import('@/views/offerManager/sanWeiJiaOfferAdd'),
        name: '三维家报价-添加',
        hidden: true,
        meta: { title: '三维家报价-添加', icon: 'icon', noCache: true }
      }
    ]
  },
  {
    path: '/orderManage',
    component: Layout,
    redirect: 'orderManager',
    hidden: true,
    meta: {
      title: '订单管理',
      icon: 'example'
    },
    children: [
      {
        path: 'allOrders',
        component: () => import('@/views/order/orderManager'),
        name: '所有订单',
        hidden: true,
        meta: { title: '所有订单', icon: 'icon', noCache: true }
      },
      {
        path: 'voipRecord',
        component: () => import('@/views/orderManager/voipRecord'),
        name: '呼叫记录',
        hidden: true,
        meta: { title: '呼叫记录', icon: 'icon', noCache: true }
      },
      {
        path: 'returnVisit',
        component: () => import('@/views/order/returnVisit'),
        name: '回访订单',
        hidden: true,
        meta: { title: '回访订单', icon: 'icon', noCache: true }
      }
    ]
  },

  { path: '*', redirect: '/404', hidden: true }
]

export default new Router({
  mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRoutes
})

export const asyncRoutes = []
