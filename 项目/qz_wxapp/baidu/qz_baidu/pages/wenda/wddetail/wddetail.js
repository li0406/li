import { getWalkList } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
Page({
    data: {
        wlist: [],
        isclick: false,
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: '加载中',
        page: 1,
        ismask: true,
        isIpx: false,
        itemOne: false,
        conInfo: true
    },
    imgYu(event) {
        var src = event.currentTarget.dataset.src;//获取data-src
        var imgList = event.currentTarget.dataset.list;
        let urls = []
        imgList.forEach((item) => {
            urls.push(item.path)
        })
        //图片预览
        swan.previewImage({
            current: src,
            urls: urls,
            success: function (res) {
                console.log('previewImage success', res);
            }
        })
    },
    artHandle(e) {
        let list = this.data.wlist
        for (let item of list) {
            if (item.id == e.currentTarget.dataset.id) {
                if (this.data.isclick) {
                    item.handelName = "展开全文"
                    item.isclick = !this.data.isclick
                    this.setData({
                        isclick: !this.data.isclick
                    })
                    break
                } else {
                    item.handelName = "收起全文"
                    item.isclick = !this.data.isclick
                    this.setData({
                        isclick: !this.data.isclick
                    })
                    break
                }
            }
        }
        this.setData({
            wlist: list,
        })
    },
    itemHandle() {
        if (this.data.itemOne) {
            itemOne = !this.data.itemOne
            this.setData({
                itemOne: !this.data.itemOne,
                itemName: "展开全文"
            })
        } else {
            itemOne = !this.data.itemOne
            this.setData({
                itemOne: !this.data.itemOne,
                itemName: "收起全文"
            })
        }
    },
    praiseHandle(e) {
        // 获取当前点击下标
        var id = e.currentTarget.dataset.id;
        // data中获取列表
        var message = this.data.wlist;
        message.forEach(item => {
            if (item.id == id) {
                if (item.collected == 0) {
                    item.collected = parseInt(item.collected) + 1
                    item.agree = parseInt(item.agree) + 1
                } else {
                    item.collected = parseInt(item.collected) - 1
                    item.agree = parseInt(item.agree) - 1
                }
            }
        })
        this.setData({
            wlist: message
        })
    },
    xartHandle(e) {
        let list = this.data.xlist
        for (let item of list) {
            if (item.id == e.currentTarget.dataset.id) {
                if (this.data.isclick) {
                    item.handelName = "展开全文"
                    item.isclick = !this.data.isclick
                    this.setData({
                        isclick: !this.data.isclick
                    })
                    break
                } else {
                    item.handelName = "收起全文"
                    item.isclick = !this.data.isclick
                    this.setData({
                        isclick: !this.data.isclick
                    })
                    break
                }
            }
        }
        this.setData({
            xlist: list,
        })
    },
    xpraiseHandle(e) {
        // 获取当前点击下标
        var id = e.currentTarget.dataset.id;
        // data中获取列表
        var message = this.data.xlist;
        message.forEach(item => {
            if (item.id == id) {
                if (item.collected == 0) {
                    item.collected = parseInt(item.collected) + 1
                    item.agree = parseInt(item.agree) + 1
                } else {
                    item.collected = parseInt(item.collected) - 1
                    item.agree = parseInt(item.agree) - 1
                }
            }
        })
        this.setData({
            xlist: message
        })
    },
    lower(e) {
        let hlist = this.data.hlist
        this.setData({
            xlist: hlist,
            nomore:'没有更多记录了···'
        })
    },
    getDetail: function (id) {
        let that = this
        swan.showLoading({
            title: '加载中',
        })
        getWalkList(
            '/bd/v1/wenda/detail',
            {
                id: id
            }
        ).then(data => {
            let detail = data.data
            let dataList = data.data.answer_list
            let imgList = data.data.img
            let time = data.data.post_time
            let post_time = $time.formatTime(time, 'Y-M-D')
            let best_aid = detail.best_aid
            if (best_aid) {
                let conwen = detail.best_answer.content
                let conadd = conwen
                if (conadd.length > 72) {
                    this.setData({
                        itemName: '展开全文'
                    })
                }
                that.setData({
                    best_answer: detail.best_answer,
                    conwen: conadd
                })
            } else {
                that.setData({
                    conInfo: false
                })
            }
            that.setData({
                detail: detail,
                imgList: imgList,
                post_time: post_time
            })
            dataList.forEach(item => {
                item.content = (item.content).replace(/<(\/p).*?>/g, '');
                if (item.content.length > 72) {
                    item.handelName = '展开全文'
                }
                item.post_time = $time.formatTime(item.post_time, 'Y-M-D')
                item.isclick = that.data.isclick
                item.collected = 0
            })
            let leng = dataList.length
            let preList = dataList.slice(0, 5)
            let phList = dataList.slice(5, leng)
            let phlength = phList.length
            if(phlength>0){
                that.setData({
                    nomore:'加载中...'
                })
            }else{
                that.setData({
                    nomore:'没有更多记录了···'
                })
            }
            that.setData({
                wlist: preList,
                hlist: phList
            })
            swan.hideLoading()
            swan.setPageInfo({
                title: detail.title + "-齐装网",
                keywords: detail.keywords,
                description: detail.description,
                releaseDate: detail.post_time
            })
        }).catch(error => {
            swan.showToast({
                title: '',
                icon: 'none'
            })
        })
    },
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
    onLoad: function (options) {
        swan.setPageInfo({
            title: '齐装网',
            keywords: '齐装，齐装网，装修，装修网，装修公司',
            description: '资深家居装饰装修平台，装修难题我帮你。'
        })
        this.getSystemInfo()
        this.setData({
            id: options.id
        })
        this.getDetail(options.id)
    },
    onShow: function (options) {
        swan.setPageInfo({
            title: '齐装网',
            keywords: '齐装，齐装网，装修，装修网，装修公司',
            description: '资深家居装饰装修平台，装修难题我帮你。'
        })
    },
    onReady: function () {
    },
    onShareAppMessage: function () {
    }
});
