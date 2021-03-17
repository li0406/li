// pages/moneyManagement/signList.js
import {
  getConsult,
  postConsult
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({
  data: {
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
    // 请求数据
    list: [],
    id:'',
    pageCurrent: '0',
    pageTotalNumber: '0',
    noResult: false,
    page: false,
    pageNumber: [],
    parms: {
      company_id: '1',
      page: '1'
    },
    data:{}
  },
  //列表数据
  getOrderList: function (parms) {
    let that = this;
    console.log(that.data.parms)
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/company/roundorder/sign_list',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            //获取页码
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            let datalist = res.data.list
            if (datalist.length < 1) {
              that.setData({
                list: [],
                page: false,
                noResult: true
              })
              return false
            }
            that.setData({
              list: datalist,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              noResult: false,
              page: true
            })
            console.log(that.data.list)
            if (res.data.page.total_number < 10) {
              that.setData({
                page: false
              })
            } else {
              that.setData({
                page: true
              })
            }
          } else {
            that.setData({
              list: [],
              page: false,
              noResult: true,
              noInternet: false,

            })
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            page: false,
            noResult: false,
            noInternet: true
          })
        })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  //头部信息
  getOrderTotal: function () {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/company/roundorder/sign_total',
          {company_id:that.data.id}, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          console.log(res)
          if (res.error_code == 0) {
            that.setData({
              data: res.data,
            })
          } else {
            
          }
        }).catch(function (error) {
          
        })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  //复制
  copyBtn: function (e) {
    wx.setClipboardData({
      data: e.target.dataset.id,
      success(res) {
        wx.showToast({
          title: '订单号已复制',
          duration: 1000
        })
      }
    })
  },
  //上一页
  prevBtn: function (e) {
    let that = this;
    let page = that.data.pageCurrent;
    if (page <= 1) {
      page = 1;
      return;
    } else {
      page = page - 1;
    }
    that.setData({
      ['parms.page']: page
    })
    that.getOrderList(that.data.parms)

  },
  //下一页
  nextBtn: function (e) {
    let that = this;
    let page = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (page >= max) {
      page = max;
      return;
    } else {
      page = page + 1;
    }
    that.setData({
      ['parms.page']: page
    })
    that.getOrderList(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  // 网络故障
  toCustom: function () {
    wx.navigateTo({
      url: '../consult/list'
    })
  },
  // 分页
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getOrderList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  onLoad: function (options) {
    console.log(options)
    let that = this
    if (options) {
      that.setData({
        ['parms.company_id']: options.id,
        id: options.id
      })
      that.getOrderList(that.data.parms)
      that.getOrderTotal()
    }
  },

  onReady: function () {

  },

  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})