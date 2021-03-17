
import request from '@/utils/request'

// 小报备-列表页
export function fetchSmallReportList(query) {
    return request({
      url: '/v1/sale_report/payment/list',
      method: 'get',
      params: query
    })
}

// 小报备-获取编辑信息
export function fetchSmallReportEdit(query) {
  return request({
    url: '/v1/sale_report/payment/editinfo',
    method: 'get',
    params: query
  })
}

// 小报备-提交
export function fetchSmallReportSubmit(query) {
    return request({
      url: '/v1/sale_report/payment/submit',
      method: 'post',
      data: query
    })
}

// 小报备-提交撤回
export function fetchSmallReportRecall(query) {
    return request({
      url: '/v1/sale_report/payment/recall',
      method: 'post',
      data: query
    })
}

// 小报备-删除
export function fetchSmallReportDelete(query) {
    return request({
      url: '/v1/sale_report/payment/delete',
      method: 'post',
      data: query
    })
}

// 小报备-选择大报备
export function fetchBigReportList(query) {
  return request({
    url: '/v1/sale_report/payment/report_select',
    method: 'get',
    params: query
  })
}
// 小报备-关联比较数据一致性
export function fetchReportCompare(query) {
  return request({
    url: '/v1/sale_report/payment/compare',
    method: 'get',
    params: query
  })
}

// 小报备-关联操作
export function fetchSmallReportRelated(query) {
  return request({
    url: '/v1/sale_report/payment/related',
    method: 'post',
    data: query
  })
}

// 小报备-详情
export function fetchSmallReportDetail(query) {
    return request({
      url: '/v1/sale_report/payment/detail',
      method: 'get',
      params: query
    })
}

// 小报备-添加、编辑
export function fetchSmallReportSave(query) {
  return request({
    url: '/v1/sale_report/payment/save',
    method: 'post',
    data: query
  })
}

// 权限城市
export function fetchPrivcitys(query) {
  return request({
    url: '/privcitys',
    method: 'get',
    params: query
  })
}

// 其他业绩人list
export function fetchPerPerformance(query) {
  return request({
    url: '/v1/sale_report/payment/other_person_list',
    method: 'get',
    params: query
  })
}
// 返点会员订单查询

export function fetchSigninfo(query) {
  return request({
    url: '/v1/order/signinfo',
    method: 'get',
    params: query
  })
}
