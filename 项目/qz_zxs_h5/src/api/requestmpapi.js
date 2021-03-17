import axios from 'axios'
import qs from 'qs'
import {getCookie} from './util'
import qzConfig from '../utils/config'

// 创建axios实例
let baseUrl = ''
baseUrl = qzConfig.api_base_url_mpapi
const service = axios.create({
  baseURL: baseUrl,
  // baseURL: '',
  timeout: 5000 // 请求超时时间
})
// request拦截器
service.interceptors.request.use(
  config => {
    const token = getCookie('token')
    if (config.url.indexOf('zbfb') !== -1) {
      config.data = qs.stringify(config.data)
    }
    if (token) {
      config.headers.token = token
    } else {
      config.headers.token = sessionStorage.token
    }
    return config
  },
  error => {
    // Do something with request error
    console.log(error) // for debug
    Promise.reject(error)
  }
)
export default service
