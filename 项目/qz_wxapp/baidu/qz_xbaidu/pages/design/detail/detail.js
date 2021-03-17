import { getDesignerDetail, getDesignerWorks } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        classActive: true,
        tabs: [{
            index: 1,
            label: '个人介绍',
        }, {
            index: 0,
            label: '设计作品',
        }],
        id: '',
        userid: '',
        currentIndex: 1,
        workList: [],
        personInfo: '',
        scrollTop: 0,
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.setData({
            userid: options.id || 124633
        })
        this.getDesignerDetail()
    },
    changeTab(e) {
        this.setData({
            currentIndex: e.currentTarget.dataset.index
        })
    },
    getDesignerDetail: function() {
        getDesignerDetail(
            '/bd/v1/designer/old_design_details',
            {
                id: this.data.userid
            }
        ).then(data => {
            if(data.error_code === 0){
                this.setData({
                    personInfo: data.data.info,
                    id: data.data.info.company_id
                })
                this.getDesignerWorks(data.data.info.company_id)
                if (data.data.info.case_count > 0){
                    this.setData({
                        ['tabs[1].label']: '设计作品（' + data.data.info.case_count + '）'
                    })
                } else {
                    this.setData({
                        ['tabs[1].label']: '设计作品'
                    })
                }
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
            } else if(data.error_code === 4000001){
                swan.navigateTo({
                    url: '../../gonglue/detail/detail'
                });
            }else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    getDesignerWorks: function(id){
        canUseReachBottom = false;
        getDesignerWorks(
            '/bd/v1/case/list',
            {
                company_id: id,
                userid: this.data.userid,
                page: this.data.page,
                limit: 20
            }
        ).then(data => {
            if(data.error_code === 0){
                let workList = this.data.workList.concat(data.data.list)
                this.setData({
                    workList: workList,
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
                that.getDesignerWorks(that.data.id)
            })
        }
    },
    scroll: function(e){
        this.setData({
            scrollTop: e.detail.scrollTop
        })
    },
    toDetail: function(e){
        let cs = e.currentTarget.dataset.bm
        let id = e.currentTarget.dataset.id
        swan.navigateTo({
            url: '../../caseinfo/details/details?cs=' + cs + '&id=' + id
        });
    }
});