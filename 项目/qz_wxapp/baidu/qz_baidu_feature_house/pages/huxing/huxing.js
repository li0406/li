const app = getApp()
Page({
    data: {
        hxData: [
            {
                text:"一居室",
                selected: false,
                imageUrl:'http://staticqn.qizuang.com/meitu/20181225/1462720799166-717824-s5.jpg',
                id: '1'
            },
            {
                text:"二居室",
                selected: false,
                imageUrl:'http://staticqn.qizuang.com/meitu/20190626/QQjietu20190612172817-539615-s5.jpg',
                id: '2'
            },
            {
                text:"三居室",
                selected: false,
                imageUrl:'http://staticqn.qizuang.com/meitu/20190626/QQjietu20190612172833-539990-s5.jpg',
                id: '3'
            },
            {
                text:"四居室",
                selected: false,
                imageUrl:'http://staticqn.qizuang.com/meitu/20190524/4-688209-s5.jpg',
                id: '4'
            }
        ],
        hxselectId: '',
        cid:''
    },
    onLoad: function (option) {
        // 监听页面加载的生命周期函数
        if (option.refer == 2) {
            this.setCurrent(option.hx)
        } else {
            for(let i in this.data.hxData) {
                this.setData({
                    ['hxData['+i+'].selected']:false
                })
            }
        }
        app.getCityName()
    },
    onShow(){
        swan.setPageInfo({
            title: '户型设计 - 户型图选择 - 齐装网',
            keywords: '户型设计，户型图',
            description:'齐装网通过用户对户型的选择，记录用户偏好，帮助用户定制专属的装修设计方案，看到未来家的样子。'
        })
    },
    setCurrent:function(id){
        let me = this
        for (let i in me.data.hxData) {
            if (me.data.hxData[i].id == id) {
                me.setData({
                    ['hxData['+i+'].selected']: true,
                    hxselectId:id
                })
            } else {
                me.setData({
                    ['hxData['+i+'].selected']: false
                })
            }
        }
    },
    changeSelect:function(e){
        this.setCurrent(e.currentTarget.dataset.id)
    },
    toStyle: function(){
        if (this.data.hxselectId === '') {
            swan.showToast({
                title: '请选择户型',
                icon: 'none'
            });
            return
        }
        swan.redirectTo({
            url: '/pages/fengge/fengge?hx='+this.data.hxselectId+'&refer=1'
        });
    }
});