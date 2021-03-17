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
    xiaoji(option){
        let ellipsisArr = []
        this.setData({
            maskShow:true,
            now_track_company_id: option.comid
        })
        console.log('111')
        console.log(this.data.company_track_list)
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
    getOrderdetail: function(option) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderList('/v1/order/detail', {
                    id: option.orderid
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
                        
                       
                        console.log(res.data.detail)
                        var track_list = []
                        if (company_track_list && company_track_list instanceof Array){
                            track_list = company_track_list.map(item => item)
                        }
                        let arr1 = res.data.detail.companys;
                        let arr2 = res.data.detail.liangfanglist;
                        let arrLenth = arr1.length
                        var merge = [],
                            add = [],
                            kvIndex = {};
                        if (arr2.length >= 1) {
                            for (var i = 0; i < arr1.length; i++) {
                                for (var j = 0; j < arr2.length; j++) {
                                    if (arr1[i].company_id == arr2[j].comid) {
                                        var item
                                        if (kvIndex[arr1[i].company_id] == undefined) {
                                            kvIndex[arr1[i].company_id] = merge.length;
                                            item = {};
                                            for (var attr in arr1[i]) item[attr] = arr1[i][attr];
                                            merge[kvIndex[arr1[i].company_id]] = item;

                                        } else item = merge[kvIndex[arr1[i].company_id]];
                                        for (var attr in arr2[j]) item[attr] = arr2[j][attr];
                                    } else if (arr1[i].company_id != arr2[j].comid) {
                                        add.push(arr1[i])
                                    }
                                }
                            }
                            merge.push(...add);
                        } else {
                            merge.push(...arr1);
                        }
                        let mera = merge.slice(0, arrLenth)
                        // 显示小计
                        for (var i in mera) {
                            var is_show_track = 0;
                            for (j in company_track_list) {
                                if (company_track_list[j]["company_id"] == mera[i]["company_id"]) {
                                    is_show_track = 1;
                                    break;
                                }
                            }
                            mera[i]["is_show_track"] = is_show_track;
                        }

                        that.setData({
                            detail: res.data.detail,
                            merge: mera,
                            visit_list: res.data.detail.visit_list,
                            visit_content: visit_content,
                            company_track_list: company_track_list,
                            track_list: track_list
                        })
                        that.xiaoji(option)
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
        console.log(options)
        this.getOrderdetail(options)
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