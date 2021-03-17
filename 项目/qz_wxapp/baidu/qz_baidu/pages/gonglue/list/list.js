import { getIndex } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
const app = getApp()
Page({
    data: {
        scrollHeight: '',
        userInfo: {},
        hasUserInfo: false,
        pageInfo: {
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            description: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            keywords: '装修攻略,装修流程,装修风水,装修风格,局部装修'
        },
        images: [],
        current: 0,
        switchIndicateStatus: true,
        switchAutoPlayStatus: true,
        switchDuration: 500,
        autoPlayInterval: 5000,
        // 装修流程
        tabChange: [true, false, false],
        currentTab: '',
        switchIndicateStatusdur: false,
        switchAutoPlayStatusdur: false,
        switchDurationdur: 0,
        autoPlayIntervaldur: 5000,
        toView: '',
        hasSCroll: false,
        scrollTab: [[
            {
                name: "验房",
                imgSrc: "../../../images/zu_1.png",
                href: "../../gonglue/process/process?category=shoufang"
            }, {
                name: "选公司",
                imgSrc: "../../../images/zu_2.png",
                href: "../../gonglue/process/process?category=gongsi"
            },
            {
                name: "量房",
                imgSrc: "../../../images/zu_3.png",
                href: "../../gonglue/process/process?category=liangfang"
            },
            {
                name: "设计预算",
                imgSrc: "../../../images/zu_4.png",
                href: "../../gonglue/process/process?category=shejiyusuan"
            }], [
            {
                name: "选材",
                imgSrc: "../../../images/sg_1.png",
                href: "../../gonglue/process/process?category=xuancai"
            },
            {
                name: "拆改",
                imgSrc: "../../../images/sg_2.png",
                href: "../../gonglue/process/process?category=chagai"
            },
            {
                name: "水电",
                imgSrc: "../../../images/sg_3.png",
                href: "../../gonglue/process/process?category=shuidian"
            },
            {
                name: "防水",
                imgSrc: "../../../images/sg_4.png",
                href: "../../gonglue/process/process?category=fangshui"
            },
            {
                name: "泥瓦",
                imgSrc: "../../../images/sg_5.png",
                href: "../../gonglue/process/process?category=niwa"
            },
            {
                name: "木工",
                imgSrc: "../../../images/sg_6.png",
                href: "../../gonglue/process/process?category=mugong"
            },
            {
                name: "油漆",
                imgSrc: "../../../images/sg_7.png",
                href: "../../gonglue/process/process?category=youqi"
            }, {}
        ], [
            {
                name: "验收",
                imgSrc: "../../../images/rz_1.png",
                href: "../../gonglue/process/process?category=jianche"
            },
            {
                name: "保养",
                imgSrc: "../../../images/rz_2.png",
                href: "../../gonglue/process/process?category=baoyang"
            }, {
                name: "配饰",
                imgSrc: "../../../images/rz_3.png",
                href: "../../gonglue/process/process?category=peishi"
            }, {
                name: "家居",
                imgSrc: "../../../images/rz_4.png",
                href: "../../gonglue/process/process?category=jjsh"
            }
        ]],
        autoHeight: false,
        //  装修知识
        currentGoog: '',
        autoGoog: false,
        tipList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        guideList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        baikeList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        wendaList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        strategy: [],
        baike: [],
        question: [],
        isHas: true
    },
    // 轮播跳转
    banner: function () {
        getIndex(
            '/bd/v1/pindao/banner',
        ).then(data => {
            if (data.error_code == 0) {
                this.setData({
                    images: data.data
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: 'banner请求失败',
                    icon: 'none'
                })
            })
    },
    toSwiper: function (e) {
        let url = e.currentTarget.dataset.url;
        swan.navigateTo({
            url: url
        })
    },
    // 装修指南
    tip: function () {
        getIndex(
            '/bd/v1/pindao/tip',
        ).then(data => {
            if (data.error_code == 0) {
                const tiplist = []
                const tipdata = data.data
                tipdata.forEach((item, index) => {
                    item.views = item.pv
                    item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                    tiplist.push(item)
                })
                this.setData({
                    tipList: tiplist
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '数据请求失败',
                    icon: 'none'
                })
            })
    },
    // 选材导购
    guide: function () {
        getIndex(
            '/bd/v1/pindao/guide',
        ).then(data => {
            if (data.error_code == 0) {
                const tiplist = []
                const tipdata = data.data
                tipdata.forEach((item, index) => {
                    item.views = item.pv
                    item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                    tiplist.push(item)
                })
                this.setData({
                    guideList: tiplist
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '数据请求失败',
                    icon: 'none'
                })
            })
    },
    // 装修百科
    baike: function () {
        getIndex(
            '/bd/v1/baike/recent',
        ).then(data => {
            if (data.error_code == 0) {
                const tiplist = []
                const tipdata = data.data.list
                tipdata.forEach((item, index) => {
                    item.url = '/pages/baike/bkdetail/bkdetail?id=' + item.id
                    item.face = item.thumb
                    tiplist.push(item)
                })
                this.setData({
                    baikeList: tiplist
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '数据请求失败',
                    icon: 'none'
                })
            })
    },
    // 装修问答
    wenda: function () {
        getIndex(
            '/bd/v1/wenda/recent',
        ).then(data => {
            if (data.error_code == 0) {
                const tiplist = []
                const tipdata = data.data.list
                tipdata.forEach((item, index) => {
                    item.url = '/pages/wenda/wddetail/wddetail?type=' + 'wenda' + '&' + 'id=' + item.id
                    item.post_time = $time.formatTime(item.post_time, 'Y-M-D')
                    tiplist.push(item)
                })
                this.setData({
                    wendaList: tiplist
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '数据请求失败',
                    icon: 'none'
                })
            })
    },
    // 装修知识
    zhishi: function () {
        getIndex(
            '/bd/v1/zhishi/list',
        ).then(data => {
            if (data.error_code == 0) {
                const tiplist = []
                const zsList = data.data
                if (Object.keys(zsList) == 0) {
                    this.setData({
                        isHas: false
                    })
                }
                const strategy = data.data.strategy
                const baike = data.data.baike
                const question = data.data.question
                this.setData({
                    strategy: strategy,
                    baike: baike,
                    question: question
                })
            }
        })
            .catch(error => {
                swan.showToast({
                    title: '数据请求失败',
                    icon: 'none'
                })
            })
    },
    // 类型跳转
    toClass: function (e) {
        let url = e.currentTarget.dataset.url;
        swan.navigateTo({
            url: url
        })
    },
    changTab: function (e) {
        if (e.target.dataset.current == 1) {
            this.setData({
                autoHeight: true
            })
        } else {
            this.setData({
                autoHeight: false
            })
        }
        var that = this;
        that.setData({
            currentTab: e.target.dataset.current
        });
    },
    catchTouchMove: function () {
        return false
    },
    bindChange1: function (e) {
        var that = this;
        that.setData({ currentTab: e.detail.current });
    },
    // 更多
    more: function (e) {
        let url = e.currentTarget.dataset.url;
        swan.navigateTo({
            url: url
        })
    },
    // 裝修知识
    changGong: function (e) {
        if (e.target.dataset.current == 2) {
            this.setData({
                autoGoog: true
            })
        } else {
            this.setData({
                autoGoog: false
            })
        }
        var that = this;
        that.setData({
            currentGoog: e.target.dataset.current
        });
    },
    bindChange2: function (e) {
        var that = this;
        that.setData({ currentGoog: e.detail.current });
    },
    myScroll: function (e) {
        if (!this.data.hasSCroll) {

        }
        this.setData({
            hasSCroll: true
        })
    },
    onLoad() {
        swan.setNavigationBarTitle({
            title: "齐装网"
        })
        swan.setPageInfo({
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            keywords: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            description: '装修攻略,装修流程,装修风水,装修风格,局部装修',
            image: '/images/index_1.png'
        })
        let that = this
        that.banner()
        that.tip()
        this.guide()
        this.baike()
        this.wenda()
        this.zhishi()
    },
    onShow() {
        swan.setPageInfo({
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            keywords: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            description: '装修攻略,装修流程,装修风水,装修风格,局部装修',
            image: '/images/index_1.png'
        })
    }
})
