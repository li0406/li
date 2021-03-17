import request from '@/utils/request'

export function fetchListUser(query) {
  return request({
    url: '/v1/report/list_user',
    method: 'get',
    params: query
  })
}

export function fetchListVisit(query) {
  return request({
    url: '/v1/report/list_visit',
    method: 'get',
    params: query
  })
}

export function fetchMemberOrderID(query) {
  return request({
    url: '/v1/company/old_list',
    method: 'get',
    params: query
  })
}

export function fetchContract(query) {
  return request({
    url: '/v1/report/get_contract',
    method: 'get',
    params: query
  })
}

export function fetchAddUser(query) {
  return request({
    url: '/v1/report/add_user',
    method: 'post',
    data: query
  })
}

export function fetchEditUser(query) {
  return request({
    url: '/v1/report/edit_user',
    method: 'post',
    data: query
  })
}

export function fetchAddListVisit(query) {
  return request({
    url: '/v1/report/add_visit',
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

export function fetchSoundRecordList(query) {
  return request({
    url: '/v1/voip/info',
    method: 'get',
    params: query
  })
}

export function fetchSoundRecordDetail(query) {
  return request({
    url: '/v1/telcenter/recordurl',
    method: 'get',
    params: query
  })
}
