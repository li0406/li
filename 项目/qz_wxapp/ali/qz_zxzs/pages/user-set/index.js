const app = getApp(),
      apiUrl = app.getApiUrl();;

Page({
  data: {
    size : 0
  },
  onLoad : function(){
    let that = this;
    my.getStorageInfo({
        success: function (res) {
            that.calcStorageSize(res.currentSize);
        }
    });
  },
  clearStorage : function(){
    let that = this;
    my.showToast({
        content: '清除成功',
        icon: 'success',
        duration: 1000
    })
    try {
        my.clearStorageSync();
        my.getStorageInfo({
            success: function (res) {
                that.calcStorageSize(res.currentSize);
            }
        });
    } catch (e) {
        // Do something when catch error
    }
  },
  //计算缓存并设置前台显示数据
  calcStorageSize : function(size){
    let storageSize = ""; 
    // my.alert({
    //     content : "缓存大小："+size
    // });
    if(parseFloat(size) < 102){
        storageSize = size + "KB"
    }else{
        storageSize = Math.floor(parseFloat(size) / 1024) + "M";
    }
    this.setData({ 
        size: storageSize
    })
  }

});
