// pages/comment/comment.js
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        comment_content:'',
        token:'',
        id:''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this
        let token = wx.getStorageSync('jwt_token')
        let id = options.id
        that.setData({
            id:id,
            token:token
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

    },
    inputValue:function(e){
        let that = this
        that.setData({
            comment_content:e.detail.value
        })
    },
    release:function(){
        let that = this
        let value = that.data.comment_content
        let token = that.data.token
        let id = that.data.id
        if (value) {
            wx.request({
                url: zxsApiUrl + '/business/worksite/user/info_comment',
                method: 'POST',
                data: {
                    info_id: id,
                    comment_content: value
                },
                header: {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    if (res.data.error_code == 0) {
                        wx.showToast({
                            title: '发布成功',
                            icon: 'none',
                            duration: 1000
                        })
                        wx.navigateBack()
                    }
                },
                complete: function (res) {
                }

            });
        }
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