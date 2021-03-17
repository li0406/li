// pages/journal/journal.js
import { addJournal } from "../../utils/api.js"
const app = getApp();
var dateTimePicker = require('../../utils/dateTimePicker.js');
var dateObj = new Date();
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
    tabActive: true,
    companyName: '请选择',
    company_id:'',
    curCity:'',
    areaName:'',
    cs:'',
    qx:'',
    contactName: '',
    contactJob: '',
    tel: '',
    wechat: '',
    qq: '',
    companyAddress: '',
    khType: ['请选择', '新客户', '老客户'],
    khTypeIndex: 0,
    khSource: ['请选择','已签会员', '客服咨询转接','会员转介绍','后台注册公司','扫楼','同行网站','订单轰炸','空间营销','其他','合作页面'],
    khSourceIndex: 0,
    selectTextDefault: '请选择',
    cityText:'',
    areaText:'',
    way: ['请选择', '上门', '电话', 'QQ', '微信'],
    level: ['请选择', 'A', 'B', 'C'],
    wayIndex: 0,
    levelIndex: 0,
    signTime: ['请选择', '未签约', '已签约','本周内', '半月内', '1个月内', '3个月内', '暂无意向'],
    timeIndex:0,
    desc:'',
    preMoney:'',
    date: '2018-10-01',
    time: '12:00',
    visitStartTime: '请选择',
    visitEndTime: '请选择',
    contactTime: '请选择',
    dateTimeArray: null, //二维数组，保存年月日时分秒各列能选择的数据
    dateTime: null,   //用户保存翻动后时间的位置 
    defaultTime: null, //用户保存当前默认时间的位置
    startYear: 2000, //初始化开始年份
    endYear: 2030,   //初始化结束年份
    ymdStart:'',
    ymdEnd: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 获取完整的年月日 时分秒，以及默认显示的数组
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    // 精确到分的处理，将数组的秒去掉
    var lastArray = obj.dateTimeArray.pop();
    var lastTime = obj.dateTime.pop();
    this.setData({
      defaultTime: obj.dateTime,
      dateTime: obj.dateTime,
      dateTimeArray: obj.dateTimeArray,
    });
  },
  changeType: function (e) {
    this.setData({
      tabActive: e.currentTarget.dataset.type == "true"
    });
  },
  // 选择城市
  selectCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?curCity=' + city
    })
  },
  // 选择客户类型
  selectType: function (e) {
    this.setData({
      khTypeIndex: e.detail.value
    })
  },
  // 选择客户来源
  selectSource: function (e) {
    this.setData({
      khSourceIndex: e.detail.value
    })
  },
  // 选择谈判方式
  selectWay: function (e) {
    this.setData({
      wayIndex: e.detail.value
    })
  },
  // 选择意向等级
  selectLevel: function (e) {
    this.setData({
      levelIndex: e.detail.value
    })
  },
  // 选择签约时间
  selectSignTime: function (e) {
    let that = this;
    let company_id = that.data.company_id;
    if(company_id != ''){
      that.setData({
        timeIndex: e.detail.value
      })
    }else{
      if (e.detail.value == 2) {
        that.setData({
          timeIndex: 0
        })
      } else {
        that.setData({
          timeIndex: e.detail.value
        })
      }
    }
  },
  baseSave: function (e) {
    let that = this;
    let companyName = that.data.companyName;
    let company_id = that.data.company_id;
    let cs = that.data.cs;
    let qx = that.data.qx;
    let contactName = that.data.contactName;
    let contactJob = that.data.contactJob;
    let tel = that.data.tel;
    let wechat = that.data.wechat;
    let qq = that.data.qq;
    let khType = that.data.khTypeIndex;
    let khSource = that.data.khSourceIndex;
    let companyAddress = that.data.companyAddress;
    let intention = that.data.levelIndex;
    let visit_style = that.data.wayIndex;
    let visit_start_time = that.data.visitStartTime;
    let visit_end_time = that.data.visitEndTime;
    let qianyue_status = that.data.timeIndex;
    let desc = that.data.desc;
    let pre_money = that.data.preMoney;
    let visit_next_time = that.data.contactTime;
    let ymdStart = that.data.ymdStart;
    let ymdEnd = that.data.ymdEnd;

    if(companyName == '请选择'){
      alertViewWithCancel("保存失败", "请输入公司名称", function () {
      });
      return;
    }
    if (cs == '') {
      alertViewWithCancel("保存失败", "请输入城市区域", function () {
      });
      return;
    }
    if (contactName == '') {
      alertViewWithCancel("保存失败", "请输入联系人", function () {
      });
      return;
    }
    if (tel == '' && wechat == '' && qq == ''){
      alertViewWithCancel("保存失败", "请输入联系方式，手机、微信、QQ必须填写一项", function () {
      });
      return;
    }else if(tel != ''){
      var reg = new RegExp("^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请输入正确的手机号", function () {
        });
        return false;
      }
    }
    if (khType == 0){
      alertViewWithCancel("保存失败", "请选择客户类型", function () {
      });
      return;
    }
    if (khSource == 0) {
      alertViewWithCancel("保存失败", "请选择客户来源", function () {
      });
      return;
    } 
    if (companyAddress == '') {
      alertViewWithCancel("保存失败", "请输入公司地址", function () {
      });
      return;
    } 
    if (intention == 0) {
      alertViewWithCancel("保存失败", "请选择意向等级", function () {
      });
      return;
    }
    if (visit_style == 0) {
      alertViewWithCancel("保存失败", "请选择谈判方式", function () {
      });
      return;
    }
    if (visit_start_time == '请选择') {
      alertViewWithCancel("保存失败", "请选择拜访时间", function () {
      });
      return;
    } else if (visit_end_time == '请选择'){
      alertViewWithCancel("保存失败", "请选择拜访时间（止）", function () {
      });
      return;
    } else if (visit_start_time > visit_end_time) {
      alertViewWithCancel("保存失败", "请选择正确的拜访时间", function () {
      });
      return;
    } else if (ymdStart != ymdEnd){
      alertViewWithCancel("保存失败", "拜访时间请选择同一天", function () {
      });
      return;
    }
    // if (qianyue_status == 0) {
    //   alertViewWithCancel("保存失败", "请选择预计签约", function () {
    //   });
    //   return;
    // }
    if (desc == '') {
      alertViewWithCancel("保存失败", "请输入谈话内容", function () {
      });
      return;
    } else if (desc.length < 35){
      alertViewWithCancel("保存失败", "字数未满35字，请继续输入", function () {
      });
      return;
    }

    if (visit_next_time == '请选择') {
      alertViewWithCancel("保存失败", "请选择下次联系时间", function () {
      });
      return;
    }
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        addJournal('/v1/report/add_first_visit', {
          company_name: companyName,
          user_id: company_id,
          cs: cs,
          qx: qx,
          user_name: contactName,
          user_job: contactJob,
          user_tel: tel,
          user_wx: wechat,
          user_qq: qq,
          is_new: khType,
          customer_source: khSource,
          address: companyAddress,
          intention: intention,
          visit_style: visit_style,
          visit_start_time: visit_start_time,
          visit_end_time: visit_end_time,
          qianyue_status: qianyue_status,
          desc: desc,
          pre_money: pre_money,
          visit_next_time: visit_next_time,
        }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '保存成功',
                icon: 'success',
                duration: 200
              })
              setTimeout(function(){
                wx.navigateBack({
                  delta: 1,
                })
              },300)
            }else{
              alertViewWithCancel("保存失败", res.error_msg, function () {
              });
              return;
            }
          })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  changeDateTime1(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    var ymdStart = year + '-' + month + '-' + date;
    this.setData({
      dateTime: e.detail.value,
      visitStartTime: text,
      ymdStart: ymdStart
    });
  },
  changeDateTimeColumn1(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  changeDateTime2(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    var ymdEnd = year + '-' + month + '-' + date
    this.setData({
      dateTime: e.detail.value,
      visitEndTime: text,
      ymdEnd: ymdEnd
    });
  },
  changeDateTimeColumn2(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  changeDateTime3(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    this.setData({
      dateTime: e.detail.value,
      contactTime: text
    });
  },
  changeDateTimeColumn3(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
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
    let that = this;
    let companyName = that.data.companyName;
    let company_id = that.data.company_id;
    let curCity = that.data.curCity;
    let areaName = that.data.areaName;
    let cs = that.data.cs;
    let qx = that.data.qx;

    if (curCity !=''){
      that.setData({
        selectTextDefault:'',
        companyName: companyName,
        cityText: curCity,
        areaText: areaName,
      })
    }else{
      that.setData({
        selectTextDefault: '请选择',
        companyName: companyName,
        cityText: '',
        areaText: '',
      })
    }
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
  companyName: function (e) {
    wx.navigateTo({
      url: '../company/company',
    })
  },
  // companyName: function (e) {
  //   this.setData({
  //     companyName: e.detail.value
  //   })
  // },
  contactName: function (e) {
    this.setData({
      contactName: e.detail.value
    })
  },
  contactJob: function (e) {
    this.setData({
      contactJob: e.detail.value
    })
  },
  tel: function (e) {
    this.setData({
      tel: e.detail.value
    })
  },
  wechat: function (e) {
    this.setData({
      wechat: e.detail.value
    })
  },
  qq: function (e) {
    this.setData({
      qq: e.detail.value
    })
  },
  companyAddress: function (e) {
    this.setData({
      companyAddress: e.detail.value
    })
  },
  desc: function (e) {
    this.setData({
      desc: e.detail.value
    })
  },
  preMoney: function (e) {
    this.setData({
      preMoney: e.detail.value
    })
  },
})