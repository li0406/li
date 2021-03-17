import {
  baobeiList,
  smalladd
} from "../../utils/api.js"

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function(res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {

      }
    }
  });
}

function alertViewNoCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function(res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    curCity: '不限',
    cs: '',
    daType: '',
    type: '',
    parms: {
      city_id: '',
      keyword: '',
      cooperation_type: '',
      page: 1,
      limit: 10
    },
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    showEdit: false,
    showDel: false,
    showBack: false,
    noResult: false,
    noInternet: false,
    showModal: false,
    ifModel: false,
    showModal: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this
    that.setData({
      cs: options.cityid,
      curCity: options.cname,
      xbid: options.xbid,
      type: options.type
    })
  },
  onReady: function() {
    let that = this;
    that.getList(that.data.parms)
  },
  onShow: function() {
    let that = this;
    that.setData({
      ['parms.city_id']: that.data.cs,
      ['parms.city_name']: that.data.curCity,
      ['parms.cooperation_type']: that.data.type
    });
    if (that.data.parms.city_id == '') {
      that.setData({
        ['parms.city_id']: '',
        ['parms.city_name']: ''
      })
    }
    that.getList(that.data.parms);
    if (that.data.list != '') {
      that.setData({
        noResult: false
      })
    }
  },
  /**
   * 搜索条件
   */
  // 2. 搜索
  searchWord: function(e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.keyword']: value,
      ['parms.page']: 1
    })
    that.getList(that.data.parms);
  },
  // 3. 分页处理
  prevBtn: function(e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  nextBtn: function(e) {
    let that = this;
    let p = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      return;
    } else {
      p = p + 1;
    }
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  toPage: function(e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
   * 请求数据
   */
  getList: function(parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/payment/report_select', that.data.parms, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let list = res.data.list;
            let pageArr = [];
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            that.setData({
              list: list,
              noResult: false,
              page: true,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
            })
            if (list.length == 0) {
              that.setData({
                noResult: true,
                page: false
              })
            }
          } else {
            console.log(res.error_msg)
          }
        }).catch(function(error) {
          that.setData({
            list: [],
            page: false,
            noInternet: true
          })
        })
      }
    })
  },
  /**
   * 跳转操作
   */
  toCity: function() { //城市选择
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  toMemberReport: function() { // 重载
    wx.navigateTo({
      url: '../glReport/glReport'
    })
  },
  /**
   * 关联操作
   */
  preventTouchMove: function() { // 关闭弹窗
    let that = this
    that.setData({
      showModal: false
    })
  },
  anClick: function(e) {
    let that = this
    let id = e.currentTarget.dataset.id;
    let daType = e.currentTarget.dataset.type;
    that.setData({
      daid: id,
      daType,
      ifModel: true
    })
  },
  cancelModel() {
    let that = this
    that.setData({
      daid: '',
      ifModel: false
    })
  },
  related() {
    let that = this
    let daid = that.data.daid
    let xbid = that.data.xbid
    let daType = that.data.daType
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/payment/compare', {
          id: xbid,
          report_id: daid,
          report_cooperation_type: daType
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let compare = res.data.all_compare
            let msg_info = res.data.msg_info
            if (compare == '1') {
              alertViewWithCancel("关联确认", "确认要关联到该大报备中么？", function() {
                wx.getStorage({
                  key: 'info',
                  success: function(res) {
                    let token = res.data.token;
                    smalladd('/v1/sale_report/payment/related', {
                      id: xbid,
                      report_id: daid,
                      report_cooperation_type: daType
                    }, {
                      token: token,
                      'content-type': 'application/x-www-form-urlencoded',
                    }).then(res => {
                      if (res.error_code == 0) {
                        wx.showToast({
                          title: '关联成功',
                          icon: 'success',
                          duration: 2000
                        })
                        setTimeout(function() {
                          wx.navigateBack({
                            delta: 1,
                          })
                        }, 300)
                      } else {
                        alertViewNoCancel("关联失败", res.error_msg, function() {});
                        return;
                      }
                    })
                  }
                })
              });
            } else {
              that.setData({
                showModal: true,
                msg_info: msg_info
              })
            }
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function() {});
            return;
          }
        })
      }
    })
  },
  glCheck() {
    let that = this
    let daid = that.data.daid
    let xbid = that.data.xbid
    let daType = that.data.daType
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        smalladd('/v1/sale_report/payment/related', {
          id: xbid,
          report_id: daid,
          report_cooperation_type: daType
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            wx.showToast({
              title: '关联成功',
              icon: 'success',
              duration: 2000
            })
            setTimeout(function() {
              wx.navigateBack({
                delta: 1,
              })
            }, 300)
          } else {
            alertViewNoCancel("关联失败", res.error_msg, function() {});
            return;
          }
        })
      }
    })
  }
})