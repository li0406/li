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
    xgtId : "",
    imgPath : "",
    watchs : "",
    title : "",
    collectNumber : "",
    isCollect : 0,
    // 图片可点击提示
    hiddenMask:false,
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
    selectText : "",
    customerPhone : "",
    houseArea : ""
  },
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
  onLoad : function(options){
    let title = options.title;
    let that = this,
      provinceJson = [],
      cityJson = [],
      areaJson = [];
	  let userid = ''
		if(app.globalData.userInfo && app.globalData.userInfo.userId) {
			userid = app.globalData.userInfo.userId
			this.setData({
				userId: userid
			})
		}
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
    my.setNavigationBar({
        title: title,
    })
    that.setData({
      xgtId: options.id
    })
    app.getUserInfo(function (res) {
        that.setData({ 
            userId: res.userId 
        });
    })
    my.httpRequest({
      url: apiUrl + '/appletcarousel/meituDetails',
      data: {
        id: options.id,
        userid: that.data.userId,
        classtype : 8
      },
      header: {
        'Content-Type': 'application/json'
      },
      success: function (res) {
          //console.log(res);
        that.setData({
          imgPath: res.data.info[0].img_path,
          watchs:res.data.info[0].pv,
          title: res.data.info[0].title || '',
          collectNumber: res.data.info[0].is_collect,
          isCollect : res.data.info[0].is_collect
        })
        my.setNavigationBar({ 
          title: that.data.title
        })
      },
      fail: function () {
        console.log('error!!!!!!!!!!!!!!')
      }
    })

    let isKnowTips = app.getNewStorage('knowTips');
    if (isKnowTips == "false"){
       that.setData({
         hiddenMask:true
       })
    }else{
      that.setData({
        hiddenMask:false
      })
    }
  },
  iKonwStatus : function(){
    let that = this;
    that.setData({
      hiddenMask: true,
    });
    let isKnowTips = app.getNewStorage('knowTips');
    if(!isKnowTips){
      app.setNewStorage('knowTips', 'false', 604800);
    }
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
                        // my.alert({
                        //     content: "缓存设置成功"
                        // });
						console.log("缓存设置成功")
                    },
                    fail: function() {
                        // my.alert({
                        //     content: "缓存设置失败"
                        // });
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
    toPicDetail: function () {
      my.navigateTo({
        url : "../pic-detail/index?id=" + this.data.xgtId
      });
    },
    setCustomerPhone:function(e) {
        this.setData({ 
          customerPhone: e.detail.value 
        });
    },
    setHouseArea:function(e) {
        this.setData({ 
          houseArea: e.detail.value 
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
        if (that.data.houseArea.length < 1) {
            alertViewWithCancel("提示", "请输入面积", function () {
                // console.log("点击确定按钮");
            });
            return;
        } else {
            if (isNaN(parseFloat(that.data.houseArea)) || parseFloat(that.data.houseArea) < 0) {
                alertViewWithCancel("提示", "请输入正确的面积", function () {
                    // console.log("点击确定按钮");
                });
                return;
            }
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
                    cs: app.transformCity(that.data.selectText)
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
        //             cs: app.transformCity(that.data.selectText)
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
    /**
    * 重写分享函数，不重写，分享功能无效
    */
    onShareAppMessage: function (res) {
        if(!my.canIUse('button.open-type.share')){
            my.alert({
                content : "您的支付宝版本较低，请升级"
            });
            return;
        }
        return {
            title: this.data.title,
            path : "pages/xgt-detail/xgt-detail?id=" + this.data.xgtId + "&title=" + this.data.title,
            success: function (res) {
                // 转发成功
            },
            fail: function (res) {
                // 转发失败
            }
        }
    },
    collectAction : function(e){
        let id = this.data.xgtId,
            that = this,
            userId = '';
        
        if (that.data.userId){
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '8', // 效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if (res.data.state == 1) {
                        my.showToast({
                            content: '收藏成功',
                            type: 'success',
                            duration: 1200
                        });
                        let isCollect = 1;
                        that.setData({ 
                            isCollect: isCollect
                        })
                    }
                }
            });
        }else{
            app.getLoginAgain(function (res) {
                that.setData({
                    userId : res.userId
                });
                if(that.data.userId){
                    my.httpRequest({
                        url: apiUrl + '/appletcarousel/editcollect',
                        data: {
                            userid: that.data.userId,
                            classtype: '8', // 效果图
                            classid: id
                        },
                        header: {
                            'content-type': 'application/x-www-form-urlencoded'
                        },
                        method: "POST",
                        success: function (res) {
                            if (res.data.state == 1) {
                                my.showToast({
                                    content: '收藏成功',
                                    type: 'success',
                                    duration: 1200
                                });
                                let isCollect = 1;
                                that.setData({ 
                                    isCollect: isCollect
                                })
                            }
                        }
                    });
                }
            });
        }
  },
    cancelCollectAction:function(e){
        let id = this.data.xgtId,
            that = this;
        if (that.data.userId != '') {
            my.httpRequest({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId, // 用户ID
                    classtype: '8', // 效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state ==1){
                        my.showToast({
                            content: '你已取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        let isCollect = 0;
                        that.setData({ 
                            isCollect: isCollect
                        })
                    }
                }
            });
        }
    }

});
