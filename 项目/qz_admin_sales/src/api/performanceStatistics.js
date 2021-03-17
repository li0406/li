import request from '@/utils/request'

export function getTeamList(query) {
  return request({
    url: '/v1/team/select',
    method: 'get',
    params: query
  })
}
export function getTeamChart(query) {
  return request({
    url: '/v1/team/getteamchart',
    method: 'get',
    params: query
  })
}
// 今日业绩
export function getperformancechart(query) {
  return request({
    url: '/v1/team/getperformancechart',
    method: 'get',
    params: query
  })
}

// 今日业绩查询
export function getToday(query) {
  return request({
    url: '/v1/team/get_daily_achievement',
    method: 'get',
    params: query
  })
}

// 会员总览
export function getmemberchart(query) {
  return request({
    url: '/v1/team/getmemberchart',
    method: 'get',
    params: query
  })
}
// 团队总览
export function getteamindicatorschart(query) {
  return request({
    url: '/v1/team/getteamindicatorschart',
    method: 'get',
    params: query
  })
}
// 其他数据
export function getotherchart(query) {
  return request({
    url: '/v1/team/getotherchart',
    method: 'get',
    params: query
  })
}

// 个人业绩统计--导出
export function fetchMemberExport(query) {
  return request({
    url: '/v1/indicators/payment/saler/export',
    method: 'get',
    params: query
  })
}

// 个人业绩统计--部门和团队list
export function fetchTeamList(query) {
  return request({
    url: '/v1/team/select',
    method: 'get',
    params: query
  })
}

// 个人业绩统计list
export function fetchPerformList(query) {
  return request({
    url: '/v1/indicators/payment/saler',
    method: 'get',
    params: query
  })
}

// 业绩明细统计list
export function fetchPerformDetailList(query) {
  return request({
    url: '/v1/indicators/payment/detailed',
    method: 'get',
    params: query
  })
}

// 业绩明细统计--导出
export function fetchPerformExport(query) {
  return request({
    url: '/v1/indicators/payment/detailed/export',
    method: 'get',
    params: query
  })
}
// 业绩明细统计--合计
export function fetchPaymentTotal(query) {
  return request({
    url: '/v1/indicators/payment/sum',
    method: 'get',
    params: query
  })
}

// 其他业绩人 list
export function fetchPersonList(query) {
  return request({
    url: '/v1/sale_report/payment/other_person_list',
    method: 'get',
    params: query
  })
}

// 团队管理 list
export function fetchSelect(query) {
  return request({
    url: '/v1/team/select',
    method: 'get',
    params: query
  })
}

// 收款方列表 list
export function fetchPayeelist(query) {
  return request({
    url: '/v1/sale_report/payment/options',
    method: 'get',
    params: query
  })
}
