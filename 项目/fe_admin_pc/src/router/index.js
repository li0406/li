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
      meta: { title: '首页', icon: 'el-icon-s-home' }
    }]
  },
  {
    path: '/wares',
    component: Layout,
    redirect: '/wares/goods-manage',
    alwaysShow: true, // will always show the root menu
    name: 'wares',
    meta: { title: '商品管理', icon: 'el-icon-s-goods', noCache: true },
    children: [
      {
        path: 'goods-manage',
        component: () => import('@/views/wares/goods/goods-manage'),
        name: 'goods-manage',
        meta: { title: '商品管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'goods-addedit',
        component: () => import('@/views/wares/goods/goods-addedit'),
        name: 'goods-addedit',
        meta: { title: '商品添加/编辑', activeMenu: '/wares/goods-manage', icon: '', noCache: true }
      },
      {
        path: 'classify-manage',
        component: () => import('@/views/wares/classify/classify-manage'),
        name: 'classify-manage',
        meta: { title: '商品分类管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'classify-addedit',
        component: () => import('@/views/wares/classify/classify-addedit'),
        name: 'classify-addedit',
        meta: { title: '商品分类添加/编辑', activeMenu: '/wares/classify-manage', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/order',
    component: Layout,
    redirect: '/order/order-manage',
    alwaysShow: true, // will always show the root menu
    name: 'order',
    meta: { title: '订单管理', icon: 'el-icon-s-order', noCache: true },
    children: [
      {
        path: 'order-manage',
        component: () => import('@/views/order/order-manage'),
        name: 'order-manage',
        meta: { title: '订单管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'order-info',
        component: () => import('@/views/order/order-info'),
        name: 'order-info',
        meta: { title: '订单详情', activeMenu: '/order/order-manage', noCache: true }
      },
      {
        path: 'order-return-handling',
        component: () => import('@/views/order/order-return-handling'),
        name: 'order-handling',
        meta: { title: '退换货管理', noCache: true }
      }
    ]
  },
  {
    path: '/finance',
    component: Layout,
    redirect: '/finance/finance-manage',
    alwaysShow: true, // will always show the root menu
    name: 'finance',
    meta: { title: '财务管理', icon: 'el-icon-s-finance', noCache: true },
    children: [
      {
        path: 'finance-manage',
        component: () => import('@/views/finance/finance-manage'),
        name: 'finance-manage',
        meta: { title: '财务管理', icon: 'dashboard', noCache: true }
      },
      {
        hidden: true,
        path: 'finance-info',
        component: () => import('@/views/finance/finance-info'),
        name: 'finance-info',
        meta: { title: '账单明细', activeMenu: '/finance/finance-manage', icon: 'dashboard', noCache: true }
      },
      {
        path: 'payment-detailed',
        component: () => import('@/views/finance/payment-detailed'),
        name: 'payment-detailed',
        meta: { title: '支付明细', icon: 'dashboard', noCache: true }
      },
      {
        path: 'finance-refund',
        component: () => import('@/views/finance/finance-refund'),
        name: 'finance-refund',
        meta: { title: '退款明细', icon: 'dashboard', noCache: true }
      },
      {
        path: 'withdrawal',
        component: () => import('@/views/finance/withdrawal'),
        name: 'withdrawal',
        meta: { title: '提现申请', icon: 'dashboard', noCache: true }
      }
    ]
  },
  {
    path: '/business',
    component: Layout,
    redirect: '/business/business-manage',
    alwaysShow: true, // will always show the root menu
    name: 'business',
    meta: { title: '商家管理', icon: 'el-icon-s-shop', noCache: true },
    children: [
      {
        path: 'business-manage',
        component: () => import('@/views/business/review/business-manage'),
        name: 'business-manage',
        meta: { title: '商家管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'business-review',
        component: () => import('@/views/business/review/business-review'),
        name: 'business-review',
        meta: { title: '商家审核', activeMenu: '/business/business-manage', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/label',
    component: Layout,
    redirect: '/label/user-label',
    alwaysShow: true,
    name: 'label',
    meta: { title: '标签库管理', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'user-label',
        component: () => import('@/views/label/user-label'),
        name: 'user-label',
        meta: { title: '用户标签管理', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/relationship',
    component: Layout,
    redirect: '/relationship/social',
    alwaysShow: true,
    name: 'relationship',
    meta: { title: '关系管理', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'social',
        component: () => import('@/views/relationship/social/social'),
        name: 'social',
        meta: { title: '社交管理', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/user',
    component: Layout,
    redirect: '/user/user-list',
    alwaysShow: true,
    name: 'user',
    meta: { title: '用户管理', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'user-list',
        component: () => import('@/views/user/user-list'),
        name: 'user-list',
        meta: { title: '用户列表', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'user-info',
        component: () => import('@/views/user/user-info'),
        name: 'user-info',
        meta: { title: '用户详情', activeMenu: '/user/user-list', icon: '', noCache: true }
      },
      {
        path: 'designer-review',
        component: () => import('@/views/user/designer-review'),
        name: 'designer-review',
        meta: { title: '设计师审核', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/content',
    component: Layout,
    redirect: '/content/note-manage',
    alwaysShow: true, // will always show the root menu
    name: 'content',
    meta: { title: '内容管理', icon: 'el-icon-s-shop', noCache: true },
    children: [
      {
        path: 'note-manage',
        component: () => import('@/views/content/note/note'),
        name: 'note-manage',
        meta: { title: '笔记管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'add-note',
        component: () => import('@/views/content/note/add-note'),
        name: 'add-note',
        meta: { title: '新建/编辑笔记', activeMenu: '/content/note-manage', icon: '', noCache: true }
      },
      {
        path: 'feed-manage',
        component: () => import('@/views/content/feed/feed'),
        name: 'feed-manage',
        meta: { title: 'FEED流管理', icon: '', noCache: true }
      },
      {
        path: 'time-manage',
        component: () => import('@/views/content/timeLine/timeLine'),
        name: 'time-manage',
        meta: { title: '时间轴管理', icon: '', noCache: true }
      },
      {
        path: 'word-manage',
        component: () => import('@/views/content/recommendWord/recommendWord'),
        name: 'word-manage',
        meta: { title: '推荐语管理', icon: '', noCache: true }
      },
      {
        path: '/photo',
        component: {
          render: c => c('router-view')
        },
        redirect: '/content/photo-manage',
        alwaysShow: true, // will always show the root menu
        name: 'photo',
        meta: { title: '相册管理', icon: '', noCache: true },
        children: [
          {
            path: 'image-manage',
            component: () => import('@/views/content/photo/image'),
            name: 'image-manage',
            meta: { title: '图片管理', icon: '', noCache: true }
          },
          {
            path: 'video-manage',
            component: () => import('@/views/content/photo/video'),
            name: 'video-manage',
            meta: { title: '视频管理', icon: '', noCache: true }
          }
        ]
      },
      {
        path: '/ask',
        component: {
          render: c => c('router-view')
        },
        redirect: '/content/ask-manage',
        alwaysShow: true, // will always show the root menu
        name: 'ask',
        meta: { title: '问答管理', icon: '', noCache: true },
        children: [
          {
            path: 'sort-manage',
            component: () => import('@/views/content/ask/sort'),
            name: 'sort-manage',
            meta: { title: '分类管理', icon: '', noCache: true }
          },
          {
            path: 'question-manage',
            component: () => import('@/views/content/ask/question'),
            name: 'question-manage',
            meta: { title: '问题管理', icon: '', noCache: true }
          },
          {
            path: 'answer-manage',
            component: () => import('@/views/content/ask/answer'),
            name: 'answer-manage',
            meta: { title: '答案管理', icon: '', noCache: true }
          }
        ]
      },
      {
        path: 'violate-manage',
        component: () => import('@/views/content/violate/violate'),
        name: 'violate-manage',
        meta: { title: '违规管理', icon: '', noCache: true }
      },
      {
        path: 'comment-manage',
        component: () => import('@/views/content/comment/comment'),
        name: 'comment-manage',
        meta: { title: '评论管理', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/system',
    component: Layout,
    alwaysShow: true,
    name: 'system',
    redirect: '/system/authority/authority-manage',
    meta: { title: '系统设置', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'message',
        component: {
          render: c => c('router-view')
        },
        name: 'message',
        redirect: '/system/message/message-manage',
        meta: { title: '消息管理', icon: '', noCache: true },
        children: [
          {
            path: 'message-manage',
            component: () => import('@/views/system/message/sms'),
            name: 'message-manage',
            meta: { title: '短信消息', icon: '', noCache: true }
          },
          {
            path: 'message-mail',
            component: () => import('@/views/system/message/mail'),
            name: 'message-mail',
            meta: { title: '站内信消息', icon: '', noCache: true }
          }
        ]
      },
      {
        path: 'authority',
        component: {
          render: c => c('router-view')
        },
        alwaysShow: true,
        name: 'authority',
        redirect: '/system/authority/authority-manage',
        meta: { title: '权限管理', icon: '', noCache: true },
        children: [
          {
            path: 'member',
            component: () => import('@/views/system/authority/member'),
            name: 'member',
            meta: { title: '成员管理', icon: '', noCache: true }
          },
          {
            hidden: true,
            path: 'member-add',
            component: () => import('@/views/system/authority/member-add'),
            name: 'member-add',
            meta: { title: '添加成员', activeMenu: '/system/authority', icon: '', noCache: true }
          },
          {
            hidden: true,
            path: 'authority-manage',
            component: () => import('@/views/system/authority/authority'),
            name: 'authority-manage',
            meta: { title: '权限管理', icon: '', noCache: true }
          }
        ]
      }
    ]
  },
  {
    path: '/advertising',
    component: Layout,
    redirect: '/advertising/advertising-manage',
    alwaysShow: true,
    name: 'advertising',
    meta: { title: '广告管理', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'advertising-manage',
        component: () => import('@/views/advertising/advertising'),
        name: 'advertising-manage',
        meta: { title: '广告管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'advertising-add',
        component: () => import('@/views/advertising/advertising-add'),
        name: 'advertising-add',
        meta: { title: '添加广告', activeMenu: '/advertising/advertising-add', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/bdorder',
    component: Layout,
    redirect: '/bdorder/bdorder-manage',
    alwaysShow: true,
    name: 'bdorder',
    meta: { title: '百度接单后台', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'bdorder-manage',
        component: () => import('@/views/bdorder/bdorder-manage'),
        name: 'bdorder-manage',
        meta: { title: '百度接单后台', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'bdorder-info',
        component: () => import('@/views/bdorder/bdorder-info'),
        name: 'bdorder-info',
        meta: { title: '百度接单后台详情', activeMenu: '/bdorder/bdorder-manage', icon: '', noCache: true }
      }
    ]
  },
  {
    path: '/curriculum',
    component: Layout,
    redirect: '/curriculum/study',
    alwaysShow: true,
    name: 'curriculum',
    meta: { title: '百度课程管理', icon: 'el-icon-s-platform', noCache: true },
    children: [
      {
        path: 'curriculum-type',
        component: () => import('@/views/curriculum/curriculum-type'),
        name: 'curriculum-type',
        meta: { title: '课程分类管理', icon: '', noCache: true }
      },
      {
        path: 'administer',
        component: () => import('@/views/curriculum/administer'),
        name: 'administer',
        meta: { title: '课程管理', icon: '', noCache: true }
      },
      {
        hidden: true,
        path: 'administer-add',
        component: () => import('@/views/curriculum/administer-add'),
        name: 'administer-add',
        meta: { title: '课程管理添加/编辑', icon: '', noCache: true }
      },
      {
        path: 'examination',
        component: () => import('@/views/curriculum/examination'),
        name: 'examination',
        meta: { title: '考试库', icon: '', noCache: true }
      },
      {
        path: 'study',
        component: () => import('@/views/curriculum/study'),
        name: 'study',
        meta: { title: '学习库', icon: '', noCache: true }
      }
    ]
  }
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
