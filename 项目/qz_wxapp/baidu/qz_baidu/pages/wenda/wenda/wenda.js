import { getWenda } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
//获取应用实例
const app = getApp();
Page({
    data: {
        pageInfo: {
            title: '装修攻略_装修流程_装修风水_装修风格_局部装修-齐装网',
            description: '齐装网官方装修攻略基于广大业主装修真实经历和心得的装修全攻略。装修流程、装修风水、装修风格、局部装修等全新装修攻略应有尽有。',
            keywords: '装修攻略,装修流程,装修风水,装修风格,局部装修'
        },
        levelOneNav: null,
        levelTwoNav: null,
        currentlevelOneIndex: 0, // 用于控制一级分类选中状态
        currentlevelTwoIndex: 0, // 用于控制二级分类选中状态
        cateType: "", // 一级分类标识
        category: "", // 二级分类标识
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        nodata: "no-data",
        ismask: true,
        isIpx: false,
        topNum: 0
    },
    onLoad: function (options) {
        // 设置一级分类并请求数据
        this.getFilter(options)
    },
    // 用于某个文章pv和收藏更新
    onShow: function () {
    },
    // 获取筛选项
    getFilter: function (options) {
        let me = this
        getWenda(
            '/bd/v1/wenda/cate',
        ).then(data => {
            var datas = data;
            if (datas.error_code == 0) {
                let wList = datas.data.list
                this.setData({
                    levelOneNav: wList,
                    nodata: ""
                }, function () {
                    me.setTab(null, options);
                });
            } else {
                swan.showToast({
                    title: '分类请求失败',
                    icon: 'none'
                })
            }
        })
    },
    //监听页面滚动
    onPageScroll: function (scrollObj) {
    },
    scroll: function (e) {
        if (e.detail.scrollTop > 2) {
            this.setData({
                isFixed: true
            });
        } else {
            this.setData({
                isFixed: false
            });
        }
    },
    setTab: function (e, options) {
        let id = options.category,
            pid = options.pid,
            cate = options.ty,
            category = options.category;
        let zid = pid ? pid : id;
        this.setData({
            scrollLeft: zid * 80
        });
        if (parseInt(cate) == 1) {
            //设置一级栏目选中状态并清除二级栏目的选中状态
            this.setData({
                currentlevelOneIndex: id,
                currentlevelTwoIndex: 0
            });
            this.getLevelTwoNav(options);
        } else {
            this.setData({
                currentlevelOneIndex: pid,
                currentlevelTwoIndex: id
            });
            this.getLevelTwoNav(options);
        }
        // 因为前台需要拼接数据，因此点击选项卡之后要清空数据列表，以免其他选项的数据被拼接
        this.setData({
            dataList: [],
            page: 1,
            nomore: 2,
            cateType: cate,
            category: category
        });
        swan.pageScrollTo({
            scrollTop: 0
        })
        this.getContentList(e);
    },
    // 获取二级栏目
    getLevelTwoNav: function (e) {
        let id = e.category,
            pid = e.pid,
            type = e.ty,
            levelTwoNavs = null;
        let levelTwoNav = this.data.levelOneNav
        let zid = pid ? pid : id;
        if (zid > 0) {
            levelTwoNavs = levelTwoNav[parseInt(zid)].sub_cate;
        }
        // 填充二级栏目
        this.setData({
            levelTwoNav: levelTwoNavs
        });
    },
    lower: function () {
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
    //回到顶部
    goTop: function (e) {
        this.setData({
            topNum: this.data.topNum = 0
        });
    },
    // 获取攻略列表
    getContentList: function (e) {
        let obj = this.data,
            cateType = "",
            category = "";
        currentlevelOneIndex = "";
        cateType = this.data.cateType;
        category = this.data.category;
        currentlevelOneIndex = this.data.currentlevelOneIndex;
        if (currentlevelOneIndex == undefined) {
            currentlevelOneIndex = 0
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
            '/bd/v1/wenda/list',
            {
                keyword: '',
                p: obj.page,
                page_size: 10,
                cate_id: category,
                top_id: currentlevelOneIndex
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
                    item.url = '/pages/wenda/wddetail/wddetail?type=' + 'wenda' + '&' + 'id=' + item.id
                    item.user_logo = item.logo
                    item.user_name = item.username
                    let time = parseInt(item.post_time) + ''
                    if (time.length > 8) {
                        item.post_time = $time.formatTime(item.post_time, 'Y-M-D')
                    }
                    tipdata.push(item)
                })
                this.setData({
                    dataList: tipdata
                })
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
                        description: datas.data.tdk.description
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
    }
})
