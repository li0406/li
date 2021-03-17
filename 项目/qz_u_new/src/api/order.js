import request from '@/utils/request';

// 订单管理-订单跟进-新增/编辑
function trackEdit(data) {
  return request({
    url: '/cp/v1/order/track_edit',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 订单管理-微信-验证用户扫描订单二维码
function checkOrderWechat() {
  return request({
    url: '/cp/v1/order/check_order_wechat',
    method: 'get',
  });
}

// 订单管理-微信-验证用户扫描绑定二维码
function checkBindWechat() {
  return request({
    url: '/cp/v1/order/check_bind_wechat',
    method: 'get',
  });
}

// 订单管理-生成查看订单二维码
function showQrCode() {
  return request({
    url: '/cp/v1/order/show_qrcode',
    method: 'get',
  });
}

// 订单管理-修改订单密码
function editPwd(data) {
  return request({
    url: '/cp/v1/order/edit_pwd',
    method: 'post',
    headers: { 'Content-Type': 'application/json' },
    data,
  });
}

// 订单管理-微信接收订单-绑定列表
function wechat(query) {
  return request({
    url: '/cp/v1/order/wechat',
    method: 'get',
    params: query,
  });
}

// 订单管理-微信接收订单-删除
function wechatDel(data) {
  return request({
    url: '/cp/v1/order/wechat_del',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 订单管理-微信接收订单-生成绑定二维码
function showBindQrcode(query) {
  return request({
    url: '/cp/v1/order/show_bind_qrcode',
    method: 'get',
    params: query,
  });
}

// 订单管理-订单详情
function detail(query) {
  return request({
    url: '/cp/v1/order/detail',
    method: 'get',
    params: query,
  });
}

// 订单管理-列表
function list(query) {
  return request({
    url: '/cp/v1/order',
    method: 'get',
    params: query,
  });
}
// 1.0订单管理-列表
function listv1(query) {
  return request({
    url: '/cp/v1/order/list',
    method: 'get',
    params: query,
  });
}

// 订单管理-列表-编辑订单状态
function changeState(data) {
  return request({
    url: '/cp/v1/order/change_state',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 订单管理-订单跟进情况
function trackList(query) {
  return request({
    url: '/cp/v1/order/track_list',
    method: 'get',
    params: query,
  });
}

// 订单管理-订单跟进-删除
function trackDel(data) {
  return request({
    url: '/cp/v1/order/track_del',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 订单管理-密码查看订单
function checkPwd(data) {
  return request({
    url: '/cp/v1/order/check_pwd',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

//  订单管理-申请补轮-列表
function roundApplyList(params){
  return request({
    url: '/cp/v1/order/round_apply_list',
    method: 'get',
    params,
  });
}
//  订单管理-申请补轮-申请补轮检查
function roundApplyCheck(params){
  return request({
    url: '/cp/v1/order/round_apply_check',
    method: 'get',
    params,
  });
}
//  订单管理-申请补轮-申请补轮
function roundApplySubmit(data){
  return request({
    url: '/cp/v1/order/round_apply_submit',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

function getemployeedropdowlist(params){
  return request({
    url: '/cp/v1/common/getemployeedropdowlist',
    method: 'get',
    params,
  });
}

function designated(data) {
  return request({
    url: '/cp/v1/order/designated',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 列表页
function robList(params){
  return request({
    url: '/cp/v1/order/rob/list',
    method: 'get',
    params,
  });
}

// 详情页
function robDetail(params){
  return request({
    url: '/cp/v1/order/rob/orderdetail',
    method: 'get',
    params,
  });
}

// 抢单
function roborder(data) {
  return request({
    url: '/cp/v1/order/rob/roborder',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

// 不抢
function norob(data) {
  return request({
    url: '/cp/v1/order/rob/norob',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}
// 订单管理-申请补轮-撤消
function roundApplyCancel(data) {
  return request({
    url: '/cp/v1/order/round_apply_cancel',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}
// 订单管理-指派人员列表
function designatedList(query) {
  return request({
    url: '/cp/v1/order/designated_list',
    method: 'get',
    params: query,
  });
}
// 订单管理-取消指派
function designatedCancel(data) {
  return request({
    url: '/cp/v1/order/designated_cancel',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}
// 订单管理-列表-编辑相关服务信息
function changeService(data) {
  return request({
    url: '/cp/v1/order/change_service',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}
// 订单管理-订单跟进-标记回访已读
function visitRead(data) {
  return request({
    url: '/cp/v1/order/visit_read',
    headers: { 'Content-Type': 'application/json' },
    method: 'post',
    data,
  });
}

export default {
  trackEdit,
  checkOrderWechat,
  checkBindWechat,
  showQrCode,
  editPwd,
  wechat,
  wechatDel,
  showBindQrcode,
  detail,
  list,
  changeState,
  trackList,
  trackDel,
  checkPwd,
  roundApplyList,
  roundApplyCheck,
  roundApplySubmit,
  getemployeedropdowlist,
  designated,
  robList,
  robDetail,
  roborder,
  norob,
  roundApplyCancel,
  designatedList,
  designatedCancel,
  changeService,
  visitRead,
  listv1
};
