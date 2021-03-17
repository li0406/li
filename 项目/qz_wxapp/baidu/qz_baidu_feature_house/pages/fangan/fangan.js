const app = getApp()
import { getCityIdByName, getCityIdByIp, fadanHandle } from '../../utils/api.js';
import validate from '../../utils/validate.js'
Page({
    data: {
        huxing:'',
        fengge:'',
        tel:'',
        source:'',
        cid:'',
        src:'',
        isDisclaimer:true,
        openMask:false,
        hideBack:true
    },
    onLoad: function (option) {
        let me = this
        // 监听页面加载的生命周期函数
        if(option.refer==2) {
            this.setData({
                huxing:option.hx,
                fengge:option.fg
            })
        }
        if(app.globalData.cityName == 'error'){
            getCityIdByIp().then(res=>{
                me.setData({
                    cid:res.cid
                })
            })
        }else{
            getCityIdByName({cityname:app.globalData.cityName}).then(res=>{
                if(res.status == 1) {
                    me.setData({
                        cid:res.data.id
                    })
                }
            })
        }
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow(){
        swan.setPageInfo({
            title: '装修设计方案 - 装修设计方案获取- 齐装网',
            keywords: '装修设计，装修设计方案',
            description:'齐装网通过用户对户型、装修风格的选择，依据记录的用户偏好，帮助用户定制专属的装修设计方案，看到未来家的样子。'
        })
    },
    toStyle:function(){
        swan.redirectTo({
            url: '/pages/fengge/fengge?refer=3&fg='+this.data.fengge+'&hx='+this.data.huxing
        });
    },
    changeDisclaimer:function(){
        this.setData({
            isDisclaimer: !this.data.isDisclaimer
        })
    },
    closeMask:function(e){
        this.setData({
            openMask: !this.data.openMask,
            hideBack:false
        })
        if(e) {
            let data = e.currentTarget.dataset.click
            if(data==1){
                swan.redirectTo({
                    url: '/pages/index/index'
                });
            }
        }
    },
    toGetPlan:function(){
       let me = this
       if(!validate.checkNull(this.data.tel,'请输入您的手机号码^_^')){
           return
       }
       if(!validate.checkPhone(this.data.tel,'请输入正确的手机号码^_^')){
           return
       }
       if(!me.data.isDisclaimer){
            swan.showToast({
                title: "请勾选我已阅读并同意齐装网的《免责声明》",
                icon: 'none'
            });
            return
       }
       fadanHandle(
           '/v1/findhomefborder',
           {
               hx_no:me.data.huxing,
               fg_jc:me.data.fengge,
               cs:me.data.cid,
               tel:me.data.tel,
               source:app.globalData.source,
               src:app.globalData.src
           }
       ).then(res=>{
           me.setData({
               tel:''
           })
           if(res.error_code==0){
                me.closeMask()
           }else{
                swan.showToast({
                    title: res.error_msg,
                    icon: 'none'
                });
           }
       })
    },
    bindKeyInput:function(e){
        this.setData({
            tel: e.detail.value
        })
    }
});