import request from '@/utils/request'

export function loginByUsername(username, password) {
  const data = {
    username,
    password
  }
  return request({
    url: '/login/login',
    method: 'post',
    data
  })
}

export function logout() {
  return request({
    url: '/loginout',
    method: 'post'
  })
}

export function getUserInfo(token) {
  return request({
    url: '/user/info',
    method: 'get',
    params: { token }
  })
}
export function clearMenuCache() {
  return request({
    url:'/clearcache',
    method:'get',
    params:''
  })
}

export function routeArray() {
  return request({
    url: '/auth/menus',
    method: 'get'
  })
}
