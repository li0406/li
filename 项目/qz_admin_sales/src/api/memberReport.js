import request from '@/utils/request'

export function fetchMemberReportList(query) {
    return request({
        url: '/v1/sale_report/baobei',
        method: 'get',
        params: query
    })
}

export function fetchMemberReportAdd(query) {
    return request({
        url: '/v1/sale_report/save',
        method: 'post',
        data: query
    })
}

export function fetchMemberReportDel(query) {
    return request({
        url: '/v1/sale_report/del',
        method: 'post',
        data: query
    })
}

export function fetchCityOfferDel(query) {
    return request({
        url: '/v1/quote/del_city_quote',
        method: 'post',
        data: query
    })
}

export function fetchOtherOfferDel(query) {
    return request({
        url: '/v1/quote/del_other_quote',
        method: 'post',
        data: query
    })
}

export function fetchMemberReportDetail(query) {
    return request({
        url: '/v1/sale_report/show',
        method: 'get',
        params: query
    })
}

export function fetchCityOfferList(query) {
    return request({
        url: '/v1/quote/get_city_quote',
        method: 'get',
        params: query
    })
}

export function fetchCityOfferDetail(query) {
    return request({
        url: '/v1/quote/find_city_quote',
        method: 'get',
        params: query
    })
}

export function fetchErpSwjOfferDetail(query) {
    return request({
        url: '/v1/quote/find_other_quote',
        method: 'get',
        params: query
    })
}

export function fetchOtherOfferList(query) {
    return request({
        url: '/v1/quote/get_other_quote',
        method: 'get',
        params: query
    })
}

export function fetchCityOfferSave(query) {
    return request({
        url: '/v1/quote/add_city_quote',
        method: 'post',
        data: query
    })
}

export function fetchOtherOfferSave(query) {
    return request({
        url: '/v1/quote/add_other_quote',
        method: 'post',
        data: query
    })
}

export function fetchOfferRecordList(query) {
    return request({
        url: '/v1/quote/get_quote_log',
        method: 'get',
        params: query
    })
}

export function fetchMemberReportLogList(query) {
    return request({
        url: '/v1/sale_report/log',
        method: 'get',
        params: query
    })
}

export function fetchMemberReportStatus(query) {
    return request({
        url: '/v1/sale_report/set_status',
        method: 'post',
        data: query
    })
}

// 根据文本查找公司名称
export function fetchFindUser(query) {
    return request({
        url: '/v1/sale_report/test_company',
        method: 'get',
        params: query
    })
}
// 需要客服上传凭证
export function fetchMemberReportSup(query) {
    return request({
        url: '/v1/sale_report/kf_voucher',
        method: 'post',
        data: query
    })
}
// 验证当前合同是否异常
export function fetchCheckContract(query) {
    return request({
        url: '/v1/sale_report/check_contract',
        method: 'post',
        data: query
    })
}

// 获取编辑筛选项
export function fetchOptions(query) {
  return request({
    url: '/v1/sale_report/options',
    method: 'get',
    params: query
  })
}
// 获取延期公司信息
export function fetchFindDelayCompany(query) {
  return request({
    url: '/v1/sale_report/find_delay_company',
    method: 'get',
    params: query
  })
}
// 获取装修公司分配订单数
export function fetchCompanyAllotOrders(query) {
  return request({
    url: '/v1/company/allot_orders',
    method: 'get',
    params: query
  })
}
