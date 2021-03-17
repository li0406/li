import request from '@/utils/request'

export function fetchMemberOrderList(query) {
  return request({
    url: '/v1/saler/signlist',
    method: 'get',
    params: query
  })
}

export function fetchOrderInfo(query) {
  return request({
    url: '/v1/saler/signinfo',
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
export function fetchConsultationList(query) {
  return request({
    url: '/v1/orders/consultation',
    method: 'get',
    params: query
  })
}
export function consultationDetail(query) {
  return request({
    url: '/v1/orders/consultation/show',
    method: 'get',
    params: query
  })
}
export function consultationCheck(query) {
  return request({
    url: '/v1/orders/consultation/check',
    method: 'post',
    data: query
  })
}