// pages/watch/watch.js
const app = getApp();
const zxsApi = app.getZxsApiUrl();
function reversePeople(array) {
  let newArr = [];
  for (let i = array.length - 1; i >= 0; i--) {
    newArr[newArr.length] = array[i];
  }
  return newArr;
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isData: false,
    infoList: [],
    token: '',
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.showLoading({
      title: '加载中',
    })
  },
  toWatch: function () {
    wx.switchTab({
      url: '../company/company',
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
    this.comList()
  },

  //滚动到底部100px时触发
  lower: function (e) {
    this.comList()
  },
  comList(id) {
    let that = this
    if (that.data.nomore != 2) {
      return
    }
    that.setData({
      nomore: 1
    })
    if (that.data.page == 1) {
      wx.showLoading({
        title: '加载中',
      })
    }
    let token = wx.getStorageSync('jwt_token')
    wx.request({
      url: zxsApi + '/business/worksite/applet_user/my_live',
      method: 'post',
      data: {
        page: that.data.page,
        limit: 10
      },
      header: {
        "Content-Type": "application/x-www-form-urlencoded",
        token: token
      },
      success(res) {
        wx.hideLoading()
        if (res.data.error_code == 0 && res.statusCode == 200) {
          let datas = res.data.data.info;
          let infoList = that.data.infoList.concat(datas);
          if (infoList.length > 0) {
            that.setData({
              isData: false
            })
          } else {
            that.setData({
              isData: true
            })
          }
          infoList.forEach((item, index) => {
            if (item.fans) {
              item.fans = reversePeople(item.fans.slice(0,6))
              item.fanLen = item.fans.length
            }
            if (item.media_list) {
              item.meiaLen = item.media_list.length
            }
          })
          that.setData({
            infoList: infoList
          })
          if (datas.length < 10) {
            that.setData({
              nomore: 3
            })
          } else {
            that.setData({
              nomore: 2,
              page: that.data.page + 1
            })
          }
        } else {
          that.setData({
            nomore: 3
          })
          wx.showToast({
            title: res.data.error_msg,
            icon: 'none'
          })
        }
      }
    })
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