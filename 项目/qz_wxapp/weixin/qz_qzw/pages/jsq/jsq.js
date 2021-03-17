const app = getApp()
let apiUrl = app.getApiUrl();
let imgUrl = app.getImgUrl();

const navActive = require('../../utils/util.js');
var config = require('../../config');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function(res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
// 获取随机数的方法
function GetRandomNum(Min, Max) {
  var Range = Max - Min;
  var Rand = Math.random();
  return (Min + Math.round(Rand * Range));
}
Page({
  data: {
    mji: "",
    tel: "",
    emptymianji: "",
    emptyphone: "",
    countys: [],
    county: '',
    condition: false,
    prev: [],
    city: [],
    cityId: '',
    prevIndex: '0',
    cityIndex: '0',
    valuecity: null,
    val: [],
    json: [],
    isHide: true,
    selectTextDefault: '选择城市',
    selectText: '',
    prevCityAreaId: '',
    orderid: '',
    num: '52800',
    lingNum: '00000000000',
    currentUrl: "",
    navList: "",
    colorCont: [false, false, false],
    src: "",
    flag: 0,
    checkValue: true,
    clickAble: true,
    autoGetPhone: false,
    imgUrl: imgUrl,
  },

  onShow: function() {
  },
  onLoad: function(options) {
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let cityUrl;
    if (options.src) {
      that.setData({
        src: options.src
      });
    } else {
      that.setData({
        src: app.globalData.sourceMark
      });
    }
    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function(res) {
        let cityJsonNew = res.data;
        console.log('cityjsonNew', cityJsonNew)
        that.setData({
          prev: cityJsonNew.prev,
          city: cityJsonNew.city
        });
        app.getMyPosition(cityJsonNew, that)
      },
      fail: function() {
        wx.request({
          url: apiUrl + '/city/allcity?level=2',
          header: {
            'content-type': 'application/json'
          },
          success: function(res) {
            that.setData({
              prev: res.data.prev,
              city: res.data.city,
              json: res.data.json
            });
            app.getMyPosition(res.data, that)
            wx.setStorage({
              key: 'cityJson',
              data: {
                prev: res.data.prev,
                city: res.data.city,
                json: res.data.json
              },
            })
          }
        })
      }
    });
    // 随机数
    let timer = setInterval(function() {
      let getNum = GetRandomNum(30000, 120000);
      if (getNum > 99999) {
        that.setData({
          lingNum: '0000000000',
          num: getNum
        });
      } else {
        that.setData({
          lingNum: '00000000000',
          num: getNum
        });
      }
    }, 400);
  },


  onPullDownRefresh: function() {
    wx.stopPullDownRefresh()
  },

  onShareAppMessage: function(ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/jsq/jsq',
      success: function(res) {},
      fail: function(res) {}
    }
  },


  // 发单模块
  jsqOrder: function(e) {
    let that = this;
    var bjmj = e.currentTarget.dataset.mianji
    var tel = e.currentTarget.dataset.tel
    var city = that.data.selectText;
    var check = that.data.checkValue;
    if (city.length < 1) {
      alertViewWithCancel("提示", "请选择您的地区", function() {});
      return;
    }
    if (bjmj.length < 1) {
      alertViewWithCancel("提示", "请输入房屋面积", function() {});
      return;
    } else {
      var reg3 = new RegExp("^[1-9][0-9]{0,3}$");
      if (!reg3.test(bjmj)) {
        alertViewWithCancel("提示", "房屋面积请输入1-10000的数字", function() {});
        return;
      }
    }

    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入您的手机号码", function() {});
      return;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function() {});
        return false;
      }
    }
    if (!check) {
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function() {});
      return;
    }

    wx.request({
      url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid
          + "&click_id=" + app.globalData.click_id,
      data: {
        cs: that.data.cityId,
        mianji: bjmj,
        tel: tel,
        fb_type: 'baojia',
        gdt_vid: app.globalData.gdt_vid,
        aid: app.globalData.aid,
        click_id: app.globalData.click_id,
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function(res) {
        if (res.data.status == 1) {
          that.setData({
            orderid: res.data.data.orderid
          })
          // 埋点-腾讯有数-用户预约
          app.sr.track('reservation', {
            "phone_number": tel,
          })
          that.zxbjxq(res.data.data.orderid);
        } else {
          wx.showToast({
            title: res.data.info,
            icon: "none"
          })
        }


      }
    });
  },
  zxbjxq: function(e) {
    wx.navigateTo({
      url: '../zuangxbjxq/zuangxbjxq?orderid=' + e,
    });
  },
  Mianji: function(e) {
    this.setData({
      mji: e.detail.value
    })
  },
  Phone: function(e) {
    this.setData({
      tel: e.detail.value
    })
  },
  getPhoneNumber: function(e) {
    app.getPhoneAuto(e, this)
  },
  checkboxChange: function(e) {
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
  goLogin(e) {
    wx.pageScrollTo({
      scrollTop: 0
    })
  },

  // 城市
  bindChange: function(e) {
    let that = this;
    let cityJson = [];

    const val = e.detail.value;
    let prev = val[0];
    let city = val[1];

    let oldVal = that.data.val;
    that.setData({
      val: val
    })
    wx.getStorage({
      key: 'cityJson',
      success: function(res) {
        let json = res.data.json
        if (oldVal[0] != prev && oldVal[1] == city) {
          city = 0;
          that.setData({
            valuecity: [prev, 0]
          })
        } else if (oldVal[0] == prev && oldVal[1] != city) {
          that.setData({
            valuecity: [prev, city]
          })
        } else if (oldVal[0] == prev && oldVal[1] == city) {}
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({
            id: json[prev].children[j].id,
            text: json[prev].children[j].text
          })
        }
        that.setData({
          city: cityJson,
          prevIndex: prev,
          cityIndex: city,
        })
      }
    });

  },
  closebtn: function() {
    this.setData({
      isHide: true
    });
  },
  okbtn: function() {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;

    let prevId = prevInfo[that.data.prevIndex].id
    let cityId = cityInfo[that.data.cityIndex].id

    let prevText = prevInfo[that.data.prevIndex].text
    let cityText = cityInfo[that.data.cityIndex].text

    let prevCityAreaId = prevId + ',' + cityId
    let selectText = prevText + ' ' + cityText;
    this.setData({
      isHide: true,
      colorCont: [true],
      selectText: selectText,
      cityId: cityId,
      selectTextDefault: ''
    });
  },
  selectHandle: function() {
    let that = this;
    that.setData({
      isHide: false
    })
  },
})