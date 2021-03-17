//app.js
import config from './config.js'

App({
    onLaunch: function (options) {
        // 展示本地存储能力
        var logs = wx.getStorageSync('logs') || []
        logs.unshift(Date.now())
        wx.setStorageSync('logs', logs);
    },
    getUserInfo: function (cb) {
        let that = this,
            apiUrl = that.getApiUrl(),
            apid = that.getAPPid();
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
                    that.globalData.userInfo = { error: 'error', nickName: '游客' };
                }
            });
        }
    },
    getLoginAgain: function (cb){
        let that = this;
        let apiUrl = that.getApiUrl(),
            apid = that.getAPPid();
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
                                                        appid: apid,
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
        again: null,
        //局部小程序配置 ⑥ 配置推广渠道标识
        sourceMark: config.service.sourceMark
    },
    getApiUrl () {
        let apiUrl = config.service.host_api;
        return apiUrl
    },
    getImgUrl () {
        let imgUrl = config.service.host_img;
        return imgUrl
    },
    getAPPid () {
        //局部小程序配置: ① 修改appid
        let appid = config.service.appid;
        return appid
    },
    gettitle () {
        //局部小程序配置: ② 修改局部名称
        let xcxtitle = config.service.xcxtitle;
        return xcxtitle
    },
    // 设置缓存方法
    setNewStorage (k, v, t) {
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
    getNewStorage (k, def) {
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
    onShow (){
        wx.setStorageSync('popState', 'true');
    },
    onHide (){
        wx.setStorageSync('popState', 'false');
    }
})