var config = require('./config')
import CONFIG_INFO from './utils/config.js'

App({
  globalData: {
    userInfo: null,
    // userInfo : {
    //     avatar:"https://tfs.alipayobjects.com/images/partner/T1hF4aXa4dXXXXXXXX",
    //     nickName:"470211273@qq.com",
    //     userId : 1000
  
    // },
    again: null,
    sourceMark: null, // 配置推广渠道标识
    queryParams: null, // 参数
    version: "0.0.26"
  },

   onLaunch(options) {
     console.log("onLaunch:", options);
  },

  onShow(options) {
    console.log("onShow:", options);

    const updateManager = my.getUpdateManager()
    updateManager.onCheckForUpdate(function (res) {
      // 请求完新版本信息的回调
      console.log(res.hasUpdate)
    })

    updateManager.onUpdateReady(function () {
      my.confirm({
        title: '更新提示',
        content: '新版本已经准备好，是否重启应用？',
        success: function (res) {
          if (res.confirm) {
            // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
            updateManager.applyUpdate()
          }
        }
      })
    })

    updateManager.onUpdateFailed(function () {
      my.confirm({
        title: '自动更新失败',
        content: '新版本已经上线啦~，请您删除当前小程序，重新搜索打开哟~'
      })
    })

    //获取外部query和extraData参数
    if((options.query || options.referrerInfo) && !this.globalData.queryParams) {
      let queryParams= {};
      //deeplink的query参数 (h5)
      if(options.query){
        queryParams = options.query;
      }
      //小程序跳转的extraData参数
      if(options.referrerInfo && options.referrerInfo.extraData){
        queryParams = options.referrerInfo.extraData;
      }

      this.globalData.queryParams = queryParams;
      if (queryParams.src) {
        this.globalData.sourceMark = queryParams['src'];
      } else {
        // queryParams没有src给默认
        this.globalData.sourceMark = config.service.sourceMark
      }
    } else {
      if (this.globalData.queryParams && this.globalData.queryParams.src) {
        // this.globalData.queryParams 如果有值直接取
        this.globalData.sourceMark = this.globalData.queryParams.src
      } else {
        // 没有src给默认
        this.globalData.sourceMark = config.service.sourceMark
      }
    }
    console.log("globalData:",this.globalData)

    // // 获取渠道来源，存入全局参数
    // // 小程序跳转extraData参数
    // if(options.referreInfo && options.referreInfo.extraData){
    //   let extraData = options.referrerInfo.extraData
    //   this.globalData.sourceMark = extraData.src
    // }else if(options.query && options.query.src){
    //     this.globalData.sourceMark = options.query.src
    // }else {
    //     this.globalData.sourceMark = config.service.sourceMark
    // }
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
  getAppTitle: function() {
    let title = config.service.xcxtitle;
    return title;
  },
  getUserInfo: function(callback) {
    let that = this;
    let apiUrl = that.getApiUrl();
	console.log(this.globalData.userInfo)
    if (this.globalData.userInfo && this.globalData.userInfo.userId) {
      typeof callback == "function" && callback(this.globalData.userInfo)
    } else {
      //调用用户信息接口
      my.getAuthCode({
        scopes: "auth_user",
        //用户同意授权
        success: function(res) {
          my.getAuthUserInfo({
            //成功获取到用户信息
            success: function(userInfo) {
              if (res.authCode) {
                // 这里的作用是拿到用户在齐装网的userId，进而调出该用户的收藏
                my.httpRequest({
                  url: apiUrl + '/login/alipay',
                  data: {
                    appid: that.getAPPid(),
                    code: res.authCode,
                    name: userInfo.nickName,
                    logo: userInfo.avatar
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  method: "POST",
                  dataType: 'json',
                  //登录成功
                  success: function(res) {
                    if (res.data.data) {
                      //请求成功并获取到userId
                      userInfo.userId = res.data.data;
                      that.globalData.userInfo = userInfo;

                      typeof callback == "function" && callback(that.globalData.userInfo);
                    } else {
                      //请求成功但未获取到userId
                      // my.alert({
                      //   content: "网络忙，请稍后重试！"
                      // });
                    }
                  },
                  //授权登录失败
                  fail: function() {
                    my.alert({
                      content: "网络忙，请稍后重试！"
                    });
                    userInfo.error = 'error';
                    userInfo.userId = "";
                    that.globalData.userInfo = userInfo;
                    typeof callback == "function" && callback(that.globalData.userInfo);
                  }
                });
              };

            },
            //获取不到用户信息
            fail: function() {
              that.globalData.userInfo = { error: 'error', nickName: '游客' };
            }
          });
        },
        //用户拒绝授权
        fail: function() {
			// my.alert({
            //             content: "禁止授权"
            //           });
          that.globalData.userInfo = { error: 'error', nickName: '游客' };
        }
      });
    }
  },
  getLoginAgain: function(callback) {
    let that = this;
    let apiUrl = that.getApiUrl();
    if (this.globalData.userInfo && this.globalData.userInfo.error != 'error') {
      typeof callback == "function" && callback(this.globalData.userInfo)
    } else {
      //调用用户信息接口
      my.getAuthCode({
        scopes: "auth_user",
        success: function(res) {
          my.getAuthUserInfo({
            success: function(userInfo) {
              if (res.authCode) {
                // 这里的作用是拿到用户在齐装网的userId，进而调出该用户的收藏
                my.httpRequest({
                  url: apiUrl + '/login/alipay',
                  data: {
                    appid: that.getAPPid(),
                    code: res.authCode,
                    name: userInfo.nickName,
                    logo: userInfo.avatar
                  },
                  header: {
                    'content-type': 'application/x-www-form-urlencoded'
                  },
                  method: "POST",
                  dataType: 'json',
                  success: function(res) {
                    if (res.data.data) {
                      userInfo.userId = res.data.data;
                      that.globalData.userInfo = userInfo;
                      typeof callback == "function" && callback(that.globalData.userInfo);
                    } else {
                      my.alert({
                        content: "网络忙，请稍后重试！"
                      });
                    }
                  },
                  fail: function() {
                    my.alert({
                      content: "网络忙，请稍后重试！"
                    });
                    userInfo.error = 'error';
                    that.globalData.userInfo = userInfo;
                    typeof callback == "function" && callback(that.globalData.userInfo);
                  }
                });
              };
            },
            fail: function() {
              that.globalData.userInfo = { error: 'error' };
            }
          });
        }
      });

    }
  },
  /**
   * 设置缓存方法
   * @params k 缓存key值
   * @params v 缓存值
   * @params t 缓存事件 单位毫秒，若为负值代表清除时间缓存
  */
  setNewStorage: function(k, v, t) {
    // 设置缓存数据
    my.setStorageSync({
      key: k,
      data: v
    });
    let s = t ? parseInt(t) : 604800;// 设置的缓存时间 s/秒，默认7天也就是604800
    if (s > 0) {
      let timestamp = Date.parse(new Date());// 系统当前时间
      timestamp = (timestamp / 1000) + s; //缓存到期时间点
      // 存储缓存时间
      my.setStorageSync({
        key: k + 'Time',
        data: timestamp + '',
      });
    } else {
      my.removeStorageSync({
        key: k + 'Time'
      });
    }
  },







  getMyPosition(fn) {
    let that = this
    my.getLocation({
      success: function(res) {
        if (res.city) {
          fn(res.city)
        } else {
          fn('苏州')
        }
      },
      fail: function(res) {
        fn('苏州')
      }
    })
  },
  // 设置缓存
  setStorage: function(obj) {
    my.setStorage({
      key: obj.key,
      data: {
        data: obj.data,
        startTime: new Date().getTime()
      }
    });
  },
  // 取出缓存
  getStorage: function(obj) {
    //  如果是iOS环境，不走缓存
    if (this.getSysInfo()) {
      obj.success({
        code: 0
      })
      return
    }
    console.log(obj.key)
    my.getStorage({
      key: obj.key,
      success: function(res) {
        if (res.data) {
          let startTime = res.data.startTime
          let nowTime = new Date().getTime()
          let time = CONFIG_INFO.CACH_TIME
          if (time > nowTime - startTime) {
            obj.success({
              code: 1,
              data: res.data
            })
          }
        } else {
          my.removeStorage({
            key: 'key',
            success: function(res) {
              obj.success({
                code: 0
              })
            }
          })
        }
      },
      fail: function() {
        obj.success({
          code: 0
        })
      }
    })
  },
  // 判断是否是iOS
  getSysInfo() {
    try {
      const result = my.getSystemInfoSync();
      return result.system.indexOf('iOS') === 0 && result.platform !== "devtools"
    } catch (e) {
      return false
    }
  },








  // 获取缓存方法
  getNewStorage: function(k) {
    let deadtime = "", ret = null;
    deadtime = my.getStorageSync({ key: k + 'Time' }).data;
    let nowTime = Date.parse(new Date()) / 1000;
    if (deadtime) {
      if (deadtime < nowTime) {// 当时间到期
        // console.log('过期了')
        console.log("过期了");
        return;
      } else {
        // console.log('未过期')
        ret = my.getStorageSync({ key: k });
        if (ret) {
          return ret.data;
        } else {
          return;
        }
      }
    } else {// 如果没有设置缓存时间
      ret = my.getStorageSync({ key: k });
      if (ret) {
        return ret.data;
      } else {
        return;
      }
    }
  },
  aliGetUserInfo: function(callback) {
    let that = this;
    let apiUrl = that.getApiUrl();

    if (this.globalData.userInfo && this.globalData.userInfo.userId) {
      typeof callback == "function" && callback(this.globalData.userInfo)
    } else {

      //调用用户信息接口
      my.getAuthCode({
        scopes: "auth_user",
        //用户同意授权
        success: function(res) {
          my.httpRequest({
            url: '', // 目标服务器 url,实现的功能是服务端拿到authcode去开放平台进行token验证
            data: {
              authcode: res.authcode
            },
            success: (res) => {
              //请求成功返回userId，nickName，avatar
              let userInfo = null;
              userInfo.userId = res.userId;
              userInfo.nickName = res.nickName;
              userInfo.avatar = res.avatar;
              that.globalData.userInfo = userInfo;
              typeof callback == "function" && callback(that.globalData.userInfo);
            },
            fail: (res) => {
              that.globalData.userInfo = { error: 'error', nickName: '游客' };
            }
          });
        },
        //用户拒绝授权
        fail: function() {
          that.globalData.userInfo = { error: 'error', nickName: '游客' };
        }
      });
    }
  },
  //把“A 安徽省 合肥 瑶海区”形式的城市转换成“安徽省,合肥,瑶海区”
  transformCity: function(s) {
    let s1 = "",
      arr = [];
    if (typeof s !== "string") {
      return;
    }
    arr = s.split(" ");
    arr.forEach(function(item, index) {
      if (index > 0) {
        s1 += (item + ",");
      }
    });
    return s1.slice(0, s1.length - 1);
  }
});
