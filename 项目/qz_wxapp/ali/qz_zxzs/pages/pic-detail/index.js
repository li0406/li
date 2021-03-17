const app = getApp();
let apiUrl = app.getApiUrl();

Page({
  data: {
    //轮播图部分
    indicatorDots : true,
    autoplay : true,
    interval : 3000,
    duration : 1000,
    circular : true,
    swiperHeight : "113px",
    imgUrls : null,
    title : "",
    picNumber : 1,
    imgUrls : null,
    index : 1,
    picId : ""
  },
  onLoad : function(options){
    this.setData({
        picId : options.id
    });
  },
  onShow : function(){
      let that = this;
        my.httpRequest({
            url: apiUrl+'/appletcarousel/meituDetails',
            data: {
                //id: options.id
                id : that.data.picId
            },
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                if(res.data.info.length<2){
                    that.setData({
                        indicatorDots : false
                    });
                }
                that.setData({
                    imgUrls: res.data.info,
                    picNumber: res.data.info.length,
                    title: res.data.info[0].title
                });
                
                my.setNavigationBar({
                    title: that.data.title,
                })
            },
            fail: function () {
                console.log('error!!!!!!!!!!!!!!')
            }
        })
  },
    changeCurrent : function(e){
        let current = e.detail.current;
        this.setData({
            index : current+1
        });
    },
    toXgtDetail : function(e){
        let id = e.currentTarget.dataset.id,
            title = e.currentTarget.dataset.title;
        my.navigateTo({
            url: '../xgt-detail/xgt-detail?id=' + id + '&title=' + title
        });
    },
});
