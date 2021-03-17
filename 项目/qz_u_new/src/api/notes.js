import request from '@/utils/request';

function getlist(query) {
  return request({
    url: `/cp/v1/comment/getlist`,
    method: 'get',
    params: query,
  });
}

function commentup(query) {
  return request({
    url: `/cp/v1/comment/commentup`,
    method: 'get',
    params: query,
  });
}

function recomentup(data) {
  return request({
    url: `/cp/v1/comment/recomentup`,
    method: 'post',
    data,
  });
}

function delreply(data) {
  return request({
    url: `/cp/v1/comment/delreply`,
    method: 'post',
    data,
  });
}


function replyup(data) {
  return request({
    url: `/cp/v1/comment/replyup`,
    method: 'post',
    data,
  });
}

export default {
  getlist,
  commentup,
  recomentup,
  delreply,
  replyup,
};
