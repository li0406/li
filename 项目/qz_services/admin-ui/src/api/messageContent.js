import request from '@/utils/request'

// 获取消息类型
export function fetchMessageType(query) {
  return request({
    url: '/msg/v1/type/all',
    method: 'get',
    params: query
  })
}

// 获取消息列表
export function fetchMessagelList(query) {
  return request({
    url:'/msg/v1/template/list',
    method: 'get',
    params:query
  })
}

// 修改启用禁用
export function fetchMessageEnable(query) {
  return request({
    url: '/msg/v1/template/enabled',
    method: 'post',
    data: query
  })
}

// 导出功能
export function fetchMessageExport(query) {
  return request({
    url: '/msg/v1/template/export',
    method: 'get',
    params: query,
    responseType: 'arraybuffer', // 这个一定要有
    xsrfHeaderName: 'Authorization'
  })
}

// 编辑/新增页面获取详情
export function fetchMessageInfo (query){
  return request({
    url:'/msg/v1/template/detail',
    method: 'get',
    params: query
  })
}

// 新增页面的保存功能
export function fetchMessageSave(query) {
  return request({
    url: '/msg/v1/template/save',
    method: 'post',
    data: query
  })
}

// 应用平台筛选
export function fetchAppList(query){
  return request({
    url:'/msg/v1/template/applist',
    method: 'get',
    params: query
  })
}
