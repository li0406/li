import { getXgt, getXgtList } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        switchIndicateStatus: true,
        switchAutoPlayStatus: true,
        switchDuration: 500,
        autoPlayInterval: 5000,
        tags: [
            {
                index: 1,
                name: '空间'
            },
            {
                index: 2,
                name: '风格'
            },
            {
                index: 3,
                name: '局部'
            },
            {
                index: 4,
                name: '户型'
            },
            {
                index: 5,
                name: '公装'
            }
        ],
        currentIndex: 1,
        fenList: [],
        bannerList: [],
        home_categorys: [],
        pub_categorys: [],
        xgtList: [],
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        backTopValue: false,
        topNum: 0
    },
    onLoad: function () {
        // 监听页面加载的生命周期函数
        this.getXgtInfo()
        this.getXgtList()
        this.removeSkeleton()
    },
    changeTab: function(e) {
        let index  =  e.currentTarget.dataset.index
        this.setData({
            currentIndex: index+1
        })
        if(index != 4){
            this.setData({
                fenList: this.data.home_categorys[index].children
            })
        } else {
            this.setData({
                fenList: this.data.pub_categorys
            })
        }
    },
    getXgtInfo: function(){
        getXgt('/bd/v1/meitu/home/options',{}).then(data => {
            if(data.error_code === 0){
                this.setData({
                    bannerList: data.data.banners,
                    home_categorys: data.data.home_categorys,
                    pub_categorys: data.data.pub_categorys,
                    fenList: data.data.home_categorys[0].children
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
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    getXgtList: function() {
        canUseReachBottom = false;
        getXgtList(
            '/bd/v1/meitu/home/list',
            {
                page: this.data.page
            }
        ).then(data => {
            if(data.error_code === 0){
                let datas = data.data.list
                let list = this.data.xgtList.concat(datas)
                this.setData({
                    xgtList: list,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.list.length < 10) {
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
    toScore: function(e) {
        let url = e.currentTarget.dataset.url
        let id = e.currentTarget.dataset.id
        if(id == 3 || id == 4){
            swan.switchTab({
                url: '../../' + url
            })
        } else {
           swan.navigateTo({
                url: '../../' + url
            })
        }

    },
    toXgt: function(e) {
        let url_full = e.currentTarget.dataset.url_full
        let index = e.currentTarget.dataset.index
        swan.navigateTo({
            url: '../../tu/tu/tu?type=' + url_full
        });
    },
    toTuDetail: function(e) {
        let id = e.currentTarget.dataset.detail_flag
        swan.navigateTo({
            url: '../../tu/detail/detail?id=' + id
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
                that.getXgtList()
            })
        }
    },
    scroll: function(e) {
        let backTopValue = e.detail.scrollTop > 1200 ? true : false
        this.setData({
            backTopValue: backTopValue
        })
    },
    backTop: function(e){
        this.setData({
            topNum: 0
        })
    }
});