import request from '@/utils/request'

export function fetchSmsTplList(query) {
  return request({
    url: '/sms/v1/temp/list',
    method: 'get',
    params: query
  })
}

export function fetchSmsSendTplList(query) {
  return request({
    url: '/sms/v1/getsmstemplatelist',
    method: 'get',
    params: query
  })
}

export function fetchSignatureList(query) {
  return request({
    url: '/sms/v1/sign/list',
    method: 'get',
    params: query
  })
}

export function fetchSignatureEnable(query) {
  return request({
    url: '/sms/v1/sign/enabled',
    method: 'post',
    data: query
  })
}

export function fetchSignatureEdit(query) {
  return request({
    url: '/sms/v1/sign/save',
    method: 'post',
    data: query
  })
}

export function fetchFilterOptions(query) {
  return request({
    url: '/sms/v1/temp/options',
    method: 'get',
    params: query
  })
}

export function fetchFilterTplInfo(query) {
  return request({
    url: '/sms/v1/temp/getinfo',
    method: 'get',
    params: query
  })
}

export function fetchSmsTpl(query) {
  return request({
    url: '/sms/v1/temp/export',
    method: 'get',
    params: query
  })
}

export function fetchSmsTplExport(query) {
  return request({
    url: '/sms/v1/temp/export-excel',
    method: 'get',
    params: query,
    responseType: 'arraybuffer', // 这个一定要有
    xsrfHeaderName: 'Authorization'
  })
}

export function fetchTplEnable(query) {
  return request({
    url: '/sms/v1/temp/enabled',
    method: 'post',
    data: query
  })
}

export function fetchTplSave(query) {
  return request({
    url: '/sms/v1/temp/save',
    method: 'post',
    data: query
  })
}

export function fetchSendSmsSave(query) {
  return request({
    url: '/sms/v1/sendsms',
    method: 'post',
    data: query
  })
}
