import request from '@/utils/request';

const apiCoupon = {
  getList() {
    return request({
      url: `https://www.fastmock.site/mock/01ed2518eeaf00c39cc39939c786d61d/list/couponList`,
      method: 'get',
    });
  },
};


export default apiCoupon;
