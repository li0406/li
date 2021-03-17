// pages/user_mark/user_mark.js
let app = getApp();
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        isHide: [true, false, false],
        isEmpty:[true,true,true],
        xgtArr:[],
        zxgsArr:[],
        zxgl:[],
        oImgUrl: oImgUrl,
        loginUserId: ''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let userId = '',
            that = this;
        app.getUserInfo(function (res) {
            if (res.userId || that.data.loginUserId) {
                // 获取装修效果图
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: res.userId,
                        classtype: '8'//效果图
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        let empty = that.data.isEmpty;
                        console.log(res)

                        if (res.data.length > 0) {
                            empty[0] = true;
                            that.setData({ xgtArr: res.data, isEmpty: empty })
                        } else {
                            empty[0] = false;
                            that.setData({ xgtArr: res.data, isEmpty: empty })
                        }
                    }
                });
                // 获取装修公司
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: res.userId,
                        classtype: '9'//装修公司
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        let empty = that.data.isEmpty;
                        if (res.data.length > 0) {
                            empty[1] = true;
                            that.setData({ zxgsArr: res.data, isEmpty: empty })
                        } else {
                            empty[1] = false;
                            that.setData({ zxgsArr: res.data, isEmpty: empty })
                        }
                    }
                });
                // 获取装修攻略
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: res.userId,
                        classtype: '10'//装修攻略
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        let empty = that.data.isEmpty;
                        if (res.data.length > 0) {
                            empty[2] = true;
                            that.setData({ zxglArr: res.data, isEmpty: empty })
                        } else {
                            empty[2] = false;
                            that.setData({ zxglArr: res.data, isEmpty: empty })
                        }

                    }
                });
            } else {
                app.getLoginAgain(function (res) {
                    // 获取装修效果图
                    wx.request({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            count: '10',
                            userid: res.userId,
                            classtype: '8'//效果图
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;

                            if (res.data.length > 0) {
                                empty[0] = true;
                                that.setData({ xgtArr: res.data, isEmpty: empty })
                            } else {
                                empty[0] = false;
                                that.setData({ xgtArr: res.data, isEmpty: empty })
                            }
                        }
                    });
                    // 获取装修公司
                    wx.request({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            count: '10',
                            userid: res.userId,
                            classtype: '9'//装修公司
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;
                            if (res.data.length > 0) {
                                empty[1] = true;
                                that.setData({ zxgsArr: res.data, isEmpty: empty })
                            } else {
                                empty[1] = false;
                                that.setData({ zxgsArr: res.data, isEmpty: empty })
                            }
                        }
                    });
                    // 获取装修攻略
                    wx.request({
                        url: apiUrl + '/appletcarousel/collect',
                        data: {
                            count: '10',
                            userid: res.userId,
                            classtype: '10'//装修攻略
                        },
                        header: {
                            'content-type': 'application/json'
                        },
                        success: function (res) {
                            let empty = that.data.isEmpty;
                            if (res.data.length > 0) {
                                empty[2] = true;
                                that.setData({ zxglArr: res.data, isEmpty: empty })
                            } else {
                                empty[2] = false;
                                that.setData({ zxglArr: res.data, isEmpty: empty })
                            }

                        }
                    });
                })
                that.setData({ isEmpty: [false, false, false] })
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
    onShareAppMessage: function (res) {
        if (res.from === 'button') {
            // 来自页面内转发按钮
            if(res.target.dataset.style=='xgt'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/xiaoguotuxiangqfd/xiaoguotuxiangqfd?id=' + res.target.dataset.id + '&title=' + res.target.dataset.title,
                    imageUrl: oImgUrl + res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            } else if (res.target.dataset.style == 'zxgs'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/det_company/det_company?id=' + res.target.dataset.id,
                    imageUrl: res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            } else if (res.target.dataset.style == 'zxgl'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/shouyexq/shouyexq?id=' + res.target.dataset.id,
                    imageUrl: oImgUrl + res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            }
        }
        
    },
    /**
     * 我的收藏tab切换
     */
    showModal: function (res) {
        let index = (res.currentTarget.dataset.index);
        let hideArr = [false,false,false];
        hideArr[index] = true;
        this.setData({ isHide: hideArr});
    },
    /**
     * 点击跳转到效果图
     */
    toXGT:function(){
        wx.switchTab({
            url: '../xiaoguotu/xiaoguotu'
        })
    },
    /**
     * 点击跳转到装修公司
     */
    toZXGS: function () {
        wx.navigateTo({
            url: '../des_company/des_company'
        })
    },
    /**
     * 点击跳转到装修攻略
     */
    toZXGL: function () {
        wx.navigateTo({
            url: '../user_mark/user_mark'
        })
    },
    toDel:function(e){
        let id = e.currentTarget.dataset.id,
            eStyle = e.currentTarget.dataset.style,
            userId = e.currentTarget.dataset.userid,
            that = this;
        if (eStyle === 'xgt'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '8', // 装修效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state==1){
                        // 获取装修效果图
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                count: '10',
                                userid: userId,
                                classtype: '8'//效果图
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;

                                if (res.data.length > 0) {
                                    empty[0] = true;
                                    that.setData({ xgtArr: res.data, isEmpty: empty })
                                } else {
                                    empty[0] = false;
                                    that.setData({ xgtArr: res.data, isEmpty: empty })
                                }
                            }
                        });
                    }
                }
            });
        }else if(eStyle === 'zxgs'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state == 1){
                        // 获取装修公司
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                userid: userId,
                                classtype: '9'//装修公司
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;
                                if (res.data.length > 0) {
                                    empty[1] = true;
                                    that.setData({ zxgsArr: res.data, isEmpty: empty })
                                } else {
                                    empty[1] = false;
                                    that.setData({ zxgsArr: res.data, isEmpty: empty })
                                }
                            }
                        });
                    }

                }
            });
        }else if(eStyle === 'zxgl'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '10', // 装修攻略
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state == 1){
                        // 获取装修攻略
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                userid: userId,
                                classtype: '10'//装修攻略
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;
                                if (res.data.length > 0) {
                                    empty[2] = true;
                                    that.setData({ zxglArr: res.data, isEmpty: empty })
                                } else {
                                    empty[2] = false;
                                    that.setData({ zxglArr: res.data, isEmpty: empty })
                                }
                            }
                        }); 
                    }

                }
            });
        }
        
    }
})