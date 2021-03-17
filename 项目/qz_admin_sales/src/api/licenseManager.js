import request from '@/utils/request'

export function fetchFindUser(query) {
  return request({
    url: '/finduser',
    method: 'post',
    data: query
  })
}

export function fetchLicenseStatistics(query) {
  return request({
    url: '/v1/businesslicence/statistics',
    method: 'get',
    params: query
  })
}

export function fetchLicenseList(query) {
  return request({
    url: '/v1/businesslicence/list',
    method: 'get',
    params: query
  })
}

export function fetchLicenseUploadList(query) {
  return request({
    url: '/v1/businesslicence/uplist',
    method: 'get',
    params: query
  })
}

export function fetchLicenseAuditWait(query) {
  return request({
    url: '/v1/businesslicence/examfirst',
    method: 'post',
    data: query
  })
}

export function fetchConfirmCheck(query) {
  return request({
    url: '/v1/businesslicence/examfinal',
    method: 'post',
    data: query
  })
}

export function fetchResetCheck(query) {
  return request({
    url: '/v1/businesslicence/examreset',
    method: 'post',
    data: query
  })
}

export function fetchclearLicense(query) {
  return request({
    url: '/v1/businesslicence/upclean',
    method: 'post',
    data: query
  })
}

export function fetchUploadLicense(query) {
  return request({
    url: '/v1/businesslicence/upfile',
    method: 'post',
    data: query
  })
}

export function fetchCityManager(query) {
  return request({
    url: '/v1/businesslicence/citymanager',
    method: 'get',
    params: query
  })
}
