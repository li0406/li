import {
    memberReportDetail,
    memberReportCheckAction,
    historyQuote
} from "../../utils/api.js"

function getTime(timestamp) {
    let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '/';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function(res) {
            if (res.confirm) {
                confirm();
            } else if (res.cancel) {

            }
        }
    });
}
const app = getApp()
Page({

    /**
     * 页面的初始数据
     */
    data: {
        type: '', // 记录当前显示哪个栏目，装修公司，erp还是三维家
        common: false,
        erp: false,
        three: false,
        id: '', // 当前记录id
        ctype: '', // 会员类型
        info: null, // 数据信息
        maskOfferShow: false,
        offerHeight: 0,
        city_quote: null,
        contract_days: '',
        current_contract_days:'',
        isShowModal: false,
        modalTitle: '审核',
        checkCooperationType: '', // 需要审核记录的cooperation_type
        checkId: '', // 需要审核记录的id
        checkPassVal: '', // 存储通过还是不通过的值，3表示通过，4表示不通过
        checkRemark: '', // 存储审核备注
        activeTag: false,
        zxType: ['请选择', '只接家装', '只接公装', '家装公装都接', '其他'],
        hasQuote: false,
        showModal: false,
        showHistoryModal: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        const type = options.type
        const id = options.id
        const ctype = options.ctype
        this.setData({
            id: id,
            checkId: id,
            ctype: ctype,
            checkCooperationType: ctype
        })
        this.getDetail()
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {
        this.setData({
            showModal:false
          })
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    },

    /**
     * 获取当前记录详情
     */
    getDetail() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                memberReportDetail('/v1/sale_report/show', {
                    id: that.data.id,
                    cooperation_type: that.data.ctype,
                    admin_user: 1,
                    city_quote: 1
                }, {
                    'Content-Type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        let contract_days = res.data.info.contract_days
                        let current_contract_days = res.data.info.current_contract_days
                        const info = res.data.info
                        info.contract_start = info.contract_start == 0 ? '' : getTime(info.contract_start)
                        info.contract_end = info.contract_end == 0 ? '' : getTime(info.contract_end)
                        info.current_contract_start = info.current_contract_start == 0 ? '' :  getTime(info.current_contract_start)
                        info.current_contract_end = info.current_contract_end == 0 ? '' :  getTime(info.current_contract_end)
                        info.current_payment_time = getTime(info.current_payment_time)
                        info.next_payment_time = info.next_payment_time ? getTime(info.next_payment_time) : info.next_payment_time
                        info.year_month_one = info.year_month_one
                        info.year_month_two = info.year_month_two
                        info.cashdeposit_handler = info.cashdeposit_handler ? getTime(info.cashdeposit_handler):''
                        info.lxs = that.transferLxs(info.lxs)
                        info.city_quote.create_time = info.city_quote.create_time == '' ? '--' : info.city_quote.create_time
                        if (info.city_quote) {
                            info.city_quote.level = that.transferLevel(info.city_quote.little)
                        }
                        for (var i = 0; i < info.img_list.length; i++) {
                            info.img_list[i] = app.globalData.imgBaseUrl + '/' + info.img_list[i]
                        }
                        for (var i = 0; i < info.kf_voucher_img.length; i++) {
                          info.kf_voucher_img[i] = app.globalData.imgBaseUrl + '/' + info.kf_voucher_img[i]
                        }
                        info.paymentImg = []
                        if(info.payment_img && info.payment_img.length > 0){
                            for (var i = 0; i < info.payment_img.length; i++) {
                                info.paymentImg.push(app.globalData.imgBaseUrl + '/' + info.payment_img[i].img_src)
                            }
                        }
                        that.setData({
                            info: res.data.info,
                            city_quote: info.city_quote,
                            companyBbid: res.data.info.company_id
                        })
                        wx.setNavigationBarTitle({
                            title: '查看' + info.cooperation_type_name
                        })
                        if (contract_days != '') {
                            let month = parseInt(contract_days / 30) > 0 ? parseInt(contract_days / 30) + '个月' : ''
                            let day = contract_days % 30 > 0 ? contract_days % 30 + '天' : ''
                            that.setData({
                                contract_days: month + day
                            })
                        }else{
                            that.setData({
                                contract_days: '0天'
                            })
                        }
                        if (current_contract_days != '') {
                            let month = parseInt(current_contract_days / 30) > 0 ? parseInt(current_contract_days / 30) + '个月' : ''
                            let day = current_contract_days % 30 > 0 ? current_contract_days % 30 + '天' : ''
                            that.setData({
                                current_contract_days: month + day
                            })
                        }else{
                            that.setData({
                                current_contract_days: '0天'
                            })
                        }
                    } else {
                        wx.showToast({
                            title: res.error_msg,
                            duration: 2000,
                            icon: 'none'
                        })
                    }
                })
            },
            fail: function(res) {
                wx.navigateTo({
                    url: '../login/login'
                })
            }
        })
    },
    /**
     * 显示报价详情
     */
    showOfferDetail() {
        this.setData({
            maskOfferShow: true,
            offerHeight: "100%"
        })
        setTimeout(() => {
            this.setData({
                activeTag: true
            })
        }, 200)
    },
    /**
     * 隐藏报价详情
     */
    hideMaskOffer() {
        this.setData({
            maskOfferShow: false
        })
    },
    transferLevel(val) {
        switch (val) {
            case 0:
                return 'A类'
                break
            case 1:
                return 'B类'
                break
            case 2:
                return 'C类'
                break
            case 3:
                return 'D类'
                break
            case 4:
                return 'S1类'
                break
            case 5:
                return 'S2类'
                break
            default:
                return ''
        }
    },
    transferLxs(val) {
        switch (val) {
            case 1:
                return '家装'
                break
            case 2:
                return '公装'
                break
            case 3:
                return '公装/家装'
                break
            case 4:
                return '其他'
                break
            default:
                return ''
        }
    },
    /**
     * 自定义模态框取消事件
     */
    modalCancel() {
        this.setData({
            isShowModal: false
        })
    },
    /**
     * 自定义模态框确认事件
     */
    modalConfirm() {

        if (!this.data.checkPassVal) {
            alertViewWithCancel('提示', '请选择是否通过', function() {})
            this.setData({
                isShowModal: true
            })
            return
        }
        this.handleCheckAjax()
    },
    historyQuote() {
      let that = this;
      let city_name = that.data.info.city_name
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          historyQuote('/v1/quote/history_city_quote', {
            city_name: city_name
          }, {
              'Content-Type': 'application/json',
              'token': token
            }).then(res => {
              if(res.error_code == 0){
                if (res.data.list.length > 1){
                  const info = res.data.list[1]
                  if (res.data.list.content) {
                    info.city_quote.level = that.transferLevel(res.data.list.content.little)
                  }
                  that.setData({
                    hasQuote: true,
                    info: info,
                    city_quote: info.content
                  })
                }else{
                  return false;
                }
              }
            })
        }
      })
    },
    /**
     * 发送审核数据
     */
    handleCheckAjax() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: (res) => {
                let token = res.data.token;
                memberReportCheckAction('/v1/sale_report/set_status', {
                    cooperation_type: this.data.checkCooperationType,
                    id: this.data.checkId,
                    status: this.data.checkPassVal, // 通过填3， 不通过填4
                    admin_remarke: this.data.checkRemark
                }, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        that.setData({
                            isShowModal: false
                        })
                        wx.showToast({
                            title: '审核成功',
                            icon: 'none',
                            duration:3000
                        })
                        setTimeout(function(){
                            wx.navigateBack({
                                url: '../memberReportCheck/memberReportCheck',
                            })
                        },2000)
                    } else {
                        that.setData({
                            isShowModal: true
                        })
                        alertViewWithCancel('提示', res.error_msg, function() {})
                    }
                })
            }
        })
    },
    /**
     * 获取通过还是不通过的值
     */
    radioChange(e) {
        this.setData({
            checkPassVal: e.detail.value
        })
    },
    /**
     * 获取备注的值
     */
    getRemark(e) {
        this.setData({
            checkRemark: e.detail.value
        })
    },
    /**
     *  预览图片
     */
    previewImg1: function(e) {
        //获取当前图片的下标
        var index = e.currentTarget.dataset.index;
        //所有图片
        var pics = this.data.info.img_list;
        wx.previewImage({
            //当前显示图片
            current: pics[index],
            //所有图片
            urls: pics
        })
    },
    previewImg2: function (e) {
      //获取当前图片的下标
      var index = e.currentTarget.dataset.index;
      //所有图片
      var kfPics = this.data.info.kf_voucher_img;
      wx.previewImage({
        //当前显示图片
        current: kfPics[index],
        //所有图片
        urls: kfPics
      })
    },
    previewImg3: function (e) {
        //获取当前图片的下标
        var index = e.currentTarget.dataset.index;
        //所有图片
        var kfPics = this.data.info.paymentImg;
        wx.previewImage({
          //当前显示图片
          current: kfPics[0],
          //所有图片
          urls: kfPics
        })
      },
    /**
     * 显示审核弹窗，同时要记录cooperation_type和id
     */
    showModalFn(e) {
        this.setData({
            isShowModal: true,
            checkRemark: '',
            checkPassVal: ''
        })
    },
    showModalHistoryFn(e) {
        let that = this
        that.setData({
            showHistoryModal: true,
        })
    },
    closeOffer() {
        this.setData({
            maskOfferShow: false,
            activeTag: false,
            hasQuote:false
        })
      this.getDetail()
    },
  tapDpu: function (e) {
    let that = this
    that.setData({
      showModal: true
    })
  },
  preventTouchMove: function (e) {
    let that = this
    that.setData({
      showModal: false
    })
  },
  bingoBb: function (e) {
    let that = this
    let url = e.currentTarget.dataset.url
    let comid = that.data.companyBbid
    let type = that.data.ctype
    if (parseInt(e.currentTarget.dataset.active) === 1){
        wx.navigateTo({
            url: url + '?report_id=' + this.data.id+'&type='+type
        })
    } else if (parseInt(e.currentTarget.dataset.active) === 2){
        wx.navigateTo({
            url: url + '?comid=' + comid
        })
    }
    
  },
})