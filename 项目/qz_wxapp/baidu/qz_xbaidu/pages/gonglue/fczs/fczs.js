import { getWalkList } from '../../../utils/api.js'
import { million } from '../../../utils/utils.js'
Page({
    data: {
        pageInfo: {
            title: '房地产专业实用知识大全',
            description: '',
            keywords: ''
        },
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
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
            '/bd/v1/gonglue/fczs',
            {
                page: this.data.page,
                limit: 10
            }
        ).then(data => {
            var datas = data;
            let _that = this;
            if (obj.page == 1) {
                swan.hideLoading()
                this.setData({
                    dataList: []
                })
            }
            if (datas.error_code == 0) {
                var dataList = this.data.dataList;
                dataList = dataList.concat(datas.data.list);
                const tipdata = []
                dataList.forEach((item, index) => {
                    item.views = item.pv
                    item.likes = million(item.likes)
                    item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                    tipdata.push(item)
                })
                this.setData({
                    dataList: tipdata
                })

                let tempTitle = 'pageInfo.title'
                let tempKeywords = 'pageInfo.keywords'
                let tempDes = 'pageInfo.description'
                this.setData({
                    [tempTitle]: data.data.tdk.title,
                    [tempKeywords]: data.data.tdk.keywords,
                    [tempDes]: data.data.tdk.description
                })
                swan.setPageInfo({
                    title: data.data.tdk.title + '-齐装网',
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description
                })

                if (datas.data.list.length < 10) {
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
                swan.showToast({
                    title: datas.error_msg,
                    icon: 'none',
                    mask: false,
                });
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    lower(e) {
        this.getdetail()
    },
    onLoad: function () {
        this.getdetail()
    },
    onShow: function () {
        swan.setPageInfo({
            title: this.data.pageInfo.title + '- 齐装网',
            keywords: this.data.pageInfo.keywords,
            description: this.data.pageInfo.description
        })
    }
});