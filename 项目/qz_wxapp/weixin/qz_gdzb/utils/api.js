// api 方法，变量命名方式驼峰法：getBanner
import {baseUrl, _get, _post, _put, _delete} from './request.js'
// 登陆
export function login(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 用户信息
export function getUser(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 获取直传七牛token，用于直传视频
export function getQiniuToken(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 首页
export function getHomeLIst(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 上传施工图片
export function getImg(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 施工詳情
export function getLive(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 施工阶段
export function getStep(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 施工信息刪除
export function sanDetail(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 首页竣工
export function completeOperate(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 首页绑定施工编号
export function bindorder(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 编辑施工信息
export function getInfodetail(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 新增施工信息
export function pastDetail(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}