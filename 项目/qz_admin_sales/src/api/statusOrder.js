import request from '@/utils/request'

// 模拟接口
export function fetchStautsOrderList(query) {
  return request({
    url: '/v1/statistics/company_orders',
    method: 'get',
    params: query
  })
}

export function fetchReadOrderList(query) {
  return request({
    url: '/v1/statistics/unread_orders',
    method: 'get',
    params: query
  })
}

export function fetchStautsOrderDetail(query) {
  return request({
    url: '/v1/orders/fen_companys',
    method: 'get',
    params: query
  })
}

export function fetchOrderInfo(query) {
  return request({
    url: '/v1/orders/fen_order_info',
    method: 'get',
    params: query
  })
}

export function fetchFindUser(query) {
  return request({
    url: '/finduser',
    method: 'post',
    data: query
  })
}

