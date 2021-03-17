// pages/zxgonglue_list/zxgonglue_list.js
const app = getApp()
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        oImgUrl: oImgUrl,
        articleList:[],
        urlstr:'',
        articleCount:'10',
        userId:''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this,
            userId = '';
        wx.setNavigationBarTitle({
            title: options.urlstrname
        });
        app.getUserInfo(function (res) { that.setData({ userId: res.userId }); })
        that.setData({ urlstr: options.urlstr})
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
        let that = this;
        app.getUserInfo(function (res) {
            that.setData({ userId: res.userId });
            wx.request({
                url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + res.userId,
                data: { count: '10' },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    console.log(res)
                    that.setData({ articleList: res.data })
                }
            });
        })
        
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
    /**
     * 上拉加载数据
     */
    lower:function(){
        let that = this,
            count = parseInt(that.data.articleCount);
            count+=10;
        wx.request({
            url: apiUrl + '/gonglue/' + that.data.urlstr+'&userid='+that.data.userId,
            data: { count: count },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                wx.showLoading({
                    title: '数据加载中',
                });
                setTimeout(function () {
                    wx.hideLoading()
                }, 1200);
                that.setData({ articleList: res.data, articleCount: count});
            }
        });
    },
    /**
     * 跳转到详情页面
     */
    toArticle:function(e){
        let id = e.currentTarget.dataset.id;
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id='+id
        })
    },
    toMark:function(e){
        let id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index,
            that = this,
            userId = '';
        console.log(e)
        if (that.data.userId){
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
                success: function (res) {
                    if (res.data.state == 1) {
                        wx.showToast({
                            title: '收藏成功',
                            icon: 'success',
                            duration: 1200
                        });
                        let artList = that.data.articleList
                        artList[index].is_collect=1
                        that.setData({ articleList: artList})
                        // wx.request({
                        //     url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + that.data.userId,
                        //     data: { count: 10 },
                        //     header: {
                        //         'content-type': 'application/json'
                        //     },
                        //     success: function (res) {
                        //         that.setData({ articleList: res.data })
                        //     }
                        // });
                    }
                }
            });
        }else{
            app.getLoginAgain(function (res) {
                let nickName = res.nickName,
                    avatarUrl = res.avatarUrl;
                wx.login({
                    success: function (l) {
                        if (l.code) {
                            wx.request({
                                url: apiUrl + '/login',
                                data: {
                                    appid: 'wxbf01eb5781c89e13',
                                    code: l.code,
                                    name: nickName,
                                    logo: avatarUrl
                                },
                                header: {
                                    'content-type': 'application/x-www-form-urlencoded'
                                },
                                method: "POST",
                                dataType: 'json',
                                success: function (u) {
                                    that.setData({userId:u.data.data})
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/editcollect',
                                        data: {
                                            userid: u.data.data,
                                            classtype: '10', // 装修攻略
                                            classid: id
                                        },
                                        header: {
                                            'content-type': 'application/x-www-form-urlencoded'
                                        },
                                        method: "POST",
                                        success: function (res) {
                                            if (res.data.state == 1) {
                                                wx.showToast({
                                                    title: '收藏成功',
                                                    icon: 'success',
                                                    duration: 1200
                                                });
                                                let artList = that.data.articleList
                                                artList[index].is_collect = 1
                                                that.setData({ articleList: artList })
                                                // wx.request({
                                                //     url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + userId,
                                                //     data: { count: 10 },
                                                //     header: {
                                                //         'content-type': 'application/json'
                                                //     },
                                                //     success: function (res) {
                                                //         that.setData({ articleList: res.data })
                                                //     }
                                                // });
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            });
        }

    }
})