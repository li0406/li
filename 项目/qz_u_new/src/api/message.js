import request from '@/utils/request';

function notices(query) {
  return request({
    url: `/cp/v1/company/notices`,
    method: 'get',
    params: query,
  });
}

function delNotice(data) {
  return request({
    url: `/cp/v1/company/del_notice`,
    method: 'post',
    data,
  });
}

//  消息中心-消息列表
function list(query) {
  return request({
    url: `/cp/v1/msg/list`,
    method: 'get',
    params: query,
  });
}

//  消息中心-标记已读
function read(data) {
  return request({
    url: `/cp/v1/msg/read`,
    method: 'post',
    data,
  });
}

//  消息中心-消息删除
function deleteMsg(data) {
  return request({
    url: `/cp/v1/msg/delete`,
    method: 'post',
    data,
  });
}

//  消息中心-未读消息数量
function unreadnum(query) {
  return request({
    url: `/cp/v1/msg/unreadnum`,
    method: 'get',
    params: query,
  });
}

export default {
  notices,
  delNotice,
  list,
  read,
  deleteMsg,
  unreadnum
};
