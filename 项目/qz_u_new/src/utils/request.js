import axios from 'axios';
import { Message } from 'element-ui';
// import store from '@/store';

const instance = axios.create({
  baseURL: process.env.VUE_APP_BASE_API,
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 15000, // request timeout
});

// request 拦截器
instance.interceptors.request.use(
  (config) => {
    // if (store.getters.token) {
    //   // eslint-disable-next-line no-param-reassign
    // }
    // FIXME: token 添加获取错误流程
    // TODO: token 错误 过期跳回u.qizuang.com/login
    // eslint-disable-next-line no-param-reassign
    config.headers.token = localStorage.getItem('token');
    // eslint-disable-next-line no-param-reassign
    return config;
  },
  (error) => Promise.reject(error),
);

// response 拦截器
instance.interceptors.response.use(
  (response) => {
    if (response.data.error_code === 40001 || response.data.error_code === 3000003 || response.data.error_code === 20003) {
      window.location = '/login'
    }
    return response;
  },
  (error) => {
    Message({
      message: error,
      type: 'error',
      duration: 5 * 1000,
    });
    return Promise.reject(error);
  },
);

export default instance;
