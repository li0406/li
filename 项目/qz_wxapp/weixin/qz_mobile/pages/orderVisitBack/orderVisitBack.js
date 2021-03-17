// pages/order/order.js
import {
    getOrderVisitBackList,
    getOrderVisitBackOption,
    visitPushDeatil
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function(res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}
Page({
    data: {
        curCity: '不限',
        curArea: '',
        cs: '',
        qx: '',
        createTime: '2010-10-10',
        nowTime: util.formatDate(new Date()),
        // date_begin: '',
        // date_end: '',
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        // tab
        fen: [{
            id: '0',
            name: '被动'
        }, {
            id: '1',
            name: '主动'
        }],
        fen_index: 1,
        yu: [],
        yu_index: -1,
        cla: [{
            id: '',
            name: '请选择'
        }, {
            id: '1',
            name: '已回访'
        }, {
            id: '2',
            name: '未回访'
        }],
        cla_index: 1,
        meth: [{
            id: '0',
            name: '请选择'
        }, {
            id: '1',
            name: '未推送'
        }, {
            id: '2',
            name: '已推送'
        }],
        meth_index: -1,
        fenId: 2,
        yuId: '',
        claId: '1',
        methId: '',
        // 请求数据
        list: [],
        pageCurrent: '0',
        pageTotalNumber: '0',
        parms: {
            //   id: '',
            order_id: '', // 订单号
            //   date_begin: '',
            //   date_end: '',
            visit_step: '', // 回访阶段
            visit_type: 2, // 回访类型
            valid_status: '1', // 回访状态
            visit_push: '', // 是否推送
            cs: '',
            //   area_id: '',
            page: 1
        },
        noResult: false,
        page: false,
        pageNumber: [],
        id: ''
    },
    //城市选择
      toCity: function() {
        let city = this.data.curCity;
        wx.navigateTo({
          url: '../city/city?needArea=1&curCity=' + city
        })
      },
    //搜索框
    searchWord: function(e) {
        let that = this;
        let value = e.detail.value;
        that.setData({
            ['parms.order_id']: value,
            ['parms.page']: 1,
            ['parms.valid_status']: '',
            cla_index: 0
        })
        that.getOrderVisitBackList(that.data.parms);
    },
    // 监听输入
    watchPassWord: function(event) {
        console.log(event.detail.value);
    },
    //选择时间
    //   dateChange: function(e) {
    //     var keys = e.currentTarget.dataset.time;
    //     var obj = {};
    //     obj[keys] = e.detail.value;
    //     if (keys == 'date_begin' && this.data.date_end != '' && e.detail.value > this.data.date_end) {
    //       obj.date_end = e.detail.value;
    //     }
    //     this.setData(obj);
    //     let that = this
    //     if (that.data.date_begin != '' && that.data.date_end != '') {
    //       let date_begin = that.data.date_begin
    //       let date_end = that.data.date_end
    //       that.setData({
    //         ['parms.date_begin']: date_begin,
    //         ['parms.date_end']: date_end,
    //         ['parms.page']: 1
    //       })
    //       that.getOrderVisitBackList(that.data.parms);
    //     }
    //   },
    // 选项卡
    bindPickerChange_fen: function(e) {
        let id = Number(this.data.fen[e.detail.value].id) + 1
        this.setData({
            fen_index: e.detail.value,
            fenId: id
        })
        let that = this;
        that.setData({
            ['parms.visit_type']: id,
            ['parms.page']: 1
        })
        that.getOrderVisitBackList(that.data.parms);
    },
    bindPickerChange_yu: function(e) {
        let id = this.data.yu[e.detail.value].id
        this.setData({
            yu_index: e.detail.value,
            yuId: id
        })
        let that = this;
        that.setData({
            ['parms.visit_step']: id,
            ['parms.page']: 1
        })
        that.getOrderVisitBackList(that.data.parms);
    },
    bindPickerChange_cla: function(e) {
        let id = this.data.cla[e.detail.value].id
        this.setData({
            cla_index: e.detail.value,
            claId: id
        })
        let that = this;
        that.setData({
            ['parms.valid_status']: id,
            ['parms.page']: 1
        })
        that.getOrderVisitBackList(that.data.parms);
    },
    bindPickerChange_meth: function(e) {
        let id = this.data.meth[e.detail.value].id
        this.setData({
            meth_index: e.detail.value,
            methId: id
        })
        let that = this;
        that.setData({
            ['parms.visit_push']: id,
            ['parms.page']: 1
        })
        that.getOrderVisitBackList(that.data.parms);
    },
    getFormOptions: function() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderVisitBackOption('/v1/visit/options', '', {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    let allOptions = [{
                        id: '0',
                        name: '请选择'
                    }]
                    allOptions = allOptions.concat(res.data.visit_step_list)
                    that.setData({
                        yu: allOptions
                    })
                })
            }
        })
    },

    //列表数据
    getOrderVisitBackList: function(parms) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderVisitBackList('/v1/visit/list',
                    parms, {
                        'content-type': 'application/json',
                        'token': token
                    }
                ).then(res => {
                    if (res.error_code == 0) {
                        //獲取頁嗎
                        let totalNumber = res.data.page.page_total_number;
                        let pageArr = [];
                        for (let i = 0; i < totalNumber; i++) {
                            pageArr.push(i + 1)
                        }

                        let datalist = res.data.list
                        if (datalist.length < 1) {
                            that.setData({
                                list: [],
                                page: false,
                                noResult: true
                            })
                            return false
                        }

                        datalist.forEach((item, index) => {
                            let ndate = new Date(item.created_at * 1000)
                            let hours = ndate.getHours()
                            let mintes = ndate.getMinutes()
                            if(parseInt(hours) < 10) {
                                hours = '0' + hours
                            }
                            if(parseInt(mintes) < 10) {
                                mintes = '0' + mintes
                            }
                            item.url = '../orderVisitBackDetail/orderVisitBackDetail?id=' + item.id + '&order_id=' + item.order_id + '&order_type=' + item.order_type
                            item.created_date = ndate.getFullYear() + '-' + (ndate.getMonth() + 1) + '-' + ndate.getDate() + ' ' + hours + ':' + mintes
                            switch(item.visit_push){
                                case 1:
                                    item.visit_push_name = '未推送'
                                    break
                                case 2:
                                    item.visit_push_name = '已推送'
                                    break
                                default:
                                    item.visit_push_name = ''
                            }
                            
                        })
                        that.setData({
                            list: datalist,
                            pageCurrent: res.data.page.page_current,
                            pageTotalNumber: res.data.page.page_total_number,
                            pageNumber: pageArr,
                            noResult: false,
                            page: true,

                        })
                        if (res.data.list[0].visit_type) {
                            that.setData({
                                fen_index: Number(res.data.list[0].visit_type) - 1
                            })
                        } else {
                            that.setData({
                                fen_index: 1
                            })
                        }
                        if (res.data.page.total_number < 10) {
                            that.setData({
                                page: false
                            })
                        } else {
                            that.setData({
                                page: true
                            })
                        }
                    } else {
                        that.setData({
                            list: [],
                            page: false,
                            noResult: true,
                            noInternet: false
                        })
                    }
                }).catch(function(error) {
                    that.setData({
                        list: [],
                        page: false,
                        noResult: false,
                        noInternet: true
                    })
                })
            },
            fail: function(res) {
                wx.showToast({
                    title: '请登陆',
                    icon: 'none',
                    duration: 2000
                })
                setTimeout(function() {
                    wx.navigateTo({
                        url: '../login/login'
                    })
                }, 2000)

            }
        })
    },
    prevBtn: function(e) {
        let that = this;
        let page = that.data.pageCurrent;
        if (page <= 1) {
            page = 1;
            return;
        } else {
            page = page - 1;
        }
        that.setData({
            ['parms.page']: page
        })
        that.getOrderVisitBackList(that.data.parms)

    },
    nextBtn: function(e) {
        let that = this;
        let page = that.data.pageCurrent;
        let max = that.data.pageTotalNumber;
        if (page >= max) {
            page = max;
            return;
        } else {
            page = page + 1;
        }
        that.setData({
            ['parms.page']: page
        })
        that.getOrderVisitBackList(that.data.parms)
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })

    },
    // 网络故障
    toCustom: function() {
        wx.navigateTo({
          url: '../orderVisitBack/orderVisitBack'
        })
    },
    // 分页
    toPage: function(e) {
        let that = this;
        let p = e.detail.value;
        p = Number(p) + 1;
        that.setData({
            ['parms.page']: p
        })
        that.getOrderVisitBackList(that.data.parms);
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })
    },
    // 撤回
    widthdrawVisit: function(e) {
        let that = this;
        let id = e.currentTarget.dataset.id
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                wx.showModal({
                    title: '提示',
                    content: '您确定要撤回吗',
                    success: function(res) {
                        if(res.confirm) {
                            that.visitDeleteAjax(token, e)
                        }
                    }
                })
               
            }
        })
    },
    visitDeleteAjax(token,e) {
        visitPushDeatil('/v1/visit/delete', {
            id: e.currentTarget.dataset.id
        }, {
            'content-type': 'application/json',
            'token': token
        }).then(res => {
            if (res.error_code == 0) {
                wx.showToast({
                    title: '撤回成功',
                    icon: 'success',
                    duration: 2000
                })
                this.getOrderVisitBackList(this.data.parms);
            } else{
                wx.showToast({
                    title: '撤回失败',
                    icon: 'success',
                    duration: 2000
                })
            }
        })
    },
    onLoad: function(options) {
        this.getFormOptions()
        let that = this;
        that.getOrderVisitBackList(that.data.parms);
    },

    onReady: function() {

    },

    onShow: function() {
        let that = this;
        that.setData({
            //   ['parms.date_begin']: that.data.date_begin,
            //   ['parms.date_end']: that.data.date_end,
            ['parms.visit_step']: that.data.yuId,
            ['parms.visit_type']: that.data.fenId,
            ['parms.valid_status']: that.data.claId,
            ['parms.visit_push']: that.data.methId,
            ['parms.cs']: that.data.cs,
            //   ['parms.area_id']: that.data.qx
        });

        that.getOrderVisitBackList(that.data.parms)
        if (that.data.list != '') {
            that.setData({
                noResult: false
            })
        }
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})