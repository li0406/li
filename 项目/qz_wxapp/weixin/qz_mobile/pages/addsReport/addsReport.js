// pages/addsReport/addsReport.js
import {
  smalladd,
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
    success: function(res) {
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
    success: function(res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {

      }
    }
  });
}
function validatePositiveInt(value){
  const reg = /^[1-9]\d*$/
  if (!reg.test(value)) {
      return true
  } else {
      return false
  }
}
Page({
  data: {
    hzList: [],
    hzIndex: '',
    typeId:'',
    bsList: ['请选择', '0.5', '1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '5.5', '6.0', '6.5', '7.0', '7.5', '8.0', '8.5', '9.0', '9.5', '10.0'],
    bsIndex: 0,
    Ratios: [],
    RatioIndex: 0,
    lxList: [],
    lxTop: {id:0,name:'请选择'},
    lxAllList: {},
    lxIndex: 0, // 收款类型
    refundTypeList: [],
    typeIndex: '',
    curCity: '请选择',
    cs: '',
    qiandanJine:'', // 返点金额
    orderBackMoney:'', // 返点金额
    parms: {
      company_name: '',
      payment_uname: '',
      payment_date: '',
      payment_total_money: '',
      refund_money: '',
      company_id: '',
      auth_imgs: [],
      person_list: [],
      payee_list: [],
      remark: '',
      order_id: '',
      other_money: '',
      other_money_remark: '',
      round_order_money:'', // 轮单费
      deposit_money:'', // 保证金
      platform_money:'', // 平台使用费
      platform_money_start_date:'', // 平台使用费有效期开始时间
      platform_money_end_date:'' // 平台使用费有效期结束时间
    },
    personList: [],
    pics: [],
    plist: [],
    ctype: '',
    // 新增字段
    showDialog: false,
    datas: [],
    checkDatas: [],
    checkboxs: [],
    showChoose: true,
    member:'',
    isListTo: true,
    payeeNeq: false, // 合计不等于
    showPayeeDialog: false, // 显示收款明细弹窗
    payeeList: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this
    let id = options.id ? options.id : ''
    let orderId = options.orderId ? options.orderId : ''
    if (id != '') {
      wx.setNavigationBarTitle({
        title: '编辑报备'
      })
      that.setData({
        ['parms.id']: id,
        id: id,
        ctype: options.cooperation_type,
        isListTo: false
      })
    } else {
      that.formateTime()
    }
    this.editinfo(id)
    if(orderId){
      that.setData({
        ['parms.order_id']: orderId,
        showChoose: false,
        member:'会员返点',
        hzIndex: 8
      })
    }
  },
  onReady: function() {

  },
  onShow: function() {
    const that = this
    const orderId = that.data.parms.order_id
    const isListTo = that.data.isListTo
    if(orderId && isListTo){
      that.getOrderInfo(orderId)
    }
  },
  add0(m) {
    return m < 10 ? '0' + m : m
  },
  formateTime() {
    let date = new Date()
    let year = date.getFullYear()
    let month = date.getMonth() + 1
    let day = date.getDate()
    let currentDate = year + '-' + this.add0(month) + '-' + this.add0(day)
    this.setData({
      ['parms.payment_date']: currentDate
    })
  },

  // 弹窗
  toggleDialog() {
    this.setData({
      showDialog: !this.data.showDialog,
    });
  },
  freeBack: function() {
    let that = this
    let checkboxs = that.data.checkboxs
    let datas = that.data.datas
    let checkDatas = datas.filter((ele) =>
      checkboxs.filter((x) => x == ele.payee_type).length > 0
    );
    that.setData({
      showDialog: !this.data.showDialog,
      checkboxs: checkboxs,
      checkDatas: checkDatas
    })
  },
  freetoBack: function() {
    let that = this
    that.setData({
      showDialog: !this.data.showDialog,
    })
  },
  checkboxChange(e) {
    this.setData({
      checkboxs: e.detail.value
    })
  },

  editinfo(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        editinfo('/v1/sale_report/payment/editinfo', {
          id: id,
          actfrom: 'applet'
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let datas = res.data.payment_payee_list.map(item => {
              let obj = {
                'payee_type' : item.id,
                'payee_type_name' : item.name,
                'payee_money' : ''
              }
              return obj
            })
            // let typeList = res.data.cooperation_type_list
            let typeList = res.data.cooperation_type_list
            let lxList = [{id:0,name:'请选择'}, ...res.data.payment_type_list]
            let lxAllList = res.data.payment_type_groups
            let refundTypeList = res.data.refund_type_list
            let back_ratio_list = res.data.back_ratio_list
            back_ratio_list.unshift("请选择")
            that.setData({
              datas: datas,
              Ratios: back_ratio_list,
              hzList: typeList,
              lxList: lxList,
              lxAllList: lxAllList,
              refundTypeList
            })
            let xdetail = res.data.detail;
            if (xdetail.id) {
              let img_list = res.data.detail.auth_imgs
              let pics = img_list.map((item, index) => {
                return item.img_full
              })
              let auth_imgs = img_list.map((item, index) => {
                return item.img_src
              })
              if (that.data.ctype) {
                that.data.plist = that.data.plist.concat(xdetail.person_list)
              }

              typeList.forEach((item,index) => {
                if(item.id === xdetail.cooperation_type){
                  that.setData({
                    member:item.name
                  })
                }
              })

              // 收款方
              let checkboxs = xdetail.payee_list.map((item) => {  // 原id list
                return item.payee_type
              })
              let checkDatas = xdetail.payee_list.map((item) => {
                return {
                  payee_type: item.payee_type,
                  payee_type_name: item.payee_type_name,
                  payee_money: item.payee_money
                }
              })
              let payeeList = []
              datas.forEach((item) => {
                checkDatas.forEach((it) => {
                  if (item.payee_type == it.payee_type) {
                    item.checked = true
                    item.payee_money = it.payee_money
                  }
                })
              })
              
              // 收款类型
              if(Number(that.data.ctype) == 8){
                that.setData({
                  lxList: [that.data.lxTop, ...lxAllList[8]]
                })
              }else if(Number(that.data.ctype) == 9){
                that.setData({
                  lxList: [that.data.lxTop, ...lxAllList[9]]
                })
              }else if(Number(that.data.ctype) == 11){
                that.setData({
                  lxList: [that.data.lxTop, ...refundTypeList]
                })
              }else{
                that.setData({
                  lxList: [that.data.lxTop, ...lxAllList[0]]
                })
              }
              // 对应index
              let lxIndex = that.data.lxList.findIndex(v => v.id == xdetail.payment_type)
              that.setData({
                ['parms.company_name']: xdetail.company_name,
                ['parms.remark']: xdetail.remark,
                hzIndex: xdetail.cooperation_type,
                cs: xdetail.city_id,
                curCity: xdetail.city_name,
                bsIndex: that.data.bsList.indexOf(xdetail.viptype),
                RatioIndex: that.data.Ratios.indexOf(xdetail.back_ratio),
                ['parms.payment_uname']: xdetail.payment_uname,
                ['parms.payment_date']: xdetail.payment_date,
                ['parms.payment_total_money']: parseInt(xdetail.payment_total_money),
                ['parms.refund_money']: parseInt(xdetail.refund_money),
                lxIndex,
                typeIndex: xdetail.payment_account_type,
                ['parms.auth_imgs']: auth_imgs,
                pics: pics,
                creator_name: xdetail.creator_name,
                personList: that.data.plist,
                // 其他业绩人字段
                checkboxs: checkboxs,
                checkDatas: checkDatas,
                datas: datas, // 收款方列表
                payeeList: payeeList,
                ['parms.payee_list']: checkboxs,
                showChoose: false,
                ['parms.order_id']: xdetail.order_id,
                ['parms.other_money']: xdetail.other_money,
                ['parms.other_money_remark']: xdetail.other_money_remark,
                ['parms.round_order_money']: parseInt(xdetail.round_order_money),
                ['parms.platform_money']: xdetail.platform_money,
                ['parms.platform_money_start_date']: xdetail.platform_money_start_date,
                ['parms.platform_money_end_date']: xdetail.platform_money_end_date,
              })
              if (xdetail.cooperation_type == 6 || xdetail.cooperation_type == 15) {
                that.setData({
                  ['parms.deposit_money']: parseInt(xdetail.deposit_money)
                })
              }else if(xdetail.cooperation_type == 8){
                that.setData({
                  ['parms.deposit_money']: parseInt(xdetail.deposit_money),
                  qiandanJine: xdetail.order_sign_money,
                  orderBackMoney: xdetail.order_sign_money * parseInt(xdetail.back_ratio)/100
                })
              }else if(xdetail.cooperation_type == 10){
                that.setData({
                  ['parms.deposit_to_round_money']: parseInt(xdetail.deposit_to_round_money),
                  ['parms.deposit_money']: parseInt(xdetail.deposit_money),
                  ['parms.company_id']: parseInt(xdetail.company_id),
                  companyId: parseInt(xdetail.company_id)
                })
              }else if(xdetail.cooperation_type == 13){
                that.setData({
                  ['parms.deposit_to_platform_money']: parseInt(xdetail.deposit_to_platform_money),
                  ['parms.deposit_money']: parseInt(xdetail.deposit_money),
                  ['parms.company_id']: parseInt(xdetail.company_id),
                  companyId: parseInt(xdetail.company_id)
                })
                that.getCompanyInfo()
              }
            }
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function() {});
            return;
          }
        }).catch(function(error) {})
      },
      fail: function(res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function() {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)
      }
    })
  },

  getOrderInfo(id){
    let that = this
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        smallinfo('/v1/order/signinfo', {
          order_id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            const orderInfo = res.data.orderinfo
            that.setData({
              ['parms.company_name']: orderInfo.company_name,
              curCity: orderInfo.user_city_name,
              qiandanJine: orderInfo.order_sign_money,
              hzIndex: 8,
              lxIndex: 5
            })
            if(orderInfo.back_ratio_show){
              that.setData({
                orderBackMoney: orderInfo.order_sign_money * orderInfo.back_ratio/100,
                RatioIndex: that.data.Ratios.indexOf(orderInfo.back_ratio_show),
              })
            }else{
              that.setData({
                orderBackMoney: '',
                RatioIndex: 0
              })
            }
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function() {});
            return;
          }
        }).catch(function(error) {})
      },
      fail: function(res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function() {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)
      }
    })
  },
  //收款方明细弹窗确定
  payeeOk(){
    let that = this
    let checkDatas = that.data.checkDatas
    let paymentMoney = Number(that.data.parms.payment_total_money)
    let totalMoney = 0
    let reg = /^[+]{0,1}(\d+)$/
    for(var i in checkDatas){
      let money = Number(checkDatas[i].payee_money)
      let name = checkDatas[i].payee_type_name
      if(!money){
        alertViewNoCancel("保存失败", `请输入${name}收款金额`, function() {});
        return
      }else if(!reg.test(money)){
        alertViewNoCancel("保存失败", `${name}请输入正整数`, function() {});
        return
      }else{
        totalMoney += money
      }
    }
    if(totalMoney !=  paymentMoney){
      alertViewNoCancel("保存失败", '合计与收款金额不一致！', function() {});
      return
    } else {
      that.setData({
        ['parms.payee_list']: JSON.stringify(checkDatas),
        showPayeeDialog:false
      })
      that.submitData()
    }

  },
   //收款方明细弹窗取消
  payeeCancel(){
    this.setData({
      showPayeeDialog:false
    })
  },
  bindOrderId(){
    wx.navigateTo({
      url: '../sign/sign?type=8'
    })
  },
  /**
   * 图片上传
   * 
   */
  chooseImg: function(e) {
    var that = this,
    pics = this.data.pics;
    that.setData({
      isListTo: false
    })
    var FSM = wx.getFileSystemManager();
    if (pics.length < 20) {
      wx.chooseImage({
        count: 9, // 最多可以选择的图片张数，默认9
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: function(res) {
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
              success: function(res) {
                var data = JSON.parse(res.data)
                if (data.error_code == 0) {
                  that.data.parms.auth_imgs.push(data.data.img_path)
                  pics.push(data.data.img_full)

                  that.setData({
                    pics: pics
                  })
                }
              },
              fail: function(res) {
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
  deleteImg: function(e) {
    var that = this;
    var pics = this.data.pics;
    var imgs = this.data.parms.auth_imgs;
    var index = e.currentTarget.dataset.index;
    pics.splice(index, 1);
    imgs.splice(index, 1);
    this.setData({
      pics: pics,
      ['parms.auth_imgs']: imgs
    })
  },
  // 预览图片
  previewImg1: function(e) {
    var index = e.currentTarget.dataset.index;
    var pics = this.data.pics;
    wx.previewImage({
      current: pics[index],
      urls: pics
    })
  },
  // 头部合作类型选择
  showChoose: function () {
    this.setData({
        showChoose: true
    })
  },
  chooseMember: function (e) {
    let that = this;
    let type = e.currentTarget.dataset.type;
    let text = e.currentTarget.dataset.text;
    let index = e.currentTarget.dataset.index;
    let lxAllList = that.data.lxAllList;
    let refundTypeList = that.data.refundTypeList;
    let lxTop = that.data.lxTop;
    let lxList = [];
    let lxIndex = '';
    if(type){
      that.setData({
        hzIndex:type,
        member:text,
        showChoose: false
      })
    }
    if(type == 8){
      lxList =[lxTop, ...lxAllList[8]]
      lxIndex = lxList.findIndex(v => v.id === 5)
      that.setData({
        lxIndex:lxIndex
      })
    }else if(type == 9){
      lxList = [lxTop, ...lxAllList[9]]
      lxIndex = lxList.findIndex(v => v.id === 6)
      that.setData({
        lxIndex:lxIndex
      })
    }else if(type == 10){
      lxList = [lxTop, ...lxAllList[0]]
      lxIndex = lxList.findIndex(v => v.id === 2)
      that.setData({
        lxIndex:lxIndex
      })
    }else if(type == 13){
      lxList = [lxTop, ...lxAllList[0]]
      lxIndex = lxList.findIndex(v => v.id === 2)
      that.setData({
        lxIndex:lxIndex
      })
    }else{
      if(type == 11){
        lxList = [lxTop, ...refundTypeList]
      }else{
        lxList = [lxTop, ...lxAllList[0]]
      }
      if(that.data.lxIndex > 4){
        that.setData({
          lxIndex:0
        })
      }
    }
    that.setData({
      lxList: lxList
    })
  },
  // 网店代码
  company_id: function(e) {
    this.setData({
      ['parms.company_id']: e.detail.value
    })
  },
  // 公司名称
  company_name: function(e) {
    this.setData({
      ['parms.company_name']: e.detail.value
    })
  },
  // 备注
  remark: function (e) {
    this.setData({
      ['parms.remark']: e.detail.value
    })
  },
  payment_uname: function(e) {
    this.setData({
      ['parms.payment_uname']: e.detail.value
    })
  },
 // 收款金额
  payment_total_money: function(e) {
    this.setData({
      ['parms.payment_total_money']: e.detail.value
    })
  },
  // 退款金额
  refund_money: function(e) {
    this.setData({
      ['parms.refund_money']: e.detail.value
    })
  },
  // 轮单费
  round_order_money: function(e) {
    this.setData({
      ['parms.round_order_money']: e.detail.value
    })
  },
  // 平台使用费
  platform_money: function(e) {
    this.setData({
      ['parms.platform_money']: e.detail.value
    })
  },
  // 保证金
  deposit_money: function(e) {
    this.setData({
      ['parms.deposit_money']: e.detail.value
    })
  },
  // 其他金额
  other_money: function(e) {
    this.setData({
      ['parms.other_money']: e.detail.value
    })
  },
  // 其他金额类型
  other_money_remark: function(e) {
    this.setData({
      ['parms.other_money_remark']: e.detail.value
    })
  },
  // 方法
  // 选择城市
  toCity: function() {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../limitCity/limitCity?needArea=1&curCity=' + city
    })
  },
  // 合作类型
  selecthz: function(e) {
    let id = this.data.hzList[e.detail.value].id;
    this.setData({
      hzIndex: id
    })
    if (id == '6') {
      this.setData({
        RatioIndex: 0
      })
    } else {
      this.setData({
        bsIndex: 0
      })
    }
  },
  // 选择倍数
  selectbs: function(e) {
    this.setData({
      bsIndex: e.detail.value
    })
  },
  // 返点比例
  selectRatio: function(e) {
    const that = this
    const index = e.detail.value
    const backRatio = that.data.Ratios[index]
    const hzIndex = that.data.hzIndex
    if(hzIndex == 8 && backRatio === '0%') {
      wx.showToast({
        title: '返点比例不能为0%',
        icon: 'none',
        duration: 1000
      })
      that.setData({
        RatioIndex: 0,
        orderBackMoney: 0
      })
    }else{
      that.setData({
        RatioIndex: index
      })
    }
    if(index != 0){
      that.setData({
        orderBackMoney: that.data.qiandanJine * parseInt(that.data.Ratios[e.detail.value])/100
      })
    }else{
      that.setData({
        orderBackMoney: ''
      })
    }
  },
  // 收款类型
  selectlx: function(e) {
    this.setData({
      lxIndex: e.detail.value
    })
  },
  // 选择时间
  dateChange1: function(e) {
    this.setData({
      ['parms.payment_date']: e.detail.value
    });
    let timeValue = (new Date(this.data.parms.payment_date).getTime())
    let newtime = new Date().getTime()
    if ((timeValue - newtime) > 0) {
      alertViewNoCancel("错误提示", "不可超出当前时间", function() {});
      this.setData({
        ['parms.payment_date']: ''
      });
      return;
    }
  },
  // 平台有效期开始时间
  platform_money_start_date: function (e) {
    this.setData({
        ['parms.platform_money_start_date']: e.detail.value
    });
    if (this.data.parms.platform_money_end_date != '') {
      let startTime = this.data.parms.platform_money_start_date
      let nedTime = this.data.parms.platform_money_end_date
      let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
      if (timeValue < 0) {
        alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
        });
        this.setData({
          ['parms.platform_money_start_date']:''
        });
        return;
      }
    }
  },
  // 平台有效期结束时间
  platform_money_end_date: function (e) {
    this.setData({
      ['parms.platform_money_end_date']: e.detail.value
    });
    if (this.data.parms.platform_money_start_date != '') {
      let startTime = this.data.parms.platform_money_start_date
      let nedTime = this.data.parms.platform_money_end_date
      let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
      if (timeValue < 0) {
        alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
        });
        this.setData({
          ['parms.platform_money_end_date']:''
        });
        return;
      }
    }
  },
  // 保证金抵扣轮单费
  depositToRoundMoney(e) {
    this.setData({
      'parms.deposit_to_round_money': e.detail.value
    })
  },
  // 保证金抵扣金额
  depositToPlatformMoney(e) {
    this.setData({
      'parms.deposit_to_platform_money': e.detail.value
    })
  },

  //  提交
  submit: function(e) {
    let that = this;
    
    let hzIndex = that.data.hzIndex //合作类型
    let cs = that.data.cs //城市
    let bsIndex = that.data.bsIndex // 单倍
    let RatioIndex = that.data.RatioIndex // 比例
    let lxIndex = that.data.lxIndex
    let paymentType = that.data.lxList[lxIndex].id
    let percent = 0
    let paymentTotalMoney = Number(that.data.parms.payment_total_money?that.data.parms.payment_total_money:0) //收款金额
    let roundOrderMoney = Number(that.data.parms.round_order_money?that.data.parms.round_order_money:0) //轮单费
    let depositMoney = Number(that.data.parms.deposit_money?that.data.parms.deposit_money:0) //保证金
    let otherMoney = Number(that.data.parms.other_money?that.data.parms.other_money:0) //其他金额
    let depositToRoundMoney = Number(that.data.parms.deposit_to_round_money?that.data.parms.deposit_to_round_money:0) //保证金抵扣轮单费
    let depositToPlatformMoney = that.data.parms.deposit_to_platform_money //保证金抵扣平台使用费金额

    // 收款方名称
    let payee_list = that.data.checkDatas

    that.data.personList.forEach((index, item) => {
      percent += Number(index.share_ratio)
    });
    if (that.data.parms.order_id == '' && hzIndex == 8) {
      alertViewNoCancel("保存失败", "请输入订单编号", function() {});
      return;
    }
    if (that.data.parms.company_id == '' && hzIndex == 13) {
      alertViewNoCancel("保存失败", "请输入网店代码", function() {});
      return;
    }
    if (that.data.parms.company_name == '') {
      alertViewNoCancel("保存失败", "请输入公司名称", function() {});
      return;
    }
    if (hzIndex == 0) {
      alertViewNoCancel("保存失败", "请选择合作类型", function() {});
      return;
    }
    if (cs == '' && hzIndex != 8) {
      if(hzIndex == 11){
        alertViewNoCancel("保存失败", "请选择退款城市", function() {});
      }else{
        alertViewNoCancel("保存失败", "请选择收款城市", function() {});
      }
      return;
    }
    if (bsIndex == 0 && (hzIndex == 1 || hzIndex == 2 || hzIndex == 3 || hzIndex == 7)) {
      alertViewNoCancel("保存失败", "请选择单倍/几倍", function() {});
      return;
    }
    if (RatioIndex == 0 && (hzIndex == 6 || hzIndex == 8)) {
      alertViewNoCancel("保存失败", "请选择返点比例", function() {});
      return;
    }
    if (hzIndex == 12 && that.data.parms.payment_uname == '') {
      alertViewNoCancel("保存失败", "请输入汇款方名称", function() {});
      return;
    }
    if (that.data.parms.payment_date == '') {
      let name = ''
      if (hzIndex == 11) {
        name = '退款'
      } else {
        name = '汇款'
      }
      alertViewNoCancel("保存失败", `请选择${name}时间`, function() {});
      return;
    }
    if (that.data.parms.payment_total_money == '' && hzIndex != 8 && hzIndex != 10 && hzIndex != 11 && hzIndex != 13) {
      alertViewNoCancel("保存失败", "请输入收款金额", function() {});
      return;
    }else if (that.data.parms.payment_total_money == '' && hzIndex != 8 && hzIndex != 10 && hzIndex != 11 && hzIndex != 13){
      that.setData({
        ['parms.payment_total_money'] : 0
      })
    }
    if (that.data.parms.refund_money == '' && hzIndex == 11) {
      alertViewNoCancel("保存失败", "请输入退款金额", function() {});
      return;
    }

    if (!that.data.parms.round_order_money && (hzIndex == 1 || hzIndex == 2 || hzIndex == 3 || hzIndex == 7 || hzIndex == 14)) {
      alertViewNoCancel("保存失败", "请输入会员费", function() {});
      return;
    }
    if (!that.data.parms.round_order_money && (hzIndex == 6 || hzIndex == 15)) {
      alertViewNoCancel("保存失败", "请输入轮单费", function() {});
      return;
    }
    if (Number(that.data.parms.round_order_money) > Number(that.data.parms.payment_total_money) && (hzIndex == 1 || hzIndex == 2 || hzIndex == 3 || hzIndex == 7 || hzIndex == 14 || hzIndex == 6 || hzIndex == 15)) {
      let name = ''
      if (hzIndex == 6 || hzIndex == 15) {
        name = '轮单费'
      } else {
        name = '会员费'
      }
      alertViewNoCancel("保存失败", `${name}不可超出收款金额`, function() {});
      return;
    }
    if(hzIndex == 6 || hzIndex == 15){
      if (Number(that.data.parms.deposit_money) > Number(that.data.parms.payment_total_money)) {
        alertViewNoCancel("保存失败", "保证金不可超出收款金额", function() {});
        return;
      }
      /* if(roundOrderMoney+depositMoney+otherMoney!== paymentTotalMoney){
        alertViewNoCancel("保存失败", "合计金额不等于收款金额", function() {});
        return;
      } */
    }
    if (Number(that.data.parms.platform_money) > Number(that.data.parms.payment_total_money)) {
      alertViewNoCancel("保存失败", "平台使用费不可超出收款金额", function() {});
      return;
    }
    if(hzIndex == 13){
      if(!depositToPlatformMoney){
        alertViewNoCancel("保存失败", "请输入保证金抵扣金额", function() {});
        return;
      }else if(validatePositiveInt(depositToPlatformMoney)) {
        alertViewNoCancel("保存失败", "请输入正确的保证金抵扣金额", function() {});
        return;
      }else if(depositToPlatformMoney > depositMoney){
        alertViewNoCancel("保存失败", "抵扣金额不得超出保证金余额", function() {});
        return;
      }
    }
    if(hzIndex === 12 || hzIndex === 13 || that.data.parms.platform_money){
      if (!that.data.parms.platform_money_start_date && !that.data.parms.platform_money_end_date) {
        alertViewNoCancel("保存失败", "请选择平台使用费有效期", function() {});
        return;
      } else if (!that.data.parms.platform_money_start_date && that.data.parms.platform_money_end_date) {
        alertViewNoCancel("保存失败", "请选择平台使用费有效期开始时间", function() {});
        return;
      } else if (that.data.parms.platform_money_start_date && !that.data.parms.platform_money_end_date) {
        alertViewNoCancel("保存失败", "请选择平台使用费有效期结束时间", function() {});
        return;
      }
    }
    if (Number(that.data.parms.other_money) > Number(that.data.parms.payment_total_money)) {
      alertViewNoCancel("保存失败", "其他金额不可超出收款金额", function() {});
      return;
    }
    if(Number(that.data.parms.other_money) && !that.data.parms.other_money_remark){
      alertViewNoCancel("保存失败", "请输入其他金额类型", function() {});
      return;
    }
    if(!that.data.parms.other_money){
      that.setData({
        ['parms.other_money_remark']: '',
      })
    }
    if(hzIndex == 8 && paymentTotalMoney + roundOrderMoney + depositMoney === 0){
      alertViewNoCancel("保存失败", "收款金额、轮单费抵扣、保证金额不得同时为0！！！", function() {});
      return;
    }
    if(hzIndex == 10){
      if(!depositToRoundMoney){
        alertViewNoCancel("保存失败", "请输入保证金抵扣轮单费", function() {});
        return;
      } else if(depositToRoundMoney > depositMoney){
        alertViewNoCancel("保存失败", "抵扣金额不得超出保证金余额", function() {});
        return;
      }
    }

    if (paymentType == 0) {
      let name = ''
      if (hzIndex == 11) {
        name = '退款'
      } else {
        name = '收款'
      }
      alertViewNoCancel("保存失败", `请选择${name}类型`, function() {});
      return;
    }
    if (paymentTotalMoney && payee_list.length < 1 && hzIndex !== 13) {
      alertViewNoCancel("保存失败", "请选择收款方名称", function() {});
      return;
    }
    if (percent > 100) {
      alertViewNoCancel("保存失败", "业绩人的分成比例总和不得超过100%", function() {});
      return;
    }
    if(hzIndex == 11 && that.data.personList.length == 0){
      alertViewNoCancel("保存失败", "请选择相关业绩人", function() {});
      return;
    }
    if (that.data.parms.auth_imgs.length < 1 && paymentTotalMoney) {
      alertViewNoCancel("保存失败", "请上传汇款凭证", function() {});
      return;
    }
    if (that.data.parms.auth_imgs.length < 1 && hzIndex == 11) {
      alertViewNoCancel("保存失败", "请上传退款凭证", function() {});
      return;
    }
    let bsList = that.data.bsList
    let Ratios = that.data.Ratios
    if(hzIndex == 10 || hzIndex == 13){
      that.setData({
        ['parms.company_id']: that.data.companyId
      })
    }
    let viptype =  ''
    if(hzIndex == 14){
      viptype = 1
    } else {
      viptype = bsIndex > 0 ? bsList[bsIndex] : ''
    }
    let back_ratio =  ''
    if(hzIndex == 15){
      back_ratio = 1
    } else {
      back_ratio = RatioIndex > 0 ? Ratios[RatioIndex] : ''
    }
    that.setData({
      ['parms.cooperation_type']: hzIndex,
      ['parms.city_id']: cs,
      ['parms.viptype']: viptype,
      ['parms.back_ratio']: back_ratio,
      ['parms.payment_type']: paymentType,
      ['parms.is_submit']: 0,
      ['parms.person_list']: JSON.stringify(that.data.personList),
      ['parms.payee_list']: JSON.stringify(payee_list)
    })
    that.payeeDataSet()
  },
  // 提交前判断收款方
  payeeDataSet(){
    let that = this
    let payee_list =  JSON.parse(that.data.parms.payee_list)
    if ( payee_list.length === 0 ) {
      that.submitData()
    }else if ( payee_list.length === 1 ) {
      let checkDatas = that.data.checkDatas
      checkDatas[0].payee_money = that.data.parms.payment_total_money
      that.setData({
        ['parms.payee_list']: JSON.stringify(checkDatas)
      })
      that.submitData()
    }else{
      this.setData({
        showPayeeDialog:true
      })
    }
  },
  // 数据提交接口
  submitData(){
    let that = this
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        smalladd('/v1/sale_report/payment/save', that.data.parms, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 200
            })
            setTimeout(function() {
              wx.navigateTo({
                url: '../sReport/sReport'
              })
            }, 300)
          } else {
            alertViewNoCancel("提交失败", res.error_msg, function() {});
            return;
          }
        })
      }
    })
  },
  // 删除其他业绩人
  deletePerson: function(e) {
    let sale_id = e.currentTarget.dataset.id
    let that = this
    let arr = that.data.personList
    wx.showModal({
      title: '删除确认',
      content: '确认要删除该业绩人吗?',
      success(res) {
        if (res.confirm) {
          arr.forEach((index, item) => {
            if (index.saler_id == sale_id) {
              arr.splice(item, 1)
            }
          })
          that.setData({
            personList: arr
          })

        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },

  // 删除
  subDel: function(e) {
    let that = this
    alertViewWithCancel("删除确认", "确认要删除该报备信息吗？（该操作不可挽回！）", function() {
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          smalladd('/v1/sale_report/payment/delete', {
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
              setTimeout(function() {
                wx.navigateBack({
                  delta: 1,
                })
              }, 300)
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function() {});
              return;
            }
          })
        }
      })
    })
  },
  // 跳转至业绩人
  toPerformancePerson: function() {
    this.setData({
      ctype: ''
    })
    wx.navigateTo({
      url: '../performancePerson/performancePerson?id=' + this.data.id,
    })
  },
  getCompanyInfoBtn(){
    let that = this
    let val = that.data.parms.company_id
    let reg = /^[+]{0,1}(\d+)$/
    if (!val) {
      alertViewNoCancel("提示", '请输入网店代码', function() {})
    } else if (!reg.test(val)) {
      alertViewNoCancel("提示", '请输入正确网店代码', function() {})
    } else {
      that.getCompanyInfo()
    }
  },
  // 网店代码获取公司详情
  getCompanyInfo(){
    let that = this
    let hzIndex = that.hzIndex
    that.setData({
      companyId:that.data.parms.company_id
    })
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        let id = that.data.parms.company_id
        editinfo('/v1/sale_report/test_company', {uid: id}, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let data = res.data[0][0]
            if(data){
              that.setData({
                'parms.company_name':data.jc,
                'parms.deposit_money':Number(data.deposit_money),
                cs:data.cs,
                curCity:data.cname
              })
              if(hzIndex == 10){
                that.backRatioTo0()
              }
            }
          } else {
            alertViewNoCancel("查询失败", res.error_msg, function() {});
            return;
          }
        })
      }
    })
  },
  // 返点比例设置为0
  backRatioTo0(){
    let that = this
    let list = that.data.Ratios
    let RatioIndex = 0
    RatioIndex = list.findIndex(v => v === '0%')
    that.setData({
      RatioIndex:RatioIndex
    })
  },
  payeeInputChange(e){
    let that = this
    let val = e.detail.value
    let index = e.target.dataset.index
    let checkDatas = that.data.checkDatas
    checkDatas[index].payee_money = val
    that.setData({
      checkDatas
    })
  },
  payeeInputBlur(e){
    let that = this
    let val = e.detail.value
    let index = e.target.dataset.index
    let checkDatas = that.data.checkDatas
    checkDatas[index].payee_money = val
    that.setData({
      checkDatas
    })
  }

})