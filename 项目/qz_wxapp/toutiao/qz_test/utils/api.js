// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'

//发单
export function fadanHandle(url='', params={}) {
    return _post(url,params, 'application/x-www-form-urlencoded')
}


// 获取案例列表
export function getCaseData(url='', params={}) {
    return _get(url,params)
}

// 获取案例详情
export function getCaseDetail(url='', params={}){
    return _get(url,params)
}
// 公司详情页
export function getCompanyDetail(url='', params={}){
    return _get(url,params)
}
// 设计师列表
export function getDesigner(url='', params={}){
    return _get(url,params)
}
// 设计师案例
export function getDesignerCase(url='', params={}){
    return _get(url,params)
}
// 业主点评
export function getComments(url='', params={}){
    return _get(url,params)
}
// 获取城市
export function getCityData(url='', params={}){
    return _get(url,params)
}