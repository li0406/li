const app = getApp()
let apiUrl = app.getApiUrl();
let imgUrl = app.getImgUrl();
// let cityUrl = app.getCityDataUrl();
// console.log(cityUrl)
const navActive = require('../../utils/util.js');

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
// 获取随机数的方法
function GetRandomNum(Min, Max) {
  var Range = Max - Min;
  var Rand = Math.random();
  return (Min + Math.round(Rand * Range));
}
Page({
  /**
   * 页面的初始数据
   */
  data: {
    mji: "",
    tel: "",
    name:"",
    xiaoqu:"",
    emptymianji:"",
    emptyphone:"",
    countys: [],
    county: '',
    condition: false,
    prev: [],
    city: [],
    area: [],
    prevIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
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
    src:"",
    flag:0,
    isEmpty:[true,false],
    checkValue:true
  },
  zxbjxq: function (e) {
    console.log(e+"11111");
    wx.navigateTo({
      url: '../zuangxbjxq/zuangxbjxq?orderid=' + e,
    });
  },
  bindRegionChange: function (e) {
    // console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      region: e.detail.value
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let areaJson = [];
    let cityUrl;
    wx.currentUrl = getCurrentPages()[getCurrentPages().length - 1].route;//获取当前页面url
    this.setData({
      navList: navActive.activeNav(wx.currentUrl)
    });

    if (options.src) {
      that.setData({
        src: options.src
      });
    } else {
      wx.getStorage({
        key: 'src',
        success: function (res) {
          that.setData({
            src:res.data
          });
        },
      })
    }
    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        console.log(23,res)
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
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
            host[0] = host[0] + 's';
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
    // 随机数
    let timer = setInterval(function () {
      let getNum = GetRandomNum(30000, 120000);
      if (getNum > 99999) {
        that.setData({ lingNum: '0000000000', num: getNum });
      } else {
        that.setData({ lingNum: '00000000000', num: getNum });
      }
    }, 400);
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
    wx.stopPullDownRefresh()
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
  // 切换免责
  checkboxChange : function (e) {
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
  boda: function () {
    wx.makePhoneCall({
      phoneNumber: '4008659600',
      success: function () {
        // console.log('成功拨打电话')

      }
    })
  },
  ljjsbjff: function (e) {
    let that = this;
    var bjmj = e.currentTarget.dataset.mianji
    var bjxq = e.currentTarget.dataset.xiaoqu
    var tel = e.currentTarget.dataset.tel
    var city = that.data.selectText;
    var check = that.data.checkValue;
    if (city.length < 1) {
      alertViewWithCancel("提示", "请选择您的地区", function () {
        // console.log("点击确定按钮");
      });
      return;
    }
    if (bjmj.length < 1) {
      alertViewWithCancel("提示", "请输入面积", function () {
        // console.log("点击确定按钮");
      });
      return;
    } else {
      var reg3 = new RegExp("^[1-9][0-9]{0,3}$");
      if (!reg3.test(bjmj)) {
        alertViewWithCancel("提示", "房屋面积请输入1-10000的数字", function () { });
        return;
      }
    }
    
    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入您的手机号码", function () {
        // console.log("点击确定按钮");
      });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      var reg2 = new RegExp("^174|175[0-9]{8}$");
      if (!reg.test(tel) || reg2.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () {
        });
        // console.log(" ^_^!");
        return false;
      }
    }
    if (!check) {
      // console.log("需要验证是否打勾");
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
      });
      return;
    }

    wx.request({
      url: apiUrl + '/fb_order' ,
      data: {
        cs: that.data.cityId,
        qx: that.data.areaId,
        mianji: bjmj,
        tel: tel,
        fb_type: 'baojia',
        // qx: 320508
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function (res) {
        console.log(res)
        that.setData({
          // emptymianji: "",
          // emptyphone: "",
          isEmpty:[false,true],
          orderid: res.data.data.orderid
        })
        console.log(that.data.orderid)
      }
    });
  },
  jsbj:function(e) {
    let that = this;
    var bjname = e.currentTarget.dataset.name;
    var bjxq = e.currentTarget.dataset.xiaoqu;

    if (bjname.length < 1) {
      alertViewWithCancel("提示", "请输入称呼", function () {
      });
      return;
    }
    var reg = /^\s*$/g;
    if (bjxq == "" || reg.test(bjxq)) {
      alertViewWithCancel("提示", "请输入小区名称", function () {
        that.setData({
          emptyxiaoqu: "",
        })
      });
      return;
    } else if (!isNaN(bjxq)) {
      alertViewWithCancel("提示", "小区名称不能为纯数字", function () {
        that.setData({
          emptyxiaoqu: "",
        })
      });
      return;
    } 
    if (bjxq.length < 1) {
      alertViewWithCancel("提示", "请输入小区名称", function () {
      });
      return;
    }
    wx.request({
      url: apiUrl + '/fb_order/?src=' + that.data.src,
      data: {
        name: bjname,
        bjxq: bjxq
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        // 'REFERER': 'appapi.qizuang.com'
      },
      method: "POST",
      success: function (res) {
        // that.setData({
        //   emptyname: "",
        //   emptyxiaoqu: "",
        // })

        that.zxbjxq(that.data.orderid);
        console.log(that.data.orderid);

      }
    });
  },
  Mianji: function (e) {
    // console.log(e)
    this.setData({
      mji: e.detail.value
    })
  },
  Phone: function (e) {
    this.setData({
      tel: e.detail.value
    })
  },
  Username: function (e) {
    this.setData({
      name: e.detail.value
    })
  },
  Zsxq: function (e) {
    this.setData({
      xiaoqu: e.detail.value
    })
  },
  // 城市选择滑动
  bindChange: function (e) {
    console.log(1222,e)
    let that = this;
    let cityJson = [];
    let areaJson = [];
    const val = e.detail.value;
    // let json = that.data.json;
    let prev = val[0];
    let city = val[1];
    let area = val[2];
    let oldVal = that.data.val;
    console.log(that)
    that.setData({ val: val })
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
          city = 0; area = 0;
          that.setData({ valuecity: [prev, 0, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
          area = 0;
          that.setData({ valuecity: [prev, city, 0] })
        } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {
        }
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
        }
        // 滑动城市获取区域
        for (let k = 0; k < json[prev].children[city].children.length; k++) {
          areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text })
        }
        that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area })
      }
    });

  },
  closebtn: function () {
    this.setData({ isHide: true });
  },
  okbtn: function () {
    let that = this;
    // console.log(that)
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
    this.setData({ isHide: true, colorCont: [true], selectText: selectText, cityId: cityId, areaId: areaId, serverVal: areaText, selectTextDefault: ''  });
  },
  selectHandle: function () {
    let that = this;
    that.setData({ isHide: false })
  }
})
