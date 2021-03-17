import request from '@/utils/request'
//  订单
const ORDER = {
  // G端-订单管理-订单列表
  orderList: data =>
    request({
      url: `/console/order/list`,
      method: 'post',
      data
    }),
  // G端-订单管理-导出订单列表
  exportOrderList: data =>
    request({
      url: `/console/order/exportOrderList`,
      method: 'post',
      responseType: 'arraybuffer',
      data
    }),
  // G端-订单管理-订单详情
  orderDetail: data =>
    request({
      url: `/console/order/detail`,
      method: 'post',
      data
    }),
  // G端-订单管理-订单取消
  orderCancel: data =>
    request({
      url: `/console/order/cancel`,
      method: 'post',
      data
    }),
  // G端-订单管理-获取收货信息
  getReceive: data =>
    request({
      url: `/console/order/getReceive`,
      method: 'post',
      data
    }),
  // G端-订单管理-确认收货
  orderConfirmReceive: data =>
    request({
      url: `/console/order/confirmReceive`,
      method: 'post',
      data
    }),
  // G端-订单管理-申请退款
  orderRefundApply: data =>
    request({
      url: `/console/order/refundApply`,
      method: 'post',
      data
    }),
  // G端-订单管理-关闭订单
  orderClose: data =>
    request({
      url: `/console/order/close`,
      method: 'post',
      data
    }),
  // G端-订单管理-退款列表
  orderRefundList: data =>
    request({
      url: `/console/order/refundList`,
      method: 'post',
      data
    }),
  // G端-订单管理-修改收货人地址
  orderUpdateReceive: data =>
    request({
      url: `/console/order/updateReceive`,
      method: 'post',
      data
    }),
  orderList2: data =>
    request({
      url: `/console/goods/list`,
      method: 'post',
      data
    }),
  // G端-退换货管理-列表
  exchangeList: data =>
    request({
      url: `/console/exchange/list`,
      method: 'post',
      data
    }),
  // G端-退换货管理-导出列表
  exchangeExport: data =>
    request({
      url: `/console/exchange/export`,
      method: 'post',
      responseType: 'arraybuffer',
      data
    }),
  // G端-退换货管理-列表
  confirmReturn: data =>
    request({
      url: `/console/exchange/confirmReturn`,
      method: 'post',
      data
    })
}
export default ORDER
