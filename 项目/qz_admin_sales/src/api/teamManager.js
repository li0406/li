import request from '@/utils/request'

export function fetchTeamPersonList(query) {
  return request({
    url: '/findadmin',
    method: 'post',
    data: query
  })
}

// 团队管理--所属团队搜索下拉
export function fetchTopTeamList(query) {
  return request({
    url: '/v1/team/gettopteam',
    method: 'get',
    params: query
  })
}

// 团队管理---列表
export function fetchTeamList(query) {
  return request({
    url: '/v1/team/getteamlist',
    method: 'get',
    params: query
  })
}

// 团队管理---编辑
export function fetchTeamEdit(query) {
  return request({
    url: '/v1/team/teamup',
    method: 'get',
    params: query
  })
}

// 团队管理---停用
export function fetchTeamForbidden(query) {
  return request({
    url: '/v1/team/teamstatusup',
    method: 'post',
    data: query
  })
}
// 团队管理---添加/编辑（保存）
export function fetchTeamSave(query) {
  return request({
    url: '/v1/team/teamup',
    method: 'post',
    data: query
  })
}

// 成员管理---（成员）列表
export function fetchMemberList(query) {
  return request({
    url: '/v1/team/getteammemberlist',
    method: 'get',
    params: query
  })
}

// 成员管理---移出团队
export function fetchRemoveTeam(query) {
  return request({
    url: '/v1/team/removeteammember',
    method: 'post',
    data: query
  })
}

// 成员管理---团队树
export function fetchMemberTree(query) {
  return request({
    url: '/v1/team/getteamtree',
    method: 'get',
    params: query
  })
}

// 成员管理---编辑
export function fetchMemberEdit(query) {
  return request({
    url: '/v1/team/teammemberup',
    method: 'post',
    data: query
  })
}

// 成员管理---团队成员信息
export function fetchMemberInfo(query) {
  return request({
    url: '/v1/team/getteammember',
    method: 'get',
    params: query
  })
}
// 获取团队业绩
export function getTeamList(query) {
  return request({
    url: '/v1/indicators/getteamindicators',
    method: 'get',
    params: query
  })
}
// 获取个人业绩
export function getMemberList(query) {
  return request({
    url: '/v1/indicators/getteammemberindicators',
    method: 'get',
    params: query
  })
}
// 编辑业绩指标
export function fetchIndicatorsEdit(query) {
  return request({
    url: '/v1/indicators/indicatorsup',
    method: 'post',
    data: query
  })
}
