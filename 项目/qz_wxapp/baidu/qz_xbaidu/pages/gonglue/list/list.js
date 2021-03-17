import { getIndex } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
const app = getApp()
import { million } from '../../../utils/utils.js'
Page({
    data: {
        scrollHeight: '',
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
        topNum: 0,
    },
    banner: function () {
        getIndex(
            '/bd/v1/pindao/banner',
        ).then(data => {
            if (data.error_code == 0) {
                this.setData({
                    images: data.data
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: "网络异常",
                icon: 'none'
            })
        })
    },
    gonglue: function () {
        getIndex(
            '/bd/v1/gonglue',
        ).then(data => {
            if (data.error_code == 0) {
                const fczsList = []
                const fczs = data.data.fczs
                if (fczs) {
                    fczs.forEach((item, index) => {
                        item.views = item.pv
                        item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                        item.likes = million(item.likes)
                        fczsList.push(item)
                    })
                }
                const baikeList = []
                const baike = data.data.baike
                if (baike) {
                    baike.forEach((item, index) => {
                        item.face = item.thumb
                        item.url = '/pages/baike/bkdetail/bkdetail?id=' + item.id
                        baikeList.push(item)
                    })
                }
                const askList = []
                const ask = data.data.ask
                if (ask) {
                    ask.forEach((item, index) => {
                        item.url = '/pages/wenda/wddetail/wddetail?type=' + 'wenda' + '&' + 'id=' + item.id
                        item.post_time = $time.formatTime(item.post_time, 'Y-M-D')
                        askList.push(item)
                    })
                }
                const zhinanList = []
                const zhinan = data.data.zhinan
                if (zhinan) {
                    zhinan.forEach((item, index) => {
                        item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                        item.likes = million(item.likes)
                        zhinanList.push(item)
                    })
                }
                const xcdgList = []
                const xcdg = data.data.xcdg
                if (xcdg) {
                    xcdg.forEach((item, index) => {
                        item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                        item.likes = million(item.likes)
                        xcdgList.push(item)
                    })
                }
                this.setData({
                    ask: askList,
                    baike: baikeList,
                    fczs: fczsList,
                    xcdg: xcdgList,
                    zhinan: zhinanList,
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: "网络异常",
                icon: 'none'
            })
        })
    },
    goTop: function (e) {
        this.setData({
            topNum: this.data.topNum = 0
        });
    },
    scrolltoupper(e) {
        if (e.detail.scrollTop > 100) {
            this.setData({
                floorstatus: true
            });
        } else {
            this.setData({
                floorstatus: false
            });
        }
    },
    toSwiper: function (e) {
        let url = e.currentTarget.dataset.url;
        swan.navigateTo({
            url: url
        })
    },
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
    onLoad() {
        this.banner()
        this.gonglue()
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
