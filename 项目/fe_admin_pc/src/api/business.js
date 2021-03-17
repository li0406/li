import request from '@/utils/request'
//  公共
const BUSINESS = {
  //  G端-商家管理-列表
  list: data =>
    request({
      url: `/console/company/list`,
      method: 'post',
      data
    }),
  //  G端-商家管理-详情
  detail: data =>
    request({
      url: `/console/company/detail`,
      method: 'post',
      data
    }),
  //  G端-商家管理-审核/停用
  check: data =>
    request({
      url: `/console/company/check`,
      method: 'post',
      data
    }),
  //  G端-商家管理-添加商家
  add: data =>
    request({
      url: `/console/company/add`,
      method: 'post',
      data
    }),
  //  G端-商家管理-添加商家
  queryByUser: data =>
    request({
      url: `/console/company/queryByUser`,
      method: 'post',
      data
    })
}
export default BUSINESS
