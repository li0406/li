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
  data: {
    media_list: [],
    company_id: '',
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1
  },
  onLoad: function(options) {
    let that = this
    let id = options.id ? options.id : ''
    that.setData({
      company_id: id
    })
    this.comList(id)
  },
  onShow() {},
  comList(id) {
    let obj = this.data;
    let that = this

    if (obj.nomore != 2) {
      return
    }
    that.setData({
      nomore: 1
    })
    if (obj.page == 1) {
      wx.showLoading({
        title: '加载中',
      })
    }

    wx.request({
      url: zxsApi + '/business/worksite/applet_user/live_list',
      data: {
        company_id: that.data.company_id,
        media: 1,
        fans: 1,
        page: that.data.page,
        limit: 3
      },
      success(res) {
        let datas = res.data;
        if (obj.page == 1) {
          wx.hideLoading()
          that.setData({
            infoList: []
          })
        }
        if (datas.error_code == 0) {
          let infoList = that.data.infoList;
          infoList = infoList.concat(datas.data.info);
          infoList.forEach((item, index) => {
            if (item.fans) {
              item.fanLen = item.fans.length
              item.fans = reversePeople((item.fans).slice(0, 6))
            }
            if (item.media_list) {
              item.meiaLen = item.media_list.length
            }
            let sex = item.sex ? item.sex : '先生'
            item.owner = item.yz_name.substring(0, 1) + sex
          })
          that.setData({
            infoList: infoList
          })
          if (datas.data.info.length < 3) {
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
  toSitedetail(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../siteDetail/siteDetail?live_id=' + id
    });
  },
  //滚动到底部100px时触发
  lower: function(e) {
    this.comList()
  },
  videoClick(e) {
    let url = e.currentTarget.dataset.url
    wx.navigateTo({
      url: '../siteVideo/siteVideo?url=' + url
    });
  },
  tuClick(e) {
    let id = e.currentTarget.dataset.id
    let tid = e.currentTarget.dataset.tid
    let that = this
    let infoList = that.data.infoList
    var imglist = infoList.find(function(imglist) {
      return imglist.id == id
    })
    let swiperData = imglist.media_list
    let startNum = swiperData[0].id
    let currentNum = (tid - startNum) + 1

    that.setData({
      swiperData: swiperData,
      swiperLen: swiperData.length,
      currentIndex: currentNum,
      largeView: !that.data.largeView
    })
  },
  getLargeImage() {
    this.setData({
      largeView: !this.data.largeView
    })
  },
  swiperChange(e) {
    this.setData({
      currentIndex: e.detail.current + 1
    })
  },
})