// pages/addReport/addReport.js
import { addReport, testCompany, memberDetail, checkContract, seleOptions } from "../../utils/api.js"
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
function getTime(timestamp) {
    let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '-';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}
function timeReturn(timeAs, timeAe, timeBs, timeBe){
    const maxA = new Date(timeAs).getTime()
    const maxB = new Date(timeBs).getTime()
    const minA = new Date(timeAe).getTime()
    const minB = new Date(timeBe).getTime()
    const max = [maxA, maxB]
    const min = [minA, minB]
    return (Math.max.apply(null, max) <= Math.min.apply(null, min))
}

function validateInt(val){
    const reg = /^[+]{0,1}(\d+)$/
    if(!reg.test(val)){
        return true
    }else{
        return false
    }
}
function validateIntNoZero(val){
    const reg = /^[1-9]\d*$/
    if(!reg.test(val)){
        return true
    }else{
        return false
    }
}
Page({

    /**
     * 页面的初始数据
     */
    data: {
        member: '',
        typeChoose: false,
        common: false,
        erp: false,
        three: false,
        typeList: [],
        is_new: ['请选择', '新会员', '老会员'],
        oper_type: ['请选择', '一年', '终身'],
        is_kf_voucher: ['否', '是'],
        kfIndex: 0,
        is_newIndex: 0,
        is_typeIndex: 0,
        times: ['请选择', '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5', '5.5', '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10'],
        timesIndex: 0,
        backRatios: ['请选择', '5%', '3%', '2%', '1%'],
        backRatioIndex: 0,
        emptyOrderOptionsList: [],
        tonglan: ['请选择', 'A通栏', 'B通栏', 'A+B通栏'],
        tonglanIndex: 0,
        zxType: ['请选择', '只接家装', '只接公装', '家装公装都接', '其他'],
        zxTypeIndex: 0,
        totalTime: '',
        userTime: '',
        parms: {
            cooperation_type: '',
            cooperation_mode: '',
            company_name: '',
            city_name: '',
            viptype: '',
            back_ratio: '',
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
            promised_orders_fen: '',
            promised_orders_zeng: '',
            current_promised_orders_fen: '',
            current_promised_orders_zeng: '',
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
            total_contract_bond: '',
            total_contract_round_amount: '', // 总合同
            total_contract_round_total_amount: '',
            total_contract_gift_amount: 0,
            current_contract_bond: '',
            current_contract_round_amount: '', //本次
            current_contract_round_total_amount: '',
            current_contract_gift_amount: 0,
            contract_remark: '',
            total_contract_remark: '',
            is_abnormal:'',
            is_kf_voucher:'',
            open_city_chat_remark: '',
            round_order_amount: '', //轮单费单价
            current_vip_id: '', //延期会员 会员合同id
            report_id: '', //延期会员 报备id
            total_platform_money: '',
            total_platform_money_start_date: '',
            total_platform_money_end_date: '',
            current_platform_money: '',
            current_platform_money_start_date: '',
            current_platform_money_end_date: '',
            total_contract_amount_guaranteed: '', // 总合同保产值金额
            current_contract_amount_guaranteed: '' // 本次合同保产值金额
        },
        pics: [],
        kfPics: [],
        showCompany: false,
        company_jc: '',
        curCity: '请选择',
        cs: '',
        parmsStatus: false, // 是否是类型6
        cooperation_type_name:'', //合作类型名称
        vipContractList:[], // 会员延期-本次会员时间列表
        reportContractList:[], // 会员延期-本次报备合同列表
        currentVipIndex:'', // 会员延期-本次会员时间index
        currentReportIndex:'', // 会员延期-本次报备合同index
        orderFenAvg:'', // 会员延期-近三个月平均每天分单
        totalPlatformMoneyTime: '0天',
        currentPlatformMoneyTime: '0天'
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        that.saleOptions()
        let id = options.id;
        let cooperation_type = options.cooperation_type;
        let common = false
        let erp = false
        let three = false
        if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 6 || cooperation_type == 7 || cooperation_type == 14 || cooperation_type == 15) {
            common = true,
            erp = false,
            three = false
        } else if (cooperation_type == 5) {
            common = false,
            erp = true,
            three = false
        } else if (cooperation_type == 4) {
            common = false,
            erp = false,
            three = true
        }
        that.setData({
            member:cooperation_type,
            common: common,
            erp: erp,
            three: three
        })
        let option = {
            id: id,
            cooperation_type: cooperation_type
        }
        that.getDetail(option);
        let currentDate = that.getCurrentDate()
        that.setData({
            currentDate : currentDate
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
              let yearMonthList = res.data.year_month_list
              let typeList = res.data.cooperation_type_list
              let back_ratio_list = res.data.back_ratio_list
              back_ratio_list.unshift("请选择")
              that.setData({
                emptyOrderOptionsList: yearMonthList,
                backRatios: back_ratio_list,
                typeList : typeList
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
    getDetail: function (option) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function (res) {
                let token = res.data.token;
                memberDetail('/v1/sale_report/show', option, {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                    if (res.error_code == 0) {
                        let cooperation_type = res.data.info.cooperation_type;
                        if (res.data.info.status == 6) {
                            that.setData({
                                parmsStatus: true
                            })
                        }
                        if (res.data.info.next_payment_time == 0) {
                            res.data.info.next_payment_time = '';
                        } else {
                            res.data.info.next_payment_time = getTime(res.data.info.next_payment_time);
                        }
                        if (res.data.info.passage_position == '') {
                            that.data.tonglanIndex = 0;
                        } else {
                            that.data.tonglanIndex = res.data.info.passage_position
                        }
                        if (res.data.info.deny_promised_start == 0) {
                            res.data.info.deny_promised_start = '';
                        } else {
                            res.data.info.deny_promised_start = getTime(res.data.info.deny_promised_start);
                        }
                        if (res.data.info.deny_promised_end == 0) {
                            res.data.info.deny_promised_end = '';
                        } else {
                            res.data.info.deny_promised_end = getTime(res.data.info.deny_promised_end);
                        }
                        if (res.data.info.lave_amount == -1) {
                            res.data.info.lave_amount = 0
                        }
                        var img_list = res.data.info.img_list

                        for (var i = 0; i < img_list.length; i++) {
                            that.data.pics[i] = app.globalData.imgBaseUrl + '/' + img_list[i]
                        }
                        var kf_voucher_img = res.data.info.kf_voucher_img
                        for (var i = 0; i < kf_voucher_img.length; i++) {
                          that.data.kfPics[i] = app.globalData.imgBaseUrl + '/' + kf_voucher_img[i]
                        }
                        that.setData({
                          pics: that.data.pics,
                          kfPics: that.data.kfPics
                        })
                        if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 6 || cooperation_type == 7 || cooperation_type == 14 || cooperation_type == 15) {
                            that.setData({
                                member: res.data.info.cooperation_type,
                                cooperation_type_name: res.data.info.cooperation_type_name,
                                ['parms.cooperation_type']: res.data.info.cooperation_type,
                                ['parms.company_name']: res.data.info.company_name,
                                ['parms.city_name']: res.data.info.city_name,
                                ['parms.cs']: res.data.info.cs,
                                curCity: res.data.info.city_name,
                                cs: res.data.info.cs,
                                is_newIndex: res.data.info.is_new,
                                timesIndex: parseInt(res.data.info.viptype * 2),
                                backRatioIndex: !res.data.info.back_ratio ? '0' : that.data.backRatios.indexOf(res.data.info.back_ratio),
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
                                ['parms.year_month_one']: res.data.info.year_month_one,
                                ['parms.year_month_two']: res.data.info.year_month_two,
                                ['parms.current_year_month_one']: res.data.info.current_year_month_one,
                                ['parms.current_year_month_two']: res.data.info.current_year_month_two,
                                ['parms.company_id']: res.data.info.company_id,
                                ['parms.remarke']: res.data.info.remarke,
                                ['parms.id']: res.data.info.id,
                                ['parms.imgs']: img_list,
                                ['parms.contract_remark']: res.data.info.contract_remark,
                                ['parms.total_contract_remark']: res.data.info.total_contract_remark,
                                ['parms.is_abnormal']: res.data.info.is_abnormal,
                                ['parms.open_city_chat_remark']: res.data.info.open_city_chat_remark,
                                ['parms.total_contract_round_amount']: res.data.info.total_contract_round_amount, //总合同轮单费
                                ['parms.current_contract_round_amount']: res.data.info.current_contract_round_amount, //本次合同轮单费
                                ['parms.total_platform_money']: res.data.info.total_platform_money,
                                ['parms.current_platform_money']: res.data.info.current_platform_money,
                                ['parms.total_platform_money_start_date']: res.data.info.total_platform_money_start_date,
                                ['parms.total_platform_money_end_date']: res.data.info.total_platform_money_end_date,
                                ['parms.current_platform_money_start_date']: res.data.info.current_platform_money_start_date,
                                ['parms.current_platform_money_end_date']: res.data.info.current_platform_money_end_date,
                                pics: that.data.pics,
                                kfPics: that.data.kfPics,
                                showCompany: false,
                                totalPlatformMoneyTime: res.data.info.total_platform_money_use_days,
                                currentPlatformMoneyTime: res.data.info.current_platform_money_use_days,
                                totalTime: res.data.info.contract_days_desc,
                                userTime: res.data.info.current_contract_days_desc
                            })
                            if (cooperation_type == 6 || cooperation_type == 15){
                                that.setData({
                                    ['parms.total_contract_bond']: res.data.info.total_contract_bond,
                                    ['parms.total_contract_round_total_amount']: res.data.info.total_contract_round_total_amount,
                                    ['parms.total_contract_gift_amount']: res.data.info.total_contract_gift_amount,
                                    ['parms.current_contract_bond']: res.data.info.current_contract_bond,
                                    ['parms.current_contract_round_total_amount']: res.data.info.current_contract_round_total_amount,
                                    ['parms.current_contract_gift_amount']: res.data.info.current_contract_gift_amount,
                                    ['parms.round_order_amount']: res.data.info.round_order_amount,
                                })
                            }
                            if(cooperation_type == 14 || cooperation_type == 15){
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
                                ['parms.imgs']: img_list,
                                pics: that.data.pics,
                                kfPics: that.data.kfPics,
                                showCompany: false,
                            })
                        } else if (cooperation_type == 5) {
                            that.setData({
                                member: res.data.info.cooperation_type,
                                ['parms.cooperation_type']: res.data.info.cooperation_type,
                                ['parms.company_name']: res.data.info.company_name,
                                ['parms.city_name']: res.data.info.city_name,
                                ['parms.cs']: res.data.info.cs,
                                curCity: res.data.info.city_name,
                                cs: res.data.info.cs,
                                is_newIndex: res.data.info.is_new,
                                is_typeIndex: res.data.info.cooperation_mode,
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
                                ['parms.total_contract_amount']: res.data.info.total_contract_amount,
                                ['parms.current_contract_amount']: res.data.info.current_contract_amount,
                                ['parms.lave_amount']: res.data.info.lave_amount,
                                ['parms.company_id']: res.data.info.company_id,
                                ['parms.remarke']: res.data.info.remarke,
                                ['parms.account']: res.data.info.account,
                                ['parms.id']: res.data.info.id,
                                ['parms.imgs']: img_list,
                                ['parms.open_city_chat_remark']: res.data.info.open_city_chat_remark,
                                pics: that.data.pics,
                                kfPics: that.data.kfPics,
                                showCompany: false,
                                totalTime: res.data.info.contract_days_desc,
                                userTime: res.data.info.current_contract_days_desc
                            })
                        } else if (cooperation_type == 8) {
                            let info = res.data.info
                            that.setData({
                                ['parms.id']: info.id,
                                ['parms.cooperation_type']: info.cooperation_type,
                                ['parms.company_id']: info.company_id,
                                ['parms.company_name']: info.company_name,
                                ['parms.company_mode_name']: info.company_mode_name,
                                ['parms.current_fen_orders']: info.current_fen_orders,
                                ['parms.promise_orders']: info.promise_orders,
                                ['parms.delay_promise_orders']: info.delay_promise_orders,
                                ['parms.delay_days']: info.delay_days,
                                ['parms.delay_real_days']: info.delay_real_days,
                                ['parms.delay_contract_start']: info.delay_contract_start_show,
                                ['parms.delay_contract_end']: info.delay_contract_end_show,
                                ['parms.current_vip_id']: info.current_vip_id,
                                ['parms.current_vip_start']: info.current_vip_start_show,
                                ['parms.current_vip_end']: info.current_vip_end_show,
                                ['parms.report_cooperation_type']: info.report_cooperation_type,
                                ['parms.report_id']: info.report_id,
                                ['parms.current_contract_start']: info.current_contract_start_show,
                                ['parms.current_contract_end']: info.current_contract_end_show,
                                ['parms.remarke']: info.remarke,
                            })
                            that.searchCompanyInfo('',1)
                        }
                    }
                })
            }
        })
    },
    save: function (e) {
        this.handleData(e,true)
    },
    submit: function (e) {
        this.handleData(e,false)
    },
    handleData(e,isSave){
        let that = this;
        let cooperation_type = that.data.parms.cooperation_type;
        let status = e.currentTarget.dataset.status;
        let cs = that.data.cs
        let curCity = that.data.curCity
        if (cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 6 || cooperation_type == 7 || cooperation_type == 14 || cooperation_type == 15) {

            if (that.data.parms.company_name == '') {
                alertViewNoCancel("保存失败", "请输入公司名称", function () {});
                return;
            }
            if (cs == '') {
                alertViewNoCancel("保存失败", "请选择城市", function () {});
                return;
                }
            if (cooperation_type == 6) {
                if (that.data.backRatioIndex == 0) {
                    alertViewNoCancel("保存失败", "请选择返点比例", function () {});
                    return;
                }
            } else if(cooperation_type == 1 || cooperation_type == 2 || cooperation_type == 3 || cooperation_type == 7){
                if (that.data.timesIndex == 0) {
                    alertViewNoCancel("保存失败", "请选择倍数", function () {});
                    return;
                }
            }

            if (that.data.is_newIndex == 0) {
                alertViewNoCancel("保存失败", "请选择新/老会员", function () {});
                return;
            }
            if (that.data.parms.company_contact == '') {
                alertViewNoCancel("保存失败", "请输入联系人", function () {});
                return;
            }
            if (that.data.parms.company_contact_phone == '') {
                alertViewNoCancel("保存失败", "请输入电话", function () {});
                return;
            } else if (that.data.parms.company_contact_phone != '') {
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!reg.test(that.data.parms.company_contact_phone)) {
                    alertViewNoCancel("提示", "请输入正确的手机号", function () {});
                    return false;
                }
            }
            if (that.data.parms.contract_start == '' && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "请选择总合同开始时间", function () {});
                return;
            } else if (that.data.parms.contract_end == '' && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "请选择总合同结束时间", function () {});
                return;
            } else if ((that.data.parms.contract_start > that.data.parms.contract_end) && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "总合同开始时间不能大于结束时间", function () {});
                return;
            }
            if (that.data.parms.current_contract_start == '' && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "请选择本次会员开始时间", function () {});
                return;
            } else if (that.data.parms.current_contract_end == '' && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "请选择本次会员结束时间", function () {});
                return;
            } else if ((that.data.parms.current_contract_start > that.data.parms.current_contract_end) && cooperation_type != 6 && cooperation_type != 15) {
                alertViewNoCancel("保存失败", "本次会员开始时间不能大于结束时间", function () {});
                return;
            }
            if (that.data.parms.current_payment_time == '') {
                alertViewNoCancel("保存失败", "请选择本次款项到账时间", function () {});
                return;
            }
            if (String(that.data.parms.total_contract_amount) == '') {
                alertViewNoCancel("保存失败", "请输入总合同金额", function () {});
                return;
            } else if (String(that.data.parms.total_contract_amount) != '') {
                var reg = /^[1-9]+[0-9]*$/;
                if (!reg.test(that.data.parms.total_contract_amount)) {
                    alertViewNoCancel("保存失败", "总合同金额请输入整数", function () {});
                    return;
                }
            }
            if (String(that.data.parms.current_contract_amount) == '') {
                alertViewNoCancel("保存失败", "请输入本次到账金额", function () {});
                return;
            } else if (String(that.data.parms.current_contract_amount) != '') {
                if (validateInt(that.data.parms.current_contract_amount)) {
                    alertViewNoCancel("保存失败", "本次到账金额请输入整数", function () {});
                    return;
                }
            }
            if (cooperation_type == 6 || cooperation_type == 15){
                if (parseInt(that.data.parms.total_contract_amount) - parseInt(that.data.parms.current_contract_amount) < 0) {
                    alertViewNoCancel("保存失败", "本次到账金额不得超过总合同金额", function () {
                    });
                    return;
                }
                if (String(that.data.parms.total_contract_bond) == '') {
                    alertViewNoCancel("保存失败", "请输入总合同保证金", function () {});
                    return;
                } else if (validateInt(that.data.parms.total_contract_bond)) {
                    alertViewNoCancel("保存失败", "请输入正确的总合同保证金", function () {});
                    return;
                }
                // 总合同轮单费
                if (String(that.data.parms.total_contract_round_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入总合同轮单费", function () {});
                    return;
                } else if (validateInt(that.data.parms.total_contract_round_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的总合同轮单费", function () {});
                    return;
                }
                // 总轮单费
                if (String(that.data.parms.total_contract_round_total_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入总合同总轮单费", function () {});
                    return;
                } else if (validateInt(that.data.parms.total_contract_round_total_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的总合同总轮单费", function () {});
                    return;
                }
                // 轮单费、总轮单费比较
                if (parseInt(that.data.parms.total_contract_round_total_amount) - parseInt(that.data.parms.total_contract_round_amount) < 0) {
                    alertViewNoCancel("保存失败", "轮单费不得大于总轮单费", function () {});
                    return;
                }
                // 本次到账保证金
                if (String(that.data.parms.current_contract_bond) == '') {
                    alertViewNoCancel("保存失败", "请输入本次到账保证金", function () {});
                    return;
                } else if (validateInt(that.data.parms.current_contract_bond)) {
                    alertViewNoCancel("保存失败", "请输入正确的本次到账保证金", function () {});
                    return;
                }
                // 本次到账总轮单费
                if (String(that.data.parms.current_contract_round_total_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入本次到账总轮单费", function () {});
                    return;
                } else if (validateInt(that.data.parms.current_contract_round_total_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的本次到账总轮单费", function () {});
                    return;
                }
                // 本次到账轮单费
                if (String(that.data.parms.current_contract_round_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入本次到账轮单费", function () {});
                    return;
                } else if (validateInt(that.data.parms.current_contract_round_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的本次到账轮单费", function () {});
                    return;
                }
                // 本次到账轮单费 总轮单费 比较
                if (parseInt(that.data.parms.current_contract_round_total_amount) - parseInt(that.data.parms.current_contract_round_amount) < 0) {
                    alertViewNoCancel("保存失败", "本次到账轮单费不得大于本次到账总轮单费", function () {});
                    return;
                }
                // 保证金比较
                if (parseInt(that.data.parms.total_contract_bond) - parseInt(that.data.parms.current_contract_bond) < 0) {
                    alertViewNoCancel("保存失败", "本次到账保证金不得大于总合同保证金", function () {});
                    return;
                }
                // 轮单费比较
                if (parseInt(that.data.parms.total_contract_round_amount) - parseInt(that.data.parms.current_contract_round_amount) < 0){
                    alertViewNoCancel("保存失败", "本次到账轮单费不得大于总合同轮单费", function () {});
                    return;
                }
                // 总轮单费比较
                if (parseInt(that.data.parms.total_contract_round_total_amount) - parseInt(that.data.parms.current_contract_round_total_amount) < 0) {
                    alertViewNoCancel("保存失败", "本次总轮单费不得大于总合同总轮单费", function () {});
                    return;
                }
                if (Number(that.data.parms.total_contract_gift_amount) - Number(that.data.parms.current_contract_gift_amount) < 0) {
                    alertViewNoCancel("保存失败", "本次返现金额不得大于总合同返现金额", function () {});
                    return;
                }
                
                /* if (parseInt(that.data.parms.total_contract_round_total_amount) - (parseInt(that.data.parms.total_contract_round_amount) + Number(that.data.parms.total_contract_gift_amount))!= 0) {
                    alertViewNoCancel("保存失败", "总轮单费与轮单费，返现金额的合计不一致", function () {
                    });
                    return;
                }
                if (parseInt(that.data.parms.current_contract_round_total_amount) - (parseInt(that.data.parms.current_contract_round_amount) + Number(that.data.parms.current_contract_gift_amount)) != 0) {
                    alertViewNoCancel("保存失败", "总轮单费与轮单费，返现金额的合计不一致", function () {
                    });
                    return;
                } */

                // 轮单费单价不为空
                if (String(that.data.parms.round_order_amount) === '') {
                    alertViewNoCancel("保存失败", "请输入轮单费单价", function () {});
                    return;
                }

            }else{
                // 总合同轮单费
                if (String(that.data.parms.total_contract_round_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入总合同会员费", function () {});
                    return;
                } else if (validateInt(that.data.parms.total_contract_round_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的总合同会员费", function () {});
                    return;
                }
                // 本次合同轮单费
                if (String(that.data.parms.current_contract_round_amount) == '') {
                    alertViewNoCancel("保存失败", "请输入本次到账会员费", function () {
                    });
                    return;
                } else if (validateInt(that.data.parms.current_contract_round_amount)) {
                    alertViewNoCancel("保存失败", "请输入正确的本次到账会员费", function () {
                    });
                    return;
                }
                // 轮单费比较
                if (parseInt(that.data.parms.current_contract_round_amount) - parseInt(that.data.parms.total_contract_round_amount) > 0) {
                    alertViewNoCancel("保存失败", "本次到账会员费不得大于总合同总会员费", function () {
                    });
                    return;
                }
                // 总合同分单承诺量不为空
                if (String(that.data.parms.promised_orders_fen) == '') {
                    alertViewNoCancel("保存失败", "总合同分单承诺量不为空", function () { });
                    return;
                }
                // 总合同赠单承诺量不为空
                if (String(that.data.parms.promised_orders_zeng) == '') {
                    alertViewNoCancel("保存失败", "总合同赠单承诺量不为空", function () { });
                    return;
                }
                // 本次合同分单承诺量不为空
                if (String(that.data.parms.current_promised_orders_fen) == '') {
                    alertViewNoCancel("保存失败", "本次合同分单承诺量不为空", function () { });
                    return;
                }
                // 本次合同赠单承诺量不为空
                if (String(that.data.parms.current_promised_orders_zeng) == '') {
                    alertViewNoCancel("保存失败", "本次合同赠单承诺量不为空", function () { });
                    return;
                }
            }
            // 合同保产值验证
            if (cooperation_type == 14 || cooperation_type == 15){
                let total = '' // 总合同比较值
                let current = '' // 本次合同比较值
                let text = ''
                if(cooperation_type == 14){
                    total = that.data.parms.total_contract_round_amount
                    current = that.data.parms.current_contract_round_amount
                    text = '会员费'
                } else if (cooperation_type == 15){
                    total = that.data.parms.total_contract_round_total_amount
                    current = that.data.parms.current_contract_round_total_amount
                    text = '总轮单费'
                }
                if(!that.data.parms.total_contract_amount_guaranteed){
                    alertViewNoCancel("保存失败", "请输入总合同保产值金额", function () {});
                    return;
                }else if(validateIntNoZero(that.data.parms.total_contract_amount_guaranteed)){
                    alertViewNoCancel("保存失败", "请输入正确的总合同保产值金额", function () {});
                    return;
                }else if(that.data.parms.total_contract_amount_guaranteed < total){
                    alertViewNoCancel("保存失败", `总合同保产值金额不得小于总合同${text}`, function () {});
                    return;
                }
                if(!that.data.parms.current_contract_amount_guaranteed){
                    alertViewNoCancel("保存失败", "请输入本次合同保产值金额", function () {});
                    return;
                }else if(validateIntNoZero(that.data.parms.current_contract_amount_guaranteed)){
                    alertViewNoCancel("保存失败", "请输入正确的本次合同保产值金额", function () {});
                    return;
                }else if(that.data.parms.current_contract_amount_guaranteed < current){
                    alertViewNoCancel("保存失败", `本次合同保产值金额不得小于本次到账${text}`, function () {});
                    return;
                }
                if(that.data.parms.current_contract_amount_guaranteed > that.data.parms.total_contract_amount_guaranteed){
                    alertViewNoCancel("保存失败", "本次到账保产值金额不得大于总合同保产值金额", function () {});
                    return;
                }
            }
            // 总平台使用费
            if (String(that.data.parms.total_platform_money) != '' && String(that.data.parms.total_platform_money) != '0') {
                if (validateInt(that.data.parms.total_platform_money)) {
                    alertViewNoCancel("保存失败", "请输入正确的总合同平台使用费", function () {
                    });
                    return;
                } else {
                    if (that.data.parms.total_platform_money_start_date == '') {
                        alertViewNoCancel("保存失败", "请选择总合同平台使用费开始时间", function () {});
                        return;
                    } else if (that.data.parms.total_platform_money_end_date == '') {
                        alertViewNoCancel("保存失败", "请选择总合同平台使用费结束时间", function () {});
                        return;
                    } else if (that.data.parms.total_platform_money_start_date > that.data.parms.total_platform_money_end_date) {
                        alertViewNoCancel("保存失败", "总合同平台使用费开始时间不能大于结束时间", function () {});
                        return;
                    }
                }   
            }
            // 本次平台使用费
            if (String(that.data.parms.current_platform_money) != '' && String(that.data.parms.current_platform_money) != '0') {
                if (validateInt(that.data.parms.current_platform_money)) {
                    alertViewNoCancel("保存失败", "请输入正确的本次到账平台使用费", function () {});
                    return;
                } else {
                    if (that.data.parms.current_platform_money_start_date == '') {
                        alertViewNoCancel("保存失败", "请选择本次到账平台使用费开始时间", function () {});
                        return;
                    } else if (that.data.parms.current_platform_money_end_date == '') {
                        alertViewNoCancel("保存失败", "请选择本次到账平台使用费结束时间", function () {});
                        return;
                    } else if (that.data.parms.current_platform_money_start_date > that.data.parms.current_platform_money_end_date) {
                        alertViewNoCancel("保存失败", "本次到账平台使用费开始时间不能大于结束时间", function () {});
                        return;
                    }
                }   
            }
            // 平台使用费比较
            if (that.data.parms.current_platform_money && that.data.parms.total_platform_money && (parseInt(that.data.parms.current_platform_money) - parseInt(that.data.parms.total_platform_money) > 0)) {
                alertViewNoCancel("保存失败", "本次平台使用费不得大于总合同平台使用费", function () {});
                return;
            }
            if (String(that.data.parms.lave_amount) != '') {
                if (validateInt(that.data.parms.lave_amount)) {
                    alertViewNoCancel("保存失败", "余额请输入整数", function () {});
                    return;
                }
            }
            if (String(that.data.parms.contract_handsel_banner) != '') {
                if (validateInt(that.data.parms.contract_handsel_banner)) {
                    alertViewNoCancel("保存失败", "总广告赠送-轮显请输入整数", function () {});
                    return;
                }
            } else if (that.data.parms.contract_handsel_banner == '') {
                alertViewNoCancel("保存失败", "总广告赠送-轮显不为空", function () {});
                return;
            }
            if (String(that.data.parms.contract_handsel_passage) != '') {
                if (validateInt(that.data.parms.contract_handsel_passage)) {
                    alertViewNoCancel("保存失败", "总广告赠送-通栏请输入整数", function () {});
                    return;
                }
            } else if (that.data.parms.contract_handsel_passage == '') {
                alertViewNoCancel("保存失败", "总广告赠送-通栏不为空", function () {});
                return;
            }
            if (that.data.zxTypeIndex == 0) {
                alertViewNoCancel("保存失败", "请选择家装/公装是否都接", function () {});
                return;
            }

            //网店代码不为空
            if (that.data.parms.company_id == ''){
                alertViewNoCancel("保存失败", "网店代码不为空", function () { });
                return;
            }

            //开站讨论组备注
            if (String(that.data.parms.open_city_chat_remark) == '') {
                alertViewNoCancel("保存失败", "开站讨论组备注不为空", function () {});
                return;
            }
        } else if (cooperation_type == 4) {
            if (that.data.parms.company_name == '') {
                alertViewNoCancel("保存失败", "请输入公司名称", function () {
                });
                return;
            }
            if (that.data.parms.company_contact == '') {
                alertViewNoCancel("保存失败", "请输入姓名", function () {
                });
                return;
            }
            if (that.data.parms.company_contact_phone == '') {
                alertViewNoCancel("保存失败", "请输入电话", function () {
                });
                return;
            } else if (that.data.parms.company_contact_phone != '') {
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!reg.test(that.data.parms.company_contact_phone)) {
                    alertViewNoCancel("提示", "请输入正确的手机号", function () {});
                    return false;
                }
            }
            if (that.data.parms.account == '') {
                alertViewNoCancel("保存失败", "请输入账号", function () {});
                return;
            } else if (that.data.parms.account != '') {
                var reg = /^[A-Za-z]+$/;
                if (!reg.test(that.data.parms.account)) {
                    alertViewNoCancel("保存失败", "账号请输入纯英文字母", function () {});
                    return;
                }
            }
            if (String(that.data.parms.current_contract_amount) == '') {
                alertViewNoCancel("保存失败", "请输入金额", function () {});
                return;
            } else if (String(that.data.parms.current_contract_amount) != '') {
                if (validateInt(that.data.parms.current_contract_amount)) {
                    alertViewNoCancel("保存失败", "金额请输入整数", function () {});
                    return;
                }
            }
            if (that.data.parms.current_contract_start == '') {
                alertViewNoCancel("保存失败", "请选择开始时间", function () {});
                return;
            } else if (that.data.parms.current_contract_end == '') {
                alertViewNoCancel("保存失败", "请选择结束时间", function () {});
                return;
            } else if (that.data.parms.current_contract_start > that.data.parms.current_contract_end) {
                alertViewNoCancel("保存失败", "开始时间不能大于结束时间", function () {});
                return;
            }
        } else if (cooperation_type == 5) {
            if (that.data.parms.company_name == '') {
                alertViewNoCancel("保存失败", "请输入公司名称", function () {});
                return;
            }
            if (cs == '') {
                alertViewNoCancel("保存失败", "请选择城市", function () {});
                return;
            }
            if (that.data.timesIndex == 0) {
                alertViewNoCancel("保存失败", "请选择倍数", function () {});
                return;
            }
            if (that.data.is_newIndex == 0) {
                alertViewNoCancel("保存失败", "请选择新/老会员", function () {});
                return;
            }
            if (that.data.parms.company_contact == '') {
                alertViewNoCancel("保存失败", "请输入联系人", function () {});
                return;
            }
            if (that.data.parms.company_contact_phone == '') {
                alertViewNoCancel("保存失败", "请输入电话", function () {});
                return;
            } else if (that.data.parms.company_contact_phone != '') {
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!reg.test(that.data.parms.company_contact_phone)) {
                    alertViewNoCancel("提示", "请输入正确的手机号", function () {});
                    return false;
                }
            }
            if (that.data.is_typeIndex == '') {
                alertViewNoCancel("保存失败", "请选择合作模式", function () {});
                return;
            }
            if (that.data.is_typeIndex != 2) {
                if (that.data.parms.contract_start == '') {
                    alertViewNoCancel("保存失败", "请选择合同开始时间", function () {});
                    return;
                } else if (that.data.parms.contract_end == '') {
                    alertViewNoCancel("保存失败", "请选择合同结束时间", function () {});
                    return;
                } else if (that.data.parms.contract_start > that.data.parms.contract_end) {
                    alertViewNoCancel("保存失败", "合同开始时间不能大于结束时间", function () {});
                    return;
                }
            }

            if (that.data.parms.current_payment_time == '') {
                alertViewNoCancel("保存失败", "请选择本次款项到账时间", function () {});
                return;
            }
            if (String(that.data.parms.total_contract_amount) == '') {
                alertViewNoCancel("保存失败", "请输入总合同金额", function () {});
                return;
            } else if (String(that.data.parms.total_contract_amount) != '') {
                if (validateInt(that.data.parms.total_contract_amount)) {
                    alertViewNoCancel("保存失败", "总合同金额请输入整数", function () {});
                    return;
                }
            }
            if (String(that.data.parms.current_contract_amount) == '') {
                alertViewNoCancel("保存失败", "请输入本次到账金额", function () {});
                return;
            } else if (String(that.data.parms.current_contract_amount) != '') {
                if (validateInt(that.data.parms.current_contract_amount)) {
                    alertViewNoCancel("保存失败", "本次到账金额请输入整数", function () {});
                    return;
                }
            }
            if (String(that.data.parms.lave_amount) != '') {
                if (validateInt(that.data.parms.lave_amount)) {
                    alertViewNoCancel("保存失败", "余额请输入整数", function () {});
                    return;
                }
            }
            if (that.data.parms.account == '') {
                alertViewNoCancel("保存失败", "请输入账户登录名称", function () {});
                return;
            } else if (that.data.parms.account != '') {
                if (that.data.parms.account.length < 6) {
                    alertViewNoCancel("保存失败", "账户登录名称请输入6-18位", function () {});
                    return;
                }
            }
            //开站讨论组备注
            if (String(that.data.parms.open_city_chat_remark) == '') {
                alertViewNoCancel("保存失败", "开站讨论组备注不为空", function () {});
                return;
            }
        }
        // 当状态等于6，即”客服未通过，普通信息更改“，需要手动将提交的状态码改成3(审核通过/待客服上传)
        if(that.data.parmsStatus){
            status = 3
        }
        if(cooperation_type == 8){
            if(!that.validationFormDelay()){
                return;
            }
            that.setData({
                ['parms.status']: status
            })
        }else{
            that.setData({
                ['parms.status']: status,
                ['parms.viptype']: that.data.timesIndex / 2,
                ['parms.is_new']: that.data.is_newIndex,
                ['parms.is_kf_voucher']: that.data.kfIndex,
                ['parms.passage_position']: that.data.tonglanIndex,
                ['parms.lxs']: that.data.zxTypeIndex,
                ['parms.back_ratio']: that.data.backRatios[that.data.backRatioIndex],
                ['parms.city_name']: curCity,
                ['parms.cs']: cs      
            })
        }
        if(cooperation_type == 6) {
            that.setData({
                timesIndex: 0,
                ['parms.viptype']: ''
            })
        } else if (cooperation_type == 5) {
            that.setData({
                ['parms.cooperation_mode']: that.data.is_typeIndex,
            })
        } else if(cooperation_type == 14) {
            that.setData({
                backRatioIndex: 0,
                ['parms.back_ratio']: '',
                ['parms.viptype']: 1
            })
        } else if(cooperation_type == 15) {
            that.setData({
                timesIndex: 0,
                ['parms.viptype']: '',
                ['parms.back_ratio']: 1
            })
        }else{
            that.setData({
                backRatioIndex: 0,
                ['parms.back_ratio']: ''
            })
        }
        if(that.data.zxTypeIndex != 4) {
            that.setData({
                ['parms.lxs_remark']: ''
            })
        }
        let passage_position = that.data.parms.passage_position;
        if (passage_position == 0) {
            that.setData({
                ['parms.passage_position']: '',
            })
        }
        if (that.data.parms.total_contract_gift_amount == '') {
          that.setData({
            ['parms.total_contract_gift_amount']: 0,
          })
        }
        if (that.data.parms.current_contract_gift_amount == '') {
          that.setData({
            ['parms.current_contract_gift_amount']: 0,
          })
        }
        // 请求接口
        if (isSave) {
            that.headleAjax('保存')
        }else{
            alertViewWithCancel("提交", "确认提交该公司信息？", function () {
                that.headleAjax('提交')
            })
        }
    },
    headleAjax(name){
        let that = this
        wx.getStorage({
            key: 'info',
            success: function (res) {
                let token = res.data.token;
                addReport('/v1/sale_report/save', that.data.parms, {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                    if (res.error_code == 0) {
                        wx.showToast({
                            title: `${name}成功`,
                            icon: 'success',
                            duration: 200
                        })
                        setTimeout(function () {
                            wx.navigateBack({
                                delta: 1,
                            })
                        }, 300)
                    } else {
                        alertViewNoCancel(`${name}失败`, res.error_msg, function () {
                        });
                        return;
                    }
                })
            }
        })
    },
    validationFormDelay(){
        let that = this
        if (that.data.parms.company_id == '') {
            alertViewNoCancel("保存失败", "请输入网店代码", function () {
            });
            return false;
        }
        if (that.data.parms.current_vip_id == '') {
            alertViewNoCancel("保存失败", "请选择本次会员时间", function () {
            });
            return false;
        }
        if (that.data.parms.report_id == '') {
            alertViewNoCancel("保存失败", "请选择本次报备合同", function () {
            });
            return false;
        }
        if (that.data.parms.delay_promise_orders == '') {
            alertViewNoCancel("保存失败", "请输入延期承诺单量", function () {
            });
            return false;
        }
        if (that.data.parms.delay_real_days == '') {
            alertViewNoCancel("保存失败", "请输入实际延期天数", function () {
            });
            return false;
        }else if (that.data.parms.delay_real_days < 1) {
            alertViewNoCancel("保存失败", "实际延期天数大于0", function () {
            });
            return false;
        }
        if (that.data.parms.delay_contract_start == '') {
            alertViewNoCancel("保存失败", "请选择延期合同日期（开始）", function () {
            });
            return false;
        }
        return true
    },
    cancel: function () {
        wx.navigateBack({
            delta: 1
        })
    },
    checkCompany: function () {
        let that = this;
        let uid = that.data.parms.company_id;
        if (uid == '') {
            that.setData({
                showCompany: true,
                company_jc: '请输入网店代码'
            });
            return;
        }
        wx.getStorage({
            key: 'info',
            success: function (res) {
                let token = res.data.token;
                testCompany('/v1/sale_report/test_company', {
                    uid: uid
                }, {
                    token: token,
                    'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                    if (res.error_code == 0) {
                        that.setData({
                            showCompany: true,
                            company_jc: res.data[0][0].qc + ' 注册地点：' + res.data[0][0].cname
                        })
                    } else {
                        that.setData({
                            showCompany: true,
                            company_jc: res.error_msg
                        })
                    }
                })
            },
            fail: function (err) {
                console.log(err)
            }
        })
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },
    /**
    * 图片上传
    * 
    */
    chooseImg: function (e) {
        var that = this, pics = this.data.pics;
        var FSM = wx.getFileSystemManager();
        if (pics.length < 20) {
            wx.chooseImage({
                count: 9, // 最多可以选择的图片张数，默认9
                sizeType: ['original', 'compressed'], // original 原图，compressed 压缩图，默认二者都有
                sourceType: ['album', 'camera'], // album 从相册选图，camera 使用相机，默认二者都有
                success: function (res) {
                    // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
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
                                    that.data.parms.imgs.push(data.data.img_path)
                                    pics.push(data.data.img_full)

                                    that.setData({
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
                fail: function (res) {
                    console.log(res)
                }
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
        var pics = this.data.pics;
        var imgs = this.data.parms.imgs;
        var index = e.currentTarget.dataset.index;
        pics.splice(index, 1);
        imgs.splice(index, 1);
        this.setData({
            pics: pics,
            ['parms.imgs']: imgs
        })
    },
    // 预览图片
    previewImg1: function (e) {
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
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

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

    },
    chooseMember: function (e) {
        let that = this;
        let type = that.data.member;
        let status = that.data.parmsStatus;
        if (status) {
            return
        }
        if (type == 1 || type == 2 || type == 3 || type == 6 || type == 7 || type == 14 || type == 15) {
            this.setData({
                typeChoose: true
            })
        } else if (type == 4 || type == 5) {
            return
        }
    },
    choose: function (e) {
        let member = Number(e.currentTarget.dataset.type)
        let name = e.currentTarget.dataset.text
        this.setData({
            typeChoose: false,
            member: member,
            cooperation_type_name: name,
            ['parms.cooperation_type']: member
        })
    },
    dateChange1: function (e) {
        let type = this.data.parms.cooperation_type
        this.setData({
            ['parms.contract_start']: e.detail.value
        });
        if (type != 4) {
            if (this.data.parms.contract_end != '') {
                let startTime = this.data.parms.contract_start
                let nedTime = this.data.parms.contract_end
                let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
                if (timeValue < 0) {
                    alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                    });
                    this.setData({
                      ['parms.contract_start']:''
                    });
                    return;
                }
                let arr = []
                let list = this.data.emptyOrderOptionsList
                list.forEach(item => {
                    if(timeReturn(startTime,nedTime,item.start,item.end)){
                        arr.push(item.value)
                    }
                })
                this.setData({
                    totalTime: this.monthDayDiff(startTime,nedTime),
                    'parms.year_month_one': arr[0] || '',
                    'parms.year_month_two': arr[1] || ''
                })
            }
        }
    },
    dateChange2: function (e) {
        let type = this.data.parms.cooperation_type
        this.setData({
            ['parms.contract_end']: e.detail.value
        });
        if (type != 4) {
            if (this.data.parms.contract_start != '') {
                let startTime = this.data.parms.contract_start
                let nedTime = this.data.parms.contract_end
                let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
                if (timeValue < 0) {
                    alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                    });
                    this.setData({
                      ['parms.contract_end']: ''
                    })
                    return;
                }
                let arr = []
                let list = this.data.emptyOrderOptionsList
                list.forEach(item => {
                    if(timeReturn(startTime,nedTime,item.start,item.end)){
                        arr.push(item.value)
                    }
                })
                this.setData({
                    totalTime: this.monthDayDiff(startTime,nedTime),
                    'parms.year_month_one': arr[0] || '',
                    'parms.year_month_two': arr[1] || ''
                })
            }
        }
    },
    dateChange3: function (e) {
        let that = this
        let cooperation_type = that.data.parms.cooperation_type
        let city_name = that.data.parms.city_name
        let current_contract_amount = that.data.parms.current_contract_amount
        let current_contract_start = that.data.parms.current_contract_start
        let current_contract_end = that.data.parms.current_contract_end
        let viptype = that.data.timesIndex / 2
        let type = this.data.parms.cooperation_type
        let start = e.detail.value
        this.setData({
            ['parms.current_contract_start']: e.detail.value
        });

        if (city_name != '' && viptype != '' && current_contract_start != '' && current_contract_end != '' && current_contract_amount != '') {
            wx.getStorage({
                key: 'info',
                success: function (res) {
                    let token = res.data.token;
                    checkContract('/v1/sale_report/check_contract', {
                    cooperation_type: cooperation_type,
                    city_name: city_name,
                    current_contract_amount: current_contract_amount,
                    current_contract_start: current_contract_start,
                    current_contract_end: current_contract_end,
                    viptype: viptype
                    }, {
                        token: token,
                        'content-type': 'application/x-www-form-urlencoded',
                    }).then(res => {
                        if (res.error_code == 0) {
                            if (res.data.info.is_abnormal == 1) {
                                that.setData({
                                ['parms.is_abnormal']: 1
                                })
                                return;
                            }
                        } else {
                            alertViewNoCancel("错误提示", res.error_msg, function () {
                            });
                            return;
                        }
                    })
                },
                fail: function (err) {
                    console.log(err)
                }
            })
        }
        if (type == 1 || type == 2 || type == 3 || type == 6 || type == 7 || type == 14 || type == 15) {
            
            if (this.data.parms.current_contract_end != '') {
                let startTime = this.data.parms.current_contract_start
                let nedTime = this.data.parms.current_contract_end
                let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
                if (timeValue < 0) {
                    alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                    });
                    this.setData({
                      ['parms.current_contract_start']: ''
                    })
                    return;
                }
                let arr = []
                let list = this.data.emptyOrderOptionsList
                list.forEach(item => {
                    if(timeReturn(startTime,nedTime,item.start,item.end)){
                        arr.push(item.value)
                    }
                })
                this.setData({
                    userTime: this.monthDayDiff(startTime,nedTime),
                    'parms.current_year_month_one': arr[0] || '',
                    'parms.current_year_month_two': arr[1] || ''
                })
            }
        } else if (type == 4) {
            let oldYear = Number(start.split('-')[1]) < 3 ? Number(start.split('-')[0]) : Number(start.split('-')[0]) + 1
            let allYearDay = oldYear % 4 === 0 ? 365 : 364
            let begin = (new Date(start).getTime()) + allYearDay*24*60*60*1000
            let xbegin = new Date(begin);
            var year = xbegin.getFullYear();
            var month = xbegin.getMonth() + 1 > 9 ? xbegin.getMonth() + 1 : '0' + (xbegin.getMonth() + 1);
            var day = xbegin.getDate() > 9 ? xbegin.getDate() : '0' + xbegin.getDate();
            this.setData({
                ['parms.current_contract_end']: year + '-' + month + '-' + day
            })
        }
    },
    dateChange4: function (e) {
        let that = this
        let cooperation_type = that.data.parms.cooperation_type
        let city_name = that.data.parms.city_name
        let current_contract_amount = that.data.parms.current_contract_amount
        let current_contract_start = that.data.parms.current_contract_start
        let current_contract_end = that.data.parms.current_contract_end
        let viptype = that.data.timesIndex / 2
        this.setData({
            ['parms.current_contract_end']: e.detail.value
        });

        if (city_name != '' && viptype != '' && current_contract_start != '' && current_contract_end != '' && current_contract_amount != '') {
          wx.getStorage({
            key: 'info',
            success: function (res) {
              let token = res.data.token;
              checkContract('/v1/sale_report/check_contract', {
                cooperation_type: cooperation_type,
                city_name: city_name,
                current_contract_amount: current_contract_amount,
                current_contract_start: current_contract_start,
                current_contract_end: current_contract_end,
                viptype: viptype
              }, {
                  token: token,
                  'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                  if (res.error_code == 0) {
                    if (res.data.info.is_abnormal == 1) {
                      that.setData({
                        ['parms.is_abnormal']: 1
                      })
                      return;
                    }
                  } else {
                    alertViewNoCancel("错误提示", res.error_msg, function () {
                    });
                    return;
                  }
                })
            },
            fail: function (err) {
              console.log(err)
            }
          })
        }
        if (this.data.parms.current_contract_start != '') {
            let startTime = this.data.parms.current_contract_start
            let nedTime = this.data.parms.current_contract_end
            let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
            if (timeValue <= 0) {
                alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                });
                this.setData({
                  ['parms.current_contract_end']: ''
                });
                return;
            }
            let arr = []
                let list = this.data.emptyOrderOptionsList
                list.forEach(item => {
                    if(timeReturn(startTime,nedTime,item.start,item.end)){
                        arr.push(item.value)
                    }
                })
            this.setData({
                userTime: this.monthDayDiff(startTime,nedTime),
                'parms.current_year_month_one': arr[0] || '',
                'parms.current_year_month_two': arr[1] || ''
            })
        }
    },
    dateChange5: function (e) {
        this.setData({
            ['parms.current_payment_time']: e.detail.value
        });
    },
    dateChange6: function (e) {
        this.setData({
            ['parms.next_payment_time']: e.detail.value
        });
    },
    dateChange7: function (e) {
        this.setData({
            ['parms.deny_promised_start']: e.detail.value
        });
    },
    dateChange8: function (e) {
        this.setData({
            ['parms.deny_promised_end']: e.detail.value
        });
    },
    total_platform_money: function (e) {
        this.setData({
            ['parms.total_platform_money']: e.detail.value
        });
    },
    current_platform_money: function (e) {
        this.setData({
            ['parms.current_platform_money']: e.detail.value
        });
    },
    total_platform_money_start_date: function (e) {
        this.setData({
            ['parms.total_platform_money_start_date']: e.detail.value
        });
        if (this.data.parms.total_platform_money_end_date != '') {
            let startTime = this.data.parms.total_platform_money_start_date
            let nedTime = this.data.parms.total_platform_money_end_date
            let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
            if (timeValue < 0) {
                alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                });
                this.setData({
                  ['parms.total_platform_money_start_date']:''
                });
                return;
            }
            this.setData({
                totalPlatformMoneyTime: this.monthDayDiff(startTime,nedTime)
            })
        }
    },
    total_platform_money_end_date: function (e) {
        this.setData({
            ['parms.total_platform_money_end_date']: e.detail.value
        });
        if (this.data.parms.total_platform_money_start_date != '') {
            let startTime = this.data.parms.total_platform_money_start_date
            let nedTime = this.data.parms.total_platform_money_end_date
            let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
            if (timeValue < 0) {
                alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                });
                this.setData({
                  ['parms.total_platform_money_start_date']:''
                });
                return;
            }
            this.setData({
                totalPlatformMoneyTime: this.monthDayDiff(startTime,nedTime)
            })
        }
    },
    current_platform_money_start_date: function (e) {
        this.setData({
            ['parms.current_platform_money_start_date']: e.detail.value
        });
        if (this.data.parms.current_platform_money_end_date != '') {
            let startTime = this.data.parms.current_platform_money_start_date
            let nedTime = this.data.parms.current_platform_money_end_date
            let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
            if (timeValue < 0) {
                alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                });
                this.setData({
                  ['parms.current_platform_money_start_date']:''
                });
                return;
            }
            this.setData({
                currentPlatformMoneyTime: this.monthDayDiff(startTime,nedTime)
            })
        }
    },
    current_platform_money_end_date: function (e) {
        this.setData({
            ['parms.current_platform_money_end_date']: e.detail.value
        });
        if (this.data.parms.current_platform_money_start_date != '') {
            let startTime = this.data.parms.current_platform_money_start_date
            let nedTime = this.data.parms.current_platform_money_end_date
            let timeValue = (new Date(nedTime).getTime() - new Date(startTime).getTime()) / 86400000
            if (timeValue < 0) {
                alertViewNoCancel("错误提示", "结束时间不能小于开始时间", function () {
                });
                this.setData({
                  ['parms.current_platform_money_end_date']:''
                });
                return;
            }
            this.setData({
                currentPlatformMoneyTime: this.monthDayDiff(startTime,nedTime)
            })
        }
    },
    // 选择城市
    toCity: function () {
        let city = this.data.curCity;
        wx.navigateTo({
        url: '../city/city?needArea=1&curCity=' + city
        })
    },
    // 选择倍数
    selectTimes: function (e) {
        this.setData({
            timesIndex: e.detail.value
        })
    },
    // 选择返现比例
    selectRatio: function (e) {
        this.setData({
            backRatioIndex: e.detail.value
        })
    },
    // 选择会员类型
    selectMemberType: function (e) {
        this.setData({
            is_newIndex: e.detail.value,
            kfIndex: e.detail.value == 1 ? 0 : 1,
            'parms.is_kf_voucher': e.detail.value == 1 ? 0 : 1
        })
    },
    // 选择合作模式
    selectSelectType: function (e) {
        this.setData({
            is_typeIndex: e.detail.value
        })
    },
    // 选择通栏
    selsectTonglan: function (e) {
        this.setData({
            tonglanIndex: e.detail.value
        })
    },
    // 选择家装公装
    selectZxType: function (e) {
        this.setData({
            zxTypeIndex: e.detail.value
        })
    },
    // 过年月不承诺订单量一
    selectEmptyOrderOne: function (e) {
        this.setData({
            emptyOrderOneIndex: e.detail.value
        })
    },
    company_name: function (e) {
        this.setData({
            ['parms.company_name']: e.detail.value
        })
    },
    city_name: function (e) {
        this.setData({
            ['parms.city_name']: e.detail.value
        })
    },
    company_contact: function (e) {
        this.setData({
            ['parms.company_contact']: e.detail.value
        })
    },
    company_contact_position: function (e) {
        this.setData({
            ['parms.company_contact_position']: e.detail.value
        })
    },
    company_contact_phone: function (e) {
        this.setData({
            ['parms.company_contact_phone']: e.detail.value
        })
    },
    company_contact_weixin: function (e) {
        this.setData({
            ['parms.company_contact_weixin']: e.detail.value
        })
    },
    total_contract_amount: function (e) {
        this.setData({
            ['parms.total_contract_amount']: e.detail.value
        })
    },
    total_contract_bond: function (e) {
        this.setData({
            ['parms.total_contract_bond']: e.detail.value
        })
    },


    total_contract_round_amount: function (e) {
        let type = this.data.parms.cooperation_type
        if(type != 6 && type != 15 ){
            this.setData({
                ['parms.total_contract_round_amount']: e.detail.value,
                ['parms.total_contract_amount_guaranteed']: e.detail.value*10
              })
        }else{
            this.setData({
                ['parms.total_contract_round_amount']: e.detail.value
              })
        }
      
    },

    total_contract_round_total_amount: function (e) {
      this.setData({
        ['parms.total_contract_round_total_amount']: e.detail.value,
        ['parms.total_contract_amount_guaranteed']: e.detail.value*10
      })
    },
    total_contract_gift_amount: function (e) {
      this.setData({
        ['parms.total_contract_gift_amount']: e.detail.value
      })
    },


    current_contract_amount: function (e) {
        this.setData({
            ['parms.current_contract_amount']: e.detail.value
        })
    },
    current_contract_bond: function (e) {
        this.setData({
            ['parms.current_contract_bond']: e.detail.value
        })
    },

    current_contract_round_amount: function (e) {
        let type = this.data.parms.cooperation_type
        if(type != 6 && type != 15 ){
            this.setData({
                ['parms.current_contract_round_amount']: e.detail.value,
                ['parms.current_contract_amount_guaranteed']: e.detail.value*10
              })
        }else{
            this.setData({
                ['parms.current_contract_round_amount']: e.detail.value
              })
        }
      
    },

    current_contract_round_total_amount: function (e) {
      this.setData({
        ['parms.current_contract_round_total_amount']: e.detail.value,
        ['parms.current_contract_amount_guaranteed']: e.detail.value*10
      })
    },
    current_contract_gift_amount: function (e) {
      this.setData({
        ['parms.current_contract_gift_amount']: e.detail.value
      })
    },

    // 总合同保产值金额
    total_contract_amount_guaranteed(e){
        this.setData({
            ['parms.total_contract_amount_guaranteed']: e.detail.value
        })
    },
    // 本次合同保产值金额
    current_contract_amount_guaranteed(e){
        this.setData({
            ['parms.current_contract_amount_guaranteed']: e.detail.value
        })
    },

    // 轮单费单价
    round_order_amount(e){
        this.setData({
            ['parms.round_order_amount']: e.detail.value
        })
    },
    lave_amount: function (e) {
        this.setData({
            ['parms.lave_amount']: e.detail.value
        })
    },
    logo_address: function (e) {
        this.setData({
            ['parms.logo_address']: e.detail.value
        })
    },
    contract_handsel_passage: function (e) {
        this.setData({
            ['parms.contract_handsel_passage']: e.detail.value
        })
    },
    otherOrderDescAction: function (e) {
        this.setData({
            ['parms.lxs_remark']: e.detail.value
        })
    },
    contract_handsel_banner: function (e) {
        this.setData({
            ['parms.contract_handsel_banner']: e.detail.value
        })
    },
    current_adver_content: function (e) {
        this.setData({
            ['parms.current_adver_content']: e.detail.value
        })
    },
    amount_and_area: function (e) {
        this.setData({
            ['parms.amount_and_area']: e.detail.value
        })
    },
    totalContractFengAction: function (e) {
        this.setData({
            ['parms.promised_orders_fen']: e.detail.value
        })
    },
    totalContractZengAction: function (e) {
        this.setData({
            ['parms.promised_orders_zeng']: e.detail.value
        })
    },
    currentContractFengAction: function (e) {
        this.setData({
            ['parms.current_promised_orders_fen']: e.detail.value
        })
    },
    currentContractZengAction: function (e) {
        this.setData({
            ['parms.current_promised_orders_zeng']: e.detail.value
        })
    },
    company_id: function (e) {
        this.setData({
            ['parms.company_id']: e.detail.value
        })
    },
    remarke: function (e) {
        this.setData({
            ['parms.remarke']: e.detail.value
        })
    },
    account: function (e) {
        this.setData({
            ['parms.account']: e.detail.value
        })
    },
    contract_remark: function (e) {
      this.setData({
        ['parms.contract_remark']: e.detail.value
      })
    },
    total_contract_remark: function (e) {
      this.setData({
        ['parms.total_contract_remark']: e.detail.value
      })
    },
    openCityChatRemark: function (e) {
        this.setData({
            ['parms.open_city_chat_remark']: e.detail.value
        })
    },
    check_contract: function (e) {
        let that = this
        let cooperation_type = that.data.parms.cooperation_type
        let city_name = that.data.parms.city_name
        let current_contract_amount = that.data.parms.current_contract_amount
        let current_contract_start = that.data.parms.current_contract_start
        let current_contract_end = that.data.parms.current_contract_end
        let viptype = that.data.timesIndex / 2
        if (city_name != '' && viptype != '' && current_contract_start != '' && current_contract_end != '' && current_contract_amount != '') {
        wx.getStorage({
            key: 'info',
            success: function (res) {
            let token = res.data.token;
            checkContract('/v1/sale_report/check_contract', {
                cooperation_type: cooperation_type,
                city_name: city_name,
                current_contract_amount: current_contract_amount,
                current_contract_start: current_contract_start,
                current_contract_end: current_contract_end,
                viptype: viptype
            }, {
                token: token,
                'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                if (res.error_code == 0) {
                    if (res.data.info.is_abnormal == 1) {
                    that.setData({
                        ['parms.is_abnormal']: 1
                    })
                    return;
                    }else{
                    that.setData({
                        ['parms.is_abnormal']: res.data.info.is_abnormal
                    })
                    }
                } else {
                    alertViewNoCancel("错误提示", res.error_msg, function () {
                    });
                    return;
                }
                })
            },
            fail: function (err) {
            console.log(err)
            }
        })
        }
    },
    selectKfVoucher: function (e) {
        this.setData({
        kfIndex: e.detail.value,
        'parms.is_kf_voucher': e.detail.value
        })
    },
    //会员延期
    wdBtnSearch(e){
        this.setData({
            ['parms.company_id']: e.detail.value
        })
    },
    // 查询网店代码公司详情
    searchCompanyInfo(e,n){
        let that = this
        let companyId = that.data.parms.company_id
        if(!companyId){
            alertViewNoCancel("提示", "请输入网店代码", function () {});
        }else{
            wx.getStorage({
                key: 'info',
                success: function (res) {
                  let token = res.data.token;
                  testCompany('/v1/sale_report/find_delay_company', {
                    company_id: companyId
                  }, {
                      token: token,
                      'content-type': 'application/x-www-form-urlencoded',
                    }).then(res => {
                      if (res.error_code == 0) {
                        let companyInfo = res.data.company_info
                        let reportContractList = res.data.report_contract_list
                        let vipContractList = res.data.vip_contract_list
                        let company_mode_name = '' // 会员类型名称
                        let reportId ;
                        let promiseOrders ;
                        let currentContractStart ;
                        let currentContractEnd ;
                        let currentVipId ;
                        let reportCooperationType ;
                        let currentVipStart ;
                        let currentVipEnd ;
                        let currentVipIndex ;
                        let currentReportIndex ;
                        if(reportContractList.length === 0 && vipContractList.length === 0){
                            alertViewNoCancel("提示", "查询公司无合同", function () {});
                            return;
                        }
                        if(reportContractList.length > 0){
                            reportContractList.map((item, index) => {
                                item.item = item.current_contract_start + '至' + item.current_contract_end
                                if(n){
                                    if(item.report_id === that.data.parms.report_id){
                                        currentReportIndex = index
                                    }
                                }
                                return item
                            })
                            if(!n){
                                reportId = reportContractList[0].report_id
                                promiseOrders = reportContractList[0].current_promised_orders_fen
                                currentContractStart = reportContractList[0].current_contract_start
                                currentContractEnd = reportContractList[0].current_contract_end
                            }
                        }else{
                            alertViewNoCancel("提示", "无本次报备", function () {});
                        }
                        if(vipContractList.length > 0){
                            vipContractList.map((item, index) => {
                                item.item = item.start_time + '至' + item.end_time
                                if(n){
                                    if(item.id === that.data.parms.current_vip_id){
                                        currentVipIndex = index
                                    }
                                }
                                return item
                            })
                            if(!n){
                                currentVipId = vipContractList[0].id
                                reportCooperationType = vipContractList[0].company_mode
                                currentVipStart = vipContractList[0].start_time
                                currentVipEnd = vipContractList[0].end_time
                                that.getCompanyAllotOrders()
                            }
                        }else{
                            alertViewNoCancel("提示", "无本次会员", function () {});
                        }
                        if(!n){
                            if(companyInfo.company_mode === 1){
                                company_mode_name = '常规会员'
                            }else if(companyInfo.company_mode === 2){
                                company_mode_name = '新签返'
                            }else if(companyInfo.company_mode === 3){
                                company_mode_name = '老签返'
                            }
                        }
                        if(!n){
                            that.setData({
                                'parms.company_name': companyInfo.company_qc,
                                'parms.company_mode': companyInfo.company_mode,
                                'parms.company_mode_name': company_mode_name,
                                reportContractList:reportContractList,
                                vipContractList:vipContractList,
                                currentVipIndex:0,
                                currentReportIndex:0,
                                'parms.report_id': reportId,
                                'parms.promise_orders': promiseOrders,
                                'parms.current_contract_start': currentContractStart,
                                'parms.current_contract_end': currentContractEnd,
                                'parms.current_vip_id': currentVipId,
                                'parms.report_cooperation_type': reportCooperationType,
                                'parms.current_vip_start': currentVipStart,
                                'parms.current_vip_end': currentVipEnd,
                            })
                        }else{
                            that.setData({
                                reportContractList:reportContractList,
                                vipContractList:vipContractList,
                                currentVipIndex:currentVipIndex,
                                currentReportIndex:currentReportIndex,
                            })
                        }

                        
                      } else {
                        alertViewNoCancel("错误提示", res.error_msg, function () {
                        });
                        return;
                      }
                    })
                },
                fail: function (err) {
                  console.log(err)
                }
            })
        }
    },
    // 本次会员时间
    currentVipChange(e){
        let that = this
        let index = e.detail.value
        let list = that.data.vipContractList
        that.setData({
            currentVipIndex: e.detail.value,
            'parms.current_vip_id': list[index].id,
            'parms.current_vip_start': list[index].start_time,
            'parms.current_vip_end': list[index].end_time,
            'parms.report_cooperation_type': list[index].company_mode
        })
        that.getCompanyAllotOrders()
    },
    // 本地报备合同
    currentReportChange(e){
        let that = this
        let index = e.detail.value
        let list = that.data.reportContractList
        that.setData({
            currentReportIndex: index,
            'parms.report_id': list[index].report_id,
            'parms.promise_orders': list[index].current_promised_orders_fen,
            'parms.current_contract_start': list[index].current_contract_start,
            'parms.current_contract_end': list[index].current_contract_end
        })
        that.watchVipReport()
    },
    // 获取装修公司分配订单数
    getCompanyAllotOrders(){
        let that = this
        wx.getStorage({
            key: 'info',
            success: function (res) {
              let token = res.data.token;
              testCompany('/v1/company/allot_orders', {
                company_id: that.data.parms.company_id,
                date_begin: that.data.parms.current_vip_start,
                date_end: that.data.parms.current_vip_end,
              }, {
                  token: token,
                  'content-type': 'application/x-www-form-urlencoded',
                }).then(res => {
                  if (res.error_code == 0) {
                    let orderFenAvg = res.data.order_fen_avg
                    let promiseOrders = that.data.parms.promise_orders
                    that.setData({
                        'parms.current_fen_orders': res.data.fen_orders,
                        orderFenAvg: orderFenAvg
                    })
                    that.getDelayPromisOorders(promiseOrders, orderFenAvg)
                    that.watchVipReport()
                    that.delayContractEnd()
                  } else {
                    alertViewNoCancel("错误提示", res.error_msg, function () {
                    });
                    return;
                  }
                })
            },
            fail: function (err) {
              console.log(err)
            }
        })
    },
    // 本次合同本次报备更改时操作
    watchVipReport(){
        let that = this
        let vipNum = that.data.parms.current_fen_orders
        let reportNum = that.data.parms.promise_orders
        let orderFenAvg = that.data.orderFenAvg
        const patrn = /(^-?[0-9]\d*$)/
        if(patrn.test(vipNum) && patrn.test(reportNum)){
            let num = Number(reportNum) - Number(vipNum)
            if(orderFenAvg === 0){
                that.setData({
                    'parms.delay_promise_orders' : num,
                    'parms.delay_days' : '',
                    'parms.delay_real_days' : '',
                }) 
            }else{
               that.setData({
                    'parms.delay_promise_orders' : num,
                    'parms.delay_days' : Math.ceil(num/orderFenAvg),
                    'parms.delay_real_days' : Math.ceil(num/orderFenAvg*0.7)
                }) 
            }
        }
        that.delayContractEnd()
    },
    // 延期承诺单量
    delayPromisOordersInput(e){
        let that = this
        let val = e.detail.value
        let orderFenAvg = that.data.orderFenAvg
        if(orderFenAvg === 0) {
            that.setData({
                'parms.delay_promise_orders' : val,
                'parms.delay_days' : '',
                'parms.delay_real_days' : '',
            }) 
        }else{
            that.setData({
                'parms.delay_promise_orders' : val,
                'parms.delay_days' : Math.ceil(val/orderFenAvg),
                'parms.delay_real_days' : Math.ceil(val/orderFenAvg*0.7)
            })
        }
        that.delayContractEnd()
    },
    // 延期承诺单量获取接口时值修改
    getDelayPromisOorders(a,b){
        let that = this
        let val = a
        let orderFenAvg =  b
        that.setData({
            'parms.delay_promise_orders' : val,
            'parms.delay_days' : Math.ceil(val/orderFenAvg),
            'parms.delay_real_days' : Math.ceil(val/orderFenAvg*0.7)
        })
    },
    //实际延期天数
    delayRealDaysInput(e){
        let that = this
        that.setData({
            'parms.delay_real_days' : e.detail.value
        })
    },
    // 延期合同开始日期
    dateDelayChange(e){
        let that = this
        let val = e.detail.value
        that.setData({
            'parms.delay_contract_start' : val
        })
        that.delayContractEnd()
    },
    // 延期合同结束日期
    delayContractEnd(){
        let that = this
        let val = that.data.parms.delay_contract_start
        let addDayNum = Number(that.data.parms.delay_real_days)
        let dateTime = ''
        if(val && addDayNum > 0){
            dateTime = new Date(val)
            dateTime = dateTime.setDate(dateTime.getDate() + addDayNum - 1)
            dateTime = getTime(new Date(dateTime)/1000)
        }
        that.setData({
            'parms.delay_contract_end' : dateTime
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
    getCurrentDate(){
        var date = new Date()
        //获取年份  
        var Y =date.getFullYear();
        //获取月份  
        var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
        //获取当日日期 
        var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
        return Y + '-' + M + '-' + D
    },
    monthDayDiff(startDate,endDate) {
        let getLength = function(m){
          let flag = [1, 3, 5, 7, 8, 10, 12, 4, 6, 9, 11, 2];
          let index = flag.findIndex((temp) => {
              return temp === m + 1
          });
          if (index <= 6) {
              return 31
          } else if (index > 6 && index <= 10) {
              return 30
          } else {
            if(start.getFullYear()%4 === 0){
              return 29
            }else{
              return 28
            }
          }
        }
        let start = new Date(startDate);
        let end = new Date(endDate);
        let year = end.getFullYear() - start.getFullYear();
        let month = end.getMonth() - start.getMonth();
        let day = end.getDate() - start.getDate();
        let startMonthLength = getLength(start.getMonth())
        let endMonthLength = getLength(end.getMonth())
        if (month < 0) {
            year--;
            month = end.getMonth() + (12 - start.getMonth());
        }
        if (day < -1) {
            month--;
            if(day + startMonthLength > 0 ){
              day = end.getDate() + (startMonthLength - start.getDate());
            }
        }
        if(day === startMonthLength-1 && month=== 0 || (start.getDate()==1 && endMonthLength === end.getDate())){
          month ++
          day = -1
        }
        let text = ''
        if(year>0){
          month += year*12
        }
        if(month>0){
          text += `${month}月`
        }
        if(day >= 0){
          text += `${day+1}天`
        }
         return text
    }
})
