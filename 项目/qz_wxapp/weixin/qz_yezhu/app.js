//app.js
var config = require('./config');

App({
  onLaunch: function (options) {
    var that=this;
  },
  //设置缓存到期时间点
  setStorageEndTime(long){ //接收参数单位为毫秒,指的是缓存时长
    if (!isNaN(long) && long>0 ){
      var nowTime = new Date().getTime() + long*1000
      return nowTime
    }
    return 0;
  },
  getNewStorage(key,cb){
      wx.getStorage({
        key: key,
        success: function(res) {
          let nowDateTime = new Date().getTime();
          if(nowDateTime - res.data.endTime < 0) {
            cb(res.data);
          }else{
            wx.removeStorageSync(key);
            cb(0);
          }
        },
        fail:function(res) {
          cb(0)
        }
      })
  },
  globalData: {
    loginInfo: {
      token:"",
      tel:"",
      userInfo:""
    },
    currentOrderId:'',
  },
  getApiUrl: function () {
    let apiUrl = config.service.host_api;
    return apiUrl
  },
  getImgUrl: function () {
    let imgUrl = config.service.host_img;
    return imgUrl
  },
  getAPPid: function () {
    //局部小程序配置: ① 修改appid
    let appid = config.service.appid;
    return appid
  }
})