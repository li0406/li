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
    onLoad (options) {
        let that = this;
        wx.getStorageInfo({
            success(res) {
                that.setData({ size: Math.floor(res.currentSize / 1024*100)/100+' M'})
            }
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    /**
     * 清除缓存
     */
    clear(){
        let that = this;
        wx.showToast({
            title: '清除成功',
            icon: 'success',
            duration: 1000
        })
        try {
            wx.clearStorageSync();
            wx.getStorageInfo({
                success (res) {
                    that.setData({ size: Math.floor(res.currentSize / 1024 * 100) / 100 + ' M' })
                }
            });
        } catch (e) {
            
        }
    }
})