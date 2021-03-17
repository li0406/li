/* eslint-disable */
/*
 * @Author: your name
 * @Date: 2020-07-21 14:48:06
 * @LastEditTime: 2020-07-22 13:47:01
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_zxs_h5\src\api\casedetails.js
 */
/*
 * 案例详情接口
 */
import request from './request'

export default {
  // 整屋案例-案例详情
  detail(query) {
    return request({
      url: '/v1/roomcase/detail',
      method: 'get',
      params: query
    })
  },
  // 整屋案例-分享页-最热案例
  share_hot_cases() {
    return request({
      url: '/v1/roomcase/share_hot_cases',
      method: 'get'
    })
  },
  // 图文视频分享页
  converFn(query) {
    return request({
      url: '/v1/pins/view',
      method: 'get',
      params: query
    })
  },
  // 话题分享页
  topicFn(query) {
    return request({
      url: '/v1/pins/topic/detail',
      method: 'get',
      params: query
    })
  },
  //话题最热  最新
  topicWelFn(query) {
    return request({
      url: '/v1/pins/list',
      method: 'get',
      params: query
    })
  },
}
