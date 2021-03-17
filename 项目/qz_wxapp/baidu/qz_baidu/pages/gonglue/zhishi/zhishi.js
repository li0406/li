import { getZhihiData } from '../../../utils/api.js'
Page({
    data: {
        currentPage:1,
        pageNumber:1,
        group:'',
        dataList:[{name:''},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:''},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '},{name:' '}],
        keyword_module:{
            gonglue:1,
            baike:2,
            wenda:3
        },
        gonglue:{
            title: '房屋装修知识大全 - 房屋装修攻略 - 齐装网',
            description: '齐装网房屋装修知识大全，不仅为业主提供好看的房屋装修设计、房屋装修流程、房屋装修风水、房屋装修注意事项等房屋装修攻略知识内容，还能免费获得房屋装修报价清单和设计方案',
            keywords: '房屋装修，装修知识，装修攻略，房屋装修设计，房屋装修步骤，房屋装修风水，房屋装修注意事项'
        },
        baike:{
            title: '家居装修百科知识大全 - 齐装网装修百科',
            description: '齐装网家居装修百科知识平台通过装修百科词条旨在为用户提供全面的家居装修、装修百科、家居知识等家居装修百科知识问题。学装修，看齐装网百科知识词条。',
            keywords: '百科知识，家居装修，装修百科，百科全书,百科词条,百科知识大全'
        },
        wenda:{
            title: '在线知识问答平台 - 齐装网装修问答',
            description: '齐装网在线知识问答平台提供全面的生活装修知识问答、在线问答、问答知识等网友提问及回答信息，解决装修问题、生活问题就来齐装网在线知识问答平台。',
            keywords: '知识问答,问答平台,在线问答,装修问答'
        }
    },
    onLoad: function (options) {
        this.setData({
            currentPage: options.p||1,
            group:options.group,
        })
        swan.setPageInfo(this.data[this.data.group])
        this.getDatas()
        if(options.group==='gonglue'){
            swan.setNavigationBarTitle({
                title: "装修攻略知识"
            })
        }
        if(options.group==='baike'){
            swan.setNavigationBarTitle({
                title: "装修百科知识"
            })
        }
        if(options.group==='wenda'){
            swan.setNavigationBarTitle({
                title: "装修问答知识"
            })
        }
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function() {
        // 监听页面显示的生命周期函数
        swan.setPageInfo(this.data[this.data.group])
    },
    getDatas:function(){
        getZhihiData(
            '/bd/v1/zhishi/',
            {
                p: this.data.currentPage,
                keyword_module:this.data.keyword_module[this.data.group],
                page_size:58
            }
        ).then(res=>{
            if(res.error_code===0){
                this.setData({
                   dataList:res.data.list,
                   pageNumber:res.data.page.page_total_number
                })
            }else{
                swan.showToast({
                    title: '网络错误，请稍后重试',
                    icon: 'none'
                })
            }
        }).catch(res=>{
            swan.showToast({
                title: '网络错误，请稍后重试',
                icon: 'none'
            })
        })
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