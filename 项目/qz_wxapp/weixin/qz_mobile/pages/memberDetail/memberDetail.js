// pages/memberDetail/memberDetail.js
import {
    memberDetail,
    seleOptions
} from "../../utils/api.js"

function getTime(timestamp) {
    let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '-';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}
const app = getApp()
Page({

    /**
     * 页面的初始数据
     */
    data: {
        member: '',
        common: false,
        erp: false,
        three: false,
        is_new: ['请选择', '新会员', '老会员'],
        is_newIndex: 0,
        times: ['请选择', '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5', '5.5', '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10'],
        timesIndex: 0,
        backRatios: ['请选择', '5%', '3%', '2%', '1%'],
        backRatioIndex: 0,
        tonglan: ['请选择', 'A通栏', 'B通栏', 'A+B通栏'],
        tonglanIndex: 0,
        zxType: ['请选择', '只接家装', '只接公装', '家装公装都接', '其他'],
        zxTypeIndex: 0,
        contract_days: '',
        current_contract_days: '',
        total_contract_fixed_amount: '',
        total_contract_bond: '',
        current_contract_fixed_amount: '',
        current_contract_bond: '',
        cashdeposit_handler:'',
        parms: {
            cooperation_type: '',
            cooperation_type_name: '',
            cooperation_mode: '',
            company_name: '',
            city_name: '',
            viptype: '',
            is_new: '',
            company_contact: '',
            company_contact_position: '',
            company_contact_phone: '',
            company_contact_weixin: '',
            contract_start: '',
            contract_end: '',
            current_contract_start: '',
            current_contract_end: '',
            current_payment_time: '',
            next_payment_time: '',
            total_contract_amount: '',
            current_contract_amount: '',
            lave_amount: '',
            logo_address: '',
            passage_position: '',
            contract_handsel_passage: '',
            contract_handsel_banner: '',
            current_adver_content: '',
            amount_and_area: '',
            lxs: '',
            lxs_remark: '',
            month_promised_orders: '',
            promised_orders: '',
            year_month_one: '',
            year_month_two: '',
            current_year_month_one: '',
            current_year_month_two: '',
            company_id: '',
            remarke: '',
            status: '',
            section: '',
            account: '',
            id: '',
            company_rolers: '',
            company_tag: '',
            imgs: [],
            contract_remark: '',
            total_contract_remark: '',
            is_abnormal:'',
            open_city_chat_remark:'',
            convert_order_count:0,
            total_convert_order_count:0,
            round_order_amount:'',
            total_contract_amount_guaranteed: '', // 总合同保产值金额
            current_contract_amount_guaranteed: '' // 本次合同保产值金额
        },
        checkLog:[],
        pics: [],
        kfPics: [],
        showModal: false,
        info:{}
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        let that = this;
        that.saleOptions()
        let id = options.id;
        let cooperation_type = options.cooperation_type;
        if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 6 || cooperation_type == 7 || cooperation_type == 14 || cooperation_type == 15) {
            that.setData({
                common: true,
                erp: false,
                three: false
            })
        } else if (cooperation_type == 5) {
            that.setData({
                common: false,
                erp: true,
                three: false
            })
        } else if (cooperation_type == 4) {
            that.setData({
                common: false,
                erp: false,
                three: true
            })
        } else if(cooperation_type == 8){
            that.setData({
                common: false,
                erp: false,
                three: false
            })
        }
        let option = {
            id: id,
            cooperation_type: cooperation_type,
            city_quote: 1
        }
        this.getDetail(option);
        this.getLog({
            cooperation_type: cooperation_type,
            report_id:id
        })
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
              let back_ratio_list = res.data.back_ratio_list
              back_ratio_list.unshift("请选择")
              that.setData({
                backRatios: back_ratio_list
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
    getDetail: function(option) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                memberDetail('/v1/sale_report/show?', option, {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                    if (res.error_code == 0) {
                        const dataInfo = res.data.info
                        let cooperation_type = res.data.info.cooperation_type;
                        let companyBbid = res.data.info.company_id || ''
                        if (res.data.info.next_payment_time == 0) {
                            res.data.info.next_payment_time = '';
                        } else {
                            res.data.info.next_payment_time = getTime(res.data.info.next_payment_time);
                        }
                        if (res.data.info.passage_position == '') {
                            that.data.tonglanIndex = 0;
                        } else {
                            that.data.tonglanIndex = res.data.info.passage_position;
                        }


                        if (res.data.info.deny_promised_end == 0) {
                            res.data.info.deny_promised_end = '';
                        } else {
                            res.data.info.deny_promised_end = getTime(res.data.info.deny_promised_end);
                        }
                        if (res.data.info.lave_amount == -1) {
                            res.data.info.lave_amount = 0
                        }
                        for (var i = 0; i < res.data.info.img_list.length; i++) {
                            that.data.pics[i] = app.globalData.imgBaseUrl + '/' + res.data.info.img_list[i]
                        }

                        for (var i = 0; i < res.data.info.kf_voucher_img.length; i++) {
                          that.data.kfPics[i] = app.globalData.imgBaseUrl + '/' + res.data.info.kf_voucher_img[i]
                        }
                        that.setData({
                            pics: that.data.pics,
                            kfPics: that.data.kfPics,
                            companyBbid: companyBbid,
                            'parms.cooperation_type_name': dataInfo.cooperation_type_name
                        })
                        if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 6 || cooperation_type == 7 || cooperation_type == 14 || cooperation_type == 15) {
                            that.setData({
                                member: res.data.info.cooperation_type,
                                ['parms.cooperation_type']: res.data.info.cooperation_type,
                                ['parms.company_name']: res.data.info.company_name,
                                ['parms.city_name']: res.data.info.city_name,
                                is_newIndex: res.data.info.is_new,
                                timesIndex: res.data.info.viptype == '0.0' ? '0' : parseInt(res.data.info.viptype * 2),
                                backRatioIndex: res.data.info.back_ratio == '5%' ? 1 : (res.data.info.back_ratio == '3%' ? 2 : (res.data.info.back_ratio == '2%' ? 3 : (res.data.info.back_ratio == '1%' ? 4 : (res.data.info.back_ratio == '0%' ? 5: 0)))),
                                ['parms.company_contact']: res.data.info.company_contact,
                                ['parms.company_contact_position']: res.data.info.company_contact_position,
                                ['parms.company_contact_phone']: res.data.info.company_contact_phone,
                                ['parms.company_contact_weixin']: res.data.info.company_contact_weixin,
                                ['parms.contract_start']: res.data.info.contract_start==0 ? '' : getTime(res.data.info.contract_start),
                                ['parms.contract_end']: res.data.info.contract_end == 0 ? '' : getTime(res.data.info.contract_end),
                                ['parms.current_contract_start']: res.data.info.current_contract_start == 0 ? '' : getTime(res.data.info.current_contract_start),
                                ['parms.current_contract_end']: res.data.info.current_contract_end == 0 ? '' :  getTime(res.data.info.current_contract_end),
                                ['parms.current_payment_time']: getTime(res.data.info.current_payment_time),
                                ['parms.next_payment_time']: res.data.info.next_payment_time,
                                contract_days: dataInfo.contract_days_desc,
                                current_contract_days: dataInfo.current_contract_days_desc,
                                ['parms.total_contract_amount']: res.data.info.total_contract_amount,
                                ['parms.current_contract_amount']: res.data.info.current_contract_amount,
                                ['parms.lave_amount']: res.data.info.lave_amount,
                                ['parms.logo_address']: res.data.info.logo_address,
                                ['parms.passage_position']: res.data.info.passage_position,
                                tonglanIndex: that.data.tonglanIndex,
                                ['parms.contract_handsel_passage']: res.data.info.contract_handsel_passage,
                                ['parms.contract_handsel_banner']: res.data.info.contract_handsel_banner,
                                ['parms.current_adver_content']: res.data.info.current_adver_content,
                                ['parms.amount_and_area']: res.data.info.amount_and_area,
                                ['parms.lxs']: res.data.info.lxs,
                                ['parms.lxs_remark']: res.data.info.lxs_remark,
                                zxTypeIndex: res.data.info.lxs,
                                ['parms.promised_orders_fen']: res.data.info.promised_orders_fen,
                                ['parms.promised_orders_zeng']: res.data.info.promised_orders_zeng,
                                ['parms.current_promised_orders_fen']: res.data.info.current_promised_orders_fen,
                                ['parms.current_promised_orders_zeng']: res.data.info.current_promised_orders_zeng,
                                ['parms.round_order_amount']: res.data.info.round_order_amount,
                                ['parms.year_month_one']: res.data.info.year_month_one,
                                ['parms.year_month_two']: res.data.info.year_month_two,
                                ['parms.company_id']: res.data.info.company_id,
                                ['parms.remarke']: res.data.info.remarke,
                                ['parms.id']: res.data.info.id,
                                ['parms.year_month_one']: res.data.info.year_month_one == '' ? '--' : res.data.info.year_month_one,
                                ['parms.year_month_two']: res.data.info.year_month_two == '' ? '--' : res.data.info.year_month_two,
                                ['parms.current_year_month_one']: res.data.info.current_year_month_one == '' ? '--' : res.data.info.current_year_month_one,
                                ['parms.current_year_month_two']: res.data.info.current_year_month_two == '' ? '--' : res.data.info.current_year_month_two,
                                ['parms.contract_remark']: res.data.info.contract_remark,
                                ['parms.total_contract_remark']: res.data.info.total_contract_remark,
                                ['parms.is_abnormal']: res.data.info.is_abnormal,
                                ['parms.open_city_chat_remark']: res.data.info.open_city_chat_remark,
                                total_contract_round_amount: res.data.info.total_contract_round_amount, //总合同轮单费
                                current_contract_round_amount: res.data.info.current_contract_round_amount, // 本次合同轮单费
                                showCompany: false,
                                report_payment_money: res.data.info.report_payment_money, // 添加小报备金额
                                report_payment_number: res.data.info.report_payment_number,
                                report_money_compart: res.data.info.report_money_compart,
                                company_report_payment_number: res.data.info.company_report_payment_number,
                                company_report_number: res.data.info.company_report_number,
                                info: dataInfo
                            })
                            if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3){
                                that.setData({
                                    ['parms.convert_order_count']: dataInfo.convert_order_count,
                                    ['parms.total_convert_order_count']: dataInfo.total_convert_order_count
                                })
                            }
                            if (cooperation_type == 6 || cooperation_type == 15){
                                that.setData({
                                    total_contract_fixed_amount: res.data.info.total_contract_fixed_amount,
                                    total_contract_bond: res.data.info.total_contract_bond,
                                    total_contract_round_total_amount: res.data.info.total_contract_round_total_amount,
                                    total_contract_gift_amount: res.data.info.total_contract_gift_amount,
                                    current_contract_fixed_amount: res.data.info.current_contract_fixed_amount,
                                    current_contract_bond: res.data.info.current_contract_bond,
                                    current_contract_round_total_amount: res.data.info.current_contract_round_total_amount,
                                    current_contract_gift_amount: res.data.info.current_contract_gift_amount,
                                    cashdeposit_handler: res.data.info.cashdeposit_handler? getTime(res.data.info.cashdeposit_handler) : ''
                                })
                            }
                            if (cooperation_type == 14 || cooperation_type == 15 ){
                                that.setData({
                                    ['parms.total_contract_amount_guaranteed']: res.data.info.total_contract_amount_guaranteed,
                                    ['parms.current_contract_amount_guaranteed']: res.data.info.current_contract_amount_guaranteed
                                })
                            }
                        } else if (cooperation_type == 4) {
                            that.setData({
                                member: res.data.info.cooperation_type,
                                ['parms.cooperation_type']: res.data.info.cooperation_type,
                                ['parms.company_name']: res.data.info.company_name,
                                ['parms.company_contact']: res.data.info.company_contact,
                                ['parms.company_contact_phone']: res.data.info.company_contact_phone,
                                ['parms.current_contract_start']: getTime(res.data.info.current_contract_start),
                                ['parms.current_contract_end']: getTime(res.data.info.current_contract_end),
                                ['parms.current_contract_amount']: res.data.info.current_contract_amount,
                                ['parms.remarke']: res.data.info.remarke,
                                ['parms.section']: res.data.info.section,
                                ['parms.account']: res.data.info.account,
                                ['parms.id']: res.data.info.id,
                                ['parms.company_rolers']: res.data.info.company_rolers,
                                ['parms.company_tag']: res.data.info.company_tag,
                                showCompany: false,
                            })
                        } else if (cooperation_type == 5) {
                            that.setData({
                                member: res.data.info.cooperation_type,
                                ['parms.cooperation_type']: res.data.info.cooperation_type,
                                ['parms.cooperation_mode']: res.data.info.cooperation_mode,
                                ['parms.company_name']: res.data.info.company_name,
                                ['parms.city_name']: res.data.info.city_name,
                                is_newIndex: res.data.info.is_new,
                                timesIndex: parseInt(res.data.info.viptype * 2),
                                ['parms.company_contact']: res.data.info.company_contact,
                                ['parms.company_contact_position']: res.data.info.company_contact_position,
                                ['parms.company_contact_phone']: res.data.info.company_contact_phone,
                                ['parms.company_contact_weixin']: res.data.info.company_contact_weixin,
                                ['parms.contract_start']: getTime(res.data.info.contract_start),
                                ['parms.contract_end']: getTime(res.data.info.contract_end),
                                ['parms.current_contract_start']: getTime(res.data.info.current_contract_start),
                                ['parms.current_contract_end']: getTime(res.data.info.current_contract_end),
                                ['parms.current_payment_time']: getTime(res.data.info.current_payment_time),
                                ['parms.next_payment_time']: res.data.info.next_payment_time,
                                contract_days: dataInfo.contract_days_desc,
                                ['parms.total_contract_amount']: res.data.info.total_contract_amount,
                                ['parms.current_contract_amount']: res.data.info.current_contract_amount,
                                ['parms.lave_amount']: res.data.info.lave_amount,
                                ['parms.company_id']: res.data.info.company_id,
                                ['parms.remarke']: res.data.info.remarke,
                                ['parms.account']: res.data.info.account,
                                ['parms.id']: res.data.info.id,
                                ['parms.open_city_chat_remark']: res.data.info.open_city_chat_remark,
                                showCompany: false,
                            })
                        } else if (cooperation_type == 8) {
                            that.setData({
                                info: dataInfo
                            })
                        }
                    }
                })
            }
        })
    },
    getLog(option){
        let that = this
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                memberDetail('/v1/sale_report/log?', option, {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                    if (res.error_code == 0) {
                        let checkLog = res.data.list
                        that.setData({
                            checkLog
                        })
                    }
                })
            }
        })
    
    },
    previewImg1: function(e) {
        //获取当前图片的下标
        var index = e.currentTarget.dataset.index;
        //所有图片
        var pics = this.data.pics;
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
      var kfPics = this.data.kfPics;
      wx.previewImage({
        //当前显示图片
        current: kfPics[index],
        //所有图片
        urls: kfPics
      })
    },
    tapDpu:function(e){
      let that =this
      that.setData({
        showModal : true
      })
    },
    preventTouchMove:function(e){
      let that = this
      that.setData({
        showModal: false
      })
    },
    bingoBb:function(e){
      let that = this
      let url = e.currentTarget.dataset.url
      let comid = that.data.companyBbid
      wx.navigateTo({
        url: url+'?comid='+comid
      })
    },
    // 应延期天数弹窗
    popRemark: function(e) {
        wx.showModal({
        title: '应延期天数名词解释：',
        content: '本次分单差额/最近三个月每日平均分单数',
        showCancel: false,
        confirmText: '知道了',
        success(res) {
            if (res.confirm) {}
        }
        })
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

    }
})