import request from './request'

// 避坑指南
export function yusuanMarket (id) {
  return request({
    url: '/v1/yusuan/market?cs=' + id,
    method: 'get'
  })
}
// 预算管理-版本列表
export function getVersion () {
  return request({
    url: '/v1/budget/version/list',
    method: 'get'
  })
}
// 发单
export function orderForm (data, src) {
  return request({
    url: '/zbfb/v1/fb/yusuan',
    method: 'post',
    data: data,
    headers: {
      'X-QZ-SRC': src
    }
  })
}
// 装修快报，预算结果
export function getResultDetail (data) {
  return request({
    url: '/v1/yusuan/budget',
    method: 'post',
    data: data
  })
}
// 推荐装修公司
export function getTuijianComp (data) {
  return request({
    url: '/v1/yusuan/recommend_company?cs=' + data,
    method: 'get'
  })
}
