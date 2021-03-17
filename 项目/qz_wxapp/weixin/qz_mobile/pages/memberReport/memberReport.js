// pages/memberReport/memberReport.js
import { baobeiList, delReport, withDrawReport, supReport, seleOptions } from "../../utils/api.js"
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
    style: [],
    style_index: 0,
    isNew: [{ id: '0', name: '新/老会员' },{ id: '1', name: '新会员' }, { id: '2', name:'老会员'}],
    isNew_index: 0,
    status: [{ id: '0', name: '状态' }, { id: '1', name: '待提交' }, { id: '2', name: '待审核' }, { id: '3', name: '审核通过' }, { id: '4', name: '未通过' }, { id: '5', name: '客服审核通过' }, { id: '6', name: '客服审核未通过，普通信息更改' }, { id: '7', name: '客服审核未通过，需要重新审核' }, { id: '8', name: '客服完成上传' }, { id: '9', name: '客服暂停' }, { id: '10', name: '待客服补充' }, { id: '11', name: '客服补充完成' }],
    status_index: 0,
    parms: {
      company: '',
      cooperation_type: '',
      cname: '',
      is_new: '',
      saler: '',
      start: '',
      end: '',
      p: 1,
      psize: 10
    },
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    showEdit: false,
    showDel: false,
    showBack:false,
    noResult: false,
    noInternet: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this
    // that.saleOptions()
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    let that = this;
    that.saleOptions()
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this;
    that.setData({
      ['parms.cname']: that.data.curCity,
    });
    if (that.data.parms.cname == '不限') {
      that.setData({
        ['parms.cname']: '',
      })
    }
    that.getList(that.data.parms);
    if (that.data.list != '') {
      that.setData({
        noResult: false
      })
    }
  },
  // 获取返点比例
  saleOptions() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        seleOptions('/v1/sale_report/options', {}, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let typeList = [{ id: 0, name: '合作类型' }, ...res.data.cooperation_type_list]
            that.setData({
              style: typeList
            })
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function () { });
            return;
          }
        }).catch(function (error) { })
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
  bindPickerChange_style: function (e) {
    let id = this.data.style[e.detail.value].id;
    this.setData({
      style_index: e.detail.value
    })
    let that = this;
    that.setData({
      ['parms.cooperation_type']: id,
      ['parms.p']: 1
    })
    that.getList(that.data.parms);
  },
  bindPickerChange_isNew: function (e) {
    let id = this.data.isNew[e.detail.value].id;
    this.setData({
      isNew_index: e.detail.value
    })
    let that = this;
    that.setData({
      ['parms.is_new']: id,
      ['parms.p']: 1
    })
    that.getList(that.data.parms);
  },
  bindPickerChange_status: function (e) {
    let id = this.data.status[e.detail.value].id;
    this.setData({
      status_index: e.detail.value
    })
    let that = this;
    that.setData({
      ['parms.status']: id,
      ['parms.p']: 1
    })
    that.getList(that.data.parms);
  },
  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  toAddReport: function () {
    wx.navigateTo({
      url: '../addReport/addReport'
    })
  },
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.condition']: value,
      ['parms.p']: 1
    })
    that.getList(that.data.parms);
  },
  getList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/baobei', that.data.parms, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              let totalNumber = res.data.page.page_total_number;
              let list = res.data.list;
              let pageArr = [];
              for (let i = 0; i < totalNumber; i++) {
                pageArr.push(i + 1)
              }
              that.setData({
                list: list,
                noResult: false,
                page: true,
                pageCurrent: res.data.page.page_current,
                pageTotalNumber: res.data.page.page_total_number,
                pageNumber: pageArr,
              })
              if( list.length == 0) {
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
      ['parms.p']: p
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
      ['parms.p']: p
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
      ['parms.p']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  // 编辑、删除、撤回、查看
  toEdit: function (e) {
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    wx.navigateTo({
      url: '../editMember/editMember?id=' + id + '&cooperation_type=' + cooperation_type,
    })
  },
  toDel: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    alertViewWithCancel("删除", "确认删除该公司信息", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          delReport('/v1/sale_report/del', {
            cooperation_type: cooperation_type,
            id: id
          }, {
              token: token,
              'content-type': 'application/x-www-form-urlencoded',
            }).then(res => {
              if (res.error_code == 0) {
                that.getList(that.data.parms);
              } else {
                alertViewNoCancel("删除失败", res.error_msg, function () {
                });
                return;
              }
            })
        }
      })
    });
  },
  toback: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    alertViewWithCancel("删除", "确认撤回该公司信息", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          withDrawReport('/v1/sale_report/set_status', {
            cooperation_type: cooperation_type,
            status: 1,
            id: id
          }, {
              token: token,
              'content-type': 'application/x-www-form-urlencoded',
            }).then(res => {
              if (res.error_code == 0) {
                that.getList(that.data.parms);
              } else {
                alertViewNoCancel("撤回失败", res.error_msg, function () {

                });
                return;
              }
            })
        }
      })
    });
  },
  toDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    wx.navigateTo({
      url: '../memberDetail/memberDetail?id=' + id + '&cooperation_type=' + cooperation_type,
    })
  },
  toSupplement: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;

    alertViewWithCancel("提交", "确认需要客服补充该公司信息？", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          supReport('/v1/sale_report/kf_voucher', {
            cooperation_type: cooperation_type,
            id: id
          }, {
              token: token,
              'content-type': 'application/x-www-form-urlencoded',
            }).then(res => {
              if (res.error_code == 0) {
                that.getList(that.data.parms);
              } else {
                alertViewNoCancel("提交失败", res.error_msg, function () {
                });
                return;
              }
            })
        }
      })
    });
  },
  toMemberReport: function () {
    wx.navigateTo({
      url: '../memberReport/memberReport'
    })
  },
})