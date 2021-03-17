import request from '@/utils/request'
const BASE_URL = 'reportapi.qizuang.com'
export function login(data) {
  return request({
    url: `${BASE_URL}/vue-admin-template/user/login`,
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: '/vue-admin-template/user/info',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return request({
    url: '/vue-admin-template/user/logout',
    method: 'post'
  })
}
