import request from '@/utils/request';

// 网店管理-装修案例/3d效果图-列表
function list(query) {
  return request({
    url: `/cp/v1/case/list`,
    method: 'get',
    params: query,
  });
}

// 网店管理-装修案例-搜索条件
function searchParams() {
  return request({
    url: '/cp/v1/case/search_params',
    method: 'get',
  });
}

// 网店管理-装修案例-装修风格
function fengGe() {
  return request({
    url: '/cp/v1/service/fengge',
    method: 'get',
  });
}

// 网店管理-装修案例-户型
function huXing() {
  return request({
    url: '/cp/v1/service/huxing',
    method: 'get',
  });
}

// 网店管理-装修案例-合同总价
function jiaGe() {
  return request({
    url: '/cp/v1/service/jiage',
    method: 'get',
  });
}
// 网店管理-装修案例-获取设计师下拉菜单
function getemployeedropdowlist(query) {
  return request({
    url: '/cp/v1/common/getemployeedropdowlist',
    method: 'get',
    params: query,
  });
}

// 网店管理-装修案例-类型(服务)
function leiXing(query) {
  return request({
    url: '/cp/v1/service/leixing',
    method: 'get',
    params: query,
  });
}

// 网店管理-装修公司-获取规模
function guiMo() {
  return request({
    url: '/cp/v1/service/guimo',
    method: 'get',
  });
}

// 网店管理-装修案例-删除
function caseDel(data) {
  return request({
    url: '/cp/v1/case/del',
    headers: {
      'Content-Type': 'application/json'
    },
    method: 'post',
    data,
  });
}

// 网店管理-装修案例-添加/编辑-设计师
function designers() {
  return request({
    url: '/cp/v1/company/designers',
    method: 'get',
  });
}

// 网店管理-装修案例-添加/编辑-获取详情
function caseGet(query) {
  return request({
    url: '/cp/v1/case/get',
    method: 'get',
    params: query,
  });
}

// 网店管理-装修案例-添加/编辑-搜索小区
function xiaoQuSearch() {
  return request({
    url: '/cp/v1/xiaoqu/search',
    method: 'get',
  });
}

// ISSUE: post 方法需要传数据统一使用 json
// 网店管理-装修案例/3d效果图-添加/编辑-保存
function caseSave(data) {
  return request({
    url: '/cp/v1/case/save',
    method: 'post',
    headers: {
      'Content-Type': 'application/json'
    },
    data,
  });
}

export default {
  list,
  searchParams,
  fengGe,
  huXing,
  jiaGe,
  getemployeedropdowlist,
  leiXing,
  guiMo,
  caseDel,
  designers,
  caseGet,
  xiaoQuSearch,
  caseSave,
};
