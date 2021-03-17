var app = getApp();
let apiUrl = app.getApiUrl();
Page({
    /**
     * 页面的初始数据
     */
    data: {
        circular: true,
        Idex: 1,
        shujv: null,
        changdu: null,
        biaoti:''
    },
    EventHandle (event) {
        var count = event.detail.current;
        this.data.Idex = count + 1;
        this.data.biaoti = this.data.shujv[count].title;
        this.setData({ Idex: this.data.Idex });
        this.setData({biaoti:this.data.biaoti});
        wx.setNavigationBarTitle({ title: this.data.biaoti })
    },
    /*
     * 生命周期函数--监听页面加载
     */
    onLoad (options) {
        var self = this;
        wx.request({
            url: apiUrl+'/appletcarousel/meituDetails',
            data: {
                id: options.id
            },
            header: {
                'Content-Type': 'application/json'
            },
            success (res) {
                self.setData({
                    shujv: res.data.info,
                    changdu: res.data.info.length,
                    biaoti: res.data.info[0].title
                })
                wx.setNavigationBarTitle({ title: self.data.biaoti })
            }
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage () {

    },
    zxbjtiaoz(){
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    }
})