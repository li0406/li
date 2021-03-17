import Vue from 'vue';
import VueRouter from 'vue-router';
import Layout from '@/layout/index.vue';

Vue.use(VueRouter);
// ⚡(商家首页)
// -首页
const home = [
  {
    path: 'home',
    name: 'Home',
    component: () => import('@/views/home/index.vue'),
  },
  {
    path: 'recharge',
    name: 'Recharge',
    component: () => import('@/views/home/recharge.vue'),
  },
];
// ⚡(订单管理)
// -全部订单
const orderAll = [
  {
    path: 'order-all',
    name: 'OrderAll',
    component: () => import('@/views/order-all/index.vue')
  },
  {
    path: 'order-detail',
    name: 'OrderDetail',
    component: () => import('@/views/order-all/order-detail.vue'),
  },
  {
    path: 'order-password-update',
    name: 'OrderPasswordUpdate',
    component: () => import('@/views/order-all/order-password-update.vue'),
  },
];
const orderList = [
  {
    path: 'order-listv1.0',
    name: 'orderListv1.0',
    component: () => import('@/views/order-all/index-v1.0.vue')
  },
  {
    path: 'order-detail',
    name: 'OrderDetail',
    component: () => import('@/views/order-all/order-detail.vue'),
  },
  {
    path: 'order-password-update',
    name: 'OrderPasswordUpdate',
    component: () => import('@/views/order-all/order-password-update.vue'),
  },
];
// -申请补轮
const orderRound = [
  {
    path: 'order-round',
    name: 'OrderRound',
    component: () => import('@/views/order-round/index.vue'),
  },
];
// -微信接受订单
const orderWechat = [
  {
    path: 'order-wechat',
    name: 'OrderWechat',
    component: () => import('@/views/order-wechat/index.vue'),
  },
];
// -装修现场
const orderSite = [
  {
    path: 'order-site',
    name: 'OrderSite',
    component: () => import('@/views/order-site/index.vue'),
  },
  {
    path: 'site-detail/:id',
    name: 'SiteDetail',
    component: () => import('@/views/order-site/detail.vue'),
  },
];
// -抢单池
const orderPond = [
  {
    path: 'order-pond',
    name: 'OrderPond',
    component: () => import('@/views/order-pond/index.vue'),
  },
];

// ⚡(网店管理)
// -装修案例
const decoration = [
  {
    path: 'decoration-case/:tab',
    name: 'DecorationCase',
    component: () => import('@/views/decoration-case/index.vue'),
  },
  {
    path: 'add-case/:id',
    name: 'AddCase',
    component: () => import('@/views/decoration-case/add-case.vue'),
  },
  {
    path: 'add-effect',
    name: 'AddEffect',
    component: () => import('@/views/decoration-case/add-effect.vue'),
  },
];
// ⚡(运营管理)
// -优惠活动
const promotions = [
  {
    path: 'promotions',
    name: 'Promotions',
    component: () => import('@/views/promotions/index.vue'),
  },
  {
    path: 'editEvent/:id',
    name: 'EventEdit',
    component: () => import('@/views/promotions/components/edit-event.vue'),
  },
  {
    path: 'coupon/edit/:id',
    name: 'EditCoupon',
    component: () => import('@/views/promotions/components/update-add-coupon.vue'),
  },
  {
    path: 'coupon/detail/:id',
    name: 'couponDetail',
    component: () => import('@/views/promotions/components/couponDetail.vue'),
  },
  {
    path: 'receive/:id',
    name: 'Receive',
    component: () => import('@/views/promotions/receive.vue'),
  },
  {
    path: 'coupon/lijuan',
    name: 'Lijuan',
    component: () => import('@/views/promotions/components/lijuan.vue'),
  },
  {
    path: 'coupon/ljdetail/:id',
    name: 'Ljdetail',
    component: () => import('@/views/promotions/components/ljDetail.vue'),
  },
  {
    path: 'coupon/klqdetail/:id',
    name: 'Klqdetail',
    component: () => import('@/views/promotions/components/klqDetail.vue'),
  },
];
// -评价管理
const notes = [
  {
    path: 'notes',
    name: 'Notes',
    component: () => import('@/views/notes/index.vue'),
  },
];
// ⚡(企业管理)
// -基本信息管理
const baseInfo = [
  {
    path: 'base-info',
    name: 'BaseInfo',
    component: () => import('@/views/base-info/index.vue'),
    meta: {
      title: '基本信息管理',
      icon: 'list',
      noCache: true,
    },
  },
];
// -店铺轮播图
const storeBanner = [
  {
    path: 'store-banner',
    name: 'StoreBanner',
    component: () => import('@/views/shop-banner/index.vue'),
  }
];
// -员工管理
const workersList = [
  {
    path: 'workers-list',
    name: 'WorkersList',
    component: () => import('@/views/workers-list/index.vue'),
    meta: {
      title: '员工管理',
      icon: 'list',
      noCache: true,
    },
  },
  {
    path: 'staff-index',
    name: 'WorkersList',
    component: () => import('@/views/workers-list/staffIndex.vue'),
    meta: {
      title: '添加员工',
      icon: 'list',
      noCache: true,
    },
    redirect: '/set-first', //  路由重定向
    children: [
      {
        path: '/set-first',
        name: 'WorkersList',
        component: () => import('@/views/workers-list/addStaff/setFirst.vue'),
        meta: {
          flag: 1, //  阶段样式标识
        },
      },
      {
        path: '/set-second',
        name: 'WorkersList',
        component: () => import('@/views/workers-list/addStaff/setSecond.vue'),
        meta: {
          flag: 2, //  阶段样式标识
        },
      },
      {
        path: '/set-third',
        name: 'WorkersList',
        component: () => import('@/views/workers-list/addStaff/setThird.vue'),
        meta: {
          flag: 3, //  阶段样式标识
        },
      },
      {
        path: '/set-fourth', // 不是设计师第四阶段
        name: 'WorkersList',
        component: () => import('@/views/workers-list/addStaff/setFourth.vue'),
        meta: {
          flag: 4, //  阶段样式标识
        },
      },
      {
        path: '/set-fourth-design', //  设计师第四阶段
        name: 'WorkersList',
        component: () => import('@/views/workers-list/addStaff/setFourthDesign.vue'),
        meta: {
          flag: 4, //  阶段样式标识
        },
      },
    ],
  },
  {
    path: 'designerSort',
    name: 'WorkersList',
    component: () => import('@/views/workers-list/designerSort/designerSort.vue'),
    meta: {
      title: '设计师排序',
      icon: 'list',
      noCache: true,
    },
  },
  {
    path: 'staff',
    name: 'Staff',
    component: () => import('@/views/staff/index.vue'),
    meta: {
      title: '员工管理',
      icon: 'list',
      noCache: true,
    },
  },
  {
    path: 'editMessage',
    name: 'EditMessage',
    component: () => import('@/views/staff/editMessage.vue'),
    meta: {
      title: '员工基本信息编辑',
      icon: 'list',
      noCache: true,
    },
  },
  {
    path: 'editLimits',
    name: 'EditLimits',
    component: () => import('@/views/staff/editLimits.vue'),
    meta: {
      title: '角色权限编辑',
      icon: 'list',
      noCache: true,
    },
  },
];
// ⚡(财务管理)
// 资金明细
const finance = [
  {
    path: 'finance',
    name: 'Finance',
    component: () => import('@/views/finance/index.vue'),
    meta: {
      title: '资金明细',
      icon: 'list',
      noCache: true,
    },
  },
];
// ⚡(账号管理)
// -消息中心
const messageCenter = [
  {
    path: 'message-center',
    name: 'MessageCenter',
    component: () => import('@/views/message-center/index.vue'),
    meta: {
      title: '消息中心',
      icon: 'list',
      noCache: true,
    },
  },
];
// -账户中心
const alter = [
  {
    path: 'alter',
    name: 'Alter',
    component: () => import('@/views/alter/index.vue'),
    meta: {
      title: '账号中心',
      icon: 'list',
      noCache: true,
    },
  },
];
// -个人中心
const personal = [
  {
    path: 'personal',
    name: 'Personal',
    component: () => import('@/views/alter/personal.vue'),
    meta: {
      title: '个人中心',
      icon: 'list',
      noCache: true,
    },
  },
  {
    path: 'editPersonal',
    name: 'EditPersonal',
    component: () => import('@/views/alter/editPersonal.vue'),
    meta: {
      title: '编辑',
      icon: 'list',
      noCache: true,
    },
  },
];
const wechatBinding = [
  {
    path: 'wechat-binding',
    name: 'wechatBinding',
    component: () => import('@/views/wechat-binding/index.vue'),
    meta: {
      title: '微信绑定',
      icon: 'list',
      noCache: true,
    },
  },
];
const setMenu = [
  {
    path: 'set-menu',
    name: 'setMenu',
    component: () => import('@/views/set-system/index.vue'),
    meta: {
      title: '订单设置',
      icon: 'list',
      noCache: true,
    },
  },
];
const selectionMall = [
  {
    path: 'selection-mall',
    name: 'selectionMall',
    component: () => import('@/views/selection-mall/index.vue'),
    meta: {
      title: '分销管理',
      icon: 'list',
      noCache: true,
    },
  },
];
// ⚡(404页面)
const NotFound = [
  {
    path: '*',
    component: () => import('@/views/NotFound.vue'),
  },
];

const ruleMapping = {
  home,
  'order-all': orderAll,
  'order-listv1.0':orderList,
  'order-wechat': orderWechat,
  'order-round': orderRound,
  'order-site': orderSite,
  'order-pond': orderPond,
  'decoration-case/case': decoration,
  promotions,
  notes,
  'base-info': baseInfo,
  'store-banner': storeBanner,
  'workers-list': workersList,
  'message-center': messageCenter,
  finance,
  alter,
  personal,
  'wechat-binding':wechatBinding,
  'set-menu':setMenu,
  'selection-mall':selectionMall,
  NotFound,
};

const routes = [
  {
    path: '/jump',
    name: 'Jump',
    component: () => import('@/views/jump.vue'),
  },
  {
    path: '/',
    component: Layout,
    redirect:'/home',
    children: [
      {
        path: 'home',
        name: 'Home',
        component: () => import('@/views/home/index.vue'),
      },
      {
        path: 'recharge',
        name: 'Recharge',
        component: () => import('@/views/home/recharge.vue'),
      }
    ],
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/login-system/login.vue'),
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/login-system/register.vue'),
  },
  {
    path: '/changepassword',
    name: 'changepassword',
    component: () => import('@/views/login-system/changepassword.vue'),
  },
];

const router = new VueRouter({
  mode: 'history',
  routes,
});

export function initDynamicRoutes(childList) {
  const currentRoutes = router.options.routes;
  if (childList) {
    childList.forEach((item) => {
      const itemRule = ruleMapping[item.link];
      if(itemRule !== undefined){
        itemRule[0].meta = {
          ...itemRule[0].meta,
          rights: item.rights,
        };
        currentRoutes[1].children.push(...itemRule);
      }
    });
    router.addRoutes(currentRoutes);
  }
}
export default router;
