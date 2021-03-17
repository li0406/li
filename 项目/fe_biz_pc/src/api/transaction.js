import request from '@/utils/request'
//  交易管理
const TRANSACTION = {
  // B端-交易列表
  chargeList: data =>
    request({
      url: `/console/trans/chargeList`,
      method: 'post',
      data
    }),
  // B端-商户提现申请记录列表
  getShopWithdrawList: params =>
    request({
      url: `/console/balance/getShopWithdrawList`,
      method: 'get',
      params
    }),
  // B端-提现申请详情
  getUserBalanceDetail: params =>
    request({
      url: `/console/balance/getUserBalanceDetail`,
      method: 'get',
      params
    }),
  // B端-提现申请
  withdrawApply: data =>
    request({
      url: `/console/balance/withdrawApply`,
      method: 'post',
      data
    }),
  // B端-交易管理-银行卡列表
  shopBankList: params =>
    request({
      url: `/console/shopBank/list`,
      method: 'get',
      params
    }),
  // B端-订单管理-银行卡添加
  addShopBank: data =>
    request({
      url: `/console/shopBank/addShopBank`,
      method: 'post',
      data
    }),
  // B端-订单管理-银行卡删除
  deleteShopBank: data =>
    request({
      url: `/console/shopBank/deleteShopBank`,
      method: 'post',
      data
    })
}
export default TRANSACTION
