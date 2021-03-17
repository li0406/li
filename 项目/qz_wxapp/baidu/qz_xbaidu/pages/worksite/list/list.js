const app = getApp()
import { getWorkSite } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        bottomIcon: true,
        workSiteList: [],
        company_id: '',
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.setData({
            company_id: options.id
        })
        this.workSiteList()
    },
    onTap: function(e) {
        let media_list = e.currentTarget.dataset.media_list
        let url_arry = media_list.map((item) => {
            return item.url_full
        })
        swan.previewImage({
            current:e.currentTarget.dataset.src,
            urls: url_arry
        });
    },
    toDetail: function(e){
        let id = e.currentTarget.dataset.id
        let step = e.currentTarget.dataset.step
        swan.navigateTo({
            url: '../detail/detail?id=' + id + '&step=' + step
        });
    },
    toCompany: function(){
        swan.navigateTo({
            url: '../../company/details/details?id=' + this.data.company_id
        });
    },
    workSiteList:function(){
        canUseReachBottom = false;
        getWorkSite(
            '/bd/v1/worksite',
            {
                company_id: this.data.company_id,
                page: this.data.page,
                limit: 10
            }
        ).then(data => {
            if(data.error_code === 0){
                let datas = data.data.info
                let list = this.data.workSiteList.concat(datas)
                datas.forEach((item) => {
                    if(item.media_list && item.media_list.length >= 3) {
                        item.urlImages = item.media_list
                        item.media_list = item.media_list.slice(0,3)
                    } else {
                        item.urlImages = item.media_list
                    }
                });
                this.setData({
                    workSiteList: list,
                    pageCurrent: data.data.page.page_current,
                    page_total_number: data.data.page.page_total_number
                })
                swan.setPageInfo({
                    title: this.data.workSiteList[0].jc + '装修现场_'+ this.data.workSiteList[0].jc +'施工工地-'+ this.data.workSiteList[0].cname +'齐装网',
                    keywords: this.data.workSiteList[0].jc + '装修现场,'+ this.data.workSiteList[0].jc +'施工工地',
                    description: '齐装网为广大业主提供'+ this.data.workSiteList[0].jc +'装修现场,施工工地图片做参考!让您了解'+ this.data.workSiteList[0].jc +'施工现场管理制度,室内装修方案,工程施工安全检查等信息.',
                    image: [],
                    success: res => {
                        console.log('setPageInfo success', res);
                    },
                    fail: err => {
                        console.log('setPageInfo fail', err);
                    }
                })
                if(data.data.page.page_total_number>0){
                    if (data.data.info.length < 10) {
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
                that.workSiteList()
            })
        }
    }
});