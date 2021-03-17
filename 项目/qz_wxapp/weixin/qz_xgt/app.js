//app.js
var config = require('./config')

require('./utils/push_sdk.js')
var QQMapWX = require('./utils/qqmap-wx-jssdk.min.js');
const mtjwxsdk = require("./utils/mtj-wx-sdk.js");

App({
  onLaunch: function(options) {
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs);

    // 查看场景值
    if (options.scene == 1020 || options.scene == 1035 || options.scene == 1036 || options.scene == 1037 || options.scene == 1038 || options.scene == 1043) {
      if (options.referrerInfo.appId == 'wxa0e758a2610a0e1f') { // 齐装网装修管家
        this.globalData.sourceMark = 'xcx-4';
      } else if (options.referrerInfo.appId == 'wx6b5407a1d54e6013') { // 装修百宝书
        this.globalData.sourceMark = 'xcx-5';
      } else if (options.referrerInfo.appId == 'wx82551f1467f205f0') { // 乐活百宝书
        this.globalData.sourceMark = 'xcx-6';
      } else if (options.referrerInfo.appId == 'wx5325d2552984b88e') { // 去哪装修
        this.globalData.sourceMark = 'xcx-7';
      } else if (options.referrerInfo.appId == 'wxac655b4229e6ddfc') { // 装修达人周刊
        this.globalData.sourceMark = 'xcx-8';
      } else if (options.referrerInfo.appId == 'wxb2cd8162d0ed9c26') { // 齐装网家装助手
        this.globalData.sourceMark = 'xcx-9';
      } else if (options.referrerInfo.appId == 'wx051e36a624bd7c2c') { // 诸葛装修
        this.globalData.sourceMark = 'xcx-10';
      } else if (options.referrerInfo.appId == 'wx0f4d716224508e09') { // 装修进行时
        this.globalData.sourceMark = 'xcx-11';
      } else if (options.referrerInfo.appId == 'wx494b79e803022452') { // 装修PLUS
        this.globalData.sourceMark = 'xcx-12';
      } else if (options.referrerInfo.appId == 'wxb3f16ec735077b6d') { // 麦子家居
        this.globalData.sourceMark = 'xcx-13';
      } else {
        this.globalData.sourceMark = config.service.sourceMark;
      }
    }
  },
  getUserInfo: function(cb) {
    let that = this;
    let apiUrl = that.getApiUrl();
    let apid = that.getAPPid();
    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      //调用登录接口
      wx.getUserInfo({
        withCredentials: false,
        success: function(res) {
          res.userInfo.error = 'ok';
          let info = res.userInfo;
          wx.login({
            success: function(res) {
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
                  success: function(res) {
                    info.userId = res.data.data;
                  }
                });
              }
            }
          });
          that.globalData.userInfo = info;
          typeof cb == "function" && cb(that.globalData.userInfo);
        },
        fail: function(res) {
          that.globalData.userInfo = {
            error: 'error',
            nickName: '游客登录'
          };
        }
      });
    }
  },
  getLoginAgain: function(cb) {
    let that = this;
    let apiUrl = that.getApiUrl();
    let apid = that.getAPPid();
    if (this.globalData.userInfo.error != 'error') {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      wx.openSetting({
        success: function(res) {
          wx.getSetting({
            success: function(res) {
              wx.getUserInfo({
                withCredentials: false,
                success: function(res) {
                  res.userInfo.error = 'ok';
                  let info = res.userInfo;
                  wx.login({
                    success: function(res) {
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
                          success: function(res) {
                            info.userId = res.data.data;
                          }
                        });
                      }
                    }
                  });
                  that.globalData.userInfo = info;
                  typeof cb == "function" && cb(that.globalData.userInfo);
                },
                fail: function(res) {
                  that.globalData.userInfo = {
                    error: 'error',
                    nickName: '游客登录'
                  };
                }
              });
            }
          })
        }
      })
    }
  },
  globalData: {
    userInfo: null,
    again: null,
    //局部小程序配置 ⑥ 配置推广渠道标识
    sourceMark: config.service.sourceMark
  },
  getApiUrl: function() {
    let apiUrl = config.service.host_api;
    return apiUrl
  },
  getImgUrl: function() {
    let imgUrl = config.service.host_img;
    return imgUrl
  },
  getAPPid: function() {
    //局部小程序配置: ① 修改appid
    let appid = config.service.appid;
    return appid
  },
  gettitle: function() {
    //局部小程序配置: ② 修改局部名称
    let xcxtitle = config.service.xcxtitle;
    return xcxtitle
  },
  getJubuGonglueType: function() {
    //局部小程序配置: ③ 修改局部api 攻略调用类型
    let jubuGonglueType = config.service.jubuGonglueType;
    return jubuGonglueType
  },
  getJubuxgtType: function() {
    //局部小程序配置: ④ 修改局部api 效果图调用类型
    let jubuxgtType = config.service.jubuxgtType;
    return jubuxgtType
  },
  getJubusyxgtpx: function() {
    //局部小程序配置: ⑤ 修改首页效果图数据排序
    let jubusyxgtpx = config.service.jubusyxgtpx;
    return jubusyxgtpx
  },
  // 设置缓存方法
  setNewStorage: function(k, v, t) {
    // 设置缓存数据
    wx.setStorageSync(k, v);
    let s = parseInt(t); // 设置的缓存时间 s/秒
    if (s > 0) {
      let timestamp = Date.parse(new Date()); // 系统当前时间
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
  getNewStorage: function(k, def) {
    let deadtime = parseInt(wx.getStorageSync(k + 'Time')); // 获取缓存的过期时间
    let nowTime = Date.parse(new Date()) / 1000;
    if (deadtime) {
      if (deadtime < nowTime) { // 当时间到期
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
    } else { // 如果没有设置缓存时间
      let res = wx.getStorageSync(k);
      if (res) {
        return res;
      } else {
        return def;
      }
    }
  },
  onShow: function() {
    wx.setStorageSync('popState', 'true');
  },
  onHide: function() {
    wx.setStorageSync('popState', 'false');
  },
  getMyPosition: function(cityJsonNew, that, index) {
    this.getLocationCity(that);
    wx.getStorage({
      key: 'locationCity',
      success: function(res) {
        let cityId;
        if (cityJsonNew.json && cityJsonNew.json.length > 0) {
          for (let i = 0; i < cityJsonNew.json.length; i++) {
            if (cityJsonNew.json[i].text.indexOf(res.data.currentProv) != -1) {
              let cityJson = cityJsonNew.json[i].children;
              for (let j = 0; j < cityJson.length; j++) {
                if (res.data.currentCity.indexOf(cityJson[j].text) != -1) {
                  let areaJson = cityJson[j].children;
                  cityId = cityJson[j].id;
                }
              }
            }
          }
          // 绑定城市所有数据
          that.setData({
            ["fd.selectText"]: res.data.currentProv + " " + res.data.currentCity,
            ["fd.selectTextDefault"]: '',
            ["fd.colorCont"]: [true],
            ['fd.cityId']: cityId,
            ["fd.data.selectText"]: res.data.currentProv + " " + res.data.currentCity,
            ["fd.data.selectTextDefault"]: '',
            ["fd.data.colorCont"]: [true],
            ['fd.data.cityId']: cityId,
            selectText: res.data.currentProv + " " + res.data.currentCity,
            selectTextDefault: '',
            colorCont: [true],
            cityId: cityId,
          })
        }
      },
      fail: function(res) {
        console.log(res)
      }
    });
  },
  getLocationCity: function(that) {
    wx.getStorage({
      key: 'locationCity',
      success: function(res) {

      },
      fail: function() {
        let qqmapsdk = new QQMapWX({
          key: config.service.qqMapKey
        });
        wx.getLocation({
          type: 'wgs84',
          success: function(res) {
            var latitude = res.latitude
            var longitude = res.longitude
            qqmapsdk.reverseGeocoder({
              location: {
                latitude: latitude,
                longitude: longitude
              },
              success: function(data) {
                let currentProv = data.result.address_component.province;
                let currentCity = data.result.address_component.city;
                let currentArea = data.result.address_component.district;
                wx.setStorage({
                  key: 'locationCity',
                  data: {
                    currentProv: currentProv,
                    currentCity: currentCity,
                    currentArea: currentArea,
                  },
                })
                that.setData({
                  selectText: currentProv + " " + currentCity,
                  selectTextDefault: '',
                  cityId: 320500,
                })
              },
              fail: function(data) {
                console.log(data)
              }
            })
          },
          fail: function(res) {
            console.log('fail' + JSON.stringify(res))
          }
        })
      }
    })
  }
})