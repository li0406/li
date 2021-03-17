// pages/search/search.js
var util = require('../../utils/util.js');
import { getHomeLIst, getLive, completeOperate } from "../../utils/api.js"
Page({

  /**
   * 页面的初始数据
   */
  data: {
    infoShow: '',
    isData: false,
    info: '',
    getInfoList: [],
    totalPage: '',
    nomore: false,
    errorTit: false,
    errorInfo: '',
    isClicked:false,
    parms: {
      state: '',
      keyword: '',
      page: 1,
      limit: 10
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  },
  toHome: function(){
    wx.navigateBack({
      url: '../home/home',
    })
  },
  searchInfo: function (e) {
    this.setData({
      info: e.detail.value,
      imgShow: false,
      infoShow: '没有搜到“' + e.detail.value + '”相关信息',
      ['parms.keyword']: e.detail.value,
      ['parms.page']: 1,
      getInfoList: [],
      nomore: false
    })
    
    this.getHomeLIst(this.data.parms)
  },
  getHomeLIst: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getHomeLIst('/business/worksite/applet/live_list',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code === 0) {
            if (res.data.info.length > 0) {
              res.data.info.forEach(function (item, index) {
                if (item.last_time) {
                  item.last_time = util.formatTime(new Date(item.last_time * 1000))
                }
                item.endtime = util.formatTime(new Date(item.endtime * 1000))
              })
              let getInfoList = that.data.getInfoList
              getInfoList = getInfoList.concat(res.data.info);
              that.setData({
                getInfoList: getInfoList,
                totalPage: res.data.page.page_total_number,
                isData: false,
                nomore: false
              })
            } else {
              that.setData({
                isData: true,
                nomore: false
              })
            }
          }else{
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 1000,
              success: function () {
                setTimeout(function () {
                  wx.navigateTo({
                    url: '../login/login',
                  })
                }, 1000);
              }
            })
          }

        }).catch(function (error) {

        })
      },
      fail: function (res) {
      }
    })
  },
  toDetail: function (e) {
    let id = e.currentTarget.dataset.id
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: id
        }, {
            'content-type': 'application/x-www-form-urlencoded',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              if (!that.data.isClicked) {
                that.setData({
                  isClicked: true
                })
                wx.navigateTo({
                  url: '../sgdetail/sgdetail?id=' + id,
                })
              }
            } else {
              that.setData({
                errorTit: true,
                errorInfo: res.error_msg,
                ['parms.page']: 1,
                getInfoList: []
              })
              setTimeout(function () {
                that.setData({
                  errorTit: false,
                  errorInfo: ''
                })
              }, 3000)
              that.getHomeLIst(that.data.parms)
            }
          })
      },
      fail: function () {

      }
    })
  },
  completeConfirm: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    wx.showModal({
      content: '请确认完成此工地所有项目，可竣工验收',
      cancelColor: "#666666",
      confirmColor: "#FF5353",
      confirmText: "确认竣工",
      success: function (res) {
        if (res.confirm) {
          that.setData({
            ['parms.page']: 1,
            getInfoList: []
          })
          that.completeOperate(id)
          that.getHomeLIst(that.data.parms)
        } else if (res.cancel) {
        }
      }
    })
  },
  completeOperate: function (id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        completeOperate('/business/worksite/applet/completed_live',
          { id: id }, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            if (res.data.info.length > 0) {

            } else {
              that.setData({
                isData: false
              })
            }
          } else {
            that.setData({
              errorTit: true,
              errorInfo: res.error_msg
            })
            setTimeout(function () {
              that.setData({
                errorTit: false,
                errorInfo: ''
              })
            }, 2000)
          }
        }).catch(function (error) {

        })
      },
      fail: function (res) {
      }
    })
  },
  toSgDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    let that = this
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: id
        }, {
            'content-type': 'application/x-www-form-urlencoded',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              if (!that.data.isClicked) {
                that.setData({
                  isClicked: true
                })
                wx.navigateTo({
                  url: '../addsg/addsg?id=' + id
                })
              }
            } else {
              that.setData({
                errorTit: true,
                errorInfo: res.error_msg,
                ['parms.page']: 1,
                getInfoList: []
              })
              setTimeout(function () {
                that.setData({
                  errorTit: false,
                  errorInfo: ''
                })
              }, 3000)
              that.getHomeLIst(that.data.parms)
            }
          })
      },
      fail: function () {

      }
    }) 
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      isClicked: false
    })
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
      this.setData({
          getInfoList: [],
          ['parms.page']: 1
      })
      if(this.data.info != ''){
          this.getHomeLIst(this.data.parms)
      }
      wx.stopPullDownRefresh()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var that = this;
    // 显示加载图标
    wx.showLoading({
      title: '请求中，请耐心',
    })
    // 页数+1
    that.setData({
      ['parms.page']: that.data.parms.page + 1
    })
    if (that.data.parms.page > that.data.totalPage) {
      that.setData({
        nomore: true
      })
      wx.hideLoading()
    } else {
      that.setData({
        nomore: false
      })
      that.getHomeLIst(that.data.parms)
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})