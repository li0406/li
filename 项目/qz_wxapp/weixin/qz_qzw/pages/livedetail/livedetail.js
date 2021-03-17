// pages/livedetail/livedetail.js
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        id: '',
        company_info: '',
        info: '',
        tel: '',
        step_list: [], //阶段名称列表
        step: '',  //施工阶段
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,  //当前页
        limit: 5, //每页条数
        stepList: [], //阶段内容列表
        scrollInfo: null, //固定阶段名称
        topNum: 0,
        largeView: false,
        swiperData: [],
        swiperLen: '',
        currentIndex: 1,
        isOrderShow: false,
        teleInfo: '',
        teleCode: '',
        errorTit: '',
        isError: false,
        isTime: false,
        time: 60,
        isShow: false,   //发布输入框是否显示
        isTanchuang: '', //发布成功后弹窗
        info_id: '', //施工id
        comment_content: '', //评论内容
        wxqrcode_url: '', //二维码图片
        like_num: ''
    },

    closeOrder: function () {
        this.setData({
            isOrderShow: false,
            teleInfo: '',
            teleCode: ''
        })
    },
    orderShow: function () {
        this.setData({
            isOrderShow: true
        })
    },

    userTel: function (e) {
        this.setData({
            teleInfo: e.detail.value
        })
    },
    searchOrder: function (e) {
        this.setData({
            teleCode: e.detail.value
        })
    },
    liveSubmutBtn: function () {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        let token = wx.getStorageSync('jwt_token')
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入业主手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的业主手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else {
            if (that.data.teleCode == '') {
                that.setData({
                    isError: true,
                    errorTit: '请输入业主验证码'
                })
                setTimeout(function () {
                    that.setData({
                        isError: false,
                        errorTit: ''
                    })
                }, 1000)
            } else {
                wx.request({
                    url: zxsApiUrl + '/business/worksite/user/get_lastorder_status',
                    method: 'GET',
                    data: {
                        tel: that.data.teleInfo,
                        day: 14,
                        sms_code: that.data.teleCode,
                        from_type: 2
                    },
                    header: {
                        token: token,
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    success: function (res) {
                        if (res.data.error_code === 0) {
                            if (res.data.data.order_status == 4) {
                                wx.navigateTo({
                                    url: '../livedetail/livedetail?phone=' + that.data.teleInfo + '&order_status=' + res.data.data.order_status,
                                })
                            } else {
                                wx.navigateTo({
                                    url: '../livereserve/livereserve?phone=' + that.data.teleInfo + '&order_status=' + res.data.data.order_status,
                                })
                            }
                        } else {
                            that.setData({
                                isError: true,
                                errorTit: res.data.error_msg
                            })
                            setTimeout(function () {
                                that.setData({
                                    isError: false,
                                    errorTit: ''
                                })
                            }, 1000)
                        }
                    }
                });
            }
        }
    },
    getCode: function () {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入业主手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的业主手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else {
            that.setData({
                isError: false,
                errorTit: ''
            })
            wx.request({
                url: zxsApiUrl + '/business/common/send_sms_code',
                method: 'POST',
                data: {
                    phone: that.data.teleInfo,
                    type: 1
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    that.setData({
                        isError: true,
                        errorTit: '验证码已发送'
                    })
                    setTimeout(function () {
                        that.setData({
                            isError: false,
                            errorTit: '',
                            isTime: true
                        })
                    }, 1000)
                    let timer = setInterval(function () {
                        let time = that.data.time - 1
                        that.setData({
                            time: time
                        })
                        if (time === 0) {
                            that.setData({
                                isTime: false,
                                time: 60
                            })
                            clearInterval(timer)
                        }
                    }, 1000)
                }
            });
        }
    },
    scrollBig(e) {
        let scrollT = e.detail.scrollTop
        this.setData({
            scrollInfo: scrollT
        })
    },
    chooseItem: function (e) {
        this.setData({
            topNum: 300
        });
        let step = e.currentTarget.dataset.step || ''
        this.setData({
            scrollInfo: true,
            step: step,
            nomore: 2,
            stepList: [],
            page: 1
        })
        this.getdetail()
    },
    getdetail: function () {
        let that = this
        let token = wx.getStorageSync('jwt_token')
        if (that.data.nomore != 2) {
            return
        }
        that.setData({
            nomore: 1
        })
        if (that.data.page == 1) {
            wx.showLoading({
                title: '加载中',
            })
        }
        wx.request({
            url: zxsApiUrl + '/business/worksite/user/info_list',
            method: 'GET',
            data: {
                live_id: that.data.id,
                step: that.data.step,
                page: that.data.page,
                limit: that.data.limit
            },
            header: {
                token: token,
                'content-type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
                if (that.data.page == 1) {
                    wx.hideLoading()
                    that.setData({
                        stepList: []
                    })
                }
                if (res.data.error_code == 0) {
                    let stepList = that.data.stepList;
                    stepList = stepList.concat(res.data.data.list);
                    stepList.forEach(item => {
                        item.tuLen = item.media_list.length
                        if (item.media_list.length > 0) {
                            item.videoInfo = item.media_list[0].type
                        }
                        // 满意度
                        if (item.check_ret == 1) {
                            item.check_ret_msg = '非常满意'
                        } else if (item.check_ret == 2) {
                            item.check_ret_msg = '还算满意'
                        } else if (item.check_ret == 3) {
                            item.check_ret_msg = '很不满意'
                        }
                        wx.setStorageSync('info_id', item.id)
                        wx.setStorageSync('check_ret', item.check_ret)
                        item.check_effect_list = item.check_effect.split(',')
                    })

                    that.setData({
                        stepList: stepList,
                        info_id: stepList.map(item => item.id),
                    })
                    if (res.data.data.list.length < 5) {
                        that.setData({
                            nomore: 3
                        })
                    } else {
                        that.setData({
                            nomore: 2,
                            page: that.data.page + 1
                        })
                    }
                }

            }
        });

    },
    //滚动到底部100px时触发
    lower: function (e) {
        this.getdetail()
    },
    tuClick(e) {
        let id = e.currentTarget.dataset.id
        let tid = e.currentTarget.dataset.tid
        let that = this
        let stepList = that.data.stepList
        var imglist = stepList.find(function (imglist) {
            return imglist.id == id
        })
        let swiperData = imglist.media_list
        let startNum = swiperData[0].id
        let currentNum = (tid - startNum) + 1

        that.setData({
            swiperData: swiperData,
            swiperLen: swiperData.length,
            currentIndex: currentNum,
            largeView: !that.data.largeView
        })
    },
    getLargeImage() {
        this.setData({
            largeView: !this.data.largeView
        })
    },
    swiperChange(e) {
        this.setData({
            currentIndex: e.detail.current + 1
        })
    },
    onLoad: function (options) {
        // console.log(options)
        let that = this
        that.setData({
            tel: options.phone
        })
        that.getInfo(this.data.tel)
    },
    onShow: function () {
        let that = this
        that.setData({
            isOrderShow: false,
            page: 1,
            stepList: [],
            nomore: 2
        })
        that.getInfo(this.data.tel)
    },
    getInfo: function (tel) {
        let that = this
        let token = wx.getStorageSync('jwt_token')
        wx.request({
            url: zxsApiUrl + '/business/worksite/user/live_detail',
            method: 'GET',
            data: {
                tel: tel
            },
            header: {
                token: token,
                'content-type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
                if (res.data.error_code == 0) {
                    that.setData({
                        info: res.data.data.info,
                        id: res.data.data.info.id,
                        step_list: res.data.data.info.step_list,
                        stepLen: res.data.data.info.step_list.length
                    })
                }
                that.getdetail()
            }
        });
    },

    changeData: function () {
        that.getdetail()
    },

    //阶段验收
    toAcceptance: function (e) {
        wx.setStorageSync('info_id', e.currentTarget.dataset.id)
        wx.navigateTo({
            url: '../acceptance/acceptance',
        })
    },

    //施工点评
    toComment: function (e) {
        let that = this
        // that.setData({
        //     info_id: e.currentTarget.dataset.id,
        //     isShow: true
        // })
        let id= e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../comment/comment?id='+id,
        })
    },

    //删除评论
    contentDel: function (e) {
        let that = this;
        let infoId = e.currentTarget.dataset.infoid;
        let commentId = e.currentTarget.dataset.id;
        wx.showModal({
            title: '删除提示',
            content: '是否确定删除此评论？',
            success(res) {
                if (res.confirm) {
                    let token = wx.getStorageSync('jwt_token')
                    wx.request({
                        url: zxsApiUrl + '/business/worksite/user/info_comment_del',
                        method: 'POST',
                        data: {
                            info_id: infoId,
                            comment_id: commentId
                        },
                        header: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            token: token
                        },
                        dataType: 'json',
                        responseType: 'text',
                        success: function (res) {
                            if (res.data.error_code === 0) {
                                wx.showToast({
                                    title: '删除成功',
                                    icon: 'none',
                                    duration: 2000
                                })
                                that.getNewdetail()
                            } else {
                                wx.showToast({
                                    title: res.data.error_msg,
                                    icon: 'none',
                                    duration: 2000
                                })
                            }
                        },
                        fail: function (res) { },
                        complete: function (res) { },
                    })
                } else if (res.cancel) {
                    // console.log('用户点击取消')
                }
            }
        })
    },

    //发布
    bindFormSubmit: function (e) {
        let that = this;
        let token = wx.getStorageSync('jwt_token')
        let comment_content = e.detail.value.textarea
        if (comment_content) {
            wx.showLoading();
            wx.request({
                url: zxsApiUrl + '/business/worksite/user/info_comment',
                method: 'POST',
                data: {
                    info_id: that.data.info_id,
                    comment_content: comment_content
                },
                header: {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    wx.hideLoading();
                    if (res.data.error_code == 0) {
                        that.setData({
                            isShow: false,
                            isTanchuang: res.data.data.wxofficial_show,
                            wxqrcode_url: res.data.data.wxqrcode_url
                        })
                        wx.showToast({
                            title: '发布成功',
                            icon: 'none',
                            duration: 2000
                        })
                        that.getNewdetail()
                    }
                },
                complete: function (res) {
                    wx.hideLoading();
                }

            });
        }
    },

    //关闭弹窗
    closeTanc: function () {
        this.setData({
            isTanchuang: false
        })
    },
    //点赞
    onChangeTap: function (e) {
        let that = this;
        let id = e.currentTarget.dataset.id;
        let liveNum = e.currentTarget.dataset.liveNum;
        let isLike = e.currentTarget.dataset.isLike;
        let index = e.currentTarget.dataset.index
        let token = wx.getStorageSync('jwt_token')

        let list = that.data.stepList


        if (token == '') {
            wx.navigateTo({
                url: '../login/login'
            })
        } else {
            if (isLike == 0) {
                wx.request({
                    url: zxsApiUrl + '/business/worksite/user/info_like',
                    method: 'POST',
                    data: {
                        info_id: e.currentTarget.dataset.id
                    },
                    header: {
                        token: token,
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    success: function (res) {
                        if (res.data.error_code == 0) {
                            list[index].like_checked = 1;
                            list[index].like_num++;
                            that.setData({
                                stepList: list,
                            })
                        } else {
                            wx.showToast({
                                title: res.data.error_msg,
                                icon: 'none',
                                duration: 2000
                            })
                        }
                    }
                });
            }
        }
    },
    getNewdetail: function () {
        let that = this;
        let token = wx.getStorageSync('jwt_token')
        that.setData({
            step: that.data.step,
            page: 1,
            stepList: []
        })
        wx.request({
            url: zxsApiUrl + '/business/worksite/user/info_list',
            method: 'GET',
            data: {
                live_id: that.data.id,
                step: that.data.step,
                page: that.data.page,
                limit: that.data.limit
            },
            header: {
                token: token,
                'content-type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
                if (that.data.page == 1) {
                    wx.hideLoading()
                    that.setData({
                        stepList: []
                    })
                }
                if (res.data.error_code == 0) {
                    let stepList = that.data.stepList;
                    stepList = stepList.concat(res.data.data.list);
                    stepList.forEach(item => {
                        item.tuLen = item.media_list.length
                        if (item.media_list.length > 0) {
                            item.videoInfo = item.media_list[0].type
                        }
                        if (item.check_ret == 1) {
                            item.check_ret = '非常满意'
                        } else if (item.check_ret == 2) {
                            item.check_ret = '还算满意'
                        } else if (item.check_ret == 3) {
                            item.check_ret = '很不满意'
                        }
                    })
                    that.setData({
                        stepList: stepList,
                    })
                }
            }
        });
    },

    onHide: function () {

    },

    onUnload: function () {

    },


    onPullDownRefresh: function () {

    },


    onReachBottom: function () {

    },

    onShareAppMessage: function () {

    }
})