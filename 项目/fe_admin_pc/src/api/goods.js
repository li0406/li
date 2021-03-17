import request from '@/utils/request'
// G端-商品管理-查询列表
export function getGoodsList(data) {
  return request({
    url: '/console/goods/list',
    method: 'post',
    data
  })
}
// G端-商品管理-更新上下架状态(支持批量)
export function getGoodsUpdateState(data) {
  return request({
    url: '/console/goods/updateState',
    method: 'post',
    data
  })
}
// G端-商品管理-删除商品
export function getGoodsDelete(data) {
  return request({
    url: '/console/goods/delete',
    method: 'post',
    data
  })
}
// G端-商品管理-获取商品详情
export function getGoodsDetail(data) {
  return request({
    url: '/console/goods/getDetail',
    method: 'post',
    data
  })
}
// G端-商品分类-列表
export function getTypeList(data) {
  return request({
    url: '/console/goodsType/queryListWithPage',
    method: 'post',
    data
  })
}
// G端-商品分类-更新有效性
export function goodsTypeUpdateState(data) {
  return request({
    url: '/console/goodsType/updateState',
    method: 'post',
    data
  })
}
// G端-商品分类-删除
export function goodsTypeDelete(data) {
  return request({
    url: '/console/goodsType/delete',
    method: 'post',
    data
  })
}

export function getTypeInfo(data) {
  return request({
    url: '/console/goodsType/getDetail',
    method: 'post',
    data
  })
}

export function getGoodsTypeSelect(data) {
  return request({
    url: '/v1/goods/type_select',
    method: 'post',
    data
  })
}

export function getChild(params) {
  return request({
    url: '/console/goodsType/getChild',
    method: 'get',
    params
  })
}
export function getDetail(data) {
  return request({
    url: '/console/goodsType/getDetail',
    method: 'post',
    data
  })
}
export function save(data) {
  return request({
    url: '/console/goodsType/save',
    method: 'post',
    data
  })
}
