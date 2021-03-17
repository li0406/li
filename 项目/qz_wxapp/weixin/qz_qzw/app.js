//app.js
// var pushApp = require('./utils/pushsdk.js').pushSdk();
require('./utils/push_sdk.js')
var config = require('./config');
const ald = require('./utils/ald-stat.js'); //阿拉丁
var QQMapWX = require('./utils/qqmap-wx-jssdk.min.js');
const mtjwxsdk = require("./utils/mtj-wx-sdk.js");
let sr = require('./utils/sr-sdk-wxapp.min.js').init({
    appid: config.service.appid, // AppID(小程序ID)
    token: config.service.sr_token, // token是唯一必须配置的参数，代表接入凭证，详见「init接口」
    proxyPage: true, // 是否开启自动代理 Page，默认是：false
  }); // 腾讯有数
App({
    sr,
    onShow(option) {
        let apiUrl = this.getApiUrl()
        let appid = this.getAPPid();
        wx.login({
            success: function (res) {
              var jsCode = res.code;
              wx.request({
                url: apiUrl + '/login',
                data: {
                    appid: appid,
                    code: res.code,
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                dataType: 'json',
                success: function(res) {
                    if (res.data && res.data.openid) {
                        let openid =  res.data && res.data.openid
                        sr.setUser({open_id: openid,})
                        wx.aldPushSendOpenid(openid)
                    }
                }
              })
            }
        })
    },
    onLaunch: function(options) {
        if (options.query.src) {
            this.globalData.sourceMark = options.query.src;
        }
        if (options.query.gdt_vid) {
            this.globalData.gdt_vid = options.query.gdt_vid
        }
        if (options.query.click_id) {
            this.globalData.click_id = options.query.click_id
        }
        if (options.query.weixinadinfo) {
            this.globalData.weixinadinfo = weixinadinfo
            let weixinadinfoArr = options.query.weixinadinfo.split('.')
            this.globalData.aid = weixinadinfoArr[0]
        }
    },
    getLoginAgain: function(cb) {
        let that = this;
        let apiUrl = that.getApiUrl();
        let apid = that.getAPPid();
        if (this.globalData.userInfo && this.globalData.userInfo.userId !== "") {
            typeof cb == "function" && cb(this.globalData.userInfo);
                wx.navigateBack({
                    url: '-1'
                })
            } else {
            wx.showLoading({
                title: "登录中",
            });
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
                                    that.globalData.userInfo = info;
                                    typeof cb == "function" && cb(that.globalData.userInfo);
                                    //授权成功后，跳转进入小程序首页
                                    wx.hideLoading();
                                }
                                });
                            }
                        }
                    })
                }
            })
        }
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
    globalData: {
        version: "v1.4.4",
        sourceMark: config.service.sourceMark,
        userInfo: null,
        personNum: 851,
        loginCode: '',
        hasPhone: '',
        gdt_vid: '',
        weixinadinfo: '',
        aid: '',
        click_id: ''
    },
    getApiUrl: function() {
        let apiUrl = config.service.host_api;
        return apiUrl
    },
    getZxsApiUrl: function() {
        let zxsApiUrl = config.service.host_zxs_api;
        return zxsApiUrl
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
    changeTime: function(time) {
        let date = new Date(time * 1000);
        let year = date.getFullYear();
        let month = date.getMonth() + 1;
        let day = date.getDate();
        let hour = date.getHours();
        let min = date.getMinutes();
        let second = date.getSeconds();
        return year + "-" + month + "-" + day + "  " + hour + ":" + min + ":" + second;
    },
    getPVNum: function() {
        let time = new Date();
        let num = time.getFullYear() - 900 + time.getMonth() + time.getDate() + time.getHours() + time.getMinutes() + time.getSeconds() + Math.ceil(Math.random() * 100);
        return num;
    },
    newSq: function(targetObj, fn) {
        let that = this;
        targetObj.cancelSq = function() {
        targetObj.setData({
            setMyNewSq: false
        })
        typeof fn == "function" && fn(0);
        }
        targetObj.hideSq = function() {
            targetObj.setData({
                setMyNewSq: false
            })
        }
        wx.getStorage({
            key: 'userInfo',
            fail: function() {
                wx.getSetting({
                success: function(data) {
                    if (data.authSetting['scope.userInfo']) {
                        targetObj.setData({
                            setMyNewSq: false
                        })
                        that.getLoginAgain(function(res) {
                            that.globalData.userInfo = res;
                            wx.setStorage({
                                key: 'userInfo',
                                data: res
                            });
                            wx.setStorage({
                                key: 'userId',
                                data: res.userId
                            })
                            typeof fn == "function" && fn(res);
                            return
                        });
                        } else {
                            targetObj.setData({
                                setMyNewSq: true
                            })
                        }
                    }
                })
                targetObj.bindgetuserinfo = function(e) {
                    if (e.detail.userInfo) { // 获取之后
                        that.getLoginAgain(function(res) {
                        that.globalData.userInfo = res;
                        wx.setStorage({
                            key: 'userInfo',
                            data: res
                        });
                        wx.setStorage({
                            key: 'userId',
                            data: res.userId
                        })
                        typeof fn == "function" && fn(res);
                        });
                    } else {
                        typeof fn == "function" && fn(0);
                    }
                }
            }
        })
    },
    getPersonNum: function() {
        // 获取随机数的方法
        function GetRandomNum(Min, Max) {
        var Range = Max - Min;
        var Rand = Math.random();
        return (Min + Math.round(Rand * Range));
        }
        let personNum = GetRandomNum(300, 1000);;
        return personNum;
    },
    getLocationCity: function(cityJsonNew, that, index) {
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
    },
    // getUser:function(){
    //     let that = this;
    //     //授权userId
    //     wx.getStorage({
    //         key: 'userId',
    //         success: function (res) {
    //             console.log(res)
    //             that.setData({
    //                 userid: res.data
    //             })
    //         },
    //         fail: function (res) {
    //             app.newSq(that, function (data) {
    //                 if (data === 0) {
    //                     return
    //                 }
    //                 that.setData({
    //                     userid: data.userId
    //                 })
    //             });
    //         }
    //     })
    // },
  
    getMyPosition: function(cityJsonNew, that, index) {
        this.getLocationCity();
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
    getPhoneAuto: function(e, that, index, fn) {
        if (e.detail.errMsg == 'getPhoneNumber:fail user deny') {
        if (index == 1) {
            that.setData({
            ["fd.autoGetPhone"]: true
            })
        } else if (index == 2) {
            that.setData({
            ["fd.autoGetPhone"]: true
            })
        } else {
            that.setData({
            autoGetPhone: true,
            });
        }
            return
        } else if (e.detail.errMsg == 'getPhoneNumber:ok'){
          let _that = this;
          wx.login({
            success: function (res) {
              _that.globalData.loginCode = res.code;
              wx.request({
                url: _that.getApiUrl() + '/wechat/user/info',
                header: {
                  'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                data: {
                  code: res.code,
                  encrypted_data: e.detail.encryptedData,
                  iv: e.detail.iv
                },
                success: function (res) {
                  if (index == 1) {
                    that.setData({
                      ["fd.autoGetPhone"]: true,
                      ["fd.telNumber"]: res.data.data.purePhoneNumber,
                      ["fd.emptyPhoneValue"]: res.data.data.purePhoneNumber
                    })
                  } else if (index == 2) {
                    that.setData({
                      ["fd.autoGetPhone"]: true,
                      ["fd.data.telNumber"]: res.data.data.purePhoneNumber,
                      ["fd.emptyPhoneValue"]: res.data.data.purePhoneNumber
                    })
                  } else {
                    that.setData({
                      tel: res.data.data.purePhoneNumber,
                      emptyphone: res.data.data.purePhoneNumber,
                      autoGetPhone: true,
                      inputphone: res.data.data.purePhoneNumber,
                      telNumber: res.data.data.purePhoneNumber
                    });
                  }
                  wx.setStorage({
                    key: 'telInfo',
                    data: {
                      autoGetPhone: true,
                      tel: res.data.data.purePhoneNumber
                    }
                  })
                }
              })
            }
          })
        } else {
          that.setData({
            autoGetPhone: true,
          });
        }

    },
    showLoading: function(title = '加载中') {
        wx.showLoading({
        title: title,
        icon: 'loading',
        mask: true
        });
    },
    hideLoading: function() {
        wx.hideLoading();
    },
    showToast: function(msg) {
        if (msg == undefined) {
        wx.showToast({
            title: '网络繁忙，稍后重试',
            icon: 'none',
            mask: true
        })
        } else if (typeof msg == 'string') {
        wx.showToast({
            title: msg,
            icon: 'none',
            mask: true
        })
        }
    },
})