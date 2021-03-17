import request from '@/utils/request'
export default{
    //  发票报备列表
    list(query) {
        return request({
            url: '/v1/invoice/list',
            method: 'get',
            params: query
        })
    },
    //  发票报备列表搜索选项
    options(query) {
        return request({
            url: '/v1/invoice/options',
            method: 'get',
            params: query
        })
    },
    //  发票报备：添加/编辑发票
    save(data) {
        return request({
          url: '/v1/invoice/save',
          method: 'post',
          data
        })
    },
    //  查看发票报备
    view(query) {
        return request({
            url: '/v1/invoice/view',
            method: 'get',
            params: query
        })
    },
    //  发票报备：提交
    submit(data) {
        return request({
          url: '/v1/invoice/submit',
          method: 'post',
          data
        })
    },
    //  发票报备：删除
    delete(data) {
        return request({
          url: '/v1/invoice/delete',
          method: 'post',
          data
        })
    },
    //  发票报备：撤回提交
    recall(data) {
        return request({
          url: '/v1/invoice/recall',
          method: 'post',
          data
        })
    },
    //发票报备搜索选项
    select(query) {
      return request({
          url: '/v1/team/select',
          method: 'get',
          params: query
      })
    },
}
