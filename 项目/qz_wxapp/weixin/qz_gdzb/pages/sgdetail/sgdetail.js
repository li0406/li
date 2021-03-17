import {
  getLive,
  getStep,
  sanDetail,
  completeOperate
} from "../../utils/api.js"
const utils = require('../../utils/util.js');

const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: '',
    company_info: '',
    info: '',
    step_list: [],
    step: '',
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
    stepList: [],
    scrollInfo: null,
    topNum: 0,
    largeView: false,
    swiperData: [],
    swiperLen: '',
    currentIndex: 1
  },
  scrollBig(e) {
    let scrollT = e.detail.scrollTop
    this.setData({
      scrollInfo: scrollT
    })
  },
  chooseItem: function(e) {
    this.setData({
      topNum: 300
    });
    let step = e.currentTarget.dataset.step || ''
    this.setData({
      scrollInfo: true,
      step: step,
      nomore: 2,
      stepList: [],
      page: 1
    })
    this.getdetail()
  },
  getdetail() {
    let obj = this.data;
    let that = this;
    if (obj.nomore != 2) {
      return
    }
    that.setData({
      nomore: 1
    })
    if (obj.page == 1) {
      wx.showLoading({
        title: '加载中',
      })
    }
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        let wxName = res.data.nikename;
        let wximg = res.data.headimg;
        getStep('/business/worksite/applet/step_list', {
          live_id: that.data.id,
          step: that.data.step,
          page: that.data.page,
          limit: 5
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          let datas = res;
          if (obj.page == 1) {
            wx.hideLoading()
            that.setData({
              stepList: []
            })
          }
          if (datas.error_code == 0) {
            let stepList = that.data.stepList;
            stepList = stepList.concat(datas.data.list);
            stepList.forEach(item => {
              item.tuLen = item.media_list.length
              if (item.media_list.length > 0) {
                item.videoInfo = item.media_list[0].type
              }  
              let wximgs = wximg.substring(wximg.length - 20)
              if ((item.wx_headimg).substring((item.wx_headimg).length - 20) == wximgs) {
                item.infoName = true
              }
            })
            that.setData({
              stepList: stepList
            })
            if (datas.data.list.length < 5) {
              that.setData({
                nomore: 3
              })
            } else {
              that.setData({
                nomore: 2,
                page: that.data.page + 1
              })
            }
          } else {
            that.setData({
              nomore: 3
            })

          }
        })
      },
      fail: function() {
        wx.showToast({
          title: 'articlelist请求错误',
          icon: 'none'
        })
      }
    })
  },
  //滚动到底部100px时触发
  lower: function(e) {
    this.getdetail()
  },
  toItem: function(e) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: that.data.id
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let id = that.data.id
            let url = e.currentTarget.dataset.url
            wx.navigateTo({
              url: url + "?id=" + id
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 2000,
              success: function() {
                setTimeout(function() {
                  wx.navigateBack({
                    url: '../home/home',
                  })
                }, 2000);
              }
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: '暂无登陆信息,请登陆',
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 1000);
          }
        })
      }
    })
  },
  getLive() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: that.data.id
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let dataList = res.data
            that.setData({
              company_info: dataList.company_info,
              info: dataList.info,
              step_list: dataList.step_list,
              stepLen: dataList.step_list.length
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 2000
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: '暂无登陆信息,请登陆',
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 1000);
          }
        })
      }
    })
  },
  getInfo() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: that.data.id
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            return false
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 2000
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: '暂无登陆信息,请登陆',
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 1000);
          }
        })
      }
    })
  },
  sanDetail(e) {
    let that = this
    let id = e.currentTarget.dataset.id
    wx.showModal({
      content: '是否确认删除此条施工信息',
      success(res) {
        if (res.confirm) {
          wx.getStorage({
            key: 'info',
            success(res) {
              let token = res.data.token;
              sanDetail('/business/worksite/applet/info_delete', {
                id: id
              }, {
                'content-type': 'application/x-www-form-urlencoded',
                'token': token
              }).then(res => {
                if (res.error_code == 0) {
                  let stepList = that.data.stepList
                  let arr = []
                  arr = stepList.filter((item, index) => {
                    return item.id != id
                  })
                  that.setData({
                    stepList: arr
                  })
                  wx.showToast({
                    title: '已删除',
                    icon: 'success',
                    duration: 2000
                  })
                } else {
                  wx.showToast({
                    title: res.error_msg,
                    icon: 'none',
                    duration: 1000
                  })
                }
              })
            },
            fail: function() {

            }
          })
        } else if (res.cancel) {

        }
      }
    })
  },
  xiuDetail(e) {
    let live_id = e.currentTarget.dataset.id
    let id = this.data.id
    wx.navigateTo({
      url: "../addsg/addsg?id=" + id + "&live_id=" + live_id,
    })
  },
  junGong() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success(res) {
        let token = res.data.token;
        getLive('/business/worksite/applet/live_detail', {
          id: that.data.id
        }, {
          'content-type': 'application/x-www-form-urlencoded',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let id = that.data.id
            wx.showModal({
              content: '请确认完成此工地所有项目，可竣工验收',
              cancelColor: "#666666",
              confirmColor: "#FF5353",
              confirmText: "确认竣工",
              success: function(res) {
                if (res.confirm) {
                  that.completeOperate(id)
                } else if (res.cancel) {}
              }
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 2000,
              success: function() {
                setTimeout(function() {
                  wx.navigateBack({
                    url: '../home/home',
                  })
                }, 2000);
              }
            })
          }
        })
      },
      fail: function() {
        wx.showToast({
          title: '暂无登陆信息,请登陆',
          icon: 'none',
          duration: 1000,
          success: function() {
            setTimeout(function() {
              wx.navigateTo({
                url: '../login/login',
              })
            }, 1000);
          }
        })
      }
    })

  },
  completeOperate: function(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        completeOperate('/business/worksite/applet/completed_live', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              step: '',
              nomore: 2,
              stepList: [],
              page: 1
            })
            that.getLive()
            that.getdetail()
          } else {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 1000
            })

          }
        }).catch(function(error) {

        })
      },
      fail: function(res) {}
    })
  },
  tuClick(e) {
    let id = e.currentTarget.dataset.id
    let tid = e.currentTarget.dataset.tid
    let that = this
    let stepList = that.data.stepList
    console.log(stepList)
    var imglist = stepList.find(function(imglist) {
      return imglist.id == id
    })
    console.log(imglist)
    let swiperData = imglist.media_list
    let startNum = swiperData[0].id
    let currentNum = (tid - startNum) + 1

    that.setData({
      swiperData: swiperData,
      swiperLen: swiperData.length,
      currentIndex: currentNum,
      largeView: !that.data.largeView
    })
  },
  getLargeImage() {
    this.setData({
      largeView: !this.data.largeView
    })
  },
  swiperChange(e) {
    this.setData({
      currentIndex: e.detail.current + 1
    })
  },
  onLoad: function(options) {
    let id = options.id
    this.setData({
      id: id
    })
    this.getLive()
    // this.getdetail()
  },
  onShow: function() {
    this.setData({
      scrollInfo: true,
      step: '',
      nomore: 2,
      stepList: [],
      page: 1
    })
    this.getdetail()
    this.getLive()
  },

  onHide: function() {

  },

  onUnload: function() {

  },


  onPullDownRefresh: function() {

  },


  onReachBottom: function() {

  },

  onShareAppMessage: function() {

  }
})