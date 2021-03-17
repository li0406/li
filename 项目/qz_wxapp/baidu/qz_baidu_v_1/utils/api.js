// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'

// 获取攻略列表
export function getWalkList(url = '', parmars={}) {
    return _get(url, parmars)
    console.log(_get);
}
// 攻略详情页
export function getWalkDetail(url = '', parmars={}) {
    return _get(url, parmars)
}
//发单
export function fadanHandle(url='', params={}) {
    return _post(url,params, 'application/x-www-form-urlencoded')
}
// 报价详情
export function getOrderDetail(url='', params={}) {
    return _get(url,params)
}
//商家入驻
export function merchantHandle(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}
// 获取城市
export function getCityData(url='', params={}){
    return _get(url,params)
}
