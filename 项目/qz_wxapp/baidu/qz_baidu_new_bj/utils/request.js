import config from './config.js'
const baseUrl = config.API_HOST
const timeOut = config.REQUEST_TIMEOUT
// 请求方法封装
const http = ({ url = '', param = {}, contentType = 'application/json', method = 'get', ...other } = {}) => {
    swan.showLoading({
        title: '请求中，请耐心等待..'
    })
    let timeStart = Date.now()
    return new Promise((resolve, reject) => {
        swan.request({
            url: getUrl(url),
            data: param,
            method:method,
            header: {
                // 'content-type': 'application/json' // 默认值 ,另一种是 
                // "content-type": "application/x-www-form-urlencoded"
                "content-type": contentType
            },
            complete: (res) => {
                swan.hideLoading()
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
const _get = (url, param = {}) => {
    return http({
        url,
        param
    })
}

// post方法
const _post = (url, param = {}, contentType) => {
    return http({
        url,
        param,
        contentType,
        method: 'post'
    })
}

// put方法
const _put = (url, param = {}) => {
    return http({
        url,
        param,
        method: 'put'
    })
}

// delete方法
const _delete = (url, param = {}) => {
    return http({
        url,
        param,
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