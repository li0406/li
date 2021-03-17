import request from '@/utils/request'
//  公共
const ADVERT = {
  //  G端-广告列表
  list: data =>
    request({
      url: `/console/forum/platform/banner/list`,
      method: 'POST',
      data
    }),
  //  G端-编辑/添加广告
  detail: data =>
    request({
      url: `/console/forum/platform/banner/detail`,
      method: 'POST',
      data
    }),
  //  G端-编辑/添加广告
  save: data =>
    request({
      url: `/console/forum/platform/banner/save`,
      method: 'POST',
      data
    }),
  //  G端-置顶广告
  top: data =>
    request({
      url: `/console/forum/platform/banner/top`,
      method: 'POST',
      data
    }),
  //  G端-批量删除广告
  deleteBanner: data =>
    request({
      url: `/console/forum/platform/banner/deleteBanner`,
      method: 'POST',
      data
    })
}
export default ADVERT
