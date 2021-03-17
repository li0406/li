import { getDesignerWorks } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        company_id: '',
        userid: '',
        workList: [],
        fengge_option: [],
        leftView: '',
        fengge_id: '',
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2
    },
    // 监听页面加载的生命周期函数
    onLoad: function (options) {
        let id = options.index || 0
        this.setData({
            company_id: options.id,
            fengge_id: options.index
        })
        if(options.userid) {
            this.setData({
                userid: options.userid
                //userid: 46333
            })
        }
        this.getDesignerWorks(id)
    },
    refer: function (e) {
        let that = this
        let index = e.currentTarget.dataset.index
        swan.redirectTo({
            url: "../../company/xgt/xgt?id=" + that.data.company_id + "&index=" + index
        })
    },
    setTab: function (e, index) {
        let id = index < 10 ? index : 1;
        this.setData({
            leftView: index,
            fengge_id: index,
            scrollLeft: (id-1) * 52
        });
    },
    getDesignerWorks: function(id){
        let that = this
        canUseReachBottom = false;
        if (that.data.nomore != 2) {
            return
        }
        that.setData({
            nomore: 1
        })
        if (that.data.page == 1) {
            swan.showLoading({
                title: '加载中',
            })
        }
        getDesignerWorks(
            '/bd/v1/case/list',
            {
                company_id: that.data.company_id,
                userid: that.data.userid,
                fengge_id: id,
                page: that.data.page,
                limit: 10
            }
        ).then(data => {
            if (that.data.page == 1) {
                swan.hideLoading()
                that.setData({
                    workList: []
                })
            }
            if(data.error_code === 0){
                let workList = that.data.workList.concat(data.data.list)
                that.setData({
                    workList: workList,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                that.setData({
                    fengge_option: data.data.fengge_option
                },function(){
                    that.setTab(null, id);
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.list.length < 10) {
                        that.setData({
                            nomore: 3
                        })
                    } else {
                        that.setData({
                            nomore: 2,
                            page: that.data.page + 1
                        })
                    }
                } else {
                    that.setData({
                        nomore: 2
                    })
                }
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                canUseReachBottom = true;
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    lower() {
        if (!canUseReachBottom) return;
        let fengge_id = this.data.fengge_id
        this.getDesignerWorks(fengge_id)
    },
    toDetail: function(e){
        let id = e.currentTarget.dataset.id
        let cs = e.currentTarget.dataset.cs
        swan.navigateTo({
            url: '../../caseinfo/details/details?cs='+ cs +'&id=' + id
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