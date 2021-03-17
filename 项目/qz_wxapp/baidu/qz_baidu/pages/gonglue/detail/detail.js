import { getWalkDetail } from '../../../utils/api.js'
import { fadanHandle, getLikes } from "../../../utils/api";
var bdParse = require('../../../templates/bdParse/bdParse.js')
const app = getApp()
Page({
    data: {
        id: '',
        title: '',
        addtime: '',
        views: '',
        conrent: '',
        isHide: true,
        isShow: false,
        maskShow: false,
        hb_show: false,
        result_show: false,
        article: '',
        likes: '',
        dianzan_num: '',
        pageInfo: {
            title: '',
            description: '',
            keywords: ''
        },
        dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        isChecked: true,
        cityName: '',
        cityId: '',
        tel: '',
        name: '',
        area: '',
        nodata: "no-data",
        isIpx: false
    },
    getDetail: function (id) {
        console.log(id)
        swan.showLoading({
            title: '加载中',
        })
        getWalkDetail(
            '/bd/v1/strategy/detail',
            {
                id: id
            }
        ).then(data => {
            swan.hideLoading()
            let pageInfo = data.data;
            let contents = data.data.content;
            let dataList = pageInfo.recommend
            var that = this;
            bdParse.bdParse('article', 'html', contents, that, 5);
            swan.setNavigationBarTitle({
                title: pageInfo.title
            })
            that.setData({
                title: pageInfo.title,
                addtime: pageInfo.addtime,
                views: pageInfo.realview,
                likes: pageInfo.likes,
                nodata: ""
            })
            dataList.forEach(item => {
                item.url = '/pages/gonglue/detail/detail?type=' + item.shortname + '&' + 'id=' + item.id
                item.face = 'http://' + item.face
            })
            that.setData({
                dataList: dataList
            })
            swan.setPageInfo({
                title: pageInfo.title + "-齐装网",
                keywords: pageInfo.keywords,
                description: pageInfo.description,
                releaseDate: pageInfo.addtime
            })
        }).catch(error => {
            swan.showToast({
                title: 'article请求错误',
                icon: 'none'
            })
        })
    },
    //点赞
    onClickHandle() {
        if (!this.data.isShow) {
            getLikes('/bd/v1/articlelike', {
                id: this.data.id,
                is_add: 1
            }).then(res => {
                let temp = parseInt(this.data.likes) + 1
                this.setData({
                    isShow: true,
                    likes: temp
                })
            })
            console.log(this.data.likes)

        } else {
            getLikes('/bd/v1/articlelike', {
                id: this.data.id,
                is_add: 2
            }).then(res => {
                let temp = parseInt(this.data.likes) - 1
                this.setData({
                    isShow: false,
                    likes: temp
                })
            })
            console.log(this.data.likes)
        }

    },
    //发单
    getFdHandle: function () {
        this.setData({
            maskShow: true,
            hb_show: true
        })
    },
    onCloseFdHandle: function () {
        this.setData({
            maskShow: false,
            hb_show: false
        })
    },

    SubmitHandle: function (e) {
        // let city = e.detail.value.city
        let city = this.data.cityName
        let area = e.detail.value.area
        let phone = e.detail.value.tel
        let name = e.detail.value.name
        //所在城市
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        //联系方式
        if (!app.checkFun.checkNull(phone, '请输入您的手机号') || !app.checkFun.checkPhone(phone)) return
        //用户名称
        if (!app.checkFun.checkConnectName(name, "请输入正确的称呼")) return
        //小区
        // if (!app.checkFun.checkNull(area, '请输入小区名称') || !app.checkFun.checkXiaoqu(area)) return
        if (area == '') {
            swan.showToast({
                title: '请输入小区名称',
                icon: 'none'
            });
            return false
        }
        if (this.data.isChecked == false) {
            swan.showToast({
                title: "请确定已阅读免责声明，并勾选。",
                icon: 'none'
            });
            return false;
        }
        this.setData({
            tel: phone,
            name: name,
            area: area
        })
        fadanHandle(
            '/v1/fb',
            {
                cs: this.data.cityId,
                tel: this.data.tel,
                name: this.data.name,
                xiaoqu: this.data.area,
                src: app.config.CHANNEL_FLAG,
                source: 19070318
            })
            .then(res => {
                if (res.error_code == 0) {
                    this.setData({
                        hb_show: false,
                        result_show: true,
                        tel: '',
                        name: '',
                        area: '',
                        cityName: "B 北京市 北京",
                        cityId: "110100"
                    })
                } else {
                    swan.showToast({
                        title: res.error_msg,
                        icon: 'none'
                    })
                }

            })
            .catch(error => {
                swan.showToast({
                    title: error,
                    icon: 'none'
                })
            })

    },
    telHandle: function (e) {
        this.setData({
            tel: e.detail.value
        })
    },
    nameHanle: function (e) {
        this.setData({
            name: e.detail.value
        })
    },
    xiaoquHandle: function (e) {
        this.setData({
            area: e.detail.value
        })
    },

    onCloseAllHandle: function () {
        this.setData({
            maskShow: false,
            hb_show: false,
            result_show: false,
        })
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    //选择城市
    closeSelect: function (res) {
        this.setData({
            isHide: true,
            cityName: res.detail[1],
            cityId: res.detail[0]
        })

    },
    onChangeHandle: function (val) {
        this.setData({
            isChecked: !this.data.isChecked
        })
    },
    getSystemInfo: function () {
        let that = this
        swan.getSystemInfo({
            success: function (res) {
                if (res.model.indexOf("iPhone XR") > -1 || res.model.indexOf("iPhone X")>-1) {
                    that.setData({
                        isIpx: true
                    })
                }
            }
        });
    },

    onLoad: function (e) {
        this.getSystemInfo()
        let id = e.id;
        this.setData({
            id: e.id
        })
        this.getDetail(id)
    },
    onShow: function (e) {
        let that = this
        swan.setPageInfo({
            title: that.data.pageInfo.title + "-齐装网",
            keywords: that.data.pageInfo.keywords,
            description: that.data.pageInfo.description,
            releaseDate: that.data.pageInfo.addtime
        })
    },
    bdParseTagATap: function (e) {
        let href = e.currentTarget.dataset.src
        let navgaitUrl = ''
        if (href == 'http://www.qizuang.com/zhaobiao/') {
            navgaitUrl = '/pages/gonglue/zhuangxiusj/zhuangxiusj'
            swan.navigateTo({
                url: navgaitUrl
            });
        }
        if (href.indexOf('http://www.qizuang.com/gonglue') != -1) {
            this.urlParseFn(href)
        }
    },
    urlParseFn: function (url) {
        let urlData = url.split('/')
        let type = urlData[4]
        let id = urlData[5].split('.')[0]
        swan.navigateTo({
            url: '/pages/gonglue/detail/detail?id=' + id + '&type=' + type
        });
        // return newUrl
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});
