import { getWenda } from '../../utils/api.js'
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    tt.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function (res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}
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
        subcate: "",
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        ismask: true,
        isIpx: false,
        topNum: 0,
        topNum: 0,
        quest: ''
    },
    // 设置当前栏目选中状态，一级类目点击，二级类目点击都分发到这里
    setTab: function (e, options) {
        let id = options.category ? options.category : 0,
            pid = options.pid ? options.pid : '',
            cate = options.type ? options.type : '',
            category = options.category ? options.category : 0,
            subcate = options.subcate ? options.subcate : '';
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
            //设置一级栏目选中状态并清除二级栏目的选中状态
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
        // 因为前台需要拼接数据，因此点击选项卡之后要清空数据列表，以免其他选项的数据被拼接
        this.setData({
            dataList: [],
            page: 1,
            nomore: 2,
            cateType: cate,
            category: zid,
            subcate: subcate
        });
        tt.pageScrollTo({
            scrollTop: 0
        })
        this.getContentList(e);
    },
    // 获取二级栏目
    getLevelTwoNav: function (e) {
        let id = e.category,
            pid = e.pid,
            type = e.type,
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
                    tt.showToast({
                        title: '分类请求失败',
                        icon: 'none'
                    })
                }
            })
        }
        // 填充二级栏目
        this.setData({
            levelTwoNav: levelTwoNavs
        });
    },
    // 获取攻略列表
    getContentList: function (e) {
        let obj = this.data,
            cateType = "",
            subcate = "",
            category = "";
        cateType = this.data.cateType;
        category = this.data.category;
        subcate = this.data.subcate
        if (category == 0) {
            tt.setNavigationBarTitle({
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
            tt.showLoading({
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
                tt.hideLoading()
                this.setData({
                    dataList: []
                })
            }
            if (datas.error_code == 0) {
                var dataList = this.data.dataList;
                dataList = dataList.concat(datas.data.list);
                const tipdata = []
                dataList.forEach((item, index) => {
                    item.url = '/pages/bkdetail/bkdetail?type=' + 'baike' + '&' + 'id=' + item.id
                    tipdata.push(item)
                })
                this.setData({
                    dataList: tipdata
                })

                if (parseInt(category)) {
                    tt.setNavigationBarTitle({
                        title: dataList[0].cate_name
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
                tt.showToast({
                    title: '',
                    icon: 'none'
                })
            })
    },
    //滚动到底部100px时触发
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
    //回到顶部
    goTop: function (e) {
        this.setData({
            topNum: this.data.topNum = 0
        });
    },
    // 对数组进行排序
    compare(property) {
        return function (a, b) {
            var value1 = a[property]
            var value2 = b[property]
            return value1 - value2
        }
    },
    // 获取筛选项
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
                tt.showToast({
                    title: '分类请求失败',
                    icon: 'none'
                })
            }
        })
    },
    inputquest: function (e) {
        this.setData({ quest: e.detail.value })
    },
    tapSearch() {
        let quest = this.data.quest;
        let cid = this.data.cid ? this.data.cid : '';
        let sub_cid = this.data.sub_cid ? this.data.sub_cid : '';
        if (!quest) {
            tt.redirectTo({
                url: '/pages/index/index',
            });
            return;
        }
        tt.navigateTo({
            url: '/pages/wdsearch/wdsearch?title=' + quest + '&cid=' + cid + '&sub_cid=' + sub_cid,
        });
    },
    enterSearch(e) {
        let that = this;
        let value = e.detail.value;
        let cid = this.data.cid ? this.data.cid : '';
        let sub_cid = this.data.sub_cid ? this.data.sub_cid : '';
        if (!value) {
            tt.redirectTo({
                url: '/pages/index/index',
            });
            return;
        }
        tt.navigateTo({
            url: '/pages/wdsearch/wdsearch?title=' + value + '&cid=' + cid + '&sub_cid=' + sub_cid,
        });
    },
    onLoad: function (options) {
        // 设置一级分类并请求数据
        this.getFilter(options)
        let that = this
        if (options.pid) {
            let cid = options.pid,
                sub_cid = options.subcate;
            that.setData({
                cid: cid,
                sub_cid: sub_cid
            })
        } else {
            let cid = options.category;
            that.setData({
                cid: cid
            })
        }
    },
    onShow: function (options) {
    },
    onReady: function () {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});
