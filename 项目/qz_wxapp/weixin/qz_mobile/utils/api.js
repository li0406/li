// api 方法，变量命名方式驼峰法：getBanner
import {
  baseUrl,
  _get,
  _post,
  _put,
  _delete
} from './request.js'
// 登陆
export function login(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 账号关联，用于消息系统
export function connectAccount(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 用户信息
export function getUser(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
//  我的工作
export function getMywork(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 获取城市
export function getCitys(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 搜索获取公司
export function getCompany(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 获取区域
export function getAreas(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 客户维护列表
export function getCompanyList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 日志记录（列表页）
export function getListVisit(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 再次报备-联系人列表（日志详情）
export function getContactList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 再次报备-拜访记录列表
export function getJournalDetail(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 再次报备 - 添加拜访记录
export function addSign(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 添加日志 - 首次添加日志
export function addJournal(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 再次报备 - 报备新增联系人
export function addUser(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 再次报备 - 报备编辑联系人
export function editJournal(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 签单管理列表
export function getSignList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 订单管理列表
export function getOrderList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 订单回访列表
export function getOrderVisitBackList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 订单回访列表 -- 撤回
export function visitDelete(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 订单回访列表详情 -- 推送
export function visitPushDeatil(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 订单回访列表详情 -- 新订单推送
export function visitNewPushDeatil(url = '', parmars = {}, header = {}) {
    return _post(url, parmars, header)
}

// 订单回访列表详情 -- 获取新增选项
export function getAddOptions(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 订单回访列表详情 -- 新增回访单
export function visitAdd(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 获取回访状态选项
export function getOrderVisitBackOption(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 会员到期提醒
export function getExpired(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 退出登陆
export function exit(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 分单统计
export function fendan(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 签单审核 - 确认/取消
export function signHandle(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 订单查看时间
export function ordertime(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 订单查看时间
export function fendetails(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 获取工作台/统计菜单
export function miniDetails(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 会员报备列表
export function baobeiList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 删除会员报备
export function delReport(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 撤回会员报备
export function withDrawReport(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 驳回会员报备
export function rollbackDrawReport(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 添加会员报备
export function addReport(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 会员报备审核列表页
export function memberReportCheckList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 查看会员报备审核详情
export function memberReportDetail(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 验证网店代码
export function testCompany(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 会员详情
export function memberDetail(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 会员报备审核
export function memberReportCheckAction(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

//图片上传
export function uploadImgs(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 需要客服上传凭证
export function supReport(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 城市历史报价
export function historyQuote(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 验证当前合同是否异常
export function checkContract(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

// 小报备
export function smalladd(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}

export function editinfo(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 小报备详情
export function smallinfo(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 返点订单列表
export function signlist(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 签单审核管理-签单详情
export function qdDetail(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 返点记录 
export function reportPaymentList(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 其他业绩人
export function performancePersonList(url = '', parmars = {}, header = {}) {
    return _get(url, parmars, header)
}

// 获取编辑筛选项
export function seleOptions(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}

// 装企咨询-get
export function getConsult(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 装企咨询-post
export function postConsult(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
// 发票-get
export function getInvoice(url = '', parmars = {}, header = {}) {
  return _get(url, parmars, header)
}
// 发票-post
export function postInvoice(url = '', parmars = {}, header = {}) {
  return _post(url, parmars, header)
}
