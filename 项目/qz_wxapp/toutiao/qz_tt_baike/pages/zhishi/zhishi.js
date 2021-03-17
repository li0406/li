import { getZhihiData } from '../../utils/api.js'
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
        currentPage: 1,
        pageNumber: 1,
        ifbj: true,
        infoImg: false,
        group: '',
        dataList: [{ name: '' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: '' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }, { name: ' ' }],
        keyword_module: {
            gonglue: 1,
            baike: 2,
            wenda: 3
        },
        wenda: {
            title: '在线知识问答平台 - 齐装网装修问答',
            description: '齐装网在线知识问答平台提供全面的生活装修知识问答、在线问答、问答知识等网友提问及回答信息，解决装修问题、生活问题就来齐装网在线知识问答平台。',
            keywords: '知识问答,问答平台,在线问答,装修问答'
        },
        title: ''
    },
    onLoad: function (options) {
        this.setData({
            currentPage: options.p || 1,
            group: options.group,
        })
        this.getDatas()
        if (options.group === 'gonglue') {
        }
    },
    onReady: function () {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function () {
        // 监听页面显示的生命周期函数
    },
    getDatas:function(){
        getZhihiData(
            '/bd/v1/zhishi/',
            {
                p: this.data.currentPage,
                keyword_module:2,
                page_size:58
            }
        ).then(res=>{
            if(res.error_code===0){
                this.setData({
                   dataList:res.data.list,
                   pageNumber:res.data.page.page_total_number
                })
            }else{
                tt.showToast({
                    title: '网络错误，请稍后重试',
                    icon: 'none'
                })
            }
        }).catch(res=>{
            tt.showToast({
                title: '网络错误，请稍后重试',
                icon: 'none'
            })
        })
    },
    onHide: function () {
        // 监听页面隐藏的生命周期函数
    },
    onUnload: function () {
        // 监听页面卸载的生命周期函数
    },
    onPullDownRefresh: function () {
        // 监听用户下拉动作
    },
    onReachBottom: function () {
        // 页面上拉触底事件的处理函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});