import request from '@/utils/request';

// 财务管理-轮单明细
function account(query) {
  return request({
    url: `/cp/v1/finance/account`,
    method: 'get',
    params: query,
  });
}

function deposit(query) {
  return request({
    url: `/cp/v1/finance/deposit`,
    method: 'get',
    params: query,
  });
}

export default {
  account,
  deposit,
};
