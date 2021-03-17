import { getWalkList } from '../../../utils/api.js'
Page({
    data: {
        pageInfo: {
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            description: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            keywords: '装修攻略,装修流程,装修风水,装修风格,局部装修'
        },
        items: [
            {
                url: 'http://staticqn.qizuang.com/custom/20190525/FhYdInR1i4qDbdaJg1C_sCvOe-Ge',
            }
        ],
        current: 0,
        switchIndicateStatus: true,
        switchAutoPlayStatus: false,
        switchDuration: 500,
        autoPlayInterval: 2000,
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nodata: "no-data",
        nomore: 2,
        page: 1,
    },
    lower(e) {
        this.getdetail()
    },
    getdetail: function () {
        var obj = this.data;
        if (obj.nomore != 2) {
            return
        }
        this.setData({
            nomore: 1
        })
        if (obj.page == 1) {
            swan.showLoading({
                title: '加载中',
            })
        }
        getWalkList(
            '/bd/v1/articlelist',
            {
                page: this.data.page,
                num: 10
            }
        ).then(data => {
            var datas = data;
            if (obj.page == 1) {
                swan.hideLoading()
                this.setData({
                    dataList: []
                })
            }
            if (datas.error_code == 0) {
                var dataList = this.data.dataList;
                dataList = dataList.concat(datas.data.list);
                dataList.forEach(item => {
                    item.url = '/pages/gonglue/detail/detail?type=' + item.short_name + '&' + 'id=' + item.id
                })
                this.setData({
                    dataList: dataList
                })
                if (datas.data.length < 10) {
                    this.setData({
                        nomore: 3
                    })
                } else {
                    this.setData({
                        nomore: 2,
                        page: this.data.page + 1
                    })
                }
            } else {
                this.setData({
                    nomore: 3
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: 'articlelist请求错误',
                    icon: 'none'
                })
            })
    },
    onLoad: function () {
        this.getdetail()
    },
    onShow() {
        swan.setPageInfo(this.data.pageInfo)
    },
    onReady: function () {
    },
    onShareAppMessage: function () {
    }
});