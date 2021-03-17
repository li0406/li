// pages/det_company/det_company.js
const app = getApp(),
      apiUrl = app.getApiUrl(),
      apid = app.getAPPid(),
      oImgUrl = app.getImgUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: oImgUrl,
        details:{},
        caseList:[],
        team:[],
        count:10,
        anlicount:'',
        id:'',
        loadBool:true,
        arrHide:[false,true,true]
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        app.getUserInfo(function(res){
            that.setData({ userId: res.userId})
        });
        wx.request({
            url: apiUrl +'/appletcarousel/companyDetails?userid='+that.data.userId,
            data: { id:options.id,count:10,classtype:9},
            header: {
                'content-type': 'application/json'
            },
            success:function(res){
                res.data.details.star = options.star;
                that.setData({ details: res.data.details, caseList: res.data.cases, id: options.id, anlicount: options.anlicount, team:res.data.team});
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
    /**
     * 滚动加载更多
     */
    downLoad(){
        let that = this,
            count = that.data.count;
        if (that.data.loadBool && that.data.arrHide[1]==false){
            wx.showToast({
                title: '数据加载中...',
                icon: 'loading'
            });
            let len = that.data.caseList.length;
            count += 10;
            wx.request({
                url: apiUrl + '/appletcarousel/companyDetails',
                data: { id: that.data.id, count: count },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    
                    if (len == res.data.cases.length){
                        that.setData({loadBool:false})
                    }else{
                        that.setData({ caseList: res.data.cases, count: count, loadBool: true })
                    }
                }
            });
        }else{
            if (that.data.arrHide[1]==false){
                wx.showToast({
                    title: '没有更多了',
                    icon: 'success'
                });
            }
        }
    },
    /**
     * tab切换
     */
    navTab(e){
        let index = e.currentTarget.dataset.index,
            that = this,
            arr = [true,true,true];
        arr[index] = false;
        that.setData({arrHide:arr});
    },
    /**
     * 到设计页
     */
    toSheji(){
        wx.navigateTo({
            url: '../zhuangxiusj/zhuangxiusj',
        })
    },
    /**
     * 到报价页
     */
    toBaojia() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj',
        })
    },
    /**
     * 到单案列
     */
    toAnli(e){
        let id = e.currentTarget.dataset.id;
        wx.navigateTo({
            url: '../anli/anli?id='+id,
        })
    },
    /**
     * 到设计师详情
     */
    toDesgin(e){
        let id = e.currentTarget.dataset.id;
        wx.navigateTo({
            url: '../anli_designer/anli_designer?id=' + id,
        });
    },
    toMark: function (e) {
        let companyId = e.currentTarget.dataset.id,
            that = this,
            details = that.data.details;
        if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: companyId
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
                        details.is_collect = 1;
                        that.setData({ details: details })
                    }
                }
            });
        } else {
            app.getLoginAgain(function (e) {
                wx.login({
                    success: function (l) {
                        if (l.code) {
                            wx.request({
                                url: apiUrl + '/login',
                                data: {
                                  appid: apid,
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
                                  console.log(i)
                                  that.setData({userId:i.data.data})
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/editcollect',
                                        data: {
                                            userid: i.data.data,
                                            classtype: '9', // 装修攻略
                                            classid: companyId
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
                                                details.is_collect = 1;
                                                that.setData({ details: details })
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
    },
    delMark: function (e) {
        let companyId = e.currentTarget.dataset.id,
            that = this,
            details = this.data.details;
        if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: companyId
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if (res.data.state == 1) {
                        wx.showToast({
                            title: '你已取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        details.is_collect = 0;
                        that.setData({ details: details })
                    }
                }
            });
        }
    }
})