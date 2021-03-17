const app = getApp()
import { chinaTu, cartTu } from '../../../utils/api.js'
var canUseReachBottom = true;
Page({
    data: {
        currentIndex: '',
        tuData:[],
        pageCurrent: '',
        page_total_number: '',
        nomore: 2,
        page: 1,
        title:'',
        backTopValue: false,
        topNum: 0,
        xgtList: [],
        catelist:[],
        tuDataWn:false,
        colorId:-1
    },
    // 公装效果图请求列表数据
    chinalist(typeId) {
        let that = this
        canUseReachBottom = false;
        chinaTu(
            '/bd/v1/meitu/list',
            {
                page:that.data.page,
                type:typeId
            }
        ).then(data => {
            if(data.error_code === 0){
                let list = that.data.tuData.concat(data.data.list)
                that.setData({
                    tuData:list,
                    page_total_number: data.data.page.page_total_number,
                    pageCurrent: data.data.page.page_current
                })
                if(that.data.page_total_number===0 && that.data.tuData==''){
                    that.setData({
                        tuDataWn: true
                    })
                }
                if(data.data.page.page_total_number>0){
                    if (data.data.list.length <20) {
                        this.setData({
                            nomore: 3
                        })
                    } else {
                        this.setData({
                            nomore: 2
                        })
                    }
                } else {
                    this.setData({
                        nomore: 2
                    })
                }
                canUseReachBottom = true;
            } else {
                swan.showToast({
                    title: '网络异常',
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    lower() {
        if (!canUseReachBottom) return;
        let that = this
        let pageCurrent = that.data.pageCurrent
        let page_total_number = that.data.page_total_number
        if (pageCurrent < page_total_number) {
            that.setData({
                page: pageCurrent + 1
            }, function () {
                that.chinalist(that.data.typeId)
            })
        }
    },
    scroll: function(e) {
        let backTopValue = e.detail.scrollTop > 1200 ? true : false
        this.setData({
            backTopValue: backTopValue
        })
    },
    backTop: function(e){
        this.setData({
            topNum: 0
        })
    },
    // 选项分类接口数据
    cartList(typeId) {
        let that = this
        cartTu(
            '/bd/v1/meitu/list/options',
            {
                type:typeId
            }
        ).then(data => {
            if(data.error_code === 0){
                that.setData({
                    title:data.data.title,
                    xgtList: data.data.category_list,
                    catelist:data.data.category_list[0].children
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
                that.titleFn()
                for(var i = 0; i < that.data.xgtList.length; i++) {
                    for(var j = 0; j < that.data.xgtList[i].children.length; j++) {
                        if(that.data.xgtList[i].children[j].url_full == that.data.typeId){
                            that.setData({
                                ['xgtList['+ i +'].name']: that.data.xgtList[i].children[j].name,
                                colorId:that.data.xgtList[i].children[j].id,
                            })

                        }
                    }
                }

            } else {
                swan.showToast({
                    title: '网络异常',
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    // 点击图片旋转180度效果
    clickPerson:function(e){
        let index = e.currentTarget.dataset.index
        if(this.data.currentIndex || this.data.currentIndex ===0){
            if (this.data.currentIndex === index) {
                this.setData({
                    currentIndex: ''
                })
            } else {
                this.setData({
                    currentIndex: index,
                    catelist:this.data.xgtList[index].children,
                })
            }
        } else {
            this.setData({
                currentIndex: index,
                catelist:this.data.xgtList[index].children,
            })
        }
    },
    // 让当前点击的内容项变色
    toogleColor(e){
        let untils=e.currentTarget.dataset.until
        this.setData({
            currentIndex:''
        })
        swan.navigateTo({
            url: `../../tu/tu/tu?type=${untils}`
        });
    },
    clickoptic(){
        this.setData({
            currentIndex:''
        })
    },
    // 动态标题
    titleFn () {
        swan.setNavigationBarTitle({
          title: this.data.title
        })
    },
    // 跳转详情
    godetail(e){
        let flags=e.currentTarget.dataset.flag
        swan.navigateTo({
            url: `../../tu/detail/detail?id=${flags}`
        });
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        let typeId=options.type
        this.setData({
            typeId:typeId,
            options
        })
        this.chinalist(typeId)
        this.cartList(typeId)
    },
    onShow: function() {
        this.setData({
            typeId:this.data.options.type
        })
    }
});