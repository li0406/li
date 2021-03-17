
import request from '@/utils/request';

export default {
  //  员工管理
  //  账号检测
  accountcheck: (data) => {
    return request({
      url: '/cp/v1/employee/accountcheck',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
  //  帐号注册/编辑
  accountup: (data) => {
    return request({
      url: '/cp/v1/employee/accountup',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
  //  角色列表
  rolelist: (params) => {
    return request({
      url: '/cp/v1/employee/rolelist',
      method: 'get',
      params,
    });
  },
  //  帐号信息
  accountinfo: (params) => {
    return request({
      url: '/cp/v1/employee/accountinfo',
      method: 'get',
      params,
    });
  },
  //  员工入职/离职
  accountstateup: (data) => {
    return request({
      url: '/cp/v1/employee/accountstateup',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
  //  员工列表
  accountlist: (params) => {
    return request({
      url: '/cp/v1/employee/accountlist',
      method: 'get',
      params,
    });
  },
  //  重置密码
  resetpwd: (data) => {
    return request({
      url: '/cp/v1/employee/resetpwd',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
  //  员工管理-设计师排序列表
  designerlist: (params) => {
    return request({
      url: '/cp/v1/store/designerlist',
      method: 'get',
      params,
    });
  },
  //  员工管理-设置设计师排序
  designersortup: (data) => {
    return request({
      url: '/cp/v1/store/designersortup',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
  telup: (data) => {
    return request({
      url: '/cp/v1/employee/telup',
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'post',
      data,
    });
  },
}
