// pages/gongluelist/gongluelist.js
const app = getApp()
let apiUrl = app.getAppUrl();
const imgUrl = app.getImgUrl();
const fadan = require('../../utils/fadan.js');

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

let levelOneNav = [{ id: 1, text: "装修前" }, { id: 2, text: "装修中" }, { id: 3, text: "装修后" }];
Page({

  /**
   * 页面的初始数据
   */
  data: {
    qiehuanpd: true,
    arr: [true, false, false],
    shoucpd: true,
    footshow: false,
    levelOneNav: null,
    listshujv: [],
    upshowlistshujv: [],
    currentPage: 1,
    imgUrl: imgUrl,
    pid: "",
    numpid: [],
    hasData: 1, //是否还有数据标识
    fd: "",
    dataList: [],
    chuanlaiid: null,
    userId: null,
    vid: "", //用户当前查看的文章id，用户返回时更新pv和收藏情况
    scrollContainerHeight: '1455rpx',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    tt.getStorage({
      key: 'userId',
      success: function (res) {
        that.setData({
          userId: res.data
        })
        that.addshujv();
      },
      fail: function () {
        that.addshujv();
      }
    })
  //发单引用
    fadan.fadan.init(this, 1,{
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false
    });

    that.setData({
      levelOneNav: levelOneNav,
      chuanlaiid: options.id,
    });

    for (let i = 0; i < that.data.levelOneNav.length; i++) {
      if (that.data.chuanlaiid == that.data.levelOneNav[i].id) {
        let arr = "arr[" + i + "]";
        that.setData({
          [arr]: true,
          pid: that.data.chuanlaiid,
        });
      }
    }
  },

  addshujv: function () {
    let that = this;
    let tempDataSet = [];
    tt.showLoading({
      title: "加载中",
      duration: 300,
    })
    tt.request({
      url: apiUrl + '/appletgonglue/getVideoList',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "GET",
      dataType: 'json',
      data: {
        page: that.data.currentPage,
        type: that.data.pid,
        userid: that.data.userId,
      },
      success: function (res) {
        if (res.data.data.length < 6 && res.data.data.length > 0) {
          that.setData({
            hasData: 0,
            footshow: true,
          });
        } else {
          that.setData({
            hasData: 1,
            footshow: false,
          });
        }


        tempDataSet = that.data.listshujv.concat(res.data.data);
        that.setData({
          listshujv: tempDataSet,
          upshowlistshujv: res.data.data,
        })
        tt.hideLoading()

      },
      fail: function () {
        console.log("error!!!!");
      }
    })

  },

  /**
 * 生命周期函数--监听页面显示
 */
  onShow: function () {
    let that = this;
    let viewArticleId = "";

    try {
      viewArticleId = tt.getStorageSync("viewArticleId");
      if (viewArticleId) {
        this.setData({
          vid: viewArticleId
        });

        tt.removeStorage({
          key: 'viewArticleId'
        })

        // this.updateArticle();
      }
    } catch (e) {

    }


  },
  // 更新文章查看后返回的pv及收藏状态
  updateArticle: function () {

    let vid = this.data.vid,
      videoList = this.data.listshujv,
      pv = "",
      isCollect = 0,
      that = this,
      len = videoList.length;
    tt.request({
      url: apiUrl + '/appletgonglue/getVideoDetail',
      data: {
        userid: that.data.userId,
        classid: this.data.vid,
        classtype: 11
      },
      header: { 'content-type': 'application/json' },
      success: function (res) {
        if (res.data.data.videoDetail) {
          pv = res.data.data.videoDetail.pv;
          isCollect = res.data.data.videoDetail.is_collect;
          for (let i = 0; i < len; i++) {
            if (videoList[i].id == vid) {
              videoList[i].pv++;
              videoList[i].is_collect = isCollect;
            }
          }
          that.setData({
            listshujv: videoList
          });
        }
      },
      fail: function () {
        console.log("error!!!");
      }
    })
  },


  qiehuan: function (e) {
    let that = this;

    let index = e.currentTarget.dataset.index;
    let acceptpid = e.currentTarget.dataset.id;

    that.setData({
      listshujv: [],
    })
    for (let i = 0; i < that.data.arr.length; i++) {
      let current = "arr[" + i + "]";
      if (i == index) {
        that.setData({
          [current]: true,
          pid: acceptpid,
          currentPage: 1,
        })
        that.addshujv();
      } else {
        that.setData({
          [current]: false
        })
      }

    }

  },


  shoucang: function () {
    var that = this;
    that.setData({
      shoucpd: !that.data.shoucpd,
    })

  },

  selectHandle() {
    this.setData({ isHideCity: false });
  },

  searchxq: function () {
    tt.navigateTo({
      url: "../search/index"
    })
  },


  shipindetail: function (y) {
    let passid = y.currentTarget.dataset.id;
    tt.navigateTo({
      url: '../play_detail/play_detail?id=' + passid + '&pid=' + this.data.pid
    })

    //将当前查看的文章id缓存起来，页面返回时需要更新该文章的pv以及收藏情况
    tt.setStorage({
      key: 'viewArticleId',
      data: passid,
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
      this.addshujv();
    }
  },



  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

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
      path: 'pages/play/play',
      success: function (res) { },
      fail: function (res) { }
    }
  }
})