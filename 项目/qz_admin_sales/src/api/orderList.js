import request from '@/utils/request'

//回访订单-获取新增选项
export function getAddOptions(query) {
    return request({
        url: '/v1/visit/add_options',
        method: 'get',
        params: query
    })
}
//回访订单-新增回访单
export function fetchAddVisit(query) {
    return request({
        url: '/v1/visit/add',
        method: 'post',
        data: query
    })
}
//回访订单-列表
export function getVisitList(query) {
    return request({
        url: '/v1/visit/list',
        method: 'get',
        params: query
    })
}
//回访订单-获取列表查询选项
export function getVisitOptions(query) {
    return request({
        url: '/v1/visit/options',
        method: 'get',
        params: query
    })
}

//回访订单-详情
export function getVisitDetails(query) {
    return request({
        url: '/v1/visit/detail',
        method: 'get',
        params: query
    })
}

//回访订单-撤回
export function fetchVisitDelete(query) {
    return request({
        url: '/v1/visit/delete',
        method: 'post',
        data: query
    })
}
//回访订单-推送
export function fetchVisitPush(query) {
    return request({
        url: '/v1/visit/push',
        method: 'post',
        data: query
    })
}

//回访订单-新推送
export function fetchVisitNewPush(query) {
  return request({
    url: '/v1/visit/new_push',
    method: 'post',
    data: query
  })
}

//回访订单-导出
export function getVisitExport(query) {
    return request({
        url: '/v1/visit/export',
        method: 'get',
        data: query
    })
}

// 获取预算列表
export function getYuSuan(query) {
  return request({
    url: '/yusuan',
    method: 'get',
    params: query
  })
}

// 获取装修方式列表
export function getFangShi(query) {
  return request({
    url: '/fangshi',
    method: 'get',
    params: query
  })
}

// 城市订单统计-列表
export function getCityInfo(query) {
  return request({
    url: '/v1/statistics/order/city',
    method: 'get',
    params: query
  })
}
