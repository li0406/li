import { thematicList } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
       list:[],
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        backTopValue: false,
        topNum: 0
    },
    onLoad: function () {
        // 监听页面加载的生命周期函数
        this.thematicList()
    },
    thematicList: function() {
        thematicList(
            '/bd/v1/thematic/tulist',
            {
                page: this.data.page
            }
        ).then(data => {
            if(data.error_code === 0){
                let list = this.data.list.concat(data.data.list)
                this.setData({
                    list: list,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.list.length < 50) {
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
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
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
                that.thematicList()
            })
        }
    },
    scroll: function (e) {
        let backTopValue = e.detail.scrollTop > 1200 ? true : false
        this.setData({
            backTopValue: backTopValue,
        })
    },
    backTop: function(e){
        this.setData({
            topNum: 0
        })
    },
    toZtPage: function(e) {
        let id = e.currentTarget.dataset.id
        swan.navigateTo({
            url: '../../tu/thematiclist/thematiclist?type=' + id
        });
    }
});