// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'

// 获取攻略列表
export function getWalkList(url = '', parmars={}) {
    return _get(url, parmars)
}
// 攻略详情页
export function getWalkDetail(url = '', parmars={}) {
    return _get(url, parmars)
}

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
// 文章内页 详情
export function getArtitles(url='', parmars={}){
    return _get(url,parmars)
}
export function getLikes(url='', parmars={}) {
    return _post(url,parmars,'application/x-www-form-urlencoded')
}
// 裝修指南
export function getGuide(url='', params={}){
    return _get(url,params)
}
// 装修流程
export function getProcess(url='', params={}){
    return _get(url,params)
}
// 首页
export function getIndex(url='', params={}){
    return _get(url,params)
}

//装修报价
export function baojia(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}

// 装修问答
export function getWenda(url='', params={}){
     return _get(url,params)
}
// 知识列表
export function getZhihiData(url='', params={}){
    return _get(url,params)
}
// 问答列表页数据
export function getZhishiListData(url='', params={}) {
    return _get(url,params)
}