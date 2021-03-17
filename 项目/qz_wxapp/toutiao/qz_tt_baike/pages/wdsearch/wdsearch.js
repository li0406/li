import { getWenda } from '../../utils/api.js'
const $time = require('../../utils/utils.js')
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  tt.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    dataList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
    nodata: "no-data",
    ismask: true,
    isIpx: false,
    topNum: 0,
    quest: '',
    cid: '',
    sub_cid: '',
    title: '',
    infoImg: false,
    tapTrue: false
  },
  //回到顶部
  goTop: function (e) {
    this.setData({
      topNum: this.data.topNum = 0
    });
  },
  lower: function () {
    this.getContentList()
  },
  scrolltoupper(e) {
    if (e.detail.scrollTop > 100) {
      this.setData({
        floorstatus: true
      });
    } else {
      this.setData({
        floorstatus: false
      });
    }
  },
  // 获取搜索列表数据
  getContentList: function (options) {
    let cid = this.data.cid,
      sub_cid = this.data.sub_cid,
      title = this.data.title;
    let obj = this.data;
    if (obj.nomore != 2) {
      return
    }
    this.setData({
      nomore: 1
    })
    if (obj.page == 1) {
      tt.showLoading({
        title: '加载中',
      })
    }
    getWenda(
      '/bd/v1/baike/list',
      {
        p: obj.page,
        parent_cate: cid,
        sub_cate: sub_cid,
        keyword: title
      }
    ).then(data => {
      var datas = data;
      if (obj.page == 1) {
        tt.hideLoading()
        this.setData({
          dataList: []
        })
      }
      if (datas.error_code == 0) {
        let oneList = (datas.data.list).length
        if (!oneList) {
          this.setData({
            infoImg: true
          })
        }
        var dataList = this.data.dataList;
        dataList = dataList.concat(datas.data.list);
        const tipdata = []
        dataList.forEach((item, index) => {
          item.url = '/pages/bkdetail/bkdetail?type=' + 'baike' + '&' + 'id=' + item.id
          tipdata.push(item)
        })
        this.setData({
          dataList: tipdata
        })

        if (datas.data.list.length < 10) {
          this.setData({
            nomore: 3
          })
        } else {
          this.setData({
            nomore: 2,
            page: this.data.page + 1
          })
        }
      } else {
        this.setData({
          nomore: 3
        })
      }
    })
      .catch(error => {
        tt.showToast({
          title: '',
          icon: 'none'
        })
      })
  },
  inputquest: function (e) {
    this.setData({ quest: e.detail.value, tapTrue: true })
  },
  tapSearch() {
    let that = this
    let questA = that.data.quest
    let questB = that.data.quest ? that.data.quest : that.data.title;
    let quest = that.data.tapTrue ? questA : questB
    if (!quest) {
      tt.reLaunch({
        url: '/pages/index/index',
      });
      return;
    }
    this.setData({
      title: quest,
      nomore: 2,
      page: 1,
      infoImg: false
    })
    this.getContentList()
  },
  enterSearch(e) {
    let that = this;
    let value = e.detail.value;
    if (!value) {
      return;
    }
    this.setData({
      title: value,
      nomore: 2,
      page: 1,
      infoImg: false
    })
    this.getContentList()
  },
  onLoad: function (options) {
    let cid = options.cid ? options.cid : '',
      sub_cid = options.sub_cid ? options.sub_cid : '',
      title = options.title;
    this.setData({
      cid: cid,
      sub_cid: sub_cid,
      title: title
    })
    this.getContentList(options)
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
      path: 'pages/disclaimer/disclaimer',
      success: function (res) { },
      fail: function (res) { }
    }
  }
})