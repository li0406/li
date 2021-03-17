// pages/zuangxbjxq/zuangxbjxq.js
let app = getApp();
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    // showView:[false,false],
    showView1: false,
    showView2: false,
    showView3: false,
    showView4: false,
    showView5: false,
    showView6: false,
    showView7: false,
    showView8: false,
    banbaojia:{},
    halfTotal:'',
    keting:{},
    canting:[],
    zhuwo:[],
    ciwo: [],
    ggwsj:[],
    sdaz:[],
    zhqt:[],
    bookhouse: [],
  },

  kindToggle: function (e) {
    console.log(e);
    var id = e.currentTarget.id, list = this.data.list;
    for (var i = 0, len = list.length; i < len; ++i) {
      if (list[i].id == id) {
        list[i].open = !list[i].open
      } else {
        list[i].open = false
      }
    }
    this.setData({
      list: list
    });
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      console.log(options)
    var that = this;
    wx.request({
        url: apiUrl+'/zxjt/details/id/' + options.orderid,
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function (res) {
        console.log(res);
        that.setData({
          banbaojia: res.data.data.nowdetails,
          halfTotal: res.data.data.halfTotal
        })
       // console.log(that.data.banbaojia)
       for (var key in that.data.banbaojia){
         for (let k in that.data.banbaojia[key]) {
           if (that.data.banbaojia[key].id==1){
            //  console.log(that.data.banbaojia[key])
             that.setData({
               keting: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 2){
             that.setData({
               canting: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 3) {
             that.setData({
               zhuwo: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 3) {
             that.setData({
               zhuwo: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 9) {
             that.setData({
               ciwo: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 4) {
             that.setData({
               ggwsj: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 57) {
             that.setData({
               sdaz: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 65) {
             that.setData({
               zhqt: that.data.banbaojia[key]
             })
           } else if (that.data.banbaojia[key].id == 11) {
             that.setData({
               bookhouse: that.data.banbaojia[key]
             })
           }
         }
       }
       console.log(that.data.bookhouse)

       // console.log(that.data.banbaojia[key]

       
      //  if (that.data.banbaojia[key].id == "1"){
      //    console.log(123)
      //  }

      }
    })
    showView: (options.showView == "true" ? true : false)
  },
  onChangeShowState1: function () {
    var that = this;
    that.setData({
      showView1: (!that.data.showView1),
      showView2: false,
      showView3: false,
      showView4: false,
      showView5: false,
      showView6: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState2: function () {
    var that = this;
    that.setData({
      showView2: (!that.data.showView2),
      showView1: false,
      showView3: false,
      showView4: false,
      showView5: false,
      showView6: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState3: function () {
    var that = this;
    that.setData({
      showView3: (!that.data.showView3),
      showView2: false,
      showView1: false,
      showView4: false,
      showView5: false,
      showView6: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState4: function () {
    var that = this;
    that.setData({
      showView4: (!that.data.showView4),
      showView2: false,
      showView3: false,
      showView1: false,
      showView5: false,
      showView6: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState5: function () {
    var that = this;
    that.setData({
      showView5: (!that.data.showView5),
      showView2: false,
      showView3: false,
      showView4: false,
      showView1: false,
      showView6: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState6: function () {
    var that = this;
    that.setData({
      showView6: (!that.data.showView6),
      showView2: false,
      showView3: false,
      showView4: false,
      showView5: false,
      showView1: false,
      showView7: false,
      showView8: false,
    })
  },
  onChangeShowState7: function () {
    var that = this;
    that.setData({
      showView7: (!that.data.showView7),
      showView2: false,
      showView3: false,
      showView4: false,
      showView5: false,
      showView6: false,
      showView1: false,
      showView8: false,
    })
  },
  onChangeShowState8: function () {
    var that = this;
    that.setData({
      showView8: (!that.data.showView8),
      showView2: false,
      showView3: false,
      showView4: false,
      showView5: false,
      showView6: false,
      showView7: false,
      showView1: false,
    })
  },
  zxbj: function () {
    wx.navigateTo({
        url: '../zhuangxiubj/zhuangxiubj'
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
  onShareAppMessage: function () {
  
  }
})