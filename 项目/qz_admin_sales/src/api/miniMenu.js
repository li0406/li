import request from '@/utils/request'

export function fetchMiniList(query) {
  return request({
    url: '/v1/appletmenu/list',
    method: 'get',
    params: query
  })
}

export function fetchMiniSwitch(query) {
  return request({
    url: '/v1/appletmenu/enabled',
    method: 'post',
    data: query
  })
}

export function fetchMiniAdd(query) {
  return request({
    url: '/v1/appletmenu/save',
    method: 'post',
    data: query
  })
}

// 编辑/新增
export function fetchMiniDetail(query) {
  return request({
    url: '/v1/appletmenu/detail',
    method: 'get',
    params: query
  })
}

export function fetchMiniSys(query) {
  return request({
    url: '/v1/appletmenu/sysmenu',
    method: 'get',
    params: query
  })
}