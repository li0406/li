const app = getApp()
Page({
  data: {
    list:{},
    imageList:[],
    totalNum:0,
    currentIndex: 1
  },
  onLoad: function (options) {
    let list = JSON.parse(options.imgList)
    let type = options.type
    if(type == 1){
      wx.setNavigationBarTitle({  
        title:'案例详情'  
      })  
    }else{
      wx.setNavigationBarTitle({  
        title:'设计师详情'  
      })  
    }
    this.setData({
      list: list,
      imageList: list.imgs,
      totalNum: list.imgs.length
    })
  },
  swiperChange(e) {
    this.setData({
      currentIndex: e.detail.current + 1
    })
  },
  onShow: function () {
    
  }
})
