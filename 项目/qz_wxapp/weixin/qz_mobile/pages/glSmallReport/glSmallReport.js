import {
  baobeiList,
  smalladd
} from "../../utils/api.js"

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function (res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {

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
Page({

  /**
   * 页面的初始数据
   */
  data: {
    curCity: '不限',
    cs: '',
    type: '',
    parms: {
      city_id: '',
      keyword: '',
      pass_status: 1,
      exam_status: 3,
      page: 1,
      limit: 10
    },
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    showEdit: false,
    showDel: false,
    showBack: false,
    noResult: false,
    noInternet: false,
    showModal: false,
    ifModel: false,
    showModal: false,
    chooseReportList: [],
    chooseReportIdList: [],
    obj: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this
    if (!options) {
      that.setData({
        cs: options.cityid,
        curCity: options.cname,
      })
    }
  },
  onShow: function () {
    let that = this;
    that.setData({
      ['parms.city_id']: that.data.cs,
      ['parms.city_name']: that.data.curCity
    });
    if (!that.data.parms.city_id) {
      that.setData({
        ['parms.city_id']: '',
        ['parms.city_name']: ''
      })
    }
    let isData = wx.getStorageSync('smallReportList')
    if (!isData) {
      that.getList(that.data.parms);
    } else {
      that.setSmallReport()
    }
  },
  setSmallReport() {
    let that = this
    wx.getStorage({
      key: 'smallReportList',
      success: function (res) {
        that.setData({
          chooseReportList: res.data.list,
          chooseReportIdList: res.data.idList
        })
        that.getList(that.data.parms);
      }
    })
  },
  /**
   * 搜索条件
   */
  // 2. 搜索
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.keyword']: value,
      ['parms.page']: 1
    })
    that.getList(that.data.parms);
  },
  // 3. 分页处理
  prevBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  nextBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      return;
    } else {
      p = p + 1;
    }
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p,
      daid: '',
      ifModel: false
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
   * 请求数据
   */
  getList: function (parms) {
    let that = this;
    let idList = that.data.chooseReportIdList
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/payment/list', that.data.parms, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let list = res.data.list;
            let pageArr = [];
            let ifModel = that.data.ifModel
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            let num = 0
            list.map(item => {
              if (idList.indexOf(item.id) > -1) {
                item.checked = true
                num++
              } else {
                item.checked = false
              }
              return item
            })
            if (num > 0) {
              ifModel = true
            } else {
              ifModel = false
            }
            that.setData({
              list: list,
              noResult: false,
              page: true,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              ifModel
            })
            if (list.length == 0) {
              that.setData({
                noResult: true,
                page: false
              })
            }
          } else {
            console.log(res.error_msg)
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            page: false,
            noInternet: true
          })
        })
      }
    })
  },
  /**
   * 跳转操作
   */
  toCity: function () { //城市选择
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  toMemberReport: function () { // 重载
    wx.navigateTo({
      url: '../glSmallReport/glSmallReport'
    })
  },
  /**
   * 关联操作
   */
  checkboxChange(e) {
    const list = this.data.list
    const values = e.detail.value
    for (let i = 0, lenI = list.length; i < lenI; ++i) {
      list[i].checked = false
      for (let j = 0, lenJ = values.length; j < lenJ; ++j) {
        if (list[i].id == values[j]) {
          list[i].checked = true
          break
        }
      }
    }
    this.setData({
      ifModel: true,
      list
    })
    this.setActiveData()
  },
  cancelModel() {
    let that = this
    const list = this.data.list
    for (let i = 0, lenI = list.length; i < lenI; ++i) {
      list[i].checked = false
    }
    that.setData({
      list,
      ifModel: false
    })
    this.setActiveData()
  },
  setActiveData() {
    let that = this
    let list = that.data.list
    let array = that.data.chooseReportList
    let idArray = that.data.chooseReportIdList
    for (let i = 0, lenI = list.length; i < lenI; ++i) {
      if (list[i].checked === true) {
        if (idArray.indexOf(list[i].id) === -1) {
          array.push(list[i])
          idArray.push(list[i].id)
        }
      } else {
        let index = idArray.indexOf(list[i].id)
        if (index > -1) {
          array.splice(index, 1)
          idArray.splice(index, 1)
        }
      }
    }
    let obj = {
      list: array,
      idList: idArray
    }
    that.setData({
      obj
    })
  },
  related() {
    let that = this
    console.log(that.data.obj)
    wx.setStorage({
      key: 'smallReportList',
      data: that.data.obj,
      success: function (res) {
        wx.navigateBack({
          delta: 1
        })
      }
    })
  }
})