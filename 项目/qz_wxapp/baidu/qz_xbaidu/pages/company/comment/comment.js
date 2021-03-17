import { getComment, getCContent } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        id: '',
        info: '',
        step_list: [],
        recommend_show: '',
        classActive: true,
        currentIndex: 0,
        leftView: '',
        page: 1,
        comment: [],
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        sj_arr: [],
        fw_arr: [],
        zl_arr: []
    },
    getBigImage: function(e) {
        let src = e.currentTarget.dataset.src
        let urls = e.currentTarget.dataset.urls
        swan.previewImage({
            current: src,
            urls: urls
        });
    },
    getComment: function() {
        getComment(
            '/bd/v1/comment/options',
            {
                company_id: this.data.id
            }
        ).then(data => {
            if(data.error_code === 0) {
                let sj_arr = this.starNum(data.data.comment.star_sj)
                let fw_arr = this.starNum(data.data.comment.star_fw)
                let zl_arr = this.starNum(data.data.comment.star_sg)
                this.setData({
                    info: data.data.comment,
                    step_list: data.data.step_list,
                    recommend_show: data.data.recommend_show,
                    sj_arr,
                    fw_arr,
                    zl_arr
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
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
    starNum: function(num){
        let arr = []
        for(var i = 0 ; i < 5; i++) {
            if (i < num){
                arr.push('/images/stars.png')
            } else {
                arr.push('/images/no-star.png')
            }
        }
        return arr
    },
    getCContent: function(step) {
        canUseReachBottom = false;
        getCContent(
            '/bd/v1/comment/list',
            {
                company_id: this.data.id,
                step: step,
                recommend: this.data.currentIndex == 1 ? 1 : '',
                limit: 20,
                page: this.data.page
            }
        ).then(data => {
            if (data.error_code === 0){
                let comment = this.data.comment.concat(data.data.list)
                this.setData({
                    comment: comment,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.list < 10) {
                        this.setData({
                            nomore: 3
                        })
                    } else {
                        this.setData({
                            nomore: 2
                        })
                    }
                } else {
                    this.setData({
                        nomore: 2
                    })
                }
                canUseReachBottom = true;
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    refer: function (e) {
        let label = e.currentTarget.dataset.label
        let index = e.currentTarget.dataset.index
        this.setData({
            comment: [],
            page: 1
        })
        if(index !== undefined && index !== '') {
            this.setData({
                currentIndex: index
            })
        } else {
            this.setData({
                currentIndex: 3
            })
        }
        if(label !== undefined && label !== ''){
            this.setData({
                leftView: label
            })
        } else {
            this.setData({
                leftView: ''
            })
        }
        this.getCContent(label)
    },
    lower() {
        if (!canUseReachBottom) return;
        let that = this
        let pageCurrent = that.data.pageCurrent
        let page_total_number = that.data.page_total_number
        if (pageCurrent <= page_total_number) {
            that.setData({
                page: pageCurrent + 1
            }, function () {
                that.getCContent(that.data.leftView)
            })
        }
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        let id = options.id
        //let id = 91587
        this.setData({
            id: id
        })
        this.getComment()
        this.getCContent('')
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