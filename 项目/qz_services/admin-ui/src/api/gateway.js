import request from '@/utils/request'

export function fetchGatewayList(query) {
  return request({
    url: '/sms/v1/gateway/list',
    method: 'get',
    params: query
  })
}

export function fetchGatewayStatus(query) {
  return request({
    url: '/sms/v1/gateway/enable',
    method: 'post',
    data: query
  })
}

export function fetchGatewayDetail(query) {
  return request({
    url: '/sms/v1/gateway/detail',
    method: 'get',
    params: query
  })
}

export function fetchGatewaySave(query) {
  return request({
    url: '/sms/v1/gateway/save',
    method: 'post',
    data: query
  })
}
