// pages/city/city.js
import { getCitys, getAreas } from "../../utils/api.js"
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    curCity: '',
    cname: '',
    areaName: '',
    cs: '',
    qx: '',
    prev: [],
    city: [],
    json: [],
    cityList: [],
    areaList: [],
    allCity: [],
    isHide: true,
    value: [0]
  },
  /**
   * 生命周期函数--监听页面加载
   */

  getFormOptions: function () {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getOrderList('/v1/order/options', '', {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          that.setData({
            fen: res.data.type_fw,
            yu: res.data.yusuan,
            cla: res.data.leixing,
            meth: res.data.fangshi
          })
        })
      }
    })
  },


  onLoad: function (options) {
    let that = this;
    that.setData({
      curCity: options.curCity,
      needArea: options.needArea
    })
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getCitys('/v1/order/options', {}, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          that.setData({
            cityList: res.data.citys,
            allCity: res.data.citys,
          })
        })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  input(e) {
    this.value = e.detail.value;
    this.searchMt();
  },
  // 基础搜索功能
  searchMt() {
    this._search();
  },
  _search() {
    let data = this.data.allCity;
    let newData = [];
    for (let i = 0; i < data.length; i++) {
      let itemArr = [];
      let itemJson = {};
      if (data[i].cname.indexOf(this.value) > -1) {
        for (let k in data[i]) {
          itemJson[k] = data[i][k]
        }
        itemArr.push(itemJson);
      }
      if (itemArr.length === 0) {
        continue;
      }
      newData.push(itemArr[0])
    }
    this.setData({
      cityList: newData
    })
  },
  detailMt: function (e) {
    let that = this;
    let cname = e.currentTarget.dataset.detail;
    let cs = e.currentTarget.dataset.cs;
    let needArea = that.data.needArea;
    if (needArea == 1) {
      that.setData({
        curCity: cname,
        cs: cs
      })
      let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
      let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）

      prevPage.setData({
        curCity: cname,
        cs: cs,
      })

      wx.navigateBack({
        delta: 1,
      })
    } else {
      if (e.currentTarget.dataset.cs == '') {
        that.setData({
          isHide: true,
          curCity: cname,
        })
        let pages = getCurrentPages();
        let prevPage = pages[pages.length - 2];

        prevPage.setData({
          curCity: cname,
          areaName: '',
          cs: '',
          qx: ''
        })
      } else {
        wx.getStorage({
          key: 'info',
          success: function (res) {
            let token = res.data.token;
            getAreas('/areas/', {
              cid: cs
            }, {
                'content-type': 'application/x-www-form-urlencoded',
                'token': token
              }).then(res => {
                that.setData({
                  isHide: false,
                  areaList: res.data[0],
                  cs: cs,
                  cname: cname
                })

                let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
                let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）

                prevPage.setData({
                  curCity: cname,
                  areaName: '',
                  cs: cs,
                  qx: ''
                })
              })
          }
        })
      }
    }


  },
  selectArea: function (e) {
    console.log(e)
  },
  choose: function (e) {
    let that = this;
    let curCity = that.data.cname;
    let areaName = e.currentTarget.dataset.name;
    let cs = that.data.cs;
    let qx = e.currentTarget.dataset.area;
    that.setData({
      isHide: true,
      qx: qx,
      curCity: curCity
    });
    let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
    let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）

    prevPage.setData({
      curCity: curCity,
      areaName: areaName,
      cs: cs,
      qx: qx
    })
    wx.navigateBack({
      delta: 1,
    })
  },
  close: function () {
    this.setData({
      isHide: true,
      qx: ''
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
  onShareAppMessage: function () {

  }
})