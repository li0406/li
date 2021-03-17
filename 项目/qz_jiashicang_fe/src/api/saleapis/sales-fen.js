import request from '@/utils/request'
import domain from '../../config/config'
//  销售系统分单数据报表所有api
const SALES_FEN = {
  // 分单数据-申请补轮量
  applyorder: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/applyorder`,
      method: 'get',
      params
    }),
  // 分单数据-实际已补轮数量
  applypassorder: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/applypassorder`,
      method: 'get',
      params
    }),
  // 分单数据-补轮通过率图表
  applypassorderfull: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/applypassorderfull`,
      method: 'get',
      params
    }),
  // 分单数据-分单浪费率
  orderwaste: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderwaste`,
      method: 'get',
      params
    }),
  // 分单数据-分单满足率
  orderfill: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderfill`,
      method: 'get',
      params
    }),
  // 分单数据-订单撤回详情
  orderrebutdetail: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderrebutdetail`,
      method: 'get',
      params
    }),
  // 分单数据-违规补轮详情
  violateapplydetail: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/violateapplydetail`,
      method: 'get',
      params
    }),
  // 订单撤回次数
  orderrebut: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderrebut`,
      method: 'get',
      params
    }),
  // 违规补轮次数
  violateapply: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/violateapply`,
      method: 'get',
      params
    }),
  // 分单数据-分单客单价
  orderprice: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderprice`,
      method: 'get',
      params
    }),
  // 分单平均分配次数
  distributeorder: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/distributeorder`,
      method: 'get',
      params
    }),
  // 总消耗金额
  consumptiontotal: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/consumptiontotal`,
      method: 'get',
      params
    })
}
export default SALES_FEN
