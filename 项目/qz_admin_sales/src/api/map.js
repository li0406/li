import request from '@/utils/request'

// 权限城市
export function fetchPrivcitys(query) {
  return request({
    url: '/privcitys',
    method: 'get',
    params: query
  })
}

// 回访订单-推送
export function fetchVisitPush(query) {
  return request({
    url: '/v1/visit/push',
    method: 'post',
    data: query
  })
}

// 城市订单统计-列表
export function fetchCompanyList(query) {
  return request({
    url: '/v1/basicinfo/order_map',
    method: 'get',
    params: query
  })
}
// 保存
export function fetchAddCompanyInfo(query) {
  return request({
    url: '/v1/basicinfo/mark',
    method: 'post',
    data: query
  })
}
// 删除
export function fetchDelentCompanyInfo(query) {
  return request({
    url: '/v1/basicinfo/delmark',
    method: 'post',
    data: query
  })
}
