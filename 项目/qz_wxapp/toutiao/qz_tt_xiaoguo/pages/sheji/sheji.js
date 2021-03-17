// pages/sheiji/sheji.js
import { fadanHandle, allcityHandle } from "../../utils/api.js"
//显示带取消按钮的消息提示框
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
let app = getApp();
let apiUrl = app.getApiUrl();
Page({
    /**
     * 页面的初始数据
     */
    data: {
        indicatorDots: false,
        autoplay: false,
        interval: 5000,
        duration: 1000,
        circular: true,
        inputphone: '',
        inputfangan: '',
        mji: "",
        emptymianji:"",
        emptyxiaoqu:"",
        emptyphone:"",
        prev: [],
        city: [],
        prevIndex: '0',
        cityIndex: '0',
        valuecity: null,
        json: [],
        isHideCity: true,
        selectText: '',
        xzcity: '请选择城市',
        cityId: '',
        src:'',
        valuecity:[],
        val:[],
        checkValue:true,
        clickAble:true,
        autoGetPhone:true
    },
    // 装修设计表单提交1
    formSubmit: function (e) {
      let clickAble=this.data.clickAble;
      if (!clickAble){
        alertViewWithCancel("提示", "请勿频繁操作", function () {});
        return false;
      }
      let that = this;
      let city = this.data.cityId;
      let bjmj = e.currentTarget.dataset.mianji;
      let tel = e.currentTarget.dataset.phone;
      let xiaoqu = e.currentTarget.dataset.fangan;
      let check = that.data.checkValue;
      let srcRemark = this.data.src;
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
      //小区
      if (!app.checkFun.checkNull(xiaoqu, '请输入您的小区') || !app.checkFun.checkXiaoqu(xiaoqu)) return
      //联系方式
      if (!app.checkFun.checkNull(tel, '请输入您的手机号') || !app.checkFun.checkPhone(tel)) return

      if (!check) {
        alertViewWithCancel("提示", "请勾选我已阅读并同意齐装网的《免责声明》！", function () {
        });
        return false;
      }

      that.setData({
        clickAble:false
      });
      setTimeout(function(){
        that.setData({
          clickAble: true
        })
      },5000)
        
      fadanHandle('/v1/fb', {
        cs: that.data.cityId,
        mianji: bjmj,
        xiaoqu: xiaoqu,
        tel: tel,
        src: srcRemark,
        source:19092728
      }).then(data => {
         if(data.error_code == 0){
            that.setData({
              emptymianji: "",
              emptyxiaoqu: "",
              emptyphone: "",
              mji:"",
              inputfangan:"",
              inputphone:""
            });
            alertViewWithCancel("领取成功", "【齐装网】给小主请安！小齐已经接到您的装修圣旨，正马不停蹄赶来您的身边，并会在第一时间与您联系，请您注意接听哦，如有疑问请致电4006969336", function () { });
          }else{
            alertViewWithCancel("提示", data.error_msg, function () { });
          }
      })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
      var that = this;
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
      let that = this;
      let json = [];
      let prevJson = [];
      let cityJson = [];
      let cityUrl;

      // 获取缓存城市数据
      tt.getStorage({
        key: 'cityJson',
        success: function (res) {
          let cityJsonNew = res.data;
          that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city });
          app.getMyPosition(cityJsonNew, that);
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
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {
        this.setData({ isHideCity: true })
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
        tt.stopPullDownRefresh()
    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function (ops) {
      if (ops.from === 'button') {
        // 来自页面内转发按钮
      }
      return {
        title: '齐装网装修家居',
        path: 'pages/sheji/sheji',
        success: function (res) { },
        fail: function (res) { }
      }
    },
    Mianji: function (e) {
        this.setData({ mji: e.detail.value })
    },
    inputPhone: function (e) {
        this.setData({ inputphone: e.detail.value })
    },
    inputFangan: function (e) {
        this.setData({ inputfangan: e.detail.value })
    },
    // 城市选择滑动
    bindChange: function (e) {
      let that = this;
      let cityJson = [];
      const val = e.detail.value;
      let oldVal = that.data.val;
      that.setData({ val: val })
      let prev = val[0];
      let city = val[1];
      tt.getStorage({
        key: 'cityJson',
        success: function (res) {
          let json = res.data.json
          // 滑动省份获取城市
          if (oldVal[0] != prev && oldVal[1] == city) {
              city = 0; 
              that.setData({ valuecity: [prev, 0, 0] })
          } else if (oldVal[0] == prev && oldVal[1] != city ) {

              that.setData({ valuecity: [prev, city, 0] })
          } else if (oldVal[0] == prev && oldVal[1] == city ) {

          }
          for (let j = 0; j < json[prev].children.length; j++) {
              cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
          }

          that.setData({ city: cityJson, prevIndex: prev, cityIndex: city })
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
      let prevId = prevInfo[that.data.prevIndex].id;
      let cityId = cityInfo[that.data.cityIndex].id;
      let prevText = prevInfo[that.data.prevIndex].text;
      let cityText = cityInfo[that.data.cityIndex].text;
      let prevCityAreaId = prevId + ',' + cityId;
      let selectText = prevText + ' ' + cityText;
      this.setData({ xzcity: '',isHideCity: true, selectText: selectText, cityId: cityId });
    },
    selectHandle: function () {
      this.setData({ isHideCity: false })
    },
    // 切换免责
    checkboxChange: function (e) {
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
  getPhoneNumber: function (e) {
    app.getPhoneAuto(e, this)
  }
})