import {
  baobeiList,
  smalladd,
  editinfo,
  rollbackDrawReport
} from "../../utils/api.js"

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function(res) {
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
    success: function(res) {
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
    status: [{
      id: '0',
      name: '状态'
    }, {
      id: '2',
      name: '待审核'
    }, {
      id: '3',
      name: '审核通过'
    }, {
      id: '4',
      name: '审核不通过'
    }, {
      id: '5',
      name: '审核通过/待客服上传'
    }, {
      id: '6',
      name: '客服审核不通过'
    }, {
      id: '30',
      name: '审核通过/客服完成上传'
    }],
    status_index: 0,
    parms: {
      cooperation_type: '',
      exam_status: '',
      city_id: '',
      keyword: '',
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
    showModal: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.editinfo()
  },
  onReady: function() {
    let that = this;
    // that.getList()
  },
  onShow: function() {
    let that = this;
    that.setData({
      ['parms.city_id']: that.data.cs
    });
    if (that.data.parms.city_id == '') {
      that.setData({
        ['parms.city_id']: '',
      })
    }
    that.getList();
    if (that.data.list != '') {
      that.setData({
        noResult: false
      })
    }
  },
  editinfo(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        editinfo('/v1/sale_report/payment/editinfo', {
          actfrom: 'applet'
        }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              let typeList = [{
                id: '',
                name: '请选择'
              }, ...res.data.cooperation_type_list]
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
  /**
   * 搜索条件
   */
  // 1. 类型勾选
  bindPickerChange_style: function(e) {
    let id = this.data.style[e.detail.value].id;
    this.setData({
      style_index: e.detail.value
    })
    let that = this;
    that.setData({
      ['parms.cooperation_type']: id,
      ['parms.page']: 1
    })
    that.getList();
  },
  bindPickerChange_status: function(e) {
    let id = this.data.status[e.detail.value].id;
    this.setData({
      status_index: e.detail.value
    })
    let that = this;
    that.setData({
      ['parms.exam_status']: id,
      ['parms.page']: 1
    })
    that.getList();
  },
  // 2. 搜索
  searchWord: function(e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.keyword']: value,
      ['parms.page']: 1
    })
    that.getList();
  },
  // 3. 分页处理
  prevBtn: function(e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.page']: p
    })
    that.getList();
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  nextBtn: function(e) {
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
      ['parms.page']: p
    })
    that.getList();
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  toPage: function(e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getList();
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
   * 请求数据
   */
  getList: function() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        baobeiList('/v1/sale_report/payment/examlist', that.data.parms, {
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
            list.forEach((item, index) => {
              item.cut_company = item.company_name.substring(0, 12)
              if(item.exam_status === 2){
                item.className = 'wait-sub'
              }else if(item.exam_status === 3){
                item.className = 'pass'
              }else if(item.exam_status === 4){
                item.className = 'no-pass'
              }else if(item.exam_status === 5){
                item.className = 'wait-examine'
              }else if(item.exam_status === 6){
                item.className = 'no-pass'
              }
            })
            that.setData({
              list: list,
              noResult: false,
              page: true,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
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
        }).catch(function(error) {
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
   *列表操作(编辑、删除、撤回、查看、补充)
   */
  // 详情
  toDetail: function(e) {
    let id = e.currentTarget.dataset.id;
    let status = e.currentTarget.dataset.status;
    wx.navigateTo({
      url: '../sCheckDetail/sCheckDetail?id=' + id + '&noExit=true' + '&status=' + status,
    })
  },
  // 选择关联
  toSelect: function(e) {
    let that = this
    let id = e.currentTarget.dataset.id;
    that.setData({
      showModal: true,
      checkId: id
    })
  },
  // 驳回
  toReject(e){
    let that = this
    let id = e.currentTarget.dataset.id;
    wx.showModal({
      title: '提示',
      content: '确定要驳回该条数据吗？此操作不可挽回！',
      success: function (result) {
        if (result.confirm) {
          wx.getStorage({
            key: 'info',
            success: function(res) {
              let token = res.data.token;
              rollbackDrawReport('/v1/sale_report/payment/rollback', { id: id }, {
                token: token,
                'content-type': 'application/x-www-form-urlencoded',
              }).then(res => {
                if (res.error_code == 0) {
                  wx.showToast({
                    title: '成功',
                    icon: 'success',
                    duration: 2000
                  })
                  that.getList();
                } else {
                  wx.showToast({
                    title: res.error_msg,
                    duration: 2000
                  })
                }
              }).catch(function(error) {
                console.log('catch', error)
              })
            }
          })
        }
      }
    });
  },
  preventTouchMove: function() { // 关闭弹窗
    let that = this
    that.setData({
      showModal: false,
      infoStatus: '',
      reason: ''
    })
  },
  // 单选框
  signStatus(e) {
    let status = e.detail.value
    this.setData({
      infoStatus: status
    })
  },
  bindTextAreaBlur: function(e) {
    this.setData({
      reason: e.detail.value
    })
  },
  exampass(e) {
    let that = this
    let id = that.data.checkId
    let reason = that.data.reason
    let rea = (reason == undefined) ? '' : reason
    let infoStatus = e.currentTarget.dataset.info;
    if (infoStatus == '1') {
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          smalladd('/v1/sale_report/payment/exampass', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              that.getList();
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                showModal: false,
                infoStatus: '',
                reason: ''
              })
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function() {});
              return;
            }
          })
        }
      })
    } else if (infoStatus == '2') {
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          smalladd('/v1/sale_report/payment/examfail', {
            id: id,
            reason: rea
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              that.getList();
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                showModal: false,
                infoStatus: '',
                reason: ''
              })
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function() {});
              return;
            }
          })
        }
      })
    }

  },
  /**
   * 跳转操作
   */
  toCity: function() { //城市选择
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  toAddReport: function() { //添加新报备
    wx.navigateTo({
      url: '../addsReport/addsReport'
    })
  },
  toMemberReport: function() { // 重载
    wx.navigateTo({
      url: '../sCheck/sCheck'
    })
  },
})