import request from '@/utils/request'
import domain from '../config/config'
//  公共数据分析--所有api
const PUBDATA = {
  // 城市数据明细
  cityOrderDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/city/order/detailed`,
      method: 'get',
      params
    }),
  // 城市分单数据明细
  cityFendanDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/city/fendan/detailed`,
      method: 'get',
      params
    }),
  // 城市会员明细
  cityUserDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/city/user/detailed`,
      method: 'get',
      params
    }),
  // 会员详情
  companyDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/company/detailed`,
      method: 'get',
      params
    }),
  // 会员申请补轮明细
  roundapplyDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/roundapply/detailed`,
      method: 'get',
      params
    }),
  // 渠道数据分析-筛选项
  srcOptions: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/src/options`,
      method: 'get',
      params
    }),
  // 渠道数据分析-检索来源
  srcSearch: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/src/search`,
      method: 'get',
      params
    }),
  // 渠道数据分析-渠道数据统计（按渠道/按城市）
  srcDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/src/detailed`,
      method: 'get',
      params
    }),
  // 渠道数据分析-渠道数据统计（按日期）
  srcDateDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/src/date/detailed`,
      method: 'get',
      params
    }),
  // 渠道数据分析-发单位置数据统计（按发单位置）
  sourceDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/source/detailed`,
      method: 'get',
      params
    }),
  // 渠道数据分析-发单位置数据统计（按日期）
  sourceDateDetailed: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/source/date/detailed`,
      method: 'get',
      params
    }),
  // 各部门订单转化
  departtransform: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/order/departtransform`,
      method: 'get',
      params
    }),
  // 各部门订单浪费分析
  departwaste: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/order/departwaste`,
      method: 'get',
      params
    }),
  // 城市发单浪费率
  fadanwastecity: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/order/fadanwastecity`,
      method: 'get',
      params
    }),
  // 发单时间段分布
  fadantimeanalysis: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/order/fadantimeanalysis`,
      method: 'get',
      params
    }),
  // 注册用户发单周期
  fadancycle: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/user/fadancycle`,
      method: 'get',
      params
    }),
  // 异常单分析
  abnormalanalysis: params =>
    request({
      url: `${domain.BASE_URL}/v1/pubdata/order/abnormalanalysis`,
      method: 'get',
      params
    }),
  // 账户list
  getAccountList: params =>
    request({
      url: `${domain.BASE_URL}/v1/basic/sourceaccount/accountall`,
      method: 'get',
      params
    }),
  // 上传渠道消耗金额table
  getChannelList: params =>
    request({
      url: `${domain.BASE_URL}/v1/basic/sourceaccount/expend_list`,
      method: 'get',
      params
    }),
  // 新增、编辑渠道消耗金额
  handleChannel: data =>
    request({
      url: `${domain.BASE_URL}/v1/basic/sourceaccount/expend_save`,
      method: 'post',
      data
    }),
  // 删除渠道消耗金额
  deleteChannel: data =>
    request({
      url: `${domain.BASE_URL}/v1/basic/sourceaccount/expend_delete`,
      method: 'post',
      data
    }),
  // 时长ROI数据分析---数据汇总
  getDataCollect: params =>
    request({
      url: `${domain.BASE_URL}/v1/market/sourceroi/total`,
      method: 'get',
      params
    }),
  // 时长ROI数据分析---数据明细
  getDataList: params =>
    request({
      url: `${domain.BASE_URL}/v1/market/sourceroi/detailed`,
      method: 'get',
      params
    }),
  // 时长ROI数据分析---市场ROI趋势图
  getRoiTrend: params =>
    request({
      url: `${domain.BASE_URL}/v1/market/sourceroi/trendline`,
      method: 'get',
      params
    })
}
export default PUBDATA
