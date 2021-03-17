import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/v1/company/list_company',
    method: 'get',
    params: query
  })
}

export function fetchvoip(query) {
  return request({
    url: '/v1/voip/info',
    method: 'get',
    params: query
  })
}

export function fetchCompanyImport(query) {
  return request({
    url: '/v1/report/company_import',
    method: 'post',
    data: query
  })
}

export function fetchlistSale(query) {
  return request({
    url: '/v1/company/list_sale',
    method: 'get',
    params: query
  })
}

export function fetchFindUser(query) {
  return request({
    url: '/v1/company/list',
    method: 'get',
    params: query
  })
}

export function fetchCompanySale(query) {
  return request({
    url: '/v1/saler/list_sale',
    method: 'get',
    params: query
  })
}