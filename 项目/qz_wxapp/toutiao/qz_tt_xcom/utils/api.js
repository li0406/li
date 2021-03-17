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
// 装修案例列表
export function anLiList(url='/v1/company/case/list', params={}){
    return _get(url,params)
}
// 装修案例-风格
export function anLiStyle(url='/v1/company/case/fengge', params={}){
    return _get(url,params)
}
// 装修案例-类型
export function anLiType(url='/v1/company/case/type', params={}){
    return _get(url,params)
}
// 设计师团队-列表
export function designTeamlist(url='/v1/company/designer/list', params={}){
    return _get(url,params)
}
// 设计师详情-列表
export function designlist(url='/v1/company/designer/caselist', params={}){
    return _get(url,params)
}
// 设计师详情
export function designdetail(url='/v1/company/designer/detail', params={}){
    return _get(url,params)
}
// 首页数据加载
export function getFilterData(url='/v1/company/list', params={}){
    return _get(url,params)
}
