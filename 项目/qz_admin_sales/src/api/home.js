import request from '@/utils/request'

export function fetcheAuditorder(query) {
  return request({
    url: '/v1/order/auditorder',
    method: 'get',
    params: query
  })
}
export function getRemind(query) {
  return request({
    url: '/v1/msgsystem/getremind',
    method: 'get',
    params: query
  })
}
export function getList(query) {
  return request({
    url: '/v1/msgsystem/getlist',
    method: 'get',
    params: query
  })
}
export function setRead(query) {
  return request({
    url: '/v1/msgsystem/setread',
    method: 'post',
    data: query
  })
}
//消息中心获取列表类型
export function getTypeList(query) {
    return request({
      url: '/v1/msgsystem/get_type_list',
      method: 'get',
      params: query
    })
}
//消息中心删除
export function delList(query){
    return request({
        url: '/v1/msgsystem/delete',
        method: 'post',
        data: query,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
    })
}