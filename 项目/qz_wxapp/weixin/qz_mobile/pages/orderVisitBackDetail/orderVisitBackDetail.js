// pages/orderdetail/orderdetail.js
import {
    getOrderList,
    visitPushDeatil,
    visitNewPushDeatil
} from "../../utils/api.js"


Page({

    /**
     * 页面的初始数据
     */
    data: {
        visitOrderInfo: [],
        orderInfo: [],
        currentTab: 1,
        merge: []
    },
    switchTab(e) {
        let tab = e.currentTarget.dataset.tab;
        this.setData({
            currentTab: tab
        })
    },

    // 推送
    visitPush: function(e) {
        let that = this;
        if(!e.detail.value.visit_content) {
            wx.showToast({
                title: '请输入回访反馈',
                icon: 'none',
                duration: 2000
            })
            return
        }
        if(e.detail.value.company_ids.length <=0) {
            wx.showToast({
                title: '请选择要推送的装修公司',
                icon: 'none',
                duration: 2000
            })
            return
        }
        console.log(e.detail.value.company_ids)
        
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                wx.showModal({
                    title: '提示',
                    content: '您确定要推送吗',
                    success: function(res) {
                        if(res.confirm) {
                            if (that.data.visitOrderInfo.order_type === 1) {
                                that.visitNewPushAjax(token, e)
                            } else {
                                that.visitPushAjax(token, e)
                            }
                        }
                    }
                })
            }
        })
    },
    visitPushAjax(token, e) {
        visitPushDeatil('/v1/visit/push', {
            id: this.data.visitOrderInfo.id,
            company_ids: e.detail.value.company_ids.join(","),
            visit_content: e.detail.value.visit_content,
        }, {
            'content-type': 'application/json',
            'token': token
        }).then(res => {
            if (res.error_code == 4000002) {
                wx.showToast({
                    title: res.error_msg,
                    icon: 'none',
                    duration: 2000
                })
            } else if (res.error_code == 1000001){
                wx.showToast({
                    title: res.error_msg,
                    icon: 'none',
                    duration: 2000
                })
            }
            if (res.error_code == 0) {
                wx.showToast({
                    title: '成功',
                    icon: 'success',
                    duration: 2000
                })
            }
        }).catch(() =>{
            wx.hideToast({
                title: '取消推送',
                icon: 'success',
                duration: 2000
            })
        })
    },
    visitNewPushAjax(token, e) {
        visitNewPushDeatil('/v1/visit/new_push', {
            order_id: this.data.visitOrderInfo.order_id,
            company_ids: e.detail.value.company_ids.join(","),
            visit_content: e.detail.value.visit_content,
        }, {
                'content-type': 'application/json',
                'token': token
            }).then(res => {
                if (res.error_code == 4000002) {
                    wx.showToast({
                        title: res.error_msg,
                        icon: 'none',
                        duration: 2000
                    })
                } else if (res.error_code == 1000001) {
                    wx.showToast({
                        title: res.error_msg,
                        icon: 'none',
                        duration: 2000
                    })
                }
                if (res.error_code == 0) {
                    wx.showToast({
                        title: '成功',
                        icon: 'success',
                        duration: 2000
                    })
                }
            }).catch(() => {
                wx.hideToast({
                    title: '取消推送',
                    icon: 'success',
                    duration: 2000
                })
            })
    },
    getOrderVisitBackDetail: function(id,order_id,order_type) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderList('/v1/visit/detail', {
                    id: id,
                    order_id: order_id,
                    order_type: order_type
                }, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    console.log('orderinfo',res)
                    if (res.error_code == 0) {
                        let visitOrderInfo = res.data.detail
                        let orderInfo = res.data.orderinfo
                        that.setData({
                            visitOrderInfo: visitOrderInfo,
                            orderInfo: orderInfo,
                        })
                        console.log(that.data.visitOrderInfo.order_type)
                    } else {
                        console.log('错误')
                    }
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

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        let id = options.id
        let order_id = options.order_id
        let order_type = options.order_type
        if (options.type_fw) {
            let type_fw = options.type_fw
            this.setData({
                type_fw: type_fw
            })
        }
        this.getOrderVisitBackDetail(id,order_id,order_type)
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

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