// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'

//发单
export function fadanHandle(url='', params={}) {
    return _post(url,params, 'application/x-www-form-urlencoded')
}
// 报价结果
export function resultHandle(url='', params={}) {
    return _get(url,params, 'application/x-www-form-urlencoded')
}
// 所有城市
export function allcityHandle(url='', params={}) {
    return _get(url,params, 'application/x-www-form-urlencoded')
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

//问答
export function getWenda(url='', params={}){
    return _get(url,params)
}

//详情
export function getWalkList(url='', params={}){
    return _get(url,params)
}
// 知识问答
export function getZhihiData(url='', params={}){
    return _get(url,params)
}
//问答专题列表页
export function getZhishiListData(url='', params={}){
    return _get(url,params)
}
// 报价
export function getWalkDetail(url = '', parmars={}) {
    return _get(url, parmars)
}
//装修报价
export function baojia(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}