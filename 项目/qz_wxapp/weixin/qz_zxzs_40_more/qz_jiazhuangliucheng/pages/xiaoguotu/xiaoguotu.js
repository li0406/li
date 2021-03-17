var app = getApp();
let apiUrl = app.getApiUrl();
Page({
    data: {
        data: [],//数据
        data2: [],
        house_space: 0,//风格
        house_style: 0,//空间
        house_section: 0,//户型
        house_color: 0,//颜色
        page: 1,//当前页码
        // count:null,
        tabTxt: ['风格', '空间', '户型', '颜色'],//tab文案
        tab: [true, true, true, true],
        scrollTop: 0,
        iidex: null,
        filterfengge: null,
        filterhuxing: null,
        filterjvbu: null,
        filteryanse: null,
        moren: false,
        chushi: 20,
        fgnumber: 20,
        kongnumber: 20,
        huxingnumber: 20,
        colornumber: 20,
        fgbc: null,
        kjbc: null,
        hxbc: null,
        ysbc: null,
        mrshujv: null,
        shoucpanduan: true,
        userInfo: null,
        classtype: null,
        userId: null


    },


    xiaoguotuxq: function (e) {
        let id = e.currentTarget.dataset.id,
            title = e.currentTarget.dataset.title;
        wx.navigateTo({
            url: '../xiaoguotuxiangqfd/xiaoguotuxiangqfd?id=' + id + '&title=' + title
        });
    },

    onReady: function () {


    },
    onLoad:function(){

    },

    onShow: function () {
        //初始化数据
        var that = this;
        that.getFilter();
        app.getUserInfo(function (res) {
            that.setData({ userId: res.userId });
        })
        wx.request({
            url: apiUrl + '/appletcarousel/meitu',
            data: {
                count: that.data.chushi,
                userid: that.data.userId
            },
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                console.log(res)
                that.setData({
                    data: res.data.info.list,
                    mrshujv: res.data.info.list
                })

            },
            fail: function () {
                // console.log('error!!!!!!!!!!!!!!')
            }
        })
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    // 选项卡
    filterTab: function (e) {
        var data = [true, true, true, true], index = e.currentTarget.dataset.index;
        data[index] = !this.data.tab[index];
        this.setData({
            tab: data,
        })
    },
    // 获取筛选项
    getFilter: function () {
        var that = this;
        wx.request({
            url: apiUrl + '/appletcarousel/meitu',
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                that.setData({
                    filterfengge: res.data.attribute.fengge,
                    filterhuxing: res.data.attribute.huxing,
                    filterjvbu: res.data.attribute.location,
                    filteryanse: res.data.attribute.color,
                });

            },
            fail: function () {
                // console.log('error!!!!!!!!!!!!!!')
            }
        })
    },


    // 筛选项点击操作
    filter: function (e) {
        var that = this, id = e.currentTarget.dataset.id, iidex = e.currentTarget.dataset.index, txt = e.currentTarget.dataset.txt, tabTxt = this.data.tabTxt;
        that.setData({
            fgnumber: 20,
            kongnumber: 20,
            huxingnumber: 20,
            colornumber: 20,
        })
        switch (iidex) {
            case '0':
                tabTxt[0] = txt;
                that.setData({
                    house_space: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true, true],
                    tabTxt: tabTxt,
                    iidex: 0,
                    moren: true,
                    fgbc: id

                });
                wx.request({
                    url: apiUrl + '/appletcarousel/meitu',
                    data: {
                        fengge: that.data.fgbc,
                        huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.fgnumber
                    },
                    header: {
                        'Content-Type': 'application/json'
                    },
                    success: function (res) {
                        if (res.data.info.list.length < 1) {

                            that.setData({
                                data: that.data.mrshujv,
                                scrollTop: 0
                            })
                        } else {
                            that.setData({
                                data: res.data.info.list,
                                scrollTop: 0
                            })
                        }

                    },
                    fail: function () {
                        // console.log('error!!!!!!!!!!!!!!')
                    }
                })

                break;
            case '1':
                tabTxt[1] = txt;
                that.setData({
                    house_style: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true, true],
                    tabTxt: tabTxt,
                    iidex: 1,
                    moren: true,
                    kjbc: id,
                });

                wx.request({
                    url: apiUrl + '/appletcarousel/meitu',
                    data: {
                        fengge: that.data.fgbc,
                        huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.kongnumber
                    },
                    header: {
                        'Content-Type': 'application/json'
                    },
                    success: function (res) {
                        if (res.data.info.list < 1) {
                            that.setData({
                                data: that.data.mrshujv,
                                scrollTop: 0
                            })
                        } else {
                            that.setData({
                                data: res.data.info.list,
                                scrollTop: 0
                            })
                        }

                    },
                    fail: function () {
                        // console.log('error!!!!!!!!!!!!!!')
                    }
                })

                break;
            case '2':
                tabTxt[2] = txt;
                that.setData({
                    house_section: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true, true],
                    tabTxt: tabTxt,
                    iidex: 2,
                    moren: true,
                    hxbc: id
                });
                wx.request({
                    url: apiUrl + '/appletcarousel/meitu',
                    data: {
                        fengge: that.data.fgbc,
                        huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.huxingnumber
                    },
                    header: {
                        'Content-Type': 'application/json'
                    },
                    success: function (res) {
                        if (res.data.info.list < 1) {
                            that.setData({
                                data: that.data.mrshujv,
                                scrollTop: 0
                            })
                        } else {
                            that.setData({
                                data: res.data.info.list,
                                scrollTop: 0
                            })
                        }

                    },
                    fail: function () {
                        // console.log('error!!!!!!!!!!!!!!')
                    }
                })
                break;
            case '3':
                tabTxt[3] = txt;
                that.setData({
                    house_color: id,
                    page: 1,
                    data: [],
                    tab: [true, true, true, true],
                    tabTxt: tabTxt,
                    iidex: 3,
                    moren: true,
                    ysbc: id
                });

                wx.request({
                    url: apiUrl + '/appletcarousel/meitu',
                    data: {
                        fengge: that.data.fgbc,
                        huxing: that.data.kjbc,
                        location: that.data.hxbc,
                        color: that.data.ysbc,
                        count: that.data.colornumber
                    },
                    header: {
                        'Content-Type': 'application/json'
                    },
                    success: function (res) {
                        if (res.data.info.list < 1) {
                            that.setData({
                                data: that.data.mrshujv,
                                scrollTop: 0
                            })
                        } else {
                            that.setData({
                                data: res.data.info.list,
                                scrollTop: 0
                            })
                        }

                    },
                    fail: function () {
                        // console.log('error!!!!!!!!!!!!!!')
                    }
                })

                break;
        }

    },


    // //下拉加载
    downLoad: function () {
        wx.showToast({
            title: '加载中...',
            icon: 'loading',
            duration: 2000
        });
        var that = this;
        if (that.data.moren == false) {
            let kka = that.data.chushi + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitu',
                data: {
                    userid:that.data.userId,
                    count: kka
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    that.setData({
                        data: res.data.info.list,
                        chushi: kka
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }
            })
        }
        if (that.data.iidex == 0) {
            let kkb = that.data.fgnumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitu',
                data: {
                    userid:that.data.userId,
                    fengge: that.data.fgbc,
                    huxing: that.data.kjbc,
                    location: that.data.hxbc,
                    color: that.data.ysbc,
                    count: kkb,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {

                    that.setData({
                        data: res.data.info.list,
                        fgnumber: kkb
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        } else if (that.data.iidex == 1) {
            let kkc = that.data.kongnumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitu',

                data: {
                    userid: that.data.userId,
                    fengge: that.data.fgbc,
                    huxing: that.data.kjbc,
                    location: that.data.hxbc,
                    color: that.data.ysbc,
                    count: kkc,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    that.setData({
                        data: res.data.info.list,
                        kongnumber: kkc
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        } else if (that.data.iidex == 2) {
            let kkd = that.data.huxingnumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitu',

                data: {
                    userid: that.data.userId,
                    fengge: that.data.fgbc,
                    huxing: that.data.kjbc,
                    location: that.data.hxbc,
                    color: that.data.ysbc,
                    count: kkd,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    that.setData({
                        data: res.data.info.list,
                        huxingnumber: kkd
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        } else if (that.data.iidex == 3) {
            let kke = that.data.colornumber + 10
            wx.request({
                url: apiUrl + '/appletcarousel/meitu',

                data: {
                    userid: that.data.userId,
                    fengge: that.data.fgbc,
                    huxing: that.data.kjbc,
                    location: that.data.hxbc,
                    color: that.data.ysbc,
                    count: kke,
                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    that.setData({
                        data: res.data.info.list,
                        colornumber: kke
                    })

                },
                fail: function () {
                    // console.log('error!!!!!!!!!!!!!!')
                }


            })
        }

    },

    scqiehuan: function (e) {
        let that = this,
            id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index;
        if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '8', // 装修攻略
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
                        let arr = that.data.data;
                        arr[index].is_collect = 1;
                        that.setData({ data: arr});
                    }
                }
            });
        }else{
            app.getLoginAgain(function (e) {
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
                                            classtype: '8', // 装修攻略
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
                                                let arr = that.data.data;
                                                arr[index].is_collect = 1;
                                                that.setData({ data: arr })
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                })
            });
        }
    },
    toBaojia: function () {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    }

})