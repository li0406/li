import { getWalkDetail } from '../../utils/api.js'
var wxParse = require('../../templates/wxParse/wxParse.js')
const app = getApp()
Page({
    data: {
        id: '',
        title: '',
        addtime: '',
        views: '',
        conrent: '',
        isHide: true,
        maskShow: false,
        hb_show: false,
        result_show: false,
        article: '',
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
        nodata: "no-data"
    },
    getDetail: function (id) {
        tt.showLoading({
            title: '加载中',
        })
        getWalkDetail(
            '/bd/v1/baike/detail',
            {
                id: id
            }
        ).then(data => {
            tt.hideLoading()
            let detail = data.data.detail;
            let dataList = data.data.recommend
            let contents = detail.content;
            var that = this;
            wxParse.wxParse('article', 'html', contents, that, 5);
            that.setData({
                title: detail.title,
                post_time: detail.post_time,
                views: detail.views,
                description: detail.description,
                thumb: detail.thumb,
                nodata: ""
            })
            dataList.forEach(item => {
                item.url = '/pages/bkdetail/bkdetail?id=' + item.id
            })
            that.setData({
                dataList: dataList
            })
        }).catch(error => {
            tt.showToast({
                title: '',
                icon: 'none'
            })
        })
    },
    getDatas: function () {
        getWalkDetail(
            '/bd/v1/zhishi/',
            {
                p: 1,
                keyword_module: 2,
                page_size: 58
            }
        ).then(res => {
            if (res.error_code === 0) {
                let newList = res.data.list
                let newsList = []
                newList.map((item) => {
                    item.id = item.href
                    item.name = item.name
                    newsList.push({ id: item.href, name: item.name })
                })
                let arrList = newsList.slice(0, 8)
                this.setData({
                    arrList: arrList
                })
            } else {
                tt.showToast({
                    title: '网络错误，请稍后重试',
                    icon: 'none'
                })
            }
        }).catch(res => {
            tt.showToast({
                title: '网络错误，请稍后重试',
                icon: 'none'
            })
        })
    },
    tapbk(e) {
        let id = e.currentTarget.dataset.id
        tt.navigateTo({
            url:'/pages/zhishilist/zhishilist?group=baike&href='+id
        });
    },
    onLoad: function (e) {
        this.setData({
            id: e.id
        })
        this.getDetail(e.id)
        this.getDatas()
    },
    onShow: function (e) {
        let that = this
    },
    bdParseTagATap: function (e) {
        let href = e.currentTarget.dataset.src
        let navgaitUrl = ''
        if (href == 'http://www.qizuang.com/zhaobiao/') {
            navgaitUrl = '/pages/zhuangxiusj/zhuangxiusj'
            tt.navigateTo({
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
        tt.navigateTo({
            url: '/pages/detail/detail?id=' + id + '&type=' + type
        });
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});
