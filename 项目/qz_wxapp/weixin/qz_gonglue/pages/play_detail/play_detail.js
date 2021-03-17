// pages/play_detail/play_detail.js
let app = getApp();
let apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
const collect = require('../../utils/collectTool.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
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
    userId:null,
    setTimeoutPlay:false
  
  },
  

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    collect.collect.collectInit(this, "recommendshujv");//列表收藏引用
 
    let that = this;

    app.getUserInfo(function (res) {     
      that.setData({ userId: res.userId });
    })

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
    
    // 判断这篇文章是否点过赞
    let user = app.getNewStorage('user');
    if (user) {
      for (let i = 0; i < user.length; i++) {
        if (user[i] == that.data.pageId) {
          bool = false;

          that.setData({ bool: false })
          break;
        } else {
          bool = true;
        }
      }
      return bool;
    }
    
  },
  tiaozbj:function(){
    wx.navigateTo({
      url:'../jsq/jsq'
    })
  },
  playVideo:function(){
    let that = this;
    that.videoContext = wx.createVideoContext('myVideo');
    that.setData({
      cover_imgstatus: false,
      bofangkzpd: false
    });
    that.videoContext.play();
    wx.getNetworkType({
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
    that.videoContext = wx.createVideoContext('myVideo');
    that.setData({
      bofangkzpd: true,
      bofangtsxx: false
    });
    that.videoContext.pause();
  },
 

  ajaxsend:function(){
    let that=this;

    wx.request({
      url: apiUrl + '/appletgonglue/getVideoDetail',
      header: { 'content-type': 'application/json' },
      dataType: 'json',
      data: { classid: that.data.pageId,userid: that.data.userId,classtype:11 },
      success: function (res) {
        that.setData({
          bofangshujv: res.data.data.videoDetail,
          recommendshujv: res.data.data.videoRecommend,
        });
        console.log(that.data.bofangshujv)
        setTimeout(function(){
          that.setData({
            setTimeoutPlay: true
          },2500);
        });
        collect.collect.collectDetailInit(that,"bofangshujv");//详情收藏引用
      }
    })

  },

  dianzan: function () {
    let that = this;
    let user = app.getNewStorage('user');
    let bofangshujv = that.data.bofangshujv;
    let bool = true;
    console.log(that.data.bofangshujv.id)
    if (user) {
      for (let i = 0; i < user.length; i++) {
        if (user[i] == bofangshujv.id) {
          bool = false;

          that.setData({ bool: false })
          break;
        } else {
          bool = true;
        }
      }
      if (bool) {
        wx.request({
          url: apiUrl + '/appletgonglue/doVideoLikes',
          data: {
            id: that.data.bofangshujv.id
          },
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            if (res.data.state === 1) {
              let dianzanNow = that.data.bofangshujv;
              dianzanNow.likes = parseInt(dianzanNow.likes) + 1;
              console.log(dianzanNow)
              that.setData({
                bofangshujv: dianzanNow,
                bool: false
              });
              user.push(that.data.bofangshujv.id)
              app.setNewStorage('user', user);
            }
          }
        });
      } else {
        wx.showModal({
          title: '您已经点过了',
          showCancel: false,
          success: function (res) {

          }
        });
      }
    } else {
      let user = [];
      wx.request({
        url: apiUrl + '/appletgonglue/doVideoLikes',
        data: {
          id: that.data.bofangshujv.id
        },
        header: {
          'content-type': 'application/json'
        },
        success: function (res) {
          if (res.data.state === 1) {
            let dianzanNow = that.data.bofangshujv;
            dianzanNow.likes = parseInt(bofangshujv.likes) + 1;
            console.log(dianzanNow)
            that.setData({
              bofangshujv: dianzanNow,
              bool: false
            });
            user.push(that.data.bofangshujv.id)
            app.setNewStorage('user', user);
          }
        }
      });
    }

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
  onShareAppMessage: function () {
  
  }
})