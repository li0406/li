import request from '@/utils/request';

// 分销商城-入驻详情
function applyinfo(query) {
  return request({
    url: `/cp/v1/decoshop/applyinfo`,
    method: 'get',
    params: query,
  });
}

// 分销商城-申请入驻（再次申请）
function applyadd() {
  return request({
    url: '/cp/v1/decoshop/applyadd',
    method: 'post',
  });
}

export default {
  applyinfo,
  applyadd,
};
