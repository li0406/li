/**
 * @file index.js
 * @author swan
 */
Page({
    data: {
        canIgoNext:'fadeOut',
        indexTips:[
            {
                text:'你期望的装修是怎样的？',
                show:'fadeOut'
            },
            {
                text:'或许你难以描述出…',
                show:'fadeOut'
            },
            {
                text:'但肯定的是：家，装修后的样子就在你的心里',
                show:'fadeOut'
            },
            {
                text:'我们做的只是帮你发现它，还原它原本的面貌',
                show:'fadeOut'
            },
            {
                text:'简单3步，定制专属装修设计方案，看到未来家的样子',
                show:'fadeOut'
            }
        ]
    },
    onLoad() {
        // 监听页面加载的生命周期函数
        let me = this
        setTimeout(function(){
            me.setTipsFadeIn(0)
        }, 1500);
        // 获取城市ID
    },
    onShow(){
        swan.setPageInfo({
            title: '房屋装修设计方案定制 - 室内装修风格设计 - 齐装网',
            keywords: '装修设计方案、装修方案定制、装修风格设计',
            description:'未来你的家想装修成什么样子，也需你难以具体描述，齐装网寻找未来的家功能通过对您偏好的筛选，帮你发现她，寻找她原来的面貌',
            image:'/images/bg_one.jpg'
        })
    },
    onUnload(){
        let me  = this
        for(let i in me.data.indexTips){
            me.setData({
                ['indexTips['+i+'].show']: 'fadeOut'
            })
        }
    },
    setTipsFadeIn(index){
        let me = this
        me.setData({
            ['indexTips['+index+'].show']: 'fadeIn'
        })
        if (index<4) {
            index++
            setTimeout(function(){
                me.setTipsFadeIn(index)
            }, 1500);
        } else {
            setTimeout(function(){
                me.setData({
                    canIgoNext: 'fadeIn'
                })
            }, 1500)
        }
    },
    toHuxing(){
        if(this.data.canIgoNext == 'fadeOut'){
            return
        }
        swan.redirectTo({
            url: '/pages/huxing/huxing'
        });
    }
})
