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
// 获取美图分类
export function getMeituCategory(url='', params={}){
    return _get(url)
}
// 获取美图列表
export function queryMeituList(url='', params={}){
    return _get(url,params)
}
// 获取美图详情
export function getMeituDetail(url='', params={}){
    return _get(url,params)
}