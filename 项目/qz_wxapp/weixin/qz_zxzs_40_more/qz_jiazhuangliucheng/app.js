var aldstat = require("./utils/ald-stat.js");
//app.js
App({
    onLaunch: function (options) {
        // 展示本地存储能力
        var logs = wx.getStorageSync('logs') || []
        logs.unshift(Date.now())
        wx.setStorageSync('logs', logs);
        
        // 查看场景值
        if (options.scene == 1020 || options.scene == 1035 || options.scene == 1036 || options.scene == 1037 || options.scene == 1038 || options.scene == 1043){
          
              this.globalData.sourceMark = 'lljs-wxxcx';
        }
    },
    getUserInfo: function (cb) {
        let that = this;
        let apiUrl = that.getApiUrl();
        if (this.globalData.userInfo) {
            typeof cb == "function" && cb(this.globalData.userInfo)
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
                                        appid: 'wxbf01eb5781c89e13',
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
                    that.globalData.userInfo = { error: 'error', nickName: '游客' };
                }
            });
        }
    },
    getLoginAgain: function (cb){
        let that = this;
        let apiUrl = that.getApiUrl();
        if (this.globalData.userInfo.error !='error'){
            typeof cb == "function" && cb(this.globalData.userInfo)
        }else{
           wx.openSetting({
                success:function(){
                    wx.getSetting({
                        success:function(){
                            wx.getUserInfo({
                                withCredentials: false,
                                success: function (res) {
                                    res.userInfo.error = 'ok';
                                    let info = res.userInfo;
                                    wx.login({
                                        success: function (l) {
                                            if (l.code) {
                                                wx.request({
                                                    url: apiUrl + '/login',
                                                    data: {
                                                        appid: 'wxbf01eb5781c89e13',
                                                        code: l.code,
                                                        name: info.nickName,
                                                        logo: info.avatarUrl
                                                    },
                                                    header: {
                                                        'content-type': 'application/x-www-form-urlencoded'
                                                    },
                                                    method: "POST",
                                                    dataType: 'json',
                                                    success: function (i) {
                                                        info.userId = i.data.data;
                                                    }
                                                });
                                            }
                                        }
                                    });
                                    that.globalData.userInfo = info;
                                    typeof cb == "function" && cb(that.globalData.userInfo);
                                },
                                fail: function (res) {
                                    that.globalData.userInfo = { error: 'error', nickName: '游客' };
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
        sourceMark:''
    },
    getApiUrl: function () {
        let scheme = 'https://';
        let host = 'wxapp.qizuang.com';
        let apiUrl = scheme + host;
        return apiUrl
    },
    getImgUrl: function () {
        let scheme = 'http://';
        let host = 'staticqn.qizuang.com/';
        let imgUrl = scheme + host;
        return imgUrl
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
    onShow:function(){
        wx.setStorageSync('popState', 'true');
    },
    onHide:function(){
        wx.setStorageSync('popState', 'false');
    }
})