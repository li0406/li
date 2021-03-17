import request from '@/utils/request'
import domain from '../../config/config'
//  销售系统会员数据报表所有api
const SALES_MEMBER = {
  // 会员总数量获取
  getuseramount: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuseramount`,
      method: 'get',
      params
    }),
  // 会员数据-会员数量变化趋势
  membershipchange: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/membershipchange`,
      method: 'get',
      params
    }),
  // 会员数据-会员续费率
  membershiprenewal: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/membershiprenewal`,
      method: 'get',
      params
    }),
  // 会员客单价
  getusercustomerprice: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getusercustomerprice`,
      method: 'get',
      params
    }),
  // 会员数差距
  getuserexceptgap: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuserexceptgap`,
      method: 'get',
      params
    }),
  // 会员总消耗金额
  getuserconsumptiontotal: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuserconsumptiontotal`,
      method: 'get',
      params
    }),
  // 1.0会员
  getuseramountv1: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuseramountv1`,
      method: 'get',
      params
    }),
  // 2.0会员
  getuseramountv2: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuseramountv2`,
      method: 'get',
      params
    }),
  // 1.0会员消耗金额
  getuserconsumptionv1: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuserconsumptionv1`,
      method: 'get',
      params
    }),
  // 2.0会员消耗金额
  getuserconsumptionv2: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getuserconsumptionv2`,
      method: 'get',
      params
    }),
  // 会员数据- 会员概览
  membershipoverview: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/membershipoverview`,
      method: 'get',
      params
    }),
  // 1.0装企投入产出比
  getinputandoutputv1: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getinputandoutputv1`,
      method: 'get',
      params
    }),
  // 2.0装企投入产出比
  getinputandoutputv2: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getinputandoutputv2`,
      method: 'get',
      params
    }),
  // 1.0会员客单价
  getusercustomerpricev1: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getusercustomerpricev1`,
      method: 'get',
      params
    }),
  // 2.0会员客单价
  getusercustomerpricev2: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/user/getusercustomerpricev2`,
      method: 'get',
      params
    })
}
export default SALES_MEMBER
