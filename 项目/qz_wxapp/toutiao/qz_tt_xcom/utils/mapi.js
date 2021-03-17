// api 方法，变量命名方式驼峰法：getBanner

import {baseUrl, _get, _post, _put, _delete} from './mrequest.js'

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
// 获取发单城市
export function getCityData(url='', params={}){
    return _get(url,params)
}

//装修报价
export function baojia(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}

// 城市列表页面获取城市
export function getChoseCityData(url='/v1/city/getCity', params={}){
    return _get(url,params)
}
// 根据城市名获取城市信息
export function getCityInfoByName(url='/v1/city/getCityByCityName', params={}){
    return _get(url,params)
}
// 首页筛选项加载
export function getFilterData(url='/tt/v1/company/filter', params={}){
    return _get(url,params)
}

// 装修公司筛选列表
export function getIndexCom(url='/tt/v1/company/list', params={}){
    return _get(url,params)
}

// 装修公司详情页
export function getComData(url='', params={}){
    return _get(url,params)
}
// 装修案例
export function getCaseData(url='', params={}){
    return _get(url,params)
}
// 案例详情
export function getCaseDetail(url='', params={}){
    return _get(url,params)
}
// 设计师
export function getDesigner(url='', params={}){
    return _get(url,params)
}
// 业主点评
export function getComments(url='', params={}){
    return _get(url,params)
}
// 活动详情
export function activityData(url='', params={}){
    return _get(url,params)
}