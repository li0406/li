// pages/consult/list.js
import {
  getConsult,
  postConsult
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}

function getTime(timestamp) {
  let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
  return Y + M + D;
}
Page({
  data: {
    curCity: '不限',
    curArea: '',
    cs: '',
    qx: '',
    createTime: '2010-10-10',
    nowTime: util.formatDate(new Date()),
    date_begin: '',
    date_end: '',
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
    // 请求数据
    list: [],
    pageCurrent: '0',
    pageTotalNumber: '0',
    noResult: false,
    page: false,
    pageNumber: [],
    parms: {
      city_id: '', //城市ID
      date_begin: '', //咨询日期-开始
      date_end: '', //咨询日期-结束
      keyword: '', //搜索关键词（公司名称、联系方式）
      record_status: '', //合作状态
      cooperation_type: '', //合作类型
      operate_status: '', //处理状态
      page: 1, //分页页码
      page_size: 10
    },
    recordStatusList: [],
    recordStatusIndex: 0,
    recordStatusId: '',
    cooperationTypeList: [],
    cooperationTypeIndex: 0,
    cooperationTypeId: '',
    operateStatusList: [],
    operateStatusIndex: 0,
    operateStatusId: ''

  },

  //城市选择
  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  //搜索框
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.keyword']: value,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  //选择时间
  dateChange: function (e) {
    var keys = e.currentTarget.dataset.time;
    var obj = {};
    obj[keys] = e.detail.value;
    if (keys == 'date_begin' && this.data.date_end != '' && e.detail.value > this.data.date_end) {
      obj.date_end = e.detail.value;
    }
    this.setData(obj);
    let that = this
    if (that.data.date_begin != '' && that.data.date_end != '') {
      let date_begin = that.data.date_begin
      let date_end = that.data.date_end
      that.setData({
        ['parms.date_begin']: date_begin,
        ['parms.date_end']: date_end,
        ['parms.page']: 1
      })
      that.getOrderList(that.data.parms);
    }
  },
  // 选项卡
  // 合作状态
  bindPickerChange_rs: function (e) {
    let id = this.data.recordStatusList[e.detail.value].id
    this.setData({
      recordStatusIndex: e.detail.value,
      recordStatusId: id
    })
    let that = this;
    that.setData({
      ['parms.record_status']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  // 合作类型
  bindPickerChange_ct: function (e) {
    let id = this.data.cooperationTypeList[e.detail.value].id
    this.setData({
      cooperationTypeIndex: e.detail.value,
      cooperationTypeId: id
    })
    let that = this;
    that.setData({
      ['parms.cooperation_type']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  // 处理状态
  bindPickerChange_os: function (e) {
    let id = this.data.operateStatusList[e.detail.value].id
    this.setData({
      operateStatusIndex: e.detail.value,
      operateStatusId: id
    })
    let that = this;
    that.setData({
      ['parms.operate_status']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  //筛选数据
  getFormOptions: function () {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        console.log(res)
        let token = res.data.token;
        getConsult('/v1/consult/options', '', {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          that.setData({
            recordStatusList: [{id:'',name:'请选择'}, ...res.data.record_status_list],
            cooperationTypeList: [{id:'',name:'请选择'}, ...res.data.cooperation_type_list],
            operateStatusList: [{id:'',name:'请选择'}, ...res.data.operate_status_list]
          })
        })
      }
    })
  },
  //打电话
  toTel(e) {
    let tel = e.currentTarget.dataset.tel
    wx.makePhoneCall({
      phoneNumber: tel
    })
  },
  // 处理
  toDeal(e) {
    console.log(e.currentTarget.dataset.id)
    let id = e.currentTarget.dataset.id
    alertViewWithCancel("提交", "是否确认该条信息已处理？", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          postConsult('/v1/consult/deal', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '处理成功',
                icon: 'success',
                duration: 200
              })
              that.getOrderList(that.data.parms);
            } else {
              alertViewWithCancel("处理失败", res.error_msg, function () {});
              return;
            }
          })
        }
      })
    })
  },
  // 新增记录
  toAdd(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'recordAdd?id=' + id,
    })
  },
  // 历史记录
  toRecord(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'recordList?id=' + id,
    })
  },

  //列表数据
  getOrderList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/consult/list',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            //获取页码
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            let datalist = res.data.list
            if (datalist.length < 1) {
              that.setData({
                list: [],
                page: false,
                noResult: true
              })
              return false
            }
            datalist.forEach((item, index) => {
              item.url = 'detail?id=' + item.id
            })
            let time_begin = res.data.options.date_begin
            let time_end = res.data.options.date_end
            that.setData({
              list: datalist,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              noResult: false,
              page: true,
              date_begin: time_begin,
              date_end: time_end
            })
            if (res.data.page.total_number < 10) {
              that.setData({
                page: false
              })
            } else {
              that.setData({
                page: true
              })
            }
          } else {
            that.setData({
              list: [],
              page: false,
              noResult: true,
              noInternet: false,

            })
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            page: false,
            noResult: false,
            //   noInternet: true
          })
        })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  //上一页
  prevBtn: function (e) {
    let that = this;
    let page = that.data.pageCurrent;
    if (page <= 1) {
      page = 1;
      return;
    } else {
      page = page - 1;
    }
    that.setData({
      ['parms.page']: page
    })
    that.getOrderList(that.data.parms)

  },
  //下一页
  nextBtn: function (e) {
    let that = this;
    let page = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (page >= max) {
      page = max;
      return;
    } else {
      page = page + 1;
    }
    that.setData({
      ['parms.page']: page
    })
    that.getOrderList(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  // 网络故障
  toCustom: function () {
    wx.navigateTo({
      url: '../consult/list'
    })
  },
  // 分页
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getOrderList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  onLoad: function (options) {
    this.getFormOptions()
  },

  onReady: function () {

  },

  onShow: function () {
    let that = this;
    that.setData({
      ['parms.date_begin']: that.data.date_begin,
      ['parms.date_end']: that.data.date_end,
      ['parms.record_status']: that.data.recordStatusId,
      ['parms.cooperation_type']: that.data.cooperationTypeId,
      ['parms.operate_status']: that.data.operateStatusId,
      ['parms.city_id']: that.data.cs
    });
    that.getOrderList(that.data.parms)
    if (that.data.list != '') {
      that.setData({
        noResult: false
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

  }
})