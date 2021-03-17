import request from './request'

export function fetchShareContrast (query) {
  return request({
    url: '/v1/pk/detail',
    method: 'get',
    params: query
  })
}

export function fetchCommentList (query) {
  return request({
    url: '/v1/comment/list',
    method: 'get',
    params: query
  })
}

export function fetchVideoInfo (query) {
  return request({
    url: '/v2/vedio/detail',
    method: 'get',
    params: query
  })
}

export function fetchVideoRecommend (query) {
  return request({
    url: '/v2/vedio/recommendvideo',
    method: 'get',
    params: query
  })
}

export function fetchPicInfo (query) {
  return request({
    url: '/v2/picture/getdetail',
    method: 'get',
    params: query
  })
}

export function fetchPicRecommend (query) {
  return request({
    url: '/v2/picture/relatedlist',
    method: 'get',
    params: query
  })
}

export function fetchStrategy (query) {
  return request({
    url: '/v2/strategy/get',
    method: 'get',
    params: query
  })
}

export function fetchStagRecmd (query) {
  return request({
    url: '/v2/strategy/relatedlist',
    method: 'get',
    params: query
  })
}

export function fetchCheate (query) {
  return request({
    url: '/v1/experience/web',
    method: 'get',
    params: query
  })
}

export function fetchCheateRecmd (query) {
  return request({
    url: '/v2/experience/recommendexperience',
    method: 'get',
    params: query
  })
}

export function xiaobaiVideo (query) {
  return request({
    url: '/v1/zxzn/xiaobai_video',
    method: 'get',
    params: query
  })
}

export function xiaobaiCompany (query) {
  return request({
    url: '/v1/zxzn/xiaobai_company',
    method: 'get',
    params: query
  })
}

export function youLike (query) {
  return request({
    url: '/v1/zxzn/guess_you_like',
    method: 'get',
    params: query
  })
}
