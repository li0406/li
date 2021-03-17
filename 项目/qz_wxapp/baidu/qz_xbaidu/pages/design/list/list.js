const app = getApp()
import { getDesignList } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        deList:[],
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        page: 1,
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        let id = options.id
        // let id = 91587
        this.setData({
            id: id
        })
        this.designer(id)
    },
    designer(id){
        let that = this
        canUseReachBottom = false;
        getDesignList(
            '/bd/v1/designer/list',
            {
                company_id: id,
                page: that.data.page,
                limit: 10
            }
        ).then(data => {
            let datas = data;
            if (datas.error_code == 0) {
                let designerList = that.data.deList.concat(datas.data.list)
                that.setData({
                   deList: designerList,
                   page_total_number: datas.data.page.page_total_number,
                   pageCurrent: datas.data.page.page_current
                })
                swan.setPageInfo({
                    title: datas.data.tdk.title,
                    keywords: datas.data.tdk.keywords,
                    description:datas.data.tdk.description,
                    image: [],
                    success: res => {
                        console.log('setPageInfo success', res);
                    },
                    fail: err => {
                        console.log('setPageInfo fail', err);
                    }
                })
                if(datas.data.page.page_total_number>0){
                    if (data.data.list.length <10) {
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
                    title: datas.error_msg,
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
        if (pageCurrent < page_total_number) {
            that.setData({
                page: pageCurrent + 1
            }, function () {
                that.designer(that.data.id)
            })
        }
    },
    jumpdetail(e){
        let userid=e.currentTarget.dataset.userid
        let bm=e.currentTarget.dataset.bm
        let type =e.currentTarget.dataset.type
        if(type == 2){
            swan.navigateTo({
                url: `../../design/detail_new/detail_new?cs=${bm}&id=${userid}`
            });
        } else if(type == 1) {
            swan.navigateTo({
                url: `../../design/detail/detail?cs=${bm}&id=${userid}`
            });
        }
    },
    // 点击封面跳转
    goimg(e){
        let bm = e.currentTarget.dataset.bm
        let alid = e.currentTarget.dataset.alid
        swan.navigateTo({
            url: `../../caseinfo/details/details?cs=${bm}&id=${alid}`
        });
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