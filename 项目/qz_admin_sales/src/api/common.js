import request from '@/utils/request'

export function fetchCityList(query) {
  return request({
    url: '/citys/',
    method: 'get',
    params: query
  })
}

export function fetchAreaList(query) {
  return request({
    url: '/areas/',
    method: 'get',
    params: query
  })
}

export function fetchNoVipCitys(query) {
  return request({
    url: '/novipcitys/',
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
