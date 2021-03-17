import request from '@/utils/request'

const CONTENT = {
  // 获取标签
  getTagList: params =>
    request({
      url: `/console/forum/tags`,
      method: 'get',
      params
    }),
  // 标记&问答列表
  getNoteList: params =>
    request({
      url: `/console/forum/posts`,
      method: 'get',
      params
    }),
  // 笔记详情
  getNoteDetail: params =>
    request({
      url: `/console/forum/posts/details`,
      method: 'get',
      params
    }),
  // 评论&答案列表
  getCommentList: params =>
    request({
      url: `/console/forum/comments`,
      method: 'get',
      params
    }),
  // feed列表
  feedList: data =>
    request({
      url: `/console/forum/platform/feed/list`,
      method: 'post',
      data
    }),
  // feed列表更新
  feedUpdate: data =>
    request({
      url: `/console/forum/platform/feed/update`,
      method: 'post',
      data
    }),
  // 添加笔记
  getUpdate: data =>
      request({
        url: `/console/forum/platform/post/add`,
        method: 'post',
        data
   }),
  getEdit: data =>
      request({
        url: `/console/forum/platform/post/edit`,
        method: 'post',
        data
  }),
}
export default CONTENT
