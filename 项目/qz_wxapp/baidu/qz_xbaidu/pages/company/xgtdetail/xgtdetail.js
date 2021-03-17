import { caseList, caseOptions } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        selectPerson:true,
        rotateSelect:false,
        currentIndex: -1,
        fgOptions:[
            {
                id:0,
                name:"不限"
            }
        ],
        hxOptions:[
            {
                id:0,
                name:"不限"
            }
        ],
        zjOptions:[
            {
                id:0,
                name:"不限"
            }
        ],
        isToogtle:false,
        colorIndex:0,
        colorIndexone:0,
        colorIndextwo:0,
        colorIndexthree:0,
        selectNametwo:"风格",
        selectNamethree:"户型",
        selectNamefore:"造价",
        xzcity: '苏州',
        cityName: '苏州',
        cs: 'sz',
        cid: 320500,
        dataList: [],
        page: 1,
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        topNum: 0,
        backTopValue: false,
        fengge_id: '',
        huxing_id: '',
        jiage_id: '',
        noData: false
    },
    // 点击图片旋转180度效果
    clickPerson(e){
        let index = e.currentTarget.dataset.index
        if(this.data.currentIndex || this.data.currentIndex ===0){
            if (this.data.currentIndex === index) {
                this.setData({
                    currentIndex: ''
                })
            } else {
                this.setData({
                    currentIndex: index
                })
            }
        } else {
            this.setData({
                currentIndex: index
            })
        }
    },
    toogleColorone(e){
        let index = e.currentTarget.dataset.fengge_id
        let item = e.currentTarget.dataset.fengge_name
        let fengge_index = e.currentTarget.dataset.fengge_index
        let res = swan.getStorageSync('xoptions') ? swan.getStorageSync('xoptions') : {}
        res['fengge_name'] = item
        res['fengge_id'] = index
        res['fengge_index'] = fengge_index
        swan.setStorageSync('xoptions', res)
        swan.redirectTo({
            url: '../../company/xgtdetail/xgtdetail?cs=' + this.data.cs
        });
    },
    toogleColortwo(e){
        let index = e.currentTarget.dataset.huxing_id
        let item = e.currentTarget.dataset.huxing_name
        let huxing_index = e.currentTarget.dataset.huxing_index
        let res = swan.getStorageSync('xoptions') ? swan.getStorageSync('xoptions') : {}
        res['huxing_name'] = item
        res['huxing_id'] = index
        res['huxing_index'] = huxing_index
        swan.setStorageSync('xoptions', res)
        swan.redirectTo({
            url: '../../company/xgtdetail/xgtdetail?cs=' + this.data.cs
        });
    },
    toogleColorthree(e){
        let index = e.currentTarget.dataset.jiage_id
        let item = e.currentTarget.dataset.jiage_name
        let zaojia_index = e.currentTarget.dataset.zaojia_index
        let res = swan.getStorageSync('xoptions') ? swan.getStorageSync('xoptions') : {}
        res['jiage_name'] = item
        res['jiage_id'] = index
        res['zaojia_index'] = zaojia_index
        swan.setStorageSync('xoptions', res)
        swan.redirectTo({
            url: '../../company/xgtdetail/xgtdetail?cs=' + this.data.cs
        });
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false,
            currentIndex: ''
        })
    },
    closeSelect: function (res) {
        let that = this
        let xoptions = swan.getStorageSync('xoptions') ? swan.getStorageSync('xoptions') : {}
        that.setData({
            isHide: true,
        })
        if (res.detail[3] == 1) {
            that.setData({
                dataList: [],
                nomore: 2,
                page: 1,
                cid: res.detail[0],
                page: 1,
                cityName: res.detail[2],
                cs: res.detail[4]
            })
            xoptions['cid'] = res.detail[0]
            xoptions['cityName'] = res.detail[2]
            xoptions['cs'] = res.detail[4]
            swan.setStorageSync('xoptions', xoptions)
            swan.redirectTo({
                url: '../../company/xgtdetail/xgtdetail?cs=' + this.data.cs
            });
        }
    },
    lower() {
        if (!canUseReachBottom) return;
        let that = this
        let pageCurrent = that.data.pageCurrent
        let page_total_number = that.data.page_total_number
        if (pageCurrent <= page_total_number) {
            that.setData({
                page: pageCurrent + 1
            }, function () {
                that.caseList()
            })
        }
    },
    scroll: function(e) {
        let backTopValue = e.detail.scrollTop > 1200 ? true : false
        this.setData({
            backTopValue: backTopValue
        })
    },
    backTop: function(e){
        this.setData({
            topNum: 0
        })
    },
    toDetail: function(e){
        let id = e.currentTarget.dataset.id
        let cs = e.currentTarget.dataset.cs
        swan.navigateTo({
            url: '../../caseinfo/details/details?cs='+ cs +'&id=' + id
        });
    },
    closeMask: function() {
        this.setData({
            currentIndex: ''
        })
    },
    caseOptions: function() {
        caseOptions(
            '/bd/v1/case/options'
        ).then(data => {
            if(data.error_code === 0) {
                let fgOptions = this.data.fgOptions.concat(data.data.fengge_option)
                let hxOptions = this.data.hxOptions.concat(data.data.huxing_option)
                let zjOptions = this.data.zjOptions.concat(data.data.jiage_option)
                this.setData({
                    fgOptions: fgOptions,
                    hxOptions: hxOptions,
                    zjOptions: zjOptions
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
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
    caseList: function() {
        caseList(
            '/bd/v1/case/listbycity',
            {
                cid: this.data.cid,
                limit: 10,
                page: this.data.page,
                fengge_id: this.data.fengge_id,
                huxing_id: this.data.huxing_id,
                jiage_id: this.data.jiage_id
            }
        ).then(data => {
            if(data.error_code === 0){
                let data_list = this.data.dataList
                let list = data_list.concat(data.data.list)
                let page = data.data.page
                this.setData({
                    dataList: list,
                    pageCurrent: page.page_current,
                    page_total_number: page.page_total_number
                })
                if(data.data.page.page_total_number === 0 && data.data.list.length === 0){
                    this.setData({
                        noData: true
                    })
                }
                if(page.total_number>0){
                    if (data.data.list.length < 10) {
                        this.setData({
                            nomore: 3
                        })
                    } else {
                        this.setData({
                            nomore: 2
                        })
                    }
                } else {
                    this.setData({
                        nomore: 2,
                        nototal:true
                    })
                }
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                canUseReachBottom = true;
            } else {
                swan.showToast({
                    title: data.error_msg,
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
    onLoad: function () {
        let xoptions = swan.getStorageSync('xoptions');
        this.setData({
            selectNametwo: xoptions.fengge_name && xoptions.fengge_name != '不限' ? xoptions.fengge_name : '风格',
            selectNamethree: xoptions.huxing_name && xoptions.huxing_name != '不限' ? xoptions.huxing_name : '户型',
            selectNamefore: xoptions.jiage_name && xoptions.jiage_name != '不限' ? xoptions.jiage_name : '造价',
            fengge_id: xoptions.fengge_id ? xoptions.fengge_id : '',
            huxing_id: xoptions.huxing_id ? xoptions.huxing_id : '',
            jiage_id: xoptions.jiage_id ? xoptions.jiage_id : '',
            cid: xoptions.cid ? xoptions.cid : 320500,
            cityName: xoptions.cityName ? xoptions.cityName : '苏州',
            colorIndexone: xoptions.fengge_index ? xoptions.fengge_index : 0,
            colorIndextwo: xoptions.huxing_index ? xoptions.huxing_index : 0,
            colorIndexthree: xoptions.zaojia_index ? xoptions.zaojia_index : 0
        })
        this.caseList()
        this.caseOptions()
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function() {
        // 监听页面显示的生命周期函数
    },
    onHide: function() {
        // 监听页面隐藏的生命周期函数
    },
    onUnload: function() {
        // 监听页面卸载的生命周期函数
    },
    onPullDownRefresh: function() {
        // 监听用户下拉动作
    },
    onReachBottom: function() {
        // 页面上拉触底事件的处理函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});