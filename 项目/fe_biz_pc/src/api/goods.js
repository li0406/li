import request from '@/utils/request'
//  公共
const GOODS = {
  test: params =>
    request({
      url: `/v1/common/city/all`,
      method: 'get',
      params
    }),
  // 商品管理-首页
  list: data =>
    request({
      url: `/console/shopGoods/list`,
      method: 'post',
      data
    }),
  // 商品管理-商品设置销售价/商品上下架
  update: data =>
    request({
      url: `/console/shopGoodsDetail/update`,
      method: 'post',
      data
    }),
  // 商品管理-批量上下架
  updateGoodsStatus: data =>
    request({
      url: `/console/shopGoodsDetail/updateGoodsStatus`,
      method: 'post',
      data
    }),
  // 商品管理-删除商品(支持批量)
  delete: data =>
    request({
      url: `/console/shopGoodsDetail/delete`,
      method: 'post',
      data
    }),
  // 商品管理-商品池-商品详情
  getDetail: data =>
    request({
      url: `/console/goods/getDetail`,
      method: 'post',
      data
    }),
  // 商品管理-商品详情
  getDetailBySku: data =>
    request({
      url: `/console/goods/getDetailBySku`,
      method: 'post',
      data
    }),
  // 商品管理-获取多商品规格信息
  getDetailBySkuList: data =>
    request({
      url: `/console/goods/getDetailBySkuList`,
      method: 'post',
      data
    }),
  // 商品管理-商品详情
  goodsDetail: data =>
    request({
      url: `/console/shopGoods/goodsDetail`,
      method: 'post',
      data
    })
}
export default GOODS
