// pages/zxgonglue_list/zxgonglue_list.js
const app = getApp();
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        oImgUrl: oImgUrl,
        articleList: [],
        urlstr: '',
        articleCount: '10',
        userId: ''
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad(options) {
        let that = this,
            userId = '';
        wx.setNavigationBarTitle({
            title: options.urlstrname
        });
        app.getUserInfo((res) => { that.setData({ userId: res.userId }); })
        that.setData({ urlstr: options.urlstr })
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady() {
    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow() {
        let that = this;
        app.getUserInfo((res) => {
            that.setData({ userId: res.userId });
            wx.request({
                url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + res.userId,
                data: { count: '10' },
                header: {
                    'content-type': 'application/json'
                },
                success(res) {
                    console.log(res)
                    that.setData({ articleList: res.data })
                }
            });
        });
    },
    /**
     * 上拉加载数据
     */
    lower() {
        let that = this,
            count = parseInt(that.data.articleCount);
        count += 10;
        wx.request({
            url: apiUrl + '/gonglue/' + that.data.urlstr + '&userid=' + that.data.userId,
            data: { count: count },
            header: {
                'content-type': 'application/json'
            },
            success(res) {
                wx.showLoading({
                    title: '数据加载中',
                });
                setTimeout(function () {
                    wx.hideLoading()
                }, 1200);
                that.setData({ articleList: res.data, articleCount: count });
            }
        });
    },
    /**
     * 跳转到详情页面
     */
    toArticle(e) {
        let id = e.currentTarget.dataset.id;
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id=' + id
        })
    }
});