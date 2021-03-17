// pages/shaixuan/shaixuan.js
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
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    kehu: ['请选择','新客户','老客户'],
    way: ['请选择','上门','电话','QQ','微信'],
    level: ['请选择','A','B','C'],
    kehuIndex: 0,
    wayIndex: 0,
    levelIndex: 0,
    date: '2018-10-01',
    time: '12:00',
    startTimeText: '请选择',
    endTimeText: '请选择',
    nextStartTimeText: '请选择',
    nextEndTimeText: '请选择',
    dateTimeArray: null, //二维数组，保存年月日时分秒各列能选择的数据
    dateTime: null,   //用户保存翻动后时间的位置 
    defaultTime: null, //用户保存当前默认时间的位置
    startYear: 2000, //初始化开始年份
    endYear: 2030,   //初始化结束年份
    createTime: '2010-10-10',
    nowTime: util.formatDate(new Date())
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // 获取完整的年月日 时分秒，以及默认显示的数组
    let obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    // 精确到分的处理，将数组的秒去掉
    let lastArray = obj.dateTimeArray.pop();
    let lastTime = obj.dateTime.pop();
    // 筛选值
    let that = this;
    if(options){
      let company_style = options.company_style;
      let visit_style = options.visit_style;
      let level = options.level;
      let enter_start = options.enter_start;
      let enter_end = options.enter_end;
      let next_start = options.next_start;
      let next_end = options.next_end;
      that.setData({
        kehuIndex: company_style,
        wayIndex: visit_style,
        levelIndex: level,
        startTimeText: enter_start,
        endTimeText: enter_end,
        nextStartTimeText: next_start,
        nextEndTimeText: next_end,
        defaultTime: obj.dateTime,
        dateTime: obj.dateTime,
        dateTimeArray: obj.dateTimeArray,
      })
    }
  },
  // 选择客户类型
  selectKehu: function (e) {
    this.setData({
      kehuIndex: e.detail.value
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
  //选择时间
  dateChange1: function (e) {
    console.log(e)
    this.setData({
      startTimeText: e.detail.value
    });
  },
  dateChange2: function (e) {
    this.setData({
      endTimeText: e.detail.value
    });
  },
  dateChange3: function (e) {
    this.setData({
      nextStartTimeText: e.detail.value
    });
  },
  dateChange4: function (e) {
    this.setData({
      nextEndTimeText: e.detail.value
    });
  },
  reset: function (e) {
    this.setData({
      kehuIndex: 0,
      wayIndex: 0,
      levelIndex: 0,
      startTimeText: '',
      endTimeText: '',
      nextStartTimeText: '',
      nextEndTimeText: '',
    })
  },
  save: function (e) {
    let that = this;
    let company_style = that.data.kehuIndex;
    let visit_style = that.data.wayIndex;
    let level = that.data.levelIndex;
    let startTime = that.data.startTimeText;
    let endTime = that.data.endTimeText;
    let nextStartTime = that.data.nextStartTimeText;
    let nextEndTime = that.data.nextEndTimeText;
    if (company_style == '请选择') {
      company_style = '';
    }
    if (visit_style == '请选择') {
      visit_style = '';
    }
    if (level == '请选择') {
      level = '';
    }
    if (startTime == '请选择'){
      startTime = '';
    }
    if (endTime == '请选择') {
      endTime = '';
    }
    if (nextStartTime == '请选择') {
      nextStartTime = '';
    }
    if (nextEndTime == '请选择') {
      nextEndTime = '';
    }
    console.log(endTime)
    if (endTime != '' & startTime==''){
      alertViewWithCancel("保存失败", "请选择录入时间（起）", function () {
      });
      return;
    }
    if (startTime != '' & (endTime == '请选择' || endTime == '')){
      alertViewWithCancel("提示", '请选择录入结束时间', function () {
      });
      return
    } else if (startTime > endTime) {
      alertViewWithCancel("保存失败", "录入开始时间不能大于结束时间", function () {
      });
      return;
    } 
    if (nextEndTime != '' & nextStartTime == '') {
      alertViewWithCancel("保存失败", "请选择下次联系时间（起）", function () {
      });
      return;
    }
    if (nextStartTime != '' & (nextEndTime == '请选择' || nextEndTime == '')) {
      alertViewWithCancel("提示", '请选择下次联系时间（止）', function () {
      });
      return
    } else if (nextStartTime > nextEndTime) {
      alertViewWithCancel("保存失败", "下次联系时间（止）不能早于下次联系时间（起）", function () {
      });
      return;
    } 
    let pages = getCurrentPages();//当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
    let prevPage = pages[pages.length - 2];//上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）
    prevPage.setData({
      company_style: company_style,
      visit_style: visit_style,
      level: level,
      enter_start: startTime,
      enter_end: endTime,
      next_start:nextStartTime,
      next_end: nextEndTime
    })
    wx.navigateBack({
      delta: 1,
    })
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

  }
})