import { fadanHandle, getOrderDetail, xgtDetail } from "../../../utils/api.js"
const app = getApp()
Page({
    data: {
        orderTitle: '<div><span style="color: #DB2C2C;font-size: 50rpx;">8秒</span>计算装修需要多少钱</div>',
        info: '',
        id: '',
        preId: '',
        nextId: '',
        isHide: true,
        selectText: '',
        cityId: '',
        site_source: 20092258,
        bj_detail: {},
        total: '',
        orderOpen: true,
        result_flag: false,
        windOpen: false,
        startX: ''
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.getXgtDetail(options.id)
        // this.getXgtDetail('j68126')
    },
    touchStart: function(e) {
        if (e.touches.length == 1) {
            this.setData({
                startX: e.touches[0].clientX
            });
        }
    },
    touchEnd: function(e) {
        if(this.data.id){
            this.getXgtDetail(this.data.id)
        }
    },
    touchMove: function(e) {
        if (e.touches.length == 1) {
            var moveX = e.touches[0].clientX;
            var diffX = this.data.startX - moveX;
            if (diffX < 0) { //向右滑
                if(this.data.preId) {
                    this.setData({
                        id: this.data.preId
                    })
                }
            } else if (diffX > 0) { //向左滑
                if(this.data.nextId){
                    this.setData({
                        id: this.data.nextId
                    })
                }
            }
        }
    },
    noImage: function() {
        // do thing
    },
    getXgtDetail: function(id) {
        xgtDetail(
            '/bd/v1/meitu/detail',
            {
                id: id
            }
        ).then(data => {
            if(data.error_code === 0){
                this.setData({
                    info: data.data.detail,
                    preId: data.data.nearby.next_detail_flag,
                    nextId: data.data.nearby.prev_detail_flag
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: [],
                    success: res => {
                        console.log('setPageInfo success', res);
                    },
                    fail: err => {
                        console.log('setPageInfo fail', err);
                    }
                })
            } else {
                this.setData({
                    id: ''
                })
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
    baoJia: function() {
        this.setData({
            windOpen: true,
            orderOpen: true,
            result_flag: false
        })
    },
    closeAllWin: function() {
        this.setData({
            windOpen: false
        })
    },
    selectHandle: function () {
        this.setData({
            isHide: false
        })
    },
    closeSelect: function (res) {
        if (JSON.stringify(res.detail) === '{}') {
            this.setData({
                isHide: true,
            })
        } else {
            this.setData({
                isHide: true,
                cityId: res.detail[0],
                selectText: res.detail[1]
            })
        }
    },
    formSubmitHandle: function (e) {
        let city = this.data.selectText;
        let area = e.detail.value.area;
        let phone = e.detail.value.phone;
        let srcRemark = app.globalData.sourceMark;
        //所在城市
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        //房屋面积
        if (!app.checkFun.checkNull(area, '请输入房屋面积') || !app.checkFun.checkNumber(area)) return
        //联系方式
        if (!app.checkFun.checkNull(phone, '请输入您的手机号') || !app.checkFun.checkPhone(phone)) return
        //发单报价
        fadanHandle(
            '/v1/fb?source=' + parseInt(this.data.site_source), {
            cs: this.data.cityId,
            mianji: area,
            tel: phone,
            src: srcRemark,//渠道标识
            source: parseInt(this.data.site_source)//位置标识
        }).then(data => {
            if (data.error_code == 0) {
                getOrderDetail('/v1/quote', {
                    tel: phone,
                    source: this.data.site_source
                }).then(data => {
                    if (data.error_code == 0) {
                        this.setData({
                            bj_detail: data.date.child,
                            total: data.date.total,
                            orderOpen: false,
                            result_flag: true
                        })
                    }
                }).catch(res => {

                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none',
                    mask: false,
                });
            }
        }).catch(res => {
            swan.showToast({
                title: "网络异常",
                icon: 'none',
                mask: false,
            });
        })
    },
    setNavigationBarColor() {
        swan.setNavigationBarColor({
            frontColor: '#ffffff',
            backgroundColor: '#000000',
            animation: {
                duration: 500,
                timingFunc: 'linear'
            },
            success: res => {
                console.log('setNavigationBarColor success');
            },
            fail: err => {
                console.log('setNavigationBarColor fail', err);
            }
        });
    }
});