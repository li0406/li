// pages/user_set/user_set.js
Page({

    /**
     * 页面的初始数据
     */
    data: {
        size:'0'
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        wx.getStorageInfo({
            success: function (res) {
                that.setData({ size: Math.floor(res.currentSize / 1024*100)/100+' M'})
            }
        });
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

    },
    clear:function(){
        let that = this;
        wx.showToast({
            title: '清除成功',
            icon: 'success',
            duration: 1000
        })
        try {
            wx.clearStorageSync();
            wx.getStorageInfo({
                success: function (res) {
                    that.setData({ size: Math.floor(res.currentSize / 1024 * 100) / 100 + ' M' })
                }
            });
        } catch (e) {
            // Do something when catch error
        }
    }
})