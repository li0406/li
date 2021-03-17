//index.js
//获取应用实例
const app = getApp()
const apiUrl = app.getApiUrl();
var config = require('../../config');
const imgUrl = app.getImgUrl();

Page({
    data: {
        imgUrls:"",
        indicatorDots: false,
        autoplay: true,
        interval: 3000,
        duration: 1000,
        getImgUrl: app.getImgUrl(),
        dataPage:1,
        indexDataList:[],
        scrollHeight:600,
        fd:"",
        setMyNewSq:false,
        underLine:false,
        bannerUrl: ["../zhuangxiusj/zhuangxiusj", "../company/company", "../jsq/jsq"],
        userId: '',
        limit:1
    },
    onLoad: function (options) {
        let that = this;
        if(options.p){
            if(options.p==='sj') {
                wx.navigateTo({
                url: '../zhuangxiusj/zhuangxiusj?src='+options.src
                })
            }
            if(options.p==='bj') {
                wx.navigateTo({
                url: '../jsq/jsq?src=' + options.src
                })
            }
            if (options.t) {
                wx.navigateTo({
                url: '../tuisong/tuisong?src=' + options.src + '&p=' + options.p + '&t=' + options.t
                })
            }
        }
        that.getBannerList();
        app.getLocationCity();
        // app.getUser();
    },
    onShow:function () {
        wx.getSetting({
            success: function (res) {
                if (res.authSetting["scope.userInfo"]) {
                    wx.getUserInfo({
                        success: function (res) {
                            var userInfo = res;
                            wx.login({
                                success: function (res) {
                                var jsCode = res.code;
                                //app.aldpush.pushuserinfo(userInfo, jsCode);
                                }
                            })
                        }
                    })
                }
            } 
        })
    },
    tap: function (eventId) {
        let that = this
        if (that.data.limit === 1) {
            wx.aldPushSubscribeMessage({
                eventId: eventId,
                success(res) {
                    // 成功后的回调函数
                    that.setData({
                        limit: 2
                    })
                    console.log(that.data.limit)
                    console.log(res)
                },
                fail(res, e) {
                    // 失败后的回调函数
                    console.log(res)
                    console.log(e)
                }
            })
        }   
    },
    getBannerList:function(){
        let _that=this;
        wx.request({
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
   
    getUserInfo: function(e) {
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
    tocompany:function(e){
        this.tap('5ec4d96d7739104342928ebc')
        let url = e.currentTarget.dataset.url;
        if (url == '../zhuge/zhuge'){
        wx.navigateToMiniProgram({
            appId: 'wxb748bbed6783eafc',
            path: 'pages/old/index/index?spread=mp_g_qzwesf_kl&ald_media_id=4084&ald_link_key=8b34a834220f378a',
            extraData: {},
            envVersion: 'release',
            success(res) {
            console.log(res)
            }
        })
        }else{
            wx.navigateTo({
                url: url
            })
            wx.switchTab({
                url: url
            }) 
        }
    },
    tozxsj:function () {
        this.tap('5ec4d9436df4251c4a09a4dd')
        wx.navigateTo({
        url: '../zhuangxiusj/zhuangxiusj',
        })
    },
    tojsq: function () {
        this.tap('5ec4d8607739104342928eba')
        wx.navigateTo({
        url: '../jsq/jsq',
        })
    },
    toknowledge: function () {
        this.tap('5ec4d90e7739104342928ebb')
        wx.navigateTo({
        url: '../dec-knowledge/knowledge',
        })
    },
    toxgt: function () {
        wx.navigateTo({
        url: '../decoration-style/index',
        })
    },
    tobz: function () {
        wx.navigateTo({
        url: '../six_baozhang/index',
        })
    },
    toyj: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=20',
        })
    },
    toxd: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=5',
        })
    },
    toxc: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=11',
        })
    },
    toblk: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=33',
        })
    },
    tojo: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=15',
        })
    },
    tojy: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=12',
        })
    },
    tohxd: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=23',
        })
    },
    tofs: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=32',
        })
    },
    tozs: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=4',
        })
    },
    todzh: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=6',
        })
    },
    tobo: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=21',
        })
    },
    todny: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=16',
        })
    },
    togd: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=24',
        })
    },
    tofg: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=31',
        })
    },
    toms: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=7',
        })
    },
    tors: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=10',
        })
    },
    tohs: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=18',
        })
    },
    tohd: function () {
        wx.navigateTo({
        url: '../decoration-style/index?id=17',
        })
    }
})
