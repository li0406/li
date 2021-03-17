import request from '@/utils/request'

export function fetchWorkReportList(query) {
  return request({
    url: '/v1/reportwork/list',
    method: 'get',
    params: query
  })
}

export function fetchReportInfo(query) {
  return request({
    url: '/v1/reportwork/detail',
    method: 'get',
    params: query
  })
}

export function fetchAddReport(query) {
  return request({
    url: '/v1/reportwork/add',
    method: 'post',
    data: query
  })
}

export function fetchAddReply(query) {
  return request({
    url: '/v1/reportwork/reply',
    method: 'post',
    data: query
  })
}
