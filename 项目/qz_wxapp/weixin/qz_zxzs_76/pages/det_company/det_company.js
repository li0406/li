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
    onLoad (options) {
        let that = this;
        wx.request({
            url: apiUrl+'/appletcarousel/companyDetails',
            data: { id:options.id,count:9},
            header: {
                'content-type': 'application/json'
            },
            success(res){
                res.data.details.star = options.star; 
                that.setData({ 
                    details: res.data.details,
                    caseList: res.data.cases,
                    id: options.id,
                    anlicount: options.anlicount
                });
            }
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    /**
     * 上拉加在更多案例数据
     */
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
                success (res) {
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