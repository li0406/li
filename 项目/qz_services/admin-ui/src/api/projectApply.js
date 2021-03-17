import request from '@/utils/request'

// APP推送消息管理列表
export function getMsglist(query) {
    return request({
        url: '/msg/v1/app/list',
        method: 'get',
        params: query
    })
}

//推送消息添加列表
export function fetchMsgAdd(query) {
    return request({
        url: '/msg/v1/app/save',
        method: 'post',
        data: query
    })
}

//推送消息编辑列表
export function getMsgEdit(query) {
    return request({
        url: '/msg/v1/app/get',
        method: 'get',
        params: query
    })
}

//删除
export function getMsgDel(query) {
    return request({
        url: '/msg/v1/app/del',
        method: 'post',
        data: query
    })
}
// 图片
export function fetchPicUpload(query) {
    return request({
        url: '/common/upload',
        method: 'post',
        params: query
    })
}

//消息推送列表
export function getLogList(query) {
    return request({
      url: '/msg/v1/log/list',
      method: 'get',
      params: query
    })
}

//城市列表
export function getCityList(query) {
    return request({
      url: '/common/city',
      method: 'get',
      params: query
    })
}

// 导出功能
export function getExport(query) {
    return request({
      url: '/msg/v1/app/export',
      method: 'get',
      params: query,
      responseType: 'arraybuffer', // 这个一定要有
      xsrfHeaderName: 'Authorization'
    })
  }

export function fetchApplyList(query) {
  return request({
    url: '/sms/v1/app/list',
    method: 'get',
    params: query
  })
}

export function fetchApplySave(query) {
  return request({
    url: '/sms/v1/app/save',
    method: 'post',
    data: query
  })
}

export function fetchAppEnable(query) {
  return request({
    url: '/sms/v1/app/enable',
    method: 'post',
    data: query
  })
}

export function fetchAppDetail(query) {
  return request({
    url: '/sms/v1/app/detail',
    method: 'get',
    params: query
})


}
