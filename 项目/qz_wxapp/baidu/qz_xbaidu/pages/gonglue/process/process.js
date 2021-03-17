import { getWalkList, getProcess } from '../../../utils/api.js'
import { million } from '../../../utils/utils.js'
const app = getApp()
Page({
    data: {
        parms: '',
        pageInfo: {
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            description: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            keywords: '装修攻略,装修流程,装修风水,装修风格,局部装修'
        },
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        // 上方滑动
        classify: [],
        currentlevelOneIndex: 0, // 用于控制一级分类选中状态
        scrollLeft: "", // 滚动选项横向定位
        leftView: "", // 定位的id
        inputValue: '',
        isIpx: false
    },
    setTab: function (e, options) {
        let category = e ? e.currentTarget.dataset.category : options.category;
        this.setData({
            leftView: category,
            nomore: 2
        });
        swan.pageScrollTo({
            scrollTop: 0
        })
        this.getContentList(e);
    },
    //滚动到底部100px时触发
    lower(e) {
        this.getContentList()
    },
    // 获取顶部导航分类
    getCategory: function (options) {
        let me = this
        getProcess(
            '/bd/v1/process/category',
        ).then(data => {
            var datas = data;
            if (datas.error_code == 0) {
                datas.data.forEach(item => {
                    item.text = item.classname
                })
                this.setData({
                    classify: datas.data
                }, function () {
                    me.setTab(null, options);
                });
            } else {
            }
        })
    },
    // 获取攻略列表
    getContentList: function (e) {
        let obj = this.data,
            leftView = obj.leftView || 'shoufang';
        if (e) {
            if (e.currentTarget.dataset.category) {
                leftView = e.currentTarget.dataset.category;
                this.setData({
                    page: 1,
                    dataList: []
                })
            }
        }
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
        getProcess(
            '/bd/v1/process/list',
            {
                short: leftView,
                page: obj.page,
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
                    description: data.data.tdk.description,
                    image: []
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
                this.setData({
                    nomore: 3
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '请求错误',
                    icon: 'none'
                })
            })
    },
    onLoad: function (options) {
        this.getCategory(options)
        this.setData({
            isPhonex: app.globalData.isIphoneX ? "isPhonex" : '',
            isIpx: app.globalData.isIphoneX
        })
    },
    onShow: function (options) {
        swan.setPageInfo({
            title: '齐装网',
            keywords: '齐装，齐装网，装修，装修网，装修公司',
            description: '资深家居装饰装修平台，装修难题我帮你。',
            image: []
        })
    }
});
