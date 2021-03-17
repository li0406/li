// pages/performancePerson/performancePerson.js
import { performancePersonList} from "../../utils/api.js"
const app = getApp()
Page({

    /**
     * 页面的初始数据
     */
    data: {
        id: '',
        personList: [],
        percentData:[],
        saler_ids: '',
        value: '',
        isShow: false,
        noData: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let arr = []
        let that = this
        if (options.id) {
            that.setData({
                id: options.id
            })
        } else {
            that.setData({
                id: ''
            })
        }
        for(var i = 1; i <= 100; i++){
            arr.push(i)
        }
        that.setData({
            percentData: arr
        })
        this.performancePersonList(this.data.id, '')
    },
    searchWord: function(e) {
        this.performancePersonList(this.data.id, e.detail.value)
    },

    // 业绩人list
    performancePersonList(id, saler_name) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function (res) {
                let token = res.data.token;
                performancePersonList('/v1/sale_report/payment/other_person_list', {
                    id: id,
                    saler_ids: that.data.saler_ids,
                    saler_name: saler_name
                }, {
                        'content-type': 'application/json',
                        'token': token
                    }).then(res => {
                        if (res.error_code == 0) {
                            if (res.data.list.length > 0) {
                                that.setData({
                                    personList: res.data.list,
                                    isShow: true,
                                    noData: false
                                })
                            } else {
                                that.setData({
                                    personList: [],
                                    isShow: false,
                                    noData: true
                                })
                            }
                            
                        } else {
                            alertViewNoCancel("请求失败", res.error_msg, function () { });
                            return;
                        }
                    }).catch(function (error) { })
            },
            fail: function (res) {
                
            }
        })
    },
    getPercent(e) {
        let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
        let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）
        let object = prevPage.data.personList
        let that = this
        that.setData({
            value: e.detail.value
        })
        object.push({
            saler_name: e.currentTarget.dataset.name,
            saler_id: e.currentTarget.dataset.id,
            share_ratio: parseInt(e.detail.value) + 1
        })
        prevPage.setData({
            personList: object
        })
        wx.navigateBack({
            delta: 1,
        })
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
        let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
        let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）
        let arr = prevPage.data.personList
        let brr = ''
        arr.forEach((index, item) => {
            brr = brr + ',' + index.saler_id
        })
        this.setData({
            saler_ids: brr
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

    }
})