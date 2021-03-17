// pages/play_detail/play_detail.js
let app = getApp();
let apiUrl = app.getAppApiUrl();
const imgUrl = app.getImgUrl();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    autoplay: false,
    detailInfo:{},
    imgUrl: imgUrl,
    bofangkzpd:true,
    cover_imgstatus:true,
    bofangtsxx:false,
    pageId:null,
    bofangshujv:null,
    recommendshujv:[],
    imgUrl: imgUrl,
    bool:true,
    userId:null
  },
  

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.setData({ 
      pageId: options.id,
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
    let that=this;
    let bool = true;
    that.ajaxsend();
    
    
  },
  tiaozbj:function(){
    tt.navigateTo({
      url:'../baojia/baojia'
    })
  },
  playVideo:function(){
    let that = this;
    that.videoContext = tt.createVideoContext('myVideo');
    that.setData({
      cover_imgstatus: false,
      bofangkzpd: false
    });
    that.videoContext.play();
    tt.getNetworkType({
      success: function (res) {
        if (res.networkType == "3g" || res.networkType == "4g" || res.networkType == "2g") {
          that.setData({
            bofangtsxx: true,
          })
          setTimeout(function () {
            that.setData({
              bofangtsxx: false
            })
          }, 1000)
        }
      }
    })
  },
  stopVideo:function(){
    let that = this;
    that.videoContext = tt.createVideoContext('myVideo');
    that.setData({
      bofangkzpd: true,
      bofangtsxx: false
    });
    that.videoContext.pause();
  },
  ajaxsend:function(){
    let that=this;

        tt.request({
          url: apiUrl + '/appletgonglue/getVideoDetail',
          header: { 'content-type': 'application/json' },
          dataType: 'json',
          data: { classid: that.data.pageId, classtype: 11 },
          success: function (res) {
            let videoRecommend = (res.data.data.videoRecommend).slice(0, 3);
            that.setData({
              bofangshujv: res.data.data.videoDetail,
              recommendshujv: videoRecommend,
            });
            // setTimeout(function () {
            //   that.playVideo()
            // }, 1000);
          }
        })

      
    
  },
  /**
    * 用户点击右上角分享
    */
  onShareAppMessage: function () {

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
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/play_detail/play_detail',
      success: function (res) { },
      fail: function (res) { }
    }
  },
  moreSp:function () {
    tt.navigateTo({
      url: '../play/play?id=1',
    })
  },
  toSpDetail: function (e) {
    let passid = e.currentTarget.dataset.id;
    tt.navigateTo({
      url: "../play_detail/play_detail?id=" + passid
    })
  },
})