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

export function fetchGetList(query) {
  return request({
    url: '/v1/worksite/live_list',
    method: 'get',
    params: query
  })
}

export function fetchGetListDetail(query) {
  return request({
    url: '/v1/worksite/live_detail',
    method: 'get',
    params: query
  })
}

export function fetchGetInfoList(query) {
  return request({
    url: '/v1/worksite/info_list',
    method: 'get',
    params: query
  })
}
// 删除
export function fetchGetInfoListDelete(query) {
  return request({
    url: '/v1/worksite/info_delete',
    method: 'POST',
    data: query
  })
}
// 单个详情
export function fetchGetInfoDetail(query) {
  return request({
    url: '/v1/worksite/info_detail',
    method: 'GET',
    params: query
  })
}
// 编辑
export function fetchGetInfoListEdit(query) {
  return request({
    url: '/v1/worksite/info_edit',
    method: 'POST',
    data: query
  })
}

// 二维码
export function fetchGetInfoListqrcode(query) {
  return request({
    url: '/v1/worksite/live_qrcode',
    method: 'GET',
    params: query
  })
}

// 施工阶段
export function fetchGetStepList(query) {
  return request({
    url: '/v1/worksite/step_list',
    method: 'GET',
    params: query
  })
}

