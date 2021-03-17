import request from '@/utils/request';

const apiPromotions = {
  // 优惠活动
  getEventList(query) {
    return request({
      url: `/cp/v1/company/activity?name=${query.name}&state=${query.state}&page=${query.page}&limit=${query.limit}`,
      method: 'get',
    });
  },

  statusChange(id, state) {
    return request({
      url: `/cp/v1/company/activity/change`,
      method: 'POST',
      data: {
        id,
        state,
      },
    });
  },

  getDetail(id) {
    return request({
      url: `/cp/v1/company/activity/detail?id=${id}`,
      method: 'get',

    });
  },

  saveDetail(params) {
    return request({
      url: `/cp/v1/company/activity/edit`,
      method: 'POST',
      data: params,
    });
  },


  deleteEvent(id) {
    return request({
      url: `/cp/v1/company/activity/del/`,
      method: 'POST',
      data: {
        id,
      },
    });
  },

  // 礼券
  getCouponList(query) {
    return request({
      url: `/cp/v1/company/dedicated_card?name=${query.name}&card_status=${query.card_status}&check_status=${query.check_status}&page=${query.page}&limit=${query.limit}`,
      method: 'GET',
    });
  },

  saveCoupon(params) {
    return request({
      url: `/cp/v1/company/dedicated_card/edit`,
      method: 'POST',
      data: params,
    });
  },

  getCouponDetail(id) {
    return request({
      url: `/cp/v1/company/dedicated_card/detail?id=${id}`,
      method: 'get',
    });
  },

  operating(id, action) {
    return request({
      url: `/cp/v1/company/dedicated_card/change`,
      method: 'post',
      data: {
        id,
        action_status: action,
      },
    });
  },


//  领用查询
  List(query) {
    return request({
      url: `/cp/v1/company/card/get_log`,
      method: 'get',
      params: query,
    });
  },

  //  领用查询
  cardList(query) {
    return request({
      url: `/cp/v1/company/common_card/get_log`,
      method: 'get',
      params: query,
    });
  },

  commonCard(query) {
    return request({
      url: `/cp/v1/company/common_card?name=${query.name}&card_status=${query.card_status}&page=${query.page}&limit=${query.limit}`,
      method: 'get',
    });
  },

  canReceive(query) {
    return request({
      url: `/cp/v1/company/common_card/can_receive?page=${query.page}&limit=${query.limit}`,
      method: 'get',
    });
  },

  cardDetail(id) {
    return request({
      url: `/cp/v1/company/common_card/detail?id=${id}`,
      method: 'get',
    });
  },

  canReceiveDetail(id) {
    return request({
      url: `/cp/v1/company/common_card/can_receive_detail?id=${id}`,
      method: 'get',
    });
  },

  cardSave(params) {
    return request({
      url: `/cp/v1/company/common_card/save`,
      method: 'POST',
      data: params,
    });
  },

  cardChange(params) {
    return request({
      url: `/cp/v1/company/common_card/change`,
      method: 'POST',
      data: params,
    });
  },
};


export default apiPromotions;
