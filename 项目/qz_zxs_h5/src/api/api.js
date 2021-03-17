/* eslint-disable */
/*
* @Author: qz_xsc
* @Date:   2018-10-18 10:29:46
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-10-23 16:42:03
*/
import request from './request'

// 定位
export function mapLocation (query) {
  return request({
    url: '/v2/getlocationhaddefault',
    method: 'get',
    data: query
  })
}

// 避坑指南
export function experience (id) {
  return request({
    url: '/v1/experience/web?id=' + id,
    method: 'get',
    data: id
  })
}
// 攻略详情
export function gonglue (id) {
  return request({
    url: '/v2/strategy/get?id=' + id,
    method: 'get'
  })
}

// 装修心得分享(详情)
export function shareExperence (id) {
  return request({
    url: '/v2/article/getarticleinfo?id=' + id,
    method: 'get'
  })
}
// 装修心得分享(推荐)
export function shareExperenceTui (id) {
  return request({
    url: '/v2/article/recommmandarticle?id=' + id,
    method: 'get'
  })
}

// 美家案例分享（热门案例）
export function shareHotAnLi (id) {
  return request({
    url: '/v2/article/hotcases?id=' + id,
    method: 'get'
  })
}

// 美家案例分享（详情）
export function shareAnLiDetail (id) {
  return request({
    url: '/v2/article/getarticleinfo?id=' + id,
    method: 'get'
  })
}

// 美家案例分享（热门评论）
export function shareAnLiComment (query) {
  return request({
    url: '/v1/comment/list',
    method: 'get',
    params: query
  })
}

// 热门话题分享（详情）
export function shareTopicDetail (query) {
  return request({
    url: '/v1/topic/detail',
    method: 'get',
    params: query
  })
}

// 热门话题分享（评论）
export function shareTopicComment (query) {
  return request({
    url: '/v1/comment/list',
    method: 'get',
    params: query
  })
}

// 热门话题分享（话题广场）
export function shareTopic (query) {
  return request({
    url: '/v2/topic/topicsquare',
    method: 'get',
    params: query
  })
}

// 选材导购（品牌榜栏目）
export function shareGuideTag (query) {
  return request({
    url: '/v1/brand/cate',
    method: 'get',
    params: query
  })
}
// 选材导购（品牌榜列表）
export function shareGuideList (query) {
  return request({
    url: '/v1/brand/list',
    method: 'get',
    params: query
  })
}

// 选材导购（banner）
export function shareGuideBanner (query) {
  return request({
    url: '/v1/face',
    method: 'get',
    params: query
  })
}

// 选材导购（分类榜栏目）
export function shareGuidefTag (query) {
  return request({
    url: '/v1/classification/cate',
    method: 'get',
    params: query
  })
}

// 选材导购（分类榜列表）
export function shareGuidefList (query) {
  return request({
    url: '/v1/classification/list',
    method: 'get',
    params: query
  })
}

//  齐装-淘好物模块前后台
export function shareGoodInfo (query) {
  return request({
    url: '/tbk/v1/goods/detail',
    method: 'get',
    params: query
  })
}

// 装修公司-列表-信赖榜单-全国
export function shareDepBrand (query) {
  return request({
    url: '/v1/company/trust_top',
    method: 'get',
    params: query
  })
}

// 装修公司-列表-口碑排行榜单-全国
export function sharePubBrand (query) {
  return request({
    url: '/v1/company/reputation_top',
    method: 'get',
    params: query
  })
}

// 装修公司-列表-装企活跃排行榜单-全国
export function shareLivelyBrand (query) {
  return request({
    url: '/v1/company/active_top',
    method: 'get',
    params: query
  })
}

// 装修公司-店铺信息
export function shareCompanyBasicinfo (query) {
  return request({
    url: '/v1/company/basicinfo',
    method: 'get',
    params: query
  })
}

// 装修公司-详情-礼券,评价,案例,设计师
export function shareCompanyDetaillist (query) {
  return request({
    url: '/v1/company/detaillist',
    method: 'get',
    params: query
  })
}

// 获取城市列表
export function getCity () {
  return request({
    url: '/v1/city/getallcitylist',
    method: 'get'
  })
}

// 设置反馈信息
export function setFeedBack (parms) {
  return request({
    url: '/v1/user/feedback',
    method: 'post',
    data: parms
  })
}

// 获取反馈详情
export function getfeedbackdetails (query) {
  return request({
    url: '/v1/user/feedback/getfeedbackdetails',
    method: 'get',
    params: query
  })
}

// 图片上传
export function upLoadImg (parms) {
  return request({
    url: '/v1/user/feedback/imgup',
    method: 'post',
    data: parms,
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// 装修吉日
export function zbfb (parms, src) {
  return request({
    url: '/zbfb/v1/hl',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 装修吉日反馈结果
export function hlresult (parms) {
  return request({
    url: '/zbfb/v1/hl/component',
    method: 'post',
    data: parms
  })
}

// 装修报价
export function baojia (parms, src) {
  return request({
    url: '/zbfb/v1/fb/first',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 装修报价2
export function baojia2 (parms, src) {
  return request({
    url: '/zbfb/v1/fb/second',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}
// 获取报价结果
export function baojiadone (parms) {
  return request({
    url: '/zbfb/v1/fb/result?orderid=' + parms,
    method: 'get'
  })
}

// 装修设计
export function sheji (parms, src) {
  return request({
    url: '/zbfb/v1/fb/sheji',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 996 装修设计
export function xhxsj (parms, src) {
  return request({
    url: '/zbfb/v1/fb/findsheji',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 抽优酷会员-判断任务20是否完成
export function isFinishTask (parms) {
  return request({
    url: '/score/v1/finish_once_task',
    method: 'post',
    data: parms
  })
}

// 996 装修报价
export function zxbj (parms, src) {
  return request({
    url: '/zbfb/v1/fb/computebaojia',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}
export function echartBill (query) {
  return request({
    url: '/zbfb/v1/fb/baojiaresult',
    method: 'get',
    params: query
  })
}
export function premark (parms) {
  return request({
    url: '/zbfb/v1/fb/improve',
    method: 'post',
    data: parms,
  })
}

// 1027  APP齐装新增八大保障H5页面
export function zxfw (parms, src) {
  return request({
    url: '/zbfb/v1/fb/publicfb',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 六月拉新活动 - 获取活动信息
export function getActivityData () {
  return request({
    url: '/v2/activity/june',
    method: 'get'
  })
}

// 获取用户参与次数
export function getUserPartis () {
  return request({
    url: '/v2/activity/june/partis',
    method: 'get'
  })
}
// 抽奖
export function drawPrizeInfo (id) {
  return request({
    url: '/v2/activity/june/draw',
    method: 'post',
    data: {
      activity_id: id
    }
  })
}

// 获取中奖记录
export function getWinList (activityId) {
  return request({
    url: '/v2/activity/june/winlist',
    method: 'get',
    data: {
      activity_id: activityId
    }
  })
}

// 分享增加次数
export function shareAddTimes (activityId) {
  return request({
    url: '/v2/activity/june/share',
    method: 'post',
    data: {
      activity_id: activityId
    },
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// 上报活动信息
export function submitWinInfo (parms) {
  return request({
    url: '/v2/activity/june/receive',
    method: 'post',
    data: parms,
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// 放弃领取
export function abandon (parms) {
  return request({
    url: '/v2/activity/june/abandon',
    method: 'post',
    data: parms,
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// 获取apk 下载链接
export function getApkUrl (params) {
  return request({
    url: '/v1/version/getnewestdownloadurl?type=' + params,
    method: 'get'
  })
}

// 获取奖品列表
export function getMyPrizeList (parms) {
  return request({
    url: '/v3/activity/october/winlist',
    method: 'get',
    data: parms
  })
}

// 获取风格测试图片
// export function getMeitu (parms) {
//   return requstmpapi({
//     url: '/bd/v1/qizuang/getmeitu',
//     method: 'get',
//     data: parms
//   })
// }

export function getMeitu (parms) {
  return request({
    url: '/v1/meitu/getmeitu',
    method: 'get',
    data: parms
  })
}

// 风格提交表单
export function postFenge (parms, src) {
  return request({
    url: '/zbfb/v1/fb/fg',
    method: 'post',
    data: parms,
    headers: {
      'Content-Type': 'multipart/form-data',
      'X-QZ-SRC': src
    }
  })
}
//  获取装企列表
export function ordercompany (id) {
  return request({
    url: '/h5/v1/company/ordercompany?order_id=' + id,
    method: 'get',
    data: id
  })
}
// export function postFenge (parms, src) {
//   return requstmpapi({
//     url: '/v1/findhomefborder',
//     method: 'post',
//     data: parms,
//     headers: {
//       'Content-Type': 'multipart/form-data',
//       'X-QZ-SRC': src
//     }
//   })
// }
