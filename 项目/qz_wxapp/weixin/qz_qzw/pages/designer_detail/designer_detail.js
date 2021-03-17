// pages/designer_detail/designer_detail.js
const app = getApp();
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
const fadan = require('../../utils/fadan.js');
const WxParse = require("../../wxParse/wxParse.js");

Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgUrl:imgUrl,
    designer:[],
    companyArr:[],
    cases:[],
    currentPage:1,
    hasData:1,
    scrollContainerHeight:'1455rpx',
    isUpdateScrollHeight:false,
    personNum: [],
    cases:true,
    cry: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    fadan.fadan.init(that, 4, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取",
    });
    wx.request({
      url: apiUrl + '/company/designer?',
      data: {
        id: options.id,
        p: that.data.currentPage,
        pagecount: '5'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        for (let i = 0; i < res.data.cases.length; i++) {
          res.data.cases[i].personNum = app.getPersonNum();
        }

        that.setData({
          designer:res.data.designer,
          cases: res.data.cases
        })
        
        if(that.data.cases.length==0){
          that.setData({
            cases: false,
            cry:true
          })
        }
      }
    })
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
      path: 'pages/designer_detail/designer_detail',
      success: function (res) { },
      fail: function (res) { }
    }
  },
  // 更新scrollContainerHeight的值
updateScrollHeight: function (size) {
    if (size < 5) {
      this.setData({
        scrollContainerHeight: size * 291
      });
    }
    this.setData({
      isUpdateScrollHeight: false
    })
  },
  getContentList: function (e) {
    let id = "",
      cateType = "",
      category = "",
      that = this,
      tempDataSet = [];
    // 从data选项中获取数据，下拉加载时无法获取到事件e
    cateType = that.data.cateType;
    category = that.data.category;
    // 处理非点击加载数据的情况，如初次打开页面，下拉加载数据,此时没有事件参数
    if (e) {
      if (e.currentTarget.dataset.id) {
        cateType = e.currentTarget.dataset.type;
      }
      category = e.currentTarget.dataset.category;
    }
    wx.showLoading({
      title: "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl + '/company/designer?',
      data: {
        id: that.data.designer.uid,
        p: that.data.currentPage,
        pagecount: '5'
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        // 点击选项卡的时候可能出现只有几条数据的情况，此时要更新scroll-view容器的高度，以免底线提示文字无法显示在可视区域
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.cases.length) : "";
        if (res.data.cases.length >=  0 && res.data.cases.length < 5) {
          that.setData({
            hasData: 0
          });
        } else {
          that.setData({
            hasData: 1
          });
        }

        for (let i = 0; i < res.data.cases.length; i++) {
          res.data.cases[i].personNum = app.getPersonNum();
        }

        tempDataSet = that.data.cases.concat(res.data.cases);
        that.setData({
          cases: tempDataSet
        })

        wx.hideLoading();
      },
      fail: function () {
        console.log("error!!!!");
        wx.hideLoading();
      }
    })
  },
  // 加载更多数据
  loadMoreData: function () {
    let page = this.data.currentPage;
    // 有数据就请求下一页，无数据就不再请求
    if (this.data.hasData) {
      this.setData({
        currentPage: ++page
      });
      this.getContentList();
    }
  },
  toExampleDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../xgt-detail/index?id=' + id + '&type=2&pv=' + app.getPVNum()
    });
  },
})