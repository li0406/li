Component({
    properties: {
        pageNumber:{
            type:Number,
            value:0,
            observer:function(newVal){
                this.renderPages(newVal)
            }
        },
        currentPage:{
            type:Number,
            value:0
        },
        pageUrl:{
            type:String,
            value:'',
            observer:function(newVal){
                if(newVal.indexOf("?") == -1){
                    this.setData({
                        toPageUrl:newVal+"?p="
                    })
                }else{
                    this.setData({
                        toPageUrl:newVal+"&p="
                    })
                }
            }
        }
    },
    data: {
        pageSelecteHide:true,
        pageArray:[],
        visiable:false,
        toPageUrl:''
    }, // 私有数据，可用于模版渲染
    // 生命周期函数，可以为函数，或一个在methods段中定义的方法名
    attached: function () {
    },
    detached: function () {},
    methods: {
        renderPages:function(newVal){
            let temArray = []
            for(let i =1; i<newVal+1; i++){
                temArray.push(i)
            }
            this.setData({
                pageArray:temArray,
                visiable:newVal>1?true:false
            })
        },
        // 选择页面
        selectCurrentPage: function (event) {
            let index = parseInt(event.target.dataset.page)
            if(index&&index!=0&&index!==parseInt(this.data.currentPage)){
                tt.navigateTo({
                    url: this.data.toPageUrl + index
                });
            }
            if(index!=0){
                this.controllPageMask()
            }
        },
        controllPageMask: function(event){
            this.setData({
                pageSelecteHide: !this.data.pageSelecteHide
            })
        },
        toPrevPage:function(){
            let prevPage = parseInt(this.data.currentPage)-1
            tt.navigateTo({
                url: this.data.toPageUrl+ prevPage
            });
        },
        toNextPage:function(){
            let nextPage = parseInt(this.data.currentPage)+1
            tt.navigateTo({
                url: this.data.toPageUrl+ nextPage
            });
        }
    },
});