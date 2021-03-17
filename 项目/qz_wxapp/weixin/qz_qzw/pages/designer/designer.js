// pages/designer/designer.js
const app = getApp();
const navActive = require('../../utils/util.js');
const fadan = require('../../utils/fadan.js');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    designerData:[],
    currentPage: 1,
    hasData: 1,
    scrollContainerHeight: '1455rpx',
    isUpdateScrollHeight: false,
    categoryNoData: 1, 
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
      url: apiUrl + '/company/team?',
      data: {
        bm: options.bm,
        id: options.id,
        p: that.data.currentPage,
        pagecount: 15,
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if(res.data.status == 1){
          that.setData({
            designerData: res.data.data
          });
        }
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
      path: 'pages/designer/designer',
      success: function (res) { },
      fail: function (res) { }
    }
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
      url: apiUrl + '/company/team?',
      data: {
        bm: that.options.bm,
        id: that.options.id,
        p: that.data.currentPage,
        pagecount: 15,
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        // 点击选项卡的时候可能出现只有几条数据的情况，此时要更新scroll-view容器的高度，以免底线提示文字无法显示在可视区域
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(res.data.data.length) : "";
        if (res.data.data.length > 0 && res.data.data.length < 15) {
          that.setData({
            hasData: 0
          });
        } else {
          that.setData({
            hasData: 1
          });
        }
        // 没有数据提示处理：只有查找的数据以及articleData中都没有数据，才能判定当前分类没有数据
        if (parseInt(res.data.data.length) == 0 && parseInt(that.data.designerData.length) == 0) {
          that.setData({
            categoryNoData: 0
          });
        } else {
          that.setData({
            categoryNoData: 1
          });
        }
        tempDataSet = that.data.designerData.concat(res.data.data);
        that.setData({
          designerData: tempDataSet
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
  toDesignerDetail :function (e) {
    let id = e ? e.currentTarget.dataset.id : options.id;
    let bm = e ? e.currentTarget.dataset.bm : options.bm;
    
    wx.navigateTo({
      url: '../designer_detail/designer_detail?id=' + id + '&p=1&pagecount=10'
    });
  }
})