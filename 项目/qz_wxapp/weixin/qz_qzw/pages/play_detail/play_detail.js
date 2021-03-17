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
    wx.getStorage({
      key: 'userId',
      success: function(data) {
        wx.request({
          url: apiUrl + '/appletgonglue/getVideoDetail',
          header: { 'content-type': 'application/json' },
          dataType: 'json',
          data: { classid: that.data.pageId, userid: data.data, classtype: 11 },
          success: function (res) {
            let videoRecommend = (res.data.data.videoRecommend).slice(0, 3);
            that.setData({
              bofangshujv: res.data.data.videoDetail,
              recommendshujv: videoRecommend,
            });
            collect.collect.collectDetailInit(that, "bofangshujv");//详情收藏引用
            collect.collect.collectInit(that, "recommendshujv");//列表收藏引用
            setTimeout(function () {
              that.playVideo()
            }, 1000);
          }
        })
      },
      fail: function(){
        app.newSq(that,function(data){
          let userId="";
          if(data!==0){
            userId=data.userId
          }
          wx.request({
            url: apiUrl + '/appletgonglue/getVideoDetail',
            header: { 'content-type': 'application/json' },
            dataType: 'json',
            data: { classid: that.data.pageId, userid:userId, classtype: 11 },
            success: function (res) {
              let videoRecommend = (res.data.data.videoRecommend).slice(0, 3);
              that.setData({
                bofangshujv: res.data.data.videoDetail,
                recommendshujv: videoRecommend,
              });
              setTimeout(function () {
                that.playVideo()
              }, 1000);
              collect.collect.collectDetailInit(that, "bofangshujv");//详情收藏引用
              collect.collect.collectInit(that, "recommendshujv");//列表收藏引用
            }
          })

        })
      }
    })
  },

  dianzan: function () {
    let that = this;
    let user = app.getNewStorage('user');
    let bofangshujv = that.data.bofangshujv;
    let bool = true;
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
          success: function (res) {}
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
    wx.navigateTo({
      url: '../play/play?id='+this.options.pid,
    })
  },
  toSpDetail: function (e) {
    let passid = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: "../play_detail/play_detail?id=" + passid
    })
  },
})