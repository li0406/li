import request from '@/utils/request'
import domain from '../../config/config'
//  销售系统城市数据报表所有api
const SALES_CITY = {
  //  城市报表-签单排行榜
  getsignranking: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/city/getsignranking`,
      method: 'get',
      params
    }),
  //  城市报表-签单率
  getsignrate: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/city/getsignrate`,
      method: 'get',
      params
    }),
  //  城市报表-签单距离占比
  getsigndistance: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/city/getsigndistance`,
      method: 'get',
      params
    }),
  //  城市报表- 总单量/分单量/赠单量
  allcount: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/allcount`,
      method: 'get',
      params
    }),
  //  城市报表-量房率
  liangfang: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/liangfang`,
      method: 'get',
      params
    }),
  //  城市报表-订单转化
  transformingdata: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/transformingdata`,
      method: 'get',
      params
    }),
  //  城市报表-订单面积占比
  orderarearate: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/order/orderarearate`,
      method: 'get',
      params
    }),
  //  总签单量金额
  getsignmoneytotal: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/city/getsignmoneytotal`,
      method: 'get',
      params
    })
}
export default SALES_CITY
