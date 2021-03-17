import request from '@/utils/request'

// 上方信息
export function fetchListUser(query) {
  return request({
    url: '/v1/report/list_user',
    method: 'get',
    params: query
  })
}

// 录音信息
export function fetchVoip(query) {
  return request({
    url: '/v1/voip/info',
    method: 'get',
    params: query
  })
}

// 列表信息
export function fetchListVisit(query) {
  return request({
    url: '/v1/report/list_visit',
    method: 'get',
    params: query
  })
}

