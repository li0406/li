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
// 发单
export function fb(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}
// 公司
export function getgs(url='', params={}) {
    return _get(url,params)
}
// 装修公司--信息页
export function getComAbout(url='', params={}) {
    return _get(url,params)
}
// 装修公司--业主评价（评分）
export function getComment(url='', params={}) {
    return _get(url,params)
}

// 装修公司--业主评价（内容）
export function getCContent(url='', params={}) {
    return _get(url,params)
}
// 装修公司--活动详情
export function hdDetail(url='', params={}) {
    return _get(url,params)
}
// 装修现场
export function getWorkSite(url='', params={}) {
    return _get(url,params)
}
// 装修现场--详情
export function getWorkSiteDetail(url='', params={}) {
    return _get(url,params)
}
// 装修现场--详情1
export function getWorkSiteDetails(url='', params={}) {
    return _get(url,params)
}
// 装修现场--装修小百科
export function getBaiKeList(url='', params={}) {
    return _get(url,params)
}
// 设计师--列表
export function getDesignList(url='', params={}) {
    return _get(url,params)
}
// 设计师--详情(个人介绍)
export function getDesignerDetail(url='', params={}) {
    return _get(url,params)
}

// 设计师--详情(个人作品)
export function getDesignerWorks(url='', params={}) {
    return _get(url,params)
}
// 案例-详情
export function cases(url='', params={}) {
    return _get(url,params)
}
// 装修案例
export function caseList(url='', params={}) {
    return _get(url,params)
}
// 装修案例 - 选项
export function caseOptions(url='', params={}) {
    return _get(url,params)
}
// 公装效果图列表数据
export function chinaTu(url='', params={}) {
    return _get(url,params)
}
// 公装家装选项
export function cartTu(url='', params={}) {
    return _get(url,params)
}
// 装修效果图首页
export function getXgt(url='', params={}) {
    return _get(url,params)
}
// 装修效果图首页 - 列表
export function getXgtList(url='', params={}) {
    return _get(url,params)
}
// 装修效果图详情页
export function xgtDetail(url='', params={}) {
    return _get(url,params)
}
// 效果图专题
export function thematicList(url='', params={}) {
    return _get(url,params)
}
// 效果图列表
export function thematicListData(url='', params={}) {
    return _get(url,params)
}
// 用户领券
export function getfdFn(url='', params={}) {
    return _post(url,params,'application/x-www-form-urlencoded')
}
//文章详情页 - 效果图&案例
export function getArticalList(url = '', parmars={}) {
    return _get(url, parmars)
}
