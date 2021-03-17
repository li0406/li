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
        swan.showLoading({
            title: '加载中',
        })
        getWalkDetail(
            '/bd/v1/baike/detail',
            {
                id: id
            }
        ).then(data => {
            swan.hideLoading()
            let detail = data.data.detail;
            let dataList = data.data.recommend
            let contents = detail.content;
            var that = this;
            var that = this;
            let imgReg = /<img.*?(?:>|\/>)/gi //匹配图片中的img标签
            let srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i // 匹配图片中的src
            let imgArry = contents.match(imgReg)  //筛选出所有的img
            let srcArry = []
            imgArry.forEach((item, index)=>{
                if (index < 3) {
                    srcArry.push(item.match(srcReg)[1])
                }
            })
            bdParse.bdParse('article', 'html', contents, that, 5);
            that.setData({
                title: detail.title,
                post_time: detail.post_time,
                views: detail.views,
                description: detail.description,
                thumb: detail.thumb,
                nodata: ""
            })
            dataList.forEach(item => {
                item.url = '/pages/baike/bkdetail/bkdetail?id=' + item.id
            })
            that.setData({
                dataList: dataList
            })
            swan.setPageInfo({
                title: detail.title + "-齐装网",
                keywords: detail.keywords,
                description: detail.description,
                releaseDate: detail.post_time,
                image: srcArry
            })
        }).catch(error => {
            swan.showToast({
                title: '',
                icon: 'none'
            })
        })
    },
    onLoad: function (e) {
        this.setData({
            id: e.id
        })
        this.getDetail(e.id)
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
