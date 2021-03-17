import request from '@/utils/request'

export function fetchDeadList(query) {
  return request({
    url: '/v1/company/expire_remind_pc',
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
