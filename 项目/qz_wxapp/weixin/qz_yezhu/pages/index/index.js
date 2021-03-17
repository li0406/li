//index.js
//获取应用实例
const app = getApp()
let apiUrl = app.getApiUrl();
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
  data: {
    userInfo: {},
    isHideCity:true,
    radioItems: [
      { name: 'lianxradio01', value: '已与您联系', num: 1,checked:false },
      { name: 'lianxradio02', value: '已为您量房', num: 2, checked: false}
    ],
    delcompdata:null,
    radiobiaoji:'',
    dingdanid:'',
    gongsid:'',
  },
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  onLoad: function (options) {
    var that = this  
    that.setData({
      dingdanid: options.order_no,
    })
    wx.request({
      url: apiUrl + '/v1/myrenovation/getcompanylist',
      data: { orderid:options.order_no },
      header: {
        'content-type': 'application/json',
        'token': app.globalData.loginInfo.token
      },
      method:"GET",
      success: function (res) {
        if (res.data.error_code == 0) {
          console.log(res.data.data.list)
          that.setData({
            delcompdata: res.data.data.list
          })
        }
      }
    });
  },
  tancselect:function(e){
     var that=this;
     that.setData({
       isHideCity:false,
       gongsid: e.currentTarget.dataset.gsid
     })
  },
  yingyclose:function(){
    var that = this;
    that.setData({
      isHideCity: true,
    })
  },
  quxiaotc:function(){
    var that = this;
    that.setData({
      isHideCity: true,
    })
  },
  gereninfo: function () {
    wx.navigateTo({
      url: "../information/information"
    })
  },
  savebc: function () {
    var that = this;
    if (that.data.radiobiaoji == ""){
      alertViewWithCancel("提示","请选择已服务项", function () { }); 
      return
    }
    wx.request({
      url: apiUrl +'/v1/myrenovation/signcompany',
      method:'POST',
      data: { 
        orderid: that.data.dingdanid, 
        comid: that.data.gongsid, 
        signdata: that.data.radiobiaoji
      },
      header:{
        "content-type":"application/json",
        "token": app.globalData.loginInfo.token
      },
      success:function(res){
        if (res.data.error_code==0){
           for (let i = 0; i < that.data.delcompdata.length; i++){
             if (that.data.delcompdata[i].comid == that.data.gongsid){
               if (that.data.radiobiaoji==1){
                 that.setData({
                   ["delcompdata["+i+"].lianxi"]:1
                 })
               }else{
                 that.setData({
                   ["delcompdata[" + i + "].liangfang"]: 1
                 })
               }
             }
           }
          wx.showToast({
            title: '标记成功',
            icon: 'success',
            duration: 1200
          }); 
        } 
        that.setData({
          ['radioItems[0].checked']: false,
          ['radioItems[1].checked']: false,
          radiobiaoji: ''
        });
      },
      fail:function(res){
        alertViewWithCancel("提示", res.data.error_msg, function () { }); 
      }
    })
    that.setData({
      isHideCity: true,
    })
  },
  radioChange: function (e) {
    var checked = e.detail.value
    var changed = {}
    for (var i = 0; i < this.data.radioItems.length; i++) {
      if (checked.indexOf(this.data.radioItems[i].name) !== -1) {
        changed['radioItems[' + i + '].checked'] = true
      } else {
        changed['radioItems[' + i + '].checked'] = false
      }
    }
    this.setData({
      radiobiaoji: checked
    })
  },
  noqianyue:function(e){
    wx.navigateTo({
      url: '../companynoqy/companynoqy?comid=' + e.currentTarget.dataset.comid,
    })
  }
})
