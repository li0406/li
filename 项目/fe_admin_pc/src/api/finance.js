import request from '@/utils/request'
//  财务管理
const FINANCE = {
  // G端-财务管理-列表
  financeList: data =>
    request({
      url: `/console/finance/list`,
      method: 'post',
      data
    }),
  // G端-财务管理-详情
  financeDetail: data =>
    request({
      url: `/console/finance/detail`,
      method: 'post',
      data
    }),
  // G端-财务管理-获取转账信息/查看凭证
  getSettleInfo: data =>
    request({
      url: `/console/finance/getSettleInfo`,
      method: 'post',
      data
    }),
  // G端-财务管理-转账结算
  financeSettle: data =>
    request({
      url: `/console/finance/settle`,
      method: 'post',
      data
    }),
  // G端财务管理列表导出excel
  financeExport: data =>
    request({
      url: `/console/finance/export`,
      method: 'post',
      responseType: 'arraybuffer',
      data
    }),
  // G端财务管理获取结算对象列表
  targetList: params =>
    request({
      url: `/console/finance/targetList`,
      method: 'get',
      params
    }),
  // G端-财务管理-支付明细-列表
  transList: data =>
    request({
      url: `/console/trans/list`,
      method: 'post',
      data
    }),
  // G端-财务管理-支付明细-导出列表
  transExport: data =>
    request({
      url: `/console/trans/export`,
      method: 'post',
      responseType: 'arraybuffer',
      data
    }),
  // G端-财务管理-支付明细-开发票
  confirmOpenBill: data =>
    request({
      url: `/console/trans/confirmOpenBill`,
      method: 'post',
      data
    }),
  // G端-财务管理-支付明细-查询开发票
  openBill: data =>
    request({
      url: `/console/trans/openBill`,
      method: 'post',
      data
    }),
  // G端-财务管理-支付明细-查看凭证
  queryVoucher: data =>
    request({
      url: `/console/trans/queryVoucher`,
      method: 'post',
      data
    }),
  // G端-财务管理-支付明细-确认对账
  verify: data =>
    request({
      url: `/console/trans/verify`,
      method: 'post',
      data
    }),
  // G端-财务管理-退款明细-记录列表
  refundList: data =>
    request({
      url: `/console/balance/refundList`,
      method: 'post',
      data
    }),
  // G端-财务管理-提现申请-记录列表
  withdrawList: data =>
    request({
      url: `/console/balance/withdrawList`,
      method: 'post',
      data
    }),
  // G端-财务管理-G端查看凭证
  viewPayment: data =>
    request({
      url: `/console/balance/viewPayment`,
      method: 'post',
      data
    }),
  // G端-财务管理-G端待退款详情
  getRefundDetail: data =>
    request({
      url: `/console/balance/getRefundDetail`,
      method: 'post',
      data
    }),
  // G端-财务管理-G端确认退款
  confirmRefund: data =>
    request({
      url: `/console/balance/confirmRefund`,
      method: 'post',
      data
    }),
  // G端-财务管理-G端确认提现
  confirmWithdraw: data =>
    request({
      url: `/console/balance/confirmWithdraw`,
      method: 'post',
      data
    })
}
export default FINANCE
