import { getProcess } from '../../../utils/api.js'
const app = getApp()
Page({
    data: {
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2,
        page: 1,
        classify: [],
        leftView: "",
        isIpx: false,
        xzcity: '苏州',
        cityName: '苏州',
        cs: 'sz',
        cid: 320500,
        isPhonex: ''
    },
    setTab: function (e, options) {
        let shortname = options.shortname,
            id = options.id < 8 ? options.id : 9;
        this.setData({
            leftView: shortname,
            nomore: 2,
            scrollLeft: id * 52
        });
        this.getContentList()
    },
    lower(e) {
        this.getContentList()
    },
    getCategory: function (options) {
        let me = this
        getProcess(
            '/bd/v1/zixun/getcategorylist',
        ).then(data => {
            var datas = data;
            if (datas.error_code == 0) {
                me.setData({
                    classify: datas.data
                }, function () {
                    me.setTab(null, options);
                });
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    getContentList: function (e) {
        let that = this
        let obj = that.data;
        if (obj.nomore != 2) {
            return
        }
        that.setData({
            nomore: 1
        })
        if (obj.page == 1) {
            swan.showLoading({
                title: '加载中',
            })
        }
        getProcess(
            '/bd/v1/zixun/getarticlelist',
            {
                cid: obj.cid ? obj.cid : 320500,
                shortname: obj.leftView,
                page: obj.page,
            }
        ).then(data => {
            var datas = data;
            if (obj.page == 1) {
                swan.hideLoading()
                that.setData({
                    dataList: []
                })
            }
            if (datas.error_code == 0) {
                var dataList = that.data.dataList;
                dataList = dataList.concat(datas.data.list);
                const tipdata = []
                dataList.forEach((item, index) => {
                    item.views = item.pv
                    item.url = '/pages/zxinfo/detail/detail?cs=' + item.cs + '&id=' + item.id
                    tipdata.push(item)
                })
                that.setData({
                    dataList: tipdata
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                if (datas.data.list.length < 10) {
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
                    nomore: 3
                })
            }
        })
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    closeSelect: function (res) {
        let that = this
        that.setData({
            isHide: true
        })
        if (res.detail[3] == 1) {
            swan.setNavigationBarTitle({
                title: res.detail[2] + '装修资讯'
            })
            that.setData({
                dataList: [],
                nomore: 2,
                page: 1,
                cid: res.detail[0],
                leftView: '',
                shortname: '',
                page: 1,
                scrollLeft: 0,
                cityName: res.detail[2],
                cs: res.detail[4]
            })
            let options = {
                cid: res.detail[0],
                cityName: res.detail[2],
                cs: res.detail[4]
            }
            swan.setStorageSync('xoptions', options);
            that.getContentList()
        }
    },
    refer: function (e) {
        swan.setStorageSync('xoptions', e.currentTarget.dataset);
        let shortname = e.currentTarget.dataset.shortname,
            cs = e.currentTarget.dataset.cs
        swan.redirectTo({
            url: "../../zxinfo/zxinfo/zxinfo?shortname=" + shortname + "&cs="+cs
        });
    },
    onLoad: function () {
        let xoptions = swan.getStorageSync('xoptions');
        this.setData({
            isPhonex: app.globalData.isIphoneX ? "isPhonex" : '',
            isIpx: app.globalData.isIphoneX,
            cid: xoptions.cid,
            cityName: xoptions.cityName,
            cs: xoptions.cs
        })
        swan.setNavigationBarTitle({
            title: xoptions.cityName ? xoptions.cityName + '装修资讯' : '苏州' + '装修资讯'
        })
        this.getCategory(xoptions)
    }
});
