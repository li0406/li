// pages/invoiceReporting/add.js

import {
  getInvoice,
  postInvoice,
  editinfo,
  smallinfo,
  qdDetail
} from "../../utils/api.js"
const app = getApp()

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

function formateTime() {
  let date = new Date()
  let year = date.getFullYear()
  let month = date.getMonth() > 8 ? (date.getMonth() + 1) : '0' + (date.getMonth() + 1)
  let day = date.getDate() > 9 ? date.getDate() : '0' + date.getDate()
  let currentDate = year + '-' + month + '-' + day
  return currentDate
}

Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: '',
    topIsOpen: false,
    arrayType: ['增值税专用发票', '增值税普通发票', '增值税电子普通发票'],
    arrayCompany: ['苏州云网通信息科技有限公司', '苏州齐装网络技术有限公司'],
    arrayIsDz: ['是', '否'],
    indexType: 0,
    indexCompany: 0,
    indexIsDz: 0,
    pics: [], // 合同
    picSm: [], // 说明
    chooseReportList: [],
    chooseReportIdList: [],
    parms: {
      id: '',
      type: '',
      billing_company: '',
      is_in_account: '',
      invoice_price: '',
      invoice_title: '',
      taxpayer_identification_number: '',
      company_address: '',
      company_phone: '',
      company_bank: '', //开户行
      company_bank_account: '',  //银行账号
      invoice_price: '',
      apply_email: '',
      receiver_name: '',
      receiver_phone: '',
      receiver_address: '',
      license_imgs: [],
      other_imgs: [],
      other_remarks: '',
      report_payment_ids: ''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let id = options.id ? options.id : ''
    let smallId = options.smallId ? options.smallId : ''
    let invoice = options.invoice ? options.invoice : ''
    if (id) {
      wx.setNavigationBarTitle({
        title: '编辑发票报备'
      })
      this.setData({
        id: id,
        'parms.id': id
      })
    }
    if (smallId) {
      this.getSmallInfo(smallId, invoice)
    }
    wx.removeStorage({
      key: 'smallReportList',
      success: function (res) {
        // console.log(res)
      }
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    this.getOption()
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    let that = this
    that.setSmallReport()
  },
  // 获取筛选项
  getOption() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/invoice/options', {
          actfrom: 'applet'
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let arrayType = [{
              id: '',
              name: '请选择'
            }, ...res.data.type]
            let arrayCompany = [{
              id: '',
              name: '请选择'
            }, ...res.data.billing_company]
            let arrayIsDz = [{
              id: '',
              name: '请选择'
            }, ...res.data.is_in_account]
            that.setData({
              arrayType,
              arrayCompany,
              arrayIsDz
            })
            if (that.data.parms.id) {
              that.getEditInfo()
            }
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function () {});
            return;
          }
        }).catch(function (error) {})
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
  // 缓存中小报备数据
  setSmallReport() {
    let that = this
    wx.getStorage({
      key: 'smallReportList',
      success: function (res) {
        that.setData({
          chooseReportList: res.data.list,
          chooseReportIdList: res.data.idList
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },
  // 删除
  delete: function (e) {
    let that = this
    alertViewWithCancel("删除确认", "确认要删除该报备信息吗？（该操作不可挽回！）", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          postInvoice('/v1/invoice/delete', {
            id: that.data.id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '删除成功',
                icon: 'success',
                duration: 200
              })
              setTimeout(function () {
                wx.navigateBack({
                  delta: 1,
                })
              }, 300)
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function () {});
              return;
            }
          })
        }
      })
    })
  },
  save(e) {
    let val = e.target.dataset.num
    let that = this
    let ids = that.data.chooseReportIdList.join(',')
    that.setData({
      'parms.is_submit': val,
      'parms.report_payment_ids': ids,
    })
    console.log(that.data.parms)
    if (that.validationData()) {
      that.saveData()
    }
  },
  // 数据验证 
  validationData() {
    let that = this
    let data = that.data.parms
    let type = data.type
    let numReg = /^[1-9]\d*$/
    let telReg = /^1[3456789]\d{9}$/
    let emailReg = /^\w+((.\w+)|(-\w+))@[A-Za-z0-9]+((.|-)[A-Za-z0-9]+).[A-Za-z0-9]+$/
    // let fpReg = /^[0-9a-zA-Z\(\)\（\）\u4e00-\u9fa5]{0,50}$/
    let nsrReg = /^[A-Z0-9]{15}$|^[A-Z0-9]{17}$|^[A-Z0-9]{18}$|^[A-Z0-9]{20}$/
    if (!data.type) {
      alertViewNoCancel("保存失败", "请选择开票类型", function () {});
      return false;
    } else if (!data.billing_company) {
      alertViewNoCancel("保存失败", "请选择开票公司", function () {});
      return false;
    } else if (!data.is_in_account) {
      alertViewNoCancel("保存失败", "请选择是否到账", function () {});
      return false;
    }
    if (!data.invoice_price) {
      alertViewNoCancel("保存失败", "请输入开票金额", function () {});
      return false;
    } else if (!numReg.test(data.invoice_price)) {
      alertViewNoCancel("保存失败", "请输入正确开票金额", function () {});
      return false;
    }
    if (type === 3 && !data.apply_email) {
      alertViewNoCancel("保存失败", "请输入申请人邮箱", function () {});
      return false;
    }
    if (data.apply_email && !emailReg.test(data.apply_email)) {
      alertViewNoCancel("保存失败", "请输入正确申请人邮箱", function () {});
      return false;
    }

    if (type !== 3 && !data.receiver_name) {
      alertViewNoCancel("保存失败", "请输入收件人姓名", function () {});
      return false;
    }

    if (type !== 3 && !data.receiver_phone) {
      alertViewNoCancel("保存失败", "请输入收件人电话", function () {});
      return false;
    }
    if (data.receiver_phone && !telReg.test(data.receiver_phone)) {
      alertViewNoCancel("保存失败", "请输入正确收件人电话", function () {});
      return false;
    }

    if (type !== 3 && !data.receiver_address) {
      alertViewNoCancel("保存失败", "请输入收件人地址", function () {});
      return false;
    }

    if (!data.invoice_title) {
      alertViewNoCancel("保存失败", "请输入发票抬头", function () {});
      return false;
    }
    /*  else if (!fpReg.test(data.invoice_title)) {
      alertViewNoCancel("保存失败", "请输入正确发票抬头", function () {});
      return false;
    } */
    if (!data.taxpayer_identification_number) {
      alertViewNoCancel("保存失败", "请输入纳税人识别号", function () {});
      return false;
    } else if (!nsrReg.test(data.taxpayer_identification_number)) {
      alertViewNoCancel("保存失败", "请输入正确纳税人识别号", function () {});
      return false;
    }

    if (type === 1 && !data.company_address) {
      alertViewNoCancel("保存失败", "请输入地址", function () {});
      return false;
    }
    if (type === 1 && !data.company_phone) {
      alertViewNoCancel("保存失败", "请输入电话", function () {});
      return false;
    }
    if (type === 1 && !data.company_bank) {
      alertViewNoCancel("保存失败", "请输入开户行", function () {});
      return false;
    }
    if (type === 1 && !data.company_bank_account) {
      alertViewNoCancel("保存失败", "请输入银行账号", function () {});
      return false;
    }
    if (data.license_imgs.length === 0) {
      alertViewNoCancel("保存失败", "请上传回传合同和营业执照", function () {});
      return false;
    }
    return true
  },
  saveData() {
    let that = this
    console.log(that.data.parms)
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        postInvoice('/v1/invoice/save', that.data.parms, {
          token: token,
          'content-type': 'application/json',
        }).then(res => {
          if (res.error_code == 0) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 200
            })
            setTimeout(function () {
              wx.navigateBack({
                delta: 1,
              })
            }, 300)
          } else {
            alertViewNoCancel("提交失败", res.error_msg, function () {});
            return;
          }
        })
      }
    })
  },
  getEditInfo() {
    let that = this;
    let id = that.data.parms.id
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/invoice/view', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            console.log(res)
            let xbbPics = []
            res.data.paymentPics.forEach(e => {
              if (e.img_src) {
                xbbPics.push('https://staticqn.qizuang.com/' + e.img_src)
              }
            });
            let data = res.data.invoiceDetails,
              paymentDetails = res.data.paymentDetails,
              invoicePics = res.data.invoicePics,
              invoiceOtherPics = res.data.invoiceOtherPics,
              pics = [],
              license_imgs = [],
              picSm = [],
              other_imgs = [],
              chooseReportIdList = [],
              indexType = that.data.arrayType.findIndex(item => item.id == data.type),
              indexCompany = that.data.arrayCompany.findIndex(item => item.id == data.billing_company),
              indexIsDz = that.data.arrayIsDz.findIndex(item => item.id == data.is_in_account)

            invoicePics.forEach((item) => {
              pics.push(item.img_full)
              license_imgs.push(item.img_src)
            })
            invoiceOtherPics.forEach((item) => {
              picSm.push(item.img_full)
              other_imgs.push(item.img_src)
            })
            if (paymentDetails.length > 0) {
              paymentDetails.forEach(item => {
                chooseReportIdList.push(item.id)
              })
              wx.setStorage({
                key: 'smallReportList',
                data: {
                  list: paymentDetails,
                  idList: chooseReportIdList
                },
                success: function (res) {

                }
              })
            }
            data.id = that.data.id
            that.setData({
              chooseReportList: paymentDetails,
              chooseReportIdList,
              parms: data,
              'parms.license_imgs': license_imgs,
              'parms.other_imgs': other_imgs,
              pics,
              picSm,
              indexType,
              indexCompany,
              indexIsDz,
            })
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function () {});
            return;
          }
        }).catch(function (error) {})
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
  chooseReportBtn() {
    wx.navigateTo({
      url: '../glSmallReport/glSmallReport',
    })
  },
  getSmallInfo(id, invoice) {
    let that = this
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/sale_report/payment/detail', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let list = res.data.detail
            list.has_invoice = invoice
            let obj = {
              list: [list],
              idList: [Number(id)]
            }
            wx.setStorage({
              key: 'smallReportList',
              data: obj,
              success: function (res) {

              }
            })
            that.setData({
              chooseReportList: obj.list,
              chooseReportIdList: obj.idList
            })
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function () {});
            return;
          }
        }).catch(function (error) {})
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
  // 删除选择小报备
  delReport(e) {
    let that = this
    alertViewWithCancel('提示', '确认要移除小报备吗？', function () {
      let index = e.target.dataset.index
      let obj = {
        list: that.data.chooseReportList,
        idList: that.data.chooseReportIdList
      }
      let idIndex = obj.idList.indexOf(obj.list[index].id)
      obj.list.splice(index, 1)
      obj.idList.splice(idIndex, 1)
      that.setData({
        chooseReportList: obj.list,
        chooseReportIdList: obj.idList
      })
      wx.setStorage({
        key: 'smallReportList',
        data: obj,
        success: function (res) {

        }
      })
    })
  },
  // 规则展开
  topOpen() {
    this.setData({
      topIsOpen: true
    })
  },
  // 规则收起
  topClose() {
    this.setData({
      topIsOpen: false
    })
  },
  // 预警信息 
  tipReport(e) {
    let id = e.currentTarget.dataset.id
    let info = e.currentTarget.dataset.info || ''
    let status = e.currentTarget.dataset.status
    let has = e.currentTarget.dataset.has || ''
    if(has === 1 || has === '1'){
      info = '该小报备已有发票记录！'
    }
    if(status > 3){
      wx.showModal({
        title: '预警信息',
        content: info,
        showCancel: false,
        confirmText: '知道了',
        success(res) {
          if (res.confirm) {}
        }
      })
    }else{
      wx.showModal({
        title: '预警信息',
        content: info,
        cancelText: '取消',
        confirmText: '查看',
        confirmColor: '#1890FF',
        showCancel: true,
        success: function (res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../invoiceReporting/list?id=' + id
            })
          }
        }
      });
    }
  },
  // 发票类型选择
  bindPickerChange1(e) {
    let that = this
    let val = Number(e.detail.value)
    let id = that.data.arrayType[val].id
    that.setData({
      indexType: val,
      'parms.type': id
    })
  },
  // 开票公司选择
  bindPickerChange2(e) {
    let that = this
    let val = Number(e.detail.value)
    let id = that.data.arrayType[val].id
    this.setData({
      indexCompany: val,
      'parms.billing_company': id
    })
  },
  // 是否到账选择
  bindPickerChange3(e) {
    let that = this
    let val = Number(e.detail.value)
    let id = that.data.arrayType[val].id
    this.setData({
      indexIsDz: val,
      'parms.is_in_account': id
    })
  },
  // 开票金额
  bindInvoicePrice(e) {
    let val = e.detail.value
    this.setData({
      'parms.invoice_price': val
    })
  },
  // 申请人邮箱
  bindApplyEmail(e) {
    let val = e.detail.value
    this.setData({
      'parms.apply_email': val
    })
  },
  // 收件人姓名
  bindReceiverName(e) {
    let val = e.detail.value
    this.setData({
      'parms.receiver_name': val
    })
  },
  // 收件人电话
  bindReceiverPhone(e) {
    let val = e.detail.value
    this.setData({
      'parms.receiver_phone': val
    })
  },
  // 收件人地址
  bindReceiverAddress(e) {
    let val = e.detail.value
    this.setData({
      'parms.receiver_address': val
    })
  },
  // 发票抬头
  bindInvoiceTitle(e) {
    let val = e.detail.value
    this.setData({
      'parms.invoice_title': val
    })
  },
  // 纳税人识别号
  bindTaxpayerIdentificationNumber(e) {
    let val = e.detail.value
    this.setData({
      'parms.taxpayer_identification_number': val
    })
  },
  // 地址
  bindCompanyAddress(e) {
    let val = e.detail.value
    this.setData({
      'parms.company_address': val
    })
  },
  // 电话
  bindCompanyPhone(e) {
    let val = e.detail.value
    this.setData({
      'parms.company_phone': val
    })
  },
  // 开户行
  bindCompanyBank(e) {
    let val = e.detail.value
    this.setData({
      'parms.company_bank': val
    })
  },
  // 银行账号
  bindCompanyPeop(e) {
    let val = e.detail.value
    this.setData({
      'parms.company_bank_account': val
    })
  },
  // 其他说明
  bindOtherRemarks(e) {
    let val = e.detail.value
    this.setData({
      'parms.other_remarks': val
    })
  },
  // 图片上传
  chooseImg: function (e) {
    var that = this,
      pics = that.data.pics,
      authImg = that.data.parms.license_imgs;
    if (pics.length < 20) {
      wx.chooseImage({
        count: 9, // 最多可以选择的图片张数，默认9
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: function (res) {
          var tempFilePaths = res.tempFilePaths;
          let userInfo = wx.getStorageSync('info')
          let token = userInfo.token;
          for (var i = 0; i < tempFilePaths.length; i++) {
            wx.uploadFile({
              url: app.globalData.baseUrl + '/uploads/upimg',
              header: {
                token: token,
                "Content-Type": "multipart/form-data"
              },
              filePath: tempFilePaths[i],
              name: 'file',
              formData: {
                'prefix': 'sale_report',
              },
              success: function (res) {
                var data = JSON.parse(res.data)
                if (data.error_code == 0) {
                  authImg.push(data.data.img_path)
                  pics.push(data.data.img_full)
                  that.setData({
                    'parms.license_imgs': authImg,
                    pics: pics
                  })
                }
              },
              fail: function (res) {
                wx.showToast({
                  title: '上传失败，请重新上传',
                  icon: 'none',
                  duration: 3000
                });
              }
            })
          }
        },
      });
    } else {
      wx.showToast({
        title: '最多上传20张图片',
        icon: 'none',
        duration: 3000
      });
    }
  },
  // 删除图片
  deleteImg: function (e) {
    var that = this;
    var pics = that.data.pics;
    var imgs = that.data.parms.license_imgs;
    var index = e.currentTarget.dataset.index;
    pics.splice(index, 1);
    imgs.splice(index, 1);
    that.setData({
      pics: pics,
      ['parms.license_imgs']: imgs
    })
  },
  // 预览图片
  previewImg1: function (e) {
    var index = e.currentTarget.dataset.index;
    var pics = this.data.pics;
    wx.previewImage({
      current: pics[index],
      urls: pics
    })
  },
  // 图片上传(其他说明)
  chooseImgSm: function (e) {
    var that = this,
      pics = that.data.picSm,
      authImgSm = that.data.parms.other_imgs;
    if (pics.length < 20) {
      wx.chooseImage({
        count: 9, // 最多可以选择的图片张数，默认9
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: function (res) {
          var tempFilePaths = res.tempFilePaths;
          let userInfo = wx.getStorageSync('info')
          let token = userInfo.token;
          for (var i = 0; i < tempFilePaths.length; i++) {
            wx.uploadFile({
              url: app.globalData.baseUrl + '/uploads/upimg',
              header: {
                token: token,
                "Content-Type": "multipart/form-data"
              },
              filePath: tempFilePaths[i],
              name: 'file',
              formData: {
                'prefix': 'sale_report',
              },
              success: function (res) {
                console.log(res)
                var data = JSON.parse(res.data)
                if (data.error_code == 0) {
                  authImgSm.push(data.data.img_path)
                  pics.push(data.data.img_full)
                  that.setData({
                    'parms.other_imgs': authImgSm,
                    picSm: pics,
                  })
                }
              },
              fail: function (res) {
                wx.showToast({
                  title: '上传失败，请重新上传',
                  icon: 'none',
                  duration: 3000
                });
              }
            })
          }
        },
      });
    } else {
      wx.showToast({
        title: '最多上传20张图片',
        icon: 'none',
        duration: 3000
      });
    }
  },
  // 删除图片(其他说明)
  deleteImgSm: function (e) {
    var that = this;
    var pics = that.data.picSm;
    console.log(pics)
    var imgs = that.data.parms.other_imgs;
    var index = e.currentTarget.dataset.index;
    pics.splice(index, 1);
    imgs.splice(index, 1);
    that.setData({
      picSm: pics,
      ['parms.other_imgs']: imgs
    })
  },
  // 预览图片(其他说明)
  previewImgSm: function (e) {
    var index = e.currentTarget.dataset.index;
    var pics = this.data.picSm;
    wx.previewImage({
      current: pics[index],
      urls: pics
    })
  },
})