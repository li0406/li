//app.js
var config = require('./config')
App({
  onLaunch: function () {
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
    wx.setStorage({
      key: 'src',
      data: config.service.sourceMark,
    });
    // 登录
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
      }
    })
    // 获取用户信息
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
          wx.getUserInfo({
            success: res => {
              // 可以将 res 发送给后台解码出 unionId
              this.globalData.userInfo = res.userInfo

              // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
              // 所以此处加入 callback 以防止这种情况
              if (this.userInfoReadyCallback) {
                this.userInfoReadyCallback(res)
              }
            }
          })
        }
      }
    })
  },

  getUserInfo: function (cb) {
    let that = this;
    let apiUrl = that.getApiUrl();
    let appid = that.getAPPid();

    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo)
      if (typeof(this.globalData.userInfo.userId) == 'undefined'){
        //调用登录接口
        wx.getUserInfo({
          withCredentials: false,
          success: function (res) {
            res.userInfo.error = 'ok';
            let info = res.userInfo;
          
            wx.login({
              success: function (res) {
                if (res.code) {
                  wx.request({
                    url: apiUrl + '/login',
                    data: {
                      appid: appid,
                      code: res.code,
                      name: info.nickName,
                      logo: info.avatarUrl
                    },
                    header: {
                      'content-type': 'application/x-www-form-urlencoded'
                    },
                    method: "POST",
                    dataType: 'json',
                    success: function (res) {
                      info.userId = res.data.data;
                      that.globalData.userInfo = info;
                      typeof cb == "function" && cb(that.globalData.userInfo);
                    }
                  });
                }
              }
            });

          },
          fail: function (res) {
            that.globalData.userInfo = { error: 'error', nickName: '游客登录', userId: "" };
            typeof cb == "function" && cb(that.globalData.userInfo);
          }
        });
      }
    } else {
      //调用登录接口
      wx.getUserInfo({
        withCredentials: false,
        success: function (res) {
          res.userInfo.error = 'ok';
          let info = res.userInfo;
          wx.login({
            success: function (res) {
              
              if (res.code) {
                wx.request({
                  url: apiUrl + '/login',
                  data: {
                    appid: appid,
                    code: res.code,
                    name: info.nickName,
                    logo: info.avatarUrl
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  method: "POST",
                  dataType: 'json',
                  success: function (res) {
                    info.userId = res.data.data;
                    that.globalData.userInfo = info;
                    
                    typeof cb == "function" && cb(that.globalData.userInfo);
                  }
                });
              }
            }
          });
          
        },
        fail: function (res) {
          that.globalData.userInfo = { error: 'error', nickName: '游客登录',userId:"" };
          typeof cb == "function" && cb(that.globalData.userInfo);
        }
      });
    }
  },
  getLoginAgain: function (cb) {
    let that = this;
    let apiUrl = that.getApiUrl();
    let apid = that.getAPPid();
    if (this.globalData.userInfo.error != 'error') {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      wx.openSetting({
        success: function (res) {
          wx.getSetting({
            success: function (res) {
              wx.getUserInfo({
                withCredentials: false,
                success: function (res) {
                  res.userInfo.error = 'ok';
                  let info = res.userInfo;
                  wx.login({
                    success: function (res) {
                      
                      if (res.code) {
                        wx.request({
                          url: apiUrl + '/login',
                          data: {
                            appid: apid,
                            code: res.code,
                            name: info.nickName,
                            logo: info.avatarUrl
                          },
                          header: {
                            'content-type': 'application/x-www-form-urlencoded'
                          },
                          method: "POST",
                          dataType: 'json',
                          success: function (res) {
                            info.userId = res.data.data;
                          }
                        });
                      }
                    }
                  });
                  that.globalData.userInfo = info;
                  typeof cb == "function" && cb(that.globalData.userInfo);
                },
                fail: function (res) {
                  that.globalData.userInfo = { error: 'error', nickName: '游客登录' };
                }
              });
            }
          })
        }
      })
    }
  },

  // 设置缓存方法
  setNewStorage: function (k, v, t) {
    // 设置缓存数据
    wx.setStorageSync(k, v);
    let s = parseInt(t);// 设置的缓存时间 s/秒
    if (s > 0) {
      let timestamp = Date.parse(new Date());// 系统当前时间
      timestamp = (timestamp / 1000) + s; //缓存到期时间点
      // 存储缓存时间
      wx.setStorage({
        key: k + 'Time',
        data: timestamp + '',
      });
    } else {
      wx.removeStorageSync(k + 'Time');
    }
  },
  // 获取缓存方法
  getNewStorage: function (k, def) {
    let deadtime = parseInt(wx.getStorageSync(k + 'Time'));// 获取缓存的过期时间
    let nowTime = Date.parse(new Date()) / 1000;
    if (deadtime) {
      if (deadtime < nowTime) {// 当时间到期
        // console.log('过期了')
        if (def) {
          return def
        } else {
          return
        }
      } else {
        // console.log('未过期')
        let res = wx.getStorageSync(k);
        if (res) {
          return res;
        } else {
          return def;
        }
      }
    } else {// 如果没有设置缓存时间
      let res = wx.getStorageSync(k);
      if (res) {
        return res;
      } else {
        return def;
      }
    }
  },

  globalData: {
    userInfo: null,
    sourceMark: config.service.sourceMark,
    personNum:0,
    fadanNum:521
  },

  getApiUrl: function () {
    let apiUrl = config.service.host_api;
    return apiUrl
  },
  getImgUrl: function () {
    let imgUrl = config.service.host_img;
    return imgUrl
  },
  getAPPid: function () {
    //局部小程序配置: ① 修改appid
    let appid = config.service.appid;
    return appid
  },

  onShow: function () {
    wx.setStorageSync('popState', 'true');
  },
  onHide: function () {
    wx.setStorageSync('popState', 'false');
  }

})