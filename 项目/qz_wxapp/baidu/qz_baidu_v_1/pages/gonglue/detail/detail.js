import { getWalkDetail } from '../../../utils/api.js'
var bdParse = require('../../../templates/bdParse/bdParse.js')
Page({
    data: {
        title: '',
        addtime: '',
        views: '',
        article: '',
        pageInfo:{
            title: '',
            description: '',
            keywords: ''
        }
    },
    getDetail: function (id) {
        swan.showLoading({
            title: '加载中',
        })
        getWalkDetail(
            '/bd/v1/article',
            {
                id: id
            }
        ).then(data => {
            swan.hideLoading()
            let pageInfo = data.data;
            let contents = data.data.content;
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
                title: pageInfo.title,
                addtime: pageInfo.addtime,
                views: pageInfo.realview,
                ["pageInfo['title']"]:pageInfo.title+"-齐装网",
                ["pageInfo['description']"]:pageInfo.description,
                ["pageInfo['keywords']"]:pageInfo.keywords
            })
            swan.setPageInfo({
                title: pageInfo.title + "-齐装网",
                keywords: pageInfo.keywords,
                description: pageInfo.description,
                releaseDate: pageInfo.addtime_fu,
                image: srcArry
            })
        }).catch(error => {
            swan.showToast({
                title: 'article请求错误',
                icon: 'none'
            })
        })
    },
    onLoad: function (e) {
        let id = e.id;
        this.getDetail(id)
    },
    onReady: function () {
    },
    onShow: function () {
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});