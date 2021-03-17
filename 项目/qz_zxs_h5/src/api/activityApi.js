/* eslint-disable */
/*
* 活动接口
*/
import request from './request'

// 金九银十活动
// 获取活动奖品信息
export function getPrizeData (id) {
  return request({
    url: '/v3/activity/october',
    method: 'get',
    data: {
      activity_id: 32
    }
  })
}

// 获取验证码
export function sendMessage (parms) {
  return request({
    url: '/v3/msg/send',
    method: 'post',
    data: parms
  })
}

// 带验证码登录
export function userLogin (parms) {
  return request({
    url: '/v3/login',
    method: 'post',
    data: parms
  })
}

// 获取当前抽奖状态
export function getNowStatus (parms) {
  return request({
    url: '/v3/activity/october/partis',
    method: 'get',
    data: parms
  })
}

// 立即抽奖
export function drawNow () {
  return request({
    url: '/v3/activity/october/draw',
    method: 'post',
    data: {
      activity_id: 32
    }
  })
}

// 分享增加抽奖次数
export function shareAddTimes () {
  return request({
    url: '/v3/activity/october/share',
    method: 'post',
    data: {
      activity_id: 32
    }
  })
}

// 发单
export function toOrder (parms) {
  return request({
    url: '/v3/activity/october/fb_order',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': parms.src
    }
  })
}

// 获取用户信息
export function getInfo (parms) {
  return request({
    url: '/v3/user/get',
    method: 'get',
    data: parms
  })
}

// 量房活动-获取用户是否领取优惠券
export function isGetDiscount (parms) {
  return request({
    url: '/v1/actitity/getmylfcoupon',
    method: 'get',
    data: parms
  })
}

// 量房活动-获取当前是否有发单状态
export function lastOrderInfo (parms) {
  return request({
    url: '/v1/user/getlastorder',
    method: 'get',
    params: parms
  })
}

// 量房活动-发单（就是领取优惠券）
export function getDiscountAction (parms) {
  return request({
    url: '/zbfb/v1/fb/lf',
    method: 'post',
    data: parms
  })
}

// 量房活动-添加量房图片
export function submitLiangFangPic (parms) {
  return request({
    url: '/v1/actitity/uplfimage',
    method: 'post',
    data: parms
  })
}

// 1097 PC&M&APP新增活动专题页-装修公司list
export function companyList (parms) {
  return request({
    url: '/v1/platformactivity/select_activity',
    method: 'get',
    params: parms
  })
}

// 1097 PC&M&APP新增活动专题页-领取津贴
export function getAllowance (parms, src) {
  return request({
    url: '/v1/platformactivity/jintie',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 1097 PC&M&APP新增活动专题页-预约到店
export function getReserve (parms, src) {
  return request({
    url: '/zbfb/v1/fb/publicfb',
    method: 'post',
    data: parms,
    headers: {
      'X-QZ-SRC': src
    }
  })
}

// 1097 PC&M&APP新增活动专题页-抽奖
export function getChouJiang (parms) {
  return request({
    url: '/v1/platformactivity/draw',
    method: 'post',
    data: parms
  })
}

