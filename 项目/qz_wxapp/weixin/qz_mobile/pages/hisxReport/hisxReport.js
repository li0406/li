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
Page({
  data: {
    list: [],
    report_id:'',
    type:'',
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
    let report_id = options.report_id ? options.report_id : ''
    let type = options.type ? options.type : ''
    that.setData({
      report_id: report_id,
      type: type
    })
  },

  onReady: function() {

  },

  onShow: function() {
    let that = this;
    let report_id = that.data.report_id
    that.getList(report_id);
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
    getList: function (report_id) {
    let that = this;
    let type = that.data.type
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/related/report_payment_list', {
            report_id: report_id,
            report_cooperation_type: type
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let list = res.data.list;
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
  // 预览图片
  previewImg1: function(e) {
    var index = e.currentTarget.dataset.index;
    var pics = e.currentTarget.dataset.pics;
    let xpics = pics.map(item =>{
      return item.img_full
    })
    wx.previewImage({
      current: xpics[index],
      urls: xpics
    })
  },
  toDetail: function(e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../detailsReport/detailsReport?id=' + id,
    })
  },
  toMemberReport: function() { // 重载
    wx.navigateTo({
      url: '../hisxReport/hisxReport'
    })
  },
})