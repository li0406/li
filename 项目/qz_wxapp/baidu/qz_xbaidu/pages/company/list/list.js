const app = getApp()
import { getgs } from '../../../utils/api.js'
var canUseReachBottom = true;

Page({
    data: {
        cityName: '苏州',
        city_order_name: '',
        chageCity: false,
        xzcity: '请选择城市',
        cityId: '',
        isHide: true,
        bjtel: '',
        //滚动
        scrollTop: null,
        // tab
        quyu: '所在区域',
        overall: '综合实力',
        isShow: [false, false],
        currentIndexOne: '',
        currentIndexTwo: '',
        qyArr: [],
        bzArr: [
            {
                id: '',
                name: "综合实力"
            },
            {
                id: 'star',
                name: "口碑"
            },
            {
                id: 'liangfang',
                name: "最新量房"
            },
            {
                id: 'qiandan',
                name: "最新签单"
            },
        ],
        parms: {
            cs: '',
            qx: '',
            order: '',
            page: 1,
            limit: '10'
        },
        pageCurrent: '',
        comList: [
            {gujia:"no"},
            {gujia:"no"},
            {gujia:"no"},
            {gujia:"no"},
            {gujia:"no"},
        ],
        nomore: 2,
        nototal: false,
        showOrderMask: false,
        showOrderMaskSuccess: false,
        backTopValue: false,
        topNum: 0
    },
    scroll: function (e) {
        this.setData({ scrollTop: e.detail.scrollTop })
        let backTopValue = e.detail.scrollTop > 1200 ? true : false
        this.setData({
            backTopValue: backTopValue,
            scrollTop: e.detail.scrollTop
        })
    },
    // tab
    changeTab(e) {
        let that = this
        let isShow = [false, false]
        let tab = e.currentTarget.dataset.tab;
        let show = e.currentTarget.dataset.show;
        isShow[tab] = true
        if (show) {
            that.setData({
                isShow: [false, false]
            })
        } else {
            that.setData({
                isShow: isShow
            })
        }
    },
    qySetTab(e) {
        let that = this
        let id = e.currentTarget.dataset.id != 'not' ? e.currentTarget.dataset.id : '';
        let name = e.currentTarget.dataset.name;
        that.setData({
            currentIndexOne: id,
            quyu: name,
            ['parms.qx']: id,
            // 重置
            comList: [],
            isShow: [false, false],
            overall: '综合实力',
            currentIndexTwo: '',
            ['parms.page']: 1,
            ['parms.order']: ''
        }, function () {
            that.getList()
        })
    },
    bzSetTab(e) {
        let that = this
        let id = e.currentTarget.dataset.id;
        let name = e.currentTarget.dataset.name;
        that.setData({
            currentIndexTwo: id,
            overall: name,
            ['parms.order']: id,
            // 重置
            comList: [],
            isShow: [false, false],
            ['parms.page']: 1,
        }, function () {
            that.getList()
        })
    },
    onTap: function(e) {
        let media_list = e.currentTarget.dataset.media_list
        let url_arry = media_list.map((item) => {
            return item.img_path
        })
        swan.previewImage({
            current:e.currentTarget.dataset.src,
            urls: url_arry
        });
    },
    // tab
    onSelectCityHandle: function () {
        this.setData({
            isHide: false,
            isShow: [false, false]
        })
    },
    onSelectCityorder: function () {
        this.setData({
            isHide: false,
            chageCity: true
        })
    },
    closeSelect: function (res) {
        let that = this
        if (JSON.stringify(res.detail) === '{}') {
            that.setData({
                isHide: true,
            })
        } else {
            if(that.data.chageCity) {
                that.setData({
                    city_order_name: res.detail[1],
                    cityId: res.detail[0],
                    chageCity: false,
                    isHide: true
                })
                return
            }
            that.getArea(res.detail[0])
            that.setData({
                isHide: true,
                cityName: res.detail[2],
                city_order_name: res.detail[1],
                cityId: res.detail[0],
                // 重置
                comList: [],
                quyu: '所在区域',
                overall: '综合实力',
                currentIndexOne: '',
                currentIndexTwo: '',
                ['parms.cs']: res.detail[0],
                ['parms.qx']: '',
                ['parms.order']: '',
                ['parms.page']: 1,
            }, function () {
                that.getList()
            })
        }
    },
    getArea(cid) {
        let that = this
        getgs(
            '/v1/city/area',
            {
                cid: cid
            }
        ).then(data => {
            let datas = data;
            if (datas.err_code == 0) {
                that.setData({
                    qyArr: datas.data
                })
            } else {
                swan.showToast({
                    title: datas.err_msg,
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
    getList() {
        canUseReachBottom = false;
        let that = this
        let parms = that.data.parms
        getgs(
            '/bd/v1/company',
            parms
        ).then(data => {
            let datas = data;
            if (datas.error_code == 0) {
                let dataList = that.data.comList
                let list = dataList.concat(datas.data.list)
                let page = datas.data.page
                list.forEach((item)=>{
                    item.url = '/pages/company/details/details?cs='+ item.bm +'&id=' + item.id
                    if(item.banner.length > 4) {
                        item.banner = item.banner.slice(0,4)
                    }
                })
                that.setData({
                    comList: list,
                    pageCurrent: page.page_current,
                    page_total_number: page.page_total_number
                })
                if(page.total_number>0){
                    if (datas.data.list.length < 10) {
                        that.setData({
                            nomore: 3
                        })
                    } else {
                        that.setData({
                            nomore: 2
                        })
                    }
                } else {
                    that.setData({
                        nomore: 2,
                        nototal:true
                    })
                }
                swan.setPageInfo({
                    title: datas.data.tdk.title,
                    keywords: datas.data.tdk.keywords,
                    description: datas.data.tdk.description,
                    articleTitle: 'xxx',
                    image: []
                })
                canUseReachBottom = true;
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
    lower() {
        if (!canUseReachBottom) return;
        let that = this
        let pageCurrent = that.data.pageCurrent
        let page_total_number = that.data.page_total_number
        if (pageCurrent <= page_total_number) {
            that.setData({
                ['parms.page']: pageCurrent + 1
            }, function () {
                that.getList()
            })
        }
    },
    // 免责声明
    checkboxChange: function (e) {
        let that = this;
        if (e.detail.value == '') {
            that.setData({
                checkValue: false
            })
        } else {
            that.setData({
                checkValue: true
            })
        }
    },
    gettel(e) {
        this.setData({
            bjtel: e.detail.value,
        })
    },
    // 预约按钮
    openOrderMask(){
        this.setData({
            showOrderMask: true
        })
    },
    // 关闭弹窗
    closeOrderMask(){
        this.setData({
            showOrderMask: false,
            showOrderMaskSuccess:false
        })
    },
    SubmitBj: function (e) {
        let city = this.data.cityId
        let phone = this.data.bjtel
        let checked = this.data.checkValue
        let that = this
        //所在城市
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        //联系方式
        if (phone.length < 1) {
            swan.showToast({
                title: "请输入您的手机号",
                icon: "none"
            })
            return;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(phone)) {
                swan.showToast({
                    title: "请输入正确的手机号码",
                    icon: "none"
                })
                return false;
            }
        }

        if (checked == false) {
            swan.showToast({
                title: "请勾选我已阅读齐装网的【免责声明】",
                icon: 'none'
            });
            return false;
        }

        //TODO 发单请求
        swan.request({
            url: 'https://appapi.qizuang.com/fb_order/?src=' + app.globalData.sourceMark,
            data: {
                cs: city,
                tel: phone,
                source: 20091139,
                src: app.globalData.sourceMark
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.status == 1) {
                    that.setData({
                        bjtel: '',
                        showOrderMaskSuccess:true,
                        showOrderMask: false
                    })
                } else {
                    swan.showToast({
                        title: res.data.info,
                        icon: "none"
                    })
                }


            }
        });
    },
    backTop: function(e){
        this.setData({
            topNum: 0
        })
    },
    // 跳转活动详情
    tohddet(e){
        let id=e.currentTarget.dataset.id
        let bm=e.currentTarget.dataset.bm
        swan.navigateTo({
            url: `../../company/hddetail/hddetail?cs=${bm}&id=${id}`
        });
    },
    todetail(e){
        let id=e.currentTarget.dataset.id
        let bm=e.currentTarget.dataset.bm
        swan.navigateTo({
            url: `../../company/details/details?cs=${bm}&id=${id}`
        });
    },
    onLoad() {

    },
    onShow() {

    }
})
