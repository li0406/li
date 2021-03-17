import request from './request'

export default{
  // 空间改造-大咖云集
  popularity (query) {
    return request({
      url: '/v1/company/designer/popularity',
      method: 'get',
      params: query
    })
  },
  fb (parms) {
    return request({
      url: '/zbfb/v3/fb/zhuangxiu',
      method: 'post',
      data: parms,
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    })
  }
}
