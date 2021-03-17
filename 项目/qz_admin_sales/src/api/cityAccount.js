import request from '@/utils/request'

export function fetchSalesCityList(query) {
  return request({
    url: '/v1/saler/citys',
    method: 'get',
    params: query
  })
}

export function fetchAllCitys(query) {
  return request({
    url: '/citys/',
    method: 'get',
    params: query
  })
}

export function fetchSaveCitys(query) {
  return request({
    url: '/v1/saler/savecitys',
    method: 'post',
    data: query
  })
}

export function fetchAccounts(query) {
  return request({
    url: '/findadmin',
    method: 'post',
    data: query
  })
}

export function fetchSalerInfo(query) {
  return request({
    url: '/v1/saler/cityinfo',
    method: 'get',
    params: query
  })
}

export function fetchSalerClear(query) {
  return request({
    url: '/v1/saler/clearcity',
    method: 'post',
    data: query
  })
}

export function fetchNonmemberCityList(query) {
  return request({
    url: '/v1/company/no_vip_citys',
    method: 'get',
    params: query
  })
}

export function fetchNonmemberOrderList(query) {
  return request({
    url: '/v1/company/no_vip_city_orders',
    method: 'get',
    params: query
  })
}

// 获取质检城市
export function fetchAuditCityList(query) {
  return request({
    url: '/v1/basicinfo/salecity_info',
    method: 'get',
    params: query
  })
}
