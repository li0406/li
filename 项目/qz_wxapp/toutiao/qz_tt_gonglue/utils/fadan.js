const app = getApp()
let apiUrl = app.getAppUrl();
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
const fadan={
  closed:false,
  selectWin:true,
  winType:0,
  selectTextDefault:"请选择城市",
  selectText:"",
  xiaoqu:"",
  telNumber:"",
  emptyPhoneValue:"",
  name : "",
  area : "",
  prev: [],
  city: [],
  clickAble: true,
  prevIndex: '0',
  cityIndex: '0',

  valuecity: null,
  val:[],
  json: [],
  isHide: true,
  prevCityAreaId: '',
  orderid: '',
  oldData:[0,0,0],
  checkValue:true,
  configs: {},
  autoGetPhone:true,
  colorCont:[false,false,false,false,false],
  init:function(data,winType,setting){//添加方法
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];

    let cityUrl;
    let defaults = {
      cityInput: true,//城市选择
      addressInput: true,//小区名称
      phoneInput: true,//电话号码
      nameInput: true,//客户名称
      areaInput: true,//面积
      btnText: "马上获取"//按钮文字
    };
    this.configs = Object.assign(defaults, setting);
    
    //请求城市
    tt.getStorage({
      key: 'cityJson',
      success: function (res) {
        let cityJsonNew = res.data;
        // IDE调试这条有效
        data.setData({ ["fd.prev"]: cityJsonNew.prev, ["fd.city"]: cityJsonNew.city});
        // 真机调试这条有效
        that.prev = cityJsonNew.prev;
        that.city = cityJsonNew.city;
        app.getMyPosition(cityJsonNew, data)
      },
      // 获取缓存失败
      fail: function () {
        // ajax请求数据并且数据本地缓存存储
        tt.request({
          url: apiUrl + '/city/allcity?level=2',
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            data.setData({ ["fd.prev"]: res.data.prev, ["fd.city"]: res.data.city, ["fd.json"]: res.data.json });
            app.getMyPosition(res.data, data)
            tt.setStorage({
              key: 'cityJson',
              data: { prev: res.data.prev, city: res.data.city, json: res.data.json },
            })
          }
        })
      }
    });
    data.openWin = function (){
      data.setData({
        ["fd.closed"]: true,
        ["fd.winType"]: winType
      });
    }
    data.closeWin=function() {
      data.setData({
        ["fd.closed"]: false,
        ["fd.xiaoqu"]: ""
      });
    }
    data.openCitySelect=function() {
      data.setData({
        ["fd.selectWin"]: false
      });
    }
    data.bindChange=function(e){//城市滑动
      let that = this;
      let cityJson = [];

      let val = e.detail.value;
      let prev = val[0];
      let city = val[1];

      let oldVal = data.data.fd.val;
      data.setData({ ["fd.val"]: val })
      //that.val = val;
      tt.getStorage({
        key: 'cityJson',
        success: function (res) {
          let json = res.data.json
          // 滑动省份获取城市
          if (oldVal[0] != prev && oldVal[1] == city ) {
            city = 0;
            that.setData({ ["fd.valuecity"]: [prev, 0] })
          } else if (oldVal[0] == prev && oldVal[1] != city ) {
            that.setData({ ["fd.valuecity"]: [prev, city] })
          } else if (oldVal[0] == prev && oldVal[1] == city ) {

          }
          for (let j = 0; j < json[prev].children.length; j++) {
            cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
          }
         
          that.setData({ ["fd.city"]: cityJson, ["fd.prevIndex"]: prev, ["fd.cityIndex"]: city });
        }
      });       
    }
    data.closeCitySelect=function(){
      data.setData({
        ["fd.selectWin"]: true
      });

    }
    data.okCitySelect=function(){

      let that = this;
      let prevInfo = that.data.fd.prev;
      let cityInfo = that.data.fd.city;

      let prevId = prevInfo[that.data.fd.prevIndex].id;
      let cityId = cityInfo[that.data.fd.cityIndex].id;


      let prevText = prevInfo[that.data.fd.prevIndex].text;
      let cityText = cityInfo[that.data.fd.cityIndex].text;


      let prevCityAreaId = prevId + ',' + cityId;
      let selectText = prevText + ' ' + cityText;
      data.setData({ ["fd.selectTextDefault"]: '', ["fd.selectWin"]: true, ["fd.selectText"]: selectText, ["fd.cityId"]: cityId, ["fd.colorCont[0]"]: true});
      
    }
    data.checkboxChange = function(e){
      if (data.data.fd.checkValue == true){
        data.setData({
          ["fd.checkValue"]: false
        })
      }else{
        data.setData({
          ["fd.checkValue"]: true
        })
      }
       
    }
    data.fadanBtn=function(e){//发单验证
      if (!data.data.fd.clickAble) {
        alertViewWithCancel("提示", "请勿频繁操作", function () {
        });
        return
      }
      let that = this;
      var xiaoqu = data.data.fd.xiaoqu
      var tel = data.data.fd.telNumber
      var city = data.data.fd.selectText;
      var area = data.data.fd.area;
      var name = data.data.fd.name;
      var check = data.data.fd.checkValue;
      var configs = this.data.fd.configs;
      if (configs.cityInput){
        //console.log("需要验证城市选择");
        if (city.length < 1) {
          alertViewWithCancel("提示", "请选择您所在的地区", function () {
          });
          return;
        }
      }
      if (configs.addressInput) {
        //console.log("需要小区名称");
        var reg4 = /^\s*$/g;
        if (xiaoqu == "" || reg4.test(xiaoqu)) {
          alertViewWithCancel("提示", "请输入小区名称", function () {
          });
          return;
        } else if (!isNaN(xiaoqu)) {
          alertViewWithCancel("提示", "小区名称不能为纯数字", function () {
          });
          return;
        } 
      }
      if (configs.areaInput) {
        // console.log("需要面积");
        if (area < 1) {
          alertViewWithCancel("提示", "请输入面积", function () {
          });
          return;
        } else {
          if (isNaN(parseFloat(area)) || parseFloat(area) < 0) {
            alertViewWithCancel("提示", "请输入正确的面积", function () {
            });
            return;
          }
        }
      }
      if (configs.nameInput) {
        // console.log("需要验证客户姓名");
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
      }
      if (configs.phoneInput) {
        //console.log("需要验证手机号");
        if (tel.length < 1) {
          alertViewWithCancel("提示", "请输入您的手机号码", function () {
          });
          return;
        } else {
          let reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
          if (!reg.test(tel)) {
            alertViewWithCancel("提示", "请输入正确的手机号码", function () {
            });
            return;
          }
        }
        if (!check) {
          // console.log("需要验证是否打勾");
          alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
          });
          return;
        }
      }
      data.setData({
        ["fd.clickAble"]: false
      })
      tt.request({
        url: apiUrl + '/fb_order?src=' + app.globalData.sourceMark + '&gdt_vid=' + app.globalData.gdt_vid + "&aid=" + app.globalData.aid,
        data: {
          cs: data.data.fd.cityId,
          xiaoqu: xiaoqu,
          name:name,
          tel: tel,
          gdt_vid: app.globalData.gdt_vid,
          aid: app.globalData.aid

        },
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        method: "POST",
        success: function (res) {
          alertViewWithCancel("提示", "获取成功,稍后我们将联系您", function () {
            data.setData({
              ["fd.closed"]: false,
              ["fd.selectTextDefault"]:"请选择城市",
              ["fd.selectText"]: "",
              ["fd.xiaoqu"]: "",
              ["fd.clickAble"]: true,
              ["fd.telNumber"]:""
            });
          });
        } 
      });   

     }
    data.getArea=function(e){
      data.setData({ 
        ["fd.xiaoqu"]: e.detail.value,
        ["fd.colorCont[1]"]: true
      });
    }
    data.getPhone=function(e){
      data.setData({
        ["fd.telNumber"]: e.detail.value,
        ["fd.colorCont[2]"]: true
      });
    }
    data.getUserName = function (e) {
      data.setData({
        ["fd.name"]: e.detail.value,
        ["fd.colorCont[3]"]: true
      });
    }
    data.getMianJi = function (e) {
      data.setData({
        ["fd.area"]: e.detail.value,
        ["fd.colorCont[4]"]: true
      });
    }
    data.getPhoneNumber = function (e) {
      app.getPhoneAuto(e, data,1)
    }
    data.setData({
      fd:this
    });
    tt.getStorage({
      key: 'telInfo',
      success: function (res) {
        data.setData({
          ["fd.autoGetPhone"]: true,
          ["fd.telNumber"]: res.data.tel,
          ['fd.emptyPhoneValue']: res.data.tel
        })
      }
    })
  }
}
module.exports = {
  fadan: fadan
}
