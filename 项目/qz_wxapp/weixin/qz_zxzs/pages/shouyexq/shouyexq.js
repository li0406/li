// pages/shouyexq/shouyexq.js
const app = getApp(),
      apiUrl = app.getApiUrl(),
      oImgUrl = app.getImgUrl();
var WxParse = require('../../wxParse/wxParse.js');
function getLocalTime(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: oImgUrl,
        userInfo: {},
        hasUserInfo: false,
        canIUse: wx.canIUse('button.open-type.getUserInfo'),
        dianji: false,
        details:{},
        articleList:[],
        dianzansl: 766,
        userId:'',
        mark:true,
        zan:true
    },

    zxsjym1: function () {
        wx.navigateTo({
            url: '../zhuangxiusj/zhuangxiusj'
        })
    },

    dianjizan: function () {
        let that = this;
        let user = app.getNewStorage('user');
        let details = that.data.details;
        let bool = true;
        if (user){
            for (let i = 0; i < user.length; i++) {
                if (user[i] == details.id){
                    bool = false;

                    that.setData({ zan:false})
                    break;
                }else{
                    bool = true;
                }
            }
            if (bool) {
                wx.request({
                    url: apiUrl+'/appletcarousel/like',
                    data:{
                        id: details.id
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success:function(res){
                        if(res.data.state === 1){
                            details.likes = parseInt(details.likes) + 1;
                            that.setData({ details: details });
                            user.push(details.id)
                            app.setNewStorage('user', user);
                        }
                    }
                });
            } else {
                wx.showModal({
                    title: '您已经点赞了',
                    showCancel: false,
                    success: function (res) {

                    }
                });
            }
        }else{
            let user = [];
            wx.request({
                url: apiUrl + '/appletcarousel/like',
                data: {
                    id: details.id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if (res.data.state === 1) {
                        details.likes = parseInt(details.likes) + 1;
                        that.setData({ details: details });
                        user.push(details.id)
                        app.setNewStorage('user', user);
                    }
                }
            });
        }

    },
    /**
     * 生命周期函数--监听页面加载
     */

    onLoad: function (options) {
        let _this = this;
        app.getUserInfo(function(res){
            _this.setData({userId:res.userId})
        });
        wx.request({
            url: apiUrl+'/appletcarousel/details',
            data: {
                userid: _this.data.userId,
                id: options.id,
                classtype:'10'
            },
            header: {
                'content-type': 'application/json'
            },
            success:function(res){
                console.log(res)
                res.data.article.addtime = getLocalTime(res.data.article.addtime);
                let content = res.data.article.content,
                    a1 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
                    a3 = '<a href="http://www.qizuang.com/zhaobiao/" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
                    a4 = '<a href="http://www.qizuang.com/zxbj/?src=gl-bj" rel= "nofollow" target= "_blank" >&gt;&gt; 点此获取专业设计师免费量房设计 < /a>',
                    a5 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank">&gt;&gt; 点此获取专业设计师免费量房设计</a>',
                    a2 = '<a href="http://www.qizuang.com/zhaobiao/" rel="nofollow" target="_blank" style="text-decoration: underline; font-size: 14px; color: rgb(192, 0, 0);"><span style="font-size: 14px; color: rgb(192, 0, 0);">&gt;&gt; 点此获取专业设计师免费量房设计</span></a>',
                    a6 = '<ahref= rel="nofollow" target="_blank" style="text-decoration:underline;font-size:14px;color:rgb(192,0,0);"><spanstyle="font-size:14px;color:rgb(192,0,0);">&gt;&gt;点此获取专业设计师免费量房设计</spanstyle="font-size:14px;color:rgb(192,0,0);"></ahref=>';
                if ( content.indexOf(a1) > 0){
                    content = content.split(a1)[0];
                } else if(content.indexOf(a2) > 0){
                    content = content.split(a2)[0];
                } else if (content.indexOf(a3) > 0) {
                    content = content.split(a3)[0];
                } else if (content.indexOf(a4) > 0) {
                    content = content.split(a4)[0];
                } else if (content.indexOf(a5) > 0) {
                    content = content.split(a5)[0];
                } else if (content.indexOf(a6) > 0) {
                    content = content.split(a6)[0];
                }
                
                WxParse.wxParse('article', 'html', content, _this);
                _this.setData({ details: res.data.article});

                wx.setNavigationBarTitle({
                    title: res.data.article.title
                })
            }
        });
        wx.request({
            url: apiUrl + '/appletcarousel/detailsRecommend?id=' + options.id,
            data: { order: 'realview',count:'9' },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                _this.setData({ articleList: res.data});
            }
        });
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    articleDet: function (e) {
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id=' + id
        })
    },
    toMatk:function(e){
        let id = e.currentTarget.dataset.id,
            that = this;
            if(that.data.userId){
                wx.request({
                    url: apiUrl + '/appletcarousel/editcollect',
                    data: {
                        userid: that.data.userId,
                        classtype: '10', // 装修攻略
                        classid: id
                    },
                    header: {
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    method: "POST",
                    success: function () {
                        let detailsNow = that.data.details;
                        detailsNow.is_collect = 1;
                        that.setData({ details: detailsNow });
                    }
                });
            }else{
                app.getLoginAgain(function(e){
                    wx.login({
                        success: function (l) {
                            if (l.code) {
                                wx.request({
                                    url: apiUrl + '/login',
                                    data: {
                                        appid: 'wxbf01eb5781c89e13',
                                        code: l.code,
                                        name: e.nickName,
                                        logo: e.avatarUrl
                                    },
                                    header: {
                                        'content-type': 'application/x-www-form-urlencoded'
                                    },
                                    method: "POST",
                                    dataType: 'json',
                                    success: function (i) {
                                        wx.request({
                                            url: apiUrl + '/appletcarousel/editcollect',
                                            data: {
                                                userid: i.data.data,
                                                classtype: '10', // 装修攻略
                                                classid: id
                                            },
                                            header: {
                                                'content-type': 'application/x-www-form-urlencoded'
                                            },
                                            method: "POST",
                                            success: function () {
                                                let detailsNow = that.data.details;
                                                detailsNow.is_collect = 1;
                                                that.setData({ details: detailsNow });
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    })
                })
            }
        
    },
    toBaojia:function(){
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj',
        })
    }
})