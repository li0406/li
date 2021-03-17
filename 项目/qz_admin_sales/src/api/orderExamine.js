import request from '@/utils/request'

// 签单审核列表
export function fetchOrderExamineList(query) {
  return request({
    url: '/v1/orders/qiandan',
    method: 'get',
    params: query
  })
}
// 不通过列表
export function fetchOrderNoList(query) {
  return request({
    url: '/v1/orders/qiandan_no_pass',
    method: 'get',
    params: query
  })
}

// 签单订单返点记录
export function fetchReportPaymentList(query) {
  return request({
    url: '/v1/sale_report/orderback/report_payment_list',
    method: 'get',
    params: query
  })
}
// 详情
export function fetchDetail(query) {
  return request({
    url: '/v1/orders/qiandan_detail',
    method: 'get',
    params: query
  })
}

// 审核
export function FetchOrdersQdup(query) {
  return request({
    url: '/v1/orders/qdup',
    method: 'post',
    data: query
  })
}
