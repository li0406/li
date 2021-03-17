import request from '@/utils/request'

export function userSearch(name) {
  return request({
    url: '/search/user',
    method: 'get',
    params: { name }
  })
}

export function fetchMemberOrderList(query) {
  return request({
    url: '',
    method: 'get',
    params: query
  })
}
