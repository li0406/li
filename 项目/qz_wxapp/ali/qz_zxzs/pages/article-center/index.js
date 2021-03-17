const app = getApp(),
      mpiUrl = 'https://mpapi.qizuang.com',
      apiUrl = app.getApiUrl();
    
let config = require("../../config.js");

function alertViewWithCancel(title, content, confirm) {
    my.alert({
        title: title || "提示",
        content: content || "消息提示",
        success: function () {
            confirm();
        }
    });
}

Page({
  data: {
    // 轮播图
    indicatorDots : true,
    autoplay : true,
    interval : 3000,
    duration : 1000,
    swiperHeight : "113px",
    circular : true,
    imgUrls : [
      {
        url: 'http://staticqn.qizuang.com/zhuanti/20170315/58c9066600cba-slt930.jpg',
        text: 'banner01'
      },
      {
        url: 'http://staticqn.qizuang.com/zhuanti/20170315/58c901f8eb65e-slt930.jpg',
        text: 'banner02'
      },
      {
        url: 'http://staticqn.qizuang.com/zhuanti/20160829/57c386c4d7ec2-slt930.jpg',
        text: 'banner03'
      },
      {
        url: 'http://staticqn.qizuang.com/zhuanti/20160823/57bc17501d653-slt930.jpg',
        text: 'banner04'
      },
      {
        url: 'http://staticqn.qizuang.com/zhuanti/20160823/57bc085f23986-slt930.jpg',
        text: 'banner05'
      }
    ],
    // 初始化数据
    materialArticle : null,
    materialCategory : "xcdg",
    partArticle : null,
    partCategory : "jubu",
    geomancyArticle : null,
    geomancyCategory : "fs",
    tab : [false,true,true],
    hidenSimulateInputOption: true,
    defaultSimulateInputText: '请选择您的装修日期',
    chooseColor : "",
    //发单部分
    isHideCity : true,
    province: [],
    city: [],
    area: [],
    json : [],
    provinceIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    valuecity: [0, 0, 0],
    val:[],
    selectTextDefault : "",
    selectText : "",
    customerName : "",
    customerPhone : ""
  },
  onLoad : function(options){
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
    
    // 攻略-选材导购
    my.httpRequest({
      url: apiUrl + '/appletcarousel/zxlclist',
      data: {
        count:2,
        category : that.data.materialCategory
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        that.setData({
          materialArticle : res.data
        })

      },
      fail: function () {
        console.log('error!!!!!!!!!!!!!!')
      }
    }),
    // 攻略-局部装修
    my.httpRequest({
      url: apiUrl + '/appletcarousel/zxlclist',
      data: {
        count: 2,
        category: that.data.partCategory
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        that.setData({
          partArticle: res.data
        })

      },
      fail: function () {
        console.log('error!!!!!!!!!!!!!!')
      }
    }),
    // 攻略-装修风水
    my.httpRequest({
      url: apiUrl + '/appletcarousel/zxlclist',
      data: {
        count: 2,
        category: that.data.geomancyCategory
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
        that.setData({
          geomancyArticle: res.data
        })

      },
      fail: function () {
        console.log('error!!!!!!!!!!!!!!')
      }
    })
  },
  toArticleList : function(e){
    let urlStr = e.currentTarget.dataset.urlStr,
        urlStrName = e.currentTarget.dataset.urlStrName;
    my.navigateTo({
      url: '../article-list/index?urlstr=' + urlStr + '&urlstrname=' + urlStrName
    })
  },
  toArticleDetail : function(e){
    let id = e.currentTarget.dataset.id;
    my.navigateTo({
        url: '../article-detail/index?id='+id
    })
  },
  activeThis : function(e){
    let tab = [true,true,true],
        index = e.currentTarget.dataset.index;
    tab[index] = false;
    this.setData({
      tab : tab
    });
  },
  //点击选择类型
  startSimulateInput: function () {
    var hidenSimulateInputOption = this.data.hidenSimulateInputOption;
    if (hidenSimulateInputOption) {
      this.setData({
        hidenSimulateInputOption: false,
      })
    } else {
      this.setData({
        hidenSimulateInputOption: true,
      })
    }
  },
  //点击切换
  mySelect: function (e) {
    this.setData({
      defaultSimulateInputText: e.target.dataset.me,
      hidenSimulateInputOption: true,
      chooseColor : "#333"
    })
  },
  // 城市选择控件 开始
  openCitySelectBox : function(){
    this.setData({
      isHideCity : !this.data.isHideCity
    });
  },
  closebtn : function(){
    this.setData({
      isHideCity : true
    });
  },
  okbtn : function(){
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
      selectTextDefault:''
    });
  },
  // 城市选择控件 结束
  
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
    citySelect: function (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let oldVal = that.data.val;
        let val = e.detail.value;
        that.setData({ val:val})
        let province = val[0];
        let city = val[1];
        let area = val[2];
        my.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data ? res.data.json : that.data.json;
                // 滑动省份获取城市
                if (oldVal[0] != province && oldVal[1] == city && oldVal[2] == area) {
                    city = 0; area = 0;
                    that.setData({ valuecity: [province,0,0]})
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
                that.setData({ city: cityJson, area: areaJson, provinceIndex: province, cityIndex: city, areaIndex: area });
            }
        });

    },
    setCustomerName:function(e){
        this.setData({ 
          customerName: e.detail.value 
        });
    },
    setCustomerPhone:function(e) {
        this.setData({ 
          customerPhone: e.detail.value 
        });
    },
    submitForm:function() {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);

        let that = this;
        if (that.data.selectText.length < 1) {
            //that.setData({ selectTextDefault: '请选择城市：'})
            alertViewWithCancel("提示", "请选择您的所在地区", function () { });
            return;
        } else {
            that.setData({ selectTextDefault: ''})
        }
          
        if (that.data.customerName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                //that.setData({ boolName: true });
            });
            return;
        } else if (that.data.customerName.search(re) == -1) {
            alertViewWithCancel("提示", "请输入正确的名称，只支持中文和英文", function () {
                //that.setData({ boolName: true });
            });
            return;
        }

        if (that.data.customerPhone.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () { });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(that.data.customerPhone)) {
                alertViewWithCancel("提示", "请输入正确的手机号", function () { });
                return false;
            }
        }

        // if (that.data.src) {
            my.httpRequest({

                url: apiUrl + '/zxjt/submit_order/?src=' + app.globalData.sourceMark,
                data: {
                    name: that.data.customerName,
                    tel: that.data.customerPhone,
                    cs: that.data.selectText
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
                },
                fail: function(res) {
                  alertViewWithCancel("提示", "提交失败", function() { });
                }
            });
        // } else {
        //     my.httpRequest({
        //         url: apiUrl + '/zxjt/submit_order/?src='+config.service.sourceMark,
        //         data: {
        //             name: that.data.customerName,
        //             tel: that.data.customerPhone,
        //             cs: that.data.selectText
        //         },
        //         header: {
        //             'content-type': 'application/x-www-form-urlencoded'
        //         },
        //         method: "POST",
        //         success: function (res) {
        //             alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
        //         }
        //     });
        // }

    },

});
