import axios from 'axios'
import { Message } from 'element-ui'
import { getToken } from '@/utils/auth'
import domain from '@/config/config'

// 创建axios实例
const service = axios.create({
  // baseURL: process.env.VUE_APP_BASE_API, // url = base url + request url
  // withCredentials: true, // 跨域请求时发送Cookie
  timeout: 15000 // request timeout
})

// request 请求拦截
service.interceptors.request.use(
  config => {
    // 在发送请求之前做些什么
    config.headers.token = getToken()
    return config
  },
  error => { // 处理请求错误
    console.log(error) // for debug
    return Promise.reject(error)
  }
)

// response 响应拦截器
service.interceptors.response.use(
  response => {
    if (response.data.error_code === 3000002) {
      window.location.href = domain.ENTRANCE_URL
    }
    return response.data
  },
  error => {
    Message({
      message: error,
      type: 'error',
      duration: 5 * 1000
    })
    return Promise.reject(error)
  }
)

export default service
