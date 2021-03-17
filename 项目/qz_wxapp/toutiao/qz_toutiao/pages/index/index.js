//index.js
//获取应用实例
const app = getApp()
const apiUrl = app.getAppUrl();
Page({
  data: {
    imgUrls: "",
    indicatorDots: false,
    autoplay: true,
    interval: 3000,
    duration: 1000,
    getImgUrl: app.getImgUrl(),
    dataPage: 1,
    indexDataList: [],
    scrollHeight: 600,
    fd: "",
    setMyNewSq: false, 
    underLine: false,
    bannerUrl: ["../zhuangxiusj/zhuangxiusj", "../company/company", "../jsq/jsq"]
  },
  onLoad: function (options) {

    if (options.p) {
      if (options.p === 'sj') {
        tt.navigateTo({
          url: '../zhuangxiusj/zhuangxiusj?src=' + options.src
        })
      }
      if (options.p === 'bj') {
        tt.navigateTo({
          url: '../jsq/jsq?src=' + options.src
        })
      }
      if (options.t) {
        tt.navigateTo({
          url: '../tuisong/tuisong?src=' + options.src + '&p=' + options.p + '&t=' + options.t
        })
      }
    }
    let that = this;
    that.getBannerList();
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let cityUrl;
    //获取缓存城市数据
    tt.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city });
        app.getMyPosition(cityJsonNew, that);
      },
      // 获取缓存失败
      fail: function () {
        tt.request({
          url: 'https://mpapi.qizuang.com' + "/v1/city/allcity?level=2", //仅为示例，并非真实的接口地址
          header: {
            'content-type': 'application/json' // 默认值
          },
          success: function (res) {
          app.getMyPosition(res.data, that)
            let prevs = res.data.prev
            let citys = res.data.city
            let jsons = res.data.json
            tt.setStorage({
              key: 'cityJson',
              data: { prev: prevs, city: citys, json: jsons }
            })
          }
        })
      }
    });
  },
  onShow: function () {
    tt.getSetting({
      success: function (res) {
        if (res.authSetting["scope.userInfo"]) {
          tt.getUserInfo({
            success: function (res) {
              var userInfo = res;
              tt.login({
                success: function (res) {
                  var jsCode = res.code;
                  app.aldpush.pushuserinfo(userInfo, jsCode);
                }
              })
            }
          })
        }
      }
    })
  },
  getBannerList: function () {
    let _that = this;
    tt.request({
      url: apiUrl + "/appletcarousel/banner?count=7", //仅为示例，并非真实的接口地址
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var imgUrls = res.data.bannerList;
        _that.setData({
          imgUrls: imgUrls
        });
      }
    })
  },
  getUserInfo: function (e) {
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/index/index',
      success: function (res) { },
      fail: function (res) { }
    }
  },
  tocompany: function (e) {
    let url = e.currentTarget.dataset.url;
    tt.navigateTo({
      url: url
    })
  },
  tozxsj: function () {
    tt.navigateTo({
      url: '../sheji/sheji',
    })
  },
  tojsq: function () {
    tt.navigateTo({
      url: '../baojia/baojia',
    })
  },
  toknowledge: function () {
    tt.navigateTo({
      url: '../toknowledge/toknowledge',
    })
  },
  toxgt: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=&type=&index=0&text=风格',
    })
  },
  tobz: function () {
    tt.navigateTo({
      url: '../six_baozhang/index',
    })
  },
  toyj: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=20&type=fg&index=0&text=宜家',
    })
  },
  toxd: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=5&type=fg&index=0&text=现代',
    })
  },
  toxc: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=11&type=fg&index=0&text=乡村',
    })
  },
  toblk: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=33&type=fg&index=0&text=巴洛克',
    })
  },
  tojo: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=15&type=fg&index=0&text=简欧',
    })
  },
  tojy: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=12&type=fg&index=0&text=简约',
    })
  },
  tohxd: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=23&type=fg&index=0&text=后现代',
    })
  },
  tofs: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=32&type=fg&index=0&text=法式',
    })
  },
  tozs: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=4&type=fg&index=0&text=中式',
    })
  },
  todzh: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=6&type=fg&index=0&text=地中海',
    })
  },
  tobo: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=21&type=fg&index=0&text=北欧',
    })
  },
  todny: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=16&type=fg&index=0&text=东南亚',
    })
  },
  togd: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=24&type=fg&index=0&text=古典',
    })
  },
  tofg: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=31&type=fg&index=0&text=复古',
    })
  },
  toms: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=7&type=fg&index=0&text=美式',
    })
  },
  tors: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=10&type=fg&index=0&text=日式',
    })
  },
  tohs: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=18&type=fg&index=0&text=韩式',
    })
  },
  tohd: function () {
    tt.navigateTo({
      url: '../xiaoguo/xiaoguo?id=17&type=fg&index=0&text=混搭',
    })
  }
})
