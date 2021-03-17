import request from '@/utils/request'

// form表单数据
export function fetchGetFormOptions(query) {
  return request({
    url: '/v1/order/options',
    method: 'get',
    params: query
  })
}
export function fetchGetFormCitys(query) {
  return request({
    url: '/citys',
    method: 'get',
    params: query
  })
}

// 城市联动
export function fetchGetArea(query) {
  return request({
    url: '/areas',
    method: 'get',
    params: query
  })
}

// 列表
export function fetchOrderList(query) {
  return request({
    url: '/v1/order/list',
    method: 'get',
    params: query
  })
}

// 查看
export function fetchOrderCheck(query) {
  return request({
    url: '/v1/order/detail',
    method: 'get',
    params: query
  })
}

// 电话录音

export function fetchVoipRecord(query) {
  return request({
    url: '/v1/voip/order_record',
    method: 'get',
    params: query
  })
}

// 录音详情
export function fetchVoipUrl(query) {
  return request({
    url: '/v1/telcenter/recordurl',
    method: 'get',
    params: query
  })
}

// 查看订单时间类表
export function fetchGetList(query) {
  return request({
    url: '/v1/orders/read_orders',
    method: 'get',
    params: query
  })
}

// 检索城市
export function fetchGetAllCity(query) {
  return request({
    url: '/citys/',
    method: 'get',
    params: query
  })
}
// 检索公司
export function FetchGetCompany(query) {
  return request({
    url: '/finduser',
    method: 'post',
    data: query
  })
}

// 补单管理
export function fetchRepairOrder(query) {
  return request({
    url: '/v1/order/remedy',
    method: 'get',
    params: query
  })
}

// 补单管理(不补，批量不补)
export function fetchRemoveRepairOrder(query) {
  return request({
    url: '/v1/order/unremedy',
    method: 'post',
    data: query
  })
}

// 补单基本信息
export function fetchRepairOrderInfo(query) {
  return request({
    url: '/v1/order/info',
    method: 'get',
    params: query
  })
}

// 已分配公司列表
export function fetchAssignOrderList(query) {
  return request({
    url: '/v1/order/company_info',
    method: 'get',
    params: query
  })
}

// 相邻公司列表
export function fetchNearByOrderList(query) {
  return request({
    url: '/v1/order/company_list',
    method: 'get',
    params: query
  })
}

// 保存分单公司
export function fetchSaveOrderCom(query) {
  return request({
    url: '/v1/order/allot',
    method: 'post',
    data: query
  })
}

// 微信通知发送记录
export function fetchGetWxLogList(query) {
  return request({
    url: '/v1/wxlog/ordersend',
    method: 'get',
    params: query
  })
}
// 城市签单汇总统计
export function fetchGetCityOrderList(query) {
  return request({
    url: '/v1/statistics/consult/count',
    method: 'get',
    params: query
  })
}

// 城市分单统计
export function fetchGetFenOrderList(query) {
  return request({
    url: '/v1/orders/orderreview',
    method: 'get',
    params: query
  })
}

// 城市分单统计导出
export function fetchGetFenOrderListExport(query) {
  return request({
    url: '/v1/orders/orderreviewouttofile',
    method: 'get',
    params: query
  })
}

// 报备不通过统计
export function fetchSaleReportUnpasslog(query) {
  return request({
    url: '/v1/sale_report/unpasslog',
    method: 'get',
    params: query
  })
}
// 城市会员统计
export function fetchGetCityVipCount(query) {
  return request({
    url: '/v1/statistics/cityvipcount',
    method: 'get',
    params: query
  })
}
export function fetchGetCityVipCountExport(query) {
  return request({
    url: '/v1/statistics/cityvipcount',
    method: 'get',
    params: query
  })
}
