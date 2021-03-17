const app = getApp()
let apiUrl = app.getApiUrl(), ImgUrl = app.getImgUrl();
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
  data: {
    prevCityAreaId: '',
    selectText: '',
    selectTextDefault: '请选择城市',
    valuecity: [0, 0, 0],
    val: [],
    prev: [],
    city: [],

    prevIndex: '0',
    cityIndex: '0',

    isHideCity: true,
    personName: '',
    inputname:"",
    telNumber: '',
    emptyNameValue: '',
    isColor: false,
    checkValue:true,
    lingPer:"",
    clickAble:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that=this;
    wx.getStorage({
      key: 'lingPer',
      success: function (res) {
        that.setData({
          lingPer: res.data
        });
      },
      fail: function (res) {
        wx.setStorage({
          key: 'lingPer',
          data:226,
        });
        that.setData({
          lingPer: 226
        });
      }
    });
  },
  onShow: function () {
    let that = this;
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


  selectHandle() {
    this.setData({ isHideCity: false });
  },

  closebtn() {
    this.setData({ isHideCity: true });
  },
  /**
   * 城市选择
   */
  okbtn() {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;

    let prevId = prevInfo[that.data.prevIndex].id;
    let cityId = cityInfo[that.data.cityIndex].id;

    let prevText = prevInfo[that.data.prevIndex].text;
    let cityText = cityInfo[that.data.cityIndex].text;

    let prevCityAreaId = prevId + ',' + cityId;
    let selectText = prevText + ' ' + cityText;
    this.setData({ isHideCity: true, isColor: true, selectText: selectText, cityId: cityId,   selectTextDefault: '' });
  },
  Name: function (e) {
    this.setData({ inputname: e.detail.value })
  },
  Phone: function (e) {
    this.setData({ telNumber: e.detail.value })
  },

  // 城市选择滑动
  bindChange: function (e) {
    let that = this;
    let cityJson = [];

    const val = e.detail.value;
    // let json = that.data.json;
    let prev = val[0];
    let city = val[1];

    let oldVal = that.data.val;
    that.setData({ val: val })
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        // 滑动省份获取城市
        if (oldVal[0] != prev && oldVal[1] == city) {
          city = 0;
          that.setData({ valuecity: [prev, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city) {
          that.setData({ valuecity: [prev, city] })
        } else if (oldVal[0] == prev && oldVal[1] == city) {
        }
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

  selectHandle: function () {
    this.setData({ isHideCity: false })
  },


  // 装修设计表单提交1
  formSubmit: function (e) {
    if (!this.data.clickAble){
      alertViewWithCancel("提示", "请勿频繁操作", function () {});
      return false
    }
    let that = this;

    var city = that.data.selectText;
    var name = e.currentTarget.dataset.name;
    var tel = e.currentTarget.dataset.phone;
    var check = that.data.checkValue;
    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);
    if (city.length < 1) {
      that.setData({
        xzcity: '请选择城市',
      })
      alertViewWithCancel("提示", "请选择您的所在地区", function () {});
      return;
    } else {
      that.setData({
        xzcity: '',
      })
    }
   
    if (name.length < 1) {
      alertViewWithCancel("提示", "请输入姓名", function () {
      });
      return;
    } else if (name.search(re) == -1) {
      alertViewWithCancel("提示", "请输入正确的姓名，仅限中文和英文", function () {
        that.setData({ boolName: true });
      });
      return;
    }

    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入手机号码", function () { });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请输入正确的手机号码", function () {
        });
        return false;
      }
    }

    if (!check){
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
      });
      
      return false;
    }
    that.setData({
      clickAble:false
    });
    setTimeout(function(){
      that.setData({
        clickAble: true
      });
    },5000);
    if (that.data.src) {
      wx.request({
        url: apiUrl + '/fb_order?src=' + that.data.src + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
        + "&click_id=" + app.globalData.click_id,
        data: {
          name: name,
          tel: tel,
          cs: that.data.cityId,
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
              personName: '',
              inputphone: '',
              inputname: '',
              telNumber: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
            that.setData({
              lingPer: that.data.lingPer+ 1
            });
            wx.setStorage({
              key: 'lingPer',
              data: that.data.lingPer,
            })
          } else {
            alertViewWithCancel("提示", res.data.data.info, function () { });
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
          cs: that.data.cityId,
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
              personName: '',
              inputphone: '',
              inputname: '',
              telNumber: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
            that.setData({
              lingPer: that.data.lingPer + 1
            });
            wx.setStorage({
              key: 'lingPer',
              data: that.data.lingPer,
            })
          } else {
            alertViewWithCancel("提示", res.data.data.info, function () { });
          }
          
        }
      });
    }
  },
  checkboxChange:function(e){
    if (this.data.checkValue == true) {
      this.setData({
        checkValue: false
      })
    } else {
      this.setData({
        checkValue: true
      })
    }
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/six_baozhang/six_baozhang',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})