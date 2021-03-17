//app.js
App({
  onLaunch: function(options) {
    //调用API从本地缓存中获取数据
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now());
    wx.setStorageSync('logs', logs);
    // 查看场景值
    console.log(options.scene)
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
            this.globalData.sourceMark = 'xcx-0';
        }
    } else {
        this.globalData.sourceMark = 'xcx-0';
    }
  },
  getUserInfo: function(cb) {
    let that = this;
    let apiUrl = that.getApiUrl();
    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      //调用登录接口
      wx.getUserInfo({
        withCredentials: false,
        success: function(res) {
          res.userInfo.error='ok';
          let info = res.userInfo;
          wx.login({
            success: function (res) {
              if (res.code) {
                wx.request({
                  url: apiUrl+'/login',
                  data: {
                    appid:'wx026c84e92848fea1',
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
        fail:function(res){
          that.globalData.userInfo = { error: 'error', nickName:'游客'};
        }
      });
    }
  },

  globalData: {
    userInfo: null,
    sourceMark:''
  },
  getApiUrl:function(){
    let scheme = 'https://';
    let host = 'wxapp.qizuang.com';
    let apiUrl = scheme + host;
    return apiUrl
  },
  getImgUrl:function(){
    let scheme = 'https://';
    let host = 'staticqn.qizuang.com/';
    let imgUrl = scheme + host;
    return imgUrl
  },
  getCityDataUrl: function (){
    let that = this;
    let apiUrl = that.getApiUrl();
    wx.request({
      url: apiUrl + '/zxjt/getcityurl',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        return res.data.data;
      }
    })
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
        key: k + 't',
        data: timestamp + '',
      });
    } else {
      wx.removeStorageSync(k + 't');
    }
  },
  // 获取缓存方法
  getNewStorage: function (k, def) {
    let deadtime = parseInt(wx.getStorageSync(k + 't'));// 获取缓存的过期时间
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
    }else{// 如果没有设置缓存时间
      let res = wx.getStorageSync(k);
      if (res) {
        return res;
      } else {
        return def;
      }
    }
  }
})
