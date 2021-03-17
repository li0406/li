// api 方法，变量命名方式驼峰法：getBanner
import config from './config.js'
import {baseUrl, _get, _post, _put, _delete} from './request.js'
const baseUrlOld = config.API_HOST
const baseUrlNew = config.API_NEW_HOST
// 获取风格
export function getStyleImage(){
    return _get(baseUrlNew+'/bd/v1/qizuang/getmeitu')
}

//发单
export function fadanHandle(url='', params={}) {
    return _post(baseUrlNew+url,params, 'application/x-www-form-urlencoded')
}
// 获取城市id
export function getCityIdByName(data){
    return _get(baseUrlOld+'/getCityByCityName',data)
}
// 根据IP 获取Id
export function getCityIdByIp(){
    return _get(baseUrlNew+'/v1/city/getlocationCityid')
}