const app = getApp()
let apiUrl = app.getApiUrl(),ImgUrl = app.getImgUrl();
const collect = require('../../utils/collectTool.js');
const fadan = require('../../utils/fadanClassTest.js');
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
    userid: "",
    ImgUrl: ImgUrl,
    classtype:"",
    cityId: '',
    selectText: '',
    fd:"",
    freeNum:0,
    autoplay:false,
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
    inputphone:'',
    telNumber: '',
    emptyNameValue: '',
    isColor: false,
    bodyData:null,
    currentIndex:1,
    checkValue:true,
    boxHeight:0,
    outheight:0,
    clickAble:true,
    clickLarge:false,
    setIndex:0,
    autoGetPhone: true
  },
  // 获取userid
  onLoad: function (options) {
    let that=this;
    var today=new Date();
    var todayTime=Date.parse(today.getFullYear()+"/"+(today.getMonth()+1)+"/"+today.getDate())/1000;
    var oldDayTime=Date.parse("2018/7/29")/1000;
    wx.getStorage({
      key: 'dateTime',
      success: function(res) {//获取缓存成功
        let oldTime=res.data;
        if (todayTime-oldTime>=86400){//缓存过期
          wx.setStorage({//设置新时间戳
            key: 'dateTime',
            data: todayTime
          });
          wx.setStorage({
            key: "freeNum",
            data: '188',
          })
          that.setData({
            freeNum: [1, 8, 8]
          });
        }else{//缓存未过期
          wx.getStorage({
            key: 'freeNum',
            success: function(res) {
              that.setData({
                freeNum: (res.data && res.data.toString()) ? res.data.toString().split(",") : 0
              });
            },
          })
        }
      },
      fail:function(){
        wx.setStorage({//设置新时间戳
          key: 'dateTime',
          data: todayTime
        });
        wx.setStorage({
          key: "freeNum",
          data: '188',
        })
        that.setData({
          freeNum: [1, 8, 8]
        });
      }
    });
    //获取userid
    wx.getStorage({
      key: 'userId',
      success: function (res) {
        that.setData({
          userid: res.data
        });
        that.getBodyData(options);
      },
      fail: function () {
        app.newSq(that, function (res) {
          if(res!==0){
            that.setData({
              userid: res.userId
            });
          }
          that.getBodyData(options);
        })
       
      }
    })
    
  },
  getBodyData: function (options){
    let that = this;
    let url = options.type == 4 ? "/meitudetail" :"/xgtdetail";
    that.setData({
      classtype: options.type
    })
    wx.request({
      url: apiUrl + url,
      data:{
        id:options.id,
        userid:that.data.userid
      },
      success:function(res){
       if(res.data.status==1){
         if (options.type==2){
           res.data.data.pv = parseInt(options.pv)+1;
           res.data.data.description = res.data.data.qc
         }
         for (let i = 0; i < res.data.data.imgs.length;i++){
           res.data.data.imgs[i].height=0;
         }
          that.setData({
            bodyData: res.data.data
          });
          wx.setNavigationBarTitle({
            title: res.data.data.title
          });
        }
       collect.collect.collectDetailInit(that, "bodyData");//收藏引用
      }
    })
  },
  changeCurrent:function(e){
    this.setData({
      currentIndex: e.detail.current+1
    })
  },
  quchuyy: function () {
    let that = this;
    that.setData({
      viewqieh: true,
    });
    let tiShi = app.getNewStorage('tiShi')
    if (!tiShi) {
      app.setNewStorage('tiShi', 'false', 86400)
    }
  },
  EventHandle: function (event) {
    var count = event.detail.current;
    this.data.Idex = count + 1;
    this.data.biaoti = this.data.shujv[count].title;
    this.setData({ Idex: this.data.Idex });
    this.setData({ biaoti: this.data.biaoti });
    wx.setNavigationBarTitle({ title: this.data.biaoti })
  },
  onShow: function () {
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];

    let cityUrl;
    wx.getStorage({
      key: 'telInfo',
      success: function (res) {
        that.setData({
          autoGetPhone: true,
          inputphone: res.data.tel,
          telNumber: res.data.tel
        })
      }
    })
    // 获取缓存城市数据
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city});
        app.getMyPosition(cityJsonNew, that)
      },
      // 获取缓存失败
      fail: function () {
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
    this.setData({ isHideCity: true, isColor: true, selectText: selectText, cityId: cityId, selectTextDefault: '' });
  },


  getName: function (e) {
    this.setData({ personName: e.detail.value });
  },
  getPhone: function (e) {
    this.setData({ telNumber: e.detail.value });
  },
  Name: function (e) {
    this.setData({ inputname: e.detail.value })
  },
  inputPhone: function (e) {
    this.setData({ inputphone: e.detail.value })
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

        that.setData({ city: cityJson,  prevIndex: prev, cityIndex: city })
      }
    });
  },

  closebtn: function () {
    this.setData({ isHideCity: true });
  },

  selectHandle: function () {
    this.setData({ isHideCity: false })
  },

  checkboxChange:function(e){
    this.setData({
      checkValue: !this.data.checkValue
    })
  },

  // 装修设计表单提交1
  formSubmit: function (e) {
    if (!this.data.clickAble) {
      alertViewWithCancel("提示", "请勿频繁操作", function () { });
      return false
    }
    let that = this;
    var city = this.data.cityId;
    var name = e.currentTarget.dataset.name;
    var tel = e.currentTarget.dataset.phone;

    let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
    let re = new RegExp(regu);
    let check = this.data.checkValue;

    if (city.length < 1) {
      that.setData({
        xzcity: '请选择城市',
      })
      alertViewWithCancel("提示", "请选择您的所在地区", function () { });
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
      alertViewWithCancel("提示", "请输入手机号码", function () {

      });
      return;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请输入正确的手机号码", function () {
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
      clickAble: false
    });
    setTimeout(function () {
      that.setData({
        clickAble: true
      });
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
          if(res.data.status==1){
            that.setData({
              inputname: "",
              inputphone: "",
              emptyNameValue:"",
              telNumber:""
            });
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
            
            let num = parseInt(that.data.freeNum.join("")) - 1;
            that.setData({
              freeNum: num.toString().split(",")
            });
            wx.setStorage({
              key: 'freeNum',
              data: that.data.freeNum
            })
          }else{
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
              inputname: "",
              inputphone: "",
              emptyNameValue: "",
              telNumber: ""
            });
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
            let num = parseInt(that.data.freeNum.join(""))-1;
            that.setData({
              freeNum:num.toString().split(",")
            });
            wx.setStorage({
              key: 'freeNum',
              data:that.data.freeNum
            })
          } else {
            alertViewWithCancel("提示", res.data.info, function () { });
          }
        }
      });
    }
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function (res) {
    let title = '';

    if(this.data.bodyData.title){
      title = this.data.bodyData.title
    }else{
      title = '齐装网装修家居'
    }

    if (res.from === 'button') {
      // 来自页面内转发按钮
      if(res.target.dataset && res.target.dataset.title) {
        title = res.target.dataset.title
      }else{
        title = '齐装网装修家居'
      }
      
    }

    return {
      title: title,
      path: 'pages/xgt_detail/index',
      success: function (res) {
        // 转发成功
      },
      fail: function (res) {
        // 转发失败
      }
    }
  },
  getAsThis:function(){
    var aa = new fadan.fadanClass(this, 2, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: true,
      areaInput: false,
      btnText: "马上获取"
    })
    this.openWin();
  },
  getPrice:function(){
    var aa = new fadan.fadanClass(this, 5, {
      cityInput: true,
      addressInput: false,
      phoneInput: true,
      nameInput: false,
      areaInput: true,
      btnText: "马上获取"
    })
    this.openWin();
   },
  changeWidth:function(e){
    let width=e.detail.width;
    let height=e.detail.height;
    let boxWidth=0;
    let index=e.target.dataset.index;
    wx.getSystemInfo({
      success: function (res) {
        boxWidth = res.windowWidth;
      }
    });
    let pe = width / height ;
    this.setData({
      ["bodyData.imgs[" + index + "].height"]: boxWidth/pe
    });
  },
  setLarge:function(e){
    let index=e.target.dataset.index;
    this.setData({
      clickLarge:true,
      setIndex:index
    });
  },
  setSmall: function () {
    this.setData({
      clickLarge:false
    });
  },
  getPhoneNumber: function (e) {
    app.getPhoneAuto(e, this)
  }
})