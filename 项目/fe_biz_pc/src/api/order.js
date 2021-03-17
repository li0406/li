import request from '@/utils/request'
//  订单
const ORDER = {
  // B端-订单管理-订单列表
  orderList: data =>
    request({
      url: `/console/order/listV2`,
      method: 'post',
      data
    }),
  // B端-兑换订单列表
  recordList: data =>
    request({
      url: `/console/shopExchange/recordList`,
      method: 'post',
      data
    }),
  // B端-兑换记录列表
  recordListV2: data =>
    request({
      url: `/console/shopExchange/recordListV2`,
      method: 'post',
      data
    }),
  // B端-验证兑换码
  checkExchangeNo: data =>
    request({
      url: `/console/shopExchange/checkExchangeNo`,
      method: 'post',
      data
    }),
  // B端-获取兑换码
  getExchangeNo: data =>
    request({
      url: `/console/shopExchange/getExchangeNo`,
      method: 'post',
      data
    }),
  // B端-B端订单生成
  orderCreateV2: data =>
    request({
      url: `/console/order/createV2`,
      method: 'post',
      data
    }),
  // B端-待结算订单
  orderSettle: params =>
    request({
      url: `/console/order/settle`,
      method: 'get',
      params
    }),
  // B端-订单支付
  orderPay: data =>
    request({
      url: `/console/order/pay`,
      method: 'post',
      data
    }),
  // B端-订单取消
  orderCancel: data =>
    request({
      url: `/console/order/cancel`,
      method: 'post',
      data
    }),
  // B端-确认收货
  confirmReceive: data =>
    request({
      url: `/console/order/confirmReceive`,
      method: 'post',
      data
    }),
  // B端-确认收货
  confirmSend: data =>
    request({
      url: `/console/order/confirmSend`,
      method: 'post',
      data
    }),
  // B端-确认收货
  confirmWaitSend: data =>
    request({
      url: `/console/order/confirmWaitSend`,
      method: 'post',
      data
    })
}
export default ORDER
