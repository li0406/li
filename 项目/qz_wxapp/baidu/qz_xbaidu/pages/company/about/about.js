import { getComAbout } from '../../../utils/api.js'

Page({
    data: {
        id: '',
        info: '',
        imgList: [],
        company_tag: []
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        let id = options.id
        //let id = 91587
        this.setData({
            id: id
        })
        this.getComAbout()
    },
    viewImage: function(e) {
        let currentSrc = e.currentTarget.dataset.src
        let urls = e.currentTarget.dataset.images
        swan.previewImage({
            current: currentSrc,
            urls: urls
        });
    },
    getComAbout: function() {
        getComAbout(
            '/bd/v1/company/about',
            {
                company_id: this.data.id
            }
        ).then(data => {
            if(data.error_code === 0) {
                let imgs = data.data.honor_list.map(item => {
                    return item.img_full
                })
                let tags = data.data.company_tag.map(item => {
                    return item.tag
                })
                this.setData({
                    info: data.data.user,
                    imgList: imgs,
                    company_tag: tags.join('、')
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    articleTitle: 'xxx',
                    image: [],
                    success: res => {
                        console.log('setPageInfo success', res);
                    },
                    fail: err => {
                        console.log('setPageInfo fail', err);
                    }
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function() {
        // 监听页面显示的生命周期函数
    },
    onHide: function() {
        // 监听页面隐藏的生命周期函数
    },
    onUnload: function() {
        // 监听页面卸载的生命周期函数
    },
    onPullDownRefresh: function() {
        // 监听用户下拉动作
    },
    onReachBottom: function() {
        // 页面上拉触底事件的处理函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});