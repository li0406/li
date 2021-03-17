// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'
//发单
export function fadanHandle(url='', parmars={}) {
    return _post(url,parmars, 'application/x-www-form-urlencoded')
}
//发单结果
export function getfdDetail(url='', parmars={}) {
    return _post(url,parmars, 'application/x-www-form-urlencoded')
}
// 报价详情
export function getOrderDetail(url='', params={}) {
    return _get(url,params)
}
//商家入驻
export function merchantHandle(url='', parmars={}) {
    return _post(url,parmars,'application/x-www-form-urlencoded')
}
// 获取城市
export function getCityData(url='', params={}){
    return _get(url,params)
}

//装修报价
export function baojia(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}