import request from '@/utils/request'

// 获取消息列表
export function fetchSendlList(query) {
  return request({
    url: '/msg/v1/msginapp/list',
    method: 'get',
    params: query
  })
}
// 编辑/新增页面获取详情
export function fetchSendInfo(query) {
  return request({
    url: '/msg/v1/msginapp/detail',
    method: 'get',
    params: query
  })
}

// 新增页面的保存功能
export function fetchMessageSave(query) {
  return request({
    url: '/msg/v1/msginapp/save',
    method: 'post',
    data: query
  })
}

// 应用平台筛选
export function fetchAppList(query) {
  return request({
    url: '/msg/v1/template/applist',
    method: 'get',
    params: query
  })
}
