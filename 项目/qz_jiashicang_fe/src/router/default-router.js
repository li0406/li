const DEFAULT_ROUTER = [
  {
    path: '/',
    redirect: '/jump'
  },
  {
    path: '/jump',
    component: () => import('@/views/jump/jump'),
    meta: { title: '齐装网驾驶舱', icon: 'el-icon-folder-opened' }
  },
  {
    path: '/whole',
    component: () => import('@/layout'),
    redirect: '/whole/dashboard',
    name: 'Whole',
    alwaysShow: true,
    meta: { title: '管理驾驶舱', icon: 'el-icon-folder-opened' },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/whole/dashboard'),
        meta: { title: '综合运营驾驶舱', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'sale',
        name: 'Sale',
        component: () => import('@/views/whole/sale'),
        meta: { title: '销售中心驾驶舱', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'longrange',
        name: 'Longrange',
        component: () => import('@/views/whole/longrange'),
        meta: { title: '远程中心驾驶舱', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'market',
        name: 'Market',
        component: () => import('@/views/whole/market'),
        meta: { title: '市场中心驾驶舱', icon: 'el-icon-aim', noCache: true }
      }
    ]
  },

  {
    path: '/sales',
    component: () => import('@/layout'),
    redirect: '/sales/achievement',
    name: 'Sales',
    alwaysShow: true,
    meta: { title: '销售中心表格分析', icon: 'el-icon-data-analysis' },
    children: [
      {
        path: 'achievement',
        name: 'Achievement',
        component: () => import('@/views/sales/achievement'),
        meta: { title: '业绩分析报表', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'regiondata',
        name: 'Regiondata',
        component: () => import('@/views/sales/regiondata'),
        meta: { title: '大区数据分析', icon: 'el-icon-aim', noCache: true }
      }
    ]
  },
  {
    path: '/marketcenter',
    component: () => import('@/layout'),
    redirect: '/marketcenter/citydata',
    name: 'Marketcenter',
    alwaysShow: true,
    meta: { title: '市场中心表格分析', icon: 'el-icon-data-analysis' },
    children: [
      {
        path: 'citydata',
        name: 'Citydata',
        component: () => import('@/views/marketcenter/citydata'),
        meta: { title: '城市数据报表', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'orderdata',
        name: 'Orderdata',
        component: () => import('@/views/marketcenter/orderdata'),
        meta: { title: '订单数据分析', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'webdata',
        name: 'Webdata',
        component: () => import('@/views/marketcenter/webdata'),
        meta: { title: '网站数据分析', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'APPdata',
        name: 'APPdata',
        component: () => import('@/views/marketcenter/APPdata'),
        meta: { title: 'APP数据分析报表', icon: 'el-icon-aim', noCache: true }
      }
    ]
  },
  {
    path: '/analysis',
    component: () => import('@/layout'),
    redirect: '/analysis/roidata',
    name: 'Analysis',
    alwaysShow: true,
    meta: { title: '市场中心数据分析', icon: 'el-icon-data-analysis' },
    children: [
      {
        path: 'roidata',
        name: 'Roidata',
        component: () => import('@/views/analysis/roidata'),
        meta: { title: '市场ROI数据分析', icon: 'el-icon-aim', noCache: true }
      }
    ]
  },
  {
    path: '/pubdata',
    component: () => import('@/layout'),
    redirect: '/pubdata/citydata',
    name: 'Pubdata',
    alwaysShow: true,
    meta: { title: '公共数据分析', icon: 'el-icon-data-analysis' },
    children: [
      {
        path: 'citydata',
        name: 'Citydata',
        component: () => import('@/views/pubdata/citydata'),
        meta: { title: '城市数据报表', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'splitorderdata',
        name: 'Splitorderdata',
        component: () => import('@/views/pubdata/splitorderdata'),
        meta: { title: '分单数据分析', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'memberdata',
        name: 'Memberdata',
        component: () => import('@/views/pubdata/memberdata'),
        meta: { title: '会员数据分析', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'channel',
        name: 'Channel',
        component: () => import('@/views/pubdata/channel'),
        meta: { title: '渠道数据分析', icon: 'el-icon-aim', noCache: true }
      }
    ]
  },
  {
    path: '/setup',
    component: () => import('@/layout'),
    redirect: '/setup/setregion',
    name: 'Setup',
    alwaysShow: true,
    meta: { title: '基础信息配置', icon: 'el-icon-setting' },
    children: [
      {
        path: 'setregion',
        name: 'Setregion',
        component: () => import('@/views/setup/setregion'),
        meta: { title: '大区设置', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'upcitydata',
        name: 'Upcitydata',
        component: () => import('@/views/setup/upcitydata'),
        meta: { title: '上传城市数据', icon: 'el-icon-aim', noCache: true }
      },
      {
        path: 'channel',
        name: 'Channel',
        component: () => import('@/views/setup/channel'),
        meta: { title: '上传渠道消耗金额', icon: 'el-icon-aim', noCache: true }
      }
    ]
  }
]
export default DEFAULT_ROUTER
