var app = getApp();
let apiUrl = app.getApiUrl(),
    apid = app.getAPPid();
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
    xiaoguotuxq (e) {
        let id = e.currentTarget.dataset.id,
            title = e.currentTarget.dataset.title,
            is_collect = e.currentTarget.dataset.is_collect;
        wx.navigateTo({
            url: '../xiaoguotuxiangqfd/xiaoguotuxiangqfd?id=' + id + '&title=' + title + '&is_collect=' + is_collect
        });
    },
    onReady () {

    },
    onLoad(){
        
    },
    onShow () {
        //初始化数据
        var that = this;
        that.getFilter();
        let userId = app.getNewStorage('userId');
        // 先获取没有登录的数据
        wx.request({
            url: apiUrl + '/appletcarousel/meitu',
            data: {
                count: that.data.chushi,
                userid: userId
            },
            header: {
                'Content-Type': 'application/json'
            },
            success(res) {
                that.setData({
                    data: res.data.info.list,
                    mrshujv: res.data.info.list
                });
            }
        });
        /**
         * 判断是否允许获取个人信息 yes（重新获取）no（不在获取）
         */
        app.getUserInfo((res) => {
            let info = res;
            wx.login({
                success(l) {
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
                            success(i) {
                                if(i.data.data){
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/meitu',
                                        data: {
                                            count: that.data.chushi,
                                            userid: i.data.data
                                        },
                                        header: {
                                            'Content-Type': 'application/json'
                                        },
                                        success(res) {
                                            that.setData({
                                                data: res.data.info.list,
                                                mrshujv: res.data.info.list
                                            });
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            });
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage () {

    },
    // 选项卡
    filterTab (e) {
        var data = [true, true, true, true], index = e.currentTarget.dataset.index;
        data[index] = !this.data.tab[index];
        this.setData({
            tab: data,
        })
    },
    // 获取筛选项
    getFilter () {
        var that = this;
        wx.request({
            url: apiUrl + '/appletcarousel/meitu',
            header: {
                'Content-Type': 'application/json'
            },
            success (res) {
                that.setData({
                    filterfengge: res.data.attribute.fengge,
                    filterhuxing: res.data.attribute.huxing,
                    filterjvbu: res.data.attribute.location,
                    filteryanse: res.data.attribute.color,
                });

            }
        });
    },
    // 筛选项点击操作
    filter (e) {
        var that = this,
            id = e.currentTarget.dataset.id,
            iidex = e.currentTarget.dataset.index,
            txt = e.currentTarget.dataset.txt,
            tabTxt = this.data.tabTxt;
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

                    }
                });

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
                    success (res) {
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
                    success (res) {
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

                    }
                });
                break;
        }

    },
    /**
     * 上拉加载更多
     */
    downLoad () {
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
                success (res) {
                    that.setData({
                        data: res.data.info.list,
                        chushi: kka
                    });
                }
            });
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
                success (res) {

                    that.setData({
                        data: res.data.info.list,
                        fgnumber: kkb
                    })

                }
            });
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
                success (res) {
                    that.setData({
                        data: res.data.info.list,
                        kongnumber: kkc
                    })

                }
            });
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
                success (res) {
                    that.setData({
                        data: res.data.info.list,
                        huxingnumber: kkd
                    })

                }
            });
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
                success (res) {
                    that.setData({
                        data: res.data.info.list,
                        colornumber: kke
                    });
                }
            });
        }
    },
    // 添加收藏
    scqiehuan (e) {
        let that = this,
            id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index,
            userId = app.getNewStorage('userId');
        if (userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId,
                    classtype: '8', // 装修效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success (res) {
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
            app.getLoginAgain((e) =>{
                wx.login({
                    success (l) {
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
                                success (i) {
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/editcollect',
                                        data: {
                                            userid: i.data.data,
                                            classtype: '8', // 装修效果图
                                            classid: id
                                        },
                                        header: {
                                            'content-type': 'application/x-www-form-urlencoded'
                                        },
                                        method: "POST",
                                        success (res) {
                                            if (res.data.state == 1) {
                                                wx.showToast({
                                                    title: '收藏成功',
                                                    icon: 'success',
                                                    duration: 1200
                                                });
                                                wx.request({
                                                    url: apiUrl + '/appletcarousel/meitu',
                                                    data: {
                                                        count: that.data.chushi,
                                                        userid: i.data.data
                                                    },
                                                    header: {
                                                        'Content-Type': 'application/json'
                                                    },
                                                    success(res) {
                                                        that.setData({
                                                            data: res.data.info.list,
                                                            mrshujv: res.data.info.list
                                                        });
                                                    }
                                                });
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
    /**
     * 取消收藏
     */
    delMark(e){
        let that = this,
            id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index,
            userId = app.getNewStorage('userId');
        wx.request({
            url: apiUrl + '/appletcarousel/editcollect',
            data: {
                userid: userId,
                classtype: '8', // 装修效果图
                classid: id
            },
            header: {
                'content-type': 'application/json'
            },
            success(res) {
                if (res.data.state == 1) {
                    wx.showToast({
                        title: '取消收藏',
                        icon: 'success',
                        duration: 1200
                    });
                    let arr = that.data.data;
                    arr[index].is_collect = 0;
                    that.setData({ data: arr });
                }
            }
        });
    },
    /**
     * 跳转到装修报价页面
     */
    toBaojia() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        });
    }
});