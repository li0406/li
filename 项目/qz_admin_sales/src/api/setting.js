import request from '@/utils/request'

// 会员公司设置
export function fetchMemberCompany(query) {
  return request({
    url: '/v1/basicinfo/company_list',
    method: 'get',
    params: query
  })
}

// 会员公司详情
export function fetchMemberCompanyDetail(query) {
  return request({
    url: '/v1/basicinfo/company_detail',
    method: 'get',
    params: query
  })
}

// 会员公司编辑
export function fetchMemberCompanyEdit(query) {
  return request({
    url: '/v1/basicinfo/company_edit',
    method: 'post',
    data: query
  })
}

// 质检分单城市设置保存
export function fetchAuditCitySave(query) {
  return request({
    url: '/v1/basicinfo/salecity_edit',
    method: 'post',
    data: query
  })
}

// 会员公司标识-列表
export function fetchMemberIdentify(query) {
  return request({
    url: '/v1/basicinfo/company_tag_list',
    method: 'get',
    params: query
  })
}

// 会员公司标识-固定标识选择
export function fetchMemberIdentifyEdit(query) {
  return request({
    url: '/v1/basicinfo/company_tag',
    method: 'get',
    params: query
  })
}

// 会员公司标识-添加公司标识
export function fetchMemberIdentifySave(query) {
  return request({
    url: '/v1/basicinfo/company_tag_add',
    method: 'post',
    data: query
  })
}
// 查询城市
export function fetchCityList(query) {
  return request({
    url: '/getadmincitys/',
    method: 'get',
    params: query
  })
}
// code
export function fetchVerifyimg(query) {
  return request({
    url: '/verifyimg',
    method: 'get',
    params: query
  })
}

// 查询用户名是否被占用
export function fetchCheckUser(query) {
  return request({
    url: '/v1/company/check_user',
    method: 'get',
    params: query
  })
}

// 注册装修公司
export function fetchRegister(query) {
  return request({
    url: '/v1/company/register',
    method: 'post',
    data: query
  })
}

// 会员公司设置-批量开启号码保护
export function fetchPnp(query) {
  return request({
    url: '/v1/basicinfo/company_pnp_on',
    method: 'post',
    data: query
  })
}
