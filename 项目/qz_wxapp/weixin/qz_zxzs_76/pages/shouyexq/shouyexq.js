// pages/shouyexq/shouyexq.js
const app = getApp(),
    apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
var WxParse = require('../../wxParse/wxParse.js');
function getLocalTime(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
Page({

    /**
     * 页面的初始数据
     */
    data: {
        imgUrl: oImgUrl,
        userInfo: {},
        hasUserInfo: false,
        canIUse: wx.canIUse('button.open-type.getUserInfo'),
        dianji: false,
        details: {},
        articleList: [],
        dianzansl: 766,
        userId: '',
        mark: true,
        zan: true
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad(options) {
        let _this = this;
        app.getUserInfo((res) => {
            _this.setData({ userId: res.userId })
        });
        wx.request({
            url: apiUrl + '/appletcarousel/details',
            data: {
                userid: _this.data.userId,
                id: options.id,
                classtype: '10'
            },
            header: {
                'content-type': 'application/json'
            },
            success(res) {
                
                res.data.article.addtime = getLocalTime(res.data.article.addtime);
                let content = res.data.article.content,
                    a1 = '点此获取专业设计师免费量房设计';
                if (content.indexOf(a1) > 0) {
                    content = content.split(a1)[0];
                }
                WxParse.wxParse('article', 'html', content, _this);
                _this.setData({ details: res.data.article });
                wx.setNavigationBarTitle({
                    title: res.data.article.title
                });
                let user = app.getNewStorage('user')||[];
                for (let i = 0; i < user.length; i++) {
                    if (user[i] == res.data.article.id) {
                        _this.setData({ zan: false });
                    }
                }
                
            }
        });
        wx.request({
            url: apiUrl + '/appletcarousel/detailsRecommend?id=' + options.id,
            data: { order: 'realview', count: '9' },
            header: {
                'content-type': 'application/json'
            },
            success(res) {
                _this.setData({ articleList: res.data });
            }
        });
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage() {

    },
    /**
     * 点赞操作
     */
    dianjizan() {
        let that = this;
        let user = app.getNewStorage('user')||[];
        let details = that.data.details;
        let bool = true;
        if (that.data.zan) {
            wx.request({
                url: apiUrl + '/appletcarousel/like',
                data: {
                    id: details.id
                },
                header: {
                    'content-type': 'application/json'
                },
                success(res) {
                    if (res.data.state === 1) {
                        details.likes = parseInt(details.likes) + 1;
                        that.setData({ details: details, zan: false });
                        user.push(details.id)
                        app.setNewStorage('user', user);
                    }
                }
            });
        } else {
            wx.showModal({
                title: '您已经点赞了',
                showCancel: false,
                success(res) { }
            });
        }
    },
    /**
     * 跳转到文章详情页面
     */
    articleDet(e) {
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id=' + id
        })
    },
    /**
     * 跳转到装修报价页面
     */
    toBaojia() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj',
        })
    },
    /**
     * 跳转到装修设计页面
     */
    zxsjym1() {
        wx.navigateTo({
            url: '../zhuangxiusj/zhuangxiusj'
        })
    }
})