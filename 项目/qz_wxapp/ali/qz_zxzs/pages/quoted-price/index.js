const app = getApp();
let apiUrl = app.getApiUrl(),
    mpiUrl = 'https://mpapi.qizuang.com';

let config = require("../../config.js");

// 获取随机数的方法
function createRandomNum(Min, Max) {
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
    //轮播图部分
    indicatorDots: false,
    autoplay: true,
    interval: 2000,
    duration: 1500,
    swiperHeight: "113px",
    imgUrls: [
      {
        id: 0,
        url: "../../images/banner.jpg"
      }
    ],
    //发单部分
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
    customerPhone: "",
    houseArea: "",
    // 报价部分
    defaultBackgroundNumber: '00000000000',
    defaultNumber: '52800',
    timer: null
  },
  openCitySelectBox: function() {
    this.setData({
      isHideCity: !this.data.isHideCity
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
      areaId: areaId,
      serverVal: areaText,
      selectTextDefault: ''
    });
  },
  // onload 数据初始化
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
  },

  // ajax获取城市数据
    getCityData() {
        let that = this, provinceJson = [], cityJson = [], areaJson = [], allCityJson = [];
        // ajax请求数据并且数据本地缓存存储
        my.httpRequest({
            url: mpiUrl+'/v1/city/allcity',
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
                    },
                    fail: function() {
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

  /**
   * 城市选择滑动
   */
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

  randomPrice: function() {
    let that = this,
      timer = null;
    timer = setInterval(function() {
      let getNum = createRandomNum(30000, 120000);
      if (getNum > 99999) {
        that.setData({
          defaultBackgroundNumber: '0000000000',
          defaultNumber: getNum
        });
      } else {
        that.setData({
          defaultBackgroundNumber: '00000000000',
          defaultNumber: getNum
        });
      }
    }, 400)
    this.setData({
      timer: timer
    });
  },

  // 卸载页面的时候要清除定时器，否则会一直运行，拖慢手机系统
  onUnload: function() {
    clearInterval(this.data.timer);
  },
  setVillageName: function(e) {
    this.setData({
      villageName: e.detail.value
    });
  },
  setCustomerPhone: function(e) {
    this.setData({
      customerPhone: e.detail.value
    });
  },
  setHouseArea: function(e) {
    this.setData({
      houseArea: e.detail.value
    });
  },
  submitForm: function() {
    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);

    let that = this;
    if (that.data.selectText.length < 1) {
      //that.setData({ selectTextDefault: '请选择城市：'})
      alertViewWithCancel("提示", "请选择您的所在地区", function() { });
      return;
    } else {
      that.setData({ selectTextDefault: '' })
    }

    if (that.data.houseArea.length < 1) {
      alertViewWithCancel("提示", "请输入面积", function() {
        // console.log("点击确定按钮");
      });
      return;
    } else {
      if (isNaN(parseFloat(that.data.houseArea)) || parseFloat(that.data.houseArea) < 0) {
        alertViewWithCancel("提示", "请输入正确的面积，仅限数字", function() {
          // console.log("点击确定按钮");
        });
        return;
      }
    }

    if (that.data.villageName.length < 1) {
      alertViewWithCancel("提示", "请输入小区名称", function() {
        //that.setData({ boolName: true });
      });
      return;
    } else if (parseFloat(that.data.villageName) == that.data.villageName || that.data.villageName.replace(/\s*/g, "") < 1) {
      alertViewWithCancel("提示", "请输入正确的小区名称", function() {
        //that.setData({ boolName: true });
      });
      return;
    }


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

    // if (that.data.src) {
      my.httpRequest({

        url: apiUrl + '/zxjt/submit_order/?src=' + app.globalData.sourceMark,
        data: {
          cs: app.transformCity(that.data.selectText),
          mianji: that.data.houseArea,
          xiaoqu: that.data.villageName,
          tel: that.data.customerPhone
        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function(res) {
          //alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
          if(res.data.data){
            that.quotedPriceDetail(res.data.data.orderid);
          }else{
            my.alert({
                content: res.data.info
            });
		  }
        }
      });
    // } else {
    //   my.httpRequest({
    //     url: apiUrl + '/zxjt/submit_order/?src=' + config.service.sourceMark,
    //     data: {
    //       cs: app.transformCity(that.data.selectText),
    //       mianji: that.data.houseArea,
    //       xiaoqu: that.data.villageName,
    //       tel: that.data.customerPhone
    //     },
    //     header: {
    //       'content-type': 'application/x-www-form-urlencoded'
    //     },
    //     method: "POST",
    //     success: function(res) {
    //       console.log(res);
    //       //alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
    //     //   that.quotedPriceDetail(res.data.data.orderid);
		//   if(res.data.data){
		// 		that.quotedPriceDetail(res.data.data.orderid);
		//   }else{
		// 	  my.alert({
    //           	content: res.data.info
    //         });
		//   }
    //     }
    //   });
    // }

  },
  quotedPriceDetail: function(id) {
    my.navigateTo({
      url: '../quoted-price-detail/index?orderid=' + id,
    });
  }
});
