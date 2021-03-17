import { getWenda } from '../../../utils/api.js'
const app = getApp()
Page({
    data: {
        pageInfo: {
            title: '家居百科-装修知识-齐装网',
            keywords: '家居百科,家居装修知识',
            description: '齐装网装修百科是一部内容开放、自由的装修百科全书，旨在创造一个涵盖所有装修领域知识、服务所有互联网用户的中文知识性装修百科全书。'
        },
        levelOneNav: null,
        levelTwoNav: null,
        currentlevelOneIndex: 0,
        currentlevelTwoIndex: 0,
        cateType: "",
        category: "",
        subcate: "",
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2,
        page: 1,
        ismask: true,
        isIpx: false,
        topNum: 0
    },
    setTab: function (e, options) {
        let id = options.category,
            pid = options.pid,
            cate = options.ty,
            category = options.category,
            subcate = options.subcate
        let zid = pid ? pid : id;
        if (zid > 3 && zid != 12) {
            this.setData({
                scrollLeft: (zid - 1) * 80
            });
        }
        if (zid == 12) {
            this.setData({
                scrollLeft: 7 * 80
            });
        }
        if (parseInt(cate) == 1) {
            this.setData({
                currentlevelOneIndex: id,
                currentlevelTwoIndex: 0
            });
            this.getLevelTwoNav(options);
        } else {
            this.setData({
                currentlevelOneIndex: pid,
                currentlevelTwoIndex: subcate
            });
            this.getLevelTwoNav(options);
        }
        this.setData({
            dataList: [],
            page: 1,
            nomore: 2,
            cateType: cate,
            category: zid,
            subcate: subcate
        });
        swan.pageScrollTo({
            scrollTop: 0
        })
        this.getContentList(e);
    },
    getLevelTwoNav: function (e) {
        let id = e.category,
            pid = e.pid,
            type = e.ty,
            levelTwoNavs = null;
        let zid = pid ? pid : id;
        if (zid > 0) {
            getWenda(
                '/bd/v1/baike/category',
                { parent_cate: zid }
            ).then(data => {
                var datas = data;
                if (datas.error_code == 0) {
                    let wList = datas.data
                    wList.forEach((item, index) => {
                        item.pid = zid
                    })
                    this.setData({
                        levelTwoNav: wList
                    });
                } else {
                    swan.showToast({
                        title: datas.error_msg,
                        icon: 'none'
                    })
                }
            })
        }
        this.setData({
            levelTwoNav: levelTwoNavs
        });
    },
    getContentList: function (e) {
        let obj = this.data,
            cateType = "",
            subcate = "",
            category = "";
        cateType = this.data.cateType;
        category = this.data.category;
        subcate = this.data.subcate
        if (category == 0) {
            swan.setNavigationBarTitle({
                title: '装修百科'
            })
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
        getWenda(
            '/bd/v1/baike/list',
            {
                p: obj.page,
                parent_cate: category,
                sub_cate: subcate
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
                    item.url = '/pages/baike/bkdetail/bkdetail?id=' + item.id
                    tipdata.push(item)
                })
                this.setData({
                    dataList: tipdata
                })

                if (parseInt(category)) {
                    swan.setNavigationBarTitle({
                        title: dataList[0].cate_name
                    })
                }
                let tempTitle = 'pageInfo.title'
                let tempKeywords = 'pageInfo.keywords'
                let tempDes = 'pageInfo.description'
                if (datas.data.tdk) {
                    this.setData({
                        [tempTitle]: datas.data.tdk.title,
                        [tempKeywords]: datas.data.tdk.keywords,
                        [tempDes]: datas.data.tdk.description
                    })
                    swan.setPageInfo({
                        title: datas.data.tdk.title + '-齐装网',
                        keywords: datas.data.tdk.keywords,
                        description: datas.data.tdk.description,
                        image: []
                    })
                }

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
                    title: '',
                    icon: 'none'
                })
            })
    },
    lower(e) {
        this.getContentList()

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
    goTop: function (e) {
        this.setData({
            topNum: this.data.topNum = 0
        });
    },
    compare(property) {
        return function (a, b) {
            var value1 = a[property]
            var value2 = b[property]
            return value1 - value2
        }
    },
    getFilter: function (options) {
        let me = this
        getWenda(
            '/bd/v1/baike/category',
        ).then(data => {
            var datas = data;
            if (datas.error_code == 0) {
                let wList = datas.data
                wList.sort(this.compare('cid'))
                this.setData({
                    levelOneNav: wList
                }, function () {
                    me.setTab(null, options);
                });
            } else {
                swan.showToast({
                    title: datas.error_msg,
                    icon: 'none'
                })
            }
        })
    },
    onLoad: function (options) {
        this.getFilter(options)
    },
    onShow: function (options) {
        swan.setPageInfo({
            title: '家居百科-装修知识-齐装网',
            keywords: '家居百科,家居装修知识',
            description: '齐装网装修百科是一部内容开放、自由的装修百科全书，旨在创造一个涵盖所有装修领域知识、服务所有互联网用户的中文知识性装修百科全书。',
            image: []
        })
    }
});
