// pages/swjMemberOffer/swjMemberOffer.js
import {
    memberReportDetail,
    memberReportCheckAction
} from "../../utils/api.js"

function getTime(timestamp) {
    let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '/';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function(res) {
            if (res.confirm) {
                confirm();
            } else if (res.cancel) {

            }
        }
    });
}
const app = getApp()
Page({

    /**
     * 页面的初始数据
     */
    data: {
        type: '', // 记录当前显示哪个栏目，装修公司，erp还是三维家
        common: false,
        erp: false,
        three: false,
        id: '', // 当前记录id
        ctype: '', // 会员类型
        info: null, // 数据信息
        maskOfferShow: false,
        offerHeight: 0,
        member_quote: null,
        isShowModal: false,
        modalTitle: '审核',
        checkCooperationType: '', // 需要审核记录的cooperation_type
        checkId: '', // 需要审核记录的id
        checkPassVal: '', // 存储通过还是不通过的值，3表示通过，4表示不通过
        checkRemark: '', // 存储审核备注
        activeTag: false, // 是否
        showHistoryModal: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const type = options.type
        const id = options.id
        const ctype = options.ctype
        console.log(options)
        this.setData({
            id: id,
            checkId: id,
            ctype: ctype,
            checkCooperationType: ctype
        })
        this.getDetail()
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
        this.setData({
            showModal:false
          })
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

    },

    /**
     * 获取当前记录详情
     */
    getDetail() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                memberReportDetail('/v1/sale_report/show', {
                    id: that.data.id,
                    cooperation_type: that.data.ctype,
                    admin_user: 1,
                    member_quote: 1
                }, {
                    'Content-Type': 'application/json',
                    'token': token
                }).then(res => {
                    console.log(res)
                    if (res.error_code == 0) {
                        const info = res.data.info
                        const member_quote = info.member_quote
                        info.current_contract_start = getTime(info.current_contract_start)
                        info.current_contract_end = getTime(info.current_contract_end)
                        for (var i = 0; i < info.img_list.length; i++) {
                            info.img_list[i] = app.globalData.imgBaseUrl + '/' + info.img_list[i]
                        }
                        for (var i = 0; i < info.kf_voucher_img.length; i++) {
                          info.kf_voucher_img[i] = app.globalData.imgBaseUrl + '/' + info.kf_voucher_img[i]
                        }
                        that.setData({
                            info: info,
                            member_quote: member_quote
                        })
                    } else {
                        wx.showToast({
                            title: res.error_msg,
                            duration: 2000,
                            icon: 'none'
                        })
                    }
                })
            },
            fail: function(res) {
                wx.navigateTo({
                    url: '../login/login'
                })
            }
        })
    },
    /**
     * 显示报价详情
     */
    showOfferDetail() {

        this.setData({
            maskOfferShow: true,
            offerHeight: "1343rpx"
        })
        setTimeout(() => {
            this.setData({
                activeTag: true
            })
        }, 200)
    },
    /**
     * 显示审核弹窗，同时要记录cooperation_type和id
     */
    showModalFn(e) {
        this.setData({
            isShowModal: true,
            checkRemark: '',
            checkPassVal: ''
        })
    },

    showModalHistoryFn(e) {
        let that = this
        that.setData({
            showHistoryModal: true,
        })
    },
    /**
     * 自定义模态框取消事件
     */
    modalCancel() {
        // wx.showToast({
        //   title: '取消',
        // })
    },
    /**
     * 自定义模态框确认事件
     */
    modalConfirm() {
        // wx.showToast({
        //   title: '确认',
        // })
        if (!this.data.checkPassVal) {
            this.setData({
                isShowModal: true
            })
            alertViewWithCancel('提示', '请选择是否通过', function() {})
            return
        }
        this.handleCheckAjax()
    },
    /**
     * 发送审核数据
     */
    handleCheckAjax() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: (res) => {
                let token = res.data.token;
                memberReportCheckAction('/v1/sale_report/set_status', {
                    cooperation_type: this.data.checkCooperationType,
                    id: this.data.checkId,
                    status: this.data.checkPassVal, // 通过填3， 不通过填4
                    admin_remarke: this.data.checkRemark
                }, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        that.setData({
                            isShowModal: false
                        })
                        wx.showToast({
                            title: '审核成功',
                            icon: 'none',
                            duration: 3000
                        })
                        setTimeout(function () {
                            wx.navigateBack({
                                url: '../memberReportCheck/memberReportCheck',
                            })
                        }, 2000)
                    } else {
                        alertViewWithCancel('提示', res.error_msg, function() {})
                        that.setData({
                            isShowModal: true
                        })
                    }
                })
            }
        })
    },
    /**
     *  预览图片
     */
    previewImg1: function(e) {
        //获取当前图片的下标
        var index = e.currentTarget.dataset.index;
        //所有图片
        var pics = this.data.info.img_list;
        wx.previewImage({
            //当前显示图片
            current: pics[index],
            //所有图片
            urls: pics
        })
    },
    previewImg2: function (e) {
      //获取当前图片的下标
      var index = e.currentTarget.dataset.index;
      //所有图片
      var kfPics = this.data.info.kf_voucher_img;
      wx.previewImage({
        //当前显示图片
        current: kfPics[index],
        //所有图片
        urls: kfPics
      })
    },
    /**
     * 获取通过还是不通过的值
     */
    radioChange(e) {
        this.setData({
            checkPassVal: e.detail.value
        })
    },
    /**
     * 获取备注的值
     */
    getRemark(e) {
        this.setData({
            checkRemark: e.detail.value
        })
    },
    closeOffer() {
        console.log('close')
        this.setData({
            maskOfferShow: false,
            activeTag: false
        })
    }
})