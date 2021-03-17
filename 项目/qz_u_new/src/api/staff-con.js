import request from '@/utils/request';

function accountup(data) {
  return request({
    url: `/cp/v1/employee/accountup`,
    method: 'post',
    data,
  });
}

function rolelist(query) {
  return request({
    url: `/cp/v1/employee/rolelist`,
    method: 'get',
    params: query,
  });
}

function accountinfo(query) {
  return request({
    url: `/cp/v1/employee/accountinfo`,
    method: 'get',
    params: query,
  });
}

function roleup(data) {
  return request({
    url: `/cp/v1/employee/roleup`,
    method: 'post',
    data,
  });
}

export default {
  accountup,
  rolelist,
  accountinfo,
  roleup,
};
