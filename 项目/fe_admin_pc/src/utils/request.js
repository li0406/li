import axios from 'axios'
import { Message } from 'element-ui'
import store from '@/store'
import { getToken } from '@/utils/auth'
import config from '@/utils/config'

// create an axios instance
const service = axios.create({
  baseURL: process.env.VUE_APP_BASE_API, // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 15000 // request timeout
})

// request interceptor
service.interceptors.request.use(
  config => {
    // do something before request is sent
    config.headers['Content-Type'] = 'application/json'
    if (store.getters.token) {
      // let each request carry token
      // ['X-Token'] is a custom headers key
      // please modify it according to the actual situation
      config.headers['Authorization'] = getToken()
    }
    return config
  },
  error => {
    // do something with request error
    console.log(error) // for debug
    return Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  /**
    *如果您想获取http信息，如标头或状态
    *请返回回复=>回复
  */
  /**
  *通过自定义代码确定请求状态
  *这里只是一个例子
  *您还可以通过HTTP状态码来判断状态
   */
  response => {
    const res = response
    if (res.data.error_code === 810015) {
      window.location.href = config.entranceurl
    }
    // if the custom code is not 20000, it is judged as an error.
    if (res.status !== 200) {
      Message({
        message: res.message || 'Error',
        type: 'error',
        duration: 5 * 1000
      })
      // 50008: Illegal token; 50012: Other clients logged in; 50014: Token expired;
      // if (res.code === 50008 || res.code === 50012 || res.code === 50014) {
      //   // to re-login
      //   MessageBox.confirm('您已注销，可以取消以停留在此页面，或重新登录', '确认注销', {
      //     confirmButtonText: '重新登录',
      //     cancelButtonText: '取消',
      //     type: 'warning'
      //   }).then(() => {
      //     store.dispatch('user/resetToken').then(() => {
      //       location.reload()
      //     })
      //   })
      // }
      return Promise.reject(new Error(res.message || 'Error'))
    } else {
      return res.data
    }
  },
  error => {
    console.log('err' + error) // for debug
    if (error.response.status === 500) { // 服务器断开
      Message({
        message: '服务器断开，请稍后重试。',
        type: 'error',
        duration: 5 * 1000
      })
    } else if (error.response.status === 403) { //无auth授权，后台不允许访问
      Message({
        message: '不允许访问,请从新登陆',
        type: 'error',
        duration: 5 * 1000
      })
      window.location.href = config.entranceurl
    } else {
      Message({
        message: error.message,
        type: 'error',
        duration: 5 * 1000
      })
    }
    return Promise.reject(error)
  }
)

export default service
