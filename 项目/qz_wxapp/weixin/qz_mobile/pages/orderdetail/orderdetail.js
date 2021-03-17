// pages/orderdetail/orderdetail.js
import {
    getOrderList
} from "../../utils/api.js"
Page({

    /**
     * 页面的初始数据
     */
    data: {
        detail: [],
        merge: [],
        company_track_list:[] ,
        now_track_company_id: 0,
        now_visit_list_index:0,
        visit_list: [], //齐装回访
        maskShow: false, //小计弹窗
        ellipsis: [], // 文字是否收起，默认收起
        order_id: ''
    },
    /**
   * 收起/展开按钮点击事件
   */
    ellipsis: function (e) {
        let index = e.currentTarget.dataset.index
        let ellipsisArr = this.data.ellipsis
        ellipsisArr[index] = !ellipsisArr[index]
        this.setData({
            ellipsis: ellipsisArr,
            now_visit_list_index: e.currentTarget.dataset.index
        })
    },


    // 小计
    xiaoji(e){
        wx.navigateTo({
            url: '../orderVisitLog/orderVisitLog?orderid=' + e.target.dataset.orderid + '&comid=' + e.target.dataset.comid
        })
        return
        let ellipsisArr = []
        this.setData({
            maskShow:true,
            now_track_company_id: e.target.dataset.comid
        })
        this.data.company_track_list.forEach(item => {
            if(this.data.now_track_company_id == item.company_id) {
                item.track_list.forEach(jitem => {
                    ellipsisArr.push(true)
                })
            }
        })
        this.setData({
            ellipsis: ellipsisArr
        })
    },
    //关闭
    close(){
        this.setData({
            maskShow: false,
            now_track_company_id: 0
        })
    },
    //复制
    copyBtn: function(e) {
        wx.setClipboardData({
            data: e.target.dataset.id,
            success (res) {
                wx.showToast({
                title: '订单号已复制',
                duration: 1000
                })
            }
        })
    },
    getOrderdetail: function(id) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderList('/v1/order/detail', {
                    id: id
                }, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                  
                    if (res.error_code == 0) {
                        let tex = res.data.detail.text;
                        if (tex) {
                            let str = tex.replace(/↵/g, '\n');
                            that.setData({
                                str: str
                            })
                        }
                        let visit_content = res.data.detail.visit_list.map(el => el.visit_content)
                        let company_track_list = res.data.detail.company_track_list
                        
                       
                        // console.log(res.data.detail)
                        var track_list = []
                        if (company_track_list && company_track_list instanceof Array){
                            track_list = company_track_list.map(item => item)
                        }
                        let arr1 = res.data.detail.companys;
                        let arr2 = res.data.detail.liangfanglist;
                        var merge = []
                        arr1.forEach(item => {
                            arr2.forEach(t => {
                                if(Number(item.company_id) === Number(t.comid)){
                                    item.status = t.status
                                }
                            })
                            company_track_list.forEach(it => {
                                if(Number(item.company_id) === Number(it.company_id)){
                                    item.is_show_track = 1
                                }
                            })
                        })
                        merge.push(...arr1);

                        that.setData({
                            detail: res.data.detail,
                            merge: merge,
                            visit_list: res.data.detail.visit_list,
                            visit_content: visit_content,
                            company_track_list: company_track_list,
                            track_list: track_list
                        })
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
    preventScroll() {},
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        let id = options.id
        this.setData({
            order_id: id
        })
        if (options.type_fw) {
            let type_fw = options.type_fw
            this.setData({
                type_fw: type_fw
            })
        }
        this.getOrderdetail(id)
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