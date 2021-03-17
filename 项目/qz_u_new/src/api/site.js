import requestTwo from '@/utils/requestTwo';

const api = {
  // 获取装修现场列表
  // search--搜索关键字 page--页数 size--每页显示的个数
  getLiveList(search, page, size) {
    return requestTwo({
      url: `/business/worksite/pc/live_list?keyword=${search}&page=${page}&limit=${size}`,
      method: 'get',
    });
  },


  // 装修现场订单信息
  // id--装修现场id
  getOrderDetail(id) {
    return requestTwo({
      url: `/business/worksite/pc/live_detail?id=${id}`,
      method: 'get',
    });
  },

  // 装修现场订单信息
  // id--装修现场id step--装修阶段
  getSgInfo(id,step) {
    return requestTwo({
      url: `/business/worksite/pc/info_list?live_id=${id}&step=${step}`,
      method: 'get',
    });
  },

  // 获取施工二维码
  getQrCode(code) {
    return requestTwo({
      url: `/business/worksite/pc/live_qrcode?live_code=${code}`,
      method: 'get',
    });
  },

  // 施工信息的回显 id--施工信息的单条id
  getSgDetail(id) {
    return requestTwo({
      url: `/business/worksite/pc/info_detail?id=${id}`,
      method: 'get',
    });
  },

  // 保存编辑的施工信息
  saveSgInfo(editSgInfoForm, mediaUrls) {
    return requestTwo({
      url: `/business/worksite/pc/info_edit`,
      method: 'post',
      data: {
        id: editSgInfoForm.id,
        step: editSgInfoForm.step,
        content: editSgInfoForm.content,
        media_type: editSgInfoForm.mediaType,
        media_urls: mediaUrls,
      },
    });
  },

  // 删除某一条施工信息
  removeSgInfo(id) {
    return requestTwo({
      url: `/business/worksite/pc/info_delete`,
      method: 'post',
      data: {
        id,
      },
    });
  },

  // 回复
  reply(data) {
    return requestTwo({
      url: `/business/worksite/pc/comment`,
      method: 'post',
      data: {
        comment_id: data.id,
        info_id: data.infoId,
        content: data.content,
      },
    });
  },

};


export default api;

