//app.js
var bmap = require('./utils/bmap-wx.js');
App({
  onLaunch: function () {
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

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
  globalData: {
    userInfo: null,
    sourceMark: 'xcx-qzwqwjjdz ',
  },
  getMyPosition: function (cityJsonNew, that, index) { //自动定位
    let mapInfo = new bmap.BMapWX({
      ak: 'bDtH352XmsGwr6wAoflISevCxZV87GVd'
    });
    mapInfo.regeocoding({
      success: function (data) {
        let currentProv = data.originalData.result.addressComponent.province;
        let currentCity = data.originalData.result.addressComponent.city;
        let currentArea = data.originalData.result.addressComponent.district;
        if (!cityJsonNew.json) {
          cityJsonNew.json = cityJsonNew
        }
        for (let i = 0; i < cityJsonNew.json.length; i++) {
          if (cityJsonNew.json[i].text.indexOf(currentProv) != -1) {
            let cityJson = cityJsonNew.json[i].children;
            for (let j = 0; j < cityJson.length; j++) {
              if (currentCity.indexOf(cityJson[j].text) != -1) {
                let areaJson = cityJson[j].children;
                if (index == 1) {
                  that.setData({
                    ['fd.prevCityAreaId']: cityJson[j].id
                  })
                } else if (index == 2) {
                  that.setData({
                    ['fd.data.prevCityAreaId']: cityJson[j].id
                  })
                } else {
                  that.setData({
                    cityId: cityJson[j].id
                  })
                }
                for (let k = 0; k < areaJson.length; k++) {
                  if (areaJson[k].text.indexOf(currentArea) != -1) {
                    if (index == 1) {
                      that.setData({
                        ['fd.areaId']: areaJson[k].id
                      })
                    } else if (index == 2) {
                      that.setData({
                        ['fd.data.areaId']: cityJson[j].id
                      })
                    } else {
                      that.setData({
                        areaId: areaJson[k].id
                      })
                    }
                  }
                }
              }
            }
          }
        }
        if (index == 1) {
          that.setData({
            ["fd.selectText"]: currentProv + " " + currentCity + " " + currentArea,
            ["fd.selectTextDefault"]: '',
            ["fd.colorCont"]: [true]
          })
        } else if (index == 2) {
          that.setData({
            ["fd.data.selectText"]: currentProv + " " + currentCity + " " + currentArea,
            ["fd.data.selectTextDefault"]: '',
            ["fd.data.colorCont"]: [true]
          })
        } else {
          that.setData({
            selectText: currentProv + " " + currentCity + " " + currentArea,
            selectTextDefault: '',
            colorCont: [true]
          })
        }
      }
    });
  },
  getApiUrl: function () {
    let apiUrl = 'https://appapi.qizuang.com';
    return apiUrl
  },
})