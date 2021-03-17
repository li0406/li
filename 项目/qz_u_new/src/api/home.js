import request from '@/utils/request';

// 首页-头部-新单提醒
function newnums() {
  return request({
    url: '/cp/v1/order/newnums',
    method: 'get',
  });
}

// 首页-详情
function home() {
  return request({
    url: '/cp/v1/home',
    method: 'get',
  });
}

// 首页-最新城市签单动态
function qianDan(query) {
  return request({
    url: '/cp/v1/home/qiandan',
    method: 'get',
    params: query,
  });
}

// 首页-最新操作记录
function operation(query) {
  return request({
    url: '/cp/v1/home/operation',
    method: 'get',
    params: query,
  });
}

// 首页-最新申请量房动态
function liangFang(query) {
  return request({
    url: '/cp/v1/home/liangfang',
    method: 'get',
    params: query,
  });
}

// 首页-退出操作
function logout() {
  return request({
    url: '/cp/v1/logout',
    method: 'post',
  });
}

// 首页-验证商家安全手机是否存在
function saleTel(query) {
  return request({
    url: '/cp/v1/company/check_sale_tel',
    method: 'get',
    params: query,
  });
}

// 首页-验证用户手机弱密码检测
function pwdcheck(query) {
  return request({
    url: '/cp/v1/personal/pwdcheck',
    method: 'get',
    params: query,
  });
}

// 用户修改密码
function pwdup(data) {
  return request({
    url: '/cp/v1/personal/pwdup',
    method: 'post',
    data,
  });
}

//  首页-排行/抢单/完善度信息
function companyInfo(query) {
  return request({
    url: '/cp/v1/home/company_info',
    method: 'get',
    params: query,
  });
}

//  首页-刷新排行数据
function companySort(query) {
  return request({
    url: '/cp/v1/home/company_sort',
    method: 'get',
    params: query,
  });
}
//  用户面板-保产值数据概览
function guaranteed(query) {
  return request({
    url: '/cp/v1/home/guaranteed',
    method: 'get',
    params: query,
  });
}

export default {
  newnums,
  home,
  liangFang,
  operation,
  qianDan,
  logout,
  saleTel,
  pwdcheck,
  pwdup,
  companyInfo,
  companySort,
  guaranteed
};
