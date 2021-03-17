import request from '@/utils/request'
//  公共
const COMMODITY = {
  //  获取七牛token
  getSevenCattleToken: params =>
    request({
      url: `/console/upload/getToken`,
      method: 'get',
      params
    }),
  //
  indexWithSupply: params =>
    request({
      url: `/console/general/indexWithSupply`,
      method: 'get',
      params
    })

}
export default COMMODITY
