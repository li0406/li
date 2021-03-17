import request from '@/utils/request';

function geetestapi1(query) {
  return request({
    url: `/cp/v1/geetestapi1`,
    method: 'get',
    params: query,
  });
}

function geetestapi2(data) {
  return request({
    url: `/cp/v1/geetestapi2`,
    method: 'post',
    data,
  });
}

function getSendCode(data) {
  return request({
    url: `/cp/v1/sms/send`,
    method: 'post',
    data,
  });
}

function get(query) {
  return request({
    url: `/cp/v1/company/get`,
    method: 'get',
    params: query,
  });
}

function check(data) {
  return request({
    url: `/cp/v1/sms/check`,
    method: 'post',
    data,
  });
}

function bind(data) {
  return request({
    url: `/cp/v1/company/bind`,
    method: 'post',
    data,
  });
}

function bindFirst(data) {
  return request({
    url: `/cp/v1/company/bind_first`,
    method: 'post',
    data,
  });
}

function getCenter(query) {
  return request({
    url: `/cp/v1/personal/center`,
    method: 'get',
    params: query,
  });
}

function accountup(data) {
  return request({
    url: `/cp/v1/personal/accountup`,
    method: 'post',
    data,
  });
}


export default {
  geetestapi1,
  geetestapi2,
  getSendCode,
  get,
  check,
  bind,
  bindFirst,
  getCenter,
  accountup,
};
