import { getWalkList } from '../../../utils/api.js'
const app = getApp()

Page({
    data: {
        pageInfo: {
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            description: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            keywords: '装修攻略,装修流程,装修风水,装修风格,局部装修'
        },
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        tabTxt: ['建材', '软装', '电器', '家具'],//tab文案
        tab: [true, true, true, true],
        filterjiancai: null,
        filterruanzuang: null,
        filterbiannqi: null,
        filterjiaju: null,
        type: '',
        mask: false,
        isIpx: false
    },
    // 0. 判断机型
    getSystemInfo: function () {
        let that = this
        swan.getSystemInfo({
            success: function (res) {
                if (res.model.indexOf("iPhone XR") > -1 || res.model.indexOf("iPhone X") > -1) {
                    that.setData({
                        isIpx: true
                    })
                }
            }
        });
    },
    // 1. 选项卡切换
    filterTab: function (e) {
        if (!this.mask) {
            this.setData({
                mask: true
            })
        }
        let data = [true, true, true, true],
            index = e.currentTarget.dataset.index;
        data[index] = !this.data.tab[index];
        let temp = data.every(item => {
            return item == true
        })
        if (temp) {
            this.setData({
                mask: false
            })
        } else {
            this.setData({
                mask: true
            })
        }
        this.setData({
            tab: data,
        })

    },

    // 2. 获取筛选项
    getFilter: function (category) {
        getWalkList(
            '/bd/v1/pindao/guide_category'
        ).then(res => {
            let _that = this
            this.setData({
                filterjiancai: res.data.jiancai,
                filterruanzuang: res.data.ruanzhuang,
                filterbiannqi: res.data.dianqi,
                filterjiaju: res.data.jiaju,
            }, function () {
                _that.getCategory(category)
            })
        })
    },

    // 3. 获取数据
    getCategory(type) {
        this.setData({
            tab: [true, true, true, true],
            dataList: [],
            ismask: true,
            page: 1,
            nomore: 2,
            type: type
        })
        this.getdetail(type)
    },
    getdetail: function (type) {
        let obj = this.data;
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
            '/bd/v1/guide/',
            {
                category: type,
                page: this.data.page,
            }
        ).then(data => {
            let datas = data;
            if (obj.page == 1) {
                swan.hideLoading()
                this.setData({
                    dataList: []
                })
            }
            if (datas.error_code == 0) {
                let dataList = this.data.dataList;
                let temp = [];
                dataList = dataList.concat(datas.data.list);
                dataList.forEach(item => {
                    item.views = item.pv
                    item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                    temp.push(item)
                })
                this.setData({
                    dataList: temp
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
                this.setData({
                    mask: false,
                    tab: [true, true, true, true]
                })
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
    lower(e) {
        let type = this.data.type
        let page = this.data.page
        this.getdetail(type, page)
    },

    onLoad: function (options) {
        this.getSystemInfo()
        let _that = this
        this.setData({
            short: options.category,
        })
        this.getFilter(options.category)
    },
    onShow: function () {
        swan.setPageInfo({
            title: this.data.pageInfo.title + '- 齐装网',
            keywords: this.data.pageInfo.keywords,
            description: this.data.pageInfo.description
        })
    },

});
