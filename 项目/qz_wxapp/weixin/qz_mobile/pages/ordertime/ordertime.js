// pages/ordertime/ordertime.js
import { ordertime } from "../../utils/api.js";
Page({

  /**
   * 页面的初始数据
   */
  data: {
    start:'',
    end:'',
    sTime: '',
    // tab
    sort: [{
      id: 0,
      name: '默认排序',
      paixu: ''
    },
    {
      id: 1,
      name: '分单时间',
      paixu: 'fen_time desc'
    },
    {
      id: 2,
      name: '阅单时间',
      paixu: 'readtime desc'
    },{
      id: 3,
      name: '阅单时间差',
      paixu: 'diff_time desc'
    }
    ],
    sort_index: -1,
    read: [{
      id: 0,
      name: '不限',
      isread: ''
    },{
      id: 1,
      name: '否',
      isread: 1
    },
    {
      id: 2,
      name: '是',
      isread: 2
    }
    ],
    read_index: -1,
    list: [],
    isShow: true,
    readed: true,
    noread: false,
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    cs: '',
    curCity: '不限',
    noResult: false,
    noInternet: false,
    parms: {
      start: '',
      end: '',
      company: '',
      order: '',
      cs: '',
      isread: '',
      p: '1',
      psize: '10',
      orderby: ''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this
    that.ordertime(that.data.parms)
  },

  ordertime: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        ordertime('/v1/orders/read_orders',
          parms, {
            'Content-Type': 'application/json',
            'token': token
          }
        ).then(res => {
          console.log(res)
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            if (res.data.list.length < 1) {
              that.setData({
                noResult: true,
                noInternet: false,
                isShow: true
              })
            } else {
              that.setData({
                noResult: false,
                isShow: true
              })
            }
            if (res.data.page.total_number <= 10) {
              that.setData({
                isShow: true
              })
            } else {
              that.setData({
                isShow: false
              })
            }
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            that.setData({
              list: res.data.list,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr
            })
          }else{
            that.setData({
              list: [],
              isShow: true,
              noResult: true,
              noInternet: false
            })
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            noInternet: true,
            noResult: false,
            isShow: true
          })
        })
      },
      fail: function (res) {
        wx.navigateTo({
          url: '../login/login'
        })
      }
    })
  },

  /**
   * 重新加载
   */
  toOrderTime: function () {
    wx.navigateTo({
      url: '../ordertime/ordertime'
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },

  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.company']: value,
      ['parms.p']: 1
    })
    console.log(that.data.parms)
    that.ordertime(that.data.parms);
  },

  // 选项卡
  bindPickerChange_sort: function (e) {
    let paixu = this.data.sort[e.detail.value].paixu
    console.log(paixu)
    this.setData({
      sort_index: e.detail.value,
      ['parms.orderby']: paixu,
      ['parms.p']: 1
    })
    console.log(this.data.parms)
    this.ordertime(this.data.parms)
  },
  bindPickerChange_read: function (e) {
    let isread = this.data.read[e.detail.value].isread
    console.log(isread)
    this.setData({
      read_index: e.detail.value,
      ['parms.isread']: isread,
      ['parms.p']: 1
    })
    this.ordertime(this.data.parms)
  },
  /**
    * 选择开始时间
    */
  bindDateChange: function (e) {
    var startTimestamp = Date.parse(new Date(e.detail.value)) / 1000
    var endTimestamp = Date.parse(new Date(this.data.end)) / 1000
    if (startTimestamp > endTimestamp){
      wx.showToast({
        title: '开始时间不能大于结束时间',
        duration: 2000,
        icon: 'none'
      })
    }else{
      this.setData({
        start: e.detail.value,
        ['parms.start']: e.detail.value,
        ['parms.p']: 1
      })
      if (this.data.parms.end) {
        this.ordertime(this.data.parms)
      }
    }
  },
  /**
  * 选择结束时间
  */
  bindEndDateChange: function (e) {
    this.setData({
      end: e.detail.value,
      ['parms.end']: e.detail.value,
      ['parms.p']: 1
    })
    if (this.data.parms.start){
      this.ordertime(this.data.parms)
    }
  },

   /**
  * 查看订单
  */
  goOrder:function(e){
    let id = e.currentTarget.dataset.id
    let start = ''
    let end = ''
    if (this.data.parms.start != '' && this.data.parms.end != ''){
      start = this.data.parms.start
      end = this.data.parms.end
    }else{
      start = e.currentTarget.dataset.start.split(" ")[0]
      end = e.currentTarget.dataset.end.split(" ")[0]
    }
    console.log(start)
    wx.navigateTo({
      url: '../order/order?&id=' + id + '&date_begin=' + start + '&date_end=' + end
    })
  },

  /**
   * 点击上一页
   */
  goPrePage: function () {
    let that = this;
    let p = that.data.pageCurrent - 1;
    if (p <= 1) {
      p = 1;
      that.setData({
        ['parms.p']: p
      })
    } else {
      that.setData({
        ['parms.p']: p
      })
    }
    that.ordertime(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
  * 点击下一页
  */
  goNextPage: function () {
    let that = this;
    let p = that.data.pageCurrent + 1;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      that.setData({
        ['parms.p']: p
      })
    } else {
      that.setData({
        ['parms.p']: p
      })
    }
    that.ordertime(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
  * 点击跳转页数
  */
  toPage: function (e) {
    console.log(e)
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.p']: p
    })
    that.ordertime(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    that.setData({
      ['parms.cs']: that.data.cs,
      ['parms.p']: 1
    });
    that.ordertime(that.data.parms);
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