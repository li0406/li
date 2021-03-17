Component({
    properties: {
        propName: {
            type: String,
            value: 'val',
            observer: function(newVal, oldVal) {
            }
        }
    },

    data: {
        display:''
    },

    methods: {
        onTap: function () {
            this.setData({});
        },
        show(){
            if(this.data.display=='none'){
                this.setData({
                    display:''
                })
            }else{
                this.setData({
                    display:'none'
                })
            }
        },
        //去装修报价页面
        tooffer(){
            swan.switchTab({
                url:"/pages/gonglue/zhuangxiubj/zhuangxiubj"
            })
        },
        //去免费设计页面
        todesign(){
            swan.navigateTo({
                url:"/pages/gonglue/zhuangxiusj/zhuangxiusj"
            })
        }
    }
});