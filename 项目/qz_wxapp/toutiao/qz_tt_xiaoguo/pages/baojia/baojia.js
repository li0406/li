// pages/baojia/baojia.js
import { fadanHandle, allcityHandle } from "../../utils/api.js"
const app = getApp()
let apiUrl = app.getApiUrl();
// 获取随机数的方法
function GetRandomNum(Min, Max) {
  let Range = Max - Min;
  let Rand = Math.random();
  return (Min + Math.round(Rand * Range));
}
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  tt.showModal({
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
    mji: "",
    tel: "",
    name:"",
    xiaoqu:"",
    emptymianji:"",
    emptyphone:"",
    prev: [],
    city: [],
    cityId:'',
    prevIndex: '0',
    cityIndex: '0',
    valuecity: null,
    val: [],
    json: [],
    isHide: true,
    selectTextDefault: '选择城市',
    selectText: '',
    prevCityAreaId: '',
    num: '52800', 
    lingNum: '00000000000',
    colorCont: [false, false, false],
    src:"",
    isEmpty:[true,false],
    checkValue:true,
    clickAble:true,
  },
  zxbjxq: function (e) {
    tt.navigateTo({
      url: '../baojiaresult/baojiaresult?tel=' + e,
    });
  },
  onLoad: function (options) {
    let that = this;
    // 获取页面来源src
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
    tt.getStorageSync({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city });
        app.getMyPosition(cityJsonNew, that)
      },
      // 获取缓存失败
      fail: function () { 
        // ajax请求数据并且数据本地缓存存储
        allcityHandle('/v1/city/allcity',{
          level:2
        }).then(res => {
          that.setData({ prev: res.prev, city: res.city,  json: res.json });
          app.getMyPosition(res, that)
          tt.setStorage({
            key: 'cityJson',
            data: { prev: res.prev, city: res.city,  json: res.json }
          })
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
  ljjsbj:function(e) {
    let that = this;
    let clickAble = this.data.clickAble;
    let city = that.data.selectText;
    let bjmj = e.currentTarget.dataset.mianji;
    let tel = e.currentTarget.dataset.tel;
    let check = that.data.checkValue;
    let srcRemark = that.data.src;
    let reg_bjmj = /^\d+(\.\d+)?$/;
    //所在城市
    if (!app.checkFun.checkNull(city, "请选择城市")) return
    //房屋面积
    if (bjmj.length < 1) {
      alertViewWithCancel("提示", "请输入面积", function () { });
      return;
    } else {
      if (parseFloat(bjmj) > 1000 || parseFloat(bjmj) <= 0) {
        alertViewWithCancel("提示", "房屋面积请输入1-1000的数字", function () {});
        return;
      } else {
        if(!reg_bjmj.test(bjmj)){
          alertViewWithCancel("提示", "房屋面积请输入1-1000的数字", function () {});
      return;
        }
      }
    }
    //联系方式
    if (!app.checkFun.checkNull(tel, '请输入您的手机号') || !app.checkFun.checkPhone(tel)) return
    // 免责
    if (!check) {
      alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
      });
      return;
    }
    fadanHandle('/v1/fb', {
      cs: that.data.cityId,
      mianji: bjmj,
      tel: tel,
      src: srcRemark,
      source:19092744
    }).then(data => {
      if(data.error_code == 0){
        that.setData({
          isEmpty: [false, true],
        })
      }else{
        tt.showToast({
          title: data.error_msg,
          icon:"none"
        })
      }
    })
  },
  jsbj:function(e) {
    let that = this;
    let bjname = e.currentTarget.dataset.name;
    let bjxq = e.currentTarget.dataset.xiaoqu;
    let tel = e.currentTarget.dataset.tel;
    let clickAble = that.data.clickAble;
    let bjmj = that.data.mji;
    let srcRemark = that.data.src;
    if (!clickAble){
      alertViewWithCancel("提示", "请勿频繁操作", function () {
      });
      return false;
    }
    //姓名
    if (!app.checkFun.checkNull(bjname, '请输入您的姓名') || !app.checkFun.checkName(bjname)) return
    //小区
    if (!app.checkFun.checkNull(bjxq, '请输入您的小区') || !app.checkFun.checkXiaoqu(bjxq)) return
    fadanHandle('/v1/fb', {
      cs: that.data.cityId,
      name: bjname,
      xiaoqu: bjxq,
      mianji:bjmj,
      tel: tel,
      src: srcRemark,
      source:19062520
    }).then(data => {
      if(data.error_code == 0){
        that.setData({
          emptymianji:"",
          emptyphone:"",
          emptyname: "",
          emptyxiaoqu: "",
          mji: "",
          tel: "",
          isEmpty: [true, false],
        });
        that.zxbjxq(tel);
      }else{
        tt.showToast({
          title: data.error_msg,
          icon:"none"
        })
      }
    })
  },
  // 城市列表
  selectHandle: function () {
    let that = this;
    that.setData({ isHide: false })
  },
  closebtn: function () {
    this.setData({ isHide: true });
  },
  okbtn: function () {
    let that = this;
    let prevInfo = that.data.prev;
    let cityInfo = that.data.city;

    let prevId = prevInfo[that.data.prevIndex].id
    let cityId = cityInfo[that.data.cityIndex].id

    let prevText = prevInfo[that.data.prevIndex].text
    let cityText = cityInfo[that.data.cityIndex].text

    let prevCityAreaId = prevId + ',' + cityId 
    let selectText = prevText + ' ' + cityText ;
    this.setData({ isHide: true, colorCont: [true], selectText: selectText, cityId: cityId, selectTextDefault: ''  });
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
    tt.getStorage({
      key: 'cityJson',
      success: function (res) {
        let json = res.data.json
        if (oldVal[0] != prev && oldVal[1] == city ) {
          city = 0;
          that.setData({ valuecity: [prev, 0] })
        } else if (oldVal[0] == prev && oldVal[1] != city ) {
          that.setData({ valuecity: [prev, city] })
        } else if (oldVal[0] == prev && oldVal[1] == city) {
        }
        // 滑动省份获取城市
        for (let j = 0; j < json[prev].children.length; j++) {
          cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
        }
        that.setData({ city: cityJson, prevIndex: prev, cityIndex: city,  })
      }
    });

  },
  Mianji: function (e) {
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
})
