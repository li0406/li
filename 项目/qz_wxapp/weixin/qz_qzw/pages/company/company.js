// pages/company/company.js
const app = getApp();
var config = require('../../config');
const apiUrl = app.getApiUrl();
const imgUrl = app.getImgUrl();
const fadan = require('../../utils/fadan.js');
var QQMapWX = require('../../utils/qqmap-wx-jssdk.min.js');
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
    userid:'',
    imgUrl: imgUrl,
    isHide:[false,false,false],
    currentIndexOne:0,
    currentIndexTwo: 0,
    currentIndexThree: 0,
    downIcon: [true, true, true,],
    upIcon: [false, false,false],
    qyArr: [{ id: 0, name: "不限", cate: "buxian" }],  
    gmArr: [{ id: 0, name: "不限", cate: "buxian" }],    
    bzArr: [{ id: 0, name: "不限", cate: "buxian" }],          
    exampleArr:[],
    companyId:[],
    currentPage: 1,
    hasData: 1,
    scrollContainerHeight: '1455rpx',
    isUpdateScrollHeight: false,
    categoryNoData: 1, 
    fuwuquyu:'服务区域',
    gsSize:'公司规模',
    fwEnsure:'服务保障',
    cityJson:[],
    bm:'',
    gm:'',
    bz:'',
    cityId:'',
    isEmpty:true,
    inputphone: '',
    inputname:'',
    emptyphone: "",
    emptyname: "", 
    prev: [],
    city: [],
    area: [],
    prevIndex: '0',
    cityIndex: '0',
    areaIndex: '0',
    valuecity: null,
    json: [],
    isHideCity: true,
    selectText: '',
    xzcity: '请选择城市',
    prevCityAreaId: '',
    src: '',
    valuecity: [],
    val: [],
    checkValue1: true,
    clickAble:true
  },
  onLoad: function (options) {
    let that = this;
    wx.showLoading({
      title: "加载中"
    });
    //授权userId
    wx.getStorage({
      key: 'userId',
      success: function(res) {
        that.setData({
          userid: res.data
        })
      },
      fail: function(res) {
        app.newSq(that, function (data) {
          if(data===0){
            return
          }
          that.setData({
            userid: data.userId
          })
        });
      }
    })
    let qqmapsdk = new QQMapWX({
      key: config.service.qqMapKey
    });

    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        var latitude = res.latitude
        var longitude = res.longitude
        qqmapsdk.reverseGeocoder({
          location: {
            latitude: latitude,
            longitude: longitude
          },
          success: function (data) {
            let pro = data.result.address_component.province;
            let city = data.result.address_component.city;
            wx.getStorage({
              key: 'cityJson',
              success: function (res) {
                let json = res.data.json;
                if(json){
                  for (var i = 0; i < json.length; i++) {
                    if(json[i].text.indexOf(pro)!=-1 ){
                      for(var j = 0;j < json[i].children.length;j++){
                        if (city.indexOf(json[i].children[j].text)!=-1){
                          let cityId = json[i].children[j].id;             //城市id
              
                          wx.request({
                            url: apiUrl + '/getCityByCityName?',
                            data: {
                              cityname: json[i].children[j].text,
                            },
                            header: {
                              'content-type': 'application/json'
                            },
                            success: function (res) {
                              that.setData({
                                bm: res.data.data.bm,
                                // cityId: cityId
                              })
                              wx.request({
                                url: apiUrl + '/company?',
                                data: {
                                  bm: res.data.data.bm,
                                  // fu: cityId,
                                  p: '1',
                                  pagecount: 10
                                },
                                header: {
                                  'content-type': 'application/json'
                                },
                                success: function (res) {
                                  if (res.data.status == 1){
                                    let exampleArr = res.data.data.data;
                                    that.data.qyArr = that.data.qyArr.concat(res.data.data.navbar.area)
                                    that.data.gmArr = that.data.gmArr.concat(res.data.data.navbar.guimo)
                                    that.data.bzArr = that.data.bzArr.concat(res.data.data.navbar.baozhang)
                                    that.setData({
                                      exampleArr: exampleArr,
                                      qyArr: that.data.qyArr,
                                      gmArr: that.data.gmArr,
                                      bzArr: that.data.bzArr
                                    })
                                  }
                                  wx.hideLoading();
                                }
                              })
                            }
                          })
                        }
                      }
                    }else{
                      // console.log('error!')
                    }
                  }
                }
              }
            });
          },
          fail:function(){
            //拒绝地理位置 直接请求苏州
            wx.getStorage({
              key: 'cityJson',
              success: function (res) {
                let json = res.data.json;
                let cityId = json[15].children[4].id;             //城市id
                wx.request({
                  url: apiUrl + '/getCityByCityName?',
                  data: {
                    cityname: json[15].children[4].text,
                  },
                  header: {
                    'content-type': 'application/json'
                  },
                  success: function (res) {
                    that.setData({
                      bm: res.data.data.bm,
                      cityId: cityId
                    })
                    wx.request({
                      url: apiUrl + '/company?',
                      data: {
                        bm: 'sz',
                        p: '1',
                        pagecount: 10
                      },
                      header: {
                        'content-type': 'application/json'
                      },
                      success: function (res) {
                        if (res.data.status == 1){
                          let exampleArr = res.data.data.data;
                          that.data.qyArr = that.data.qyArr.concat(res.data.data.navbar.area)
                          that.data.gmArr = that.data.gmArr.concat(res.data.data.navbar.guimo)
                          that.data.bzArr = that.data.bzArr.concat(res.data.data.navbar.baozhang)
                          that.setData({
                            exampleArr: exampleArr,
                            qyArr: that.data.qyArr,
                            gmArr: that.data.gmArr,
                            bzArr: that.data.bzArr
                          })
                        }
                        wx.hideLoading();
                      }
                    })
                  }
                })
              } 
            });
          }
        })
      }
    })
    //发单引用
    fadan.fadan.init(this, 0, {
      cityInput: true,
      addressInput: true,
      phoneInput: true,
      nameInput: false,
      areaInput: false,
      btnText: "获取推荐(1-4家星级装修公司)",
    });
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
  inputPhone: function (e) {
    this.setData({ inputphone: e.detail.value })
  },
  inputName: function (e) {
    this.setData({ inputname: e.detail.value })
  },
  // 城市选择滑动
  bindChange1: function (e) {
    let that = this;
    let cityJson = [];
    let areaJson = [];
    const val = e.detail.value;
    let oldVal = that.data.val;
    that.setData({ val: val })
    // let json = that.data.json;
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

  },
  toSearch: function () {
    wx.navigateTo({
      url: '../zxgs_search/index'
    })
  },
  changeTab:function (e) {
    let num = e.currentTarget.dataset.id;
    for (let i = 0; i < 3; i++) {
      let tabitem = "isHide[" + i + "]";
      let down = "downIcon[" + i + "]";
      let up = "upIcon[" + i + "]";      
      if (i == num) {
        this.setData({
          [tabitem]: true,
          [down]: false,
          [up]: true,
        })
      } else {
        this.setData({
          [tabitem]: false,
          [down]: true,
          [up]: false,
        });
      }
    }
  },
  cancel:function (e) {
    for (let i = 0; i < 3; i++) {
      let tabitem = "isHide[" + i + "]";
      let down = "downIcon[" + i + "]";
      let up = "upIcon[" + i + "]"; 
      this.setData({
        [tabitem]: false,
        [down]: true,
        [up]: false,
      });
    }
  },
  serviceSetTab:function (e) {
    let that = this;
    let id = e ? e.currentTarget.dataset.id : options.id,
      index = e ? e.currentTarget.dataset.index : options.index;

      //设置栏目选中状态
      this.setData({
        currentIndexOne: id,
        isHide: false,
        downIcon:[true,true,true],
        upIcon:[false,false,false],
        cityId: id,
      });
      if (that.data.qyArr[index].name == '不限') {
        that.setData({
          fuwuquyu: "服务区域",
        })
      } else {
        that.setData({
          fuwuquyu: that.data.qyArr[index].name,
        })
      }
      wx.request({
        url: apiUrl + '/company?',
        data: {
          bm: that.data.bm,
          fu: id,
          gm:that.data.gm,
          bz:that.data.bz,
          p: '1',
          pagecount: 10
        },
        header: {
          'content-type': 'application/json'
        },
        success: function (res) {
          if (res.data.status == 0) {
            that.setData({ exampleArr: res.data.data, isEmpty: false })
          } else if (res.data.status == 1) {
            let data = res.data.data.data;
            that.setData({
              exampleArr: data,
              isEmpty: true
            })
          }
          
        }
      })
    

  },
  companySetTab: function (e) {
    let that = this;
    let id = e ? e.currentTarget.dataset.id : options.id,
      index = e ? e.currentTarget.dataset.index : options.index;
    //设置栏目选中状态
    this.setData({
      currentIndexTwo: id,
      isHide:false,
      downIcon: [true, true, true],
      upIcon: [false, false, false],
      gm: id,
    });
    if (that.data.gmArr[index].name == '不限') {
      that.setData({
        gsSize: "公司规模",
      })
    } else {
      that.setData({
        gsSize: that.data.gmArr[index].name,
      })
    }
    wx.request({
      url: apiUrl + '/company?',
      data: {
        bm: that.data.bm,
        fu:that.data.cityId,
        gm: id,
        bz:that.data.bz,
        p: '1',
        pagecount: 10,
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if (res.data.status == 0) {
          that.setData({ exampleArr: res.data.data, isEmpty: false })
        } else if (res.data.status == 1) {
          let data = res.data.data.data;
          that.setData({
            exampleArr: data,
            isEmpty: true
          })
        }
        
      }
    })
  },
  ensureSetTab: function (e) {
    let that = this;
    let id = e ? e.currentTarget.dataset.id : options.id,
      index = e ? e.currentTarget.dataset.index : options.index;

    //设置栏目选中状态
    this.setData({
      currentIndexThree: id,
      isHide: false,
      downIcon: [true, true, true],
      upIcon: [false, false, false],
      bz: id,
    });

    if (that.data.bzArr[index].name=='不限'){
      that.setData({
        fwEnsure: "服务保障",
      })
    }else{
      that.setData({
        fwEnsure: that.data.bzArr[index].name,
      })
    }
    wx.request({
      url: apiUrl + '/company?',
      data: {
        bm: that.data.bm,
        fu: that.data.cityId,
        gm: that.data.gm,
        p: '1',
        pagecount: 10,
        bz:id
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        if(res.data.status==0){
          that.setData({ exampleArr: res.data.data,isEmpty:false})
        }else if(res.data.status==1){         
          let data = res.data.data.data;
          that.setData({
            exampleArr: data,
            isEmpty: true
          })
        }
      }
    })

  },
  toCompanyDetail (e) {
    let id = e ? e.currentTarget.dataset.id : options.id;
    let bm = e ? e.currentTarget.dataset.bm : options.bm;
    wx.navigateTo({
      url: '../company_detail/company_detail?id=' + id + '&bm='+bm+'&classtype=9&userid='+this.data.userid+'&p=1&pagecount=10'
    });
  },
  getContentList: function (e) {
    let id = "",
      cateType = "",
      category = "",
      that = this,
      tempDataSet = [];
    // 从data选项中获取数据，下拉加载时无法获取到事件e
    cateType = that.data.cateType;
    category = that.data.category;
    // 处理非点击加载数据的情况，如初次打开页面，下拉加载数据,此时没有事件参数
    if (e) {
      if (e.currentTarget.dataset.id) {
        cateType = e.currentTarget.dataset.type;
      }
      category = e.currentTarget.dataset.category;
    }
    wx.showLoading({
      title: "加载中"
    })
    // ajax获取内容列表
    wx.request({
      url: apiUrl + '/company?',
      data: {
        bm: that.data.bm,
        // fu: that.data.cityId,
        p: that.data.currentPage,
        pagecount: 10
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        let data = res.data.data.data;
        // 点击选项卡的时候可能出现只有几条数据的情况，此时要更新scroll-view容器的高度，以免底线提示文字无法显示在可视区域
        that.data.isUpdateScrollHeight ? that.updateScrollHeight(data.length) : "";
        if (data.length > 0 && data.length < 10) {
          that.setData({
            hasData: 0
          });
        } else {
          that.setData({
            hasData: 1
          });
        }
        // 没有数据提示处理：只有查找的数据以及articleData中都没有数据，才能判定当前分类没有数据
        if (parseInt(data.length) == 0 && parseInt(that.data.exampleArr.length) == 0) {
          that.setData({
            categoryNoData: 0
          });
        } else {
          that.setData({
            categoryNoData: 1
          });
        }
        tempDataSet = that.data.exampleArr.concat(data);
        that.setData({
          exampleArr: tempDataSet
        })

        wx.hideLoading();
      },
      fail: function () {
        console.log("error!!!!");
        wx.hideLoading();
      }
    })
  },
  // 加载更多数据
  loadMoreData: function () {
    let page = this.data.currentPage;
    // 有数据就请求下一页，无数据就不再请求
    if (this.data.hasData) {
      this.setData({
        currentPage: ++page
      });
      this.getContentList();
    }
  },
  formSubmit1: function (e) {
    let that = this;
    var city = this.data.cityId;
    var name = e.currentTarget.dataset.name;
    var tel = e.currentTarget.dataset.phone;
    var check = that.data.checkValue1;
    if (that.data.clickAble) {
      alertViewWithCancel("提示", "请勿频繁操作", function () {
      });
      return
    }
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
    
    let nameReg = "^[a-zA-Z\u4e00-\u9fa5]+$";
    if (name < 1) {
      alertViewWithCancel("提示", "请输入您的称呼", function () {
      });
      return;
    } else if (name.search(nameReg) == -1) {
      alertViewWithCancel("提示", "请输入正确的名称，只支持中文和英文", function () {
      });
      return;
    }

    if (tel.length < 1) {
      alertViewWithCancel("提示", "请输入手机号", function () {
      });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请填写正确的手机号码", function () {
          that.setData({
            emptyphone: "",
          })
        });
        return false;
      }
    }

    if (!check) {
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
      });
      return;
    }
    that.setData({
      clickAble:false
    })
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
              clickAble: true
            });
            // app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
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
          name:name,
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
              clickAble: true
            });
            // app.globalData.personNum = parseInt(app.globalData.personNum) + 1;
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
          } else {
            alertViewWithCancel("提示", res.data.info, function () { });
          }

        }
      });
    }
  },
  // 切换免责
  checkboxChange1: function (e) {
    let that = this;
    if (that.data.checkValue1 == true) {
      that.setData({
        checkValue1: false
      })
    } else {
      that.setData({
        checkValue1: true
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
      path: 'pages/company/company',
      success: function (res) { },
      fail: function (res) { }
    }
  }
})