import config from './config.js'
const baseUrl = config.API_HOST
// const baseUrl = config.API_HOST_DEV
const timeOut = config.REQUEST_TIMEOUT

// 请求方法封装
const http = ({ url = '',  param = {}, header = {}, method = 'get', ...other } = {}) => {
    wx.showLoading({
        title: '请求中，请耐心等待..'
    })
    let timeStart = Date.now()
    return new Promise((resolve, reject) => {
        wx.request({
            url: getUrl(url),
            data: param,
            method:method,
            header: header,
            // header: {
            //   // 'content-type': 'application/json' // 默认值 ,另一种是
            //   // "content-type": "application/x-www-form-urlencoded"
            //   "content-type": contentType,
            //   "token":token
            // },
            complete: (res) => {
                wx.hideLoading()
                if (res.statusCode >= 200 && res.statusCode < timeOut) {
                    resolve(res.data)
                } else {
                    reject(res)
                }
            }
        })
    })
}

// 获取接口地址
const getUrl = (url) => {
    if (url.indexOf('://') == -1) {
        url = baseUrl + url
    }
    return url
}

// get方法
const _get = (url, param = {},header = {}) => {
    return http({
        url,
        param,
        header
    })
}

// post方法
const _post = (url, param = {}, header = {}) => {
    return http({
        url,
        param,
        header,
        method: 'post'
    })
}

// put方法
const _put = (url, param = {}, header = {}) => {
    return http({
        url,
        param,
        header,
        method: 'put'
    })
}

// delete方法
const _delete = (url, param = {}, header = {}) => {
    return http({
        url,
        param,
        header,
        method: 'put'
    })
}

// 导出模块
module.exports = {
    baseUrl,
    _get,
    _post,
    _put,
    _delete
}
