// pages/acceptance/acceptance.js
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        manyiduTag: [
            { 'manyidu': '非常满意', 'url': '../../img/laugh.png', 'id': 1 },
            { 'manyidu': '还算满意', 'url': '../../img/smile.png', 'id': 2 },
            { 'manyidu': '很不满意', 'url': '../../img/sad.png', 'id': 3 },
        ],
        currentTab: 1,
        // curIndex:0,
        // 施工印象
        list: [],
        good_effects: [], //满意列表
        fairly_effects: [], //还算满意列表
        bad_effects: [],  //不满意列表
        detail: [], //施工验收数据
        check_effects: [], //施工印象
        old_check_effects: [], //临时存贮施工印象
        check_ret: '', //满意度（合法的值：1、2、3）
    },

    //选中满意度
    select_manyidu: function (e) {

        let that = this;
        let id = e.currentTarget.dataset.id;
        let check_effects = []

        that.resetData();

        if (id == 1) {
            that.setData({
                list: that.data.good_effects
            })
        } else if (id == 2) {
            that.setData({
                list: that.data.fairly_effects
            })
        } else if (id == 3) {
            that.setData({
                list: that.data.bad_effects
            })
        }
        that.setData({
            currentTab: e.currentTarget.dataset.id,
        })
    },
    // 选择印象标签
    checkedName: function (e) {
        let that = this;
        let index = e.currentTarget.dataset.index //选中的下标
        let list = that.data.list
        let check_effects = this.data.check_effects
        let name = this.data.list[index].name;
        if (list[index].checked == 1) {
            list[index].checked = 0
            check_effects.splice(name, 1)
        } else {
            if (check_effects.length < 3) {
                list[index].checked = 1
                check_effects.push(name)
            } else {
                wx.showToast({
                    title: '最多选择3个印象',
                    icon: 'none'
                });
            }
        }
        that.setData({
            list: list
        })

    },
    //进入验收页
    getYanshou: function () {
        let that = this;
        let token = wx.getStorageSync('jwt_token')
        wx.request({
            url: zxsApiUrl + '/business/worksite/user/info_check_msg',
            method: 'GET',
            data: {
                info_id: wx.getStorageSync('info_id')
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded',
                token: token,
            },
            success: function (res) {
                if (res.data.error_code == 0) {
                    // wx.setStorageSync('check_ret', res.data.data.detail.check_ret)
                    wx.setStorageSync('check_effects', res.data.data.detail.check_effects)
                    let currentTab = res.data.data.detail.check_ret
                    let check_effects = res.data.data.detail.check_effects
                    let good_effects = res.data.data.good_effects
                    let fairly_effects = res.data.data.fairly_effects
                    let bad_effects = res.data.data.bad_effects

                    that.setData({
                        currentTab: currentTab, //验收满意度（1：非常满意；2：还算满意；3：很不满意）
                        check_effects: check_effects, //施工印象列表
                        good_effects: good_effects, //非常满意 印象列表
                        fairly_effects: fairly_effects, //还算满意 印象列表
                        bad_effects: bad_effects, //很不满意 印象列表
                    })
                    if (currentTab == 1) {
                        that.setData({
                            list: good_effects
                        })
                    } else if (currentTab == 2) {
                        that.setData({
                            list: fairly_effects
                        })
                    } else if (currentTab == 3) {
                        that.setData({
                            list: bad_effects
                        })
                    }
                }
            }
        });

    },
    // 发布验收
    releaseYanshou: function () {
        let that = this;
        let token = wx.getStorageSync('jwt_token')
        wx.request({
            url: zxsApiUrl + '/business/worksite/user/info_check',
            method: 'POST',
            data: {
                info_id: wx.getStorageSync('info_id'),
                check_ret: that.data.currentTab, //满意度
                check_effects: that.data.check_effects.join(','), //施工印象（多个用，隔开，最多三个） 
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded',
                token: token,
            },
            success: function (res) {
                if (res.data.error_code == 0) {
                    that.setData({
                        check_ret: that.data.currentTab,
                        check_effects: that.data.check_effects,
                    })
                    wx.showToast({
                        title: "发布成功",
                        icon: 'none',
                        duration: 2000
                    })
                    wx.navigateBack()
                }
            }
        });
    },
    // 选择时重置
    resetData: function () {
        let that = this
        let good_effects = that.data.good_effects
        let fairly_effects = that.data.fairly_effects
        let bad_effects = that.data.bad_effects
        good_effects.forEach(function (item, index) {
            item.checked = 0;
        });
        fairly_effects.forEach(item => {
            item.checked = 0;
        });
        fairly_effects.forEach(item => {
            item.checked = 0;
        });
        that.setData({
            good_effects: good_effects,
            fairly_effects: fairly_effects,
            bad_effects: bad_effects,
            check_effects: []
        })
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.getYanshou()
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
    onShareAppMessage: function () {

    }
})