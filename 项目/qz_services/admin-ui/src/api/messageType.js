import request from '@/utils/request'
// 请求列表数据
export function getMessageList(query){
  return request({
    url:'/msg/v1/type/list',
    method: 'get',
    params:query
  })

}
// 状态改变
export function fetchMessageEnable(query) {
  return request({
    url: '/msg/v1/type/change',
    method: 'post',
    data: query
  })
}
// 新增编辑的保存
export function fetchMessageSave(query) {
  return request({
    url: '/msg/v1/type/save',
    method: 'post',
    data: query
  })
}
// 点击编辑按钮调模态框 获取该数据的信息
export function editMessage(query){
  return request({
    url:'/msg/v1/type/get',
    method:'get',
    params:query
  })
}
// export function fetchApplyList(query) {
//   return request({
//     url: '/sms/v1/app/list',
//     method: 'get',
//     params: query
//   })
// }

// export function fetchApplySave(query) {
//   return request({
//     url: '/sms/v1/app/save',
//     method: 'post',
//     data: query
//   })
// }

// export function fetchAppEnable(query) {
//   return request({
//     url: '/sms/v1/app/enable',
//     method: 'post',
//     data: query
//   })
// }

// export function fetchAppDetail(query) {
//   return request({
//     url: '/sms/v1/app/detail',
//     method: 'get',
//     params: query
//   })
// }
