// pages/det_company/det_company.js
const app = getApp(),
      apiUrl = app.getApiUrl(),
      oImgUrl = app.getImgUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: oImgUrl,
        details:{},
        caseList:[],
        count:9,
        anlicount:'',
        id:'',
        loadBool:true
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        wx.request({
            url: apiUrl+'/appletcarousel/companyDetails',
            data: { id:options.id,count:9},
            header: {
                'content-type': 'application/json'
            },
            success:function(res){
                console.log(res.data);
                res.data.details.star = options.star; 
                that.setData({ details: res.data.details, caseList: res.data.cases, id: options.id, anlicount: options.anlicount})
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
    downLoad(){
        let that = this,
            count = that.data.count;
        if (that.data.loadBool){
            wx.showToast({
                title: '数据加载中...',
                icon: 'loading'
            });
            let len = that.data.caseList.length;
            count += 5;
            wx.request({
                url: apiUrl + '/appletcarousel/companyDetails',
                data: { id: that.data.id, count: count },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    
                    if (len == res.data.cases.length){
                        that.setData({loadBool:false})
                    }else{
                        that.setData({ caseList: res.data.cases, count: count, loadBool: true })
                    }
                }
            });
        }else{
            wx.showToast({
                title: '没有更多了',
                icon: 'success'
            });
        }
        
    }
})