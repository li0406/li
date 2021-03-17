import request from '@/utils/request'
//  公共
const RETAIL = {
  test: params =>
    request({
      url: `/v1/common/city/all`,
      method: 'get',
      params
    }),
  // 分销商城-分类商品-获取分类
  listByGoods: params =>
    request({
      url: `/console/goodsType/listByGoods`,
      method: 'get',
      params
    }),
  // 分销商城-分类商品列表
  goodsList: data =>
    request({
      url: `/console/goods/goodsList`,
      method: 'post',
      data
    }),
  // 分销商城-商品入库
  selectGoods: data =>
    request({
      url: `/console/shopGoods/selectGoods`,
      method: 'post',
      data
    }),
  // 购物车管理-添加商品
  addGoods: data =>
    request({
      url: `/console/shopCar/addV2`,
      method: 'post',
      data
    }),
  // 购物车管理-列表
  shoppingCartList: params =>
    request({
      url: `/console/shopCar/list`,
      method: 'get',
      params
    }),
  // 购物车管理-删除商品
  deleteGoods: data =>
    request({
      url: `/console/shopCar/delete`,
      method: 'post',
      data
    }),
  // 地址管理-地址列表
  queryListWithPage: data =>
    request({
      url: `/console/receiveAddress/queryListWithPage`,
      method: 'post',
      data
    }),
  // 地址管理-地址列表-查询列表
  receiveAddressList: data =>
    request({
      url: `/console/receiveAddress/list`,
      method: 'post',
      data
    }),
  // 地址管理-添加地址
  addAddress: data =>
    request({
      url: `/console/receiveAddress/add`,
      method: 'post',
      data
    }),
  // 地址管理-删除地址
  deleteAddress: data =>
    request({
      url: `/console/receiveAddress/delete`,
      method: 'post',
      data
    }),
  // 地址管理-设置默认
  updateStatus: data =>
    request({
      url: `/console/receiveAddress/updateStatus`,
      method: 'post',
      data
    }),
  // 地址管理-获取区域编码(一级）
  getByParentCode: data =>
    request({
      url: `/console/area/getByParentCode`,
      method: 'post',
      data
    }),
  // 地址管理-获取详情
  getAddressDetails: data =>
    request({
      url: `/console/receiveAddress/get`,
      method: 'post',
      data
    }),
  // 购物车管理-购物车商品spu数量
  count: params =>
    request({
      url: `/console/shopCar/count`,
      method: 'get',
      params
    })
}
export default RETAIL
