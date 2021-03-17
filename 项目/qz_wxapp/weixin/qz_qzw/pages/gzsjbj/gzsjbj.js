// pages/gzsjbj/gzsjbj.js
let app = getApp();
let apiUrl = app.getApiUrl();
//显示带取消按钮的消息提示框
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
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
    lingNum:'',
    currentTab1: '',
    currentTab2: '',
    currentTab3: '',
    currentTab4: '',
    currentTab5: '',
    currentTab6: '',
    tabActive: true,
    tabActive2: true,
    tabActive3: true,
    autoplay1: true,
    autoplay2: true,
    autoplay3: true,
    autoplay4: true,
    autoplay5: true,
    autoplay6: true,
    interval: 3000,
    duration: 1000,
    inputFocus: false,
    clickAble: true,
    emptyname:'',
    emptyphone:'',
    inputname: '',
    inputphone: '',
    checkValue: true,
    src:'xcx-qzw-gz',
    prev: [],
    city: [],

    val: [],
    prevIndex: '0',
    cityIndex: '0',

    valuecity: null,
    json: [],
    isHideCity: true,
    selectText: '',
    xzcity: '请选择城市',
    cityId: '',
    valuecity: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  formSubmit: function (e) {
    let clickAble = this.data.clickAble;
    if (!clickAble) {
      alertViewWithCancel("提示", "请勿频繁操作", function () { });
      return false;
    };
    let that = this;
    var city = this.data.cityId;
    var name = e.currentTarget.dataset.name;
    var tel = e.currentTarget.dataset.phone;
    var check = that.data.checkValue;

    if (city.length < 1) {
      that.setData({
        xzcity: '请选择城市',
      })
      alertViewWithCancel("提示", "请选择您的所在地区", function () {

      });
      return;
    } else {
      that.setData({
        xzcity: '',
      })
    };
    if (name == '') {
      alertViewWithCancel("提示", "请输入您的姓名", function () {
      });
      return;
    }else {
      var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
      if (!reg.test(name)){
        alertViewWithCancel("提示", "姓名只支持中文和英文", function () {
        });
        return;
      }
    }

    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () {
      });
      return;
    } else {
      var reg = new RegExp("^(13|14|15||16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () {
          that.setData({
            emptyphone: "",
          })
        });
        return false;
      }
    };
    if (!check) {
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
      });
      return false;
    };
    that.setData({
      clickAble: false
    });
    setTimeout(function () {
      that.setData({
        clickAble: true
      })
    }, 5000);
    if (that.data.src) {
      wx.request({
        url: apiUrl + '/fb_order?src=' + that.data.src + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
        + "&click_id=" + app.globalData.click_id,
        data: {
          name: name,
          tel: tel,
          cs: city,
          gdt_vid: app.globalData.gdt_vid,
          aid: app.globalData.aid,
          click_id: app.globalData.click_id,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          if (res.data.status == 1) {
            that.setData({
              emptyname: "",
              emptyphone: "",
            });
            app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
          } else {
            alertViewWithCancel("提示", res.data.info, function () { });
          }
        }
      });
    } else {
      wx.request({
        url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
          + "&click_id=" + app.globalData.click_id,
        data: {
          name: name,
          tel: tel,
          cs: city,
          gdt_vid: app.globalData.gdt_vid,
          aid: app.globalData.aid,
          click_id: app.globalData.click_id,
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          if (res.data.status == 1) {
            that.setData({
              emptyname: "",
              emptyphone: ""
            });
            app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
          } else {
            alertViewWithCancel("提示", res.data.info, function () { });
          }

        }
      });
    }
  },
  inputName: function (e) {
    this.setData({ inputname: e.detail.value })
  },
  inputPhone: function (e) {
    this.setData({ inputphone: e.detail.value })
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
    let that = this;
    that.setData({ lingNum: app.globalData.personNum });
    let json = [];
    let prevJson = [];
    let cityJson = [];

    let cityUrl;

    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city });
        app.getMyPosition(cityJsonNew, that)
      },
      // 获取缓存失败
      fail: function () {
        // ajax请求数据并且数据本地缓存存储
        wx.request({
          url: apiUrl + '/city/allcity?level=2',
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {


            that.setData({ prev: res.data.prev, city: res.data.city, json: res.data.json });
            app.getMyPosition(res.data, that)
            wx.setStorage({
              key: 'cityJson',
              data: { prev: res.data.prev, city: res.data.city, json: res.data.json },
            })
          }
        })
      }
    });
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({ isHideCity: true })
  },
  // 城市选择滑动
  bindChange: function (e) {
    let that = this;
    let cityJson = [];

    const val = e.detail.value;
    let prev = val[0];
    let city = val[1];

    let oldVal = that.data.val;
    that.setData({ val: val })
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        if (oldVal[0] != prev && oldVal[1] == city) {
          city = 0;
          that.setData({ valuecity: [prev, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city) {
          that.setData({ valuecity: [prev, city] })
        } else if (oldVal[0] == prev && oldVal[1] == city) {
        }
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
        }

        that.setData({ city: cityJson, prevIndex: prev, cityIndex: city })
      }
    });

  },
  closebtn: function () {
    this.setData({ isHideCity: true });
  },
  okbtn: function () {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;

    let prevId = prevInfo[that.data.prevIndex].id
    let cityId = cityInfo[that.data.cityIndex].id

    let prevText = prevInfo[that.data.prevIndex].text
    let cityText = cityInfo[that.data.cityIndex].text

    let prevCityAreaId = prevId + ',' + cityId;
    let selectText = prevText + ' ' + cityText;
    this.setData({ xzcity: '', isHideCity: true, selectText: selectText, cityId: cityId });
  },
  selectHandle: function () {
    this.setData({ isHideCity: false })
  },
  // 切换免责
  checkboxChange: function (e) {
    let that = this;
    if (that.data.checkValue == true) {
      that.setData({
        checkValue: false
      })
    } else {
      that.setData({
        checkValue: true
      })
    }
  },
  toTop: function () {
    wx.pageScrollTo({
      scrollTop:500
    })
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

  },
  changeTab1: function (e) {
    var that = this;
    that.setData({
      currentTab1: e.target.dataset.current
    });
  },
  changeTab2: function (e) {
    var that = this;
    that.setData({
      currentTab2: e.target.dataset.current
    });
  },
  changeTab3: function (e) {
    var that = this;
    that.setData({
      currentTab3: e.target.dataset.current
    });
  },
  changeTab4: function (e) {
    var that = this;
    that.setData({
      currentTab4: e.target.dataset.current
    });
  },
  changeTab5: function (e) {
    var that = this;
    that.setData({
      currentTab5: e.target.dataset.current
    });
  },
  changeTab6: function (e) {
    var that = this;
    that.setData({
      currentTab6: e.target.dataset.current
    });
  },
  bindChange1: function (e) {
    var that = this;
    that.setData({ currentTab1: e.detail.current });
  },
  bindChange2: function (e) {
    var that = this;
    that.setData({ currentTab2: e.detail.current });
  },
  bindChange3: function (e) {
    var that = this;
    that.setData({ currentTab3: e.detail.current });
  },
  bindChange4: function (e) {
    var that = this;
    that.setData({ currentTab4: e.detail.current });
  },
  bindChange5: function (e) {
    var that = this;
    that.setData({ currentTab5: e.detail.current });
  },
  bindChange6: function (e) {
    var that = this;
    that.setData({ currentTab6: e.detail.current });
  },
  changeType1: function (e) {
    this.setData({
      tabActive: e.target.dataset.type == "true"
    });
  },
  changeType2: function (e) {
    this.setData({
      tabActive2: e.target.dataset.type == "true"
    });
  },
  changeType3: function (e) {
    this.setData({
      tabActive3: e.target.dataset.type == "true"
    });
  },

})