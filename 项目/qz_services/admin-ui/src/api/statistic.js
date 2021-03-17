import request from '@/utils/request'

export function fetchSmsSendList(query) {
  return request({
    url: '/sms/v1/getloglist',
    method: 'get',
    params: query
  })
}

export function fetchSmsSendListExport(query) {
  return request({
    url: '/sms/v1/getloglist',
    method: 'get',
    params: query
  })
}
