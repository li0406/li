import request from '@/utils/request'

export function fetchSupplierList(query) {
  return request({
    url: '/pnp/v1/providerlist',
    method: 'get',
    params: query
  })
}

export function fetchProviderup(query) {
  return request({
    url: '/pnp/v1/providerup',
    method: 'post',
    data: query
  })
}

// 编辑获取单条
export function fetchSupplierDetail(query) {
  return request({
    url: '/pnp/v1/providerup',
    method: 'get',
    params: query
  })
}

export function fetchConfig(query) {
  return request({
    url: '/pnp/v1/config',
    method: 'get',
    params: query
  })
}

export function fetchConfigup(query) {
  return request({
    url: '/pnp/v1/configup',
    method: 'post',
    data: query
  })
}

export function providerdropdownlist(query) {
  return request({
    url: '/pnp/v1/providerdropdownlist',
    method: 'get',
    params: query
  })
}

export function providerstatusup(query) {
  return request({
    url: '/pnp/v1/providerstatusup',
    method: 'post',
    data: query
  })
}
