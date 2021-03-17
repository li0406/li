import { getStyleImage } from '../../utils/api.js';
Page({
    data: {
        huxing:'',
        fengge:'',
        largeView:false,
        resData:[],
        swiperData:[],
        swiperLen:'',
        fgData:[
            {
                name:'os',
                text:'欧式风格',
                selected:false
            },
            {
                name:'zs',
                text:'中式风格',
                selected:false
            },
            {
                name:'rsjy',
                text:'日式简约风格',
                selected:false
            },
            {
                name:'dzh',
                text:'地中海风格',
                selected:false
            },
            {
                name:'ty',
                text:'田园风格',
                selected:false
            },
            {
                name:'qt',
                text:'其他',
                selected:false
            }
        ],
        currentIndex: 1,
        initCurrent:0
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        // 来自上一页
        let me = this
        if (options.refer ==1) {
            me.setData({
                huxing:options.hx,
                fengge:''
            })
        }
        // 来自下一页
        if (options.refer ==3) {
            me.setData({
                huxing:options.hx,
                fengge:options.fg
            })
        }
        getStyleImage().then(res=>{
            if(res.error_code === 0) {
                me.setData({
                    resData:res.data.list
                }, function(){
                    me.setCurrent(options.fg)
                })
            } else{
                me.setCurrent(options.fg)
            }
        })
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow(){
        swan.setPageInfo({
            title: '装修风格分类 - 装修风格选择 - 齐装网',
            keywords: '装修风格，装修风格分类，装修风格选择',
            description:'齐装网通过用户对装修风格的选择，记录用户偏好，帮助用户定制专属的装修设计方案，看到未来家的样子。'
        })
    },
    changeSelect:function(e){
        this.setCurrent(e.currentTarget.dataset.id)
    },
    setCurrent:function(id){
        if (id == undefined) {
            return
        }
        let me = this
        for (let i in me.data.fgData) {
            if (me.data.fgData[i].name == id) {
                me.setData({
                    ['fgData["'+i+'"].selected']: true,
                    fengge:id
                })
            } else {
                me.setData({
                    ['fgData["'+i+'"].selected']: false
                })
            }
        }  
    },
    toHuxing: function(){
        swan.redirectTo({
            url: '/pages/huxing/huxing?hx='+this.data.huxing+'&refer=2'
        });
    },
    toGetPlan:function(){
        if (this.data.fengge == '') {
            swan.showToast({
                title: '请选择风格',
                icon: 'none'
            });
            return
        }
        swan.redirectTo({
            url: '/pages/fangan/fangan?refer=2&hx='+this.data.huxing+'&fg='+this.data.fengge
        });
    },
    getLargeImage: function(e){
        let me = this
        if(e.currentTarget.dataset.index){
            let index = e.currentTarget.dataset.index
            this.setData({
                swiperData: me.data.resData[index],
                swiperLen: me.data.resData[index].length,
                currentIndex:1
            })
        }
        this.setData({
            largeView: !this.data.largeView
        })
    },
    swiperChange:function(e){
        this.setData({
            currentIndex:e.detail.current+1
        })
    }
});