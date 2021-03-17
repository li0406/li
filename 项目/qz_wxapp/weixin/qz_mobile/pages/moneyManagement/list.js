// pages/moneyManagement/list.js
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
      company: '', //公司搜索（会员公司/ID）
      user_on: '', //会员状态（1：vip；2：暂停；3：到期）
      amount_min: '', //余额查询区间-最小金额
      amount_max: '', //余额查询区间-最大金额
      page: 1, //分页页码
      page_size: 10
    },
    recordStatusList: [{
        id: '',
        name: '全部'
      },
      {
        id: '1',
        name: 'VIP'
      },
      {
        id: '2',
        name: '暂停'
      },
      {
        id: '3',
        name: '到期'
      },
    ],
    recordStatusIndex: -1
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
      ['parms.company']: value,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  inputChangeMax(e){
    let value = e.detail.value
    this.setData({
      ['parms.amount_max']: value,
    })
    console.log(this.data.parms.amount_max)
  },
  inputChangeMin(e){
    let value = e.detail.value
    this.setData({
      ['parms.amount_min']: value,
    })
    console.log(this.data.parms.amount_min)
  },
  //大
  searchMax: function (e) {
    let that = this;
    let value = e.detail.value;
    let min = that.data.parms.amount_min
    console.log('大2',value)
    console.log('小2',min)
    console.log('判断2',min && min < value)
    if(isNaN(value)){
      alertViewNoCancel('提示', '请输入数字',function(){})
    }else{
      if(min && value < min){
        alertViewNoCancel('提示', '最小金额不能大于最大金额',function(){})
      }else{
        that.setData({
          ['parms.amount_max']: value,
          ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
      }
    }
  },
  //小
  searchMin: function (e) {
    let that = this;
    let value = e.detail.value;
    let max = that.data.parms.amount_max
    console.log('小1',value)
    console.log('大1',max)
    console.log('判断1',max && value < max)
    if(isNaN(value)){
      alertViewNoCancel('提示', '请输入数字',function(){})
    }else{
      if(max && max < value){
        alertViewNoCancel('提示', '最小金额不能大于最大金额',function(){})
      }else{
        that.setData({
          ['parms.amount_min']: value,
          ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);

      }
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
      ['parms.user_on']: id,
      ['parms.page']: 1
    })
    that.getOrderList(that.data.parms);
  },
  // 签单记录
  toSignList(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'signList?id=' + id,
    })
  },
  // 收支明细
  toAccountList(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'accountList?id=' + id,
    })
  },

  //列表数据
  getOrderList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/company/account_list',
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
  //复制
  copyBtn: function (e) {
    wx.setClipboardData({
      data: e.target.dataset.id,
      success(res) {
        wx.showToast({
          title: '公司名称已复制',
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
    
  },

  onReady: function () {

  },

  onShow: function () {
    let that = this;
    that.setData({
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