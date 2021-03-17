// pages/companyprogress/companyprogress.js
const app = getApp()
let apiUrl = app.getApiUrl();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    arrehide:[false,true,true],
    zxjindu:null,
    designtugao:null,
    zsteam:null,
    orderhao:null,
    companyInfo:{
      comname: '',
      logo: ''
    },
    largeImgUrl:"",
    fadeLarge:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    that.setData({
      orderhao: options.order_no
    });
    if(options.type){
      let index = parseInt(options.type);
      let arr = [true, true, true];
      arr[index-1] = false;
      that.setData({ arrehide: arr });
    }
    wx.request({
      url: apiUrl +'/v1/myrenovation/showcompanyinfo',
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        'token': app.globalData.loginInfo.token
      },
      data:{
        comid: options.company_id
      },
      success:function(res){
        if(res.data.error_code===0){
          that.setData({
            'companyInfo.comname': res.data.data.info.comname,
            'companyInfo.logo': res.data.data.info.logo
          })
        }
      }
    });
    wx.request({
      url: apiUrl + '/v1/buildplan',
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        'token': app.globalData.loginInfo.token
      },
      method: "POST",
      data: { 
        orderid: options.order_no, 
        tel: app.globalData.loginInfo.tel 
       },
      success: function (res) {
        that.setData({
          zxjindu: res.data.data.build,
          designtugao: res.data.data.house_design,
          zsteam: res.data.data.team,
        })
      }
    }); 
  },
  setLargeImg:function(e){
    let url = e.currentTarget.dataset.url;
    this.setData({
      largeImgUrl:url,
      fadeLarge:false
    })
  },
  cancelLarge:function(){
    this.setData({
      largeImgUrl: "",
      fadeLarge: true
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  tabqiehuan:function(e){
    let index = e.currentTarget.dataset.index,
    that = this,
    arr = [true, true, true];
    arr[index] = false;
    that.setData({ arrehide: arr });
  },
  gereninfo:function(){
    wx.navigateTo({
      url:"../information/information"
    })
  },
  shigongteam:function(){
    var that=this;
    wx.navigateTo({
      url: "../constructionteam/constructionteam?order=" + that.data.orderhao
    })
  },
  myfeedback:function(e){
    var that=this;
    wx.navigateTo({
      url: "../feedback/feedback?shigongid=" + e.currentTarget.dataset.shigong
    })
  },
  yanshou:function(e){
    var that=this;
    wx.navigateTo({
      url: "../yanshou/yanshou?shigongid=" + e.currentTarget.dataset.shigongid + '&dingdanid=' + that.data.orderhao
    })
  }
})