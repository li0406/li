import { thematicListData } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        list:[],
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        backTopValue: false,
        topNum: 0,
        id:'',
        tuDataWn:false
    },
    thematicListData: function(id) {
        thematicListData(
            '/bd/v1/thematic/tuitem',
            {
                id:id,
                page: this.data.page
            }
        ).then(data => {
            if(data.error_code === 0){
                let list = this.data.list.concat(data.data.list)
                this.setData({
                    list:list,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                if(this.data.page_total_number===0 && this.data.list==''){
                    this.setData({
                        tuDataWn: true
                    })
                }
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.list.length < 20) {
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
                that.thematicListData(that.data.id)
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
    godetail(e){
        let flags=e.currentTarget.dataset.flag
        swan.navigateTo({
            url: `../../tu/detail/detail?id=${flags}`
        });
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        let id=options.type
        this.setData({
            id:options.type
        })
        this.thematicListData(id)
    }
});