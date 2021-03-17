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
function alertViewNoCancel(title = "提示", content = "消息提示", confirm) {
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
    searchPlaceholder1: '请输入订单号、小区名称',
    searchPlaceholder2: '装修公司简称/全称/ID',
    parms: {
      apply_begin: '', //申请日期开始
      apply_end: '', //申请日期结束
      search_type: 1, //搜索类型（1：订单信息；2：申请公司）
      search_keyword: '', //搜索关键词
      exam_status: '', //审核状态（1：待审核；2：已通过；3：未通过）
      city_id: '', //城市ID
      page: 1, //分页页码
      page_size: 10
    },
    searchTypeList: [{
        id: '1',
        name: '订单信息'
      },
      {
        id: '2',
        name: '申请公司'
      }
    ],
    searchTypeIndex: '0',
    examStatusList: [
      {
        id: '',
        name: '请选择'
      },
      {
        id: '1',
        name: '预受理'
      },
      {
        id: '2',
        name: '已通过'
      },
      {
        id: '3',
        name: '未通过'
      },
      {
        id: '11',
        name: '待定单'
      }
    ],
    examStatusIndex: '0'
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
    console.log(value)
    that.setData({
      ['parms.search_keyword']: value,
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
    if(keys == 'date_begin' && !this.data.date_end){
      alertViewNoCancel('提示','请选择结束时间',function(){})
    }
    if(keys == 'date_end' && !this.data.date_begin){
      alertViewNoCancel('提示','请选择开始时间',function(){})
    }
    if (that.data.date_begin != '' && that.data.date_end != '') {
      let date_begin = that.data.date_begin
      let date_end = that.data.date_end
      that.setData({
        ['parms.apply_begin']: date_begin,
        ['parms.apply_end']: date_end,
        ['parms.page']: 1
      })
      that.getOrderList(that.data.parms);
    }
  },
  // 选项卡
  // 搜索类型
  bindPickerChange_st: function (e) {
    let that = this;
    let id = this.data.searchTypeList[e.detail.value].id
    that.setData({
      searchTypeIndex: e.detail.value,
      ['parms.search_type']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  // 审核状态
  bindPickerChange_es: function (e) {
    let that = this;
    let id = that.data.examStatusList[e.detail.value].id
    that.setData({
      examStatusIndex: e.detail.value,
      ['parms.exam_status']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  //列表数据
  getOrderList: function (parms) {
    let that = this;
    console.log(that.data.parms)
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/roundorder/apply_list',
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
            that.setData({
              list: datalist,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              noResult: false,
              page: true
            })
            console.log(that.data.list)
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
            noInternet: true
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
  //复制
  copyBtn: function (e) {
    wx.setClipboardData({
      data: e.target.dataset.id,
      success(res) {
        wx.showToast({
          title: '订单号已复制',
          duration: 1000
        })
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
    // this.getFormOptions()
  },

  onReady: function () {

  },

  onShow: function () {
    let that = this;
    that.setData({
      ['parms.city_id']: that.data.cs
    });
    that.getOrderList(that.data.parms)
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