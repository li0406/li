import request from '@/utils/request'
import domain from '../../config/config'
// 销售中心驾驶舱
const SALES_MAIN = {
  // 全国城市重点数据
  cityImportant: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/important`,
      method: 'get',
      params
    }),
  // 当月城市缺单TOP20
  cityOrderlack: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/orderlack`,
      method: 'get',
      params
    }),
  // 城市订单价格不足TOP20
  priceinsuff: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/order/priceinsuff`,
      method: 'get',
      params
    }),
  // 签约企业分析
  signvipanalysis: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/user/signvipanalysis`,
      method: 'get',
      params
    }),
  // 成本效益图-业绩相关战区筛选数据
  achievementOptions: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/achievement/options`,
      method: 'get',
      params
    }),
  // 成本效益图-业绩月趋势图
  monthtrend: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/achievement/monthtrend`,
      method: 'get',
      params
    }),
  // 成本效益图-人均产出
  outputavg: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/achievement/outputavg`,
      method: 'get',
      params
    }),
  // 成本效益图-平均业绩档位
  gradeavg: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/achievement/gradeavg`,
      method: 'get',
      params
    }),
  // 订单问题反馈图-发单浪费率
  fawaste: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/order/fawaste`,
      method: 'get',
      params
    }),
  // 订单问题反馈图-分单浪费率
  fenwaste: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/order/fenwaste`,
      method: 'get',
      params
    }),
  // 城市企业潜力分析
  cityCitypotential: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/citypotential`,
      method: 'get',
      params
    }),
  // 各省份已签企业情况
  sfsignanalysis: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/city/sfsignanalysis`,
      method: 'get',
      params
    }),
  // 企业续费率
  renewanalysis: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/user/renewanalysis`,
      method: 'get',
      params
    }),
  // 企业量房/签单走势图
  liangfangandandsign: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/order/liangfangandandsign`,
      method: 'get',
      params
    }),
  // 销售中心驾驶舱中心板块
  departachievement: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/main/order/departachievement`,
      method: 'get',
      params
    })
}
export default SALES_MAIN
