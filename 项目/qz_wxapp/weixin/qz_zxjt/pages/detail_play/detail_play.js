// detail_play.js
let app = getApp();
let apiUrl = app.getApiUrl();
let imgUrl = app.getImgUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    classname:'comment_box',
    isHide:false,
    isShow:true,
    playImgHide:false,
    playVideoHide:true,
    commentTip:true,
    commentBool:false,
    markStyle:true,
    detailInfo:{},// 视频、讲师的信息
    commentInfo:[],// 评论的信息
    markImg:'../../img/mark-icon.png',
    markImgOk: "../../img/mark-ok-icon.png",
    userInfo: null,
    inputValue:'',
    pageId:'',
    imgUrl: imgUrl
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.setData({ pageId: options.id});
    // 请求 视频信息 讲师信息
    wx.request({
      url: apiUrl +'/zxjt/video/id/'+options.id,
      header: { 'content-type': 'application/json'},
      dataType: 'json',
      success: function(res) {
        that.data.detailInfo = res.data.data
        that.setData({ detailInfo: that.data.detailInfo});
        wx.setNavigationBarTitle({
          title: res.data.data.title
        })
      }
    })
    // 请求 评论 信息 id 338 options.id
    wx.request({
      url: apiUrl +'/zxjt/comment/act/get/id/' +options.id ,
      header: { 'content-type': 'application/json' },
      dataType: 'json',
      success: function (res) {
        console.log(res)
        let comArr = res.data.data;
        if (comArr.length<=0){
          that.setData({ commentTip: false, commentBool:true})
        }else{
          that.setData({ commentInfo: comArr, commentTip: true, commentBool: false});
        }
        
      },
      fail:function(){
        that.setData({ commentTip: false })
      }
    });
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
        // 请求用户是否收藏
        wx.request({
          url: apiUrl + '/zxjt/favorite/act/check/vid/' + that.data.pageId + '/uid/' + userInfo.userId,
          header: { 'content-type': 'application/json' },
          dataType: 'json',
          success: function (res) {
            if (res.data.status == 1)
              that.setData({ markStyle: false })
          }
        })
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
    wx.stopPullDownRefresh()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    if (res.from === 'button') {
      // 来自页面内转发按钮
      
    }
    wx.updateShareMenu({
      withShareTicket: true,
      success() {
      }
    });
  },
  // 滑到顶部
  upper:function(e){
    if (this.data.userInfo.userId) {
      this.setData({ isHide: false });
      this.setData({ isShow: true });
    } else {
      this.setData({ isHide: false })
    }
  },
  // 滑到底部
  lower:function(e){
    if (this.data.userInfo.userId) {
      this.setData({ isShow: false });
      this.setData({ isHide: true });
    }else{
      this.setData({ isShow: true })
    }
  },
  /**
   * 用户点击转发
   */
  shareInfo:function(){
    
  },
  /**
   * 用户点击收藏
   */
  mark:function(){
    let that = this;
    if (that.data.userInfo.userId){
      wx.request({ // wxapp.qizuang.com/zxjt/editfav?uid=4&vids=321&type=add
        url: apiUrl+'/zxjt/editfav',
        data:{
          "vids": that.data.pageId,
          "uid": that.data.userInfo.userId,
          "type":'add'
        },
        header: { 'content-type': 'application/json' },
        dataType: 'json',
        success: function (res) {
          // console.log(res.data)
        }
      })

      if (that.data.markStyle) {
        that.setData({ markStyle: false })
        wx.showToast({
          title: "收藏成功"
        })
      } else {
        that.setData({ markStyle: true });
        wx.showToast({
          title: "取消收藏"
        });
        wx.request({ // wxapp.qizuang.com/zxjt/editfav?uid=4&vids=321&type=del
          url: apiUrl+'/zxjt/editfav',
          data: {
            "vids": that.data.pageId,
            "uid": that.data.userInfo.userId,
            "type": 'del'
          },
          header: { 'content-type': 'application/json' },
          dataType: 'json',
          success: function (res) {
            // console.log(res.data)
          }
        })
      }
    }
  },
  /**
   * 用户点击视频播放
   */
  hideCover:function(e){
    let oldArrId=[];
    let arrInfo =[];
    let Info = this.data.detailInfo;
    let newId = e.currentTarget.dataset.id;

    arrInfo = app.getNewStorage('arrInfoKey');
    oldArrId = app.getNewStorage('arrIdKey');
    if (oldArrId){
      if (oldArrId.length>0){
        if (oldArrId.indexOf(newId) != -1) {
          // console.log('已浏览过')
        } else {
          oldArrId.push(newId);
          arrInfo.push(Info);
          app.setNewStorage('arrIdKey', oldArrId);
          app.setNewStorage('arrInfoKey', arrInfo);
        }
      }
    }else{
      oldArrId = [];
      arrInfo = [];
      oldArrId.push(newId);
      arrInfo.push(Info);
      app.setNewStorage('arrIdKey', oldArrId);
      app.setNewStorage('arrInfoKey', arrInfo);
    }
    this.setData({playImgHide:true, playVideoHide:false});

  },
  toDes:function(){
    wx.navigateTo({
      url: '../zuangxsj/zuangxsj',
    })
  },
  // 发布评论
  sendcomment:function(){
    let that = this;
    // console.log(that.data.userInfo.userId);
    if (that.data.inputValue){
        console.log(that.data.detailInfo)
      wx.request({
        url: apiUrl + '/zxjt/comment/act/add/id/' + that.data.pageId,
        data: {
          uid: that.data.userInfo.userId,
          content: that.data.inputValue
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        dataType: 'json',
        success: function (res) {
          // 重新请求 评论 信息 options.id
          wx.request({
            url: apiUrl + '/zxjt/comment/act/get/id/' + that.data.pageId,
            header: { 'content-type': 'application/json' },
            dataType: 'json',
            success: function (res) {
              let comArr = res.data.data;
              if (res.data.data < 0) {
                that.setData({ commentTip: false, commentBool: true })
              } else {
                that.setData({ commentInfo: comArr, commentTip: true, commentBool: false });
              }
            },
            fail: function () {
              that.setData({ commentTip: false })
            }
          });
        }
      })
    }else{
      wx.showToast({
        icon: 'error',
        title: "请输入评论内容"
      })
    }
  },
  // 获取评论框的内容
  valueHandle:function(e){
    this.setData({ inputValue: e.detail.value})
  }
})