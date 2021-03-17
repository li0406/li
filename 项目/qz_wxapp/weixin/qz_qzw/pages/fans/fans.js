
const app = getApp();
const zxsApi = app.getZxsApiUrl();
Page({
  data: {
    fanList: []
  },
  onLoad: function (options) {
    let that = this
    let id = options.id ? options.id : ''
    that.setData({
      live_id: id
    })
    this.fanList(id)
  },
  fanList(id){
    let that = this
    wx.request({
      url: zxsApi + '/business/worksite/applet_user/live_fans',
      data: {
       live_id:that.data.live_id
      },
      header: {
        token: ''
      },
      success(res) {
        if (res.data.error_code == 0) {
          that.setData({
            fanList : res.data.data.info
          })

        } else {
          wx.showToast({
            title: res.data.error_msg,
            icon: 'none'
          })
        }
      }

    })
  }
})