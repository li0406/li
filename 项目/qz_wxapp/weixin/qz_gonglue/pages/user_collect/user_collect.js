// pages/user_collect/user_collect.js
let app = getApp();
let apiUrl = app.getApiUrl();
let oImgUrl = app.getImgUrl();
// function GetRandomNum(Min, Max) {
//   var Range = Max - Min;
//   var Rand = Math.random();
//   return (Min + Math.round(Rand * Range));
// }
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isHide: [true, false],
    isEmpty: [true, true],
    zxgl: [],
    spArr: [],
    oImgUrl: oImgUrl,
    loginUserId: '',
    currentTab:'',
    lingNum:''
  },
  /*** 滑动切换tab***/
  bindChange: function (e) {
    var that = this;
    that.setData({ currentTab: e.detail.current });
  },
  /*** 点击tab切换***/
  swichNav: function (e) {
    var that = this;
    // console.log(e)
    that.setData({
      currentTab: e.currentTarget.dataset.current
    });

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let userId = '',
      that = this;
    //swiper的高度计算
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          clientHeight: res.windowHeight
        });
      }
    });
    app.getUserInfo((res) => {
      if (res.userId || that.data.loginUserId) {
        // 获取攻略
        wx.request({
          url: apiUrl + '/appletcarousel/collect',
          data: {
            count: '10',
            userid: res.userId,
            classtype: '10'//攻略
          },
          dataType: 'json',
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            let empty = that.data.isEmpty;
            if (res.data.length > 0) {
              empty[0] = true;
              that.setData({ zxgl: res.data, isEmpty: empty })
            } else {
              empty[0] = false;
              that.setData({ zxgl: res.data, isEmpty: empty })
            }
          }
        });
        // 获取视频
        wx.request({
          url: apiUrl + '/appletcarousel/collect',
          data: {
            count: '10',
            userid: res.userId,
            classtype: '11'//视频
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            console.log(res)
            let empty = that.data.isEmpty;
            if (res.data.length > 0) {
              empty[1] = true;
              that.setData({ spArr: res.data, isEmpty: empty })
            } else {
              empty[1] = false;
              that.setData({ spArr: res.data, isEmpty: empty })
            }
          }
        });
      } else {
        app.getLoginAgain((res) => {
          // 获取攻略
          wx.request({
            url: apiUrl + '/appletcarousel/collect',
            data: {
              count: '10',
              userid: userId,
              classtype: '10'//攻略
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              if(res.data.info == "未找到当前用户"){
                that.setData({ zxgl: []})
              }else{
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[0] = true;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                } else {
                  empty[0] = false;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                }
              }
              
            }
          });
          // 获取视频
          wx.request({
            url: apiUrl + '/appletcarousel/collect',
            data: {
              count: '10',
              userid: userId,
              classtype: '11'//视频
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              if (res.data.info == "未找到当前用户") {
                that.setData({ spArr: []})
              }else {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[1] = true;
                  that.setData({ spArr: res.data, isEmpty: empty })
                } else {
                  empty[1] = false;
                  that.setData({ spArr: res.data, isEmpty: empty })
                }
              }
            }
          });
        })
        that.setData({ isEmpty: [false, false] })
      }
    });
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage(res) {
    if (res.from === 'button') {
      // 来自页面内转发按钮
      if (res.target.dataset.style == 'sp') {
        return {
          title: res.target.dataset.title,
          path: '' + res.target.dataset.id + '' + res.target.dataset.title,
          imageUrl: oImgUrl + res.target.dataset.imgsrc,
          success(res) {
            // 转发成功
          },
          fail(res) {
            // 转发失败
          }
        }
      } else if (res.target.dataset.style == 'zxgl') {
        return {
          title: res.target.dataset.title,
          path: '' + res.target.dataset.id,
          imageUrl: oImgUrl + res.target.dataset.imgsrc,
          success(res) {
            // 转发成功
          },
          fail(res) {
            // 转发失败
          }
        }
      }
    }
  },
   /**
   * 点击跳转到设计页面
   */
  toSheji() {
    wx.navigateTo({ url: '../zhuangxiusj/zhuangxiusj' });
  },
  //阻止下拉刷新
  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
  },
  //取消收藏
  toDel(e) {
    let id = e.currentTarget.dataset.id,
      classType = e.currentTarget.dataset.classtype,
      userId = e.currentTarget.dataset.userid,
      that = this;
      // console.log(userId);
      if (classType === "10") {
        wx.request({
          url: apiUrl + '/appletcarousel/editcollect',
          data: {
            userid: userId, // 用户ID
            classtype: '10', // 装修攻略
            classid: id
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            if (res.data.state == 1) {
              // 获取装修攻略
              wx.request({
                url: apiUrl + '/appletcarousel/collect',
                data: {
                  count: '10',
                  userid: userId,
                  classtype: '10'//装修攻略
                },
                header: {
                  'content-type': 'application/x-www-form-urlencoded'
                },
                success(res) {
                  let empty = that.data.isEmpty;
                  if (res.data.length > 0) {
                    empty[0] = true;
                    that.setData({ zxgl: res.data, isEmpty: empty })
                  } else {
                    empty[0] = false;
                    that.setData({ zxgl: res.data, isEmpty: empty })
                  }
                  //收藏取消弹框
                  wx.showToast({
                    title: '你已取消收藏',
                    icon: 'success',
                    duration: 1200
                  });
                  //下拉刷新
                  wx.startPullDownRefresh();
                  wx.stopPullDownRefresh();
                }
              });
            }
          }
        });
      } else if (classType === "11") {
        wx.request({
          url: apiUrl + '/appletcarousel/editcollect',
          data: {
            userid: userId, // 用户ID
            classtype: '11', 
            classid: id
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            if (res.data.state == 1) {
              wx.request({
                url: apiUrl + '/appletcarousel/collect',
                data: {
                  userid: userId,
                  classtype: '11'
                },
                header: {
                  'content-type': 'application/x-www-form-urlencoded'
                },
                success(res) {
                  let empty = that.data.isEmpty;
                  if (res.data.length > 0) {
                    empty[1] = true;
                    that.setData({ spArr: res.data, isEmpty: empty })
                  } else {
                    empty[1] = false;
                    that.setData({ spArr: res.data, isEmpty: empty })
                  }
                  //收藏取消弹框
                  wx.showToast({
                    title: '你已取消收藏',
                    icon: 'success',
                    duration: 1200
                  });

                  //下拉刷新
                  wx.startPullDownRefresh();
                  wx.stopPullDownRefresh();
                },
                fail:function(){
                  console.log(111)
                }
              });
            }
          }
        });
      }

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function (option) {
    let that = this;
    console.log(app.globalData)
    that.setData({ lingNum: app.globalData.fadanNum });

    app.getUserInfo((res) => {
      if (res.userId || that.data.loginUserId) {
        // 获取攻略
        wx.request({
          url: apiUrl + '/appletcarousel/collect',
          data: {
            count: '10',
            userid: res.userId,
            classtype: '10'//攻略
          },
          dataType: 'json',
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            let empty = that.data.isEmpty;
            if (res.data.length > 0) {
              empty[0] = true;
              that.setData({ zxgl: res.data, isEmpty: empty })
            } else {
              empty[0] = false;
              that.setData({ zxgl: res.data, isEmpty: empty })
            }
          }
        });
        // 获取视频
        wx.request({
          url: apiUrl + '/appletcarousel/collect',
          data: {
            count: '10',
            userid: res.userId,
            classtype: '11'//视频
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success(res) {
            console.log(res)
            let empty = that.data.isEmpty;
            if (res.data.length > 0) {
              empty[1] = true;
              that.setData({ spArr: res.data, isEmpty: empty })
            } else {
              empty[1] = false;
              that.setData({ spArr: res.data, isEmpty: empty })
            }
          }
        });
      } else {
        app.getLoginAgain((res) => {
          // 获取攻略
          wx.request({
            url: apiUrl + '/appletcarousel/collect',
            data: {
              count: '10',
              userid: userId,
              classtype: '10'//攻略
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              if (res.data.info == "未找到当前用户") {
                that.setData({ zxgl: [] })
              } else {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[0] = true;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                } else {
                  empty[0] = false;
                  that.setData({ zxgl: res.data, isEmpty: empty })
                }
              }

            }
          });
          // 获取视频
          wx.request({
            url: apiUrl + '/appletcarousel/collect',
            data: {
              count: '10',
              userid: userId,
              classtype: '11'//视频
            },
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success(res) {
              if (res.data.info == "未找到当前用户") {
                that.setData({ spArr: [] })
              } else {
                let empty = that.data.isEmpty;
                if (res.data.length > 0) {
                  empty[1] = true;
                  that.setData({ spArr: res.data, isEmpty: empty })
                } else {
                  empty[1] = false;
                  that.setData({ spArr: res.data, isEmpty: empty })
                }
              }
            }
          });
        })
        that.setData({ isEmpty: [false, false] })
      }
    });
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