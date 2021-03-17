import request from '@/utils/request'
const COMMODITY = {
  //  G端-获取有效分类
  getGoodsTypes: params =>
    request({
      url: `/console/goodsType/getGoodsTypes`,
      method: 'get',
      params
    }),
  // G端-商品管理-添加、更新商品信息
  goodsSave: data =>
    request({
      url: `/console/goods/save`,
      method: 'post',
      data
    })
}

export default COMMODITY
