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
    hidden: false,
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
    path: '',
    component: Layout,
    redirect: 'dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        hidden: false,
        name: 'Dashboard',
        meta: {title: 'dashboard', icon: 'dashboard', noCache: true, affix: true}
      }
    ]
  },
  {
    path: '/smsSetting',
    component: Layout,
    redirect: 'smsSetting',
    hidden: true,
    meta: {
      title: '短信管理中心',
      icon: 'example'
    },
    children: [
      {
        path: 'gateway',
        component: () => import('@/views/smsSetting/gateway'),
        name: '三方通道网关设置',
        hidden: true,
        meta: {title: '三方通道网关设置', icon: 'list', noCache: true}
      },
      {
        path: 'smsTpl',
        component: () => import('@/views/smsSend/smsTpl'),
        name: '模板管理',
        hidden: true,
        meta: {title: '模板管理', icon: 'list', noCache: true}
      },
      {
        path: 'createSmsTpl',
        component: () => import('@/views/smsSend/components/createSmsTpl'),
        name: '创建模板',
        hidden: true,
        meta: {title: '创建模板'}
      },
      {
        path: 'createSmsTpl/:id',
        component: () => import('@/views/smsSend/components/createSmsTpl'),
        name: '编辑模板',
        hidden: true,
        meta: {title: '编辑模板', noCache: true}
      },
      {
        path: 'signature',
        component: () => import('@/views/smsSend/signature'),
        name: '签名管理',
        hidden: true,
        meta: {title: '签名管理', icon: 'list', noCache: true}
      },
      {
        path: 'smsApply',
        component: () => import('@/views/smsSend/smsApply'),
        name: '在线发送',
        hidden: true,
        meta: {title: '在线发送', icon: 'list', noCache: true}
      },
      {
        path: 'sendList',
        component: () => import('@/views/statistics/sendList'),
        name: '发送记录',
        hidden: true,
        meta: {title: '发送记录', icon: 'list', noCache: true}
      }
    ]
  },
  // 新增消息类型侧边栏
  {
    path: '/messageManage',
    component: Layout,
    redirect: 'messageType',
    hidden: true,
    meta: {
      title: '消息管理中心',
      icon: 'list'
    },
    children: [
      {
        path: 'messageType',
        component: () => import('@/views/messageType/messageType'),
        name: '消息类型设置',
        hidden: true,
        meta: {title: '消息类型设置', icon: 'list', noCache: true}
      },
      {
        path: 'messageContent',
        component: () => import('@/views/messageContent/messageContent'),
        name: '消息内容管理',
        hidden: true,
        meta: {title: '消息内容管理', icon: 'list', noCache: true}
      },
      {
        path: 'addMessage',
        component: () => import('@/views/messageContent/addMessage'),
        name: '新增消息内容',
        hidden: true,
        meta: {title: '新增消息内容', icon: 'list', noCache: true}
      },
      {
        path: 'addMessage/:id',
        component: () => import('@/views/messageContent/addMessage'),
        name: '编辑消息内容',
        hidden: true,
        meta: {title: '编辑消息内容', icon: 'list', noCache: true}
      },
      {
        path: 'sendNews',
        component: () => import('@/views/messageContent/sendNews'),
        name: 'APP推送消息管理',
        hidden: true,
        meta: {title: 'APP推送消息管理', icon: 'list', noCache: true}
      },
      {
        path: 'createMsg',
        component: () => import('@/views/messageContent/createMsg'),
        name: '新增APP推送消息',
        hidden: true,
        meta: {title: '新增APP推送消息'}
      },
      {
        path: 'createMsg/:id',
        component: () => import('@/views/messageContent/createMsg'),
        name: '编辑APP推送消息',
        hidden: true,
        meta: {title: '编辑APP推送消息', noCache: true}
      },
      {
        path: 'createMsg/:id',
        component: () => import('@/views/messageContent/createMsg'),
        name: '复用APP推送消息',
        hidden: true,
        meta: {title: '复用APP推送消息容', noCache: true}
      },
      {
        path: 'pushRecord',
        component: () => import('@/views/smsApplys/pushRecord'),
        name: '消息推送记录',
        hidden: true,
        meta: {title: '消息推送记录', icon: 'list', noCache: true}
      },
      {
        path: 'onlineSend',
        component: () => import('@/views/onlineSend/onlineSend'),
        name: 'onlineSend',
        hidden: true,
        meta: { title: 'APP在线推送', icon: 'list' }
      },
      {
        path: 'onlineSendAdd',
        component: () => import('@/views/onlineSend/onlineSendAdd'),
        name: 'onlineSendAdd',
        hidden: true,
        meta: { title: '新增/编辑/查看推送内容', icon: 'list', noCache: true }
      }
    ],
  },
  {
    path: '/smsApply',
    component: Layout,
    redirect: 'applyList',
    hidden: true,
    meta: {
      title: '项目应用接入',
      icon: 'example'
    },
    children: [
      {
        path: 'applyList',
        component: () => import('@/views/smsApplys/applyList'),
        name: '项目应用接入',
        hidden: true,
        meta: {title: '项目应用接入', icon: 'list', noCache: true}
      }
    ]
  },
  {
    path: '/virtual',
    component: Layout,
    redirect: 'virtual',
    hidden: true,
    meta: {
      title: '虚拟号管理中心',
      icon: 'example'
    },
    children: [
      {
        path: 'supplier',
        component: () => import('@/views/virtual/supplier'),
        name: 'Supplier',
        hidden: true,
        meta: { title: '供应商设置', icon: 'list', noCache: true }
      },
      {
        path: 'call',
        component: () => import('@/views/virtual/call'),
        name: 'Call',
        hidden: true,
        meta: { title: '虚拟号设置', icon: 'list', noCache: true }
      }
    ]
  },
  {path: '*', redirect: '/404', hidden: true}
]

export default new Router({
  mode: 'history', // require service support
  scrollBehavior: () => ({y: 0}),
  routes: constantRoutes
})

export const asyncRoutes = []
