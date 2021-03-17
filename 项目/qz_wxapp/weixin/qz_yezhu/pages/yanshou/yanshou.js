// pages/yanshou/yanshou.js
const app = getApp();
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

  /**
   * 页面的初始数据
   */
  data: {
    radioItems: [
      { name: 'hege', value: 1,text:'验收合格'},
      { name: 'nohege', value: 2,text:'不合格'}
    ],
    selecthide:true,
    selectstatus:0,
    yanshoums:'',
    shigongid:'',
    picarray: [],
    order_hao:'',
    fullImage:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    that.setData({
      shigongid: options.shigongid,
      order_hao: options.dingdanid
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  radioChange: function (e) {
    var that=this;
    var checked = e.detail.value
    var changed = {}
    for (var i = 0; i < this.data.radioItems.length; i++) {
      if (checked.indexOf(this.data.radioItems[i].name) !== -1) {
        changed['radioItems[' + i + '].checked'] = true
      } else {
        changed['radioItems[' + i + '].checked'] = false
      }
    }
    this.setData(changed);
    if (e.detail.value==2){
      this.setData({
        selecthide:false
      })
    }else{
      this.setData({
        selecthide: true,
        picarray:[],
        yanshoums:""
      })
    }
   that.setData({
     selectstatus: parseInt(e.detail.value)
   })
  },
  uploadsctp: function () {
    var that = this;
    wx.chooseImage({
      count: 1, // 默认1 
      sizeType: ['original'], // 可以指定是原图还是压缩图，默认二者都有 
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有 
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片 
        var tempFilePaths = res.tempFilePaths;
        if (res.tempFiles[0].size > 5120000){
          alertViewWithCancel("提示","图片大小不能大于5M", function () { });
          return
        }
        wx.showLoading({
          title: '上传中',
        });
        wx.uploadFile({
          url: apiUrl + '/uploadimg', //此处换上你的接口地址 
          filePath: tempFilePaths[0],
          name: 'file',
          header: {
            "Content-Type": "multipart/form-data",
          },
          formData: {
            'user': 'test'  //其他额外的formdata，可不写 
          },
          success: function (res) {
            var data = JSON.parse(res.data);
            if(data.error_code == 0){
              var newdata = data.data.imgurl;
              var imgArray = that.data.picarray;
              imgArray.push(newdata);
              that.setData({ picarray: imgArray }, function () {
                wx.showToast({
                  title: '成功',
                  duration: 1200
                });
                wx.hideLoading()
                if (that.data.picarray.length == 9) {
                  that.setData({
                    fullImage: false
                  });
                }
              });
            }else{
              alertViewWithCancel("提示", res.data.error_msg, function () { });
              wx.hideLoading()
            }
          },
          fail: function (res) {
            wx.hideLoading()
            alertViewWithCancel("提示",res, function () { });
          },
        })
      }
    })
  },  
  fankuiyij:function(e){
    let that=this;
    that.setData({
      yanshoums: e.detail.value
    })
  },
  tijiao:function(e){
    let that=this;
    if (that.data.selectstatus == 0){
      alertViewWithCancel("提示", "请选择验收状态", function () { });
      return
    }
    if (that.data.selectstatus == 2 && that.data.yanshoums == ""){
      alertViewWithCancel("提示", "请填写反馈意见", function () { });
      return 
    }
    
    wx.request({
      url: apiUrl +'/v1/myrenovation/check',
      method:'POST',
      data: { 
        buildid: that.data.shigongid, 
        status: that.data.selectstatus, 
        cause: that.data.yanshoums,
        fail_img:that.data.picarray
      },
      header:{
        'content-type':'application/json',
        'token': app.globalData.loginInfo.token
      },
      success:function(res){
        if (res.data.error_code==0){
          // alertViewWithCancel("提示", res.data.error_msg, function () { });
          wx.navigateTo({
            url: "../companyprogress/companyprogress?order_no=" + that.data.order_hao
          })
        }else{
          alertViewWithCancel("提示", res.data.error_msg, function () { });
        }
      },
      fail:function(res){
        alertViewWithCancel("提示", res.data.error_msg, function () { });
      }
    })
  }
})