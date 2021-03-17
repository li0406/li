// pages/login/login.js
const app = getApp()
let apiUrl = app.getApiUrl();
let storagehc = app.globalData.token;
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    timer: '',//定时器名字
    countDownNum: '60',//倒计时初始值
    yincpd:true,
    phoneval:"",
    yanzcode:"",
    erwmpanduan:true,
    jiashujv:[{a:1},{b:2}],
    userInfo: null,
    hasInfo: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    wx.getStorage({
      key: 'userInfo',
      success: function (res) {
        app.globalData.loginInfo.userInfo = res.data;
        that.setData({
          hasInfo: true
        })
      }
    });
  },
  sendcode:function(){
    let that=this;
    let newReg = new RegExp("^((13[0-9])|(14[5,7,9])|(15[0-3,5-9])|(17[0,1,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
    if (that.data.phoneval.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () { });
      return;
    } 
    if (!newReg.test(that.data.phoneval)) {
      alertViewWithCancel("提示", "抱歉，手机号输入错误", function () { });
      return;
    }
    wx.request({
      url: apiUrl + '/sendsms',
      data: { tel: that.data.phoneval},
      header: {
        'content-type': 'application/json'
      },
      method: "POST",
      success: function (res) {
        if (res.data.error_code==0){
          that.countDown();
          that.setData({
            yincpd: false,
          })
        }
      }
    });
  },
  countDown: function () {
    let that = this;
    let countDownNum = that.data.countDownNum;//获取倒计时初始值
    //如果将定时器设置在外面，那么用户就看不到countDownNum的数值动态变化，所以要把定时器存进data里面
    that.setData({
      timer: setInterval(function () {//这里把setInterval赋值给变量名为timer的变量
        //每隔一秒countDownNum就减一，实现同步
        countDownNum--;
        //然后把countDownNum存进data，好让用户知道时间在倒计着
        that.setData({
          countDownNum: countDownNum
        })
        //在倒计时还未到0时，这中间可以做其他的事情，按项目需求来
        if (countDownNum == 0) {
          clearInterval(that.data.timer);
          //这里特别要注意，计时器是始终一直在走的，如果你的时间为0，那么就要关掉定时器！不然相当耗性能
          //因为timer是存在data里面的，所以在关掉时，也要在data里取出后再关闭
          that.setData({
            countDownNum:'60',
            yincpd:true,
          })
          //关闭定时器之后，可作其他处理codes go here
        }
      }, 1000)
    })
  },
  getphone:function(e){
    let that=this;
    this.setData({ phoneval: e.detail.value });
  },
  getcode:function(e){
    this.setData({ yanzcode: e.detail.value });
  },
  bindgetuserinfo: function(e){
    let that = this;
    if(e.detail.userInfo){
      that.loginOperation();
      wx.setStorage({
        key: 'userInfo',
        data: e.detail.userInfo
      });
      that.setData({
        hasInfo:true
      });
    }
  },
  dengluwk:function(){
    if(this.data.hasInfo){
      this.loginOperation();
    }
  },
  loginOperation:function(){
    let that = this;
    let newReg = new RegExp("^((13[0-9])|(14[5,7,9])|(15[0-3,5-9])|(17[0,1,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
    if (that.data.phoneval.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () { });
      return;
    }
    if (!newReg.test(that.data.phoneval)) {
      alertViewWithCancel("提示", "抱歉，手机号输入错误", function () { });
      return;
    }
    if (that.data.yanzcode.length < 1) {
      alertViewWithCancel("提示", "请输入验证码", function () { });
      return;
    }
    wx.request({
      url: apiUrl + '/v1/login/login',
      data: { phonenum: that.data.phoneval, code: that.data.yanzcode },
      header: {
        'content-type': 'application/json'
      },
      method: "POST",
      success: function (res) {
        if (res.data.error_code == 0) {
          app.globalData.loginInfo.tel = that.data.phoneval; 
          app.globalData.loginInfo.token = res.data.data.jwt_token;
          if (res.data.data.hadorder == 1) {;
              wx.redirectTo({
                url: '../myorder/myorder',
              })
              wx.setStorage({
              key: "loginInfo",
              data: {
                token: res.data.data.jwt_token,
                tel: that.data.phoneval,
                endTime: app.setStorageEndTime(604800)
              }
            })
          } else {
            that.setData({
              erwmpanduan: false,
            });
          }
        } else {
          alertViewWithCancel("提示", res.data.error_msg, function () { });
        }
      }
    });
  },
  dianjiyc:function(){
    this.setData({
      erwmpanduan: true,
    })
  },
  previewImage:function(e){
    var current = e.target.dataset.src;
    wx.previewImage({
      urls: [current]
    })
  },
  onShareAppMessage: function () {
  }
})