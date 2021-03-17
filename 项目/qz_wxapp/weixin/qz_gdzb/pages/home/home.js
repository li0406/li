// pages/home/home.js
var util = require('../../utils/util.js');
import {
  getHomeLIst,
  completeOperate,
  bindorder,
  getLive
} from "../../utils/api.js"
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabActive: true,
    isData: true,
    isShow: false,
    errorTit: false,
    searchShow: false,
    infoShow: '暂无施工信息',
    info: '',
    orderInfo: '',
    errorInfo: '',
    getInfoList: [],
    totalPage: '',
    isClicked: false,
    parms: {
      state: 1,
      keyword: '',
      page: 1,
      limit: 10
    },
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    if (options.scene) {
      var scene = decodeURIComponent(options.scene)
      let codeId = scene.substring(scene.length - 10)
      app.globalData.codeId = codeId
      this.codeOrder(codeId)
    }
    if (options.id) {
      let codeId = options.id
      this.codeOrder(codeId)
    }
  },
  codeOrder: function(code) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        bindorder('/business/worksite/applet/add_live', {
          code: code
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.data == null) {
            wx.showToast({
              title: res.error_msg,
              icon: 'none',
              duration: 1000
            })
          } else {
            let liveId = res.data.live_id
            app.globalData.id = liveId
            wx.navigateTo({
              url: '../sgdetail/sgdetail?id=' + liveId,
            })
          }
        }).catch(function(error) {

        })
      },
      fail: function(res) {}
    })
  },

  toSearch: function() {
    wx.navigateTo({
      url: '../search/search',
    })
  },
  changeType: function(e) {
    let that = this;
    if (that.data.parms.keyword != '') {
      if (e.currentTarget.dataset.state == 1) {
        that.setData({
          infoShow: '没有搜到“' + that.data.parms.keyword + '”相关施工信息'
        });
      } else {
        that.setData({
          infoShow: '没有搜到“' + that.data.parms.keyword + '”相关竣工信息'
        });
      }
      that.setData({
        tabActive: e.currentTarget.dataset.type == "true",
        ['parms.state']: e.currentTarget.dataset.state,
        ['parms.page']: 1
      });
    } else {
      if (e.currentTarget.dataset.state == 1) {
        that.setData({
          infoShow: '暂无施工信息'
        });
      } else {
        that.setData({
          infoShow: '暂无竣工信息'
        });
      }
      that.setData({
        tabActive: e.currentTarget.dataset.type == "true",
        ['parms.state']: e.currentTarget.dataset.state,
        ['parms.page']: 1,
        getInfoList: []
      });
    }
    that.getHomeLIst(that.data.parms)
  },
  completeConfirm: function(e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    wx.showModal({
      content: '请确认完成此工地所有项目，可竣工验收',
      cancelColor: "#666666",
      confirmColor: "#FF5353",
      confirmText: "确认竣工",
      success: function(res) {
        if (res.confirm) {
          that.setData({
            ['parms.page']: 1,
            getInfoList: []
          })
          that.completeOperate(id)
          that.getHomeLIst(that.data.parms)
        } else if (res.cancel) {}
      }
    })
  },
  toSgDetail: function(e) {
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
            setTimeout(function() {
              that.setData({
                errorTit: false,
                errorInfo: ''
              })
            }, 3000)
            that.getHomeLIst(that.data.parms)
          }
        })
      },
      fail: function() {

      }
    })
  },
  searchOrder: function(e) {
    this.setData({
      orderInfo: e.detail.value
    })
  },
  bindconfirm: function(e) {
    this.bindorder(this.data.orderInfo)
  },
  bindorder: function(code) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        bindorder('/business/worksite/applet/add_live', {
          code: code
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              isShow: false,
              errorTit: true,
              errorInfo: res.error_msg
            })
            setTimeout(function() {
              that.setData({
                ['parms.page']: 1,
                getInfoList: []
              })
              that.getHomeLIst(that.data.parms)
            }, 2000)
          } else {
            that.setData({
              errorTit: true,
              errorInfo: res.error_msg
            })
          }
          setTimeout(function() {
            that.setData({
              errorTit: false,
              errorInfo: ''
            })
          }, 2000)
        }).catch(function(error) {

        })
      },
      fail: function(res) {}
    })
  },
  chooseOrder: function() {
    var that = this;
    wx.showActionSheet({
      itemList: ['扫描施工二维码', '输入施工编号'],
      itemColor: "#00000",
      success: function(res) {
        if (!res.cancel) {
          if (res.tapIndex == 0) {
            wx.scanCode({ //扫描API
              success(res) { //扫描成功

                let code = res.path
                let codeId = code.substring(code.length - 10)
                that.bindorder(codeId)
              }
            })
          } else if (res.tapIndex == 1) {
            that.setData({
              isShow: true,
              orderInfo: ''
            })
          }
        }
      }
    })
  },
  closeOrder: function() {
    this.setData({
      isShow: false
    })
  },
  toDetail: function(e) {
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
            setTimeout(function() {
              that.setData({
                errorTit: false,
                errorInfo: ''
              })
            }, 3000)
            that.getHomeLIst(that.data.parms)
          }
        })
      },
      fail: function() {

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
            setTimeout(function() {
              that.setData({
                errorTit: false,
                errorInfo: ''
              })
            }, 2000)
          }
        }).catch(function(error) {

        })
      },
      fail: function(res) {}
    })
  },
  getHomeLIst: function(parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        getHomeLIst('/business/worksite/applet/live_list',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code === 0) {
            if (res.data.info.length > 0) {
              res.data.info.forEach(function(item, index) {
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
                isData: true
              })
            } else {
              that.setData({
                isData: false
              })
            }
          } else {
            wx.showToast({
              title: res.error_msg,
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

        }).catch(function(error) {

        })
      },
      fail: function(res) {
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
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.setData({
      isClicked: false,
      getInfoList: [],
      ['parms.page']: 1
    })
    this.getHomeLIst(this.data.parms)
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.setData({
      getInfoList: [],
      ['parms.page']: 1
    })
    this.getHomeLIst(this.data.parms)
    wx.stopPullDownRefresh()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
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
        errorTit: true,
        errorInfo: '没有更多数据了'
      })
      wx.hideLoading()
      setTimeout(function() {
        that.setData({
          errorTit: false,
          errorInfo: ''
        })
      }, 1000)
    } else {
      that.setData({
        errorTit: false,
        errorInfo: ''
      })
      that.getHomeLIst(that.data.parms)
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})