import request from '@/utils/request'
import domain from '../config/config'
//  驾驶舱公共模块所有api
const COMMON_API = {
  //  城市区域-获取所有已开站城市
  getCityList: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/city/all`,
      method: 'get',
      params
    }),
  //  城市区域-获取城市管辖区域
  getAreaList: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/city/arealist`,
      method: 'get',
      params
    }),
  //  会员公司-检索公司
  getCompanySearch: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/company/search`,
      method: 'get',
      params
    }),
  //  系统菜单
  menu: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/menu`,
      method: 'get',
      params
    }),
  //  用户信息
  info: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/info`,
      method: 'get',
      params
    }),
  //  清除缓存
  clean: params =>
    request({
      url: `${domain.BASE_URL}/v1/common/clean`,
      method: 'post',
      params
    }),
  //  销售-获取顶级团队
  toplist: params =>
    request({
      url: `${domain.BASE_URL}/v1/sales/team/toplist`,
      method: 'get',
      params
    })
}
export default COMMON_API
