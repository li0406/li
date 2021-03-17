import request from '@/utils/request'

// 登录
export function login(data) {
  return request({
    url: '/vue-admin-template/user/login',
    method: 'post',
    data
  })
}
//  获取用户信息
export function getInfo() {
  return request({
    url: '/console/user/get',
    method: 'get'
  })
}
//  获取系统菜单
export function menu() {
  return request({
    url: '/v1/common/menu',
    method: 'get'
  })
}
//  清除缓存
export function clean() {
  return request({
    url: '/v1/common/clean',
    method: 'post'
  })
}
// 退出
export function logout() {
  return request({
    url: '/console/user/out',
    method: 'get'
  })
}
