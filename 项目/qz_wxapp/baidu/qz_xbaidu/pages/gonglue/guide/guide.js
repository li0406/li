import { getWalkList, getGuide } from '../../../utils/api.js'
function CheckIfEven(value) {
    if (value === true) {
        return true;
    }
}
import { million } from '../../../utils/utils.js'
Page({
    data: {
        pageInfo: {
            title: '家庭装修指南_装修风水_装修风格_提供最有用的装修知识-齐装网',
            description: '齐装网装修指南为您提供最实用、最有价值的家庭装修资讯，教您整体和局部装修风格搭配，装修风水禁忌，家居生活小技巧，为业主全面系统地讲解装修知识，避免各类装修误区。',
            keywords: '家庭装修、装修风水、装修风格、装修知识'
        },
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        tabTxt: ['装修风格', '局部装修', '装修风水', '行业资讯'],//tab文案
        tab: [true, true, true, true],
        filterfengge: null,
        filterpart: null,
        filterjvbu: null,
        filterlife: null,
        actId: '',
        type: '',
        ismask: true,
        isIpx: false
    },
    // 0. 判断机型
    getSystemInfo: function () {
        swan.getSystemInfo({
            success: function (res) {
                if (res.model.indexOf("iPhone XR") > -1 || res.model.indexOf("iPhone X") > -1) {
                    this.setData({
                        isIpx: true
                    })
                }
            }
        });
    },
    // 1. 选项卡切换
    filterTab: function (e) {
        var data = [true, true, true, true], index = e.currentTarget.dataset.index;
        data[index] = !this.data.tab[index];
        if (data.every(CheckIfEven)) {
            this.setData({
                ismask: true
            })
        }
        else {
            this.setData({
                ismask: false
            })
        }
        this.setData({
            tab: data,
        })
    },
    // 2. 获取筛选项
    getFilter: function (type) {
        getGuide(
            '/bd/v1/zhinan/getnavbar',
        ).then(data => {
            var datas = data;
            let _that = this;
            if (datas.error_code == 0) {
                this.setData({
                    filterfengge: datas.data.fengge,
                    filterpart: datas.data.jubuzhuangxiu,
                    filterjvbu: datas.data.zhuangxiufengshui,
                    filterlife: datas.data.jiajushenghuo
                }, function () {
                    _that.getCategory(type)
                });
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    // 3. 获取数据
    getCategory(type) {
        let _that = this
        if (type == undefined) {
            _that.getdetail(1, '', '')
        }
        let filterData = [...this.data.filterfengge, ...this.data.filterpart, ...this.data.filterjvbu, ...this.data.filterlife]
        filterData.forEach(item => {
            if (item.shortname == type) {
                _that.setData({
                    actId: item.id,
                    dataList: [],
                    ismask: true,
                    page: 1,
                    nomore: 2,
                    type: type
                })
                _that.getdetail(1, item.id, item.shortname)
            }
        })
    },
    getdetail: function (pg, cid, type) {
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
        getGuide(
            '/bd/v1/gonglue/zhinan/',
            {
                page: pg,
                shortname: type,
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
        let pg = this.data.page
        let cid = this.data.actId
        let type = this.data.type
        this.getdetail(pg, cid, type)
    },

    closeMask() {
        this.setData({
            ismask: true,
            tab: [true, true, true, true],
        })
    },
    onLoad: function (options) {
        this.getSystemInfo()
        let _that = this
        this.getFilter(options.category)
    },
    onShow: function () {
        swan.setPageInfo({
            title: this.data.pageInfo.title + '- 齐装网',
            keywords: this.data.pageInfo.keywords,
            description: this.data.pageInfo.description
        })
    }
});