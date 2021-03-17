//index.js
//获取应用实例
const app = getApp()
const apiUrl = 'https://appapi.qizuang.com'
// 获取随机数的方法
function GetRandomNum(Min, Max) {
  var Range = Max - Min;
  var Rand = Math.random();
  return (Min + Math.round(Rand * Range));
}
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
  data: {
    clickAble: true,
    focus: false,
    prev: [],
    city: [],
    area: [],
    mianji: "",
    phone: "",
    prevIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    valuecity: null,
    val: [],
    json: [],
    isHideCity: true,
    selectText: '',
    xzcity: '请选择城市',
    cityId: '',
    randomNum:'',
    isHide: false,
    style:'',
    kt: '1客',
    ct: '1餐',
    ws: '1卧',
    cf: '1厨',    
    ktArr: [{ id: 0, num: 0, text: '0客' }, { id: 1, num: 1, text: '1客' }, { id: 2, num: 2, text: '2客' }, { id: 3, num: 3, text: '3客' }, { id: 4, num: 4, text: '4客' }, { id: 5, num: 5, text: '5客' }],
    ctArr: [{ id: 0, num: 0, text: '0餐' }, { id: 1, num: 1, text: '1餐' }, { id: 2, num: 2, text: '2餐' }, { id: 3, num: 3, text: '3餐' }, { id: 4, num: 4, text: '4餐' }, { id: 5, num: 5, text: '5餐' }],
    wsArr: [{ id: 0, num: 0, text: '0卧' }, { id: 1, num: 1, text: '1卧' }, { id: 2, num: 2, text: '2卧' }, { id: 3, num: 3, text: '3卧' }, { id: 4, num: 4, text: '4卧' }, { id: 5, num: 5, text: '5卧' }],
    cfArr: [{ id: 0, num: 0, text: '0厨' }, { id: 1, num: 1, text: '1厨' }, { id: 2, num: 2, text: '2厨' }, { id: 3, num: 3, text: '3厨' }, { id: 4, num: 4, text: '4厨' }, { id: 5, num: 5, text: '5厨' }],
  },
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  onLoad: function () {
    let that = this;
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse){
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      app.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          app.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }
    // 随机数
    let timer = setInterval(function () {
      let getNum = GetRandomNum(10000, 40000);
      that.setData({
        randomNum: getNum
      })
    }, 400);

    let style = this.data.kt + this.data.ct + this.data.ws + this.data.cf;
    that.setData({
      currentIndexOne:1,
      currentIndexTwo: 1,
      currentIndexThree: 1,
      currentIndexFour: 1,
      style: style
    })
  },
  onShow: function () {
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let areaJson = [];
    let cityUrl;
    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
        app.getMyPosition(cityJsonNew, that)
      },
      // 获取缓存失败
      fail: function () {
        // ajax请求数据并且数据本地缓存存储
        wx.request({
          url: apiUrl + '/zxjt/getcityurl',
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            cityUrl = res.data.data.replace("/common/js", "");
            let cityUrlArr = cityUrl.split(':');
            let host = cityUrlArr[1].split('.');
           
            cityUrlArr[1] = host.join('.');
            let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1]
            wx.request({
              url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
              header: {
                'content-type': 'application/json'
              },
              success: function (res) {
                let str = res.data.replace("var rlpca = ", "");
                json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                json.shift();
                // 删除空省份/城市/区域
                for (let i = 0; i < json.length; i++) {
                  json[i].children.shift()
                  for (var j = 0; j < json[i].children.length; j++) {
                    json[i].children[j].children.shift()
                  }
                };
                // 筛选省份名称+id
                for (let i = 0; i < json.length; i++) {
                  prevJson.push({ id: json[i].id, text: json[i].text });
                }
                // 筛选第一省的第一个城市名称+id
                for (let j = 0; j < json[0].children.length; j++) {
                  cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                }
                // 筛选第一省/第一个城市/第一个区域名称+id
                for (let k = 0; k < json[0].children[0].children.length; k++) {
                  areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                }
                that.setData({ prev: prevJson, city: cityJson, area: areaJson, json: json });
                app.getMyPosition(json, that)
                wx.setStorage({
                  key: 'cityJson',
                  data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                })
              }
            })
          }
        })
      }
    });
  },
  getUserInfo: function(e) {
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  onHide: function () {
    this.setData({ isHideCity: true })
  },
  formSubmit: function (e) {
    let clickAble = this.data.clickAble;
    if (!clickAble) {
      alertViewWithCancel("提示", "请勿频繁操作", function () { });
      return false;
    }
    let that = this;
    var city = this.data.cityId;
    var area = this.data.areaId;
    var bjmj = this.data.mianji;
    var style = this.data.style;
    var tel = this.data.phone;

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
    }
    if (bjmj.length < 1) {
      alertViewWithCancel("提示", "请输入您的房屋面积", function () {
      });
      return;
    } else {
      if (bjmj < 5 || bjmj > 1000 || isNaN(bjmj)) {
        alertViewWithCancel("提示", "房屋面积请输入5-1000之间", function () {
          that.setData({
            mianji: "",
          })
        });
        return;
      }
    }
    if (style == '' || style == '0客0餐0卧0厨') {
      alertViewWithCancel("提示", "请至少选择一个户型", function () {
      });
      return;
    }
    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () {
      });
      return;
    } else {
      var reg = new RegExp("^((13[0-9])|(14[5,7,8,9])|(15[0-3,5-9])|(17[0,1,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () {
          that.setData({
            phone: "",
          })
        });
        return false;
      }
    }

    that.setData({
      clickAble: false
    });
    setTimeout(function () {
      that.setData({
        clickAble: true
      })
    }, 2000)

    wx.request({
      // 本地测试接口
      // url: 'http://appapi.qizuang.com/jiaju/jiajufb?src=' + app.globalData.sourceMark,
      url: apiUrl + '/jiaju/jiajufb?src=' + app.globalData.sourceMark, 
      data: {
        mianji: bjmj,
        tel: tel,
        cs: city,
        qy: area,
        huxing: style,
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function (res) {
        if (res.data.error_code == 0) {
          that.setData({
            mianji:'',
            phone:''
          })
          //  获取到计算结果，传给结果页
          let json = res.data.data
          let str = JSON.stringify(json);
          wx.navigateTo({
            url: "../baojiajg/jieguo?jsonStr=" + str
          })
        } else {
          alertViewWithCancel("提示", res.data.error_msg, function () { });
        }
      },
      fail: function (res) {
        alertViewWithCancel("提示", res.data.error_msg, function () { });
      }
    });
    
  },
  // 城市选择滑动
  bindChange: function (e) {
    let that = this;
    let cityJson = [];
    let areaJson = [];
    const val = e.detail.value;
    let oldVal = that.data.val;
    that.setData({ val: val })
    let prev = val[0];
    let city = val[1];
    let area = val[2];
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        // 滑动省份获取城市
        if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
          city = 0; area = 0;
          that.setData({ valuecity: [prev, 0, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
          area = 0;
          that.setData({ valuecity: [prev, city, 0] })
        } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

        }
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
        }
        // 滑动城市获取区域
        for (let k = 0; k < json[prev].children[city].children.length; k++) {
          areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
        }
        that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area })
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
    let areaInfo = that.data.area;

    let prevId = prevInfo[that.data.prevIndex].id
    let cityId = cityInfo[that.data.cityIndex].id
    let areaId = areaInfo[that.data.areaIndex].id

    let prevText = prevInfo[that.data.prevIndex].text
    let cityText = cityInfo[that.data.cityIndex].text
    let areaText = areaInfo[that.data.areaIndex].text

    let prevCityAreaId = prevId + ',' + cityId + ',' + areaId
    let selectText = prevText + ' ' + cityText + ' ' + areaText;
    this.setData({ xzcity: '', isHideCity: true, selectText: selectText, cityId: prevCityAreaId });
  },
  selectHandle: function () {
    this.setData({ isHideCity: false })
  },
  ktTab: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let kt = e.currentTarget.dataset.text;
    that.setData({
      currentIndexOne: id,
      kt: kt
    })
  },
  ctTab: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let ct = e.currentTarget.dataset.text;
    that.setData({
      currentIndexTwo: id,
      ct: ct
    })
  },
  wsTab: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let ws = e.currentTarget.dataset.text;
    that.setData({
      currentIndexThree: id,
      ws: ws
    })
  },
  cfTab: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let cf = e.currentTarget.dataset.text;
    that.setData({
      currentIndexFour: id,
      cf: cf
    })
  },
  chooseBox: function () {
    this.setData({
      isHide: true
    })
  },
  sureBtn: function () {
    let style = this.data.kt + this.data.ct + this.data.ws + this.data.cf;
    if (style == '' || style == '0客0餐0卧0厨') {
      alertViewWithCancel("提示", "请至少选择一个户型", function () {
      });
      return;
    }
    this.setData({
      style: style,
      isHide: false
    })
  },
  Mianji: function (e) {
    this.setData({ mianji: e.detail.value })
  },
  inputPhone: function (e) {
    this.setData({ phone: e.detail.value })
  },
  toMianji: function () {
    this.setData({
      focus: true
    })
  }
})
