// userCollections.js
let app = getApp();
let apiUrl = app.getApiUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    markList:[],
    isHide:true,
    getcount:0,
    userInfo:null,
    getVideoId:[],
    emptyHide:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    app.getUserInfo(function (userInfo) {
      that.setData({ userInfo: userInfo })
      if (userInfo.userId) {
        wx.request({
          url: apiUrl+'/zxjt/likestore',
          data: {
            uid: userInfo.userId,// userInfo.userId
            limit: 1
          },
          header: { 'content-type': 'application/json' },
          method: 'GET',
          dataType: 'json',
          success: function (res) {
            if (res.data.data.length<=0){
              that.setData({ emptyHide: false })
            }else{
              for (let i = 0; i < res.data.data.length; i++) {
                res.data.data.checked = false;
              }
              that.setData({ markList: res.data.data, emptyHide: true })
            }
          }
        });
      }
    });
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  },
  /**
   * 用户点击我的收藏列表跳转到视频播放页面
   */
  toDetailPlay:function (e) {
    wx.navigateTo({
      url: '../detail_play/detail_play?id='+e.currentTarget.dataset.id
    });
  },
  // checkbox是否选择
  changeInfo:function(e){
    let id = e.detail.value;
    console.log(e.detail.value)
    let newMarkList = this.data.markList;
    if(this.data.isHide){
      
      for (let i = 0; i < newMarkList.length;i++){
        if (newMarkList[i].video_id = id[0]){
          newMarkList[i].checked = false;
        }
      }
      id = [];
      this.setData({markList: newMarkList})
    }
    this.setData({ getcount: id.length, getVideoId: id})
  },
  // 编辑
  edite:function(){
    this.toDetailPlay=function(){return};
    this.setData({isHide:false});
  },
  // 全选
  selectAll:function(){
    let newGetVideoId=[];
    let newMarkList = this.data.markList;
    for (let i = 0; i < newMarkList.length;i++){
      newMarkList[i].checked = true;
      newGetVideoId.push(newMarkList[i].video_id)
    }
    // console.log(newGetVideoId)
    this.setData({ markList: newMarkList, getcount: newMarkList.length, getVideoId: newGetVideoId})

  },
  // 删除---我收藏的
  delect: function () {//wxapp.qizuang.com/zxjt/editfav?uid=4&vids=1,2,3&type=del
    let that = this, vidsList;

    if (that.data.getVideoId.length>1){
      vidsList = that.data.getVideoId.join(',');
    } else if (that.data.getVideoId.length==1){
      vidsList = that.data.getVideoId[0]
    }

    wx.request({
      url: apiUrl+'/zxjt/editfav',
      data:{
        'uid': that.data.userInfo.userId,
        'vids': vidsList,
        'type':'del'
      },
      header: { 'content-type': 'application/json' },
      dataType: 'json',
      success: function (res) {
        wx.request({
          url: apiUrl+'/zxjt/likestore',
          data: {
            uid: that.data.userInfo.userId,// userInfo.userId
            limit: 1
          },
          header: { 'content-type': 'application/json' },
          method: 'GET',
          dataType: 'json',
          success: function (res) {
            console.log(res.data)
            if (res.data.data.length>0){
              for (let i = 0; i < res.data.data.length; i++) {
                res.data.data.checked = false;
              }
              that.setData({ markList: res.data.data,emptyHide: true })
            }else{
              that.setData({ markList: [], emptyHide:false })
            }
            
          }
        });
        that.setData({ getcount: 0});
      }
    });
    
  },
  // 取消全选
  cancel:function(){
    this.setData({ isHide: true });
    let newMarkList = this.data.markList;
    for (let i = 0; i < newMarkList.length; i++) {
      newMarkList[i].checked = false;
    }
    this.setData({ markList: newMarkList,getVideoId:[],getcount:0 })
    this.toDetailPlay=function (e) {
      wx.navigateTo({
        url: '../detail_play/detail_play?id=' + e.currentTarget.dataset.id,
      })
    }
  }
})