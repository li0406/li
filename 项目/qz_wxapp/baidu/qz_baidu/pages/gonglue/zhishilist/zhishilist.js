import { getZhishiListData } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
Page({
    data: {
        group:"",
        currentPage:1,
        pageNumber:1,
        href:'',
        gonglueList:[{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"}],
        baikeList:[{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"}],
        wendaList:[{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"},{gujia: "no-data"}],
        nodata:false
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.setData({
            group:options.group,
            currentPage:options.p||1,
            href:options.href
        })
        this.getDataList()
    },
    getDataList:function(){
        let apiUrl = {
            gonglue:'/bd/v1/strategy/list_by_keyword',
            baike:'/bd/v1/baike/list_by_keyword',
            wenda:'/bd/v1/wenda/zhishi'
        }
        getZhishiListData(apiUrl[this.data.group],{
            p:this.data.currentPage,
            href:this.data.href,
            page_size:12
        }).then(res=>{
            if(res.error_code===0){
                let resData = []
                
                if(this.data.group === 'gonglue'){
                    swan.setPageInfo({
                        title:res.data.keyword+'-齐装网',
                        description:'齐装网'+res.data.keyword+'栏目为广大网友提供'+res.data.keyword+'相关攻略知识，帮你快速解决'+res.data.keyword+'问题，您要了解的攻略应有尽有！',
                        keywords:res.data.keyword
                    })
                    resData = res.data.list
                    swan.setNavigationBarTitle({
                        title: res.data.keyword
                    })
                }
                if(this.data.group === 'baike'){
                    swan.setPageInfo({
                        title:res.data.keyword+'-齐装网',
                        description:'齐装网'+res.data.keyword+'装修百科知识平台旨在未用户提供全面的关于'+res.data.keyword+'的百科知识大全。'+res.data.keyword+'百科词条让您收货更多关于'+res.data.keyword+'的知识',
                        keywords:res.data.keyword
                    })
                    resData = res.data.list
                    swan.setNavigationBarTitle({
                        title: res.data.keyword
                    })
                }
                if(this.data.group === 'wenda'){
                    swan.setPageInfo({
                        title:res.data.word.about_word+'-齐装网装修问答',
                        description:'齐装网'+res.data.word.about_word+'问答平台在线提供全面的'+res.data.word.about_word+'系列问答知识，帮助网友快速解决'+res.data.word.about_word+'类问题及需求，还可以参与互动一起解决问题。',
                        keywords:res.data.word.about_word
                    })
                    resData = res.data.list.map(function(item, obj){
                        item.post_time = $time.formatTime(item.post_time, 'Y-M-D')
                        return item
                    })
                    swan.setNavigationBarTitle({
                        title: res.data.word.about_word
                    })
                }
                 this.setData({
                    [this.data.group+'List']:resData,
                    pageNumber:res.data.page.page_total_number,
                    nodata:resData.length===0
                })
                
            }else{
                console.log(res,1)
                swan.showToast({
                    title: '网络错误，请稍后重试',
                    icon: 'none'
                })
            }
        }).catch(res=>{
             console.log(res,2)
             swan.showToast({
                title: '网络错误，请稍后重试',
                icon: 'none'
            })
        })
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function() {
        // 监听页面显示的生命周期函数
         swan.setPageInfo(this.data[this.data.group])
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