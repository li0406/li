/* eslint-disable indent */
import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

const router = new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'index',
      meta: {
        title: '装修说'
      },
      component: () => import('@/components/index/index')
    },
    {
      path: '/luckday',
      name: 'luckyDay',
      meta: {
        title: '开工吉日'
      },
      component: () => import('@/components/luckyDay/luckyDay')
    },
    {
      path: '/disclaimer',
      name: 'disclaimer',
      meta: {
        title: '免责声明'
      },
      component: () => import('@/components/disclaimer/disclaimer')
    },
    {
      path: '/share',
      name: 'share',
      meta: {
        title: '装修说'
      },
      component: () => import('@/components/qz/share/share')
    },
    {
      path: '/share-pic',
      name: 'sharePic',
      meta: {
        title: '美图分享'
      },
      component: () => import('@/components/qz/share/sharePic')
    },
    {
      path: '/share-gonglue',
      name: 'shareGongLue',
      meta: {
        title: '攻略分享'
      },
      component: () => import('@/components/qz/share/shareGongLue')
    },
    {
      path: '/share-video',
      name: 'shareVideo',
      meta: {
        title: '视频分享'
      },
      component: () => import('@/components/qz/share/shareVideo')
    },
    {
      path: '/share-cheat',
      name: 'shareCheat',
      meta: {
        title: '避坑指南分享'
      },
      component: () => import('@/components/qz/share/shareCheat')
    },
    {
      path: '/share-contrast',
      name: 'shareContrast',
      meta: {
        title: '对比广场'
      },
      component: () => import('@/components/qz/share/shareContrast')
    },
    {
      path: '/share-experience',
      name: 'shareExperience',
      meta: {
        title: '装修心得'
      },
      component: () => import('@/components/qz/share/shareExperience')
    },
    {
      path: '/share-meijia',
      name: 'shareMeiJia',
      meta: {
        title: '美家案例分享'
      },
      component: () => import('@/components/qz/share/shareMeiJia')
    },
    {
      path: '/share-selectguide',
      name: 'shareSelectGuide',
      meta: {
        title: '选材导购分享'
      },
      component: () => import('@/components/qz/share/shareSelectGuide')
    },
    {
      path: '/share-topic',
      name: 'shareTopic',
      meta: {
        title: '热门话题分享'
      },
      component: () => import('@/components/qz/share/shareTopic')
    },
    {
      path: '/share-good',
      name: 'shareGood',
      meta: {
        title: '好物分享'
      },
      component: () => import('@/components/qz/share/shareGood')
    },
    {
      path: '/share-case',
      name: 'shareCase',
      meta: {
        title: '整屋案例分享'
      },
      component: () => import('@/components/qz/share/shareCase')
    },
    {
      path: '/share-depbrand',
      name: 'shareDepBrand',
      meta: {
        title: '全国装企信赖榜单-齐装'
      },
      component: () => import('@/components/qz/share/shareDepBrand')
    },
    {
      path: '/share-pubbrand',
      name: 'sharePubBrand',
      meta: {
        title: '全国装企口碑榜单榜单-齐装'
      },
      component: () => import('@/components/qz/share/sharePubBrand')
    },
    {
      path: '/share-livelybrand',
      name: 'shareLivelyBrand',
      meta: {
        title: '全国装企活跃榜单榜单-齐装'
      },
      component: () => import('@/components/qz/share/shareLivelyBrand')
    },
    {
      path: '/share-companydetail',
      name: 'shareCompanyDetail',
      meta: {
        title: ''
      },
      component: () => import('@/components/qz/share/shareCompanyDetail')
    },
    {
        path: '/share-zhinan',
        name: 'shareZhinan',
        meta: {
            title: '攻略分享'
        },
        component: () => import('@/components/qz/share/shareZhinan')
    },
    {
      path: '/share-pin-img',
      name: 'sharePinImg',
      meta: {
        title: '动态'
      },
      component: () => import('@/components/qz/share/sharePinImg.vue')
    },
    {
      path: '/share-pin-topic',
      name: 'sharePinTopic',
      meta: {
        title: '话题'
      },
      component: () => import('@/components/qz/share/sharePinTopic.vue')
    },
    {
      path: '/playbill',
      name: 'playBill',
      meta: {
        title: '装修公司海报'
      },
      component: () => import('@/components/qz/share/playBill')
    },
    {
      path: '/share-playbill',
      name: 'sharePlayBill',
      meta: {
        title: '装修公司海报分享'
      },
      component: () => import('@/components/qz/share/sharePlayBill')
    },
    {
      path: '/zxfeature',
      name: 'zxfeature',
      meta: {
        title: '风格测试-寻找未来家的样子'
      },
      component: () => import('@/components/qz/style/zxfeature')
      // eslint-disable-next-line indent
    },
    {
      path: '/huxing',
      name: 'huxing',
      meta: {
        title: '风格测试-寻找未来家的样子'
      },
      component: () => import('@/components/qz/style/huxing')
    },
    {
      path: '/fengge',
      name: 'fengge',
      meta: {
        title: '风格测试-寻找未来家的样子'
      },
      component: () => import('@/components/qz/style/fengge')
    },
    {
      path: '/third',
      name: 'third',
      meta: {
        title: '风格测试-寻找未来家的样子'
      },
      component: () => import('@/components/qz/style/third')
    },
    {
      path: '/gonglue',
      name: 'gonglue',
      meta: {
        title: '装修攻略'
      },
      component: () => import('@/components/gonglue/gonglue')
    },
    {
      path: '/experience',
      name: 'experience',
      meta: {
        title: '避坑指南'
      },
      component: () => import('@/components/experience/experience')
    },
    {
      path: '/feedback',
      name: 'feedback',
      meta: {
        title: '意见反馈'
      },
      component: () => import('@/components/feedback/feedback')
    },
    {
      path: '/feedbackDetails',
      name: 'feedbackDetails',
      meta: {
        title: '反馈详情'
      },
      component: () => import('@/components/feedback/feedbackDetails')
    },
    {
      path: '/agreement',
      name: 'agreement',
      meta: {
        title: '用户协议'
      },
      component: () => import('@/components/agreement/agreement')
    },
    {
      path: '/share-banner',
      name: 'shareBanner',
      meta: {
        title: '装修说'
      },
      component: () => import('@/components/shareBanner/shareBanner')
    },
    {
      path: '/sheji',
      name: 'sheji',
      meta: {
        title: '装修设计'
      },
      component: () => import('@/components/sheji/sheji')
    },
    {
      path: '/shejidone',
      name: 'shejidone',
      meta: {
        title: '装修设计'
      },
      component: () => import('@/components/sheji/shejiDone')
    },
    {
      path: '/baojia',
      name: 'baojia',
      meta: {
        title: '装修报价'
      },
      component: () => import('@/components/baojia/baojia')
    },
    {
      path: '/tjyl',
      name: 'tjyl',
      meta: {
        title: '推荐有礼'
      },
      component: () => import('@/components/members/tjyl')
    },
    {
      path: '/tjylshare',
      name: 'tjylshare',
      meta: {
        title: '推荐有礼'
      },
      component: () => import('@/components/members/tjylshare')
    },
    {
      path: '/qdjl',
      name: 'qdjl',
      meta: {
        title: '签到奖励'
      },
      component: () => import('@/components/members/qdjl')
    },
    {
      path: '/baojiadone',
      name: 'baojiadone',
      meta: {
        title: '装修报价'
      },
      component: () => import('@/components/baojia/baojiaDone')
    },
    {
      path: '/activity/luckdraw', // 齐装APP引流活动
      name: 'luckdraw',
      meta: {
        title: '下载送好礼'
      },
      component: () => import('@/components/activity/luckdraw/luckDraw')
    },
    {
      path: '/activity/luckdraw/prizeinfo', // 齐装APP引流活动中奖记录
      name: 'prizeinfo',
      meta: {
        title: '中奖记录'
      },
      component: () => import('@/components/activity/luckdraw/prizeInfo')
    },
    {
      path: '/activity/luckdraw/prizeform', // 齐装APP引流活动中奖信息页
      name: 'prizeform',
      meta: {
        title: '中奖信息填写'
      },
      component: () => import('@/components/activity/luckdraw/prizeform')
    },
    {
      path: '/activity/luckdraw/activityshare',
      name: 'activityshare',
      meta: {
        title: '下载送好礼'
      },
      component: () => import('@/components/activity/luckdraw/acvityShare')
    },
    {
      path: '/activity/jjys',
      name: 'jjys',
      meta: {
        title: '幸运大转盘 - 齐装网'
      },
      component: () => import('@/components/activity/jjys/index')
    },
    {
      path: '/activity/liangfang',
      name: 'liangfang',
      meta: {
        title: '量房享好礼'
      },
      component: () => import('@/components/activity/liangfang/index')
    },
    {
      path: '/activity/measuringRoom',
      name: 'measuringRoom',
      meta: {
        title: '量房抽奖活动'
      },
      component: () => import('@/components/activity/measuringRoom/measuringRoom')
    },
    {
      path: '/activity/measuringRoomRule',
      name: 'measuringRoomRule',
      meta: {
        title: '量房抽奖活动规则'
      },
      component: () => import('@/components/activity/measuringRoom/rule')
    },
    {
      path: '/activity/mygiftpackage',
      name: 'mygiftpackage',
      meta: {
        title: '我的礼包'
      },
      component: () => import('@/components/activity/liangfang/mygiftpackage')
    },
    {
      path: '/activity/jjys/myprize',
      name: 'myprize',
      meta: {
        title: '我的奖品'
      },
      component: () => import('@/components/activity/jjys/myPrize')
    },
    {
      path: '/activity/gift',
      name: 'gift',
      meta: {
        title: '发单送好礼-齐装网7周年金秋钜惠'
      },
      component: () => import('@/components/activity/gift/index')
    },
    {
      path: '/activity/jjys/sharemyprize',
      name: 'sharemyprize',
      meta: {
        title: '我的奖品'
      },
      component: () => import('@/components/activity/jjys/shareMyPrize')
    },
    {
      path: '/qz/share',
      name: 'qzshare',
      meta: {
        title: '齐装APP'
      },
      component: () => import('@/components/qz/share/qzShare')
    },
    {
      path: '/ygj/guiddown',
      name: 'guiddown',
      meta: {
        title: '齐装云管家app下载 - 装修app软件 -跟单神器app - 齐装网'
      },
      component: () => import('@/components/ygj/guiddown/guidDown')
    },
    {
      path: '/qzdownload',
      name: 'qzdownload',
      meta: {
        title: '安全下载'
      },
      component: () => import('@/components/qz/download/qzDownLoad')
    },
    {
      path: '/qz/zxkb',
      name: '/qz/zxkb',
      meta: {
        title: '装修行情'
      },
      component: () => import('@/components/qz/zxkb/index')
    },
    {
      path: '/zxsdownload',
      name: 'zxsdownload',
      meta: {
        title: '安全下载'
      },
      component: () => import('@/components/zxs/download/zxsDownLoad')
    },
    {
      path: '/zxs/privacy',
      name: 'zxsprivacy',
      meta: {
        title: '隐私政策'
      },
      component: () => import('@/components/zxs/privacy/zxsPrivacy')
    },
    {
      path: '/bao',
      name: 'bao',
      meta: {
        title: '齐装保'
      },
      component: () => import('@/components/qz/bao/bao')
    },
    {
      path: '/qzb',
      name: 'qzb',
      meta: {
          title: '齐装保分享页'
      },
      component: () => import('@/components/qz/bao/qzb')
    },
    {
      path: '/qzw/xhxsj',
      name: 'xhxsj',
      meta: {
        title: '找设计'
      },
      component: () => import('@/components/qzw/sheji/xhxsj')
    },
    {
      path: '/xhxsj-success',
      name: 'xhxsj-success',
      meta: {
        title: '找设计'
      },
      component: () => import('@/components/qzw/sheji/xhxsj_success')
    },
    {
      path: '/qzw/design-free-bill',
      name: 'designFreeBill',
      meta: {
        title: '免费设计'
      },
      component: () => import('@/components/qzw/sheji/designFreeBill')
    },
    {
      path: '/qzw/design-free-result',
      name: 'designFreeResult',
      meta: {
        title: '免费设计'
      },
      component: () => import('@/components/qzw/sheji/designFreeResult')
    },
    {
      path: '/qzw/design-free-hour',
      name: 'designFreeHour',
      meta: {
        title: '免费设计'
      },
      component: () => import('@/components/qzw/sheji/designFreeHour')
    },
    {
      path: '/qzw/zxbj',
      name: 'zxbj',
      meta: {
        title: '算报价'
      },
      component: () => import('@/components/qzw/baojia/zxbj')
    },
    {
      path: '/zxbj-success',
      name: 'zxbj-success',
      meta: {
        title: '算报价'
      },
      component: () => import('@/components/qzw/baojia/zxbj_success')
    },
    {
      path: '/qzw/quote-free-issu',
      name: 'quoteFreeIssu',
      meta: {
        title: '免费报价'
      },
      component: () => import('@/components/qzw/baojia/quoteFreeIssu')
    },
    {
      path: '/qzw/quote-free-result',
      name: 'quoteFreeResult',
      meta: {
        title: '免费报价'
      },
      component: () => import('@/components/qzw/baojia/quoteFreeResult')
    },
    {
      path: '/qzw/quote-free-succ',
      name: 'quoteFreeSucc',
      meta: {
        title: '免费报价'
      },
      component: () => import('@/components/qzw/baojia/quoteFreeSucc')
    },
    {
      path: '/qzw/room-free-result',
      name: 'roomFreeResult',
      meta: {
        title: '免费量房'
      },
      component: () => import('@/components/qzw/baojia/roomFreeResult')
    },
    {
      path: '/qzw/room-free-hour',
      name: 'roomFreehour',
      meta: {
        title: '免费量房'
      },
      component: () => import('@/components/qzw/baojia/roomFreehour')
    },
    {
      path: '/cash-rule',
      name: 'cash-rule',
      meta: {
        title: '活动规则'
      },
      component: () => import('@/components/qz/cash/rule')
    },
    {
      path: '/cash-lottery',
      name: 'cash-lottery',
      meta: {
        title: '现金券抽奖'
      },
      component: () => import('@/components/qz/cash/lottery')
    },
    {
      path: '/zxfw',
      name: 'zxfw',
      meta: {
        title: '八大保障'
      },
      component: () => import('@/components/qzw/baozhang/zxfw')
    },
    {
      path: '/activity818',
      name: 'activity818',
      meta: {
        title: '818齐装全民家装节'
      },
      component: () => import('@/components/activity/activity818/index')
    },
    {
      path: '/activityrule',
      name: 'activityrule',
      meta: {
        title: '818齐装全民家装节'
      },
      component: () => import('@/components/activity/activity818/rule')
    },
    {
      path: '/space-change',
      name: 'spaceChange',
      meta: {
        title: '空间改造'
      },
      component: () => import('@/components/spaceChange/spaceChange.vue')
    },
    {
      path: '/see',
      name: 'See',
      meta: {
          title: '小白必看'
      },
      component: () => import('@/components/see/see.vue')
    },
    {
      path: '/smscompany',
      name: 'smscompany',
      meta: {
        title: ''
      },
      component: () => import('@/components/smscompany/smscompany')
    },
    {
      path: '*',
      name: 'notfound',
      meta: {
        title: 'not funound'
      },
      component: () => import('@/components/notFound')
    }
  ],
  scrollBehavior (to, from, saveTop) {
    if (saveTop) {
      return saveTop
    } else {
      return {x: 0, y: 0}
    }
  }
})
router.beforeEach((to, from, next) => {
  document.title = to.meta.title
  next()
})
export default router
