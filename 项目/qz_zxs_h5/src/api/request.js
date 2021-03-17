/* eslint-disable */
import axios from 'axios'
import qs from 'qs'
import {getCookie} from './util'
import qzConfig from '../utils/config'

// 创建axios实例
let baseUrl = ''
baseUrl = qzConfig.api_base_url

const service = axios.create({
  baseURL: baseUrl,
  // baseURL: '',
  timeout: 8000 // 请求超时时间
})
// request拦截器
service.interceptors.request.use(
  config => {
    let token = getCookie('token')
    if(!token) {
        token = sessionStorage.token
    }
    if(!token) {
        token = ''
    }
    if (config.url.indexOf('zbfb') !== -1) {
      config.data = qs.stringify(config.data)
    }
    config.headers.token = token
    return config
  },
  error => {
    // Do something with request error
    console.log(error) // for debug
    Promise.reject(error)
  }
)
export default service
