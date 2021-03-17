import {
  baobeiList
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
function getTime(timestamp) {
  let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear();
  let M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1;
  let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
  let H = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
  let m = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
  let s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
  return Y + '/' + M + '/' + D + ' ' + H + ':' + m + ':' + s;
}
Page({
  data: {
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    showEdit: false,
    showDel: false,
    showBack: false,
    noResult: false,
    noInternet: false,
    showModal: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this
    let company_id = options.comid ? options.comid : ''
    that.setData({
      comid: company_id
    })
  },

  onReady: function() {

  },

  onShow: function() {
    let that = this;
    let comid = that.data.comid
    that.getList(comid);
    if (that.data.list != '') {
      that.setData({
        noResult: false
      })
    }
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
      ['parms.page']: p
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
      ['parms.page']: p
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
      ['parms.page']: p
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
  getList: function(comid) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/company/report_list', {
          company_id: comid
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let list = res.data.list;
            list.forEach((item) => {
              item.check_date = getTime(item.check_time)
            })
            that.setData({
              list: list,
              noResult: false,
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
  toDetail: function(e) {
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    wx.navigateTo({
      url: '../memberDetail/memberDetail?id=' + id + '&cooperation_type=' + cooperation_type,
    })
  },
  toMemberReport: function() { // 重载
    wx.navigateTo({
      url: '../hisxReport/hisxReport'
    })
  },
})