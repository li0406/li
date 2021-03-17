import request from '@/utils/request'
//  公共
const COMMON = {
  test: params =>
    request({
      url: `/v1/common/city/all`,
      method: 'get',
      params
    }),
  //  获取七牛token
  getSevenCattleToken: params =>
    request({
      url: `/console/upload/getToken`,
      method: 'get',
      params
    }),
  // 地址管理-获取区域编码(一级）
  getByParentCode: data =>
    request({
      url: `/console/area/getByParentCode`,
      method: 'post',
      data
    }),
  // 获取城市数据
  getCityList: params =>
    request({
      url: `/console/area/getAllList`,
      method: 'get',
      params
    })
}
export default COMMON
