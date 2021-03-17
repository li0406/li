const app = getApp();
let mpiUrl = 'https://mpapi.qizuang.com'
let apiUrl = app.getApiUrl(),
  imgUrl = app.getImgUrl();

let config = require("../../config.js");
// 获取随机数的方法
function GetRandomNum(Min, Max) {
  var Range = Max - Min;
  var Rand = Math.random();
  return (Min + Math.round(Rand * Range));
}

function alertViewWithCancel(title, content, confirm) {
  my.alert({
    title: title || "提示",
    content: content || "消息提示",
    success: function() {
      confirm();
    }
  });
}

Page({
  data: {
    info: false,
    lingNum: '00000000000',
    num: '52800',
    // 发单部分
    isHideCity: true,
    province: [],
    city: [],
    area: [],
    json: [],
    provinceIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    valuecity: [0, 0, 0],
    val: [],
    selectTextDefault: "",
    selectText: "",
    villageName: "",
    huName: "",
    customerPhone: "",
    houseArea: "",
    checkValue: true,
    isEmpty: [true, false],
    //装修时间
    zxTime: [
      {
        id: 3,
        content: '中式风格',
        checked: false
      },
      {
        id: 1,
        content: '现代简约',
        checked: false
      },
      {
        id: 2,
        content: '欧式奢华',
        checked: false
      },
      {
        id: 5,
        content: '北欧简约',
        checked: false
      },
      {
        id: 8,
        content: '时尚混搭',
        checked: false
      },
      {
        id: 4,
        content: '古典风格',
        checked: false
      }
    ],
    clickAble: true,
    fengge: ''
  },
  openCitySelectBox: function() {
    this.setData({
      isHideCity: !this.data.isHideCity
    });
  },
  setHouseArea: function(e) {
    this.setData({
      houseArea: e.detail.value
    });
  },
  setCustomerPhone: function(e) {
    this.setData({
      customerPhone: e.detail.value
    });
  },
  setVillageName: function(e) {
    this.setData({
      villageName: e.detail.value
    });
  },
  sethuName: function(e) {
    this.setData({
      huName: e.detail.value
    });
  },
  checkHandle: function(e) {
    let current = e.currentTarget.dataset.id
    let tempData = this.data.zxTime
    tempData.filter(item => {
      item.checked = false
      if (item.id == current) {
        item.checked = true
      }
    })
    this.setData({
      zxTime: tempData,
      fengge: current
    })
  },
  // 切换免责
  checkboxChange: function(e) {
    let that = this;
    if (e.detail.value == '') {
      that.setData({
        checkValue: false
      })
    } else {
      that.setData({
        checkValue: true
      })
    }
  },
  submitForm: function() {
    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);
    let that = this;
    // 验证地区
    if (that.data.selectText.length < 1) {
      //that.setData({ selectTextDefault: '请选择城市：'})
      alertViewWithCancel("提示", "请选择您的所在地区", function() { });
      return;
    } else {
      that.setData({ selectTextDefault: '' })
    }
    // 验证面积
    if (that.data.houseArea.length < 1) {
      alertViewWithCancel("提示", "请输入面积", function() {
      });
      return;
    } else {
      if (isNaN(parseFloat(that.data.houseArea)) || parseFloat(that.data.houseArea) < 1) {
        alertViewWithCancel("提示", "请输入正确的面积，仅限数字", function() {
        });
        return;
      }
    }
    // 验证手机号
    if (that.data.customerPhone.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function() { });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
      if (!reg.test(that.data.customerPhone)) {
        alertViewWithCancel("提示", "请输入正确的手机号", function() { });
        return false;
      }
    }
    if (that.data.checkValue == false) {
      alertViewWithCancel("提示", "请确定已阅读免责声明，并勾选。", function() { });
      return false;
    }
    if (that.data.src) {
      my.httpRequest({
        url: mpiUrl + '/v1/fb/',
        data: {
          cs: that.data.cityId,
          qx: that.data.areaId,
          mianji: that.data.houseArea,
          tel: that.data.customerPhone,
          source: 19101757,
          src:'zfbxcx-qzwzxjj'
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function(res) {
          if (res.data.error_code == 0) {
            that.setData({
              isEmpty: [false, true]
            })
          } else {
            alertViewWithCancel("提示", "当日订单发布超限", function() { });
          }
        }
      });
    } else {
      my.httpRequest({
        url: mpiUrl + '/v1/fb/',
        data: {
          cs: that.data.cityId,
          qx: that.data.areaId,
          mianji: that.data.houseArea,
          tel: that.data.customerPhone,
          source: 19101757,
          src:'zfbxcx-qzwzxjj'
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function(res) {
          if (res.data.error_code == 0) {
            that.setData({
              isEmpty: [false, true]
            })
          } else {
            alertViewWithCancel("提示", "当日订单发布超限", function() { });
          }
        }
      });
    }

  },
  submitTwo: function() {
    let that = this
    let call = that.data.huName
    let xiaoqu = that.data.villageName
    let fengge = that.data.fengge
    let cs = that.data.cityId
    let qx = that.data.areaId
    let tel = that.data.customerPhone
    console.log(call, xiaoqu, fengge, cs, qx, tel)
    let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
    if (call == "") {
      alertViewWithCancel("提示", "请输入称呼", function() {
        that.setData({
          call: "",
        })
      });
      return;
    } else if (!reg.test(call)) {
      alertViewWithCancel("提示", "请输入正确的名称", function() {
        that.setData({
          call: "",
        })
      });
      return;
    }
    if (call.length < 1) {
      alertViewWithCancel("提示", "请输入称呼", function() { });
      return;
    }

    if (xiaoqu == "") {
      alertViewWithCancel("提示", "请输入小区名称", function() {
        that.setData({
          xiaoqu: "",
        })
      });
      return;
    } else if (!reg.test(xiaoqu)) {
      alertViewWithCancel("提示", "请输入正确的名称", function() {
        that.setData({
          xiaoqu: "",
        })
      });
      return;
    }
    if (xiaoqu.length < 1) {
      alertViewWithCancel("提示", "请输入小区名称", function() { });
      return;
    }
    my.httpRequest({
      url: mpiUrl + '/v1/fb/',
      data: {
        cs: cs,
        qx: qx,
        tel: that.data.customerPhone,
        name: call,
        xiaoqu: xiaoqu,
        source: 19101757,
        fengge: fengge,
         src: app.globalData.sourceMark
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function(res) {
        if (res.data.error_code == 0) {
          my.navigateTo({
            url: '../jzjg/jzjg'
          })
        } else {
          alertViewWithCancel("提示", "当日订单发布超限", function() { });
        }
      }
    });
  },
  rule() {
    this.setData({
      info: true
    })
  },
  tapinfo() {
    this.setData({
      info: false
    })
  },
  onLoad: function() {
    let that = this,
      provinceJson = [],
      cityJson = [],
      areaJson = [];
    my.getStorage({
      key: 'cityJson',
      success: function(res) {
        let cityJsonNew = res.data;
        if (cityJsonNew) {
          that.setData({
            province: cityJsonNew.province,
            city: cityJsonNew.city,
            area: cityJsonNew.area
          });
        } else {
          that.getCityData();
        }
      },
      fail: function() {
      }
    });
    // 随机数
    let timer = setInterval(function() {
      let getNum = GetRandomNum(30000, 120000);
      if (getNum > 99999) {
        that.setData({ lingNum: '0000000000', num: getNum });
      } else {
        that.setData({ lingNum: '00000000000', num: getNum });
      }
    }, 400);
  },
  /**
 * 城市选择滑动
 */
  // ajax获取城市数据
  getCityData() {
    let that = this, provinceJson = [], cityJson = [], areaJson = [], allCityJson = [];
    // ajax请求数据并且数据本地缓存存储
    my.httpRequest({
      url: mpiUrl + '/v1/city/allcity',
      headers: {
        'content-type': 'application/json'
      },
      method: "get",
      dataType: "json",
      success: function(res) {
        provinceJson = res.data.prev
        cityJson = res.data.city
        areaJson = res.data.area
        allCityJson = res.data.json
        that.setData({
          province: provinceJson,
          city: cityJson,
          area: areaJson,
          json: allCityJson
        });
        // 设置缓存
        my.setStorage({
          key: 'cityJson',
          data: {
            province: provinceJson,
            city: cityJson,
            area: areaJson,
            json: allCityJson
          },
          success: function() {
            console.log("缓存设置成功")
          },
          fail: function() {
            console.log("缓存设置失败")
          }
        });
      },
      fail: function(res) {
        my.alert({
          content: "无法请求," + res.errorMessage + "," + res.error
        });
      }
    })
  },
  citySelect: function(e) {
    let that = this;
    let cityJson = [];
    let areaJson = [];
    let oldVal = that.data.val;
    let val = e.detail.value;

    that.setData({
      val: val
    })
    let province = val[0];
    let city = val[1];
    let area = val[2];
    my.getStorage({
      key: 'cityJson',
      success: function(res) {

        let json = res.data ? res.data.json : that.data.json;

        //滑动省份获取城市
        if (oldVal[0] != province && oldVal[1] == city && oldVal[2] == area) {
          city = 0; area = 0;
          that.setData({ valuecity: [province, 0, 0] })
        } else if (oldVal[0] == province && oldVal[1] != city && oldVal[2] == area) {
          area = 0;
          that.setData({ valuecity: [province, city, 0] })
        } else if (oldVal[0] == province && oldVal[1] == city && oldVal[2] != area) {

        }
        for (let j = 0; j < json[province].children.length; j++) {
          cityJson.push({ id: json[province].children[j].id, text: json[province].children[j].text });
        }
        // 滑动城市获取区域
        for (let k = 0; k < json[province].children[city].children.length; k++) {
          areaJson.push({ id: json[province].children[city].children[k].id, text: json[province].children[city].children[k].text });
        }
        that.setData({
          city: cityJson,
          area: areaJson,
          provinceIndex: province,
          cityIndex: city,
          areaIndex: area
        });
      }
    });
  },
  closebtn: function() {
    this.setData({
      isHideCity: true
    });
  },
  okbtn: function() {
    let that = this;
    let provinceInfo = that.data.province;
    let cityInfo = that.data.city;
    let areaInfo = that.data.area;

    let provinceId = provinceInfo[that.data.provinceIndex].id;
    let cityId = cityInfo[that.data.cityIndex].id;
    let areaId = areaInfo[that.data.areaIndex].id;

    let provinceText = provinceInfo[that.data.provinceIndex].text;
    let cityText = cityInfo[that.data.cityIndex].text;
    let areaText = areaInfo[that.data.areaIndex].text;

    let provinceCityAreaId = provinceId + ',' + cityId + ',' + areaId;
    let selectText = provinceText + ' ' + cityText + ' ' + areaText;
    this.setData({
      isHideCity: true,
      isColor: true,
      selectText: selectText,
      provinceCityAreaId: provinceCityAreaId,
      cityId: cityId,
      areaId: areaId,
      serverVal: areaText,
      selectTextDefault: ''
    });
  },
  gotop: function() {
    my.pageScrollTo({
      scrollTop: 200
    })
  }
});
