/*
* 活动接口
*/
import request from './request'

// 推荐有礼获取随机数
export function activityNum () {
  return request({
    url: '/score/v1/activity_num',
    method: 'get'
  })
}

// 推荐有礼获取验证码
export function sendMessage (parms, env) {
  return request({
    url: '/uc/v1/msg/send',
    method: 'post',
    data: parms,
    headers: {
      'Addr': 'APP_ZXS',
      'App-From': 'c50a5ed98f4b77f07f28d181e15566f7',
      'App-Env': env
    }
  })
}

// 推荐有礼-接受邀请
export function getRegister (parms, env) {
  return request({
    url: '/uc/v1/score_register',
    method: 'post',
    data: parms,
    headers: {
      'Addr': 'APP_ZXS',
      'App-From': 'c50a5ed98f4b77f07f28d181e15566f7',
      'App-Env': env
    }
  })
}

// 推荐有礼-获取推荐的列表 轮播
export function getRecommendList (parms) {
  return request({
    url: '/score/v1/recommend_list',
    method: 'get',
    data: parms
  })
}

// 推荐有礼-获取我的推荐列表中的数据
export function getMyRecommendList (query) {
  return request({
    url: '/score/v1/my_recommend',
    method: 'get',
    params: query
  })
}

// 获取用户信息
export function getInfo (query) {
  return request({
    url: '/uc/v1/profile',
    method: 'get',
    params: query
  })
}

// 极验证1
export function getGeet1 (query) {
  return request({
    url: '/v2/geetestapi1',
    method: 'get',
    params: query
  })
}

// 极验证2
export function getGeet2 (parms) {
  return request({
    url: '/v2/geetestapi2',
    method: 'post',
    data: parms
  })
}
