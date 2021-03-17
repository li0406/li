const app = getApp()
let apiUrl = app.getApiUrl();
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
const fadan={
  closed:false,
  selectWin:true,
  winType:0,
  selectTextDefault:"请选择城市",
  selectText:"",
  xiaoqu:"",
  telNumber:"",
  name : "",
  area : "",
  prev: [],
  city: [],
  area: [],
  prevIndex: '0',
  cityIndex: '0',
  areaIndex: '0',
  valuecity: null,
  val:[],
  json: [],
  isHide: true,
  selectText: '',
  prevCityAreaId: '',
  orderid: '',
  oldData:[0,0,0],
  checkValue:true,
  configs: {},
  colorCont:[false,false,false,false,false],
  init:function(data,winType,setting){//添加方法
    let that = this;
    let json = [];
    let prevJson = [];
    let cityJson = [];
    let areaJson = [];
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
    wx.getStorage({
      key: 'cityJson',
      success: function (res) {
        console.log("获取城市缓存数据");
        console.log(res);
        let cityJsonNew = res.data;
        // IDE调试这条有效
        data.setData({ ["fd.prev"]: cityJsonNew.prev, ["fd.city"]: cityJsonNew.city, ["fd.area"]: cityJsonNew.area });
        // 真机调试这条有效
        that.prev = cityJsonNew.prev;
        that.city = cityJsonNew.city;
        that.area = cityJsonNew.area;
        console.log(data.data);
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
                console.log("ajax获取城市数据");
                console.log(res);
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
                data.setData({ ["fd.prev"]: prevJson, ["fd.city"]: cityJson, ["fd.area"]: areaJson, ["fd.json"]: json });
                that.prev = prevJson;
                that.city = cityJson;
                that.area = areaJson;
                that.json = json;

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


    data.openWin = function (){
      data.setData({
        ["fd.closed"]: true,
        ["fd.winType"]: winType
      });
    }
    data.closeWin=function() {
      data.setData({
        ["fd.closed"]: false
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
      let areaJson = [];
      let val = e.detail.value;
      let prev = val[0];
      let city = val[1];
      let area = val[2];
      let oldVal = data.data.fd.val;
      data.setData({ ["fd.val"]: val })
      //that.val = val;
      wx.getStorage({
        key: 'cityJson',
        success: function (res) {
          let json = res.data.json
          // 滑动省份获取城市
          if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
            city = 0; area = 0;
            that.setData({ ["fd.valuecity"]: [prev, 0, 0] })
          } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
            area = 0;
            that.setData({ ["fd.valuecity"]: [prev, city, 0] })
          } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

          }
          for (let j = 0; j < json[prev].children.length; j++) {
            cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text })
          }
          // 滑动城市获取区域
          for (let k = 0; k < json[prev].children[city].children.length; k++) {
            areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text })
          }
          //console.log(cityJson)
          that.setData({ ["fd.city"]: cityJson, ["fd.area"]: areaJson, ["fd.prevIndex"]: prev, ["fd.cityIndex"]: city, ["fd.areaIndex"]: area });
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
      let areaInfo = that.data.fd.area;
      let prevId = prevInfo[that.data.fd.prevIndex].id;
      let cityId = cityInfo[that.data.fd.cityIndex].id;
      let areaId = areaInfo[that.data.fd.areaIndex].id;
      //console.log(prevInfo[that.data.fd.prevIndex].id);
      let prevText = prevInfo[that.data.fd.prevIndex].text;
      let cityText = cityInfo[that.data.fd.cityIndex].text;
      let areaText = areaInfo[that.data.fd.areaIndex].text;

      let prevCityAreaId = prevId + ',' + cityId + ',' + areaId;
      let selectText = prevText + ' ' + cityText + ' ' + areaText;
      data.setData({ ["fd.selectTextDefault"]: '', ["fd.selectWin"]: true, ["fd.selectText"]: selectText, ["fd.prevCityAreaId"]: prevCityAreaId, ["fd.areaId"]: areaId, ["fd.colorCont[0]"]: true});
      
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
      console.log(data.data.fd.checkValue);
    }
    data.fadanBtn=function(e){//发单验证
    
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
          let reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
          let reg2 = new RegExp("^174|175[0-9]{8}$");
          if (!reg.test(tel)) {
            alertViewWithCancel("提示", "请输入正确的手机号码", function () {
            });
            return;
          }
          if (reg2.test(tel)) {
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

      wx.request({
        url: apiUrl + '/zxjt/submit_order/?src=' + app.globalData.sourceMark,
        data: {
          cs: data.data.fd.prevCityAreaId,
          qx: data.data.fd.areaId,
          name: xiaoqu,
          tel: tel
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

    data.setData({
      fd:this
    });
  },
}
module.exports = {
  fadan: fadan
}
