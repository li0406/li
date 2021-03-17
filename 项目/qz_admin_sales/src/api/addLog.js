import request from '@/utils/request'

export function fetchCompanyList(query) {
  return request({
    url: '/v1/company/old_list',
    method: 'get',
    params: query
  })
}

export function fetchCompanyInfo(query) {
  return request({
    url: '/v1/company/info',
    method: 'get',
    params: query
  })
}

export function fetchCompanyIs(query) {
  return request({
    url: '/v1/company/is_repeat',
    method: 'get',
    params: query
  })
}

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

export function fetchReportAdd(query) {
  return request({
    url: '/v1/report/add_first_visit',
    method: 'post',
    data: query
  })
}

export function fetchTelPhone(query) {
  return request({
    url: '/v1/voip/call',
    method: 'post',
    data: query
  })
}

export function fetchFindUser(query) {
  return request({
    url: '/finduser',
    method: 'post',
    data: query
  })
}