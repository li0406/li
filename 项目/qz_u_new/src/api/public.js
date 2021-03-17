import request from '@/utils/request';
import requestTwo from '@/utils/requestTwo';

// 极验-API1验证
function geeTestApi1(query) {
  return request({
    url: `/cp/v1/geetestapi1`,
    method: 'get',
    params: query,
  });
}

// 极验-API2验证
function geeTestApi2(data) {
  return request({
    url: `/cp/v1/geetestapi2`,
    method: 'post',
    data,
  });
}

// 发送验证码
function smsSend(data) {
  return request({
    url: `/cp/v1/sms/send`,
    method: 'post',
    data,
  });
}

// 检测该手机发送的验证码是否正确
function smsCheck(data) {
  return request({
    url: `/cp/v1/sms/check`,
    method: 'post',
    data,
  });
}

// 装修公司-根据城市获取区域
function getAreaInfoByCityId(query) {
  return requestTwo({
    url: `/v1/compant/getareainfobycityid`,
    method: 'get',
    params: query,
  });
}

// 网店管理-装修公司-获取详情
function companyGet() {
  return request({
    url: `/cp/v1/company/get`,
    method: 'get',
  });
}

// 24.账号管理-消息中心-获取未读的消息数量
function companyNoticeCount(query) {
  return request({
    url: `/cp/v1/msg/unreadnum`,
    method: 'get',
    params: query,
  });
}

// 动态菜单
function getMenu(query) {
  return request({
    url: `/cp/v1/common/getmenu`,
    method: 'get',
    params: query,
  });
}

// 获取账号信息
function getBasicinfo(query) {
  return request({
    url: `/cp/v1/common/basicinfo`,
    method: 'get',
    params: query,
  });
}

// 员工帐号未设置电话提醒
function unbindtelnotice(query) {
  return request({
    url: `/cp/v1/common/unbindtelnotice`,
    method: 'get',
    params: query,
  });
}

export default {
  geeTestApi1,
  geeTestApi2,
  smsSend,
  smsCheck,
  getAreaInfoByCityId,
  companyGet,
  companyNoticeCount,
  getMenu,
  getBasicinfo,
  unbindtelnotice
};
